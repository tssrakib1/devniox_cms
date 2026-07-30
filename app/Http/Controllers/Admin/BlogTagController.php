<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogTagController extends Controller
{
    public function index(Request $r)
    {
        $this->authorize('viewAny', BlogTag::class);

        return view('admin.blog-tags.index', ['tags' => BlogTag::withCount('posts')->when($r->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))->when($r->trashed === '1', fn ($q) => $q->onlyTrashed())->orderBy('name')->paginate(30)]);
    }

    public function store(Request $r)
    {
        $this->authorize('create', BlogTag::class);
        BlogTag::create($this->data($r));

        return back();
    }

    public function update(Request $r, BlogTag $blog_tag)
    {
        $this->authorize('update', $blog_tag);
        $blog_tag->update($this->data($r, $blog_tag->id));

        return back();
    }

    public function destroy(BlogTag $blog_tag)
    {
        $this->authorize('delete', $blog_tag);
        $blog_tag->delete();

        return back();
    }

    public function restore(int $tag)
    {
        $model = BlogTag::onlyTrashed()->findOrFail($tag);
        $this->authorize('restore', $model);
        $model->restore();

        return back();
    }

    public function merge(Request $r)
    {
        $this->authorize('merge', BlogTag::class);
        $d = $r->validate(['source_id' => ['required', 'different:target_id', 'exists:blog_tags,id'], 'target_id' => ['required', 'exists:blog_tags,id']]);
        DB::transaction(function () use ($d) {
            $s = BlogTag::findOrFail($d['source_id']);
            $t = BlogTag::findOrFail($d['target_id']);
            $t->posts()->syncWithoutDetaching($s->posts()->pluck('blog_posts.id'));
            $s->forceDelete();
        });

        return back()->with('success', 'Tags merged.');
    }

    private function data(Request $r, ?int $id = null): array
    {
        return $r->validate(['name' => ['required', 'max:100'], 'slug' => ['required', 'alpha_dash', 'max:120', 'unique:blog_tags,slug'.($id ? ",$id" : '')], 'description' => ['nullable', 'max:5000']]);
    }
}
