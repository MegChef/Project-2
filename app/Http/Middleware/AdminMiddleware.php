<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Admins only'
            ], 403);
        }

        return $next($request);
    }
}
