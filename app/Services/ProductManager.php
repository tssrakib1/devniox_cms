<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductManager
{
    public function __construct(private readonly ManagedImageService $images) {}

    public function create(array $data, int $userId): Product
    {
        return $this->persist(new Product, $data, $userId);
    }

    public function update(Product $product, array $data, int $userId): Product
    {
        return $this->persist($product, $data, $userId);
    }

    private function persist(Product $product, array $data, int $userId): Product
    {
        $newPaths = [];
        $oldPaths = [];

        try {
            $result = DB::transaction(function () use ($product, $data, $userId, &$newPaths, &$oldPaths) {
                $wasPublished = $product->exists && $product->status === ProductStatus::Published;
                $attributes = Arr::only($data, ['product_category_id', 'name', 'slug', 'version', 'status', 'is_featured', 'display_order', 'short_description', 'full_description']);
                $attributes['updated_by'] = $userId;
                $attributes['created_by'] ??= $product->exists ? $product->created_by : $userId;
                $attributes['published_at'] = $attributes['status'] === ProductStatus::Published->value
                    ? ($wasPublished ? $product->published_at : now())
                    : null;
                $product->fill($attributes)->save();

                foreach (['thumbnail' => [900, 700], 'banner' => [1920, 900], 'logo' => [900, 500]] as $field => [$width, $height]) {
                    if (($data[$field] ?? null) instanceof UploadedFile) {
                        $column = $field.'_path';
                        $newPath = $this->images->store($data[$field], 'products/'.$product->id.'/branding', $width, $height);
                        $newPaths[] = $newPath;
                        $oldPaths[] = $product->{$column};
                        $product->update([$column => $newPath]);
                    }
                }

                $this->replaceChildren($product, $data, $newPaths, $oldPaths);
                $this->syncGallery($product, $data, $newPaths, $oldPaths);

                return $product->fresh();
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $this->images->delete($path);
            }
            throw $exception;
        }

        foreach (array_filter($oldPaths) as $path) {
            $this->images->delete($path);
        }

        return $result;
    }

    private function replaceChildren(Product $product, array $data, array &$newPaths, array &$oldPaths): void
    {
        foreach (['highlights', 'modules', 'features', 'faqs'] as $relation) {
            $product->{$relation}()->delete();
            $product->{$relation}()->createMany(array_values($data[$relation] ?? []));
        }

        $product->requirements()->updateOrCreate([], $data['requirements'] ?? []);
        $product->links()->updateOrCreate([], $data['links'] ?? []);

        $seo = $data['seo'] ?? [];
        if (($seo['open_graph_image'] ?? null) instanceof UploadedFile) {
            $oldPath = $product->seo?->open_graph_image_path;
            $seo['open_graph_image_path'] = $this->images->store($seo['open_graph_image'], 'products/'.$product->id.'/seo', 1200, 630);
            $newPaths[] = $seo['open_graph_image_path'];
            $oldPaths[] = $oldPath;
            unset($seo['open_graph_image']);
        }
        $product->seo()->updateOrCreate([], $seo);

        $existingPlans = $product->pricingPlans()->with('features')->get()->keyBy('id');
        $retainedPlanIds = [];
        $existingPlansByPosition = $existingPlans->values();
        foreach ($data['pricing_plans'] ?? [] as $planIndex => $planData) {
            $planId = isset($planData['id']) ? (int) $planData['id'] : null;
            $plan = $planId ? $existingPlans->get($planId) : $existingPlansByPosition->get($planIndex);
            $features = collect(preg_split('/\R/', $planData['feature_list'] ?? ''))->map(fn ($feature) => trim($feature))->filter()->values();
            unset($planData['feature_list'], $planData['id']);
            if ($plan) {
                $plan->update($planData);
            } else {
                $plan = $product->pricingPlans()->create($planData);
            }
            $retainedPlanIds[] = $plan->id;
            $plan->features()->delete();
            $plan->features()->createMany($features->map(fn ($feature, $index) => ['feature' => $feature, 'sort_order' => $index])->all());
        }
        $product->pricingPlans()->whereNotIn('id', $retainedPlanIds)->delete();
    }

    private function syncGallery(Product $product, array $data, array &$newPaths, array &$oldPaths): void
    {
        $existing = $product->galleryImages()->get()->keyBy('id');
        foreach ($data['gallery_existing'] ?? [] as $id => $metadata) {
            $image = $existing->get((int) $id);
            if ($image) {
                $image->update(Arr::only($metadata, ['alt_text', 'sort_order']));
            }
        }

        foreach ($data['gallery_remove'] ?? [] as $id) {
            $image = $existing->get((int) $id);
            if ($image) {
                $oldPaths[] = $image->image_path;
                $image->delete();
            }
        }

        foreach ($data['gallery_replacements'] ?? [] as $id => $file) {
            $image = $existing->get((int) $id);
            if ($image && $file instanceof UploadedFile) {
                $path = $this->images->store($file, 'products/'.$product->id.'/gallery', 1920, 1440);
                $newPaths[] = $path;
                $oldPaths[] = $image->image_path;
                $image->update(['image_path' => $path]);
            }
        }

        foreach ($data['gallery_images'] ?? [] as $index => $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->images->store($file, 'products/'.$product->id.'/gallery', 1920, 1440);
                $newPaths[] = $path;
                $product->galleryImages()->create(['image_path' => $path, 'alt_text' => $product->name.' screenshot', 'sort_order' => $index]);
            }
        }
    }
}
