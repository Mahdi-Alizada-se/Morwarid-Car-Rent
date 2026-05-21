{{-- Chatbot Widget — Morwarid Car Rental AI Assistant --}}
<div x-data="chatbotWidget()" x-init="init()" style="position: fixed; bottom: 24px; right: 96px; z-index: 9998;">

    {{-- ─── Floating Button ────────────────────────────────────────────────── --}}
    <div class="flex flex-col items-center">
        <button @click="toggleOpen()" title="AI Assistant" style="width:56px;height:56px;border-radius:50%;border:none;cursor:pointer;
                       background:linear-gradient(to bottom right, #8b5cf6, #9333ea);
                       box-shadow:0 4px 20px rgba(139,92,246,0.5);
                       display:flex;align-items:center;justify-content:center;
                       position:relative;transition:transform 0.2s;" @mouseenter="$el.style.transform='scale(1.1)'"
            @mouseleave="$el.style.transform='scale(1)'">

            {{-- Sparkle AI Icon --}}
            <svg class="w-6 h-6" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
            </svg>

            {{-- Unread badge --}}
            <template x-if="unreadCount > 0">
                <span style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;
                             width:20px;height:20px;border-radius:50%;font-size:11px;font-weight:700;
                             display:flex;align-items:center;justify-content:center;" x-text="unreadCount">
                </span>
            </template>
        </button>
        <p class="text-center text-white text-xs mt-1" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">AI</p>
    </div>

    {{-- ─── Chat Panel ─────────────────────────────────────────────────────── --}}
    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translateY(16px)" x-transition:enter-end="opacity-100 translateY(0)"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" style="position:absolute;bottom:68px;right:0;width:340px;height:480px;
                background:white;border-radius:16px;
                box-shadow:0 20px 60px rgba(0,0,0,0.2);
                border:1px solid #e5e7eb;
                display:flex;flex-direction:column;overflow:hidden;">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#8b5cf6,#9333ea);
                    padding:14px 16px;display:flex;align-items:center;
                    justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg class="w-5 h-5" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
                <div>
                    <p style="color:white;font-weight:700;font-size:14px;margin:0;">AI Assistant</p>
                    <div style="display:flex;align-items:center;gap:5px;margin-top:2px;">
                        <span
                            :style="`width:8px;height:8px;border-radius:50%;background:${isOnline ? '#4ade80' : '#f87171'}`"></span>
                        <span style="color:rgba(255,255,255,0.8);font-size:11px;"
                            x-text="isOnline ? 'Online' : 'Offline'">
                        </span>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <button @click="clearHistory()" title="New Chat" style="background:rgba(255,255,255,0.15);border:none;color:white;
                               width:30px;height:30px;border-radius:8px;cursor:pointer;
                               font-size:14px;display:flex;align-items:center;justify-content:center;">
                    🔄
                </button>
                <button @click="isOpen = false" style="background:rgba(255,255,255,0.15);border:none;color:white;
                               width:30px;height:30px;border-radius:8px;cursor:pointer;
                               font-size:18px;display:flex;align-items:center;justify-content:center;">
                    ×
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div x-ref="messagesArea"
            style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;">

            {{-- Welcome message --}}
            <template x-if="messages.length === 0">
                <div style="background:#f5f3ff;border-radius:12px;padding:14px;border-left:3px solid #8b5cf6;">
                    <p style="font-size:13px;color:#1f2937;margin:0;line-height:1.5;">
                        👋 <strong>Welcome to Morwarid Car Rental!</strong><br><br>
                        I can help you with:<br>
                        • 📋 Required documents<br>
                        • 💰 Pricing and payment<br>
                        • 📅 Booking and cancellation<br>
                        • <svg class="w-4 h-4 inline-block mr-1" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15.7639,4 C16.9002,4 17.939,4.64201 18.4472,5.65836 L19.8297,8.42332 C20.0735,8.32394 20.3168,8.22155 20.5532,8.10538 C21.0471,7.85869 21.6475,8.05894 21.8944,8.55279 C22.1414,9.04676 21.9412,9.64744 21.4472,9.89443 C20.9532,10.1414 20.7265,10.2169 20.7265,10.2169 L21.6833,12.1305 C21.8915,12.5471 22,13.0064 22,13.4721 L22,16 C22,16.8885 21.6137,17.6868 21,18.2361 L21,19.5 C21,20.3284 20.3284,21 19.5,21 C18.6715,21 18,20.3284 18,19.5 L18,19 L5.99998,19 L5.99998,19.5 C5.99998,20.3284 5.3284,21 4.49997,21 C3.67155,21 2.99997,20.3284 2.99997,19.5 L2.99997,18.2361 C2.38623,17.6868 1.99997,16.8885 1.99997,16 L1.99997,13.4721 C1.99997,13.0064 2.10841,12.5471 2.31669,12.1305 L3.2735,10.2169 C3.03141,10.116 2.79108,10.0105 2.55525,9.89567 C2.05878,9.64744 1.85856,9.04676 2.10555,8.55279 C2.35213,8.05962 2.96121,7.86667 3.4517,8.10779 C3.68712,8.22182 3.92811,8.3246 4.17028,8.42332 L5.55276,5.65836 C6.06094,4.64201 7.09973,4 8.23604,4 Z M18.8341,10.9044 C17.1339,11.4406 14.715,12 12,12 C9.28499,12 6.86601,11.4406 5.16583,10.9044 L4.10555,13.0249 C4.03612,13.1638 3.99997,13.3169 3.99997,13.4721 L3.99997,16 C3.99997,16.5523 4.44769,17 4.99997,17 L19,17 C19.5523,17 20,16.5523 20,16 L20,13.4721 C20,13.3169 19.9638,13.1638 19.8944,13.0249 Z M7.49997,13 C8.3284,13 8.99997,13.6716 8.99997,14.5 C8.99997,15.3284 8.3284,16 7.49997,16 C6.67155,16 5.99997,15.3284 5.99997,14.5 C5.99997,13.6716 6.67155,13 7.49997,13 Z M16.5,13 C17.3284,13 18,13.6716 18,14.5 C18,15.3284 17.3284,16 16.5,16 C15.6715,16 15,15.3284 15,14.5 C15,13.6716 15.6715,13 16.5,13 Z M15.7639,6 L8.23604,6 C7.85727,6 7.51101,6.214 7.34162,6.55279 L6.07258,9.09086 C7.61992,9.55498 9.70503,10 12,10 C14.2949,10 16.38,9.55498 17.9274,9.09086 L16.6583,6.55279 C16.4889,6.214 16.1427,6 15.7639,6 Z"
                                fill="#4f46e5" />
                        </svg> Vehicle information<br> Vehicle information<br>
                        • - <svg class="w-4 h-4 inline-block mr-1" viewBox="0 0 14 14"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="m 10.78125,0 -0.625,0.71875 1.1875,1.09375 c 0.03621,0.036212 0.0856,0.084693 0.125,0.125 l -0.25,0.28125 C 10.818532,2.6189681 11.105689,3.1369332 11.25,3.28125 L 12,4.03125 12,10 c 0,1 -0.392136,1 -0.5,1 C 11.392136,11 11,11 11,10 L 11,6 C 11,4.7190916 10,4 9,4 L 9,2 C 9,1.4486964 8.575273,1 8,1 L 2,1 C 1.400757,1 1,1.4247267 1,2 l 0,12 8,0 0,-9 c 0,0 1,0 1,1 l 0,4 c 0,2 1.239698,2 1.5,2 0.275652,0 1.5,0 1.5,-2 L 13,3 C 13,2 12.713983,1.7907839 12.375,1.46875 L 10.78125,0 z M 2,3 8,3 8,6 2,6 2,3 z"
                                fill="#4f46e5" />
                        </svg> Fuel policy<br><br><br>
                        Ask me anything!
                    </p>
                </div>
            </template>

            {{-- Message list --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div :style="`display:flex;justify-content:${msg.role === 'user' ? 'flex-end' : 'flex-start'}`">
                    <div :style="`
                        max-width:80%;
                        padding:10px 14px;
                        border-radius:${msg.role === 'user' ? '16px 16px 4px 16px' : '16px 16px 16px 4px'};
                        background:${msg.role === 'user' ? 'linear-gradient(135deg,#8b5cf6,#9333ea)' : '#f3f4f6'};
                        color:${msg.role === 'user' ? 'white' : '#1f2937'};
                        font-size:13px;
                        line-height:1.5;
                        white-space:pre-wrap;
                        word-break:break-word;
                    `" x-text="msg.content">
                    </div>
                </div>
            </template>

            {{-- Typing animation --}}
            <template x-if="isLoading">
                <div style="display:flex;justify-content:flex-start;">
                    <div style="background:#f3f4f6;padding:10px 16px;border-radius:16px 16px 16px 4px;
                                display:flex;align-items:center;gap:4px;">
                        <span style="width:7px;height:7px;background:#9ca3af;border-radius:50%;
                                     animation:bounce 1.2s infinite;display:inline-block;"></span>
                        <span style="width:7px;height:7px;background:#9ca3af;border-radius:50%;
                                     animation:bounce 1.2s infinite 0.2s;display:inline-block;"></span>
                        <span style="width:7px;height:7px;background:#9ca3af;border-radius:50%;
                                     animation:bounce 1.2s infinite 0.4s;display:inline-block;"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input Area --}}
        <div style="padding:12px;border-top:1px solid #e5e7eb;flex-shrink:0;background:white;">
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <textarea x-model="inputText" @keydown.enter.prevent="if(!$event.shiftKey && !isLoading) sendMessage()"
                    :disabled="isLoading" placeholder="Ask me anything..." rows="2" style="flex:1;border:1px solid #e5e7eb;border-radius:12px;
                           padding:8px 12px;font-size:13px;resize:none;
                           font-family:inherit;outline:none;line-height:1.4;
                           transition:border-color 0.2s;" @focus="$el.style.borderColor='#8b5cf6'"
                    @blur="$el.style.borderColor='#e5e7eb'">
                </textarea>
                <button @click="sendMessage()" :disabled="!inputText.trim() || isLoading" :style="`
                            width:38px;height:38px;border-radius:10px;border:none;
                            cursor:${!inputText.trim() || isLoading ? 'not-allowed' : 'pointer'};
                            background:${!inputText.trim() || isLoading ? '#e5e7eb' : 'linear-gradient(135deg,#8b5cf6,#9333ea)'};
                            color:${!inputText.trim() || isLoading ? '#9ca3af' : 'white'};
                            font-size:16px;display:flex;align-items:center;justify-content:center;
                            flex-shrink:0;transition:all 0.2s;
                        `">
                    ➤
                </button>
            </div>
            <p style="font-size:10px;color:#9ca3af;margin:5px 0 0;text-align:center;">
                Enter to send · Shift+Enter for new line
            </p>
        </div>
    </div>

</div>

<style>
    @keyframes bounce {

        0%,
        60%,
        100% {
            transform: translateY(0);
        }

        30% {
            transform: translateY(-6px);
        }
    }
</style>

<script>
    function chatbotWidget() {
        return {
            isOpen: false,
            isLoading: false,
            isOnline: false,
            messages: [],
            inputText: '',
            sessionId: '',
            unreadCount: 0,
            healthTimer: null,

            async init() {
                let stored = localStorage.getItem('chatbot_session_id');
                if (!stored) {
                    stored = 'session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
                    localStorage.setItem('chatbot_session_id', stored);
                }
                this.sessionId = stored;
                await this.loadHistory();
                await this.checkHealth();
                this.healthTimer = setInterval(() => this.checkHealth(), 60000);
            },

            async toggleOpen() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.unreadCount = 0;
                    await this.checkHealth();
                    this.$nextTick(() => this.scrollToBottom());
                }
            },

            async checkHealth() {
                try {
                    const res = await fetch('/api/v1/chatbot/health', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    this.isOnline = data.online === true;
                } catch (e) {
                    this.isOnline = false;
                }
            },

            async loadHistory() {
                try {
                    const res = await fetch(`/api/v1/chatbot/history/${this.sessionId}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.messages && data.messages.length > 0) {
                        this.messages = data.messages.map(m => ({
                            role: m.role,
                            content: m.content,
                        }));
                    }
                } catch (e) {
                    // No history yet
                }
            },

            async sendMessage() {
                const text = this.inputText.trim();
                if (!text || this.isLoading) return;

                this.messages.push({ role: 'user', content: text });
                this.inputText = '';
                this.isLoading = true;

                this.$nextTick(() => this.scrollToBottom());

                const aiIndex = this.messages.length;
                this.messages.push({ role: 'assistant', content: '' });

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    const response = await fetch('/api/v1/chatbot/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'text/event-stream',
                        },
                        body: JSON.stringify({
                            message: text,
                            session_id: this.sessionId,
                        }),
                    });

                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';

                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;

                        buffer += decoder.decode(value, { stream: true });
                        const lines = buffer.split('\n');
                        buffer = lines.pop() ?? '';

                        for (const line of lines) {
                            const trimmed = line.trim();
                            if (!trimmed.startsWith('data: ')) continue;
                            const jsonStr = trimmed.substring(6);
                            try {
                                const data = JSON.parse(jsonStr);
                                if (data.delta) {
                                    this.messages[aiIndex].content += data.delta;
                                    this.$nextTick(() => this.scrollToBottom());
                                }
                                if (data.done === true) {
                                    this.isLoading = false;
                                    if (!this.isOpen) this.unreadCount++;
                                }
                            } catch (parseErr) { }
                        }
                    }
                } catch (e) {
                    console.error('Chatbot error:', e);
                    this.messages[aiIndex].content = 'Sorry, something went wrong. Please try again.';
                }

                this.isLoading = false;
                this.$nextTick(() => this.scrollToBottom());
            },

            async clearHistory() {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    await fetch(`/api/v1/chatbot/history/${this.sessionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) { }
                this.messages = [];
            },

            scrollToBottom() {
                const area = this.$refs.messagesArea;
                if (area) area.scrollTop = area.scrollHeight;
            },
        };
    }
</script>