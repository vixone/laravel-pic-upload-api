<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiGuest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user('sanctum')) {
            return response()->json(['message' => 'Already authenticated'], 403);
        }

        return $next($request);
    }
}
