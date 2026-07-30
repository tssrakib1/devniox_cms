<?php

namespace App\Services;

use App\Enums\PortfolioStatus;
use App\Models\PortfolioProject;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class PortfolioManager
{
    public function __construct(private readonly ManagedImageService $images) {}

    public function create(array $d, int $user): PortfolioProject
    {
        return $this->persist(new PortfolioProject, $d, $user);
    }

    public function update(PortfolioProject $p, array $d, int $user): PortfolioProject
    {
        return $this->persist($p, $d, $user);
    }

    public function setStatus(PortfolioProject $p, PortfolioStatus $status, int $user): void
    {
        $p->update(['status' => $status, 'published_at' => $status === PortfolioStatus::Published ? ($p->published_at ?? now()) : null, 'updated_by' => $user]);
    }

    public function feature(PortfolioProject $p, bool $value, int $user): void
    {
        $p->update(['is_featured' => $value, 'updated_by' => $user]);
    }

    public function delete(PortfolioProject $p): void
    {
        $p->delete();
    }

    public function restore(PortfolioProject $p): void
    {
        $p->restore();
    }

    private function persist(PortfolioProject $p, array $d, int $user): PortfolioProject
    {
        $new = [];
        $old = [];
        try {
            $result = DB::transaction(function () use ($p, $d, $user, &$new, &$old) {
                $was = $p->exists && $p->status === PortfolioStatus::Published;
                $a = Arr::only($d, ['portfolio_category_id', 'name', 'slug', 'client_name', 'industry', 'completion_date', 'status', 'is_featured', 'display_order', 'short_description', 'full_description']);
                $a['created_by'] = $p->exists ? $p->created_by : $user;
                $a['updated_by'] = $user;
                $a['published_at'] = $a['status'] === PortfolioStatus::Published->value ? ($was ? $p->published_at : now()) : null;
                $p->fill($a)->save();
                foreach (['thumbnail' => [800, 600], 'cover_image' => [1600, 1000]] as $f => [$w,$h]) {
                    if (($d[$f] ?? null)instanceof UploadedFile) {
                        $column = $f.'_path';
                        $path = $this->images->store($d[$f], 'portfolio/'.$p->id.'/branding', $w, $h);
                        $new[] = $path;
                        $old[] = $p->{$column};
                        $p->update([$column => $path]);
                    }
                }foreach (['objectives', 'solutions', 'features', 'technologies', 'results', 'faqs'] as $r) {
                    $p->{$r}()->delete();
                    $p->{$r}()->createMany(array_values($d[$r] ?? []));
                }$p->links()->updateOrCreate([], Arr::only($d['links'] ?? [], ['live_url', 'demo_url', 'github_url', 'documentation_url']));
                $this->gallery($p, $d, $new, $old);
                $seo = $d['seo'] ?? [];
                if (($seo['open_graph_image'] ?? null)instanceof UploadedFile) {
                    $path = $this->images->store($seo['open_graph_image'], 'portfolio/'.$p->id.'/seo', 1200, 630);
                    $new[] = $path;
                    $old[] = $p->seo?->open_graph_image_path;
                    $seo['open_graph_image_path'] = $path;
                    unset($seo['open_graph_image']);
                }$p->seo()->updateOrCreate([], $seo);

                return $p->fresh();
            });
        } catch (Throwable $e) {
            foreach ($new as $path) {
                $this->images->delete($path);
            }throw $e;
        }foreach (array_filter($old) as $path) {
            $this->images->delete($path);
        }

        return $result;
    }

    private function gallery(PortfolioProject $p, array $d, array &$new, array &$old): void
    {
        $existing = $p->galleryImages()->get()->keyBy('id');
        foreach ($d['gallery_existing'] ?? [] as $id => $meta) {
            if ($image = $existing->get((int) $id)) {
                $image->update(Arr::only($meta, ['alt_text', 'sort_order']));
            }
        }foreach ($d['gallery_remove'] ?? [] as $id) {
            if ($image = $existing->get((int) $id)) {
                $old[] = $image->image_path;
                $image->delete();
            }
        }foreach ($d['gallery_replacements'] ?? [] as $id => $file) {
            if (($image = $existing->get((int) $id)) && $file instanceof UploadedFile) {
                $path = $this->images->store($file, 'portfolio/'.$p->id.'/gallery', 1920, 1440);
                $new[] = $path;
                $old[] = $image->image_path;
                $image->update(['image_path' => $path]);
            }
        }foreach ($d['gallery_images'] ?? [] as $i => $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->images->store($file, 'portfolio/'.$p->id.'/gallery', 1920, 1440);
                $new[] = $path;
                $p->galleryImages()->create(['image_path' => $path, 'alt_text' => $p->name.' project image', 'sort_order' => $i]);
            }
        }
    }
}
