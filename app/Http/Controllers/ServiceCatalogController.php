<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function index(Request $r): View
    {
        $f = $r->validate(['category' => ['nullable', 'string', 'max:180'], 'search' => ['nullable', 'string', 'max:180']]);
        $services = Service::published()->whereHas('category', fn (Builder $q) => $q->published())->with(['category', 'benefits', 'processSteps', 'technologies', 'deliverables'])->when($f['category'] ?? null, fn (Builder $q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)->published()))->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('short_description', 'like', "%{$s}%")))->orderByDesc('is_featured')->orderBy('display_order')->latest('published_at')->paginate(12)->withQueryString();

        return view('services.index', ['services' => $services, 'categories' => ServiceCategory::published()->withCount(['services' => fn ($q) => $q->published()])->orderBy('sort_order')->get()]);
    }

    public function show(string $slug): View
    {
        $service = Service::published()->whereHas('category', fn (Builder $q) => $q->published())->where('slug', $slug)->with(['category', 'benefits', 'processSteps', 'features', 'technologies', 'deliverables', 'galleryImages', 'faqs', 'seo'])->firstOrFail();
        $related = Service::published()->whereHas('category', fn (Builder $q) => $q->published())->where('service_category_id', $service->service_category_id)->whereKeyNot($service->id)->orderByDesc('is_featured')->limit(3)->get();

        return view('services.show', compact('service', 'related'));
    }
}
