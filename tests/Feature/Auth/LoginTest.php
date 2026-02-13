<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Authentication Tests - Login Functionality
 * 
 * OWASP Compliance Testing:
 * - A07:2025 - Identification and Authentication Failures
 * - A09:2025 - Security Logging and Monitoring Failures
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertRedirect('/products');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test failed login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test rate limiting on login attempts (A07 protection)
     * Maximum 5 attempts per minute
     */
    public function test_login_rate_limiting_blocks_excessive_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        // Clear any existing rate limits
        RateLimiter::clear('login:test@example.com');

        // Make 5 failed attempts (should succeed)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt should be blocked by rate limiter
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /**
     * Test session regeneration after successful login (session fixation protection)
     */
    public function test_session_regenerates_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        // Start a session
        $this->get('/login');
        $sessionId = session()->getId();

        // Login
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        // Session ID should have changed
        $this->assertNotEquals($sessionId, session()->getId());
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test validation requires email and password
     */
    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Test login form displays correctly
     */
    public function test_login_form_displays(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test authenticated user is redirected from login page
     */
    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/products');
    }
}
