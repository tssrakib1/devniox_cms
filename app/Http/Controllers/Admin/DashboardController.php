<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Services\FinanceDashboardService;
use App\Services\MediaLibraryService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke(FinanceDashboardService $finance, MediaLibraryService $media)
    {
        $stats = Cache::remember('admin.dashboard.stats.v2', now()->addMinute(), function () {
            $products = Product::query()->selectRaw("COUNT(*) total, SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) published, SUM(CASE WHEN is_featured=1 THEN 1 ELSE 0 END) featured")->first();
            $services = Service::query()->selectRaw("COUNT(*) total, SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) published, SUM(CASE WHEN is_featured=1 THEN 1 ELSE 0 END) featured")->first();
            $portfolio = PortfolioProject::query()->selectRaw("COUNT(*) total, SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) published, SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) draft, SUM(CASE WHEN is_featured=1 THEN 1 ELSE 0 END) featured")->first();
            $leads = Lead::query()->selectRaw("COUNT(*) total, SUM(CASE WHEN type='contact' THEN 1 ELSE 0 END) contacts, SUM(CASE WHEN type='demo' THEN 1 ELSE 0 END) demos, SUM(CASE WHEN type='quote' THEN 1 ELSE 0 END) quotes, SUM(CASE WHEN status='new' THEN 1 ELSE 0 END) unread")->first();
            $orders = Order::whereNull('archived_at')->selectRaw("SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending, SUM(CASE WHEN status NOT IN ('pending','completed','cancelled') THEN 1 ELSE 0 END) active, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed, SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled")->first();

            return ['pending_orders' => (int) $orders->pending, 'active_orders' => (int) $orders->active, 'completed_orders' => (int) $orders->completed, 'cancelled_orders' => (int) $orders->cancelled, 'users' => User::count(), 'active_users' => User::where('is_active', true)->count(), 'settings' => Setting::count(), 'products' => (int) $products->total, 'published_products' => (int) $products->published, 'featured_products' => (int) $products->featured, 'services' => (int) $services->total, 'published_services' => (int) $services->published, 'featured_services' => (int) $services->featured, 'portfolio_projects' => (int) $portfolio->total, 'published_portfolio_projects' => (int) $portfolio->published, 'draft_portfolio_projects' => (int) $portfolio->draft, 'featured_portfolio_projects' => (int) $portfolio->featured, 'new_leads_today' => Lead::whereDate('submitted_at', today())->count(), 'total_leads' => (int) $leads->total, 'contact_messages' => (int) $leads->contacts, 'demo_requests' => (int) $leads->demos, 'quote_requests' => (int) $leads->quotes, 'unread_leads' => (int) $leads->unread];
        });
        $stats += ['unread_messages' => Lead::where('type', 'contact')->whereNull('read_at')->count(), 'pending_demo_requests' => Lead::where('type', 'demo')->whereIn('status', ['new', 'pending'])->count(), 'pending_quote_requests' => Lead::where('type', 'quote')->whereIn('status', ['new', 'pending'])->count(), 'communications_today' => Lead::whereDate('submitted_at', today())->count()];
        $stats['unread'] = auth()->user()->notifications()->unread()->count();
        $stats += $finance->stats();
        $stats += collect($media->stats())->mapWithKeys(fn ($value, $key) => ['media_'.$key => $value])->all();

        return view('admin.dashboard', ['stats' => $stats, 'recentUsers' => User::latest()->limit(5)->get(), 'recentLeads' => Lead::with(['contactMessage', 'demoRequest', 'quoteRequest'])->latest('submitted_at')->limit(6)->get(), 'notifications' => auth()->user()->notifications()->latest()->limit(5)->get()]);
    }
}
