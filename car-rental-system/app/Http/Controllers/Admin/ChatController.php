<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $rooms = ChatRoom::with(['customer:id,name,email,last_seen_at'])
            ->withCount([
                'messages as unread_count' => fn($q) =>
                    $q->where('is_read', false)
                        ->whereHas(
                            'sender',
                            fn($sq) =>
                            $sq->where('role', 'customer')
                        )
            ])
            ->with(['lastMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        return view('admin.chat.index', compact('rooms'));
    }
}