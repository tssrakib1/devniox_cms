<?php

namespace App\Http\Controllers;

use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioCatalogController extends Controller
{
    public function index(Request $r): View
    {
        $f = $r->validate(['category' => ['nullable', 'string', 'max:180'], 'search' => ['nullable', 'string', 'max:180']]);
        $projects = PortfolioProject::published()->whereHas('category', fn (Builder $q) => $q->published())->with(['category', 'objectives', 'solutions', 'results', 'technologies'])->when($f['category'] ?? null, fn (Builder $q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)->published()))->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('short_description', 'like', "%{$s}%")->orWhere('industry', 'like', "%{$s}%")))->orderByDesc('is_featured')->orderBy('display_order')->latest('completion_date')->paginate(12)->withQueryString();

        return view('portfolio.index', ['projects' => $projects, 'categories' => PortfolioCategory::published()->withCount(['projects' => fn ($q) => $q->published()])->orderBy('sort_order')->get()]);
    }

    public function show(string $slug): View
    {
        $project = PortfolioProject::published()->whereHas('category', fn (Builder $q) => $q->published())->where('slug', $slug)->with(['category', 'objectives', 'solutions', 'features', 'galleryImages', 'technologies', 'links', 'results', 'faqs', 'seo'])->firstOrFail();
        $related = PortfolioProject::published()->whereHas('category', fn (Builder $q) => $q->published())->with(['category', 'objectives', 'solutions', 'results', 'technologies'])->where('portfolio_category_id', $project->portfolio_category_id)->whereKeyNot($project->id)->orderByDesc('is_featured')->limit(3)->get();

        return view('portfolio.show', compact('project', 'related'));
    }
}
