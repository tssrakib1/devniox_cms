<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\CmsFooterController;
use App\Http\Controllers\Admin\CmsNavigationController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\FinanceReportController;
use App\Http\Controllers\Admin\FinanceTransactionController;
use App\Http\Controllers\Admin\IncomeCategoryController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\PortfolioCategoryController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CmsPublicPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\LeadSubmissionController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PortfolioCatalogController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\ServiceCatalogController;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->name('install.')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
    Route::post('/requirements', [InstallerController::class, 'acceptRequirements'])->name('requirements.accept');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'storeDatabase'])->name('database.store');
    Route::get('/application', [InstallerController::class, 'application'])->name('application');
    Route::post('/application', [InstallerController::class, 'storeApplication'])->name('application.store');
    Route::get('/administrator', [InstallerController::class, 'administrator'])->name('administrator');
    Route::post('/administrator', [InstallerController::class, 'storeAdministrator'])->name('administrator.store');
    Route::get('/demo-content', [InstallerController::class, 'demo'])->name('demo');
    Route::post('/demo-content', [InstallerController::class, 'storeDemo'])->name('demo.store');
    Route::get('/install', [InstallerController::class, 'install'])->name('install');
    Route::post('/run', [InstallerController::class, 'run'])->middleware('throttle:2,10')->name('run');
});

Route::get('/', HomeController::class)->name('home');
Route::get('/about', [CmsPublicPageController::class, 'about'])->name('about');
Route::get('/products', [ProductCatalogController::class, 'index'])->name('products');
Route::get('/products/{slug}', [ProductCatalogController::class, 'show'])->name('products.show');
Route::get('/services', [ServiceCatalogController::class, 'index'])->name('services');
Route::get('/services/{slug}', [ServiceCatalogController::class, 'show'])->name('services.show');
Route::get('/portfolio', [PortfolioCatalogController::class, 'index'])->name('portfolio');
Route::get('/portfolio/{slug}', [PortfolioCatalogController::class, 'show'])->name('portfolio.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/feed.xml', [BlogController::class, 'rss'])->name('blog.rss');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contact', [LeadSubmissionController::class, 'contact'])->name('contact');
Route::post('/contact', [LeadSubmissionController::class, 'storeContact'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/request-demo', [LeadSubmissionController::class, 'demo'])->name('demo-request');
Route::post('/request-demo', [LeadSubmissionController::class, 'storeDemo'])->middleware('throttle:5,1')->name('demo-request.store');
Route::get('/request-quote', [LeadSubmissionController::class, 'quote'])->name('quote-request');
Route::post('/request-quote', [LeadSubmissionController::class, 'storeQuote'])->middleware('throttle:5,1')->name('quote-request.store');
Route::get('/sitemap.xml', function () {
    $entries = Cache::remember('seo.sitemap.v1', now()->addHour(), fn () => [
        'sitemapProducts' => Product::published()->whereHas('category', fn ($query) => $query->active())->select(['slug', 'updated_at'])->get(),
        'sitemapServices' => Service::published()->whereHas('category', fn ($query) => $query->published())->select(['slug', 'updated_at'])->get(),
        'sitemapPortfolio' => PortfolioProject::published()->whereHas('category', fn ($query) => $query->published())->select(['slug', 'updated_at'])->get(),
        'sitemapPosts' => BlogPost::published()->whereHas('category', fn ($query) => $query->published())->select(['slug', 'updated_at'])->get(),
        'sitemapBlogCategories' => BlogCategory::published()->select(['slug', 'updated_at'])->get(),
        'sitemapBlogTags' => BlogTag::whereHas('posts', fn ($query) => $query->published()->whereHas('category', fn ($category) => $category->published()))->select(['slug', 'updated_at'])->get(),
    ]);

    return response()->view('seo.sitemap', $entries)->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'update'])->middleware('throttle:6,1')->name('password.update');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/confirm-password', [PasswordController::class, 'confirm'])->name('password.confirm');
    Route::post('/confirm-password', [PasswordController::class, 'confirmed'])->middleware('throttle:6,1');
    Route::prefix('admin')->name('admin.')->middleware('active')->group(function () {
        Route::get('/', DashboardController::class)->middleware('permission:dashboard,view')->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'password'])->middleware('throttle:6,1')->name('profile.password');
        Route::get('/website/{page:key}/edit', [CmsPageController::class, 'edit'])->whereIn('page', ['home', 'about', 'contact'])->name('cms.pages.edit');
        Route::put('/website/{page:key}', [CmsPageController::class, 'update'])->whereIn('page', ['home', 'about', 'contact'])->name('cms.pages.update');
        Route::get('/website/navigation', [CmsNavigationController::class, 'edit'])->name('cms.navigation.edit');
        Route::put('/website/navigation', [CmsNavigationController::class, 'update'])->name('cms.navigation.update');
        Route::get('/website/footer', [CmsFooterController::class, 'edit'])->name('cms.footer.edit');
        Route::put('/website/footer', [CmsFooterController::class, 'update'])->name('cms.footer.update');
        Route::resource('platforms', PlatformController::class)->except('show')->middleware('permission:website-settings');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/orders/bulk', [OrderController::class, 'bulk'])->middleware('permission:orders,edit')->name('orders.bulk');
        Route::post('/orders/{order}/notes', [OrderController::class, 'note'])->middleware('permission:orders,edit')->name('orders.notes.store');
        Route::post('/orders/{order}/attachments', [OrderController::class, 'attachments'])->middleware('permission:orders,edit')->name('orders.attachments.store');
        Route::put('/orders/{order}/attachments/{attachment}', [OrderController::class, 'replaceAttachment'])->middleware('permission:orders,edit')->name('orders.attachments.replace');
        Route::delete('/orders/{order}/attachments/{attachment}', [OrderController::class, 'deleteAttachment'])->middleware('permission:orders,delete')->name('orders.attachments.destroy');
        Route::get('/orders/{order}/attachments/{attachment}/download', [OrderController::class, 'downloadAttachment'])->middleware('permission:orders,view')->name('orders.attachments.download');
        Route::resource('orders', OrderController::class)->middleware('permission:orders');
        Route::prefix('finance')->name('finance.')->middleware('permission:finance')->group(function () {
            Route::get('/reports', [FinanceReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export.csv', [FinanceReportController::class, 'csv'])->name('reports.csv');
            Route::get('/reports/export.pdf', [FinanceReportController::class, 'pdf'])->name('reports.pdf');
            Route::post('/transactions/bulk', [FinanceTransactionController::class, 'bulk'])->name('transactions.bulk');
            Route::post('/transactions/{transaction}/attachments', [FinanceTransactionController::class, 'attachments'])->name('transactions.attachments.store');
            Route::put('/transactions/{transaction}/attachments/{attachment}', [FinanceTransactionController::class, 'replaceAttachment'])->name('transactions.attachments.replace');
            Route::delete('/transactions/{transaction}/attachments/{attachment}', [FinanceTransactionController::class, 'deleteAttachment'])->name('transactions.attachments.destroy');
            Route::get('/transactions/{transaction}/attachments/{attachment}/download', [FinanceTransactionController::class, 'downloadAttachment'])->name('transactions.attachments.download');
            Route::resource('transactions', FinanceTransactionController::class);
            Route::resource('income-categories', IncomeCategoryController::class)->except('show');
            Route::resource('expense-categories', ExpenseCategoryController::class)->except('show');
        });
        Route::get('/media/picker', [MediaController::class, 'picker'])->name('media.picker');
        Route::post('/media/folders', [MediaController::class, 'storeFolder'])->name('media.folders.store');
        Route::put('/media/folders/{folder}', [MediaController::class, 'updateFolder'])->name('media.folders.update');
        Route::delete('/media/folders/{folder}', [MediaController::class, 'destroyFolder'])->name('media.folders.destroy');
        Route::put('/media/{media}/replace', [MediaController::class, 'replace'])->name('media.replace');
        Route::post('/media/{media}/reuse', [MediaController::class, 'reuse'])->name('media.reuse');
        Route::patch('/media/{media}/restore', [MediaController::class, 'restore'])->name('media.restore');
        Route::delete('/media/{media}/force', [MediaController::class, 'forceDelete'])->name('media.force-delete');
        Route::get('/media/{media}/preview', [MediaController::class, 'preview'])->name('media.preview');
        Route::get('/media/{media}/download', [MediaController::class, 'download'])->name('media.download');
        Route::resource('media', MediaController::class)->parameters(['media' => 'media'])->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::get('/contact-messages', [LeadController::class, 'contacts'])->middleware('permission:leads,view')->name('contact-messages.index');
        Route::get('/demo-requests', [LeadController::class, 'demos'])->middleware('permission:leads,view')->name('demo-requests.index');
        Route::get('/quote-requests', [LeadController::class, 'quotes'])->middleware('permission:leads,view')->name('quote-requests.index');
        Route::post('/leads/bulk', [LeadController::class, 'bulk'])->middleware('permission:leads,edit')->name('leads.bulk');
        Route::patch('/leads/{lead}/restore', [LeadController::class, 'restore'])->name('leads.restore');
        Route::post('/leads/{lead}/notes', [LeadController::class, 'note'])->middleware('permission:leads,edit')->name('leads.notes.store');
        Route::post('/leads/{lead}/replies', [LeadController::class, 'reply'])->middleware('permission:leads,edit')->name('leads.replies.store');
        Route::post('/leads/{lead}/attachments', [LeadController::class, 'storeAttachment'])->name('leads.attachments.store');
        Route::put('/leads/{lead}/attachments/{attachment}', [LeadController::class, 'replaceAttachment'])->name('leads.attachments.replace');
        Route::delete('/leads/{lead}/attachments/{attachment}', [LeadController::class, 'removeAttachment'])->name('leads.attachments.destroy');
        Route::get('/leads/{lead}/attachments/{attachment}/download', [LeadController::class, 'downloadAttachment'])->name('leads.attachments.download');
        Route::post('/leads/{lead}/convert-to-order', [LeadController::class, 'convert'])->middleware('permission:leads,edit')->name('leads.convert');
        Route::get('/leads/{lead}/attachment', [LeadController::class, 'attachment'])->name('leads.attachment');
        Route::resource('leads', LeadController::class)->only(['index', 'show', 'update', 'destroy'])->middleware('permission:leads');
        Route::post('/products/bulk', [ProductController::class, 'bulk'])->middleware('permission:products,edit')->name('products.bulk');
        Route::resource('products', ProductController::class)->except('show')->middleware('permission:products');
        Route::post('/services/bulk', [ServiceController::class, 'bulk'])->middleware('permission:services,edit')->name('services.bulk');
        Route::resource('services', ServiceController::class)->except('show')->middleware('permission:services');
        Route::post('/portfolio/bulk', [PortfolioController::class, 'bulk'])->middleware('permission:portfolio,edit')->name('portfolio.bulk');
        Route::patch('/portfolio/{project}/restore', [PortfolioController::class, 'restore'])->name('portfolio.restore');
        Route::resource('portfolio', PortfolioController::class)->parameters(['portfolio' => 'portfolio'])->middleware('permission:portfolio');
        Route::post('/blog/bulk', [BlogPostController::class, 'bulk'])->middleware('permission:blog,edit')->name('blog.bulk');
        Route::patch('/blog/{post}/restore', [BlogPostController::class, 'restore'])->name('blog.restore');
        Route::resource('blog', BlogPostController::class)->except('show')->parameters(['blog' => 'post'])->middleware('permission:blog');
        Route::resource('users', UserController::class)->except('show')->middleware('permission:users');
        Route::resource('roles', RoleController::class)->except('show')->middleware('permission:roles');
        Route::get('/permissions', PermissionController::class)->middleware('permission:roles,view')->name('permissions.index');
        Route::group([], function () {
            Route::resource('activity-logs', ActivityLogController::class)->only(['index', 'show']);
            Route::resource('product-categories', ProductCategoryController::class)->except('show')->parameters(['product-categories' => 'category']);
            Route::patch('/service-categories/{category}/restore', [ServiceCategoryController::class, 'restore'])->name('service-categories.restore');
            Route::resource('service-categories', ServiceCategoryController::class)->except('show')->parameters(['service-categories' => 'category']);
            Route::patch('/portfolio-categories/{category}/restore', [PortfolioCategoryController::class, 'restore'])->name('portfolio-categories.restore');
            Route::resource('portfolio-categories', PortfolioCategoryController::class)->except('show')->parameters(['portfolio-categories' => 'portfolio_category']);
            Route::patch('/blog-categories/{category}/restore', [BlogCategoryController::class, 'restore'])->name('blog-categories.restore');
            Route::resource('blog-categories', BlogCategoryController::class)->except('show');
            Route::post('/blog-tags/merge', [BlogTagController::class, 'merge'])->name('blog-tags.merge');
            Route::patch('/blog-tags/{tag}/restore', [BlogTagController::class, 'restore'])->name('blog-tags.restore');
            Route::resource('blog-tags', BlogTagController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('/settings', [SettingsController::class, 'edit'])->middleware('permission:website-settings,view')->name('settings.edit');
            Route::get('/settings/system', [SystemController::class, 'index'])->middleware('permission:website-settings,view')->name('settings.system');
            Route::post('/settings/cache', [SystemController::class, 'clear'])->middleware(['permission:website-settings,edit', 'throttle:10,1'])->name('settings.cache.clear');
            Route::get('/settings/{section}', [SettingsController::class, 'edit'])->middleware('permission:website-settings,view')->whereIn('section', ['general', 'branding', 'contact', 'social', 'seo', 'analytics', 'email', 'integrations', 'maintenance'])->name('settings.section');
            Route::put('/settings', [SettingsController::class, 'update'])->middleware(['permission:website-settings,edit', 'password.confirm', 'throttle:10,1'])->name('settings.update');
        });
    });
});


