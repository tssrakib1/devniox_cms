<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    private const CACHE_KEY = 'website.settings.v2';

    private ?array $requestCache = null;

    private ?array $requestAll = null;

    private ?array $requestPublic = null;

    private ?Collection $requestSocialLinks = null;

    public function all(): array
    {
        if ($this->requestAll !== null) {
            return $this->requestAll;
        }

        $cached = $this->cached();
        if (! isset($cached['all'], $cached['secrets'])) {
            $this->forget();
            $cached = $this->cached();
        }

        $settings = $cached['all'];
        foreach (array_keys($cached['secrets']) as $key) {
            $settings[$key] = $this->decrypt($settings[$key] ?? null);
        }

        return $this->requestAll = $settings;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public function public(): array
    {
        if ($this->requestPublic !== null) {
            return $this->requestPublic;
        }

        $cached = $this->cached();
        if (! isset($cached['public'])) {
            $this->forget();
            $cached = $this->cached();
        }

        return $this->requestPublic = $cached['public'];
    }

    public function socialLinks(): Collection
    {
        return $this->requestSocialLinks ??= Cache::rememberForever('website.social-links', fn () => SocialLink::visible()->orderBy('display_order')->get());
    }

    public function update(array $values, array $files = [], array $removals = []): void
    {
        $values = Arr::dot($values);
        $files = Arr::dot($files);
        $removals = Arr::dot($removals);

        DB::transaction(function () use ($values, $files, $removals) {
            foreach ($values as $key => $value) {
                [$group, $name] = explode('.', $key, 2);
                $setting = Setting::where(['group' => $group, 'key' => $name])->first();
                if (! $setting || ($setting->type === 'secret' && blank($value))) {
                    continue;
                }
                $setting->update(['value' => $setting->type === 'secret' ? Crypt::encryptString($value) : $value]);
            }

            foreach ($removals as $key => $remove) {
                if (! $remove || array_key_exists($key, $files)) {
                    continue;
                }
                [$group, $name] = str_contains($key, '.') ? explode('.', $key, 2) : ['branding', $key];
                $setting = Setting::where(['group' => $group, 'key' => $name])->where('type', 'image')->first();
                if (! $setting || blank($setting->value)) {
                    continue;
                }
                $old = $setting->value;
                $setting->update(['value' => '']);
                DB::afterCommit(fn () => app(ManagedImageService::class)->delete($old));
            }

            foreach ($files as $key => $file) {
                if (! $file) {
                    continue;
                }
                [$group, $name] = str_contains($key, '.') ? explode('.', $key, 2) : ['branding', $key];
                $setting = Setting::where(['group' => $group, 'key' => $name])->where('type', 'image')->first();
                if (! $setting) {
                    continue;
                }
                $old = $setting->value;
                $size = in_array($name, ['favicon'], true) ? 512 : 1600;
                $path = app(ManagedImageService::class)->store($file, $group.'/'.$name, $size, $size);
                $setting->update(['value' => $path]);
                DB::afterCommit(fn () => app(ManagedImageService::class)->delete($old));
            }
        });

        $this->forget();
    }

    public function forget(): void
    {
        $this->requestCache = null;
        $this->requestAll = null;
        $this->requestPublic = null;
        $this->requestSocialLinks = null;
        Cache::forget(self::CACHE_KEY);
        Cache::forget('website.social-links');
    }

    private function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL),
            'integer' => (int) $value,
            'secret' => $this->decrypt($value),
            default => $value,
        };
    }

    private function cached(): array
    {
        if ($this->requestCache !== null) {
            return $this->requestCache;
        }

        try {
            return $this->requestCache = Cache::rememberForever(self::CACHE_KEY, function () {
                $all = [];
                $public = [];
                $secrets = [];
                Setting::query()->select(['group', 'key', 'value', 'type', 'is_public'])->each(function (Setting $setting) use (&$all, &$public, &$secrets) {
                    $key = $setting->group->value.'.'.$setting->key;
                    $value = $setting->type === 'secret' ? $setting->value : $this->cast($setting->value, $setting->type);
                    $all[$key] = $value;
                    if ($setting->type === 'secret') {
                        $secrets[$key] = true;
                    } elseif ($setting->is_public) {
                        $public[$key] = $value;
                    }
                });

                return compact('all', 'public', 'secrets');
            });
        } catch (QueryException) {
            return ['all' => [], 'public' => [], 'secrets' => []];
        }
    }

    private function decrypt(?string $value): ?string
    {
        if (blank($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }
}
