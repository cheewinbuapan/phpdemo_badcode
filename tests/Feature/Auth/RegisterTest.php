<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Authentication Tests - User Registration
 * 
 * OWASP Compliance Testing:
 * - A02:2025 - Cryptographic Failures (password hashing)
 * - A07:2025 - Identification and Authentication Failures
 */
class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can register with valid data
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'email' => 'newuser@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '0812345678',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertRedirect('/products');

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Verify user is authenticated
        $this->assertAuthenticated();
    }

    /**
     * Test password is hashed (A02:2025 - CRITICAL)
     * NEVER store plaintext passwords
     */
    public function test_password_is_hashed_not_plaintext(): void
    {
        $plainPassword = 'Password@123';

        $this->post('/register', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '0812345678',
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // Password must NOT be plaintext
        $this->assertNotEquals($plainPassword, $user->password);

        // Password must be hashed using Hash::check()
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    /**
     * Test strong password validation (A07:2025)
     * Minimum 8 characters with complexity requirements
     */
    public function test_weak_password_is_rejected(): void
    {
        // Too short
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '0812345678',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password');

        // No special characters or numbers
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '0812345678',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test password confirmation is required
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '0812345678',
            'password' => 'Password@123',
            'password_confirmation' => 'DifferentPassword@123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test email must be unique
     */
    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'email' => 'existing@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '0812345678',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test all required fields validation
     */
    public function test_all_fields_are_required(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'email',
            'first_name',
            'last_name',
            'phone',
            'password',
        ]);
    }

    /**
     * Test phone number format validation (Thai format)
     */
    public function test_phone_number_validation(): void
    {
        // Invalid format
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '123', // Too short
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    /**
     * Test registration rate limiting (A07 protection)
     */
    public function test_registration_is_rate_limited(): void
    {
        // Make 5 registration attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [
                'email' => "test{$i}@example.com",
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '0812345678',
                'password' => 'Password@123',
                'password_confirmation' => 'Password@123',
            ]);
        }

        // 6th attempt should be blocked
        $response = $this->post('/register', [
            'email' => 'test6@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '0812345678',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }
}
