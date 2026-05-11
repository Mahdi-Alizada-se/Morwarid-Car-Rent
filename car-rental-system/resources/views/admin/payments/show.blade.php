@extends('layouts.admin')

@section('page-title', __('payments.payment_detail'))
@section('breadcrumb')
    <a href="{{ route('admin.payments.index') }}" class="hover:text-gray-700">{{ __('common.nav_payments') }}</a>
    <span>/</span>
    <span class="text-gray-900 font-medium">{{ $payment->booking?->reference_code }}</span>
@endsection

@section('content')
    <div class="max-w-5xl space-y-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ─── Left Column ──────────────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Booking Info --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('bookings.booking_details') }}</h3>
                    <div class="flex items-center gap-4 mb-4">
                        @if($payment->booking?->vehicle?->thumbnail)
                            <img src="{{ asset('storage/' . $payment->booking->vehicle->thumbnail) }}"
                                class="w-20 h-14 object-cover rounded-xl border border-gray-200">
                        @endif
                        <div>
                            <p class="font-bold text-gray-900">{{ $payment->booking?->vehicle?->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $payment->booking?->vehicle?->license_plate }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">{{ __('bookings.reference') }}</p>
                            <p class="font-mono font-bold text-gray-900">{{ $payment->booking?->reference_code }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('vehicles.total') }}</p>
                            <p class="font-bold text-indigo-600 text-lg">AFN {{ number_format($payment->amount) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('vehicles.pickup_date') }}</p>
                            <p class="font-medium text-gray-900">{{ $payment->booking?->pickup_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('vehicles.return_date') }}</p>
                            <p class="font-medium text-gray-900">{{ $payment->booking?->return_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Payment Detail Card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('payments.payment_detail') }}</h3>

                    <div class="grid grid-cols-2 gap-3 text-sm mb-5">
                        <div>
                            <p class="text-xs text-gray-500">{{ __('payments.method') }}</p>
                            <p class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->method) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('vehicles.status') }}</p>
                            <p class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->status) }}
                            </p>
                        </div>
                        @if($payment->bank_reference)
                            <div>
                                <p class="text-xs text-gray-500">{{ __('payments.bank_reference') }}</p>
                                <p class="font-mono font-semibold text-gray-900">{{ $payment->bank_reference }}</p>
                            </div>
                        @endif
                        @if($payment->paid_at)
                            <div>
                                <p class="text-xs text-gray-500">{{ __('payments.paid_at') }}</p>
                                <p class="font-semibold text-gray-900">{{ $payment->paid_at->format('M d, Y H:i') }}</p>
                            </div>
                        @endif
                        @if($payment->confirmedByUser)
                            <div>
                                <p class="text-xs text-gray-500">{{ __('payments.confirmed_by') }}</p>
                                <p class="font-semibold text-gray-900">{{ $payment->confirmedByUser->name }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Receipt Image --}}
                    @if($payment->method === 'bank_transfer' && $receiptUrl)
                        <div class="mb-5">
                            <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('payments.receipt_image') }}</p>
                            <a href="{{ $receiptUrl }}" target="_blank">
                                @if(str_contains($payment->receipt_path, '.pdf'))
                                    <div
                                        class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors">
                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">{{ __('payments.view_pdf_receipt') }}</span>
                                    </div>
                                @else
                                    <img src="{{ $receiptUrl }}"
                                        class="max-h-64 rounded-xl border border-gray-200 cursor-zoom-in object-contain w-full"
                                        alt="Receipt">
                                @endif
                            </a>
                        </div>
                    @endif

                    {{-- Rejection Reason --}}
                    @if($payment->status === 'rejected' && $payment->rejection_reason)
                        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <p class="text-sm font-semibold text-red-800">{{ __('payments.rejection_reason') }}</p>
                            <p class="text-sm text-red-700 mt-1">{{ $payment->rejection_reason }}</p>
                        </div>
                    @endif

                    {{-- Invoice Link --}}
                    @if($payment->status === 'paid' && $invoiceUrl)
                        <div class="mb-5">
                            <a href="{{ $invoiceUrl }}" target="_blank"
                                class="flex items-center gap-2 px-4 py-3 bg-indigo-50 border border-indigo-200
                                          text-indigo-700 rounded-xl hover:bg-indigo-100 transition-colors text-sm font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('payments.download_invoice') }}
                            </a>
                        </div>
                    @endif

                </div>

            </div>

            {{-- ─── Right Column ──────────────────────────────────────────────────── --}}
            <div class="space-y-5">

                {{-- Customer Card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('common.customer') }}</h3>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center
                                    text-indigo-700 font-semibold text-sm">
                            {{ strtoupper(substr($payment->booking?->customer?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $payment->booking?->customer?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $payment->booking?->customer?->email }}</p>
                        </div>
                    </div>
                    @if($payment->booking?->customer?->phone)
                        <p class="text-sm text-gray-600 mt-2">📞 {{ $payment->booking->customer->phone }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('common.actions') }}</h3>

                    {{-- Bank Transfer Actions --}}
                    @if($payment->method === 'bank_transfer')

                        @if(in_array($payment->status, ['receipt_uploaded', 'pending']))

                            {{-- Confirm Button --}}
                            <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="mb-3">
                                @csrf
                                <button type="submit" onclick="return confirm('{{ __('payments.confirm_payment_confirm') }}')"
                                    class="w-full py-2.5 bg-green-600 text-white text-sm font-semibold
                                                       rounded-xl hover:bg-green-700 transition-colors">
                                    ✓ {{ __('payments.confirm_payment') }}
                                </button>
                            </form>

                            {{-- Reject Form --}}
                            <div x-data="{ showReject: false }">
                                <button @click="showReject = !showReject" class="w-full py-2.5 border border-red-200 text-red-600 text-sm font-medium
                                                       rounded-xl hover:bg-red-50 transition-colors">
                                    ✗ {{ __('payments.reject_receipt') }}
                                </button>
                                <div x-show="showReject" x-cloak class="mt-3">
                                    <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                        @csrf
                                        <textarea name="reason" rows="3" required
                                            placeholder="{{ __('payments.rejection_reason_placeholder') }}" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5
                                                                 focus:outline-none focus:ring-2 focus:ring-red-400 mb-2">
                                                </textarea>
                                        <button type="submit" class="w-full py-2 bg-red-600 text-white text-sm font-semibold
                                                               rounded-xl hover:bg-red-700 transition-colors">
                                            {{ __('payments.reject_and_notify') }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                        @elseif($payment->status === 'paid')
                            <div class="text-center py-4">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-green-700">{{ __('payments.already_confirmed') }}</p>
                            </div>
                        @endif

                        {{-- Counter Payment Actions --}}
                    @elseif($payment->method === 'counter' && $payment->status !== 'paid')
                        <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-green-600 text-white text-sm font-semibold
                                               rounded-xl hover:bg-green-700 transition-colors">
                                {{ __('payments.mark_paid_counter') }}
                            </button>
                        </form>

                    @elseif($payment->status === 'paid')
                        <div class="text-center py-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-green-700">{{ __('payments.already_confirmed') }}</p>
                        </div>
                    @endif

                </div>

            </div>
        </div>

    </div>
@endsection