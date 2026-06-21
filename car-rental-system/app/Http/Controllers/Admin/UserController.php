<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LicenseVerifiedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = User::where('role', 'customer')->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('license_status')) {
            match ($request->license_status) {
                'verified' => $query->where('driver_license_verified', true),
                'pending' => $query->whereNotNull('driver_license_image')
                    ->where('driver_license_verified', false),
                'missing' => $query->whereNull('driver_license_image'),
                default => null,
            };
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(User $user): View
    {
        if ($user->role !== 'customer') {
            abort(403, 'Only customer accounts can be edited here.');
        }

        return view('admin.users.edit', compact('user'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'customer') {
            abort(403, 'Only customer accounts can be edited here.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'driver_license_number' => ['nullable', 'string', 'max:50'],
            'driver_license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'driver_license_verified' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('driver_license_image')) {
            if ($user->driver_license_image) {
                Storage::disk('public')->delete($user->driver_license_image);
            }
            $validated['driver_license_image'] = $request->file('driver_license_image')->store('licenses', 'public');
        }

        $validated['driver_license_verified'] = $request->boolean('driver_license_verified');

        $user->update($validated);

        Cache::flush();

        return redirect()
            ->route('admin.users.index')
            ->with('success', $user->name . "'s information has been updated.");
    }

    // ─── Verify License ───────────────────────────────────────────────────────

    public function verifyLicense(User $user): RedirectResponse
    {
        $user->driver_license_verified = true;
        $user->save();

        $user->notify(new LicenseVerifiedNotification());

        return redirect()
            ->back()
            ->with('success', $user->name . "'s driver's license has been verified.");
    }

    // ─── Show License Image ───────────────────────────────────────────────────

    public function showLicense(User $user): RedirectResponse
    {
        if (!$user->driver_license_image) {
            abort(404, 'No license image found.');
        }

        return redirect(
            Storage::disk('public')->url($user->driver_license_image)
        );
    }
}