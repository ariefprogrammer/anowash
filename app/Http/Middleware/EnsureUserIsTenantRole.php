<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTenantRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->user()?->role, ['owner', 'admin_outlet'])) {
            abort(403);
        }

        return $next($request);
    }
}