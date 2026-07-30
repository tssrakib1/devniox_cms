<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunicationAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lead'));
    }

    public function rules(): array
    {
        return ['label' => ['nullable', 'string', 'max:160'], 'file' => ['required', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,webp,zip', 'max:10240']];
    }
}
