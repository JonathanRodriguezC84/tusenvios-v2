<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->bearerToken() ?: $request->header('X-API-Key');
        $validKey = config('services.carrier_api.key');

        if (! $validKey || ! $apiKey || ! hash_equals($validKey, $apiKey)) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        return $next($request);
    }
}
