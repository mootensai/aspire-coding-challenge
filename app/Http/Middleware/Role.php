<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class Role
{
    public function handle($request, Closure $next)
    {
        if (auth()->user() && auth()->user()->is_admin) {
            return $next($request);
        }
        else {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    }
}
