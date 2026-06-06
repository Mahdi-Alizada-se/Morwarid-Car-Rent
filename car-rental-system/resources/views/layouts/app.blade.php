<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', __('Car Rental'))</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="flex flex-col min-h-screen bg-gray-50" x-data="{ mobileMenuOpen: false }">

    {{-- ─── Navbar ───────────────────────────────────────────────────────────── --}}
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2">
                    <div class="rounded-xl px-2 py-1" style="background-color: #4F46E5;">
                        <img src="{{ asset('images/logo.png') }}" alt="Morwarid Car Rental"
                            class="h-12 w-auto object-contain">
                    </div>
                    <div class="leading-tight">
                        <span class="block text-lg font-bold text-gray-800">Morwarid</span>
                        <span class="block text-xs font-medium text-blue-600 -mt-1">Car Rental</span>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('vehicles.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                        {{ __('common.nav_vehicles') }}
                    </a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors">
                        {{ __('common.nav_about') }}
                    </a>
                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors">
                        {{ __('common.nav_contact') }}
                    </a>
                </nav>

                {{-- Auth Buttons --}}
                <div class="hidden md:flex items-center gap-3">

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
                    </div>

                    {{-- Chat Widget in Navbar --}}
                    @include('components.chat-widget')

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium
                                                   text-gray-700 hover:text-blue-600">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white
                                                                flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                {{ auth()->user()->name }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg
                                                border border-gray-100 py-1 z-50">
                                <a href="{{ route('customer.bookings.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    {{ __('common.my_bookings') }}
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-50">
                                        {{ __('common.admin_panel') }}
                                    </a>
                                @endif
                                <hr class="my-1 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm
                                                           text-red-600 hover:bg-gray-50">
                                        {{ __('common.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                            {{ __('common.login') }}
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-medium bg-blue-600 text-white px-4 py-2
                                          rounded-lg hover:bg-blue-700 transition-colors"
                            style="background-color: #4F46E5;">
                            {{ __('common.register') }}
                        </a>
                    @endauth
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-100 py-3 space-y-1">

                {{-- Language Switcher Mobile --}}
                <div class="flex items-center gap-2 px-4 py-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ __('common.language') }}:
                    </span>
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
                    </div>
                </div>

                <a href="{{ route('vehicles.index') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                    {{ __('common.nav_vehicles') }}
                </a>

                @auth
                    <a href="{{ route('customer.bookings.index') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        {{ __('common.my_bookings') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm
                                               text-red-600 hover:bg-gray-50 rounded-lg">
                            {{ __('common.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        {{ __('common.login') }}
                    </a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 text-sm font-medium text-blue-600
                                      hover:bg-blue-50 rounded-lg">
                        {{ __('common.register') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ─── Flash Messages ───────────────────────────────────────────────────── --}}
    @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border
                                            border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border
                                            border-red-200 text-red-800 rounded-lg text-sm">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    @endif

    {{-- ─── Main Content ─────────────────────────────────────────────────────── --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ─── Footer ───────────────────────────────────────────────────────────── --}}
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="rounded-xl px-2 py-1" style="background-color: #4F46E5;">
                            <img src="{{ asset('images/logo.png') }}" alt="Morwarid Car Rental"
                                class="h-12 w-auto object-contain">
                        </div>
                        <div class="leading-tight">
                            <span class="block font-bold text-gray-800">Morwarid</span>
                            <span class="block text-xs font-medium text-blue-600 -mt-0.5">
                                Car Rental
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Your trusted car rental service in Kabul.</p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li>
                            <a href="{{ route('vehicles.index') }}" class="hover:text-blue-600 transition-colors">
                                Browse Vehicles
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 transition-colors">
                                How It Works
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 transition-colors">
                                Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">
                        {{ __('common.nav_contact') }}
                    </h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li>{{ config('company.address', 'Dasht-e-Barchi, Kabul, Afghanistan') }}</li>
                        <li>info@carrental.com</li>
                        <li>{{ config('company.phone', '+93 700 000 000') }}</li>
                    </ul>
                </div>

            </div>
            <div class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-400">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- AI Chatbot Widget --}}
    <x-chatbot-widget />

    {{-- Echo + Reverb for real-time chat notifications --}}
    @auth
        @if(auth()->user()->isCustomer())
            <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
            <script>
                (function () {
                    var reverbKey = @json(config('broadcasting.connections.reverb.key'));
                    var reverbHost = @json(config('broadcasting.connections.reverb.options.host', 'localhost'));
                    var reverbPort = @json(config('broadcasting.connections.reverb.options.port', 8080));
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                    window.Echo = {
                        private: function (channel) {
                            var pusher = new Pusher(reverbKey, {
                                wsHost: reverbHost,
                                wsPort: reverbPort,
                                wssPort: reverbPort,
                                forceTLS: false,
                                enabledTransports: ['ws'],
                                cluster: 'mt1',
                                authEndpoint: '/broadcasting/auth',
                                auth: {
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                    }
                                }
                            });

                            var sub = pusher.subscribe('private-' + channel);

                            return {
                                listen: function (event, callback) {
                                    var eventName = event.startsWith('.') ? event.slice(1) : event;
                                    sub.bind(eventName, callback);
                                    return this;
                                }
                            };
                        }
                    };

                    console.log('Echo ready with key:', reverbKey);
                })();
            </script>
        @endif
    @endauth

    @stack('scripts')
</body>

</html>