<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminGuard
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }

        if ($user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Super Admin privileges required.'
            ], 403);
        }

        return $next($request);
    }
}
