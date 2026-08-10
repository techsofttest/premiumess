<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class SetSessionForAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();

        if (Str::contains($host, 'admin')) {
            config([
                'session.cookie' => env('ADMIN_SESSION_COOKIE', 'admin_session'),
                'session.domain' => env('ADMIN_SESSION_DOMAIN', $request->getHost()),
                'session.same_site' => env('ADMIN_SESSION_SAME_SITE', config('session.same_site')),
            ]);
        }

        return $next($request);
    }
}
