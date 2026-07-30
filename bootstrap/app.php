<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnforceWebsiteStatus;
use App\Http\Middleware\EnsureApplicationInstalled;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\PrepareInstallationRuntime;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependToGroup('web', PrepareInstallationRuntime::class);
        $middleware->appendToGroup('web', AddSecurityHeaders::class);
        $middleware->appendToGroup('web', EnforceWebsiteStatus::class);
        $middleware->appendToGroup('web', EnsureApplicationInstalled::class);
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => EnsureUserHasRole::class,
            'permission' => EnsureUserHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
