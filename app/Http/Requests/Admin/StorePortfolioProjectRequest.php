<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioProject;

class StorePortfolioProjectRequest extends PortfolioProjectRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PortfolioProject::class);
    }

    public function rules(): array
    {
        return $this->projectRules();
    }
}
