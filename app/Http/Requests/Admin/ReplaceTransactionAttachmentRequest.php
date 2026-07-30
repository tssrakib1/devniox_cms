<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceTransactionAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attachment = $this->route('attachment');

        return $attachment && $attachment->finance_transaction_id === $this->route('transaction')->id && $this->user()->can('manageAttachment', $this->route('transaction'));
    }

    public function rules(): array
    {
        return ['label' => ['nullable', 'string', 'max:160'], 'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip,txt,jpg,jpeg,png,webp', 'max:10240']];
    }
}
