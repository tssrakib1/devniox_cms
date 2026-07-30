<?php

namespace App\Http\Middleware;

use App\Services\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrepareInstallationRuntime
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('installer.enabled') && ! app(InstallationService::class)->isInstalled()) {
            config([
                'cache.default' => 'array',
                'queue.default' => 'sync',
                'session.driver' => 'file',
            ]);
        }

        return $next($request);
    }
}
