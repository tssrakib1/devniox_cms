<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;

class EnforceWebsiteStatus
{
    public function handle(Request $request, Closure $next)
    {
        $settings = app(SettingsService::class);
        $enabled = $settings->get('maintenance.enabled', false);
        $adminAllowed = $request->user()?->isAdmin() && $settings->get('maintenance.allow_admin', true);

        if ($enabled && ! $adminAllowed && ! $request->routeIs('login', 'logout')) {
            return response()->view('errors.503', [
                'message' => $settings->get('maintenance.message'),
                'estimatedReturn' => $settings->get('maintenance.estimated_return'),
            ], 503);
        }

        return $next($request);
    }
}
