{{-- Customer Chat Widget — include in layouts/app.blade.php --}}
@auth
    @if(auth()->user()->isCustomer())

        <div x-data="chatWidget()" x-init="init()" class="fixed bottom-6 right-6 z-50">

            {{-- Floating Button --}}
            <button @click="toggleOpen()" class="relative w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg
                           hover:bg-indigo-700 transition-all flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>

                {{-- Unread Badge --}}
                <template x-if="unreadCount > 0">
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs
                                 rounded-full flex items-center justify-center font-bold" x-text="unreadCount">
                    </span>
                </template>
            </button>

            {{-- Chat Panel --}}
            <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4" class="absolute bottom-16 right-0 w-80 bg-white rounded-2xl shadow-2xl
                        border border-gray-200 flex flex-col overflow-hidden" style="height: 450px;">

                {{-- Panel Header --}}
                <div class="flex items-center justify-between px-4 py-3 bg-indigo-600 text-white flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span class="font-semibold text-sm">{{ __('chat.support') }}</span>
                    </div>
                    <button @click="isOpen = false" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3" x-ref="widgetMessages">

                    <template x-if="loading">
                        <div class="text-center text-xs text-gray-400 py-4">{{ __('chat.loading') }}...</div>
                    </template>

                    <template x-if="messages.length === 0 && !loading">
                        <div class="text-center text-xs text-gray-400 py-6">
                            <p>{{ __('chat.start_conversation') }}</p>
                        </div>
                    </template>

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex" :class="!msg.sender.is_admin ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%]">
                                <div class="px-3 py-2 rounded-xl text-xs leading-relaxed" :class="!msg.sender.is_admin
                                        ? 'bg-indigo-600 text-white rounded-br-sm'
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
                            placeholder="{{ __('chat.type_message') }}..." rows="2" class="flex-1 text-xs border border-gray-200 rounded-xl px-3 py-2 resize-none
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </textarea>
                        <button @click="sendMessage()" :disabled="!newMessage.trim() || sending"
                            :class="newMessage.trim() ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-200'"
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

                            // Subscribe to Echo channel
                            if (this.roomId) {
                                widgetEcho.private(`chat.${this.roomId}`)
                                    .listen('.new-message', (event) => {
                                        this.messages.push(event);
                                        if (!this.isOpen) {
                                            this.unreadCount++;
                                        }
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
                            this.messages.push(data.message);
                            this.$nextTick(() => this.scrollToBottom());
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
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }
                };
            }
        </script>

    @endif
@endauth