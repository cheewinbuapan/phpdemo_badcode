<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

/**
 * Log successful login attempts for security audit trail
 * 
 * OWASP Compliance:
 * - A09:2025 - Security Logging and Monitoring Failures
 * 
 * Logs user_id, email, IP address, and user agent when authentication succeeds
 */
class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        Log::channel('security')->info('User logged in successfully', [
            'user_id' => $event->user->user_id,
            'email' => $event->user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_time' => now()->toDateTimeString(),
        ]);
    }
}
