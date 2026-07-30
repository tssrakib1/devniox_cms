<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:140', 'alpha_dash:ascii'],
            'search' => ['nullable', 'string', 'max:180'],
        ]);
        $categorySlug = $filters['category'] ?? null;
        $search = $filters['search'] ?? null;
        $relations = ['category', 'highlights', 'modules', 'galleryImages', 'pricingPlans' => fn ($query) => $query->where('is_active', true)];

        $products = Product::published()->whereHas('category', fn (Builder $category) => $category->active())->with($relations)
            ->when($categorySlug, fn (Builder $query, string $slug) => $query->whereHas('category', fn (Builder $category) => $category->where('slug', $slug)->active()))
            ->when($search, fn (Builder $query, string $search) => $query->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%")))
            ->orderBy('display_order')->latest('published_at')->paginate(12)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => ProductCategory::active()->whereHas('products', fn (Builder $query) => $query->published())->orderBy('sort_order')->get(),
            'featuredProducts' => $categorySlug || $search ? collect() : Product::published()->whereHas('category', fn (Builder $category) => $category->active())->featured()->with($relations)->orderBy('display_order')->limit(4)->get(),
            'newestProducts' => $categorySlug || $search ? collect() : Product::published()->whereHas('category', fn (Builder $category) => $category->active())->with($relations)->latest('published_at')->limit(4)->get(),
            'selectedCategory' => $categorySlug,
            'search' => $search,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::published()->whereHas('category', fn (Builder $category) => $category->active())->where('slug', $slug)->with(['category', 'highlights', 'modules', 'features', 'requirements', 'links', 'seo', 'galleryImages', 'pricingPlans' => fn ($query) => $query->where('is_active', true), 'pricingPlans.features', 'faqs'])->firstOrFail();
        $relatedProducts = Product::published()->whereHas('category', fn (Builder $category) => $category->active())->where('product_category_id', $product->product_category_id)->whereKeyNot($product->id)->with(['category', 'modules', 'pricingPlans' => fn ($query) => $query->where('is_active', true)])->orderBy('display_order')->limit(4)->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
