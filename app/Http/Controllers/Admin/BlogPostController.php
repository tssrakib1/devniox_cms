<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BlogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkBlogPostRequest;
use App\Http\Requests\Admin\StoreBlogPostRequest;
use App\Http\Requests\Admin\UpdateBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\BlogManager;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(Request $r)
    {
        $this->authorize('viewAny', BlogPost::class);
        $posts = BlogPost::with(['category', 'author'])->when($r->search, fn ($q, $s) => $q->where('title', 'like', "%$s%"))->when($r->status, fn ($q, $s) => $q->where('status', $s))->when($r->category, fn ($q, $s) => $q->where('blog_category_id', $s))->when($r->featured !== null && $r->featured !== '', fn ($q) => $q->where('is_featured', (bool) $r->featured))->when($r->trashed === '1', fn ($q) => $q->onlyTrashed())->latest('updated_at')->paginate(20)->withQueryString();

        return view('admin.blog.index', ['posts' => $posts, 'categories' => BlogCategory::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.blog.form', $this->data(new BlogPost));
    }

    public function store(StoreBlogPostRequest $r, BlogManager $m)
    {
        $d = $r->validated();
        if (! $r->user()->isAdmin()) {
            $d['is_featured'] = false;
        }$p = $m->create($d, $r->user()->id);
        ActivityLogService::log('blog', 'created', "Blog post {$p->title} created.", $p, null, $p->only(['title', 'slug', 'status']));

        return redirect()->route('admin.blog.edit', $p)->with('success', 'Post created.');
    }

    public function edit(BlogPost $post)
    {
        $this->authorize('update', $post);
        $post->load(['tags', 'products', 'services', 'faqs', 'downloads', 'seo']);

        return view('admin.blog.form', $this->data($post));
    }

    public function update(UpdateBlogPostRequest $r, BlogPost $post, BlogManager $m)
    {
        $d = $r->validated();
        if (! $r->user()->isAdmin()) {
            $d['is_featured'] = $post->is_featured;
        }$old = $post->only(['title', 'slug', 'published_at']) + ['status' => $post->status->value];
        $m->update($post, $d, $r->user()->id);
        $fresh = $post->fresh();
        ActivityLogService::log('blog', 'updated', "Blog post {$fresh->title} updated.", $fresh, $old, $fresh->only(array_keys($old)));
        if ($old['status'] !== $fresh->status->value) {
            ActivityLogService::log('blog', $fresh->status->value === 'scheduled' ? 'scheduled' : ($fresh->status->value === 'published' ? 'published' : $fresh->status->value), "Blog post {$fresh->title} status changed to {$fresh->status->value}.", $fresh, ['status' => $old['status']], ['status' => $fresh->status->value]);
        }

        return back()->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $post, BlogManager $m)
    {
        $this->authorize('delete', $post);
        ActivityLogService::log('blog', 'deleted', "Blog post {$post->title} deleted.", $post, $post->only(['title', 'slug', 'status']));
        $m->delete($post);

        return back()->with('success', 'Post deleted.');
    }

    public function restore(int $post, BlogManager $m)
    {
        $p = BlogPost::onlyTrashed()->findOrFail($post);
        $this->authorize('restore', $p);
        $m->restore($p);
        ActivityLogService::log('blog', 'restored', "Blog post {$p->title} restored.", $p);

        return back();
    }

    public function bulk(BulkBlogPostRequest $r, BlogManager $m)
    {
        $action = $r->validated('action');
        $posts = BlogPost::query()->when($action === 'restore', fn ($q) => $q->onlyTrashed())->whereKey($r->validated('post_ids'))->get();
        foreach ($posts as $p) {
            $before = $p->only(['status', 'is_featured', 'published_at']);
            if (in_array($action, ['publish', 'draft', 'schedule', 'archive'])) {
                $this->authorize('publish', $p);
                $m->status($p, BlogStatus::from($action === 'schedule' ? 'scheduled' : ($action === 'publish' ? 'published' : $action)), $r->date('published_at'));
            } elseif (in_array($action, ['feature', 'unfeature'])) {
                $this->authorize('feature', $p);
                $m->feature($p, $action === 'feature');
            } elseif ($action === 'restore') {
                $this->authorize('restore', $p);
                $m->restore($p);
            } else {
                $this->authorize('delete', $p);
                $m->delete($p);
            }
            $loggedAction = match ($action) {
                'publish' => 'published', 'schedule' => 'scheduled', 'draft' => 'drafted',
                'archive' => 'archived', 'feature' => 'featured', 'unfeature' => 'unfeatured',
                'restore' => 'restored', default => 'deleted',
            };
            ActivityLogService::log('blog', $loggedAction, "Bulk action {$action} applied to blog post {$p->title}.", $p, $before, $p->fresh()?->only(['status', 'is_featured', 'published_at']));
        }

        return back()->with('success', 'Bulk action completed.');
    }

    private function data(BlogPost $post): array
    {
        return compact('post') + ['categories' => BlogCategory::published()->get(), 'tags' => BlogTag::orderBy('name')->get(), 'authors' => User::where('is_active', true)->get(), 'products' => Product::published()->get(), 'services' => Service::published()->get(), 'statuses' => BlogStatus::cases()];
    }
}
