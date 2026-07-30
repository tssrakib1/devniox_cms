<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('addNote', $this->route('order'));
    }

    public function rules(): array
    {
        return ['note' => ['required', 'string', 'max:10000']];
    }
}
