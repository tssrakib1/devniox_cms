<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\StoreDemoRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Service;
use App\Services\CmsService;
use App\Services\LeadManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadSubmissionController extends Controller
{
    public function contact(CmsService $cms): View
    {
        return view('leads.contact', ['cmsPage' => $cms->page('contact')]);
    }

    public function storeContact(StoreContactMessageRequest $r, LeadManager $m, CmsService $cms): RedirectResponse
    {
        $m->createContact($r->validated(), $r);
        $message = $cms->page('contact')->contact->success_message ?: 'Thank you. Your message has been received.';

        return back()->with('success', $message);
    }

    public function demo(Request $r, CmsService $cms): View
    {
        return view('leads.demo', $this->options($r) + ['cmsPage' => $cms->page('request-demo')]);
    }

    public function storeDemo(StoreDemoRequest $r, LeadManager $m, CmsService $cms): RedirectResponse
    {
        $m->createDemo($r->validated(), $r);

        return back()->with('success', $cms->page('request-demo')->simpleContent->success_message ?: 'Your demo request has been received.');
    }

    public function quote(Request $r, CmsService $cms): View
    {
        return view('leads.quote', $this->options($r) + ['cmsPage' => $cms->page('request-quote')]);
    }

    public function storeQuote(StoreQuoteRequest $r, LeadManager $m, CmsService $cms): RedirectResponse
    {
        $m->createQuote($r->validated(), $r);

        return back()->with('success', $cms->page('request-quote')->simpleContent->success_message ?: 'Your quote request has been received.');
    }

    private function options(Request $r): array
    {
        return ['products' => Product::published()->whereHas('category', fn (Builder $query) => $query->active())->orderBy('name')->get(['id', 'name']), 'services' => Service::published()->whereHas('category', fn (Builder $query) => $query->published())->orderBy('name')->get(['id', 'name']), 'portfolioProjects' => PortfolioProject::published()->whereHas('category', fn (Builder $query) => $query->published())->orderBy('name')->get(['id', 'name']), 'selectedType' => $r->string('type')->value(), 'selectedId' => $r->integer('item')];
    }
}
