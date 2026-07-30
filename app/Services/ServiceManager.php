<?php

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ServiceManager
{
    public function __construct(private readonly ManagedImageService $images) {}

    public function create(array $data, int $userId): Service
    {
        return $this->persist(new Service, $data, $userId);
    }

    public function update(Service $service, array $data, int $userId): Service
    {
        return $this->persist($service, $data, $userId);
    }

    private function persist(Service $service, array $data, int $userId): Service
    {
        $newPaths = [];
        $oldPaths = [];
        try {
            $result = DB::transaction(function () use ($service, $data, $userId, &$newPaths, &$oldPaths) {
                $wasPublished = $service->exists && $service->status === ServiceStatus::Published;
                $attributes = Arr::only($data, ['service_category_id', 'name', 'slug', 'status', 'is_featured', 'display_order', 'short_description', 'full_description']);
                $attributes['updated_by'] = $userId;
                $attributes['created_by'] = $service->exists ? $service->created_by : $userId;
                $attributes['published_at'] = $attributes['status'] === ServiceStatus::Published->value ? ($wasPublished ? $service->published_at : now()) : null;
                $service->fill($attributes)->save();
                foreach (['cover_image' => [1600, 1000], 'featured_image' => [1200, 800]] as $field => [$width,$height]) {
                    if (($data[$field] ?? null)instanceof UploadedFile) {
                        $column = $field.'_path';
                        $path = $this->images->store($data[$field], 'services/'.$service->id.'/branding', $width, $height);
                        $newPaths[] = $path;
                        $oldPaths[] = $service->{$column};
                        $service->update([$column => $path]);
                    }
                }foreach (['benefits', 'processSteps', 'features', 'deliverables', 'faqs'] as $relation) {
                    $key = $relation === 'processSteps' ? 'process_steps' : $relation;
                    $service->{$relation}()->delete();
                    $service->{$relation}()->createMany(array_values($data[$key] ?? []));
                }$this->syncTechnologies($service, $data, $newPaths, $oldPaths);
                $this->syncGallery($service, $data, $newPaths, $oldPaths);
                $seo = $data['seo'] ?? [];
                if (($seo['open_graph_image'] ?? null)instanceof UploadedFile) {
                    $path = $this->images->store($seo['open_graph_image'], 'services/'.$service->id.'/seo', 1200, 630);
                    $newPaths[] = $path;
                    $oldPaths[] = $service->seo?->open_graph_image_path;
                    $seo['open_graph_image_path'] = $path;
                    unset($seo['open_graph_image']);
                }$service->seo()->updateOrCreate([], $seo);

                return $service->fresh();
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $this->images->delete($path);
            }throw $exception;
        }foreach (array_filter($oldPaths) as $path) {
            $this->images->delete($path);
        }

        return $result;
    }

    private function syncTechnologies(Service $service, array $data, array &$newPaths, array &$oldPaths): void
    {
        $existing = $service->technologies()->orderBy('sort_order')->orderBy('id')->get()->values();
        $retained = [];
        $service->technologies()->delete();
        foreach (array_values($data['technologies'] ?? []) as $index => $technology) {
            $file = $data['technology_images'][$index] ?? null;
            $old = $existing->get($index)?->image_path;
            if ($file instanceof UploadedFile) {
                $technology['image_path'] = $this->images->store($file, 'services/'.$service->id.'/technologies', 400, 400);
                $newPaths[] = $technology['image_path'];
            } elseif ($old) {
                $technology['image_path'] = $old;
                $retained[] = $old;
            }$service->technologies()->create($technology);
        }$oldPaths = array_merge($oldPaths, $existing->pluck('image_path')->filter()->diff($retained)->all());
    }

    private function syncGallery(Service $service, array $data, array &$newPaths, array &$oldPaths): void
    {
        $existing = $service->galleryImages()->get()->keyBy('id');
        foreach ($data['gallery_existing'] ?? [] as $id => $metadata) {
            if ($image = $existing->get((int) $id)) {
                $image->update(Arr::only($metadata, ['alt_text', 'sort_order']));
            }
        }foreach ($data['gallery_remove'] ?? [] as $id) {
            if ($image = $existing->get((int) $id)) {
                $oldPaths[] = $image->image_path;
                $image->delete();
            }
        }foreach ($data['gallery_replacements'] ?? [] as $id => $file) {
            if (($image = $existing->get((int) $id)) && $file instanceof UploadedFile) {
                $path = $this->images->store($file, 'services/'.$service->id.'/gallery', 1920, 1440);
                $newPaths[] = $path;
                $oldPaths[] = $image->image_path;
                $image->update(['image_path' => $path]);
            }
        }foreach ($data['gallery_images'] ?? [] as $index => $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->images->store($file, 'services/'.$service->id.'/gallery', 1920, 1440);
                $newPaths[] = $path;
                $service->galleryImages()->create(['image_path' => $path, 'alt_text' => $service->name.' project image', 'sort_order' => $index]);
            }
        }
    }
}
