<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {
    }

    // ─── Customer: Get Own Room ───────────────────────────────────────────────

    public function room(Request $request): JsonResponse
    {
        $user = auth()->user();
        $room = $this->chatService->getOrCreateRoom($user);

        $messages = Message::with('sender')
            ->where('chat_room_id', $room->id)
            ->latest()
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        // Mark messages as read
        $this->chatService->markAsRead($room, $user);

        return response()->json([
            'room' => [
                'id' => $room->id,
                'last_message_at' => $room->last_message_at?->diffForHumans(),
            ],
            'messages' => $messages->map(fn($m) => $this->formatMessage($m)),
        ]);
    }

    // ─── Admin: All Rooms ─────────────────────────────────────────────────────

    public function rooms(Request $request): JsonResponse
    {
        $rooms = ChatRoom::with(['customer', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn($room) => [
                'id' => $room->id,
                'customer' => [
                    'id' => $room->customer?->id,
                    'name' => $room->customer?->name,
                    'email' => $room->customer?->email,
                    'avatar' => $room->customer?->avatar
                        ? asset('storage/' . $room->customer->avatar)
                        : null,
                ],
                'last_message' => $room->latestMessage
                    ? [
                        'body' => $room->latestMessage->body,
                        'sender' => $room->latestMessage->sender?->name,
                        'created_at' => $room->latestMessage->created_at->diffForHumans(),
                    ]
                    : null,
                'unread_count' => $room->getUnreadCountFor(auth()->id()),
                'last_message_at' => $room->last_message_at?->diffForHumans(),
            ]);

        return response()->json(['rooms' => $rooms]);
    }

    // ─── Get Messages (Cursor Paginated) ──────────────────────────────────────

    public function messages(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = auth()->user();

        // Customer can only access their own room
        if ($user->isCustomer() && $chatRoom->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $messages = Message::with('sender')
            ->where('chat_room_id', $chatRoom->id)
            ->latest()
            ->cursorPaginate(20);

        return response()->json([
            'messages' => collect($messages->items())
                ->reverse()
                ->values()
                ->map(fn($m) => $this->formatMessage($m)),
            'next_cursor' => $messages->nextCursor()?->encode(),
        ]);
    }

    // ─── Send Message ─────────────────────────────────────────────────────────

    public function sendMessage(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = auth()->user();

        // Customer can only message in their own room
        if ($user->isCustomer() && $chatRoom->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'body' => ['required_without:attachment', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,gif', 'max:5120'],
        ]);

        $message = $this->chatService->sendMessage(
            $chatRoom,
            $user,
            $request->body ?? '',
            $request->file('attachment')
        );

        return response()->json([
            'message' => $this->formatMessage($message),
        ], 201);
    }

    // ─── Mark As Read ─────────────────────────────────────────────────────────

    public function markRead(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = auth()->user();

        if ($user->isCustomer() && $chatRoom->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->chatService->markAsRead($chatRoom, $user);

        return response()->json(['message' => 'Messages marked as read.']);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'is_read' => $message->is_read,
            'attachment_url' => $message->attachment_path
                ? Storage::disk('public')->url($message->attachment_path)
                : null,
            'sender' => [
                'id' => $message->sender?->id,
                'name' => $message->sender?->name,
                'is_admin' => $message->sender?->isAdmin() ?? false,
                'avatar' => $message->sender?->avatar
                    ? asset('storage/' . $message->sender->avatar)
                    : null,
            ],
            'created_at' => $message->created_at->diffForHumans(),
            'created_at_full' => $message->created_at->format('M d, Y H:i'),
        ];
    }
}