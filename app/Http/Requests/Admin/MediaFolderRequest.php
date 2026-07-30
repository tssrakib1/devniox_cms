<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaFolder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $folder = $this->route('folder');

        return $this->user()->can($folder ? 'update' : 'create', $folder ?: MediaFolder::class);
    }

    public function rules(): array
    {
        $folder = $this->route('folder');

        return ['name' => ['required', 'string', 'max:120'], 'parent_id' => ['nullable', 'integer', 'different:id', Rule::exists('media_folders', 'id')->whereNull('deleted_at'), Rule::notIn([$folder?->id])]];
    }
}
