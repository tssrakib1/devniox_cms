<?php

namespace App\Services;

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BlogManager
{
    public function __construct(private ManagedImageService $images) {}

    public function create(array $d, int $u): BlogPost
    {
        return $this->persist(new BlogPost, $d, $u);
    }

    public function update(BlogPost $p, array $d, int $u): BlogPost
    {
        return $this->persist($p, $d, $u);
    }

    private function persist(BlogPost $p, array $d, int $u): BlogPost
    {
        return DB::transaction(function () use ($p, $d, $u) {
            foreach (['featured_image' => ['featured_image_path', 1200, 675], 'social_image' => ['social_image_path', 1200, 630]] as $key => [$field,$w,$h]) {
                if (isset($d[$key])) {
                    $old = $p->{$field};
                    $d[$field] = $this->images->store($d[$key], 'blog/posts', $w, $h);
                    DB::afterCommit(fn () => $this->images->delete($old));
                }
            }if (isset($d['seo']['open_graph_image'])) {
                $old = $p->seo?->open_graph_image_path;
                $d['seo']['open_graph_image_path'] = $this->images->store($d['seo']['open_graph_image'], 'blog/seo', 1200, 630);
                DB::afterCommit(fn () => $this->images->delete($old));
            }$d['reading_time'] = max(1, (int) ceil(str_word_count(strip_tags($d['body'])) / 200));
            $d['updated_by'] = $u;
            $status = $d['status'] instanceof BlogStatus
                ? $d['status']
                : BlogStatus::from($d['status']);
            $d['status'] = $status;
            $publishedAt = filled($d['published_at'] ?? null) ? Carbon::parse($d['published_at']) : null;
            if ($status === BlogStatus::Published) {
                $d['published_at'] = (! $publishedAt || $publishedAt->isFuture()) ? now() : $publishedAt;
            } elseif ($status === BlogStatus::Scheduled) {
                $d['published_at'] = $publishedAt;
            } else {
                $d['published_at'] = null;
            }
            $p->fill(Arr::except($d, ['featured_image', 'social_image', 'tag_ids', 'product_ids', 'service_ids', 'faqs', 'seo', 'downloads', 'download_titles']))->save();
            $p->tags()->sync($d['tag_ids'] ?? []);
            $p->products()->sync($d['product_ids'] ?? []);
            $p->services()->sync($d['service_ids'] ?? []);
            $p->faqs()->delete();
            $p->faqs()->createMany($d['faqs'] ?? []);
            if (isset($d['seo'])) {
                $p->seo()->updateOrCreate([], Arr::except($d['seo'], 'open_graph_image'));
            }foreach ($d['downloads'] ?? [] as $i => $file) {
                $path = $file->store('blog-downloads', 'local');
                $originalName = mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())), 0, 255);
                $p->downloads()->create(['title' => $d['download_titles'][$i] ?? pathinfo($originalName, PATHINFO_FILENAME), 'file_path' => $path, 'original_name' => $originalName, 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize(), 'sort_order' => $i]);
            }

            return $p->fresh();
        });
    }

    public function status(BlogPost $p, BlogStatus $s, $at = null): void
    {
        $p->update(['status' => $s, 'published_at' => $s === BlogStatus::Published ? now() : ($s === BlogStatus::Scheduled ? $at : null)]);
    }

    public function feature(BlogPost $p, bool $v): void
    {
        $p->update(['is_featured' => $v]);
    }

    public function delete(BlogPost $p): void
    {
        $p->delete();
    }

    public function restore(BlogPost $p): void
    {
        $p->restore();
    }
}
