{{-- Customer Chat Widget --}}
@auth
    @if(auth()->user()->isCustomer())

        <div x-data="chatWidget()" x-init="init()" class="fixed bottom-6 right-6 z-[9999]">

            {{-- ─── Floating Button ────────────────────────────────────────────────── --}}
            <div class="flex flex-col items-center">
                <button @click="toggleOpen()" title="Live Chat" class="relative w-14 h-14 rounded-full border-none cursor-pointer
                               bg-gradient-to-br from-blue-500 to-blue-600
                               shadow-lg hover:shadow-xl transition-all
                               flex items-center justify-center" style="box-shadow: 0 4px 20px rgba(59,130,246,0.5);"
                    @mouseenter="$el.style.transform='scale(1.1)'" @mouseleave="$el.style.transform='scale(1)'">

                    {{-- Chat Icon --}}
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>

                    {{-- Unread Badge --}}
                    <template x-if="unreadCount > 0">
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs
                                     rounded-full flex items-center justify-center font-bold" x-text="unreadCount">
                        </span>
                    </template>
                </button>
                <p class="text-center text-white text-xs mt-1" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">Chat</p>
            </div>

            {{-- ─── Chat Panel ─────────────────────────────────────────────────────── --}}
            <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4" class="absolute bottom-20 right-0 w-80 bg-white rounded-2xl shadow-2xl
                        border border-gray-200 flex flex-col overflow-hidden" style="height: 450px;">

                {{-- Panel Header --}}
                <div class="flex items-center justify-between px-4 py-3
                            bg-gradient-to-br from-blue-500 to-blue-600 text-white flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span class="font-semibold text-sm">Support Chat</span>
                    </div>
                    <button @click="isOpen = false" class="text-white/70 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3" x-ref="widgetMessages">

                    <template x-if="loading">
                        <div class="text-center text-xs text-gray-400 py-4">Loading...</div>
                    </template>

                    <template x-if="messages.length === 0 && !loading">
                        <div class="text-center text-xs text-gray-400 py-6">
                            <p>Send a message to start the conversation.</p>
                        </div>
                    </template>

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex" :class="!msg.sender.is_admin ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%]">
                                <div class="px-3 py-2 rounded-xl text-xs leading-relaxed" :class="!msg.sender.is_admin
                                        ? 'bg-blue-600 text-white rounded-br-sm'
                                        : 'bg-gray-100 text-gray-800 rounded-bl-sm'">
                                    <p x-text="msg.body"></p>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5 px-1"
                                    :class="!msg.sender.is_admin ? 'text-right' : 'text-left'" x-text="msg.created_at">
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input --}}
                <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
                    <div class="flex items-end gap-2">
                        <textarea x-model="newMessage" @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                            placeholder="Type a message..." rows="2" class="flex-1 text-xs border border-gray-200 rounded-xl px-3 py-2 resize-none
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </textarea>
                        <button @click="sendMessage()" :disabled="!newMessage.trim() || sending"
                            :class="newMessage.trim() ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-200'"
                            class="p-2 text-white rounded-xl transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
        <script>
            const widgetEcho = new Echo({
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

            function chatWidget() {
                return {
                    isOpen: false,
                    messages: [],
                    newMessage: '',
                    sending: false,
                    loading: false,
                    unreadCount: 0,
                    roomId: null,

                    async init() {
                        await this.loadRoom();
                    },

                    async toggleOpen() {
                        this.isOpen = !this.isOpen;
                        if (this.isOpen && this.roomId) {
                            await this.markRead();
                            this.unreadCount = 0;
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    },

                    async loadRoom() {
                        this.loading = true;
                        try {
                            const res = await fetch('/api/v1/chat/room', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                }
                            });
                            const data = await res.json();
                            this.roomId = data.room?.id;
                            this.messages = data.messages ?? [];

                            if (this.roomId) {
                                widgetEcho.private(`chat.${this.roomId}`)
                                    .listen('.NewChatMessage', (event) => {
                                        this.messages.push({
                                            id: event.message.id,
                                            body: event.message.body,
                                            created_at: event.message.created_at_human,
                                            sender: {
                                                is_admin: event.message.is_admin,
                                                name: event.message.sender_name,
                                            }
                                        });
                                        if (!this.isOpen) this.unreadCount++;
                                        this.$nextTick(() => this.scrollToBottom());
                                    });
                            }
                        } catch (e) {
                            console.error('Failed to load chat room:', e);
                        }
                        this.loading = false;
                    },

                    async sendMessage() {
                        if (!this.newMessage.trim() || !this.roomId || this.sending) return;
                        this.sending = true;
                        const body = this.newMessage;
                        this.newMessage = '';

                        try {
                            const res = await fetch(`/api/v1/chat/rooms/${this.roomId}/messages`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ body })
                            });
                            const data = await res.json();
                            if (data.data) {
                                this.messages.push({
                                    id: data.data.id,
                                    body: data.data.body,
                                    created_at: data.data.created_at_human,
                                    sender: {
                                        is_admin: data.data.is_admin,
                                        name: data.data.sender_name,
                                    }
                                });
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        } catch (e) {
                            console.error('Failed to send message:', e);
                            this.newMessage = body;
                        }
                        this.sending = false;
                    },

                    async markRead() {
                        if (!this.roomId) return;
                        await fetch(`/api/v1/chat/rooms/${this.roomId}/read`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                    },

                    scrollToBottom() {
                        const container = this.$refs.widgetMessages;
                        if (container) container.scrollTop = container.scrollHeight;
                    }
                };
            }
        </script>

    @endif
@endauth