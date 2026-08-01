<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $cacheKey = 'system.health.probe';
        Cache::put($cacheKey, true, 30);
        $cacheOperational = Cache::get($cacheKey) === true;
        Cache::forget($cacheKey);
        $storageBytes = collect(['local', 'public'])->sum(function (string $disk): int {
            return collect(Storage::disk($disk)->allFiles())->sum(function (string $file) use ($disk): int {
                try {
                    return Storage::disk($disk)->size($file);
                } catch (\Throwable) {
                    return 0;
                }
            });
        });

        return view('admin.settings.system', ['system' => [
            'Application Version' => config('app.version', '1.0.0'),
            'Laravel Version' => app()->version(),
            'PHP Version' => PHP_VERSION,
            'Environment' => app()->environment(),
            'Database Driver' => config('database.default'),
            'Storage Usage' => number_format($storageBytes / 1048576, 2).' MB',
            'Cache Status' => $cacheOperational ? 'Operational ('.config('cache.default').')' : 'Unavailable',
            'Configuration Cache' => app()->configurationIsCached() ? 'Cached' : 'Not cached',
            'Route Cache' => app()->routesAreCached() ? 'Cached' : 'Not cached',
            'Queue' => config('queue.default'),
            'Debug' => config('app.debug') ? 'Enabled' : 'Disabled',
        ]]);
    }

    public function clear(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $action = $request->validate(['action' => ['required', 'in:cache,config,route,view,optimize']])['action'];

        try {
            match ($action) {
                'cache' => Artisan::call('cache:clear'),
                'config' => Artisan::call('config:clear'),
                'route' => Artisan::call('route:clear'),
                'view' => Artisan::call('view:clear'),
                'optimize' => Artisan::call('optimize'),
            };
        } catch (\Throwable $exception) {
            return back()->withErrors(['action' => 'System action failed: '.$exception->getMessage()]);
        }

        return back()->with('success', match ($action) {
            'cache' => 'Application cache cleared.',
            'config' => 'Configuration cache cleared.',
            'route' => 'Route cache cleared.',
            'view' => 'Compiled view cache cleared.',
            'optimize' => 'Application optimized successfully.',
        });
    }
}
