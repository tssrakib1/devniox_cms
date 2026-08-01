<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\CmsService;
use Closure;
use Illuminate\Http\Request;

class CmsFooterController extends Controller
{
    public function edit(CmsService $s)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.cms.footer', ['footer' => $s->footer()]);
    }

    public function update(Request $r, CmsService $s)
    {
        abort_unless($r->user()->isAdmin(), 403);

        $safeUrl = function (string $attribute, mixed $value, Closure $fail): void {
            if (blank($value)) {
                return;
            }

            $value = (string) $value;
            $isInternalPath = str_starts_with($value, '/') && ! str_starts_with($value, '//') && ! str_contains($value, '\\') && ! preg_match('/\s|[[:cntrl:]]/', $value);
            $isHttpUrl = filter_var($value, FILTER_VALIDATE_URL) && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
            if ($isInternalPath || $isHttpUrl) {
                return;
            }

            $fail('The '.$attribute.' must be an internal path or an http/https URL.');
        };

        $urlRules = ['nullable', 'max:2048', $safeUrl];
        $d = $r->validate([
            'copyright' => ['required', 'max:255'],
            'short_description' => ['required', 'max:2000'],
            'quick_links_heading' => ['required', 'max:100'],
            'products_heading' => ['nullable', 'max:100'],
            'services_heading' => ['nullable', 'max:100'],
            'ai_heading' => ['nullable', 'max:100'],
            'blog_heading' => ['required', 'max:100'],
            'company_heading' => ['nullable', 'max:100'],
            'resources_heading' => ['nullable', 'max:100'],
            'privacy_url' => $urlRules,
            'terms_url' => $urlRules,
            'cookies_url' => $urlRules,
            'about_label' => ['nullable', 'max:100'],
            'about_url' => $urlRules,
            'contact_label' => ['nullable', 'max:100'],
            'contact_url' => $urlRules,
            'demo_label' => ['nullable', 'max:100'],
            'demo_url' => $urlRules,
            'quote_label' => ['nullable', 'max:100'],
            'quote_url' => $urlRules,
            'blog_label' => ['nullable', 'max:100'],
            'blog_url' => $urlRules,
            'rss_label' => ['nullable', 'max:100'],
            'rss_url' => $urlRules,
            'sitemap_label' => ['nullable', 'max:100'],
            'sitemap_url' => $urlRules,
            'privacy_label' => ['nullable', 'max:100'],
            'terms_label' => ['nullable', 'max:100'],
            'cookies_label' => ['nullable', 'max:100'],
            'contact_heading' => ['nullable', 'max:120'],
            'contact_text' => ['nullable', 'max:2000'],
            'address_label' => ['nullable', 'max:100'],
            'email_label' => ['nullable', 'max:100'],
            'phone_label' => ['nullable', 'max:100'],
            'whatsapp_label' => ['nullable', 'max:100'],
            'business_hours_text' => ['nullable', 'max:2000'],
            'support_hours_text' => ['nullable', 'max:2000'],
            'cta_title' => ['nullable', 'max:160'],
            'cta_description' => ['nullable', 'max:2000'],
            'cta_button_text' => ['nullable', 'max:120'],
            'cta_button_url' => $urlRules,
            'newsletter_heading' => ['nullable', 'max:160'],
            'newsletter_description' => ['nullable', 'max:2000'],
            'newsletter_placeholder' => ['nullable', 'max:160'],
            'newsletter_button_text' => ['nullable', 'max:120'],
            'bottom_text' => ['nullable', 'max:255'],
            'made_by_text' => ['nullable', 'max:160'],
            'powered_by_text' => ['nullable', 'max:160'],
            'version_text' => ['nullable', 'max:80'],
        ]);
        $s->updateFooter($d, $r->user()->id);
        ActivityLogService::log('cms', 'updated', 'Footer updated.');

        return back()->with('success', 'Footer updated.');
    }
}
