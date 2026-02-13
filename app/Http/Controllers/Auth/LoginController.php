<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login form
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Attempt authentication
        if (Auth::attempt($credentials, $remember)) {
            // Log successful login
            Log::channel('security')->info('User logged in', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Regenerate session to prevent fixation
            $request->session()->regenerate();

            // Redirect based on user role
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.orders.index');
            }

            return redirect()->intended(route('products.index'));
        }

        // Log failed login attempt
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Return with error - same message for both invalid email and password
        // to prevent username enumeration (A07 requirement)
        return back()->withErrors([
            'email' => 'รหัสผ่านหรืออีเมลไม่ถูกต้อง',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request
     */
    public function destroy(): RedirectResponse
    {
        Log::channel('security')->info('User logged out', [
            'user_id' => Auth::id(),
            'email' => Auth::user()?->email,
        ]);

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
