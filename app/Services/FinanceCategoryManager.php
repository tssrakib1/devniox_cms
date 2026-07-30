<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class FinanceCategoryManager
{
    public function create(string $type, array $data, int $userId): Model
    {
        $category = $type === 'income' ? IncomeCategory::create($data) : ExpenseCategory::create($data);
        ActivityLogService::log('finance', 'category_created', ucfirst($type)." category {$category->name} created.", $category, null, $category->only(['name', 'slug', 'active']), $userId);
        Cache::forget('finance.dashboard.stats.v1');

        return $category;
    }

    public function update(string $type, Model $category, array $data, int $userId): void
    {
        $old = $category->only(['name', 'slug', 'description', 'active']);
        $category->update($data);
        ActivityLogService::log('finance', 'category_updated', ucfirst($type)." category {$category->name} updated.", $category, $old, $category->only(array_keys($old)), $userId);
        Cache::forget('finance.dashboard.stats.v1');
    }

    public function delete(string $type, Model $category, int $userId): void
    {
        if ($category->transactions()->exists()) {
            throw ValidationException::withMessages(['category' => 'This category is used by transactions and cannot be deleted. Deactivate it instead.']);
        }
        ActivityLogService::log('finance', 'category_deleted', ucfirst($type)." category {$category->name} deleted.", $category, $category->only(['name', 'slug', 'active']), null, $userId);
        $category->delete();
        Cache::forget('finance.dashboard.stats.v1');
    }
}
