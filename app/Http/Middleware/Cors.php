<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // তোমার frontend origin (ডেভেলপমেন্ট + প্রোডাকশন)
        $allowedOrigins = [
            'http://localhost:3000',
            'https://aim.rit-global.com',     // যদি frontend একই ডোমেইনে থাকে
            // প্রোডাকশন frontend যোগ করো পরে
        ];

        $origin = $request->headers->get('Origin');

        // যদি origin allowed list এ থাকে, তাহলে সেট করো, নইলে *
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');

        // এটা খুব জরুরি — credentials allow করতে
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Preflight request
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
            return $response;
        }

        return $response;
    }
}
