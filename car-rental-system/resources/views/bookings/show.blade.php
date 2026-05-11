@extends('layouts.app')

@section('title', $booking->reference_code)

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Success Banner (shown after create) --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-6 text-center">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-green-800 mb-1">{{ __('bookings.booking_confirmed') }}</h2>
                <p class="text-green-700 text-sm">{{ session('success') }}</p>
                <div class="mt-3 bg-white rounded-xl px-5 py-3 inline-block border border-green-200">
                    <p class="text-xs text-gray-500">{{ __('bookings.reference_code') }}</p>
                    <p class="text-lg font-bold text-indigo-600 font-mono">{{ $booking->reference_code }}</p>
                </div>
            </div>
        @endif

        {{-- Booking Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-mono">{{ $booking->reference_code }}</p>
                    <h2 class="font-bold text-gray-900 mt-0.5">{{ __('bookings.booking_details') }}</h2>
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

                {{-- Vehicle --}}
                <div class="flex items-center gap-4">
                    @if($booking->vehicle?->thumbnail)
                        <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                            class="w-24 h-16 object-cover rounded-xl border border-gray-200">
                    @endif
                    <div>
                        <p class="font-bold text-gray-900 text-lg">{{ $booking->vehicle?->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $booking->vehicle?->license_plate }}</p>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 font-medium">{{ __('vehicles.pickup_date') }}</p>
                        <p class="font-semibold text-gray-900 mt-1">
                            {{ $booking->pickup_date->format('M d, Y') }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $booking->pickup_date->format('H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 font-medium">{{ __('vehicles.return_date') }}</p>
                        <p class="font-semibold text-gray-900 mt-1">
                            {{ $booking->return_date->format('M d, Y') }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $booking->return_date->format('H:i') }}</p>
                    </div>
                </div>

                {{-- Locations --}}
                @if($booking->pickup_location || $booking->return_location)
                    <div class="grid grid-cols-2 gap-4">
                        @if($booking->pickup_location)
                            <div>
                                <p class="text-xs text-gray-500 font-medium">{{ __('bookings.pickup_location') }}</p>
                                <p class="text-sm text-gray-900 mt-1">{{ $booking->pickup_location }}</p>
                            </div>
                        @endif
                        @if($booking->return_location)
                            <div>
                                <p class="text-xs text-gray-500 font-medium">{{ __('bookings.return_location') }}</p>
                                <p class="text-sm text-gray-900 mt-1">{{ $booking->return_location }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Total --}}
                <div class="bg-indigo-50 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ __('vehicles.total') }}</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">
                            AFN {{ number_format($booking->total_amount) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">{{ __('vehicles.duration') }}</p>
                        <p class="font-semibold text-gray-900">{{ $booking->duration_in_days }} {{ __('vehicles.days') }}
                        </p>
                    </div>
                </div>

                {{-- Cancel Button --}}
                @if($booking->canBeCancelled())
                    <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}"
                        onsubmit="return confirm('{{ __('bookings.cancel_confirm') }}')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full py-2.5 text-red-600 border border-red-200 text-sm font-medium
                                           rounded-xl hover:bg-red-50 transition-colors">
                            {{ __('bookings.cancel_booking') }}
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- Back Link --}}
        <div class="mt-5 text-center">
            <a href="{{ route('customer.bookings.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← {{ __('bookings.back_to_bookings') }}
            </a>
        </div>

    </div>
@endsection