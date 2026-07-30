<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;

class EnforceWebsiteStatus
{
    public function handle(Request $r, Closure $next)
    {
        $s = app(SettingsService::class);
        $enabled = $s->get('maintenance.enabled', false) || $s->get('general.website_status') === 'maintenance';
        $admin = $r->user()?->isAdmin() && $s->get('maintenance.allow_admin', true);
        if ($enabled && ! $admin && ! $r->routeIs('login', 'logout')) {
            return response()->view('errors.503', ['message' => $s->get('maintenance.message')], 503);
        }

        return $next($r);
    }
}
