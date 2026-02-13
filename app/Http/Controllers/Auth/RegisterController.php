<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration form
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Create user with hashed password
        $user = User::create([
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_admin' => false, // Default to regular user
        ]);

        // Mark email as verified for now (can add email verification later)
        $user->email_verified_at = now();
        $user->save();

        // Log the user in
        Auth::login($user);

        // Regenerate session to prevent fixation attacks
        $request->session()->regenerate();

        return redirect()->route('products.index')
            ->with('success', 'สมัครสมาชิกสำเร็จ! ยินดีต้อนรับ');
    }
}
