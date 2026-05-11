@extends('layouts.admin')

@section('page-title', __('common.nav_bookings'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_bookings') }}</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('bookings.all_bookings') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('bookings.manage_bookings') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 flex-wrap">

            <div class="flex-1 min-w-48 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ __('bookings.search_placeholder') }}"
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <select name="status"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">{{ __('vehicles.all_statuses') }}</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>{{ __('common.pending') }}</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>{{ __('common.confirmed') }}</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>{{ __('bookings.active') }}</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('common.completed') }}</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('common.cancelled') }}</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="text-sm border border-gray-200 rounded-lg px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">

            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="text-sm border border-gray-200 rounded-lg px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-indigo-500">

            <button type="submit"
                    class="px-4 py-2 bg-gray-800 text-white text-sm font-medium
                           rounded-lg hover:bg-gray-700 transition-colors">
                {{ __('common.filter') }}
            </button>

            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.bookings.index') }}"
                   class="px-4 py-2 text-sm text-gray-600 border border-gray-200
                          rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('common.clear') }}
                </a>
            @endif

        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('bookings.reference') }}</th>
                        <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('common.customer') }}</th>
                        <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden md:table-cell">{{ __('vehicles.vehicle') }}</th>
                        <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">{{ __('vehicles.pickup_date') }}</th>
                        <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">{{ __('vehicles.return_date') }}</th>
                        <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('vehicles.status') }}</th>
                        <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden md:table-cell">{{ __('vehicles.total') }}</th>
                        <th class="text-right font-semibold text-gray-600 px-4 py-3">{{ __('vehicles.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50 transition-colors">

                            <td class="px-4 py-3">
                                <code class="text-xs bg-gray-100 px-2 py-0.5 rounded font-mono">
                                    {{ $booking->reference_code }}
                                </code>
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $booking->customer?->name }}</p>
                                <p class="text-xs text-gray-400">{{ $booking->customer?->email }}</p>
                            </td>

                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">{{ $booking->vehicle?->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ $booking->vehicle?->license_plate }}</p>
                            </td>

                            <td class="px-4 py-3 hidden lg:table-cell text-gray-600">
                                {{ $booking->pickup_date->format('M d, Y') }}
                            </td>

                            <td class="px-4 py-3 hidden lg:table-cell text-gray-600">
                                {{ $booking->return_date->format('M d, Y') }}
                            </td>

                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'pending'   => 'bg-yellow-50 text-yellow-700',
                                        'confirmed' => 'bg-blue-50 text-blue-700',
                                        'active'    => 'bg-green-50 text-green-700',
                                        'completed' => 'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             {{ $colors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 hidden md:table-cell font-semibold text-gray-900">
                                AFN {{ number_format($booking->total_amount) }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200
                                              rounded-lg hover:bg-indigo-50 transition-colors">
                                        {{ __('common.view') }}
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="font-medium">{{ __('bookings.no_bookings') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection