<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Services\ActivityLogService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function edit(?string $section = null)
    {
        $allowed = ['general', 'branding', 'contact', 'social', 'seo', 'analytics', 'email', 'integrations', 'maintenance'];
        abort_if($section && ! in_array($section, $allowed, true), 404);
        foreach (['facebook', 'linkedin', 'youtube', 'instagram', 'x', 'github', 'whatsapp'] as $order => $platform) {
            SocialLink::firstOrCreate(['platform' => $platform], ['display_order' => $order, 'is_visible' => false]);
        }
        $records = collect(Setting::orderBy('group')->orderBy('key')->get()->groupBy(fn ($setting) => $setting->group->value));

        return view('admin.settings.edit', [
            'records' => $section && $section !== 'social' ? $records->only($section) : $records,
            'section' => $section,
            'socialLinks' => SocialLink::orderBy('display_order')->get(),
        ]);
    }

    public function update(UpdateSettingsRequest $request, SettingsService $settings): RedirectResponse
    {
        $settings->update($request->validated('settings', []), Arr::dot($request->file('image_settings', [])), Arr::dot($request->validated('remove_image_settings', [])));

        foreach ($request->validated('social_links', []) as $platform => $data) {
            SocialLink::updateOrCreate(['platform' => $platform], $data);
        }

        ActivityLogService::log('settings', 'updated', 'Website settings updated.', null, null, ['setting_keys' => array_keys($request->validated('settings', []))]);

        return back()->with('success', 'Website settings updated.');
    }

    public function testEmail(Request $request, SettingsService $settings): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $data = $request->validate(['test_email' => ['required', 'email:rfc', 'max:254']]);
        $all = $settings->all();
        if (blank($all['email.smtp_host'] ?? null) || blank($all['email.from_email'] ?? null)) {
            return back()->withErrors(['test_email' => 'SMTP host and From Email must be configured before sending a test email.']);
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $all['email.smtp_host']);
        Config::set('mail.mailers.smtp.port', (int) ($all['email.smtp_port'] ?? 587));
        Config::set('mail.mailers.smtp.username', $all['email.smtp_username'] ?? null);
        Config::set('mail.mailers.smtp.password', $all['email.smtp_password'] ?? null);
        Config::set('mail.mailers.smtp.encryption', $all['email.smtp_encryption'] ?: null);
        Config::set('mail.from.address', $all['email.from_email']);
        Config::set('mail.from.name', $all['email.from_name'] ?? ($all['general.site_name'] ?? config('app.name')));

        try {
            Mail::raw('This is a Website Settings SMTP test email from '.($all['general.site_name'] ?? config('app.name')).'.', fn ($message) => $message->to($data['test_email'])->subject('Website Settings test email'));
        } catch (\Throwable $exception) {
            return back()->withErrors(['test_email' => 'Test email failed: '.$exception->getMessage()]);
        }

        ActivityLogService::log('settings', 'test_email_sent', 'Website settings test email sent.', null, null, ['recipient' => $data['test_email']]);

        return back()->with('success', 'Test email sent successfully.');
    }
}
