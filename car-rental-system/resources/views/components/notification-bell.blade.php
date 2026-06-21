@php
    $isRtl = in_array(app()->getLocale(), ['fa', 'ps']);
    $locale = app()->getLocale();
@endphp

<div x-data="notificationBell()" x-init="init()" class="relative">

    {{-- Bell Button --}}
    <button @click="toggleOpen()" class="relative flex items-center justify-center w-9 h-9 rounded-lg
                   text-gray-500 hover:text-indigo-600 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6
                  6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455
                  1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1
                     bg-red-500 text-white text-[10px] font-bold rounded-full
                     flex items-center justify-center">
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="isOpen" x-cloak @click.outside="isOpen = false" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="absolute mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100
                z-50 max-h-[420px] flex flex-col"
        style="{{ $isRtl ? 'left: 0; right: auto;' : 'right: 0; left: auto;' }} direction: {{ $isRtl ? 'rtl' : 'ltr' }};">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <p class="font-semibold text-gray-900 text-sm">
                @if($locale === 'fa') اعلان‌ها
                @elseif($locale === 'ps') خبرتیاوې
                @else Notifications
                @endif
            </p>
            <button @click="markAllRead()" x-show="unreadCount > 0"
                class="text-xs text-indigo-600 hover:underline font-medium">
                {{ __('notifications.mark_all_read') }}
            </button>
        </div>

        {{-- List --}}
        <div class="overflow-y-auto flex-1">

            <template x-if="loading">
                <div class="text-center text-xs text-gray-400 py-8">
                    @if($locale === 'fa') در حال بارگذاری...
                    @elseif($locale === 'ps') بارېږي...
                    @else Loading...
                    @endif
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="text-center text-sm text-gray-400 py-10 px-4">
                    {{ __('notifications.no_notifications') }}
                </div>
            </template>

            <template x-for="n in notifications" :key="n.id">
                <a :href="n.link || '#'" @click="markRead(n)"
                    class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors"
                    :class="!n.is_read ? 'bg-indigo-50/40' : ''">
                    <div class="flex items-start gap-2.5">
                        <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                            :class="!n.is_read ? 'bg-indigo-500' : 'bg-transparent'"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900" x-text="n.title"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="n.body"></p>
                            <p class="text-[11px] text-gray-400 mt-1" x-text="n.created_at_human"></p>
                        </div>
                    </div>
                </a>
            </template>

        </div>
    </div>
</div>

<script>
    function notificationBell() {
        return {
            isOpen: false,
            loading: false,
            notifications: [],
            unreadCount: 0,
            csrf: document.querySelector('meta[name="csrf-token"]').content,

            async init() {
                await this.fetchNotifications();

                // Real-time listener
                if (window.Echo) {
                    window.Echo.private('notifications.{{ auth()->id() }}')
                        .listen('NewNotification', (e) => {
                            this.notifications.unshift(e.notification);
                            this.unreadCount++;
                            this.playPing();
                        });
                }
            },

            async toggleOpen() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    await this.fetchNotifications();
                }
            },

            async fetchNotifications() {
                this.loading = true;
                try {
                    const res = await fetch('/api/v1/notifications', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                        },
                        credentials: 'include',
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.notifications = data.notifications;
                        this.unreadCount = data.unread_count;
                    }
                } catch (e) {
                    console.error('Failed to load notifications:', e);
                }
                this.loading = false;
            },

            async markRead(n) {
                if (n.is_read) return;
                n.is_read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                try {
                    await fetch('/api/v1/notifications/' + n.id + '/read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'include',
                    });
                } catch (e) { /* silent */ }
            },

            async markAllRead() {
                this.notifications.forEach(n => n.is_read = true);
                this.unreadCount = 0;
                try {
                    await fetch('/api/v1/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'include',
                    });
                } catch (e) { /* silent */ }
            },

            playPing() {
                // subtle visual pulse instead of sound
                const bell = this.$root.querySelector('button');
                if (bell) {
                    bell.classList.add('animate-pulse');
                    setTimeout(() => bell.classList.remove('animate-pulse'), 1000);
                }
            },
        };
    }
</script>