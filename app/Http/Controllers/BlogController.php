<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function index(Request $r)
    {
        $f = $r->validate(['search' => ['nullable', 'string', 'max:180'], 'category' => ['nullable', 'string'], 'tag' => ['nullable', 'string']]);
        $posts = BlogPost::published()->whereHas('category', fn (Builder $q) => $q->published())->with(['category', 'author', 'tags'])->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('title', 'like', "%$s%")->orWhere('summary', 'like', "%$s%")->orWhere('body', 'like', "%$s%")->orWhereHas('tags', fn ($q) => $q->where('name', 'like', "%$s%"))))->when($f['category'] ?? null, fn ($q, $s) => $q->whereHas('category', fn ($q) => $q->where('slug', $s)->published()))->when($f['tag'] ?? null, fn ($q, $s) => $q->whereHas('tags', fn ($q) => $q->where('slug', $s)))->orderByDesc('is_featured')->latest('published_at')->paginate(12)->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'popularPosts' => BlogPost::published()->whereHas('category', fn (Builder $q) => $q->published())->with(['category', 'author'])->orderByDesc('views_count')->latest('published_at')->limit(4)->get(),
            'categories' => BlogCategory::published()->withCount(['posts' => fn ($q) => $q->published()])->get(),
            'tags' => BlogTag::whereHas('posts', fn ($q) => $q->published()->whereHas('category', fn (Builder $q) => $q->published()))->withCount(['posts' => fn ($q) => $q->published()->whereHas('category', fn (Builder $q) => $q->published())])->get(),
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->whereHas('category', fn (Builder $q) => $q->published())->where('slug', $slug)->with(['category', 'author', 'tags', 'products', 'services', 'downloads', 'faqs', 'seo'])->firstOrFail();
        $post->increment('views_count');
        $related = BlogPost::published()->whereHas('category', fn (Builder $q) => $q->published())->with(['category', 'author'])->whereKeyNot($post->id)->where('blog_category_id', $post->blog_category_id)->limit(3)->get();

        return view('blog.show', compact('post', 'related'));
    }

    public function rss()
    {
        $posts = Cache::remember('blog.rss.v1', now()->addMinutes(10), fn () => BlogPost::published()->whereHas('category', fn (Builder $q) => $q->published())->with('author')->latest('published_at')->limit(30)->get());

        return response()->view('blog.rss', compact('posts'))->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
