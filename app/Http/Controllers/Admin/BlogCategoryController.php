<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function index(Request $r)
    {
        $this->authorize('viewAny', BlogCategory::class);

        return view('admin.blog-categories.index', ['categories' => BlogCategory::with(['parent'])->withCount('posts')->when($r->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))->when($r->trashed === '1', fn ($q) => $q->onlyTrashed())->orderBy('sort_order')->paginate(20)]);
    }

    public function create()
    {
        $this->authorize('create', BlogCategory::class);

        return view('admin.blog-categories.form', ['category' => new BlogCategory, 'parents' => BlogCategory::orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $this->authorize('create', BlogCategory::class);
        BlogCategory::create($this->data($r));

        return redirect()->route('admin.blog-categories.index');
    }

    public function edit(BlogCategory $blog_category)
    {
        $this->authorize('update', $blog_category);

        return view('admin.blog-categories.form', ['category' => $blog_category, 'parents' => BlogCategory::whereKeyNot($blog_category->id)->get()]);
    }

    public function update(Request $r, BlogCategory $blog_category)
    {
        $this->authorize('update', $blog_category);
        $blog_category->update($this->data($r, $blog_category->id));

        return back();
    }

    public function destroy(BlogCategory $blog_category)
    {
        $this->authorize('delete', $blog_category);
        abort_if($blog_category->posts()->exists(), 422);
        $blog_category->delete();

        return back();
    }

    public function restore(int $category)
    {
        $model = BlogCategory::onlyTrashed()->findOrFail($category);
        $this->authorize('restore', $model);
        $model->restore();

        return back();
    }

    private function data(Request $r, ?int $id = null): array
    {
        return $r->validate(['parent_id' => ['nullable', 'exists:blog_categories,id'], 'name' => ['required', 'max:120'], 'slug' => ['required', 'alpha_dash', 'max:140', 'unique:blog_categories,slug'.($id ? ",$id" : '')], 'description' => ['nullable', 'max:5000'], 'icon' => ['nullable', 'max:100'], 'sort_order' => ['required', 'integer', 'min:0'], 'status' => ['required', 'in:draft,published'], 'seo_title' => ['nullable', 'max:70'], 'seo_description' => ['nullable', 'max:160']]);
    }
}
