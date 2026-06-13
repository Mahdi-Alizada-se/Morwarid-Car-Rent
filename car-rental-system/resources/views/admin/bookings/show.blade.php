@extends('layouts.admin')

@section('page-title', $booking->reference_code)
@section('breadcrumb')
    <a href="{{ route('admin.bookings.index') }}" class="hover:text-gray-700">
        {{ __('common.nav_bookings') }}
    </a>
    <span>/</span>
    <span class="text-gray-900 font-medium">{{ $booking->reference_code }}</span>
@endsection

@section('content')
    <div class="max-w-5xl space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ─── Left Column ──────────────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Booking Info Card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-gray-900">{{ __('bookings.booking_details') }}</h3>
                        @php
                            $colors = [
                                'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'active' => 'bg-green-50 text-green-700 border-green-200',
                                'completed' => 'bg-gray-100 text-gray-600 border-gray-200',
                                'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                            ];
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold border
                                     {{ $colors[$booking->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ __('bookings.reference') }}</p>
                            <code class="text-sm font-mono font-bold text-gray-900">{{ $booking->reference_code }}</code>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ __('vehicles.total') }}</p>
                            <p class="text-xl font-bold text-indigo-600">AFN {{ number_format($booking->total_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ __('vehicles.pickup_date') }}</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $booking->pickup_date->format('M d, Y — H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ __('vehicles.return_date') }}</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $booking->return_date->format('M d, Y — H:i') }}
                            </p>
                        </div>
                        @if($booking->pickup_location)
                            <div>
                                <p class="text-xs text-gray-500 font-medium">{{ __('bookings.pickup_location') }}</p>
                                <p class="text-sm text-gray-900">{{ $booking->pickup_location }}</p>
                            </div>
                        @endif
                        @if($booking->return_location)
                            <div>
                                <p class="text-xs text-gray-500 font-medium">{{ __('bookings.return_location') }}</p>
                                <p class="text-sm text-gray-900">{{ $booking->return_location }}</p>
                            </div>
                        @endif
                        @if($booking->notes)
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 font-medium">{{ __('bookings.notes') }}</p>
                                <p class="text-sm text-gray-900">{{ $booking->notes }}</p>
                            </div>
                        @endif
                        @if($booking->cancellation_reason)
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 font-medium">{{ __('bookings.cancellation_reason') }}</p>
                                <p class="text-sm text-red-700">{{ $booking->cancellation_reason }}</p>
                            </div>
                        @endif

                        @if($booking->cancellation_fee > 0)
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 font-medium">Cancellation Fee</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-sm font-bold text-orange-600">
                                        AFN {{ number_format($booking->cancellation_fee) }}
                                    </p>
                                    @if($booking->cancellation_fee_paid)
                                        <span class="px-2 py-0.5 bg-green-50 text-green-700
                                         text-xs font-semibold rounded-full">
                                            ✓ Paid
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-red-50 text-red-700
                                         text-xs font-semibold rounded-full">
                                            Unpaid
                                        </span>
                                        {{-- Admin can mark fee as paid --}}
                                        <form method="POST" action="{{ route('admin.bookings.mark-fee-paid', $booking) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1 text-xs font-medium bg-green-600
                                               text-white rounded-lg hover:bg-green-700
                                               transition-colors">
                                                Mark Fee as Paid
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Vehicle Card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('vehicles.vehicle') }}</h3>
                    <div class="flex items-center gap-4">
                        @if($booking->vehicle?->thumbnail)
                            <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                                class="w-24 h-16 object-cover rounded-xl border border-gray-200">
                        @endif
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 text-lg">{{ $booking->vehicle?->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->vehicle?->license_plate }}</p>
                            <a href="{{ route('admin.vehicles.edit', $booking->vehicle) }}"
                                class="text-xs text-indigo-600 hover:underline mt-1 inline-block">
                                {{ __('common.edit') }} →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Payments --}}
                @if($booking->payments->isNotEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-900 mb-4">{{ __('common.nav_payments') }}</h3>
                        <div class="space-y-3">
                            @foreach($booking->payments as $payment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            AFN {{ number_format($payment->amount) }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ ucfirst($payment->method) }} ·
                                            {{ $payment->paid_at?->format('M d, Y') ?? '—' }}
                                        </p>
                                    </div>
                                    @php
                                        $pColors = [
                                            'paid' => 'bg-green-50 text-green-700',
                                            'pending' => 'bg-yellow-50 text-yellow-700',
                                            'failed' => 'bg-red-50 text-red-700',
                                            'refunded' => 'bg-gray-100 text-gray-600',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                         {{ $pColors[$payment->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- ─── Right Column ──────────────────────────────────────────────────── --}}
            <div class="space-y-5">

                {{-- Customer Card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('common.customer') }}</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center
                                    text-indigo-700 font-semibold text-sm flex-shrink-0">
                            {{ strtoupper(substr($booking->customer?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $booking->customer?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->customer?->email }}</p>
                        </div>
                    </div>
                    @if($booking->customer?->phone)
                        <p class="text-sm text-gray-600">📞 {{ $booking->customer->phone }}</p>
                    @endif
                </div>

                {{-- Change Status Form --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('bookings.change_status') }}</h3>
                    <form method="POST" action="{{ route('admin.bookings.update-status', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('vehicles.status') }}
                                </label>
                                <select name="status" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>
                                        {{ __('common.pending') }}</option>
                                    <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>
                                        {{ __('common.confirmed') }}</option>
                                    <option value="active" {{ $booking->status === 'active' ? 'selected' : '' }}>
                                        {{ __('bookings.active') }}</option>
                                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>
                                        {{ __('common.completed') }}</option>
                                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>
                                        {{ __('common.cancelled') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('bookings.reason') }}
                                    <span class="text-gray-400 text-xs">({{ __('common.optional') }})</span>
                                </label>
                                <textarea name="reason" rows="2" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="{{ __('bookings.reason_placeholder') }}">
                                </textarea>
                            </div>

                            {{-- Cancellation Fee Warning --}}
@if($booking->canBeCancelled() && $booking->hasCancellationFee())
    <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg text-xs text-orange-700">
        ⚠️ <strong>Cancellation Fee:</strong>
        {{ $booking->getCancellationFeeDescription() }}
    </div>
@endif
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold
                                           rounded-lg hover:bg-indigo-700 transition-colors">
                                {{ __('bookings.update_status') }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
@endsection