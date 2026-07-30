<?php

namespace App\Http\Requests\Admin;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post') ? 'website-settings.create' : 'website-settings.edit';

        return $this->user()?->hasPermission($permission) ?? false;
    }

    public function rules(): array
    {
        $platform = $this->route('platform');

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', Rule::unique(Platform::class, 'slug')->ignore($platform)],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'description' => ['required', 'string', 'max:500'],
            'website_url' => ['required', 'url:http,https', 'max:255'],
            'badge' => ['nullable', 'string', 'max:80'],
            'brand_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'display_order' => ['required', 'integer', 'min:0'],
            'open_in_new_tab' => ['boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}


