<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceptionistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // from Sanctum token

        if (!$user || $user->status !== 'Active' || $user->user_type_id != 4 || $user->role !== 'receptionist') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Receptionist can access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
