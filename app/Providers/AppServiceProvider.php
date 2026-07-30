<?php

namespace App\Providers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Lead;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use App\Services\CmsService;
use App\Services\PermissionResolver;
use App\Services\SettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(CmsService::class);
        $this->app->singleton(PermissionResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->isAdmin() ? $user->is_active : null;
        });
        Gate::define('access', fn (User $user, string $permission): bool => $user->hasPermission($permission));
        Paginator::useBootstrapFive();
        $settings = app(SettingsService::class)->all();
        $timezone = $settings['general.timezone'] ?? config('app.timezone');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
        if (filled($settings['mail.host'] ?? null)) {
            config([
                'mail.mailers.smtp.host' => $settings['mail.host'],
                'mail.mailers.smtp.port' => $settings['mail.port'] ?? 587,
                'mail.mailers.smtp.username' => $settings['mail.username'] ?? null,
                'mail.mailers.smtp.password' => $settings['mail.password'] ?? null,
                'mail.mailers.smtp.scheme' => ($settings['mail.encryption'] ?? null) === 'ssl' ? 'smtps' : null,
            ]);
        }
        View::composer(['layouts.app', 'pages.home', 'pages.about', 'pages.contact', 'leads.contact'], function ($view) {
            $view->with('siteSettings', app(SettingsService::class)->public());
            $view->with('socialLinks', app(SettingsService::class)->socialLinks());
            $view->with('headerNavigation', app(CmsService::class)->navigation('header'))->with('footerNavigation', app(CmsService::class)->navigation('footer'))->with('cmsFooter', app(CmsService::class)->footer());
        });
        View::composer('admin.*', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $data = $view->getData();
            $view->with('navbarNotifications', $data['notifications'] ?? auth()->user()->notifications()->latest()->limit(5)->get())
                ->with('unreadNotificationCount', $data['stats']['unread'] ?? auth()->user()->notifications()->unread()->count());
        });

        foreach ([Product::class, ProductCategory::class, Service::class, ServiceCategory::class, PortfolioProject::class, PortfolioCategory::class, BlogPost::class, BlogCategory::class, User::class] as $model) {
            $model::saved(function () {
                Cache::forget('home.featured-content.v1');
                Cache::forget('seo.sitemap.v1');
                Cache::forget('blog.rss.v1');
            });
            $model::deleted(function () {
                Cache::forget('home.featured-content.v1');
                Cache::forget('seo.sitemap.v1');
                Cache::forget('blog.rss.v1');
            });
            if (method_exists($model, 'restored')) {
                $model::restored(function () {
                    Cache::forget('home.featured-content.v1');
                    Cache::forget('seo.sitemap.v1');
                    Cache::forget('blog.rss.v1');
                });
            }
        }

        foreach ([BlogCategory::class, BlogTag::class] as $model) {
            $model::saved(fn () => Cache::forget('seo.sitemap.v1'));
            $model::deleted(fn () => Cache::forget('seo.sitemap.v1'));
            if (method_exists($model, 'restored')) {
                $model::restored(fn () => Cache::forget('seo.sitemap.v1'));
            }
        }

        foreach ([Product::class, Service::class, PortfolioProject::class, Lead::class, User::class, Setting::class] as $model) {
            $model::saved(fn () => Cache::forget('admin.dashboard.stats.v1'));
            $model::deleted(fn () => Cache::forget('admin.dashboard.stats.v1'));
            if (method_exists($model, 'restored')) {
                $model::restored(fn () => Cache::forget('admin.dashboard.stats.v1'));
            }
        }
    }
}
