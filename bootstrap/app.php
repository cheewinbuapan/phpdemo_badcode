<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register security headers middleware globally
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        // Register admin middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Log authorization failures for security monitoring (A09 OWASP)
        $exceptions->reportable(function (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Illuminate\Support\Facades\Log::channel('security')->warning('Authorization failed', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'policy' => $e->getMessage(),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        });
    })->create();
