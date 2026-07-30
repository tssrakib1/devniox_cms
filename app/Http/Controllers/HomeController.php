<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Platform;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Service;
use App\Services\CmsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CmsService $cms): View
    {
        $content = Cache::remember('home.featured-content.v2', now()->addMinutes(5), fn () => [
            'featuredProducts' => $this->showcase(Product::published()->whereHas('category', fn (Builder $query) => $query->active())->with(['category', 'modules', 'pricingPlans' => fn ($query) => $query->where('is_active', true)])),
            'featuredServices' => $this->showcase(Service::published()->whereHas('category', fn (Builder $query) => $query->published())->with(['category', 'benefits', 'processSteps', 'technologies', 'deliverables'])),
            'featuredPortfolio' => $this->showcase(PortfolioProject::published()->whereHas('category', fn (Builder $query) => $query->published())->with(['category', 'objectives', 'solutions', 'results', 'technologies'])),
            'latestPosts' => BlogPost::published()->whereHas('category', fn (Builder $query) => $query->published())->with(['category', 'author'])->latest('published_at')->limit(6)->get(),
            'featuredPosts' => BlogPost::published()->whereHas('category', fn (Builder $query) => $query->published())->featured()->with(['category', 'author'])->latest('published_at')->limit(3)->get(),
            'platforms' => Platform::active()->orderBy('display_order')->orderBy('name')->get(),
        ]);

        return view('pages.home', ['cmsPage' => $cms->page('home'), ...$content]);
    }

    private function showcase(Builder $query): Collection
    {
        $featured = (clone $query)->featured()->orderBy('display_order')->limit(6)->get();

        if ($featured->count() >= 3) {
            return $featured;
        }

        return $featured->concat(
            (clone $query)->whereNotIn('id', $featured->modelKeys())->orderBy('display_order')->limit(6 - $featured->count())->get()
        );
    }
}




