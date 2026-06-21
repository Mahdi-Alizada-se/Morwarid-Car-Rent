<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProfileChangeRequest;
use App\Services\ProfileChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileChangeService $profileChangeService,
    ) {
    }

    public function edit(): View
    {
        $user = auth()->user();

        $pendingRequest = ProfileChangeRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $lastReviewed = ProfileChangeRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->first();

        return view('customer.profile.edit', compact('user', 'pendingRequest', 'lastReviewed'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'driver_license_number' => ['required', 'string', 'max:50'],
            'driver_license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $licenseImage = $request->file('driver_license_image');

        $result = $this->profileChangeService->submitRequest($user, $validated, $licenseImage);

        if ($result === null) {
            return back()->with('error', __('profile.no_changes_detected'));
        }

        if ($result->wasRecentlyCreated === false) {
            return back()->with('error', __('profile.already_pending'));
        }

        return redirect()
            ->route('customer.profile.edit')
            ->with('success', __('profile.request_submitted'));
    }

    // ─── Update Password (instant, no admin approval) ───────────────────────

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:64',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'current_password.current_password' => __('profile.current_password_incorrect'),
            'password.regex' => __('profile.password_requirements'),
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('customer.profile.edit')
            ->with('success', __('profile.password_updated'));
    }
}