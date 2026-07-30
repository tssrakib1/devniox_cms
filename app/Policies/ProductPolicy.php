<?php

namespace App\Policies;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission('products.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission('products.edit');
    }

    public function publish(User $user, Product $product): bool
    {
        return $user->hasPermission('products.edit');
    }

    public function archive(User $user, Product $product): bool
    {
        return false;
    }

    public function feature(User $user, Product $product): bool
    {
        return false;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission('products.delete') && $product->status !== ProductStatus::Published;
    }
}
