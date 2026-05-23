@extends('layouts.app')

@section('title', $booking->reference_code)

@push('styles')
    <style>
        @media print {

            .no-print,
            nav,
            header,
            footer,
            aside {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-section {
                page-break-inside: avoid;
            }

            .print\:block {
                display: block !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ─── Print Header (hidden on screen, shown when printing) ─────────────── --}}
        <div class="hidden print:block mb-6 text-center border-b pb-4">
            <h1 class="text-2xl font-bold">Morwarid Car Rental</h1>
            <p class="text-gray-500">Dasht-e-Barchi, Kabul, Afghanistan</p>
            <p class="text-gray-500 text-sm">Booking Confirmation</p>
        </div>

        {{-- ─── Action Buttons ──────────────────────────────────────────────────── --}}
        <div class="flex gap-3 mb-6 no-print flex-wrap">
            <a href="{{ route('customer.bookings.index') }}"
                class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                ← Back to Bookings
            </a>
            <div class="flex gap-2 ml-auto">
                <button onclick="window.print()" class="flex items-center gap-2 bg-gray-800 text-white px-4 py-2
                               rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.75 19.817m0-5.988a42.3 42.3 0 014.339.312m-4.339-.312L4.5 19.817m15.75-5.988a42.415 42.415 0 00-10.56 0m10.56 0L19.5 19.817M9.75 9.75h4.5m-4.5 0V6.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V9.75m-4.5 0H6.375" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        {{-- ─── Success Banner ──────────────────────────────────────────────────── --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-6 text-center no-print">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center
                                justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-green-800 mb-1">
                    {{ __('bookings.booking_confirmed') }}
                </h2>
                <p class="text-green-700 text-sm">{{ session('success') }}</p>
                <div class="mt-3 bg-white rounded-xl px-5 py-3 inline-block border border-green-200">
                    <p class="text-xs text-gray-500">{{ __('bookings.reference_code') }}</p>
                    <p class="text-lg font-bold text-indigo-600 font-mono">
                        {{ $booking->reference_code }}
                    </p>
                </div>
            </div>
        @endif

        {{-- ─── Main Booking Card ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                        Booking Reference
                    </p>
                    <p class="text-2xl font-bold text-indigo-600 font-mono mt-0.5">
                        {{ $booking->reference_code }}
                    </p>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'active' => 'bg-green-50 text-green-700 border-green-200',
                        'completed' => 'bg-gray-100 text-gray-600 border-gray-200',
                        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $color = $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                @endphp
                <span class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ $color }}">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>

            <div class="p-6 space-y-6">

                {{-- ─── Vehicle ──────────────────────────────────────────────────── --}}
                <div class="print-section">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        Vehicle
                    </p>
                    <div class="flex items-center gap-4">
                        @if($booking->vehicle?->thumbnail)
                            <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                                class="w-24 h-16 object-cover rounded-xl border border-gray-200 no-print">
                        @endif
                        <div>
                            <p class="font-bold text-gray-900 text-lg">
                                {{ $booking->vehicle?->full_name }}
                            </p>
                            <div class="flex flex-wrap gap-3 text-sm text-gray-500 mt-1">
                                <span>
                                    {{ $booking->vehicle?->category?->name ?? 'N/A' }}
                                </span>
                                <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">
                                    {{ $booking->vehicle?->license_plate }}
                                </span>
                                @if($booking->vehicle?->color)
                                    <span>{{ ucfirst($booking->vehicle->color) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Customer ─────────────────────────────────────────────────── --}}
                <div class="print-section">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        Customer
                    </p>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400">Name</p>
                            <p class="font-semibold text-gray-900">
                                {{ $booking->customer?->name ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Email</p>
                            <p class="font-semibold text-gray-900">
                                {{ $booking->customer?->email ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Phone</p>
                            <p class="font-semibold text-gray-900">
                                {{ $booking->customer?->phone ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">License Number</p>
                            <p class="font-semibold text-gray-900 font-mono text-xs">
                                {{ $booking->customer?->driver_license_number ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ─── Rental Dates ─────────────────────────────────────────────── --}}
                <div class="print-section">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        Rental Period
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 font-medium">Pickup Date</p>
                            <p class="font-semibold text-gray-900 mt-1">
                                {{ $booking->pickup_date->format('M d, Y') }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $booking->pickup_date->format('h:i A') }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 font-medium">Return Date</p>
                            <p class="font-semibold text-gray-900 mt-1">
                                {{ $booking->return_date->format('M d, Y') }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $booking->return_date->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            Duration:
                            <strong>{{ $booking->duration_in_days }} days</strong>
                        </span>
                    </div>
                    @if($booking->pickup_location)
                        <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span>Pickup: {{ $booking->pickup_location }}</span>
                        </div>
                    @endif
                </div>

                {{-- ─── Payment ──────────────────────────────────────────────────── --}}
                <div class="print-section">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                        Payment
                    </p>
                    <div class="bg-indigo-50 rounded-xl p-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="text-2xl font-bold text-indigo-600 mt-1">
                                AFN {{ number_format($booking->total_amount) }}
                            </p>
                            @if($booking->payments->isNotEmpty())
                                                @php $payment = $booking->payments->first(); @endphp
                                                <div class="flex gap-2 mt-2 flex-wrap">
                                                    <span class="text-xs bg-white px-2 py-0.5 rounded-full
                                                                     border border-indigo-200 text-indigo-700 font-medium">
                                                        {{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                                                    </span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                                                     {{ $payment->status === 'paid'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-yellow-100 text-yellow-700' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Duration</p>
                            <p class="font-semibold text-gray-900">
                                {{ $booking->duration_in_days }} days
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ─── Reminders ────────────────────────────────────────────────── --}}
                <div class="print-section bg-blue-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-blue-800 mb-2">Reminders</p>
                    <ul class="space-y-1.5 text-sm text-blue-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Bring your driver's license and this booking reference
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Return the vehicle with a full fuel tank
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Return the vehicle on time to avoid late fees
                        </li>
                    </ul>
                </div>

                {{-- ─── Cancellation Policy ──────────────────────────────────────── --}}
                <div class="print-section bg-orange-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-orange-800 mb-1">Cancellation Policy</p>
                    <p class="text-xs text-orange-700">
                        Cancellations made more than 5 hours before pickup are free.
                        Cancellations within 5 hours may incur a fee.
                    </p>
                </div>

                {{-- ─── Cancel Button ────────────────────────────────────────────── --}}
                @if($booking->canBeCancelled())
                    <div class="no-print">
                        <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}"
                            onsubmit="return confirm('{{ __('bookings.cancel_confirm') }}')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full py-2.5 text-red-600 border border-red-200
                                               text-sm font-medium rounded-xl hover:bg-red-50 transition-colors">
                                {{ __('bookings.cancel_booking') }}
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection