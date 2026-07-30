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

        return view('admin.settings.system', ['system' => ['Application Version' => config('app.version', '1.0.0'), 'Laravel Version' => app()->version(), 'PHP Version' => PHP_VERSION, 'Environment' => app()->environment(), 'Database Driver' => config('database.default'), 'Storage Usage' => number_format($storageBytes / 1048576, 2).' MB', 'Cache Status' => $cacheOperational ? 'Operational ('.config('cache.default').')' : 'Unavailable', 'Configuration Cache' => app()->configurationIsCached() ? 'Cached' : 'Not cached', 'Route Cache' => app()->routesAreCached() ? 'Cached' : 'Not cached', 'Queue' => config('queue.default'), 'Debug' => config('app.debug') ? 'Enabled' : 'Disabled']]);
    }

    public function clear(Request $r)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $action = $r->validate(['action' => ['required', 'in:cache,config,route,view']])['action'];
        Artisan::call($action.':clear');

        return back()->with('success', ucfirst($action).' cache cleared.');
    }
}
