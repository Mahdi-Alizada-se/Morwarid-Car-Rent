@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Welcome Message --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">

            <div class="flex items-center justify-center mx-auto mb-4">
                <div class="rounded-xl px-2 py-1" style="background-color: #4F46E5;">
                    <img src="{{ asset('images/logo.png') }}" alt="Morwarid Car Rental" class="h-12 w-auto object-contain">
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                {{ __('common.welcome') }}, {{ auth()->user()->name }}!
            </h1>

            <p class="text-gray-500 mb-6">
                {{ __('common.app_tagline') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white
                                                                                  font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        {{ __('common.admin_panel') }}
                    </a>
                @endif

                <a href="{{ route('vehicles.index') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200
                                                  text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                    v>
                    {{ __('common.nav_vehicles') }}
                </a>

                @if(auth()->user()->isCustomer())
                    <a href="{{ route('customer.bookings.index') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200
                                                                                  text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ __('common.my_bookings') }}
                    </a>
                @endif

            </div>
        </div>

    </div>
@endsection