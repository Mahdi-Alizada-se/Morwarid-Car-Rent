{{-- Chatbot Widget — Morwarid Car Rental AI Assistant --}}
@php
    $isRtl = in_array(app()->getLocale(), ['fa', 'ps']);
    $locale = app()->getLocale();
    $panelSide = $isRtl ? 'left:96px;right:auto;' : 'right:96px;left:auto;';
    $chatSide = $isRtl ? 'left:0;right:auto;' : 'right:0;left:auto;';
@endphp

<div x-data="chatbotWidget()" x-init="init()" style="position:fixed;bottom:24px;{{ $panelSide }}z-index:9998;">

    {{-- ─── Floating Button ────────────────────────────────────────────────── --}}
    <div class="flex flex-col items-center">
        <button @click="toggleOpen()" title="AI Assistant" style="width:56px;height:56px;border-radius:50%;border:none;cursor:pointer;
                       background:linear-gradient(to bottom right,#8b5cf6,#9333ea);
                       box-shadow:0 4px 20px rgba(139,92,246,0.5);
                       display:flex;align-items:center;justify-content:center;
                       position:relative;transition:transform 0.2s;" @mouseenter="$el.style.transform='scale(1.1)'"
            @mouseleave="$el.style.transform='scale(1)'">
            <svg class="w-6 h-6" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0
                      003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0
                      00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25
                      6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456
                      2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
            </svg>
            <template x-if="unreadCount > 0">
                <span style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;
                             width:20px;height:20px;border-radius:50%;font-size:11px;font-weight:700;
                             display:flex;align-items:center;justify-content:center;" x-text="unreadCount"></span>
            </template>
        </button>
        <p class="text-center text-white text-xs mt-1" style="text-shadow:0 1px 3px rgba(0,0,0,0.5);">AI</p>
    </div>

    {{-- ─── Chat Panel ─────────────────────────────────────────────────────── --}}
    <div :style="isOpen
            ? 'display:flex;flex-direction:column;position:absolute;bottom:68px;{{ $chatSide }}width:360px;height:520px;background:white;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);border:1px solid #e5e7eb;overflow:hidden;direction:ltr;'
            : 'display:none;'">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#8b5cf6,#9333ea);
                    padding:14px 16px;display:flex;align-items:center;
                    justify-content:space-between;flex-shrink:0;min-height:56px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg class="w-5 h-5" fill="none" stroke="white" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5
                          4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5
                          4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0
                          00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259
                          1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0
                          00-2.456 2.456z" />
                </svg>
                <div>
                    <p style="color:white;font-weight:700;font-size:14px;margin:0;">
                        @if($locale === 'fa') دستیار هوش مصنوعی
                        @elseif($locale === 'ps') د AI مرستیال
                        @else AI Assistant
                        @endif
                    </p>
                    <div style="display:flex;align-items:center;gap:5px;margin-top:2px;">
                        <span
                            :style="`width:8px;height:8px;border-radius:50%;background:${isOnline ? '#4ade80' : '#f87171'}`"></span>
                        <span style="color:rgba(255,255,255,0.8);font-size:11px;" x-text="isOnline
                                ? '{{ $locale === 'fa' ? 'آنلاین' : ($locale === 'ps' ? 'آنلاین' : 'Online') }}'
                                : '{{ $locale === 'fa' ? 'آفلاین' : ($locale === 'ps' ? 'آفلاین' : 'Offline') }}'">
                        </span>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <button @click="clearHistory()" title="{{ $locale === 'fa' ? 'چت جدید' : 'New Chat' }}" style="background:rgba(255,255,255,0.15);border:none;color:white;
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
        <div x-ref="messagesArea" style="width:100%;height:360px;overflow-y:auto;overflow-x:hidden;
                    padding:16px;box-sizing:border-box;flex-shrink:0;">

            {{-- Welcome message --}}
            <template x-if="messages.length === 0">
                <div style="background:#f5f3ff;border-radius:12px;padding:14px;
                            border-left:3px solid #8b5cf6;margin-bottom:12px;">
                    @if($locale === 'fa')
                        <p
                            style="font-size:13px;color:#1f2937;margin:0;line-height:1.8;direction:rtl;text-align:right;font-family:'Vazirmatn',Tahoma,Arial,sans-serif;">
                            👋 <strong>خوش آمدید به مروارید کرایه خودرو!</strong><br><br>
                            می‌توانم در موارد زیر کمک کنم:<br>
                            • 📋 مدارک مورد نیاز<br>
                            • 💰 قیمت‌ها و پرداخت<br>
                            • 📅 رزرو و لغو<br>
                            • 🚗 اطلاعات خودرو<br>
                            • ⛽ سیاست سوخت<br>
                            • 🌍 هر سوال دیگری<br><br>
                            هر چیزی می‌خواهید بپرسید!
                        </p>
                    @elseif($locale === 'ps')
                        <p
                            style="font-size:13px;color:#1f2937;margin:0;line-height:1.8;direction:rtl;text-align:right;font-family:'Vazirmatn',Tahoma,Arial,sans-serif;">
                            👋 <strong>مروارید کار کرایه ته ښه راغلاست!</strong><br><br>
                            زه کولی شم د لاندې مواردو سره مرسته وکړم:<br>
                            • 📋 اړین اسناد<br>
                            • 💰 نرخونه او تادیه<br>
                            • 📅 بکینګ او لغول<br>
                            • 🚗 د موټر معلومات<br>
                            • ⛽ د تیلو پالیسي<br>
                            • 🌍 هر بل پوښتنه<br><br>
                            راته پوښتنه وکړئ!
                        </p>
                    @else
                        <p style="font-size:13px;color:#1f2937;margin:0;line-height:1.6;">
                            👋 <strong>Welcome to Morwarid Car Rental!</strong><br><br>
                            I can help you with:<br>
                            • 📋 Required documents<br>
                            • 💰 Pricing and payment<br>
                            • 📅 Booking and cancellation<br>
                            • 🚗 Vehicle information<br>
                            • ⛽ Fuel policy<br>
                            • 🌍 Any general question<br><br>
                            Ask me anything!
                        </p>
                    @endif
                </div>
            </template>

            {{-- Message list --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div
                    :style="`display:flex;margin-bottom:12px;justify-content:${msg.role === 'user' ? 'flex-end' : 'flex-start'}`">
                    <div :style="`
                        max-width:85%;
                        padding:10px 14px;
                        border-radius:${msg.role === 'user' ? '16px 16px 4px 16px' : '16px 16px 16px 4px'};
                        background:${msg.role === 'user' ? 'linear-gradient(135deg,#8b5cf6,#9333ea)' : '#f3f4f6'};
                        color:${msg.role === 'user' ? 'white' : '#1f2937'};
                        font-size:13px;
                        line-height:1.6;
                        word-break:break-word;
                        overflow-wrap:break-word;
                        direction:auto;
                        text-align:start;
                        font-family:'Vazirmatn',Tahoma,system-ui,sans-serif;
                    `" x-html="renderMarkdown(msg.content)">
                    </div>
                </div>
            </template>

            {{-- Typing animation --}}
            <template x-if="isLoading">
                <div style="display:flex;justify-content:flex-start;margin-bottom:12px;">
                    <div style="background:#f3f4f6;padding:10px 16px;
                                border-radius:16px 16px 16px 4px;
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
        <div style="padding:12px;border-top:1px solid #e5e7eb;
                    flex-shrink:0;background:white;min-height:80px;">
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <textarea x-model="inputText" @keydown.enter.prevent="if(!$event.shiftKey && !isLoading) sendMessage()"
                    :disabled="isLoading"
                    placeholder="{{ $locale === 'fa' ? 'سوال خود را بنویسید...' : ($locale === 'ps' ? 'پوښتنه ولیکئ...' : 'Ask me anything...') }}"
                    rows="2" style="flex:1;border:1px solid #e5e7eb;border-radius:12px;
                                 padding:8px 12px;font-size:13px;resize:none;
                                 font-family:'Vazirmatn',Tahoma,system-ui,sans-serif;
                                 outline:none;line-height:1.4;
                                 transition:border-color 0.2s;
                                 direction:{{ $isRtl ? 'rtl' : 'ltr' }};" @focus="$el.style.borderColor='#8b5cf6'"
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
                @if($locale === 'fa')
                    Enter برای ارسال · Shift+Enter برای خط جدید
                @elseif($locale === 'ps')
                    Enter لیږلو لپاره · Shift+Enter د نوې کرښې لپاره
                @else
                    Enter to send · Shift+Enter for new line
                @endif
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
                const stored = 'session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
                localStorage.setItem('chatbot_session_id', stored);
                this.sessionId = stored;
                await this.checkHealth();
                this.healthTimer = setInterval(() => this.checkHealth(), 60000);
            },

            async toggleOpen() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.unreadCount = 0;
                    await this.checkHealth();
                    setTimeout(() => this.scrollToBottom(), 200);
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

            async sendMessage() {
                const text = this.inputText.trim();
                if (!text || this.isLoading) return;

                this.messages.push({ role: 'user', content: text });
                this.inputText = '';
                this.isLoading = true;
                this.scrollToBottom();

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
                                const parsed = JSON.parse(jsonStr);
                                if (parsed.delta) {
                                    this.messages[aiIndex].content += parsed.delta;
                                    this.scrollToBottom();
                                }
                                if (parsed.done === true) {
                                    this.isLoading = false;
                                    if (!this.isOpen) this.unreadCount++;
                                }
                            } catch (e) { }
                        }
                    }

                } catch (e) {
                    console.error('Chatbot error:', e);
                    this.messages[aiIndex].content = '{{ $locale === 'fa'
    ? 'متأسفم، مشکلی پیش آمد. لطفاً دوباره تلاش کنید.'
    : 'Sorry, something went wrong. Please try again.' }}';
                }

                this.isLoading = false;
                this.scrollToBottom();
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

            renderMarkdown(text) {
                if (!text) return '';
                return text
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/^\d+\.\s+(.*)$/gm, '<div style="margin:3px 0;">$1</div>')
                    .replace(/^[•\-]\s+(.*)$/gm, '<div style="margin:3px 0;">• $1</div>')
                    .replace(/\n/g, '<br>');
            },

            scrollToBottom() {
                setTimeout(() => {
                    const area = this.$refs.messagesArea;
                    if (area) area.scrollTop = area.scrollHeight;
                }, 80);
            },
        };
    }
</script>