@extends('layouts.admin')

@section('page-title', __('common.nav_chat'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_chat') }}</span>
@endsection

@push('styles')
    <style>
        .messages-container {
            height: calc(100vh - 340px);
            min-height: 300px;
        }

        .message-bubble {
            max-width: 70%;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="flex gap-0 bg-white rounded-xl border border-gray-200 overflow-hidden" style="height: calc(100vh - 180px);"
        x-data="chatAdmin()" x-init="init()">

        {{-- ─── Left: Room List ──────────────────────────────────────────────────── --}}
        <div class="w-80 border-r border-gray-200 flex flex-col flex-shrink-0">

            {{-- Header --}}
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">{{ __('common.nav_chat') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('chat.all_conversations') }}</p>
            </div>

            {{-- Room List --}}
            <div class="flex-1 overflow-y-auto">
                <template x-if="rooms.length === 0">
                    <div class="p-6 text-center text-gray-400 text-sm">
                        {{ __('chat.no_conversations') }}
                    </div>
                </template>

                <template x-for="room in rooms" :key="room.id">
                    <div @click="selectRoom(room)"
                        class="flex items-center gap-3 px-4 py-3 cursor-pointer border-b border-gray-50 hover:bg-gray-50 transition-colors"
                        :class="selectedRoom?.id === room.id ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : ''">

                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center
                                    text-indigo-700 font-semibold text-sm flex-shrink-0"
                            x-text="room.customer?.name?.substring(0,2).toUpperCase()">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="room.customer?.name">
                                </p>
                                <span class="text-xs text-gray-400" x-text="room.last_message_at"></span>
                            </div>
                            <p class="text-xs text-gray-500 truncate"
                                x-text="room.last_message?.body ?? '{{ __('chat.no_messages') }}'">
                            </p>
                        </div>

                        {{-- Unread badge --}}
                        <template x-if="room.unread_count > 0">
                            <span class="w-5 h-5 bg-indigo-600 text-white text-xs rounded-full
                                         flex items-center justify-center font-semibold flex-shrink-0"
                                x-text="room.unread_count">
                            </span>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ─── Right: Message Thread ────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- No Room Selected --}}
            <template x-if="!selectedRoom">
                <div class="flex-1 flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <p class="text-sm font-medium">{{ __('chat.select_conversation') }}</p>
                    </div>
                </div>
            </template>

            {{-- Room Selected --}}
            <template x-if="selectedRoom">
                <div class="flex flex-col h-full">

                    {{-- Chat Header --}}
                    <div class="px-5 py-3 border-b border-gray-200 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center
                                    text-indigo-700 font-semibold text-sm"
                            x-text="selectedRoom.customer?.name?.substring(0,2).toUpperCase()">
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm" x-text="selectedRoom.customer?.name"></p>
                            <p class="text-xs text-gray-400" x-text="selectedRoom.customer?.email"></p>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3" id="messages-container"
                        x-ref="messagesContainer">

                        <template x-if="loadingMessages">
                            <div class="text-center text-sm text-gray-400 py-4">{{ __('chat.loading') }}...</div>
                        </template>

                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex" :class="msg.sender.is_admin ? 'justify-end' : 'justify-start'">
                                <div class="message-bubble">

                                    {{-- Sender name --}}
                                    <p class="text-xs text-gray-400 mb-1"
                                        :class="msg.sender.is_admin ? 'text-right' : 'text-left'" x-text="msg.sender.name">
                                    </p>

                                    {{-- Bubble --}}
                                    <div class="px-4 py-2.5 rounded-2xl text-sm" :class="msg.sender.is_admin
                                            ? 'bg-indigo-600 text-white rounded-tr-sm'
                                            : 'bg-gray-100 text-gray-900 rounded-tl-sm'">
                                        <p x-text="msg.body"></p>
                                        <template x-if="msg.attachment_url">
                                            <a :href="msg.attachment_url" target="_blank"
                                                class="block mt-1 text-xs underline opacity-75">
                                                📎 {{ __('chat.attachment') }}
                                            </a>
                                        </template>
                                    </div>

                                    {{-- Time --}}
                                    <p class="text-xs text-gray-400 mt-1"
                                        :class="msg.sender.is_admin ? 'text-right' : 'text-left'" x-text="msg.created_at">
                                    </p>

                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Input --}}
                    <div class="px-5 py-3 border-t border-gray-200">
                        <div class="flex items-end gap-3">
                            <textarea x-model="newMessage" @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                placeholder="{{ __('chat.type_message') }}..." rows="2" class="flex-1 text-sm border border-gray-200 rounded-xl px-4 py-2.5 resize-none
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </textarea>
                            <button @click="sendMessage()" :disabled="!newMessage.trim() || sending" :class="newMessage.trim() && !sending
                                        ? 'bg-indigo-600 hover:bg-indigo-700'
                                        : 'bg-gray-300 cursor-not-allowed'"
                                class="px-4 py-2.5 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
                                <span x-show="!sending">{{ __('chat.send') }}</span>
                                <span x-show="sending">...</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('chat.enter_to_send') }}</p>
                    </div>

                </div>
            </template>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <script>
        // Initialize Laravel Echo
        const echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ config('broadcasting.connections.pusher.key') }}',
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }
        });

        function chatAdmin() {
            return {
                rooms: [],
                selectedRoom: null,
                messages: [],
                newMessage: '',
                sending: false,
                loadingMessages: false,
                echoChannel: null,

                async init() {
                    await this.loadRooms();
                },

                async loadRooms() {
                    try {
                        const res = await fetch('/api/v1/chat/rooms', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                        const data = await res.json();
                        this.rooms = data.rooms ?? [];
                    } catch (e) {
                        console.error('Failed to load rooms:', e);
                    }
                },

                async selectRoom(room) {
                    this.selectedRoom = room;
                    this.messages = [];
                    this.loadingMessages = true;

                    // Unsubscribe from previous channel
                    if (this.echoChannel) {
                        echo.leave(this.echoChannel);
                    }

                    // Load messages
                    try {
                        const res = await fetch(`/api/v1/chat/rooms/${room.id}/messages`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        this.messages = data.messages ?? [];
                    } catch (e) {
                        console.error('Failed to load messages:', e);
                    }

                    this.loadingMessages = false;
                    this.$nextTick(() => this.scrollToBottom());

                    // Mark as read
                    await fetch(`/api/v1/chat/rooms/${room.id}/read`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });

                    // Update unread count in room list
                    room.unread_count = 0;

                    // Subscribe to Echo channel
                    this.echoChannel = `chat.${room.id}`;
                    echo.private(this.echoChannel)
                        .listen('.new-message', (event) => {
                            this.messages.push(event);
                            this.$nextTick(() => this.scrollToBottom());
                            // Update room list
                            this.loadRooms();
                        });
                },

                async sendMessage() {
                    if (!this.newMessage.trim() || this.sending) return;

                    this.sending = true;
                    const body = this.newMessage;
                    this.newMessage = '';

                    try {
                        const res = await fetch(`/api/v1/chat/rooms/${this.selectedRoom.id}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ body })
                        });
                        const data = await res.json();
                        this.messages.push(data.message);
                        this.$nextTick(() => this.scrollToBottom());
                    } catch (e) {
                        console.error('Failed to send message:', e);
                        this.newMessage = body;
                    }

                    this.sending = false;
                },

                scrollToBottom() {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }
            };
        }
    </script>
@endpush