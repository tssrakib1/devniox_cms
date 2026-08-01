<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,ico', 'max:4096'];
        $url = ['nullable', 'url:http,https', 'max:2048'];

        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['required', 'array'],
            'settings.*.*' => ['nullable', 'string', 'max:20000'],
            'settings.general.site_name' => ['required_with:settings.general', 'string', 'max:120'],
            'settings.general.tagline' => ['nullable', 'string', 'max:180'],
            'settings.general.default_language' => ['required_with:settings.general', 'string', 'max:12'],
            'settings.general.timezone' => ['required_with:settings.general', 'timezone'],
            'settings.branding.theme_color' => ['required_with:settings.branding', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.contact.company_name' => ['required_with:settings.contact', 'string', 'max:180'],
            'settings.contact.address' => ['nullable', 'string', 'max:2000'],
            'settings.contact.phone' => ['nullable', 'string', 'max:40'],
            'settings.contact.mobile' => ['nullable', 'string', 'max:40'],
            'settings.contact.whatsapp' => ['nullable', 'string', 'max:40'],
            'settings.contact.email' => ['nullable', 'email:rfc', 'max:254'],
            'settings.contact.support_email' => ['nullable', 'email:rfc', 'max:254'],
            'settings.contact.sales_email' => ['nullable', 'email:rfc', 'max:254'],
            'settings.contact.google_maps_embed' => ['nullable', 'string', 'max:5000', 'not_regex:/<script\b/i'],
            'settings.seo.meta_title' => ['required_with:settings.seo', 'string', 'max:70'],
            'settings.seo.meta_description' => ['required_with:settings.seo', 'string', 'max:160'],
            'settings.seo.meta_keywords' => ['nullable', 'string', 'max:500'],
            'settings.seo.canonical_base_url' => $url,
            'settings.seo.robots_meta' => ['required_with:settings.seo', Rule::in(['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow'])],
            'settings.seo.organization' => ['required_with:settings.seo', 'string', 'max:180'],
            'settings.analytics.ga4_measurement_id' => ['nullable', 'regex:/^G-[A-Z0-9]+$/i', 'max:30'],
            'settings.analytics.gtm_id' => ['nullable', 'regex:/^GTM-[A-Z0-9]+$/i', 'max:30'],
            'settings.analytics.clarity_id' => ['nullable', 'alpha_num:ascii', 'max:64'],
            'settings.analytics.facebook_pixel_id' => ['nullable', 'regex:/^[0-9]{5,30}$/'],
            'settings.email.smtp_host' => ['nullable', 'string', 'max:255'],
            'settings.email.smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'settings.email.smtp_username' => ['nullable', 'string', 'max:255'],
            'settings.email.smtp_password' => ['nullable', 'string', 'max:2000'],
            'settings.email.smtp_encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'settings.email.from_name' => ['required_with:settings.email', 'string', 'max:120'],
            'settings.email.from_email' => ['required_with:settings.email', 'email:rfc', 'max:254'],
            'settings.integrations.webhook_url' => $url,
            'settings.maintenance.enabled' => ['required_with:settings.maintenance', 'boolean'],
            'settings.maintenance.message' => ['nullable', 'string', 'max:2000'],
            'settings.maintenance.estimated_return' => ['nullable', 'date'],
            'settings.maintenance.allow_admin' => ['required_with:settings.maintenance', 'boolean'],
            'image_settings' => ['nullable', 'array'],
            'image_settings.*' => ['nullable', 'array'],
            'image_settings.*.*' => $image,
            'remove_image_settings' => ['nullable', 'array'],
            'remove_image_settings.*' => ['nullable', 'array'],
            'remove_image_settings.*.*' => ['boolean'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.url' => ['nullable', 'url:http,https', 'max:2048'],
            'social_links.*.is_visible' => ['required', 'boolean'],
            'social_links.*.display_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
