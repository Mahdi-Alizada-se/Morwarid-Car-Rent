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
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h8l2-2zM15 7h2l3 5v4h-2M9 17H7" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 text-lg">{{ config('app.name') }}</span>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('vehicles.index') }}"
                        class="text-gray-600 hover:text-indigo-600 transition-colors">
                        {{ __('common.nav_vehicles') }}
                    </a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">
                        {{ __('common.nav_about') }}
                    </a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600 transition-colors">
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
    ? 'bg-white text-indigo-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                                EN
                            </button>
                        </form>
                        <form method="POST" action="{{ route('language.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="fa">
                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all
                    {{ app()->getLocale() === 'fa'
    ? 'bg-white text-indigo-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                                FA
                            </button>
                        </form>
                    </div>
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-indigo-600">
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                {{ auth()->user()->name }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                                <a href="{{ route('customer.bookings.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('common.my_bookings') }}</a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="block px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50">{{ __('Admin Panel') }}</a>
                                @endif
                                <hr class="my-1 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">{{ __('Logout') }}</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">{{ __('Login') }}</a>
                        <a href="{{ route('register') }}"
                            class="text-sm font-medium bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">{{ __('Register') }}</a>
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
    ? 'bg-white text-indigo-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                                EN
                            </button>
                        </form>
                        <form method="POST" action="{{ route('language.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="fa">
                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all
                        {{ app()->getLocale() === 'fa'
    ? 'bg-white text-indigo-600 shadow-sm'
    : 'text-gray-500 hover:text-gray-700' }}">
                                FA
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('vehicles.index') }}" <a href="{{ route('vehicles.index') }}"
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
                        <button type="submit"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 rounded-lg">
                            {{ __('common.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                        {{ __('common.login') }}
                    </a>
                    <a href="{{ route('register') }}"
                        class="block px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg">
                        {{ __('common.register') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div
                    class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
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
                        <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h8l2-2zM15 7h2l3 5v4h-2M9 17H7" />
                            </svg>
                        </div>
                        <span class="font-bold text-gray-900">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Your trusted car rental service.') }}</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">{{ __('Quick Links') }}</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('vehicles.index') }}"
                                class="hover:text-indigo-600 transition-colors">{{ __('Browse Vehicles') }}</a></li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">{{ __('How It Works') }}</a>
                        </li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">{{ __('Contact Us') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">{{ __('Contact') }}</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li>{{ __('Kabul, Afghanistan') }}</li>
                        <li>info@carrental.com</li>
                        <li>+93 700 000 000</li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-400">
                © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
            </div>
        </div>
    </footer>

    {{-- Chat Widget --}}
    @include('components.chat-widget')

    {{-- Chat Widget (Day 5) --}}
    @include('components.chat-widget')

    {{-- AI Chatbot Widget --}}
    <x-chatbot-widget />



    @stack('scripts')
</body>

</html>