<?php
namespace Cyberland\GlobalAudit\Http\Middleware;

use Closure;
use Cyberland\GlobalAudit\Facades\GlobalAudit;
use Illuminate\Http\Request;

class GlobalAuditRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! config('global-audit.middleware.enabled', false)) {
            return $response;
        }

        $only = config('global-audit.middleware.only', []);
        $except = config('global-audit.middleware.except', []);

        if ($only && ! $request->is($only)) {
            return $response;
        }

        if ($except && $request->is($except)) {
            return $response;
        }

        $route = $request->route();
        $action = $route ? $route->getActionName() : null;

        GlobalAudit::log($action, [
            'status' => $response->getStatusCode(),
            ...$route->getAction()
        ]);

        return $response;
    }
}
