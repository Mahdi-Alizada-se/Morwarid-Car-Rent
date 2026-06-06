@extends('layouts.admin')

@section('page-title', __('common.nav_chat'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_chat') }}</span>
@endsection

@push('styles')
    <style>
        .chat-layout {
            height: calc(100vh - 130px);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden chat-layout flex" x-data="adminChat()"
        x-init="init()">

        {{-- ─── Left Sidebar ─────────────────────────────────────────────────── --}}
        <div class="w-80 border-r border-gray-200 flex flex-col flex-shrink-0 h-full">

            {{-- Header --}}
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900">Messages</h3>
                    @php $totalUnread = $rooms->sum('unread_count'); @endphp
                    <p class="total-unread-text text-xs mt-0.5
                              {{ $totalUnread > 0 ? 'text-orange-600 font-semibold' : 'text-gray-400' }}">
                        {{ $totalUnread > 0 ? $totalUnread . ' unread' : 'All caught up' }}
                    </p>
                </div>
                @if($totalUnread > 0)
                    <span class="total-unread-badge w-6 h-6 bg-red-500 text-white
                                     text-xs font-bold rounded-full flex items-center justify-center">
                        {{ $totalUnread }}
                    </span>
                @endif
            </div>

            {{-- Search --}}
            <div class="px-3 py-2 border-b border-gray-100">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Search customers..." class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-200
                                  rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Rooms List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($rooms as $room)
                        <div @click="selectRoom({{ $room->id }}, '{{ addslashes($room->customer->name) }}')"
                            data-room-id="{{ $room->id }}" :class="selectedRoomId === {{ $room->id }}
                                     ? 'bg-blue-50 border-l-4 border-blue-500'
                                     : 'hover:bg-gray-50 border-l-4 border-transparent'"
                            x-show="!search || '{{ strtolower($room->customer->name) }}'.includes(search.toLowerCase())"
                            class="px-3 py-3 cursor-pointer border-b border-gray-50 transition-colors">

                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">

                                    {{-- Avatar --}}
                                    @if($room->customer->avatar)
                                        <img src="{{ asset('storage/' . $room->customer->avatar) }}"
                                            class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700
                                                            font-bold text-sm flex items-center
                                                            justify-center flex-shrink-0">
                                            {{ strtoupper(substr($room->customer->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="room-name text-sm font-gray-900 truncate
                                                          {{ $room->unread_count > 0 ? 'font-bold' : 'font-medium' }}">
                                                {{ $room->customer->name }}
                                            </p>
                                            <span class="w-2 h-2 rounded-full flex-shrink-0
                                                    {{ $room->customer->last_seen_at &&
                    $room->customer->last_seen_at->gt(now()->subMinutes(2))
                    ? 'bg-green-500' : 'bg-gray-300' }}">
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 truncate">
                                            {{ \Illuminate\Support\Str::limit($room->lastMessage?->body ?? 'No messages yet', 35) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex-shrink-0 text-right">
                                    @if($room->last_message_at)
                                        <p class="text-xs text-gray-400">
                                            {{ $room->last_message_at->diffForHumans(null, true) }}
                                        </p>
                                    @endif
                                    @if($room->unread_count > 0)
                                        <span class="unread-badge inline-flex items-center justify-center
                                                             w-5 h-5 rounded-full bg-red-500 text-white
                                                             text-xs font-bold mt-1">
                                            {{ $room->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                @empty
                    <div class="px-4 py-8 text-center text-gray-400 text-sm">
                        No conversations yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ─── Right: Message Area ──────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col h-full min-w-0">

            {{-- Empty State --}}
            <div class="flex-1 flex items-center justify-center" x-show="selectedRoomId === null" x-cloak>
                <div class="text-center text-gray-400 p-8">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" stroke-width="1"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0
                              0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75
                              0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75
                              0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227
                              1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14
                              0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233
                              2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228
                              A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373
                              3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                    <p class="font-semibold text-gray-500 text-lg">Select a conversation</p>
                    <p class="text-sm mt-1">Choose a customer from the left panel</p>
                </div>
            </div>

            {{-- Active Conversation --}}
            <div class="flex flex-col h-full" x-show="selectedRoomId !== null" x-cloak>

                {{-- Chat Header --}}
                <div class="px-5 py-3 border-b border-gray-200 flex items-center gap-3 flex-shrink-0">
                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700
                                font-bold text-sm flex items-center justify-center flex-shrink-0">
                        <span x-text="selectedRoomName.substring(0,1).toUpperCase()"></span>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900" x-text="selectedRoomName"></p>
                        <p class="text-xs text-green-500 font-medium">● Active</p>
                    </div>
                </div>

                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="messagesArea">

                    <div x-show="isLoading" class="flex justify-center py-6">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 bg-blue-400 rounded-full animate-bounce"></div>
                            <div class="w-2.5 h-2.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay:0.1s">
                            </div>
                            <div class="w-2.5 h-2.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay:0.2s">
                            </div>
                        </div>
                    </div>

                    <template x-if="!isLoading && messages.length === 0">
                        <div class="text-center text-gray-400 text-sm py-8">
                            No messages yet. Start the conversation!
                        </div>
                    </template>

                    <template x-for="msg in messages" :key="msg.id">
                        <div :class="msg.is_admin ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="msg.is_admin
                                    ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm'
                                    : 'bg-gray-100 text-gray-800 rounded-2xl rounded-tl-sm'"
                                class="max-w-xs lg:max-w-md px-4 py-2.5">
                                <p class="text-sm leading-relaxed" x-text="msg.body"></p>
                                <p class="text-xs mt-1 opacity-70" x-text="msg.created_at_human"></p>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- Input Area --}}
                <div class="border-t border-gray-200 p-3 bg-white flex-shrink-0">
                    <textarea x-model="newMessage" rows="2" :placeholder="'Reply to ' + selectedRoomName + '...'" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm
                                     resize-none focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()">
                    </textarea>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs text-gray-400">
                            Enter to send · Shift+Enter for new line
                        </p>
                        <button @click="sendMessage()" :disabled="sending || !newMessage.trim()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2
                                       rounded-xl text-sm font-medium transition-colors
                                       disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-show="!sending">Send Reply</span>
                            <span x-show="sending" class="flex items-center gap-1">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function adminChat() {
            return {
                selectedRoomId: null,
                selectedRoomName: '',
                messages: [],
                newMessage: '',
                sending: false,
                isLoading: false,
                search: '',
                csrfToken: '{{ csrf_token() }}',
                roomIds: @json($rooms->pluck('id')),

                init() {
                    this.roomIds.forEach(roomId => {
                        window.Echo.private('chat.' + roomId)
                            .listen('.NewChatMessage', (event) => {
                                const msg = event.message;

                                if (this.selectedRoomId === roomId) {
                                    // Panel open — add message, no badge needed
                                    const exists = this.messages.find(m => m.id === msg.id);
                                    if (!exists) {
                                        this.messages.push({
                                            id: msg.id,
                                            body: msg.body,
                                            is_admin: msg.is_admin,
                                            sender_name: msg.sender_name,
                                            created_at_human: msg.created_at_human,
                                        });
                                        this.$nextTick(() => this.scrollToBottom());
                                    }
                                } else {
                                    // Different room — show badge on that room
                                    if (!msg.is_admin) {
                                        this.incrementRoomBadge(roomId);
                                    }
                                }
                            });
                    });
                },

                // ─── Add +1 to a room badge when new message arrives ─────────────────

                incrementRoomBadge(roomId) {
                    const roomEl = document.querySelector(`[data-room-id="${roomId}"]`);
                    if (!roomEl) return;

                    // Make name bold
                    const nameEl = roomEl.querySelector('.room-name');
                    if (nameEl) {
                        nameEl.classList.add('font-bold');
                        nameEl.classList.remove('font-medium');
                    }

                    // Update or create badge
                    let badge = roomEl.querySelector('.unread-badge');
                    if (badge) {
                        badge.textContent = (parseInt(badge.textContent) || 0) + 1;
                    } else {
                        const rightEl = roomEl.querySelector('.flex-shrink-0.text-right');
                        if (rightEl) {
                            badge = document.createElement('span');
                            badge.className = 'unread-badge inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold mt-1';
                            badge.textContent = '1';
                            rightEl.appendChild(badge);
                        }
                    }

                    // Update header total
                    this.updateTotalBadge(1);

                    // Update nav badge
                    this.updateNavBadge(1);
                },

                // ─── Update total unread in sidebar header ────────────────────────────

                updateTotalBadge(delta) {
                    const textEl = document.querySelector('.total-unread-text');
                    const badgeEl = document.querySelector('.total-unread-badge');

                    // Count all current badges
                    let total = 0;
                    document.querySelectorAll('.unread-badge').forEach(b => {
                        total += parseInt(b.textContent) || 0;
                    });

                    if (textEl) {
                        if (total > 0) {
                            textEl.textContent = total + ' unread';
                            textEl.className = 'total-unread-text text-xs mt-0.5 text-orange-600 font-semibold';
                        } else {
                            textEl.textContent = 'All caught up';
                            textEl.className = 'total-unread-text text-xs mt-0.5 text-gray-400';
                        }
                    }

                    if (badgeEl) {
                        if (total > 0) {
                            badgeEl.textContent = total;
                            badgeEl.style.display = '';
                        } else {
                            badgeEl.style.display = 'none';
                        }
                    }
                },

                // ─── Update nav sidebar badge ─────────────────────────────────────────

                updateNavBadge(delta) {
                    const navBadge = document.querySelector('.chat-nav-badge');
                    if (navBadge) {
                        const current = parseInt(navBadge.textContent) || 0;
                        const newVal = Math.max(0, current + delta);
                        if (newVal > 0) {
                            navBadge.textContent = newVal > 9 ? '9+' : newVal;
                            navBadge.style.display = '';
                        } else {
                            navBadge.style.display = 'none';
                        }
                    }
                },

                // ─── Select Room ──────────────────────────────────────────────────────

                async selectRoom(roomId, roomName) {
                    this.selectedRoomId = roomId;
                    this.selectedRoomName = roomName;
                    this.messages = [];
                    this.isLoading = true;

                    // Get badge count before removing
                    const roomEl = document.querySelector(`[data-room-id="${roomId}"]`);
                    let badgeCount = 0;

                    if (roomEl) {
                        const badge = roomEl.querySelector('.unread-badge');
                        if (badge) {
                            badgeCount = parseInt(badge.textContent) || 0;
                            badge.remove();
                        }
                        const nameEl = roomEl.querySelector('.room-name');
                        if (nameEl) {
                            nameEl.classList.remove('font-bold');
                            nameEl.classList.add('font-medium');
                        }
                    }

                    // Update header total
                    this.updateTotalBadge(0);

                    // Update nav badge
                    if (badgeCount > 0) {
                        this.updateNavBadge(-badgeCount);
                    }

                    // Load messages
                    try {
                        const res = await fetch('/api/v1/chat/rooms/' + roomId + '/messages', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            credentials: 'include',
                        });

                        const data = await res.json();

                        if (data.success && Array.isArray(data.data)) {
                            this.messages = data.data;
                        } else {
                            this.messages = [];
                        }

                    } catch (e) {
                        console.error('Failed to load messages:', e);
                        this.messages = [];
                    }

                    this.isLoading = false;
                    this.$nextTick(() => this.scrollToBottom());

                    // Mark as read in backend
                    fetch('/api/v1/chat/rooms/' + roomId + '/read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        credentials: 'include',
                    }).catch(() => { });
                },

                // ─── Send Message ─────────────────────────────────────────────────────

                async sendMessage() {
                    if (!this.newMessage.trim() || !this.selectedRoomId || this.sending) return;

                    this.sending = true;
                    const text = this.newMessage.trim();
                    this.newMessage = '';

                    try {
                        const res = await fetch('/api/v1/chat/rooms/' + this.selectedRoomId + '/messages', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                            credentials: 'include',
                            body: JSON.stringify({ body: text }),
                        });

                        const data = await res.json();

                        if (data.success && data.data) {
                            this.messages.push(data.data);
                            this.$nextTick(() => this.scrollToBottom());
                        } else {
                            this.newMessage = text;
                            alert('Failed to send. Please try again.');
                        }

                    } catch (e) {
                        this.newMessage = text;
                        alert('Failed to send message. Please try again.');
                    }

                    this.sending = false;
                },

                scrollToBottom() {
                    const el = this.$refs.messagesArea;
                    if (el) el.scrollTop = el.scrollHeight;
                },
            };
        }
    </script>
@endpush