@extends('layouts.admin')

@section('page-title', __('common.nav_reports'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_reports') }}</span>
@endsection

@section('content')
    @php
        $locale = app()->getLocale();
        $isFa = $locale === 'fa';
        $isPs = $locale === 'ps';

        if (!function_exists('repT')) {
            function repT($en, $fa, $ps)
            {
                $l = app()->getLocale();
                if ($l === 'fa')
                    return $fa;
                if ($l === 'ps')
                    return $ps;
                return $en;
            }
        }

        $statusLabels = [
            'pending' => __('common.pending'),
            'confirmed' => __('common.confirmed'),
            'active' => __('common.active'),
            'completed' => __('common.completed'),
            'cancelled' => __('common.cancelled'),
        ];

        $methodLabels = [
            'cash' => repT('Cash', 'نقدی', 'نغده'),
            'online' => repT('Online', 'آنلاین', 'آنلاین'),
            'bank_transfer' => repT('Bank Transfer', 'انتقال بانکی', 'د بانک لیږد'),
            'counter' => repT('Counter', 'کانتر', 'کانتر'),
            'visa' => 'Visa',
            'mastercard' => 'Mastercard',
            'amex' => 'Amex',
            'card' => repT('Card', 'کارت', 'کارت'),
        ];

        $payStatusLabels = [
            'paid' => __('common.paid'),
            'pending' => __('common.pending'),
            'receipt_uploaded' => __('payments.needs_review'),
            'failed' => __('common.failed'),
            'rejected' => __('common.cancelled'),
        ];
    @endphp

    <div class="space-y-5">

        {{-- ─── Header ────────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ repT('Booking Reports', 'گزارش رزروها', 'د بکینګونو راپورونه') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ repT(
        'Full booking records with customer and payment details',
        'سوابق کامل رزرو با جزئیات مشتری و پرداخت',
        'د پیرودونکي او تادیې توضیحاتو سره بشپړ بکینګ ریکارډونه'
    ) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.csv', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm
                          font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1
                              0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ repT('Export CSV', 'خروجی CSV', 'CSV صادرول') }}
                </a>
                <a href="{{ route('admin.reports.pdf', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm
                          font-semibold rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0
                              0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    {{ repT('Export PDF', 'خروجی PDF', 'PDF صادرول') }}
                </a>
            </div>
        </div>

        {{-- ─── Filters ─────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">

                {{-- Date From --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ repT('From', 'از تاریخ', 'له') }}
                    </label>
                    <input type="date" name="from" value="{{ $from }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Date To --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ repT('To', 'تا تاریخ', 'تر') }}
                    </label>
                    <input type="date" name="to" value="{{ $to }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Status --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ repT('Status', 'وضعیت', 'حالت') }}
                    </label>
                    <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>
                            {{ repT('All Statuses', 'همه وضعیت‌ها', 'ټول حالتونه') }}
                        </option>
                        @foreach(['pending', 'confirmed', 'active', 'completed', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                {{ $statusLabels[$s] ?? ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Method --}}
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ repT('Payment Method', 'روش پرداخت', 'د تادیې طریقه') }}
                    </label>
                    <select name="payment_method" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" {{ request('payment_method', 'all') === 'all' ? 'selected' : '' }}>
                            {{ repT('All Methods', 'همه روش‌ها', 'ټولې طریقې') }}
                        </option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>
                            {{ repT('Cash', 'نقدی', 'نغده') }}
                        </option>
                        <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>
                            {{ repT('Online', 'آنلاین', 'آنلاین') }}
                        </option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>
                            {{ repT('Bank Transfer', 'انتقال بانکی', 'د بانک لیږد') }}
                        </option>
                        <option value="counter" {{ request('payment_method') === 'counter' ? 'selected' : '' }}>
                            {{ repT('Counter', 'کانتر', 'کانتر') }}
                        </option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="flex flex-col gap-1 flex-1 min-w-48">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ repT('Search', 'جستجو', 'لټون') }}
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ repT('Customer name or reference...', 'نام مشتری یا کد رزرو...', 'د پیرودونکي نوم یا راجع...') }}"
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold
                                   rounded-lg hover:bg-indigo-700 transition-colors">
                        {{ repT('Apply', 'اعمال', 'پلي کول') }}
                    </button>
                    <a href="{{ route('admin.reports') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-200
                              rounded-lg hover:bg-gray-50 transition-colors">
                        {{ repT('Reset', 'بازنشانی', 'بیا تنظیم') }}
                    </a>
                </div>

            </form>
        </div>

        {{-- ─── Summary Row ──────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                    {{ repT('Total Bookings', 'مجموع رزروها', 'ټول بکینګونه') }}
                </p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $bookings->total() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                    {{ repT('Total Revenue', 'مجموع درآمد', 'ټول عاید') }}
                </p>
                <p class="text-2xl font-bold text-green-600 mt-1">
                    AFN {{ number_format($totalRevenue, 0) }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">
                    {{ repT('Confirmed', 'تأیید شده', 'تایید شوی') }}
                </p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $confirmedCount }}</p>
            </div>
        </div>

        {{-- ─── Table ────────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Reference', 'کد رزرو', 'راجع') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Customer', 'مشتری', 'پیرودونکی') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Phone', 'تلفن', 'تلیفون') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('License No.', 'شماره گواهینامه', 'د جواز شمیره') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Vehicle', 'موتر', 'موټر') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Pickup', 'تحویل', 'تحویل') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Return', 'بازگشت', 'بیرته راستنیدل') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Days', 'روز', 'ورځې') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Total AFN', 'مجموع (افغانی)', 'ټول (افغانۍ)') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Payment', 'پرداخت', 'تادیه') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Status', 'وضعیت', 'حالت') }}
                            </th>
                            <th class="text-left font-semibold text-gray-500 px-4 py-3 text-xs uppercase tracking-wide">
                                {{ repT('Created', 'تاریخ ثبت', 'جوړیدو نیټه') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $i => $booking)
                            @php
                                $payment = $booking->payments->first();
                                $days = $booking->pickup_date?->diffInDays($booking->return_date) ?? 0;

                                $statusColors = [
                                    'pending' => 'bg-yellow-50 text-yellow-700',
                                    'confirmed' => 'bg-blue-50 text-blue-700',
                                    'active' => 'bg-green-50 text-green-700',
                                    'completed' => 'bg-gray-100 text-gray-600',
                                    'cancelled' => 'bg-red-50 text-red-700',
                                ];

                                $methodColors = [
                                    'cash' => 'bg-green-50 text-green-700',
                                    'online' => 'bg-blue-50 text-blue-700',
                                    'bank_transfer' => 'bg-purple-50 text-purple-700',
                                    'counter' => 'bg-gray-100 text-gray-600',
                                    'visa' => 'bg-blue-50 text-blue-700',
                                    'mastercard' => 'bg-orange-50 text-orange-700',
                                    'card' => 'bg-purple-50 text-purple-700',
                                ];

                                $payStatusColors = [
                                    'paid' => 'bg-green-50 text-green-700',
                                    'pending' => 'bg-yellow-50 text-yellow-700',
                                    'receipt_uploaded' => 'bg-orange-50 text-orange-700',
                                    'failed' => 'bg-red-50 text-red-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                ];
                            @endphp
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors
                                           {{ $i % 2 === 0 ? '' : 'bg-gray-50/50' }}">

                                {{-- Reference --}}
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                        class="font-mono text-xs font-semibold text-indigo-600 hover:underline">
                                        {{ $booking->reference_code }}
                                    </a>
                                </td>

                                {{-- Customer --}}
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">
                                        {{ $booking->customer?->name ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $booking->customer?->email ?? '' }}
                                    </p>
                                </td>

                                {{-- Phone --}}
                                <td class="px-4 py-3 text-gray-600 text-xs" dir="ltr">
                                    {{ $booking->customer?->phone ?? '—' }}
                                </td>

                                {{-- License Number --}}
                                <td class="px-4 py-3">
                                    @if($booking->customer?->driver_license_number)
                                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono">
                                                    {{ $booking->customer->driver_license_number }}
                                                </code>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Vehicle --}}
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 text-xs">
                                        {{ $booking->vehicle?->brand }} {{ $booking->vehicle?->model }}
                                    </p>
                                    <p class="text-xs text-gray-400 font-mono">
                                        {{ $booking->vehicle?->license_plate }}
                                    </p>
                                </td>

                                {{-- Pickup --}}
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $booking->pickup_date?->translatedFormat('M d, Y') }}
                                    <span class="block text-gray-400">
                                        {{ $booking->pickup_date?->format('H:i') }}
                                    </span>
                                </td>

                                {{-- Return --}}
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $booking->return_date?->translatedFormat('M d, Y') }}
                                    <span class="block text-gray-400">
                                        {{ $booking->return_date?->format('H:i') }}
                                    </span>
                                </td>

                                {{-- Days --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-semibold text-gray-700">{{ $days }}</span>
                                </td>

                                {{-- Total --}}
                                <td class="px-4 py-3">
                                    <span class="font-bold text-gray-900">
                                        {{ number_format($booking->total_amount, 0) }}
                                    </span>
                                </td>

                                {{-- Payment --}}
                                <td class="px-4 py-3">
                                    @if($payment)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                             text-xs font-semibold
                                                             {{ $methodColors[$payment->method] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $methodLabels[$payment->method] ?? ucfirst(str_replace('_', ' ', $payment->method)) }}
                                        </span>
                                        <span
                                            class="block mt-1 inline-flex items-center px-2 py-0.5
                                                             rounded-full text-xs font-semibold
                                                             {{ $payStatusColors[$payment->status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $payStatusLabels[$payment->status] ?? ucfirst(str_replace('_', ' ', $payment->status)) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">
                                            {{ repT('No payment', 'پرداختی نیست', 'تادیه نشته') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Booking Status --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                                     text-xs font-semibold
                                                     {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabels[$booking->status] ?? ucfirst($booking->status) }}
                                    </span>
                                </td>

                                {{-- Created --}}
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $booking->created_at->translatedFormat('M d, Y') }}
                                    <span class="block text-gray-400">
                                        {{ $booking->created_at->format('H:i') }}
                                    </span>
                                </td>

                            </tr>
                        @empty
                                            <tr>
                                                <td colspan="12" class="px-4 py-12 text-center text-gray-400">
                                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9
                                                                  5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    <p class="font-medium">
                                                        {{ repT(
                                'No bookings found for the selected filters.',
                                'هیچ رزروی برای فیلترهای انتخابی یافت نشد.',
                                'د ټاکل شویو چاڼونو لپاره هیڅ بکینګ ونه موندل شو.'
                            ) }}
                                                    </p>
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