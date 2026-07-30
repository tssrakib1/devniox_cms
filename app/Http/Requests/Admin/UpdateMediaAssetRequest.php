<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('media'));
    }

    public function rules(): array
    {
        return ['media_folder_id' => ['nullable', 'integer', 'exists:media_folders,id'], 'name' => ['required', 'string', 'max:180'], 'alt_text' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000']];
    }
}
