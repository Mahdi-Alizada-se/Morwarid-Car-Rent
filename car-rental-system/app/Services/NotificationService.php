<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\ProfileChangeRequest;
use App\Models\User;

class NotificationService
{
    // ─── Booking Notifications ───────────────────────────────────────────────

    public function bookingCreated(Booking $booking): void
    {
        $this->create(
            user: $booking->customer,
            type: 'booking_created',
            title: __('notifications.booking_created_title'),
            body: __('notifications.booking_created_body', ['ref' => $booking->reference_code]),
            link: route('customer.bookings.show', $booking),
            bookingId: $booking->id,
        );
    }

    public function paymentConfirmed(Booking $booking): void
    {
        $this->create(
            user: $booking->customer,
            type: 'payment_confirmed',
            title: __('notifications.payment_confirmed_title'),
            body: __('notifications.payment_confirmed_body', ['ref' => $booking->reference_code]),
            link: route('customer.bookings.show', $booking),
            bookingId: $booking->id,
        );
    }

    public function bookingCancelled(Booking $booking): void
    {
        $this->create(
            user: $booking->customer,
            type: 'booking_cancelled',
            title: __('notifications.booking_cancelled_title'),
            body: __('notifications.booking_cancelled_body', ['ref' => $booking->reference_code]),
            link: route('customer.bookings.show', $booking),
            bookingId: $booking->id,
        );
    }

    // ─── Profile Change Notifications ────────────────────────────────────────

    public function profileChangeApproved(User $user, ProfileChangeRequest $request): void
    {
        $this->create(
            user: $user,
            type: 'profile_change_approved',
            title: __('notifications.profile_change_approved_title'),
            body: __('notifications.profile_change_approved_body'),
            link: route('customer.profile.edit'),
        );
    }

    public function profileChangeRejected(User $user, ProfileChangeRequest $request, string $reason): void
    {
        $this->create(
            user: $user,
            type: 'profile_change_rejected',
            title: __('notifications.profile_change_rejected_title'),
            body: __('notifications.profile_change_rejected_body', ['reason' => $reason]),
            link: route('customer.profile.edit'),
        );
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function create(
        ?User $user,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?int $bookingId = null,
    ): void {
        if (!$user) {
            return;
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'booking_id' => $bookingId,
        ]);

        broadcast(new NotificationCreated($notification));
    }
}