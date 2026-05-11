@extends('layouts.admin')

@section('page-title', __('common.nav_reports'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_reports') }}</span>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('reports.title') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('reports.subtitle') }}</p>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-3xl font-bold text-gray-900">{{ $totalBookings }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ __('reports.total_bookings') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-2xl font-bold text-green-600">AFN {{ number_format($totalRevenue) }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ __('reports.total_revenue') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-3xl font-bold text-gray-900">{{ $completedCount }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ __('reports.completed_rentals') }}</p>
            </div>
        </div>

        {{-- Filter Form --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 flex-wrap items-end">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('common.start_date') }}</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('common.end_date') }}</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('vehicles.status') }}</label>
                    <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('vehicles.all_statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                            {{ __('common.pending') }}</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>
                            {{ __('common.confirmed') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                            {{ __('bookings.active') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                            {{ __('common.completed') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                            {{ __('common.cancelled') }}</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium
                               rounded-lg hover:bg-indigo-700 transition-colors">
                    {{ __('common.filter') }}
                </button>

                @if(request()->hasAny(['from', 'to', 'status']))
                    <a href="{{ route('admin.reports') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-200
                                  rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('common.clear') }}
                    </a>
                @endif

                {{-- Export Buttons --}}
                <div class="flex gap-2 sm:ml-auto">
                    <a href="{{ route('admin.reports.csv') }}?{{ http_build_query(request()->only(['from', 'to', 'status'])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white
                              text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('reports.export_csv') }}
                    </a>
                    <a href="{{ route('admin.reports.pdf') }}?{{ http_build_query(request()->only(['from', 'to', 'status'])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white
                              text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('reports.export_pdf') }}
                    </a>
                </div>
            </form>
        </div>

        {{-- Bookings Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('bookings.reference') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden md:table-cell">
                                {{ __('common.customer') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">
                                {{ __('vehicles.vehicle') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">
                                {{ __('vehicles.pickup_date') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('vehicles.status') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('common.amount') }}</th>
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
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <p class="font-medium text-gray-900">{{ $booking->customer?->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $booking->customer?->email }}</p>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell text-gray-600">
                                    {{ $booking->vehicle?->full_name }}
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs">
                                    {{ $booking->pickup_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-50 text-yellow-700',
                                            'confirmed' => 'bg-blue-50 text-blue-700',
                                            'active' => 'bg-green-50 text-green-700',
                                            'completed' => 'bg-gray-100 text-gray-600',
                                            'cancelled' => 'bg-red-50 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    AFN {{ number_format($booking->total_amount) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                        class="text-xs text-indigo-600 font-medium hover:underline">
                                        {{ __('common.view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    {{ __('bookings.no_bookings') }}
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