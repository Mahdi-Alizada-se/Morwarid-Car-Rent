{{-- Chatbot Widget — Morwarid Car Rental AI Assistant --}}
<div x-data="chatbotWidget()" x-init="init()" style="position: fixed; bottom: 24px; left: 24px; z-index: 9999;">

    {{-- ─── Floating Button ────────────────────────────────────────────────── --}}
    <button @click="toggleOpen()" style="width:56px;height:56px;border-radius:50%;border:none;cursor:pointer;
                   background:linear-gradient(135deg,#4f46e5,#7c3aed);
                   box-shadow:0 4px 20px rgba(79,70,229,0.5);
                   display:flex;align-items:center;justify-content:center;
                   font-size:24px;transition:transform 0.2s;position:relative;"
        @mouseenter="$el.style.transform='scale(1.1)'" @mouseleave="$el.style.transform='scale(1)'">
        ✨
        {{-- Unread badge --}}
        <template x-if="unreadCount > 0">
            <span style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;
                         width:20px;height:20px;border-radius:50%;font-size:11px;font-weight:700;
                         display:flex;align-items:center;justify-content:center;" x-text="unreadCount">
            </span>
        </template>
    </button>

    {{-- ─── Chat Panel ─────────────────────────────────────────────────────── --}}
    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translateY(16px)" x-transition:enter-end="opacity-100 translateY(0)"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" style="position:absolute;bottom:68px;left:0;width:340px;height:480px;
                background:white;border-radius:16px;
                box-shadow:0 20px 60px rgba(0,0,0,0.2);
                border:1px solid #e5e7eb;
                display:flex;flex-direction:column;overflow:hidden;">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);
                    padding:14px 16px;display:flex;align-items:center;
                    justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:20px;">✨</span>
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
                <div style="background:#f0f0ff;border-radius:12px;padding:14px;border-left:3px solid #4f46e5;">
                    <p style="font-size:13px;color:#1f2937;margin:0;line-height:1.5;">
                        👋 <strong>Welcome to Morwarid Car Rental!</strong><br><br>
                        I can help you with:<br>
                        • 📋 Required documents<br>
                        • 💰 Pricing and payment<br>
                        • 📅 Booking and cancellation<br>
                        • 🚗 Vehicle information<br>
                        • ⛽ Fuel policy<br><br>
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
                        background:${msg.role === 'user' ? 'linear-gradient(135deg,#4f46e5,#7c3aed)' : '#f3f4f6'};
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
                           transition:border-color 0.2s;" @focus="$el.style.borderColor='#4f46e5'"
                    @blur="$el.style.borderColor='#e5e7eb'">
                </textarea>
                <button @click="sendMessage()" :disabled="!inputText.trim() || isLoading" :style="`
                            width:38px;height:38px;border-radius:10px;border:none;
                            cursor:${!inputText.trim() || isLoading ? 'not-allowed' : 'pointer'};
                            background:${!inputText.trim() || isLoading ? '#e5e7eb' : 'linear-gradient(135deg,#4f46e5,#7c3aed)'};
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

            // ─── Init ──────────────────────────────────────────────────────────────

            async init() {
                // Generate or load session ID from localStorage
                let stored = localStorage.getItem('chatbot_session_id');
                if (!stored) {
                    stored = 'session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
                    localStorage.setItem('chatbot_session_id', stored);
                }
                this.sessionId = stored;

                // Load existing history
                await this.loadHistory();

                // Check health
                await this.checkHealth();

                // Repeat health check every 60 seconds
                this.healthTimer = setInterval(() => this.checkHealth(), 60000);
            },

            // ─── Toggle Open ───────────────────────────────────────────────────────

            async toggleOpen() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.unreadCount = 0;
                    await this.checkHealth();
                    this.$nextTick(() => this.scrollToBottom());
                }
            },

            // ─── Check Ollama Health ───────────────────────────────────────────────

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

            // ─── Load History ──────────────────────────────────────────────────────

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
                    // No history yet — that is fine
                }
            },

            // ─── Send Message ──────────────────────────────────────────────────────

            async sendMessage() {
                const text = this.inputText.trim();
                if (!text || this.isLoading) return;

                // Add user message
                this.messages.push({ role: 'user', content: text });
                this.inputText = '';
                this.isLoading = true;

                this.$nextTick(() => this.scrollToBottom());

                // Add empty AI placeholder
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
                                    if (!this.isOpen) {
                                        this.unreadCount++;
                                    }
                                }
                            } catch (parseErr) {
                                // Skip malformed JSON lines
                            }
                        }
                    }

                } catch (e) {
                    console.error('Chatbot error:', e);
                    this.messages[aiIndex].content = 'Sorry, something went wrong. Please try again.';
                }

                this.isLoading = false;
                this.$nextTick(() => this.scrollToBottom());
            },

            // ─── Clear History ─────────────────────────────────────────────────────

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
                } catch (e) {
                    // Ignore errors
                }
                this.messages = [];
            },

            // ─── Scroll To Bottom ──────────────────────────────────────────────────

            scrollToBottom() {
                const area = this.$refs.messagesArea;
                if (area) {
                    area.scrollTop = area.scrollHeight;
                }
            },
        };
    }
</script>