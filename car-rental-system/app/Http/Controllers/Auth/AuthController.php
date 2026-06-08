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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'driver_license_number' => ['required', 'string', 'max:100'],
            'driver_license_image' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'customer',
            'driver_license_number' => $request->driver_license_number,
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
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

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