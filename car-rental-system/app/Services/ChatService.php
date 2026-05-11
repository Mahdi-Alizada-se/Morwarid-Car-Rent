<?php

namespace App\Services;

use App\Events\NewChatMessage;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    // ─── Get or Create Room ───────────────────────────────────────────────────

    public function getOrCreateRoom(User $customer): ChatRoom
    {
        return ChatRoom::firstOrCreate(
            ['customer_id' => $customer->id],
            ['last_message_at' => null]
        );
    }

    // ─── Send Message ─────────────────────────────────────────────────────────

    public function sendMessage(
        ChatRoom $room,
        User $sender,
        string $body,
        ?UploadedFile $attachment = null
    ): Message {
        $attachmentPath = null;

        // Handle attachment upload
        if ($attachment) {
            $filename = 'chat/' . uniqid() . '.' . $attachment->getClientOriginalExtension();
            $attachmentPath = Storage::disk('public')->putFileAs(
                'chat',
                $attachment,
                basename($filename)
            );
        }

        // Create the message
        $message = Message::create([
            'chat_room_id' => $room->id,
            'sender_id' => $sender->id,
            'body' => $body,
            'is_read' => false,
            'attachment_path' => $attachmentPath,
        ]);

        // Update room's last message timestamp
        $room->update(['last_message_at' => now()]);

        // Load sender for broadcast
        $message->load('sender');

        // Broadcast to private channel
        broadcast(new NewChatMessage($message))->toOthers();

        return $message;
    }

    // ─── Mark Messages As Read ────────────────────────────────────────────────

    public function markAsRead(ChatRoom $room, User $reader): void
    {
        // Mark all messages NOT sent by this reader as read
        Message::where('chat_room_id', $room->id)
            ->where('sender_id', '!=', $reader->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}