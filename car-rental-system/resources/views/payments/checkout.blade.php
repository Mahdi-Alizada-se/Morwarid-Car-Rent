@extends('layouts.app')

@section('title', __('payments.checkout'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ─── Booking Summary Card ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="font-bold text-gray-900 text-lg mb-4">
                {{ __('bookings.booking_details') }}
            </h2>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">

                @if($booking->vehicle?->thumbnail)
                    <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                        class="w-28 h-20 object-cover rounded-xl border border-gray-200 flex-shrink-0"
                        alt="{{ $booking->vehicle?->full_name }}">
                @endif

                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 text-xl">
                        {{ $booking->vehicle?->full_name }}
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-3">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.pickup_date') }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ $booking->pickup_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.return_date') }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ $booking->return_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.duration') }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ $booking->pickup_date->diffInDays($booking->return_date) }}
                                {{ __('vehicles.days') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.total') }}
                            </p>
                            <p class="text-2xl font-black text-indigo-600 mt-0.5">
                                AFN {{ number_format($booking->total_amount, 0) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Heading ─────────────────────────────────────────────────────────── --}}
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">
            {{ __('payments.choose_method') }}
        </h2>

        {{-- ─── Two Payment Cards ───────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            {{-- ─── Pay at Counter ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border-2 border-green-200 shadow-sm
                        hover:border-green-400 hover:shadow-md transition-all flex flex-col">

                {{-- Header --}}
                <div class="bg-green-50 px-6 py-5 border-b border-green-100 rounded-t-2xl">
                    <div class="text-5xl mb-3">💵</div>
                    <h3 class="text-xl font-bold text-green-800">
                        {{ __('payments.pay_at_counter') }}
                    </h3>
                    <p class="text-green-600 text-sm mt-1">
                        Pay cash when you arrive at our office
                    </p>
                </div>

                {{-- Steps --}}
                <div class="px-6 py-5 flex-1">
                    <ol class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">1</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Complete your booking now — no payment needed yet
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">2</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Come to <strong>{{ config('company.pickup_name') }}</strong>,
                                {{ config('company.address') }}
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">3</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Pay <strong class="text-green-700 text-base">
                                    AFN {{ number_format($booking->total_amount, 0) }}
                                </strong> cash at our desk
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">4</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Receive your vehicle keys and drive away! 🎉
                            </span>
                        </li>
                    </ol>
                </div>

                {{-- Button --}}
                <div class="px-6 pb-6">
                    <form method="POST" action="{{ route('customer.payments.counter') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <button type="submit" class="w-full py-4 bg-green-600 text-white font-bold text-base
                                       rounded-xl hover:bg-green-700 active:bg-green-800 transition-colors">
                            ✅ Book Now — Pay at Counter
                        </button>
                    </form>
                </div>
            </div>

            {{-- ─── Bank Transfer ───────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border-2 border-blue-200 shadow-sm
                        hover:border-blue-400 hover:shadow-md transition-all flex flex-col">

                {{-- Header --}}
                <div class="bg-blue-50 px-6 py-5 border-b border-blue-100 rounded-t-2xl">
                    <div class="text-5xl mb-3">🏦</div>
                    <h3 class="text-xl font-bold text-blue-800">
                        {{ __('payments.bank_transfer') }}
                    </h3>
                    <p class="text-blue-600 text-sm mt-1">
                        Transfer money then upload your receipt
                    </p>
                </div>

                {{-- Steps --}}
                <div class="px-6 py-5 flex-1">
                    <ol class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">1</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Complete your booking now
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">2</span>
                            <div class="text-sm text-gray-700 pt-0.5 flex-1">
                                <p class="mb-2">
                                    Transfer <strong class="text-blue-700 text-base">
                                        AFN {{ number_format($booking->total_amount, 0) }}
                                    </strong> to:
                                </p>
                                {{-- Bank Details Box --}}
                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 space-y-2 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Bank</span>
                                        <span class="font-bold text-gray-900">{{ config('company.bank_name') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Account Name</span>
                                        <span class="font-bold text-gray-900">{{ config('company.account_name') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Account Number</span>
                                        <span class="font-bold text-blue-700 font-mono text-sm">
                                            {{ config('company.account_number') }}
                                        </span>
                                    </div>
                                    @if(config('company.branch'))
                                        <div class="flex justify-between">
                                            <span class="text-gray-500 font-medium">Branch</span>
                                            <span class="font-bold text-gray-900">{{ config('company.branch') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">3</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Take a clear photo of your bank receipt
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">4</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Upload the receipt photo on the next screen
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                         justify-center text-sm font-bold flex-shrink-0 mt-0.5">5</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Admin confirms within 2 hours ✓
                            </span>
                        </li>
                    </ol>
                </div>

                {{-- Button --}}
                <div class="px-6 pb-6">
                    <form method="POST" action="{{ route('customer.payments.bank-transfer') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-bold text-base
                                       rounded-xl hover:bg-blue-700 active:bg-blue-800 transition-colors">
                            🏦 Book Now — Bank Transfer
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- ─── Location Reminder ───────────────────────────────────────────────── --}}
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div class="flex-1">
                    <p class="font-bold text-green-900 text-base">
                        📍 Pickup at {{ config('company.pickup_name') }}
                    </p>
                    <p class="text-green-700 text-sm mt-0.5">{{ config('company.address') }}</p>
                    <p class="text-green-600 text-xs mt-1">
                        🕐 {{ config('company.working_hours') }}
                    </p>
                    <a href="{{ config('company.maps_url') }}" target="_blank"
                        class="inline-block mt-2 text-sm text-green-700 hover:underline font-medium">
                        Open in Google Maps →
                    </a>
                </div>
            </div>
        </div>

    </div>
@endsection