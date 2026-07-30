<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReuseMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_active;
    }

    public function rules(): array
    {
        return ['context' => ['required', Rule::in(['product.thumbnail', 'product.banner', 'product.logo', 'service.cover_image', 'service.featured_image', 'portfolio.thumbnail', 'portfolio.cover_image', 'blog.featured_image', 'blog.social_image', 'cms.open_graph_image', 'communication.attachment', 'finance.attachment', 'order.attachment'])], 'record_id' => ['required', 'integer', 'min:1'], 'label' => ['nullable', 'string', 'max:160']];
    }
}
