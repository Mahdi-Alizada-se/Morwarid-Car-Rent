<?php

use App\Models\ChatRoom;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Chat channel — only the customer of this room or an admin can listen
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if ($user->isAdmin()) {
        return true;
    }

    $room = ChatRoom::find($roomId);
    return $room && $room->customer_id === $user->id;
});

// GPS channel — only admins and the customer with active booking can listen
Broadcast::channel('gps.{vehicleId}', function ($user, $vehicleId) {
    if ($user->isAdmin()) {
        return true;
    }

    // Customer can see GPS of their own active booking
    return $user->bookings()
        ->where('vehicle_id', $vehicleId)
        ->where('status', 'active')
        ->exists();
});

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});