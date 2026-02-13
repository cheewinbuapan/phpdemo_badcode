<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

/**
 * Log successful logout actions for security audit trail
 * 
 * OWASP Compliance:
 * - A09:2025 - Security Logging and Monitoring Failures
 * 
 * Tracks user session termination for audit purposes
 */
class LogSuccessfulLogout
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
    public function handle(Logout $event): void
    {
        // event->user may be null if session was already invalidated
        if ($event->user) {
            Log::channel('security')->info('User logged out', [
                'user_id' => $event->user->user_id,
                'email' => $event->user->email,
                'ip_address' => request()->ip(),
                'logout_time' => now()->toDateTimeString(),
            ]);
        }
    }
}
