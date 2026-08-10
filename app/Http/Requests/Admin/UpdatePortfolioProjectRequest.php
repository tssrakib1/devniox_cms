<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioProject;

class UpdatePortfolioProjectRequest extends PortfolioProjectRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->portfolio());
    }

    public function rules(): array
    {
        return $this->projectRules($this->portfolio());
    }

    private function portfolio(): PortfolioProject
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof PortfolioProject ? $portfolio : PortfolioProject::findOrFail($portfolio);
    }
}
