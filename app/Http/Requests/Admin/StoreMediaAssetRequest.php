<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MediaAsset::class);
    }

    public function rules(): array
    {
        return ['media_folder_id' => ['nullable', 'integer', 'exists:media_folders,id'], 'name' => ['nullable', 'string', 'max:180'], 'alt_text' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:5000'], 'files' => ['required', 'array', 'min:1', 'max:20'], 'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,mp4,webm,mov', 'max:51200']];
    }
}
