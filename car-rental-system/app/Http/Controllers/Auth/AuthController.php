<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AuthController extends Controller
{
    // ─── Register ─────────────────────────────────────────────────────────────

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:/^[\pL\s]+$/u', // letters (any language) and spaces only
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+?[0-9\s\-]{7,20}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:64',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', // at least 1 lowercase, 1 uppercase, 1 digit
            ],
            'driver_license_number' => [
                'required',
                'string',
                'min:4',
                'max:30',
                'regex:/^[A-Za-z0-9\-]+$/', // letters, numbers, hyphens only
            ],
            'driver_license_image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'avatar' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
        ], [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'name.min' => 'The name field must be at least 3 characters.',
            'name.max' => 'The name field may not be greater than 32 characters.',
            'phone.regex' => 'Please enter a valid phone number.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'driver_license_number.regex' => 'The license number may only contain letters, numbers, and hyphens.',
        ]);

        // Store the license image
        $licensePath = $request->file('driver_license_image')
            ->store('licenses/' . date('Y/m'), 'public');

        // Store avatar if uploaded
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'customer',
            'driver_license_number' => strtoupper(trim($validated['driver_license_number'])),
            'driver_license_image' => $licensePath,
            'avatar' => $avatarPath,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'Welcome! Your account has been created.');
    }

    // ─── Login ─────────────────────────────────────────────────────────────────

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->recordFailedAttempt();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        $request->clearRateLimit();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ─── Logout ────────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        // Clear chatbot history from cache
        $sessionId = $request->cookie('chatbot_session_id')
            ?? $request->input('chatbot_session_id');

        if ($sessionId) {
            \Illuminate\Support\Facades\Cache::forget('chatbot:' . $sessionId);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}