@extends('layouts.app')

@section('title', __('common.my_bookings'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('common.my_bookings') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('bookings.booking_history') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-4 flex items-start gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/30 border
                            border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-xl text-sm">
                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($bookings->isEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-12 text-center">
                <svg class="w-14 h-14 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0
                                          00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="font-semibold text-gray-600 dark:text-gray-300 text-lg">{{ __('bookings.no_bookings') }}</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">{{ __('bookings.no_bookings_desc') }}</p>
                <a href="{{ route('vehicles.index') }}" class="inline-block mt-5 px-5 py-2.5 bg-indigo-600 text-white text-sm
                              font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                    {{ __('common.nav_vehicles') }}
                </a>
            </div>

        @else

            <div class="space-y-4">
                @foreach($bookings as $booking)
                    @php
                        $isExpired = $booking->return_date->isPast()
                            && !in_array($booking->status, ['cancelled', 'completed']);
                        $hasFee = $booking->hasCancellationFee();
                        $feeDesc = $booking->getCancellationFeeDescription();

                        $confirmMsg = $hasFee
                            ? "⚠️ " . __('bookings.cancellation_fee_warning') . "\n\n{$feeDesc}\n\n" . __('bookings.confirm_cancel')
                            : __('bookings.confirm_cancel_free');

                        $statusLabels = [
                            'pending' => __('common.pending'),
                            'confirmed' => __('common.confirmed'),
                            'active' => __('common.active'),
                            'completed' => __('common.completed'),
                            'cancelled' => __('common.cancelled'),
                        ];

                        $statusColors = [
                            'pending' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                            'confirmed' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                            'active' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                            'completed' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
                            'cancelled' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                        ];
                        $color = $statusColors[$booking->status] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300';
                        $statusLabel = $statusLabels[$booking->status] ?? ucfirst($booking->status);
                    @endphp

                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5
                                        hover:shadow-sm dark:hover:shadow-none transition-shadow">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

                            {{-- Vehicle Thumbnail --}}
                            <div class="flex-shrink-0">
                                @if($booking->vehicle?->thumbnail)
                                    <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                                        class="w-20 h-14 object-cover rounded-xl border border-gray-200 dark:border-gray-700"
                                        alt="{{ $booking->vehicle?->full_name }}">
                                @else
                                    <div class="w-20 h-14 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5 13H8M2 9L4 10L5.27064 6.18807C5.53292 5.40125
                                                                                  5.66405 5.00784 5.90729 4.71698C6.12208 4.46013 6.39792
                                                                                  4.26132 6.70951 4.13878C7.06236 4 7.47705 4 8.30643
                                                                                  4H15.6936C16.523 4 16.9376 4 17.2905 4.13878C17.6021
                                                                                  4.26132 17.8779 4.46013 18.0927 4.71698C18.3359 5.00784
                                                                                  18.4671 5.40125 18.7294 6.18807L20 10L22 9M16 13H19M6.8
                                                                                  10H17.2C18.8802 10 19.7202 10 20.362 10.327C20.9265
                                                                                  10.6146 21.3854 11.0735 21.673 11.638C22 12.2798 22
                                                                                  13.1198 22 14.8V17.5C22 17.9647 22 18.197 21.9616
                                                                                  18.3902C21.8038 19.1836 21.1836 19.8038 20.3902
                                                                                  19.9616C20.197 20 19.9647 20 19.5 20H19C17.8954 20 17
                                                                                  19.1046 17 18C17 17.7239 16.7761 17.5 16.5 17.5H7.5C7.22386
                                                                                  17.5 7 17.7239 7 18C7 19.1046 6.10457 20 5 20H4.5C4.03534
                                                                                  20 3.80302 20 3.60982 19.9616C2.81644 19.8038 2.19624
                                                                                  19.1836 2.03843 18.3902C2 18.197 2 17.9647 2 17.5V14.8C2
                                                                                  13.1198 2 12.2798 2.32698 11.638C2.6146 11.0735 3.07354
                                                                                  10.6146 3.63803 10.327C4.27976 10 5.11984 10 6.8 10Z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Booking Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 flex-wrap">
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-gray-100">
                                            {{ $booking->vehicle?->full_name ?? '—' }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-0.5">
                                            {{ $booking->reference_code }}
                                        </p>
                                    </div>

                                    {{-- Status Badge --}}
                                    <div class="flex items-center gap-2">
                                        @if($isExpired)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                                                     text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                                {{ __('bookings.expired') }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                                             text-xs font-semibold {{ $color }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0
                                                                      00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $booking->pickup_date->translatedFormat('M d, Y') }}
                                        → {{ $booking->return_date->translatedFormat('M d, Y') }}
                                    </span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                                        AFN {{ number_format($booking->total_amount) }}
                                    </span>
                                </div>

                                {{-- Cancellation fee warning --}}
                                @if($booking->canBeCancelled() && $hasFee)
                                    <div class="mt-2 text-xs text-orange-600 dark:text-orange-400 font-medium">
                                        ⚠️ {{ $feeDesc }}
                                    </div>
                                @endif

                                {{-- Cancellation fee paid --}}
                                @if($booking->status === 'cancelled' && $booking->cancellation_fee > 0)
                                        <div class="mt-2 text-xs font-medium
                                                                {{ $booking->cancellation_fee_paid
                                    ? 'text-green-600 dark:text-green-400'
                                    : 'text-red-600 dark:text-red-400' }}">
                                            {{ __('bookings.cancellation_fee') }}:
                                            AFN {{ number_format($booking->cancellation_fee) }}
                                            —
                                            @if($booking->cancellation_fee_paid)
                                                ✓ {{ __('common.paid') }}
                                            @else
                                                ⚠️ {{ __('bookings.unpaid_contact_us') }}
                                            @endif
                                        </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">

                                <a href="{{ route('customer.bookings.show', $booking) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400
                                                  border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30
                                                  transition-colors">
                                    {{ __('common.view') }}
                                </a>

                                @if(in_array($booking->status, ['confirmed', 'active', 'completed']))
                                    <button onclick="window.open('{{ route('customer.bookings.show', $booking) }}', '_blank')" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300
                                                               border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800
                                                               transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2
                                                                          2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2
                                                                          2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0
                                                                          00-2 2v4h10z" />
                                        </svg>
                                        {{ __('bookings.print') }}
                                    </button>
                                @endif

                                @if($booking->status === 'pending')
                                    <a href="{{ route('customer.payments.checkout', $booking) }}" class="px-3 py-1.5 text-xs font-medium text-green-600 dark:text-green-400
                                                          border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30
                                                          transition-colors">
                                        {{ __('bookings.pay_now') }}
                                    </a>
                                @endif

                                @if($booking->canBeCancelled())
                                        <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}"
                                            onsubmit="return confirm('{{ addslashes($confirmMsg) }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg
                                                                       transition-colors
                                                                       {{ $hasFee
                                    ? 'text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 hover:bg-orange-50 dark:hover:bg-orange-900/30'
                                    : 'text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30' }}">
                                                {{ $hasFee
                                    ? '⚠️ ' . __('bookings.cancel_with_fee')
                                    : __('common.cancel') }}
                                            </button>
                                        </form>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($bookings->hasPages())
                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @endif

        @endif

    </div>
@endsection