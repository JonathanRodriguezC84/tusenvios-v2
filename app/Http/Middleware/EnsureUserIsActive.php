<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->accountIsActive()) {
            if ($request->routeIs('billing.blocked') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('billing.blocked');
        }

        return $next($request);
    }
}