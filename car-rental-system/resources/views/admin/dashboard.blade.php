@extends('layouts.admin')

@section('page-title', __('common.dashboard'))

@section('content')
    <div class="space-y-6" x-data="dashboard()" x-init="init()">

        {{-- ─── Header ──────────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('common.dashboard') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F d Y') }}</p>
            </div>
            <button id="refreshBtn" onclick="refreshStats()" class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700
                               px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50
                               transition-colors shadow-sm">
                <svg id="refreshIcon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0
                              0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span id="refreshText">Refresh</span>
            </button>
        </div>

        {{-- ─── Pending Receipts Alert ───────────────────────────────────────────── --}}
        @if($stats['pending_receipts'] > 0)
            <div class="flex items-center justify-between px-5 py-4 bg-orange-50
                                    border border-orange-200 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                                              1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34
                                              16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-orange-800 text-sm">
                            {{ $stats['pending_receipts'] }} payments need attention
                        </p>
                        <div class="flex gap-3 mt-0.5">
                            @if($stats['pending_bank_transfer'] > 0)
                                <p class="text-xs text-orange-600">
                                    🏦 {{ $stats['pending_bank_transfer'] }} bank transfer receipts to review
                                </p>
                            @endif
                            @if($stats['pending_cash'] > 0)
                                <p class="text-xs text-yellow-600">
                                    💵 {{ $stats['pending_cash'] }} cash payments to confirm
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 bg-orange-600 text-white text-sm font-semibold
                                      rounded-lg hover:bg-orange-700 transition-colors">
                    {{ __('common.review') }}
                </a>
            </div>
        @endif

        {{-- ─── KPI Cards ────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

            {{-- Bookings Today --}}
            <a href="{{ route('admin.bookings.index') }}" class="bg-white rounded-xl border border-gray-200 p-5
                          hover:border-blue-300 transition-colors block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0
                                      00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p id="stat-bookings-today" class="text-2xl font-bold text-gray-900">
                    {{ $stats['bookings_today'] }}
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ __('dashboard.bookings_today') }}</p>
            </a>

            {{-- Revenue This Month --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3
                                      2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11
                                      0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">
                    AFN <span id="stat-revenue-month">
                        {{ number_format($stats['revenue_this_month_afn']) }}
                    </span>
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ __('dashboard.revenue_this_month') }}</p>
            </div>

            {{-- Active Rentals --}}
            <a href="{{ route('admin.bookings.index', ['status' => 'active']) }}" class="bg-white rounded-xl border border-gray-200 p-5
                          hover:border-green-300 transition-colors block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.5 7V13C20.5 16.7712 20.5 18.6569 19.3284 19.8284C18.1569 21
                 16.2712 21 12.5 21H11.5C7.72876 21 5.84315 21 4.67157 19.8284C3.5
                 18.6569 3.5 16.7712 3.5 13V7" />
                            <path d="M2 5C2 4.05719 2 3.58579 2.29289 3.29289C2.58579 3 3.05719 3 4
                 3H20C20.9428 3 21.4142 3 21.7071 3.29289C22 3.58579 22 4.05719 22
                 5C22 5.94281 22 6.41421 21.7071 6.70711C21.4142 7 20.9428 7 20 7H4C3.05719
                 7 2.58579 7 2.29289 6.70711C2 6.41421 2 5.94281 2 5Z" />
                            <path d="M9.5 13.4L10.9286 15L14.5 11" />
                        </svg>
                    </div>
                    @if($stats['active_rentals'] > 0)
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    @endif
                </div>
                <p id="stat-active-rentals" class="text-2xl font-bold text-gray-900">
                    {{ $stats['active_rentals'] }}
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ __('dashboard.active_rentals') }}</p>
            </a>

            {{-- Pending Payments --}}
            <a href="{{ route('admin.payments.index') }}" class="bg-white rounded-xl border border-gray-200 p-5
                          hover:border-yellow-300 transition-colors block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0
                                      00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <p id="stat-pending-receipts" class="text-2xl font-bold text-gray-900">
                    {{ $stats['pending_receipts'] }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-gray-500">{{ __('dashboard.pending_payments') }}</p>
                </div>
                @if($stats['pending_cash'] > 0 || $stats['pending_bank_transfer'] > 0)
                    <div class="flex gap-1 mt-2 flex-wrap">
                        @if($stats['pending_cash'] > 0)
                            <span class="px-1.5 py-0.5 bg-yellow-50 text-yellow-700
                                                             text-xs font-medium rounded">
                                💵 {{ $stats['pending_cash'] }} Cash
                            </span>
                        @endif
                        @if($stats['pending_bank_transfer'] > 0)
                            <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700
                                                             text-xs font-medium rounded">
                                🏦 {{ $stats['pending_bank_transfer'] }} Transfer
                            </span>
                        @endif
                    </div>
                @endif
            </a>

            {{-- Available Vehicles --}}
            <a href="{{ route('admin.vehicles.index') }}" class="bg-white rounded-xl border border-gray-200 p-5
                          hover:border-teal-300 transition-colors block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-teal-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p id="stat-available-vehicles" class="text-2xl font-bold text-gray-900">
                    {{ $stats['available_vehicles'] }}
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ __('dashboard.available_vehicles') }}</p>
            </a>

            {{-- Unread Messages --}}
            <a href="{{ route('admin.chat.index') }}" class="bg-white rounded-xl border border-gray-200 p-5
                          hover:border-purple-300 transition-colors block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0
                                      012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                </div>
                <p id="stat-unread-chats" class="text-2xl font-bold text-gray-900">
                    {{ $stats['unread_chats'] }}
                </p>
                <p class="text-xs text-gray-500 mt-1">{{ __('dashboard.unread_messages') }}</p>
            </a>

        </div>

        {{-- ─── Hidden stat IDs for JS refresh ─────────────────────────────────── --}}
        <span id="stat-total-vehicles" class="hidden">{{ $stats['total_vehicles'] }}</span>
        <span id="stat-booked-vehicles" class="hidden">{{ $stats['booked_vehicles'] }}</span>
        <span id="stat-maintenance-vehicles" class="hidden">{{ $stats['maintenance_vehicles'] }}</span>
        <span id="stat-bookings-month" class="hidden">{{ $stats['bookings_this_month'] }}</span>
        <span id="stat-pending-confirmations" class="hidden">{{ $stats['pending_confirmations'] }}</span>
        <span id="stat-confirmed-bookings" class="hidden">{{ $stats['confirmed_bookings'] }}</span>
        <span id="stat-completed-bookings" class="hidden">{{ $stats['completed_bookings'] }}</span>
        <span id="stat-cancelled-bookings" class="hidden">{{ $stats['cancelled_bookings'] }}</span>
        <span id="stat-revenue-today" class="hidden">{{ $stats['revenue_today_afn'] }}</span>

        {{-- ─── Charts Row ───────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Revenue Chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">{{ __('dashboard.revenue_overview') }}</h3>
                    <div class="flex gap-1">
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $key => $label)
                                        <button onclick="changePeriod('{{ $key }}')" id="btn-{{ $key }}" class="px-3 py-1 text-xs font-medium rounded-lg transition-colors
                                                                               {{ $key === 'monthly'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            {{ $label }}
                                        </button>
                        @endforeach
                    </div>
                </div>
                <canvas id="revenueChart" height="100"></canvas>
            </div>

            {{-- Booking Status Chart --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-bold text-gray-900 mb-4">{{ __('dashboard.booking_status') }}</h3>
                <canvas id="statusChart" height="180"></canvas>
                <div class="mt-4 space-y-2">
                    @php
                        $statusLabels = $statusData['labels'];
                        $statusColors = $statusData['colors'];
                        $statusCounts = $statusData['data'];
                    @endphp
                    @foreach($statusLabels as $i => $label)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full flex-shrink-0"
                                    style="background: {{ $statusColors[$i] }}"></span>
                                <span class="text-gray-600">{{ $label }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $statusCounts[$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ─── Bottom Row ───────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Top Vehicles --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-bold text-gray-900 mb-4">{{ __('dashboard.top_vehicles') }}</h3>
                @if(count($topVehicles) === 0)
                    <p class="text-sm text-gray-400 text-center py-6">{{ __('dashboard.no_data') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach($topVehicles as $v)
                            @php
                                $utilColor = $v['utilization'] >= 70
                                    ? 'bg-green-500'
                                    : ($v['utilization'] >= 40 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $v['vehicle_name'] }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ $v['bookings_count'] }} {{ __('dashboard.bookings') }} ·
                                            AFN {{ number_format($v['revenue_afn']) }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600">
                                        {{ $v['utilization'] }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="{{ $utilColor }} h-1.5 rounded-full transition-all"
                                        style="width: {{ $v['utilization'] }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Monthly Comparison --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-bold text-gray-900 mb-4">{{ __('dashboard.monthly_comparison') }}</h3>
                <div class="space-y-4">
                    @php
                        $comparisons = [
                            'bookings' => [
                                'label' => __('dashboard.bookings'),
                                'icon' => '📅',
                                'format' => 'number',
                            ],
                            'revenue' => [
                                'label' => __('dashboard.revenue'),
                                'icon' => '💰',
                                'format' => 'money',
                            ],
                            'customers' => [
                                'label' => __('dashboard.new_customers'),
                                'icon' => '👥',
                                'format' => 'number',
                            ],
                        ];
                    @endphp

                    @foreach($comparisons as $key => $config)
                        @php $data = $monthlyComparison[$key]; @endphp
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ $config['icon'] }}</span>
                                <div>
                                    <p class="text-xs text-gray-500">{{ $config['label'] }}</p>
                                    <p class="font-bold text-gray-900 text-sm">
                                        @if($config['format'] === 'money')
                                            AFN {{ number_format($data['this']) }}
                                        @else
                                            {{ number_format($data['this']) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1 justify-end">
                                    @if($data['up'])
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                        <span class="text-green-600 text-xs font-semibold">
                                            +{{ $data['change_percent'] }}%
                                        </span>
                                    @else
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                        <span class="text-red-600 text-xs font-semibold">
                                            {{ $data['change_percent'] }}%
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ __('dashboard.vs_last_month') }}:
                                    @if($config['format'] === 'money')
                                        AFN {{ number_format($data['last']) }}
                                    @else
                                        {{ number_format($data['last']) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ─── Recent Bookings ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">{{ __('dashboard.recent_bookings') }}</h3>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm text-indigo-600 hover:underline">
                    {{ __('common.view') }} {{ __('common.all') }} →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide">
                                {{ __('bookings.reference') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide hidden md:table-cell">
                                {{ __('common.customer') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide hidden lg:table-cell">
                                {{ __('vehicles.vehicle') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide hidden lg:table-cell">
                                {{ __('vehicles.pickup_date') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide">
                                {{ __('vehicles.status') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide">
                                {{ __('common.amount') }}
                            </th>
                            <th class="text-right font-semibold text-gray-500 px-5 py-3
                                           text-xs uppercase tracking-wide">
                                {{ __('vehicles.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentBookings as $booking)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-5 py-3">
                                                    <code class="text-xs bg-gray-100 px-2 py-0.5 rounded font-mono">
                                                                    {{ $booking->reference_code }}
                                                                </code>
                                                </td>
                                                <td class="px-5 py-3 hidden md:table-cell">
                                                    <p class="font-medium text-gray-900">
                                                        {{ $booking->customer?->name }}
                                                    </p>
                                                </td>
                                                <td class="px-5 py-3 hidden lg:table-cell text-gray-600">
                                                    {{ $booking->vehicle?->full_name }}
                                                </td>
                                                <td class="px-5 py-3 hidden lg:table-cell text-gray-500 text-xs">
                                                    {{ $booking->pickup_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-5 py-3">
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'bg-yellow-50 text-yellow-700',
                                                            'confirmed' => 'bg-blue-50 text-blue-700',
                                                            'active' => 'bg-green-50 text-green-700',
                                                            'completed' => 'bg-gray-100 text-gray-600',
                                                            'cancelled' => 'bg-red-50 text-red-700',
                                                        ];
                                                    @endphp
                             <span
                                                        class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                                             {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-3 font-semibold text-gray-900">
                                                    AFN {{ number_format($booking->total_amount) }}
                                                </td>
                                                <td class="px-5 py-3 text-right">
                                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                                        class="text-xs text-indigo-600 font-medium hover:underline">
                                                        {{ __('common.view') }}
                                                    </a>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">
                                    {{ __('bookings.no_bookings') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        const revenueData = @json($revenueData);
        const statusData = @json($statusData);

        // Revenue Line Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        let revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.labels,
                datasets: [{
                    label: 'Revenue (AFN)',
                    data: revenueData.data,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'AFN ' + new Intl.NumberFormat().format(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => 'AFN ' + new Intl.NumberFormat().format(val),
                            font: { size: 11 },
                        },
                        grid: { color: '#f3f4f6' },
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false },
                    },
                },
            }
        });

        // Status Doughnut Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.data,
                    backgroundColor: statusData.colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw}`
                        }
                    }
                },
            }
        });

        async function changePeriod(period) {
            ['daily', 'weekly', 'monthly'].forEach(p => {
                const btn = document.getElementById('btn-' + p);
                btn.className = p === period
                    ? 'px-3 py-1 text-xs font-medium rounded-lg transition-colors bg-indigo-600 text-white'
                    : 'px-3 py-1 text-xs font-medium rounded-lg transition-colors bg-gray-100 text-gray-600 hover:bg-gray-200';
            });

            try {
                const res = await fetch(`/admin/api/charts/revenue?period=${period}`, {
                    credentials: 'include',
                });
                const data = await res.json();
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.data;
                revenueChart.update();
            } catch (e) {
                console.error('Failed to load chart data:', e);
            }
        }

        async function refreshStats() {
            const btn = document.getElementById('refreshBtn');
            const icon = document.getElementById('refreshIcon');
            const text = document.getElementById('refreshText');

            icon.classList.add('animate-spin');
            text.textContent = 'Refreshing...';
            btn.disabled = true;

            try {
                const response = await fetch('/admin/api/stats', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'include',
                });

                if (!response.ok) throw new Error('Server error');

                const result = await response.json();
                const stats = result.data;

                const updates = {
                    'stat-total-vehicles': stats.total_vehicles,
                    'stat-available-vehicles': stats.available_vehicles,
                    'stat-booked-vehicles': stats.booked_vehicles,
                    'stat-maintenance-vehicles': stats.maintenance_vehicles,
                    'stat-bookings-today': stats.bookings_today,
                    'stat-bookings-month': stats.bookings_this_month,
                    'stat-pending-confirmations': stats.pending_confirmations,
                    'stat-confirmed-bookings': stats.confirmed_bookings,
                    'stat-active-rentals': stats.active_rentals,
                    'stat-completed-bookings': stats.completed_bookings,
                    'stat-cancelled-bookings': stats.cancelled_bookings,
                    'stat-revenue-today': stats.revenue_today_afn,
                    'stat-revenue-month': new Intl.NumberFormat().format(Math.round(stats.revenue_this_month_afn)),
                    'stat-pending-receipts': stats.pending_receipts,
                    'stat-unread-chats': stats.unread_chats,
                };

                Object.entries(updates).forEach(([id, value]) => {
                    const el = document.getElementById(id);
                    if (el && value !== undefined) {
                        el.textContent = value;
                        el.style.transition = 'color 0.3s';
                        el.style.color = '#2563eb';
                        setTimeout(() => el.style.color = '', 600);
                    }
                });

                icon.classList.remove('animate-spin');
                text.textContent = '✓ Updated';
                setTimeout(() => {
                    text.textContent = 'Refresh';
                    btn.disabled = false;
                }, 2000);

            } catch (e) {
                icon.classList.remove('animate-spin');
                text.textContent = 'Failed — Retry';
                btn.disabled = false;
            }
        }

        setInterval(refreshStats, 30000);
        setTimeout(refreshStats, 2000);

    </script>
@endpush