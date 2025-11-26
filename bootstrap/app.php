<?php

use App\Http\Middleware\AdminGuard;
use App\Http\Middleware\Cors;
use App\Http\Middleware\PermissionGuard;
use App\Http\Middleware\SuperAdminGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 🔥 Global CORS Enable (Allow Everything)
        $middleware->prepend(Cors::class);

        // অথবা শুধু API রুটে লাগাতে চাইলে:
//         $middleware->api(prepend: [Cors::class]);

        $middleware->alias([
            'super_admin' => SuperAdminGuard::class,
            'admin' => AdminGuard::class,
            'permission' => PermissionGuard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
