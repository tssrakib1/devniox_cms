<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageAttachment', $this->route('order'));
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:160'],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip,txt,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
