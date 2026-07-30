<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunicationReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('lead'));
    }

    public function rules(): array
    {
        return ['subject' => ['required', 'string', 'max:200'], 'message' => ['required', 'string', 'min:2', 'max:30000'], 'attachments' => ['nullable', 'array', 'max:10'], 'attachments.*' => ['file', 'mimes:pdf,doc,docx,png,jpg,jpeg,webp,zip', 'max:10240']];
    }
}
