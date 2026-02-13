<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            // Log unauthorized access attempt
            Log::channel('security')->warning('Unauthorized admin access attempt', [
                'user_id' => auth()->id(),
                'email' => auth()->user()?->email,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
