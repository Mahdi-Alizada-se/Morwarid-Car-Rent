@extends('layouts.admin')

@section('page-title', __('common.nav_payments'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_payments') }}</span>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('payments.all_payments') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('payments.manage_payments') }}</p>
            </div>
            @if($needsReviewCount > 0)
                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 border border-orange-200
                                 text-orange-700 text-sm font-semibold rounded-xl">
                    <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                    {{ $needsReviewCount }} {{ __('payments.needs_review') }}
                </span>
            @endif
        </div>

        {{-- Status Filter Tabs --}}
        <div class="flex gap-2 flex-wrap">
            @php
                $tabs = [
                    '' => __('common.all'),
                    'receipt_uploaded' => __('payments.needs_review') . ($needsReviewCount > 0 ? ' (' . $needsReviewCount . ')' : ''),
                    'paid' => __('common.confirmed'),
                    'rejected' => __('common.cancelled'),
                    'pending' => __('common.pending'),
                ];
            @endphp
            @foreach($tabs as $value => $label)
                <a href="{{ route('admin.payments.index', array_merge(request()->query(), ['status' => $value])) }}" class="px-4 py-2 text-sm font-medium rounded-xl transition-all
                       {{ request('status', '') === $value
                ? ($value === 'receipt_uploaded' ? 'bg-orange-600 text-white' : 'bg-indigo-600 text-white')
                : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('bookings.reference') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('common.customer') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden md:table-cell">
                                {{ __('payments.method') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('common.amount') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('vehicles.status') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">
                                {{ __('common.date') }}</th>
                            <th class="text-right font-semibold text-gray-600 px-4 py-3">{{ __('vehicles.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50 transition-colors">

                                <td class="px-4 py-3">
                                    <code class="text-xs bg-gray-100 px-2 py-0.5 rounded font-mono">
                                            {{ $payment->booking?->reference_code }}
                                        </code>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $payment->booking?->customer?->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $payment->booking?->customer?->email }}</p>
                                </td>

                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if($payment->method === 'counter')
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                            {{ __('payments.counter') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                            {{ __('payments.bank_transfer') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 font-semibold text-gray-900">
                                    AFN {{ number_format($payment->amount) }}
                                </td>

                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-gray-100 text-gray-600',
                                            'receipt_uploaded' => 'bg-orange-50 text-orange-700',
                                            'paid' => 'bg-green-50 text-green-700',
                                            'rejected' => 'bg-red-50 text-red-700',
                                            'refunded' => 'bg-purple-50 text-purple-700',
                                        ];
                                        $statusLabels = [
                                            'pending' => __('common.pending'),
                                            'receipt_uploaded' => __('payments.needs_review'),
                                            'paid' => __('common.confirmed'),
                                            'rejected' => __('common.cancelled'),
                                            'refunded' => __('common.refunded'),
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     {{ $statusColors[$payment->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabels[$payment->status] ?? ucfirst($payment->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs">
                                    {{ $payment->created_at->format('M d, Y') }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200
                                                      rounded-lg hover:bg-indigo-50 transition-colors">
                                            {{ __('common.view') }}
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <p class="font-medium">{{ __('payments.no_payments') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection