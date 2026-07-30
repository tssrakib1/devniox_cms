<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Services\ActivityLogService;
use App\Services\SettingsService;

class SettingsController extends Controller
{
    public function edit(?string $section = null)
    {
        $allowed = ['general', 'branding', 'contact', 'social', 'seo', 'analytics', 'email', 'integrations', 'maintenance'];
        abort_if($section && ! in_array($section, $allowed), 404);
        $records = Setting::orderBy('group')->orderBy('key')->get()->groupBy(fn ($s) => $s->group->value);

        return view('admin.settings.edit', ['records' => $section && $section !== 'social' ? $records->only($section) : $records, 'section' => $section, 'socialLinks' => SocialLink::orderBy('display_order')->get()]);
    }

    public function update(UpdateSettingsRequest $r, SettingsService $s)
    {
        $s->update($r->validated('settings', []), collect(['logo', 'dark_logo', 'favicon', 'apple_touch_icon', 'default_share_image'])->mapWithKeys(fn ($k) => [$k => $r->file($k)])->all());
        foreach ($r->validated('social_links', []) as $platform => $data) {
            SocialLink::where('platform', $platform)->update($data);
        }
        ActivityLogService::log('settings', 'updated', 'Website settings updated.', null, null, ['setting_keys' => array_keys($r->validated('settings', []))]);

        return back()->with('success', 'Website settings updated.');
    }
}
