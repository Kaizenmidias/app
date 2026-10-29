<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module)
    {
        abort_unless($request->user()?->canAccessModule($module), 403);

        return $next($request);
    }
}
