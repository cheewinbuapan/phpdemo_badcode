<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;

/**
 * Log failed login attempts for security monitoring
 * 
 * OWASP Compliance:
 * - A09:2025 - Security Logging and Monitoring Failures
 * - A07:2025 - Identification and Authentication Failures (detection)
 * 
 * Helps detect brute force attacks and unauthorized access attempts
 */
class LogFailedLogin
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
    public function handle(Failed $event): void
    {
        // Only log email if credentials were provided (don't log null)
        $email = $event->credentials['email'] ?? 'N/A';

        Log::channel('security')->warning('Failed login attempt', [
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'attempt_time' => now()->toDateTimeString(),
            // SECURITY: Never log password attempts
        ]);
    }
}
