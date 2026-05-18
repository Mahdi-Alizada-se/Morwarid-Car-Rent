@extends('layouts.app')

@section('title', __('bookings.booking_confirmed'))

@push('styles')
    <style>
        @media print {

            header,
            nav,
            footer,
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="print-container bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- ─── Header ──────────────────────────────────────────────────────────── --}}
            <div class="bg-green-50 border-b border-green-100 px-8 py-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-11 h-11 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-green-800 mb-2">
                    {{ __('bookings.booking_confirmed') }}!
                </h1>
                <p class="text-green-600 text-sm mb-4">
                    Your booking has been received successfully
                </p>
                <div class="inline-block bg-white border-2 border-green-200 rounded-2xl px-8 py-4">
                    <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">
                        {{ __('bookings.reference_code') }}
                    </p>
                    <p class="text-3xl font-black text-indigo-600 font-mono tracking-wider">
                        {{ $booking->reference_code }}
                    </p>
                </div>
            </div>

            <div class="px-8 py-6 space-y-6">

                {{-- ─── Section 1: Customer Information ────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-3
                           flex items-center gap-2 text-indigo-600">
                        <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center
                                 justify-center text-xs font-bold">1</span>
                        {{ __('bookings.your_information') }}
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('common.full_name') }}
                            </p>
                            <p class="font-semibold text-gray-900 mt-0.5">
                                {{ $booking->customer?->name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('common.email') }}
                            </p>
                            <p class="font-semibold text-gray-900 mt-0.5 text-sm break-all">
                                {{ $booking->customer?->email }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('common.phone') }}
                            </p>
                            <p class="font-semibold text-gray-900 mt-0.5">
                                {{ $booking->customer?->phone ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ─── Section 2: Vehicle Details ──────────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-3
                           flex items-center gap-2 text-indigo-600">
                        <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center
                                 justify-center text-xs font-bold">2</span>
                        {{ __('bookings.vehicle_details') }}
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-4 mb-4">
                            @if($booking->vehicle?->thumbnail)
                                <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                                    class="w-24 h-16 object-cover rounded-xl border border-gray-200 flex-shrink-0">
                            @endif
                            <div>
                                <p class="font-bold text-gray-900 text-lg">
                                    {{ $booking->vehicle?->full_name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $booking->vehicle?->category?->name }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">
                                    {{ __('vehicles.license_plate') }}
                                </p>
                                <p class="font-bold text-gray-900 font-mono mt-0.5">
                                    {{ $booking->vehicle?->license_plate }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">
                                    {{ __('vehicles.color') }}
                                </p>
                                <p class="font-semibold text-gray-900 mt-0.5">
                                    {{ $booking->vehicle?->color ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Section 3: Rental Details ───────────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-3
                           flex items-center gap-2 text-indigo-600">
                        <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center
                                 justify-center text-xs font-bold">3</span>
                        {{ __('bookings.rental_details') }}
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">
                                    {{ __('vehicles.pickup_date') }}
                                </p>
                                <p class="font-semibold text-gray-900 mt-0.5">
                                    {{ $booking->pickup_date->format('l, F j Y') }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    at {{ $booking->pickup_date->format('g:i A') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">
                                    {{ __('vehicles.return_date') }}
                                </p>
                                <p class="font-semibold text-gray-900 mt-0.5">
                                    {{ $booking->return_date->format('l, F j Y') }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    at {{ $booking->return_date->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-200">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">
                                    {{ __('vehicles.duration') }}
                                </p>
                                <p class="font-semibold text-gray-900 mt-0.5">
                                    {{ $booking->pickup_date->diffInDays($booking->return_date) }}
                                    {{ __('vehicles.days') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">
                                    {{ __('bookings.pickup_location') }}
                                </p>
                                <p class="font-semibold text-gray-900 mt-0.5 text-sm">
                                    {{ config('company.pickup_name') }}
                                </p>
                                <p class="text-xs text-gray-500">{{ config('company.address') }}</p>
                                <a href="{{ config('company.maps_url') }}" target="_blank"
                                    class="text-xs text-indigo-600 hover:underline mt-1 inline-block">
                                    📍 {{ __('bookings.view_on_maps') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Section 4: Payment Summary ──────────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider mb-3
                           flex items-center gap-2 text-indigo-600">
                        <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center
                                 justify-center text-xs font-bold">4</span>
                        {{ __('bookings.payment_summary') }}
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ __('vehicles.daily_rate') }}</span>
                            <span class="font-semibold text-gray-900">
                                AFN {{ number_format($dailyRate, 0) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ __('vehicles.duration') }}</span>
                            <span class="font-semibold text-gray-900">
                                {{ $booking->pickup_date->diffInDays($booking->return_date) }}
                                {{ __('vehicles.days') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ __('payments.payment_method') }}</span>
                            <span class="font-semibold text-gray-900 capitalize">
                                {{ ucfirst(str_replace('_', ' ', $booking->latestPayment?->method ?? 'Pending')) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-gray-200 mt-2">
                            <span class="font-bold text-gray-900 text-base">
                                {{ __('vehicles.total') }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-black text-indigo-600">
                                    AFN {{ number_format($booking->total_amount, 0) }}
                                </span>
                                @if($booking->latestPayment?->status === 'paid')
                                    <span class="px-2.5 py-0.5 bg-green-100 text-green-700
                                                 text-xs font-bold rounded-full">
                                        {{ __('common.paid') }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-orange-100 text-orange-700
                                                 text-xs font-bold rounded-full">
                                        {{ __('common.pending') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Section 5: Cancellation Policy ─────────────────────────────── --}}
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
                    <h4 class="font-bold text-orange-800 text-sm mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ __('bookings.cancellation_policy') }}
                    </h4>
                    <p class="text-orange-700 text-sm">
                        ✅ <strong>Free cancellation</strong> within 5 hours of booking time.
                    </p>
                    <p class="text-orange-700 text-sm mt-2">
                        ⚠️ After 5 hours, a cancellation fee of
                        <strong>AFN {{ number_format($dailyRate, 0) }}</strong> applies.
                    </p>
                </div>

                {{-- ─── Section 6: Important Reminders ─────────────────────────────── --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                    <h4 class="font-bold text-blue-800 text-sm mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('bookings.important_reminders') }}
                    </h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-center gap-2">
                            <span class="text-blue-400">✓</span>
                            Arrive 15 minutes before your pickup time
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-blue-400">✓</span>
                            Bring your national ID or passport
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-blue-400">✓</span>
                            Bring your valid driver's license
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-blue-400">✓</span>
                            Return the vehicle with a full fuel tank
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500">📞</span>
                            For help call us:
                            <strong>{{ config('company.phone') }}</strong>
                        </li>
                    </ul>
                </div>

            </div>

            {{-- ─── Action Buttons ──────────────────────────────────────────────────── --}}
            <div class="px-8 py-6 border-t border-gray-100 no-print">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="window.print()" class="flex-1 inline-flex items-center justify-center gap-2 py-3
                               bg-gray-800 text-white font-semibold rounded-xl
                               hover:bg-gray-700 transition-colors text-sm">
                        🖨 {{ __('bookings.print_page') }}
                    </button>
                    <a href="{{ route('customer.bookings.index') }}" class="flex-1 inline-flex items-center justify-center gap-2 py-3
                          bg-indigo-600 text-white font-semibold rounded-xl
                          hover:bg-indigo-700 transition-colors text-sm">
                        ← {{ __('common.my_bookings') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection