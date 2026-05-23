@extends('layouts.app')

@section('title', 'Booking Reserved — Payment Required')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">

        {{-- ─── Main Warning Card ───────────────────────────────────────────────── --}}
        <div class="bg-orange-50 border-2 border-orange-300 rounded-2xl p-6 mb-6 text-center">

            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center
                        justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-orange-800 mb-2">
                Booking Reserved — Payment Required
            </h1>
            <p class="text-orange-700 mb-4">
                Your booking has been reserved. You must visit our office and pay within 5 hours.
            </p>

            {{-- Booking Reference --}}
            <div class="bg-white rounded-xl p-3 inline-block mb-4">
                <p class="text-xs text-gray-400 mb-1">Booking Reference</p>
                <code class="text-lg font-bold font-mono text-gray-800">
                    {{ $booking->reference_code }}
                </code>
            </div>

            {{-- Countdown Timer --}}
            <div class="bg-orange-100 rounded-xl p-4 mb-4">
                <p class="text-sm text-orange-600 font-medium mb-1">Time Remaining to Pay</p>
                <p id="countdown" class="text-4xl font-bold text-orange-800 font-mono tracking-wider">
                    --:--:--
                </p>
                <p class="text-xs text-orange-500 mt-1">
                    Deadline: {{ $booking->created_at->addHours(5)->format('h:i A, M d Y') }}
                </p>
            </div>

            {{-- Amount --}}
            <div class="bg-white rounded-xl p-4 mb-4">
                <p class="text-sm text-gray-500 mb-1">Amount to Pay at Office</p>
                <p class="text-3xl font-bold text-gray-900">
                    AFN {{ number_format($booking->total_amount, 0) }}
                </p>
            </div>

            {{-- Warning --}}
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
                <strong>⚠️ Important:</strong> If you do not visit and pay within 5 hours,
                your booking will be <strong>automatically cancelled</strong> and the vehicle
                will become available for other customers.
            </div>

        </div>

        {{-- ─── Office Info Card ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="font-bold text-gray-900 text-lg mb-4">Office Location</h2>

            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-gray-800">Morwarid Car Hub</p>
                        <p class="text-gray-500">Dasht-e-Barchi, Kabul, Afghanistan</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-gray-800">Working Hours</p>
                        <p class="text-gray-500">8:00 AM – 8:00 PM, Every Day</p>
                    </div>
                </div>
            </div>

            <a href="https://maps.google.com/?q=Dasht-e-Barchi,Kabul,Afghanistan" target="_blank" class="mt-4 inline-flex items-center gap-2 text-sm text-blue-600
                      font-medium hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Open in Google Maps
            </a>
        </div>

        {{-- ─── Booking Details Card ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h2 class="font-bold text-gray-900 text-lg mb-4">Booking Details</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Vehicle</p>
                    <p class="font-semibold text-gray-800">
                        {{ $booking->vehicle->full_name }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400">Status</p>
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                 bg-yellow-50 text-yellow-700">
                        Pending Payment
                    </span>
                </div>
                <div>
                    <p class="text-gray-400">Pickup Date</p>
                    <p class="font-semibold text-gray-800">
                        {{ $booking->pickup_date->format('M d, Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400">Return Date</p>
                    <p class="font-semibold text-gray-800">
                        {{ $booking->return_date->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ─── Actions ─────────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('customer.bookings.index') }}" class="flex-1 text-center bg-gray-800 text-white font-semibold py-3
                      rounded-xl hover:bg-gray-700 transition-colors">
                View My Bookings
            </a>
            <a href="{{ route('vehicles.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 font-semibold py-3
                      rounded-xl hover:bg-gray-200 transition-colors">
                Browse Other Vehicles
            </a>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        const deadline = new Date('{{ $booking->created_at->addHours(5)->toISOString() }}');

        function updateCountdown() {
            const now = new Date();
            const diff = deadline - now;
            const el = document.getElementById('countdown');

            if (diff <= 0) {
                el.textContent = 'EXPIRED';
                el.style.color = '#dc2626';
                return;
            }

            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);

            el.textContent = h + 'h ' +
                m.toString().padStart(2, '0') + 'm ' +
                s.toString().padStart(2, '0') + 's';
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
@endpush