<?php

namespace App\Http\Requests\Admin;

class UpdatePortfolioProjectRequest extends PortfolioProjectRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('portfolio'));
    }

    public function rules(): array
    {
        return $this->projectRules($this->route('portfolio'));
    }
}
