<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkProductActionRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ActivityLogService;
use App\Services\ProductManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'category' => ['nullable', 'integer', 'exists:product_categories,id'],
            'featured' => ['nullable', 'in:0,1'],
            'sort' => ['nullable', 'in:name,version,status,is_featured,updated_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $products = Product::query()->with('category')
            ->when($validated['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%")))
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($validated['category'] ?? null, fn (Builder $query, int $category) => $query->where('product_category_id', $category))
            ->when(array_key_exists('featured', $validated), fn (Builder $query) => $query->where('is_featured', (bool) $validated['featured']))
            ->orderBy($validated['sort'] ?? 'updated_at', $validated['direction'] ?? 'desc')
            ->paginate(20)->withQueryString();

        return view('admin.products.index', ['products' => $products, 'categories' => ProductCategory::orderBy('name')->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.form', $this->formData(new Product));
    }

    public function store(StoreProductRequest $request, ProductManager $manager): RedirectResponse
    {
        if ($request->string('status')->value() === ProductStatus::Archived->value && ! $request->user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validated();
        if (! $request->user()->isAdmin()) {
            $data['is_featured'] = false;
        }
        $product = $manager->create($data, $request->user()->id);
        ActivityLogService::log('products', 'created', "Product {$product->name} created.", $product, null, $product->only(['name', 'slug', 'status', 'is_featured']));

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        $product->load(['highlights', 'modules', 'features', 'requirements', 'links', 'seo', 'galleryImages', 'pricingPlans.features', 'faqs']);

        return view('admin.products.form', $this->formData($product));
    }

    public function update(UpdateProductRequest $request, Product $product, ProductManager $manager): RedirectResponse
    {
        $newStatus = ProductStatus::from($request->string('status')->value());
        if ($newStatus === ProductStatus::Archived) {
            $this->authorize('archive', $product);
        } elseif ($newStatus !== $product->status) {
            $this->authorize('publish', $product);
        }
        $data = $request->validated();
        if (! $request->user()->isAdmin()) {
            $data['is_featured'] = $product->is_featured;
        }
        $old = $product->only(['name', 'slug', 'is_featured']) + ['status' => $product->status->value];
        $manager->update($product, $data, $request->user()->id);
        $fresh = $product->fresh();
        ActivityLogService::log('products', 'updated', "Product {$fresh->name} updated.", $fresh, $old, $fresh->only(array_keys($old)));
        if ($old['status'] !== $fresh->status->value) {
            ActivityLogService::log('products', $fresh->status->value === 'published' ? 'published' : ($fresh->status->value === 'archived' ? 'archived' : 'drafted'), "Product {$fresh->name} status changed to {$fresh->status->value}.", $fresh, ['status' => $old['status']], ['status' => $fresh->status->value]);
        }
        if ((bool) $old['is_featured'] !== $fresh->is_featured) {
            ActivityLogService::log('products', $fresh->is_featured ? 'featured' : 'unfeatured', "Product {$fresh->name} featured state changed.", $fresh, ['is_featured' => $old['is_featured']], ['is_featured' => $fresh->is_featured]);
        }

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        ActivityLogService::log('products', 'deleted', "Product {$product->name} deleted.", $product, $product->only(['name', 'slug', 'status']));
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function bulk(BulkProductActionRequest $request): RedirectResponse
    {
        $products = Product::whereKey($request->validated('product_ids'))->get();
        $action = $request->validated('action');

        DB::transaction(function () use ($products, $action, $request) {
            foreach ($products as $product) {
                $before = $product->only(['status', 'is_featured']);
                match ($action) {
                    'publish' => $this->setStatus($request, $product, ProductStatus::Published),
                    'draft' => $this->setStatus($request, $product, ProductStatus::Draft),
                    'archive' => $this->archive($request, $product),
                    'delete' => $this->deleteProduct($request, $product),
                    'feature' => $this->setFeatured($request, $product, true),
                    'unfeature' => $this->setFeatured($request, $product, false),
                };
                ActivityLogService::log('products', $action === 'publish' ? 'published' : ($action === 'archive' ? 'archived' : ($action === 'feature' ? 'featured' : ($action === 'unfeature' ? 'unfeatured' : ($action === 'delete' ? 'deleted' : 'drafted')))), "Bulk action {$action} applied to product {$product->name}.", $product, $before, $product->fresh()?->only(['status', 'is_featured']));
            }
        });

        return back()->with('success', 'Bulk action completed.');
    }

    private function formData(Product $product): array
    {
        return ['product' => $product, 'categories' => ProductCategory::active()->orderBy('sort_order')->orderBy('name')->get(), 'statuses' => ProductStatus::cases()];
    }

    private function setStatus(Request $request, Product $product, ProductStatus $status): void
    {
        $this->authorizeForUser($request->user(), 'publish', $product);
        $product->update(['status' => $status, 'published_at' => $status === ProductStatus::Published ? ($product->published_at ?? now()) : null, 'updated_by' => $request->user()->id]);
    }

    private function archive(Request $request, Product $product): void
    {
        $this->authorizeForUser($request->user(), 'archive', $product);
        $product->update(['status' => ProductStatus::Archived, 'published_at' => null, 'updated_by' => $request->user()->id]);
    }

    private function deleteProduct(Request $request, Product $product): void
    {
        $this->authorizeForUser($request->user(), 'delete', $product);
        $product->delete();
    }

    private function setFeatured(Request $request, Product $product, bool $featured): void
    {
        $this->authorizeForUser($request->user(), 'feature', $product);
        $product->update(['is_featured' => $featured, 'updated_by' => $request->user()->id]);
    }
}
