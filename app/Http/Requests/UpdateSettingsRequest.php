<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:20000'],
            'settings.contact.email' => ['nullable', 'email'],
            'settings.contact.support_email' => ['nullable', 'email'],
            'settings.contact.sales_email' => ['nullable', 'email'],
            'settings.contact.maps_url' => ['nullable', 'url:http,https'],
            'settings.email.sender_address' => ['nullable', 'email'],
            'settings.email.reply_to' => ['nullable', 'email'],
            'settings.email.lead_notification_email' => ['nullable', 'email'],
            'settings.integrations.external_api_url' => ['nullable', 'url:http,https'],
            'settings.general.website_status' => ['nullable', 'in:online,maintenance'],
            'settings.branding.theme_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.analytics.head_scripts' => ['nullable', 'string', 'max:20000', 'not_regex:/<\/?(?:iframe|object|embed|form)\b/i'],
            'settings.analytics.footer_scripts' => ['nullable', 'string', 'max:20000', 'not_regex:/<\/?(?:iframe|object|embed|form)\b/i'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'dark_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico', 'max:512'],
            'default_share_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'apple_touch_icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.url' => ['nullable', 'url:http,https', 'max:2048'],
            'social_links.*.is_visible' => ['required', 'boolean'],
            'social_links.*.display_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
