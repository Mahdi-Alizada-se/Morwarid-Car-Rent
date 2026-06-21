{{-- Customer Support Chat Widget --}}
@auth
    @if(auth()->user()->isCustomer())

        @php $isRtl = in_array(app()->getLocale(), ['fa', 'ps']); @endphp

        <div x-data="chatWidget()" x-init="init()" style="display: inline-flex; align-items: center; position: relative;">

            {{-- Navbar Chat Button --}}
            <button @click="toggleOpen()" class="relative flex items-center gap-2 px-3 py-1.5
                                   text-white text-sm font-medium rounded-lg transition-colors"
                style="background-color: #4F46E5;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0
                                  1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354
                                  0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126
                                  2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976
                                  1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455
                                  48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76
                                  1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76
                                  3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
                <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px]
                                     bg-red-500 text-white text-xs rounded-full
                                     flex items-center justify-center font-bold px-1">
                </span>
            </button>

            {{-- Chat Panel --}}
            <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-4" style="position: fixed;
                                top: 72px;
                                {{ $isRtl ? 'left: 16px; right: auto;' : 'right: 16px; left: auto;' }}
                                width: 340px;
                                height: 480px;
                                z-index: 9998;
                                direction: ltr;" class="bg-white rounded-2xl shadow-2xl border border-gray-200
                                flex flex-col overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-4 py-3
                                    bg-gradient-to-br from-blue-500 to-blue-600
                                    text-white flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span class="font-semibold text-sm">
                            {{ app()->getLocale() === 'fa' ? 'چت پشتیبانی' : (app()->getLocale() === 'ps' ? 'د ملاتړ چیټ' : 'Support Chat') }}
                        </span>
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
                        <div class="text-center text-xs text-gray-400 py-4">
                            {{ app()->getLocale() === 'fa' ? 'در حال بارگذاری...' : 'Loading...' }}
                        </div>
                    </template>

                    <template x-if="messages.length === 0 && !loading">
                        <div class="text-center text-xs text-gray-400 py-6">
                            <p>{{ app()->getLocale() === 'fa' ? 'برای شروع مکالمه پیام بفرستید.' : 'Send a message to start the conversation.' }}
                            </p>
                        </div>
                    </template>

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex" :class="!msg.is_admin ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%]">
                                <div class="px-3 py-2 rounded-xl text-xs leading-relaxed" :class="!msg.is_admin
                                                 ? 'bg-blue-600 text-white rounded-br-sm'
                                                 : 'bg-gray-100 text-gray-800 rounded-bl-sm'">
                                    <p x-text="msg.body"></p>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5 px-1" :class="!msg.is_admin ? 'text-right' : 'text-left'"
                                    x-text="msg.created_at_human">
                                </p>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- Input --}}
                <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
                    <div class="flex items-end gap-2">
                        <textarea x-model="newMessage" @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                            placeholder="{{ app()->getLocale() === 'fa' ? 'پیام بنویسید...' : 'Type a message...' }}" rows="2"
                            class="flex-1 text-xs border border-gray-200 rounded-xl
                                                 px-3 py-2 resize-none focus:outline-none
                                                 focus:ring-2 focus:ring-blue-500">
                                </textarea>
                        <button @click="sendMessage()" :disabled="!newMessage.trim() || sending" :class="newMessage.trim()
                                            ? 'bg-blue-600 hover:bg-blue-700'
                                            : 'bg-gray-200'" class="p-2 text-white rounded-xl transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function chatWidget() {
                return {
                    isOpen: false,
                    messages: [],
                    newMessage: '',
                    sending: false,
                    loading: false,
                    unreadCount: 0,
                    roomId: null,
                    lastMessageId: 0,
                    pollTimer: null,
                    csrf: document.querySelector('meta[name="csrf-token"]').content,

                    async init() {
                        await this.loadRoom();
                        this.pollTimer = setInterval(() => this.pollMessages(), 5000);
                    },

                    async loadRoom() {
                        this.loading = true;
                        try {
                            const res = await fetch('/api/v1/chat/room', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                credentials: 'include',
                            });
                            const data = await res.json();

                            if (!data.success) return;

                            this.roomId = data.room?.id;
                            this.messages = (data.messages ?? []).map(m => ({
                                id: m.id,
                                body: m.body,
                                created_at_human: m.created_at_human,
                                is_admin: m.is_admin,
                            }));

                            if (this.messages.length > 0) {
                                this.lastMessageId = this.messages[this.messages.length - 1].id;
                            }

                            if (!this.isOpen && this.messages.length > 0) {
                                const lastReadId = parseInt(
                                    localStorage.getItem('chat_last_read_{{ auth()->id() }}') || '0'
                                );
                                const lastMsg = this.messages[this.messages.length - 1];
                                if (lastMsg && lastMsg.id > lastReadId) {
                                    const unreadAdmin = this.messages.filter(
                                        m => m.is_admin && m.id > lastReadId
                                    );
                                    this.unreadCount = unreadAdmin.length;
                                }
                            }

                        } catch (e) {
                            console.error('Failed to load chat room:', e);
                        }
                        this.loading = false;
                    },

                    async pollMessages() {
                        if (!this.roomId) return;
                        try {
                            const res = await fetch('/api/v1/chat/room', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                credentials: 'include',
                            });
                            const data = await res.json();

                            if (!data.success) return;

                            const allMessages = (data.messages ?? []).map(m => ({
                                id: m.id,
                                body: m.body,
                                created_at_human: m.created_at_human,
                                is_admin: m.is_admin,
                            }));

                            if (allMessages.length === 0) return;

                            const latestId = allMessages[allMessages.length - 1].id;
                            if (latestId <= this.lastMessageId) return;

                            const newMessages = allMessages.filter(m => m.id > this.lastMessageId);
                            newMessages.forEach(m => {
                                const exists = this.messages.find(msg => msg.id === m.id);
                                if (!exists) this.messages.push(m);
                            });

                            this.lastMessageId = latestId;

                            if (this.isOpen) {
                                this.$nextTick(() => this.scrollToBottom());
                            } else {
                                const adminMessages = newMessages.filter(m => m.is_admin);
                                if (adminMessages.length > 0) {
                                    this.unreadCount += adminMessages.length;
                                }
                            }
                        } catch (e) {
                            // silent fail
                        }
                    },

                    async toggleOpen() {
                        this.isOpen = !this.isOpen;
                        if (this.isOpen) {
                            this.unreadCount = 0;
                            if (this.lastMessageId > 0) {
                                localStorage.setItem(
                                    'chat_last_read_{{ auth()->id() }}',
                                    this.lastMessageId
                                );
                            }
                            await this.markRead();
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    },

                    async sendMessage() {
                        if (!this.newMessage.trim() || !this.roomId || this.sending) return;
                        this.sending = true;
                        const body = this.newMessage;
                        this.newMessage = '';

                        try {
                            const res = await fetch('/api/v1/chat/rooms/' + this.roomId + '/messages', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                credentials: 'include',
                                body: JSON.stringify({ body })
                            });
                            const data = await res.json();

                            if (data.data) {
                                const msg = {
                                    id: data.data.id,
                                    body: data.data.body,
                                    created_at_human: data.data.created_at_human,
                                    is_admin: data.data.is_admin,
                                };
                                const exists = this.messages.find(m => m.id === msg.id);
                                if (!exists) {
                                    this.messages.push(msg);
                                    this.lastMessageId = msg.id;
                                }
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        } catch (e) {
                            console.error('Failed to send:', e);
                            this.newMessage = body;
                        }
                        this.sending = false;
                    },

                    async markRead() {
                        if (!this.roomId) return;
                        await fetch('/api/v1/chat/rooms/' + this.roomId + '/read', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            credentials: 'include',
                        });
                    },

                    scrollToBottom() {
                        const el = this.$refs.widgetMessages;
                        if (el) el.scrollTop = el.scrollHeight;
                    }
                };
            }
        </script>

    @endif
@endauth