<?php

namespace App\Services;

use App\Models\ProfileChangeRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileChangeService
{
    private const TRACKED_FIELDS = ['name', 'email', 'phone', 'driver_license_number'];

    public function __construct(
        private NotificationService $notifications,
    ) {
    }

    /**
     * Submit a new change request. Returns null if nothing actually changed.
     */
    public function submitRequest(User $user, array $data, ?UploadedFile $licenseImage = null): ?ProfileChangeRequest
    {
        // Reject if there's already a pending request
        $existing = ProfileChangeRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        $changes = [];
        $oldValues = [];

        foreach (self::TRACKED_FIELDS as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== $user->{$field}) {
                $changes[$field] = $data[$field];
                $oldValues[$field] = $user->{$field};
            }
        }

        $licenseImagePath = null;
        if ($licenseImage) {
            $licenseImagePath = $licenseImage->store('license-change-requests', 'public');
            $changes['driver_license_image'] = $licenseImagePath;
            $oldValues['driver_license_image'] = $user->driver_license_image;
        }

        if (empty($changes)) {
            return null;
        }

        return ProfileChangeRequest::create([
            'user_id' => $user->id,
            'changes' => $changes,
            'old_values' => $oldValues,
            'license_image_path' => $licenseImagePath,
            'status' => 'pending',
        ]);
    }

    /**
     * Approve a request and apply changes to the user.
     */
    public function approve(ProfileChangeRequest $request, User $admin, ?string $notes = null): void
    {
        $user = $request->user;
        $changes = $request->changes;

        $updateData = [];
        foreach ($changes as $field => $value) {
            if ($field === 'driver_license_image') {
                // Delete old license image, apply new one, reset verification
                if ($user->driver_license_image) {
                    Storage::disk('public')->delete($user->driver_license_image);
                }
                $updateData['driver_license_image'] = $value;
                $updateData['driver_license_verified'] = false;
            } elseif ($field === 'driver_license_number') {
                $updateData['driver_license_number'] = $value;
                $updateData['driver_license_verified'] = false;
            } else {
                $updateData[$field] = $value;
            }
        }

        $user->update($updateData);

        $request->update([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        $this->notifications->profileChangeApproved($user, $request);
    }

    /**
     * Reject a request without applying changes.
     */
    public function reject(ProfileChangeRequest $request, User $admin, string $notes): void
    {
        // Clean up uploaded license image since it won't be used
        if ($request->license_image_path) {
            Storage::disk('public')->delete($request->license_image_path);
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        $this->notifications->profileChangeRejected($request->user, $request, $notes);
    }
}