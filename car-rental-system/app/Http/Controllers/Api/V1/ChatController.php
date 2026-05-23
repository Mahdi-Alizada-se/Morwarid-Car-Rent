<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\NewChatMessage;
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

        $messages = Message::with('sender:id,name,role')
            ->where('chat_room_id', $room->id)
            ->latest()
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        $this->chatService->markAsRead($room, $user);

        return response()->json([
            'success' => true,
            'room' => [
                'id' => $room->id,
                'last_message_at' => $room->last_message_at?->diffForHumans(),
            ],
            'messages' => $messages->map(fn($m) => $this->formatMessage($m)),
        ]);
    }

    // ─── Rooms ────────────────────────────────────────────────────────────────

    public function rooms(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $rooms = ChatRoom::with(['customer:id,name,email'])
                ->withCount([
                    'messages as unread_count' => fn($q) =>
                        $q->where('is_read', false)
                            ->whereHas(
                                'sender',
                                fn($sq) =>
                                $sq->where('role', 'customer')
                            )
                ])
                ->with(['lastMessage.sender:id,name,role'])
                ->orderByDesc('last_message_at')
                ->get();
        } else {
            $rooms = ChatRoom::with(['customer:id,name,email'])
                ->where('customer_id', $user->id)
                ->with(['lastMessage.sender:id,name,role'])
                ->get();
        }

        return response()->json([
            'success' => true,
            'rooms' => $rooms->map(fn($room) => [
                'id' => $room->id,
                'customer' => [
                    'id' => $room->customer?->id,
                    'name' => $room->customer?->name,
                    'email' => $room->customer?->email,
                ],
                'last_message' => $room->lastMessage ? [
                    'body' => $room->lastMessage->body,
                    'sender' => $room->lastMessage->sender?->name,
                    'is_admin' => $room->lastMessage->sender?->role === 'admin',
                    'created_at' => $room->lastMessage->created_at->diffForHumans(),
                ] : null,
                'unread_count' => $room->unread_count ?? 0,
                'last_message_at' => $room->last_message_at?->diffForHumans(),
            ]),
        ]);
    }

    // ─── Get Messages ─────────────────────────────────────────────────────────────

    public function messages(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = auth()->user();

        // Security check
        if ($user->isCustomer() && $chatRoom->customer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $messages = $chatRoom->messages()
            ->with('sender:id,name,role')
            ->orderBy('created_at', 'asc')
            ->get();

        $transformed = $messages->map(fn($message) => [
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'is_admin' => $message->sender?->role === 'admin',
            'created_at_human' => $message->created_at->diffForHumans(),
            'created_at' => $message->created_at->toISOString(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $transformed,
        ]);
    }

    // ─── Send Message ─────────────────────────────────────────────────────────

    // ─── Send Message ─────────────────────────────────────────────────────────────

    public function sendMessage(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = auth()->user();

        // Customer can only send to their own room
        if ($user->isCustomer() && $chatRoom->customer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = \App\Models\Message::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => $user->id,
            'body' => $request->body,
            'is_read' => false,
        ]);

        $message->load('sender:id,name,role');

        $chatRoom->update(['last_message_at' => now()]);

        $messageData = [
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'is_admin' => $message->sender?->role === 'admin',
            'created_at_human' => $message->created_at->diffForHumans(),
            'created_at' => $message->created_at->toISOString(),
        ];

        broadcast(new \App\Events\NewChatMessage($message))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $messageData,
        ], 201);
    }

    // ─── Mark As Read ─────────────────────────────────────────────────────────

    public function markRead(Request $request, ChatRoom $chatRoom): JsonResponse
    {
        $user = auth()->user();

        if ($user->isCustomer() && $chatRoom->customer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $this->chatService->markAsRead($chatRoom, $user);

        return response()->json(['success' => true, 'message' => 'Messages marked as read.']);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'is_read' => $message->is_read,
            'is_admin' => $message->sender?->role === 'admin',
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'attachment_url' => $message->attachment_path
                ? Storage::disk('public')->url($message->attachment_path)
                : null,
            'created_at_human' => $message->created_at->diffForHumans(),
            'created_at' => $message->created_at->toISOString(),
        ];
    }
}