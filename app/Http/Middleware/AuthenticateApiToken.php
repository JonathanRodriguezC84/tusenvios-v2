<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'API token requerido.'], 401);
        }

        $tenant = Tenant::where('api_token', hash('sha256', $token))->first();

        if (!$tenant || $tenant->status !== 'active') {
            return response()->json(['message' => 'Token invalido o cuenta inactiva.'], 401);
        }

        $request->merge(['__tenant' => $tenant]);

        return $next($request);
    }
}
