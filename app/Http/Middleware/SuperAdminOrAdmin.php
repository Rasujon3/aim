<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminOrAdmin
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

        // Super Admin, Admin => allow
        if (in_array($user->role, ['super_admin', 'Admin', 'admin'])) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Access denied. Super Admin or Admin required.'
        ], 403);
    }
}
