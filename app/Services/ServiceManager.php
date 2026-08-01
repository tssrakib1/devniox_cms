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
                $status = $attributes['status'] instanceof ServiceStatus
                    ? $attributes['status']
                    : ServiceStatus::from($attributes['status']);
                $attributes['status'] = $status;
                $attributes['published_at'] = $status === ServiceStatus::Published ? ($wasPublished ? $service->published_at : now()) : null;
                $service->fill($attributes)->save();

                foreach (['cover_image' => [1600, 1000], 'featured_image' => [1200, 800]] as $field => [$width, $height]) {
                    if (($data[$field] ?? null) instanceof UploadedFile) {
                        $column = $field.'_path';
                        $path = $this->images->store($data[$field], 'services/'.$service->id.'/branding', $width, $height);
                        $newPaths[] = $path;
                        $oldPaths[] = $service->{$column};
                        $service->update([$column => $path]);
                    }
                }

                $seo = $data['seo'] ?? [];
                if (($seo['open_graph_image'] ?? null) instanceof UploadedFile) {
                    $path = $this->images->store($seo['open_graph_image'], 'services/'.$service->id.'/seo', 1200, 630);
                    $newPaths[] = $path;
                    $oldPaths[] = $service->seo?->open_graph_image_path;
                    $seo['open_graph_image_path'] = $path;
                    unset($seo['open_graph_image']);
                }
                $service->seo()->updateOrCreate([], $seo);

                return $service->fresh();
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
}

