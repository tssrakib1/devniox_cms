<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkPortfolioActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', PortfolioProject::class);
    }

    public function rules(): array
    {
        return ['project_ids' => ['required', 'array', 'min:1', 'max:200'], 'project_ids.*' => ['required', 'integer', 'distinct', 'exists:portfolio_projects,id'], 'action' => ['required', Rule::in(['publish', 'draft', 'archive', 'delete', 'restore', 'feature', 'unfeature'])]];
    }
}
