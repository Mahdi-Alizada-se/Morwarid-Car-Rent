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

            {{-- ─── Header ─────────────────────────────────────────────────────── --}}
            <div class="bg-green-50 border-b border-green-100 px-8 py-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center
                                justify-center mx-auto mb-4">
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

                {{-- ─── Cash Payment Warning ────────────────────────────────────── --}}
                @if($booking->status === 'pending' && $booking->payments->first()?->method === 'cash')
                    <div class="bg-orange-50 border-2 border-orange-300 rounded-xl p-5">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948
                                                  3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949
                                                  3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12
                                                  15.75h.007v.008H12v-.008z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-base font-black text-orange-800">
                                    ⏰ Pay Within 5 Hours or Booking Will Be Cancelled
                                </p>
                                <p class="text-sm text-orange-700 mt-1">
                                    Your booking is <strong>pending</strong>. If cash payment
                                    is not confirmed by our admin within
                                    <strong>5 hours</strong>, the system will
                                    <strong>automatically cancel</strong> your booking and
                                    release the dates for other customers.
                                </p>

                                <div class="mt-3 bg-white rounded-xl p-4
                                                        border border-orange-200 space-y-2">
                                    <p class="text-sm font-bold text-gray-800">
                                        To confirm your booking:
                                    </p>
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <span class="text-orange-500">📍</span>
                                        Visit our office at
                                        <strong>{{ config('company.address', 'Dasht-e-Barchi, Kabul') }}</strong>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <span class="text-orange-500">💵</span>
                                        Pay
                                        <strong class="text-indigo-600 text-base">
                                            AFN {{ number_format($booking->total_amount) }}
                                        </strong>
                                        in cash
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-700">
                                        <span class="text-orange-500">🎫</span>
                                        Show reference code:
                                        <code class="font-mono font-black text-indigo-600 text-base
                                                                 bg-indigo-50 px-2 py-0.5 rounded">
                                                        {{ $booking->reference_code }}
                                                    </code>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center gap-2 bg-red-50
                                                        border border-red-200 rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-red-700 font-semibold">
                                        Deadline:
                                        <strong>{{ now()->addHours(5)->format('M d, Y — h:i A') }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ─── Bank Transfer Info ──────────────────────────────────────── --}}
                @if($booking->payments->first()?->method === 'bank_transfer')
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-5">
                        <p class="text-base font-black text-blue-800 mb-2">
                            🏦 Bank Transfer — Under Review
                        </p>
                        <p class="text-sm text-blue-700 mb-3">
                            Your booking is <strong>pending admin review</strong>.
                            Our team will verify your bank transfer and confirm
                            your booking shortly. You will be notified once confirmed.
                        </p>
                        <div class="bg-white rounded-lg p-3 text-xs space-y-1.5 border border-blue-100">
                            <p class="font-semibold text-gray-700">What happens next:</p>
                            <p class="text-gray-600">✓ Admin reviews your transfer reference</p>
                            <p class="text-gray-600">✓ Admin confirms your payment</p>
                            <p class="text-gray-600">✓ Your booking status changes to Confirmed</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 text-xs space-y-1.5
                                                border border-blue-100">
                            <p><span class="text-gray-500">Bank Name:</span>
                                <strong>Afghan United Bank</strong>
                            </p>
                            <p><span class="text-gray-500">Account Name:</span>
                                <strong>Morwarid Car Rental</strong>
                            </p>
                            <p><span class="text-gray-500">Account Number:</span>
                                <strong class="font-mono">1234-5678-9012</strong>
                            </p>
                        </div>
                        @if($booking->payments->first()?->notes)
                            <p class="text-xs text-blue-600 mt-2">
                                Your reference: {{ $booking->payments->first()->notes }}
                            </p>
                        @endif
                    </div>
                @endif

                {{-- ─── Mastercard Info ─────────────────────────────────────────── --}}
                @if($booking->payments->first()?->method === 'online')
                    <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-5">
                        <p class="text-base font-black text-purple-800 mb-2">
                            💳 Card Payment Successful
                        </p>
                        <p class="text-sm text-purple-700">
                            Your Mastercard payment has been processed successfully.
                            Your booking is confirmed.
                        </p>
                        @if($booking->payments->first()?->notes)
                            <p class="text-xs text-purple-600 mt-2">
                                {{ $booking->payments->first()->notes }}
                            </p>
                        @endif
                    </div>
                @endif

                {{-- ─── Section 1: Customer Information ────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider
                                   mb-3 flex items-center gap-2 text-indigo-600">
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

                {{-- ─── Section 2: Vehicle Details ──────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider
                                   mb-3 flex items-center gap-2 text-indigo-600">
                        <span class="w-6 h-6 bg-indigo-100 rounded-full flex items-center
                                         justify-center text-xs font-bold">2</span>
                        {{ __('bookings.vehicle_details') }}
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-4 mb-4">
                            @if($booking->vehicle?->thumbnail)
                                <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}" class="w-24 h-16 object-cover rounded-xl
                                                        border border-gray-200 flex-shrink-0">
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

                {{-- ─── Section 3: Rental Details ───────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider
                                   mb-3 flex items-center gap-2 text-indigo-600">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2
                                        border-t border-gray-200">
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

                {{-- ─── Section 4: Payment Summary ──────────────────────────────── --}}
                <div>
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider
                                   mb-3 flex items-center gap-2 text-indigo-600">
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
                                @php
                                    $method = $booking->payments->first()?->method ?? 'pending';
                                    $methodLabel = match ($method) {
                                        'cash' => '💵 Cash',
                                        'bank_transfer' => '🏦 Bank Transfer',
                                        'online' => '💳 Mastercard',
                                        default => ucfirst(str_replace('_', ' ', $method)),
                                    };
                                @endphp
                                {{ $methodLabel }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-3
                                        border-t border-gray-200 mt-2">
                            <span class="font-bold text-gray-900 text-base">
                                {{ __('vehicles.total') }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-black text-indigo-600">
                                    AFN {{ number_format($booking->total_amount, 0) }}
                                </span>
                                @if($booking->payments->first()?->status === 'paid')
                                    <span class="px-2.5 py-0.5 bg-green-100 text-green-700
                                                             text-xs font-bold rounded-full">
                                        ✓ Paid
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-orange-100 text-orange-700
                                                             text-xs font-bold rounded-full">
                                        Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Cancellation Policy ─────────────────────────────────────── --}}
                @if($booking->payments->first()?->method !== 'cash')
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
                        <h4 class="font-bold text-orange-800 text-sm mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                                                  1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464
                                                  0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ __('bookings.cancellation_policy') }}
                        </h4>
                        <p class="text-orange-700 text-sm">
                            ✅ <strong>Free cancellation</strong> within 5 hours of booking.
                        </p>
                        <p class="text-orange-700 text-sm mt-2">
                            ⚠️ After 5 hours, a cancellation fee of
                            <strong>AFN {{ number_format($dailyRate, 0) }}</strong> applies.
                        </p>
                    </div>
                @endif

                {{-- ─── Important Reminders ─────────────────────────────────────── --}}
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
                            For help call us: <strong>{{ config('company.phone') }}</strong>
                        </li>
                    </ul>
                </div>

            </div>

            {{-- ─── Action Buttons ──────────────────────────────────────────────── --}}
            <div class="px-8 py-6 border-t border-gray-100 no-print">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="window.print()" class="flex-1 inline-flex items-center justify-center gap-2
                                       py-3 bg-gray-800 text-white font-semibold rounded-xl
                                       hover:bg-gray-700 transition-colors text-sm">
                        🖨 {{ __('bookings.print_page') }}
                    </button>
                    <a href="{{ route('customer.bookings.index') }}" class="flex-1 inline-flex items-center justify-center gap-2
                                  py-3 bg-indigo-600 text-white font-semibold rounded-xl
                                  hover:bg-indigo-700 transition-colors text-sm">
                        ← {{ __('common.my_bookings') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection