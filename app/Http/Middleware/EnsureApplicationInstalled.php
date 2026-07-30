<?php

namespace App\Http\Middleware;

use App\Services\InstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('installer.enabled') || ! config('installer.enforce')) {
            return $next($request);
        }

        $installed = app(InstallationService::class)->isInstalled();
        if ($request->routeIs('install.*')) {
            return $installed ? redirect()->route('login') : $next($request);
        }

        return $installed ? $next($request) : redirect()->route('install.welcome');
    }
}
