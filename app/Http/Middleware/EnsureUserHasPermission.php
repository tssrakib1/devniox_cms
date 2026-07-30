<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $module, ?string $action = null): Response
    {
        $action ??= $this->actionFor($request);
        abort_unless($request->user()?->hasPermission($module.'.'.$action), 403);

        return $next($request);
    }

    private function actionFor(Request $request): string
    {
        $method = class_basename((string) $request->route()?->getActionMethod());
        $name = (string) $request->route()?->getName();
        if (in_array($method, ['index', 'show', 'preview', 'download', 'picker', 'contacts', 'demos', 'quotes', 'csv', 'pdf'], true)) {
            return 'view';
        }
        if ($method === 'create' || ($method === 'store' && preg_match('/^admin\.[^.]+\.store$/', $name))) {
            return 'create';
        }
        if (in_array($method, ['destroy', 'forceDelete', 'deleteAttachment', 'removeAttachment'], true)) {
            return 'delete';
        }

        return 'edit';
    }
}
