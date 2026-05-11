@extends('layouts.app')

@section('title', __('common.my_bookings'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('common.my_bookings') }}</h1>
            <p class="text-gray-500 mt-1 text-sm">{{ __('bookings.booking_history') }}</p>
        </div>

        @if($bookings->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg class="w-14 h-14 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="font-semibold text-gray-600 text-lg">{{ __('bookings.no_bookings') }}</p>
                <p class="text-sm text-gray-400 mt-2">{{ __('bookings.no_bookings_desc') }}</p>
                <a href="{{ route('vehicles.index') }}" class="inline-block mt-5 px-5 py-2.5 bg-indigo-600 text-white text-sm
                              font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                    {{ __('common.nav_vehicles') }}
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

                            {{-- Vehicle Thumbnail --}}
                            <div class="flex-shrink-0">
                                @if($booking->vehicle?->thumbnail)
                                    <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                                        class="w-20 h-14 object-cover rounded-xl border border-gray-200"
                                        alt="{{ $booking->vehicle?->full_name }}">
                                @else
                                    <div class="w-20 h-14 bg-gray-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Booking Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 flex-wrap">
                                    <div>
                                        <p class="font-bold text-gray-900">
                                            {{ $booking->vehicle?->full_name ?? '—' }}
                                        </p>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">
                                            {{ $booking->reference_code }}
                                        </p>
                                    </div>

                                    {{-- Status Badge --}}
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-50 text-yellow-700',
                                            'confirmed' => 'bg-blue-50 text-blue-700',
                                            'active' => 'bg-green-50 text-green-700',
                                            'completed' => 'bg-gray-100 text-gray-600',
                                            'cancelled' => 'bg-red-50 text-red-700',
                                        ];
                                        $color = $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                                         text-xs font-semibold {{ $color }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-500">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $booking->pickup_date->format('M d, Y') }}
                                        → {{ $booking->return_date->format('M d, Y') }}
                                    </span>
                                    <span class="font-semibold text-gray-900">
                                        AFN {{ number_format($booking->total_amount) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('customer.bookings.show', $booking) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200
                                                  rounded-lg hover:bg-indigo-50 transition-colors">
                                    {{ __('common.view') }}
                                </a>

                                @if($booking->canBeCancelled())
                                    <form method="POST" action="{{ route('customer.bookings.cancel', $booking) }}"
                                        onsubmit="return confirm('{{ __('bookings.cancel_confirm') }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200
                                                                   rounded-lg hover:bg-red-50 transition-colors">
                                            {{ __('common.cancel') }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif

    </div>
@endsection