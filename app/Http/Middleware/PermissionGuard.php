<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionGuard
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found.'
            ], 404);
        }

        $allowed = false;

        switch ($permission) {
            case 'view':
                if ($user->is_view_all || str()->contains($routeName, ['.list', '.view', '.show'])) {
                    $allowed = true;
                }
                break;

            case 'create':
                if ($user->is_create_all || str()->contains($routeName, '.store')) {
                    $allowed = true;
                }
                break;

            case 'edit':
                if ($user->is_edit_all || str()->contains($routeName, '.update')) {
                    $allowed = true;
                }
                break;

            case 'delete':
                if (str()->contains($routeName, '.delete')) {
                    if (!in_array($user->role, ['super_admin', 'Admin', 'admin'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You are not authorized to delete this resource.'
                        ], 403);
                    }
                    $allowed = true;
                }
                break;

            default:
                $allowed = true;
        }

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
