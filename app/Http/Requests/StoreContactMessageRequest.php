<?php

namespace App\Http\Requests;

class StoreContactMessageRequest extends LeadRequest
{
    public function rules(): array
    {
        return $this->commonRules() + ['subject' => ['required', 'string', 'max:200', 'not_regex:/[\r\n]/'], 'message' => ['required', 'string', 'min:10', 'max:20000'], 'website' => ['nullable', 'url:http,https', 'max:2048']];
    }
}
