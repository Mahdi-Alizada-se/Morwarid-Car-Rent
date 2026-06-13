<!DOCTYPE html>
@php
    $isRtl = in_array(app()->getLocale(), ['fa', 'ps']);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — {{ __('common.admin_panel') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @if(in_array(app()->getLocale(), ['fa', 'ps']))
            body {
                font-family: 'Tahoma', 'Arial', sans-serif;
                direction: rtl;
                text-align: right;
            }

        @endif
    </style>

    @stack('styles')
</head>

<body class="h-full" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">

        {{-- ─── Sidebar ──────────────────────────────────────────────────────────── --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200
                  flex flex-col transform transition-transform duration-200
                  lg:translate-x-0 lg:static lg:inset-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="flex items-center gap-3 h-16 px-5 border-b border-gray-200 flex-shrink-0">
                <a href="/" class="flex items-center gap-2">
                    <div class="rounded-xl px-2 py-1" style="background-color: #4F46E5;">
                        <img src="{{ asset('images/logo.png') }}" alt="Morwarid Car Rental"
                            class="h-12 w-auto object-contain">
                    </div>
                    <div class="leading-tight">
                        <span class="block text-base font-bold text-gray-800">Morwarid</span>
                        <span class="block text-xs font-medium text-blue-600 -mt-0.5">Car Rental</span>
                    </div>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Main
                </p>

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.dashboard')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0
                          01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016
                          13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25
                          0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25
                          6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5
                          15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0
                          0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span>{{ __('common.dashboard') }}</span>
                </a>

                {{-- Vehicles --}}
                <a href="{{ route('admin.vehicles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.vehicles.*')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 13H8M2 9L4 10L5.27064 6.18807C5.53292 5.40125 5.66405 5.00784
                             5.90729 4.71698C6.12208 4.46013 6.39792 4.26132 6.70951 4.13878C7.06236
                             4 7.47705 4 8.30643 4H15.6936C16.523 4 16.9376 4 17.2905 4.13878C17.6021
                             4.26132 17.8779 4.46013 18.0927 4.71698C18.3359 5.00784 18.4671 5.40125
                             18.7294 6.18807L20 10L22 9M16 13H19M6.8 10H17.2C18.8802 10 19.7202 10
                             20.362 10.327C20.9265 10.6146 21.3854 11.0735 21.673 11.638C22 12.2798
                             22 13.1198 22 14.8V17.5C22 17.9647 22 18.197 21.9616 18.3902C21.8038
                             19.1836 21.1836 19.8038 20.3902 19.9616C20.197 20 19.9647 20 19.5 20H19C17.8954
                             20 17 19.1046 17 18C17 17.7239 16.7761 17.5 16.5 17.5H7.5C7.22386 17.5
                             7 17.7239 7 18C7 19.1046 6.10457 20 5 20H4.5C4.03534 20 3.80302 20
                             3.60982 19.9616C2.81644 19.8038 2.19624 19.1836 2.03843 18.3902C2 18.197
                             2 17.9647 2 17.5V14.8C2 13.1198 2 12.2798 2.32698 11.638C2.6146 11.0735
                             3.07354 10.6146 3.63803 10.327C4.27976 10 5.11984 10 6.8 10Z" />
                    </svg>
                    <span>{{ __('common.nav_vehicles') }}</span>
                </a>

                {{-- Bookings --}}
                <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.bookings.*') && request('status') !== 'active'
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25
                          2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                          18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <span>{{ __('common.nav_bookings') }}</span>
                </a>

                {{-- Active Rentals --}}
                @php $activeRentalsCount = \App\Models\Booking::where('status', 'active')->count(); @endphp
                <a href="{{ route('admin.bookings.index', ['status' => 'active']) }}" id="active-rentals-link"
                    onclick="localStorage.setItem('active_rentals_seen', '{{ $activeRentalsCount }}')" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
          {{ request()->routeIs('admin.bookings.*') && request('status') === 'active'
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.5 7V13C20.5 16.7712 20.5 18.6569 19.3284 19.8284C18.1569 21
                 16.2712 21 12.5 21H11.5C7.72876 21 5.84315 21 4.67157 19.8284C3.5
                 18.6569 3.5 16.7712 3.5 13V7" />
                        <path d="M2 5C2 4.05719 2 3.58579 2.29289 3.29289C2.58579 3 3.05719 3 4
                 3H20C20.9428 3 21.4142 3 21.7071 3.29289C22 3.58579 22 4.05719 22
                 5C22 5.94281 22 6.41421 21.7071 6.70711C21.4142 7 20.9428 7 20 7H4C3.05719
                 7 2.58579 7 2.29289 6.70711C2 6.41421 2 5.94281 2 5Z" />
                        <path d="M9.5 13.4L10.9286 15L14.5 11" />
                    </svg>
                    <span class="flex-1">Active Rentals</span>
                    <span id="active-rentals-badge" class="hidden inline-flex items-center justify-center w-5 h-5
                 bg-green-500 text-white text-xs font-bold rounded-full">
                        {{ $activeRentalsCount }}
                    </span>
                </a>

                <script>
                    (function () {
                        const current = {{ $activeRentalsCount }};
                        const seen = parseInt(localStorage.getItem('active_rentals_seen') || '0');
                        const badge = document.getElementById('active-rentals-badge');
                        const isOnPage = {{ (request()->routeIs('admin.bookings.*') && request('status') === 'active') ? 'true' : 'false' }};

                        if (isOnPage) {
                            // Clear seen count when on the page
                            localStorage.setItem('active_rentals_seen', current.toString());
                        } else if (current > seen && badge) {
                            // Show badge only when count increased
                            badge.classList.remove('hidden');
                            badge.textContent = current - seen;
                        }
                    })();
                </script>

                {{-- Payments --}}
                <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.payments.*')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89
                             2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                    </svg>
                    <span>{{ __('common.nav_payments') }}</span>
                </a>

                {{-- Users --}}
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.users.*')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125
                          0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318
                          12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0
                          0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25
                          2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span>Users</span>
                </a>

                {{-- Chat --}}
                @php
                    $totalUnreadMessages = \App\Models\Message::whereHas('chatRoom')
                        ->where('is_read', false)
                        ->whereHas('sender', fn($q) => $q->where('role', 'customer'))
                        ->count();
                @endphp
                <a href="{{ route('admin.chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.chat.*')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125
                          0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0
                          11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994
                          2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0
                          01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626
                          2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0
                          0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25
                          6.741v6.018z" />
                    </svg>
                    <span class="flex-1">{{ __('common.nav_chat') }}</span>
                    @if($totalUnreadMessages > 0)
                        <span class="chat-nav-badge w-5 h-5 bg-red-500 text-white text-xs font-bold
                                         rounded-full flex items-center justify-center flex-shrink-0">
                            {{ $totalUnreadMessages > 9 ? '9+' : $totalUnreadMessages }}
                        </span>
                    @endif
                </a>

                {{-- GPS Tracking --}}
                <a href="{{ route('admin.gps.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.gps.*')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0
                          1115 0z" />
                    </svg>
                    <span>{{ __('common.nav_gps_tracking') }}</span>
                </a>

                {{-- Reports --}}
                <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                      {{ request()->routeIs('admin.reports*')
    ? 'bg-blue-600 text-white font-medium shadow-sm'
    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125
                          1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013
                          19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0
                          1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125
                          1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125
                          1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125
                          1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    <span>{{ __('common.nav_reports') }}</span>
                </a>

            </nav>

            {{-- User Info --}}
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center
                            text-blue-700 font-semibold text-sm flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ __('common.administrator') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors"
                            title="{{ __('common.logout') }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25
                                  2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12
                                  9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Sidebar Overlay (mobile) --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden">
        </div>

        {{-- ─── Main Content ─────────────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Navbar --}}
            <header class="h-16 bg-white border-b border-gray-200 flex items-center
                       gap-4 px-4 lg:px-6 flex-shrink-0">

                {{-- Mobile menu toggle --}}
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1">
                    <h1 class="text-lg font-semibold text-gray-900">
                        @yield('page-title', __('common.dashboard'))
                    </h1>
                </div>

                {{-- Language Switcher --}}
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all
                                   {{ app()->getLocale() === 'en'
    ? 'bg-white text-blue-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                            EN
                        </button>
                    </form>
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="fa">
                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all
                                   {{ app()->getLocale() === 'fa'
    ? 'bg-white text-blue-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                            FA
                        </button>
                    </form>
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="ps">
                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all
                                   {{ app()->getLocale() === 'ps'
    ? 'bg-white text-blue-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                            PS
                        </button>
                    </form>
                </div>

                {{-- Breadcrumb --}}
                <nav class="hidden md:flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">
                        {{ __('common.admin_panel') }}
                    </a>
                    @hasSection('breadcrumb')
                        <span>/</span>
                        @yield('breadcrumb')
                    @endif
                </nav>

            </header>

            {{-- Flash Messages --}}
            <div class="px-4 lg:px-6 pt-4">
                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-green-50 border
                                    border-green-200 text-green-800 rounded-lg text-sm">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-red-50 border
                                    border-red-200 text-red-800 rounded-lg text-sm">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto px-4 lg:px-6 py-4">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Echo + Reverb --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const PusherClient = Pusher;

        window.Echo = {
            _channels: {},

            private(channel) {
                const key = 'private-' + channel;
                const pusher = new PusherClient('{{ config('broadcasting.connections.reverb.key') }}', {
                    wsHost: '{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}',
                    wsPort:            {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                    wssPort:           {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                    forceTLS: false,
                    enabledTransports: ['ws'],
                    cluster: 'mt1',
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    }
                });

                const sub = pusher.subscribe(key);

                return {
                    listen(event, callback) {
                        const eventName = event.startsWith('.') ? event.slice(1) : event;
                        sub.bind(eventName, callback);
                        return this;
                    }
                };
            }
        };
    </script>

    @stack('scripts')
</body>

</html>