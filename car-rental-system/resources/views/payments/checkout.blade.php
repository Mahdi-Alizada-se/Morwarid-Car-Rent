@extends('layouts.app')

@section('title', __('payments.checkout'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Booking Summary --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">{{ __('bookings.booking_details') }}</h2>
            <div class="flex items-center gap-4">
                @if($booking->vehicle?->thumbnail)
                    <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                        class="w-20 h-14 object-cover rounded-xl border border-gray-200">
                @endif
                <div class="flex-1">
                    <p class="font-bold text-gray-900">{{ $booking->vehicle?->full_name }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $booking->pickup_date->format('M d, Y') }}
                        → {{ $booking->return_date->format('M d, Y') }}
                        · {{ $booking->duration_in_days }} {{ __('vehicles.days') }}
                    </p>
                    <p class="text-xs font-mono text-gray-400 mt-1">{{ $booking->reference_code }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-indigo-600">
                        AFN {{ number_format($booking->total_amount) }}
                    </p>
                    <p class="text-xs text-gray-400">{{ __('vehicles.total') }}</p>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-gray-900 mb-5">{{ __('payments.choose_method') }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-data="{
                selectedMethod: '',
                bankReference: '',
                receiptFile: null,
                receiptPreview: null,
                uploading: false,
                success: false,
                error: '',

                handleFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.receiptFile = file;
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => this.receiptPreview = e.target.result;
                        reader.readAsDataURL(file);
                    } else {
                        this.receiptPreview = null;
                    }
                },

                async submitReceipt() {
                    if (!this.receiptFile || !this.bankReference) return;
                    this.uploading = true;
                    this.error = '';

                    const formData = new FormData();
                    formData.append('receipt', this.receiptFile);
                    formData.append('bank_reference', this.bankReference);
                    formData.append('_token', document.querySelector('meta[name=csrf-token]').content);

                    try {
                        const res = await fetch('/api/v1/payments/{{ $payment?->id ?? '' }}/upload-receipt', {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.success = true;
                        } else {
                            this.error = data.message ?? 'Upload failed.';
                        }
                    } catch(e) {
                        this.error = 'Upload failed. Please try again.';
                    }
                    this.uploading = false;
                }
             }">

            {{-- ─── Pay at Counter ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border-2 transition-all cursor-pointer" :class="selectedMethod === 'counter'
                     ? 'border-indigo-500 shadow-md'
                     : 'border-gray-200 hover:border-gray-300'" @click="selectedMethod = 'counter'">

                <div class="p-6">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">{{ __('payments.pay_at_counter') }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('payments.pay_at_counter_desc') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600 mb-5">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('payments.counter_point1') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('payments.counter_point2') }}
                        </li>
                    </ul>

                    <div x-show="selectedMethod === 'counter'">
                        <form method="POST" action="{{ route('customer.payments.counter') }}">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <button type="submit" class="w-full py-3 bg-green-600 text-white font-semibold
                                           rounded-xl hover:bg-green-700 transition-colors text-sm">
                                {{ __('payments.select_counter') }}
                            </button>
                        </form>
                    </div>
                    <div x-show="selectedMethod !== 'counter'">
                        <button @click.stop="selectedMethod = 'counter'" class="w-full py-3 border border-gray-200 text-gray-700 font-medium
                                       rounded-xl hover:bg-gray-50 transition-colors text-sm">
                            {{ __('payments.select_this_method') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- ─── Bank Transfer ───────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border-2 transition-all" :class="selectedMethod === 'bank'
                     ? 'border-indigo-500 shadow-md'
                     : 'border-gray-200 hover:border-gray-300'">

                <div class="p-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">{{ __('payments.bank_transfer') }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('payments.bank_transfer_desc') }}</p>

                    {{-- Bank Account Details --}}
                    <div class="space-y-3 mb-5">
                        @foreach($bankAccounts as $account)
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                <p class="font-semibold text-blue-900 text-sm mb-2">{{ $account->bank_name }}</p>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">{{ __('payments.account_name') }}</span>
                                        <span class="font-medium text-gray-900">{{ $account->account_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">{{ __('payments.account_number') }}</span>
                                        <span class="font-mono font-bold text-blue-700">{{ $account->account_number }}</span>
                                    </div>
                                    @if($account->branch)
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">{{ __('payments.branch') }}</span>
                                            <span class="font-medium text-gray-900">{{ $account->branch }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between border-t border-blue-200 pt-2 mt-2">
                                        <span class="text-gray-500">{{ __('payments.amount_to_transfer') }}</span>
                                        <span class="font-bold text-indigo-600">AFN
                                            {{ number_format($booking->total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div x-show="selectedMethod !== 'bank'" @click.stop="selectedMethod = 'bank'">
                        <button class="w-full py-3 border border-gray-200 text-gray-700 font-medium
                                       rounded-xl hover:bg-gray-50 transition-colors text-sm">
                            {{ __('payments.select_this_method') }}
                        </button>
                    </div>

                    {{-- Upload Form --}}
                    <div x-show="selectedMethod === 'bank'" x-cloak>

                        {{-- Success Message --}}
                        <div x-show="success" class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                            <svg class="w-10 h-10 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="font-semibold text-green-800">{{ __('payments.receipt_submitted') }}</p>
                            <p class="text-sm text-green-600 mt-1">{{ __('payments.receipt_review_time') }}</p>
                            <a href="{{ route('customer.bookings.index') }}"
                                class="inline-block mt-3 text-sm text-indigo-600 hover:underline">
                                {{ __('bookings.back_to_bookings') }}
                            </a>
                        </div>

                        {{-- Upload Form --}}
                        <div x-show="!success">

                            {{-- Error --}}
                            <div x-show="error"
                                class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
                                x-text="error"></div>

                            {{-- Bank Reference --}}
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('payments.bank_reference') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="bankReference"
                                    placeholder="{{ __('payments.bank_reference_placeholder') }}" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            {{-- File Upload --}}
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('payments.upload_receipt') }} <span class="text-red-500">*</span>
                                </label>

                                {{-- Drop zone --}}
                                <label class="block border-2 border-dashed border-gray-300 rounded-xl p-6
                                              text-center cursor-pointer hover:border-indigo-400 transition-colors"
                                    :class="receiptFile ? 'border-indigo-400 bg-indigo-50' : ''">
                                    <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                                        @change="handleFile($event)">

                                    <template x-if="!receiptFile">
                                        <div>
                                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="text-sm text-gray-500">{{ __('payments.drag_drop') }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ __('payments.file_types') }}</p>
                                        </div>
                                    </template>

                                    <template x-if="receiptFile && receiptPreview">
                                        <div>
                                            <img :src="receiptPreview"
                                                class="max-h-32 mx-auto rounded-lg object-cover mb-2">
                                            <p class="text-xs text-indigo-600 font-medium" x-text="receiptFile.name"></p>
                                        </div>
                                    </template>

                                    <template x-if="receiptFile && !receiptPreview">
                                        <div>
                                            <svg class="w-8 h-8 text-indigo-500 mx-auto mb-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-xs text-indigo-600 font-medium" x-text="receiptFile.name"></p>
                                        </div>
                                    </template>
                                </label>
                            </div>

                            <button @click="submitReceipt()" :disabled="!receiptFile || !bankReference || uploading" :class="receiptFile && bankReference && !uploading
                                        ? 'bg-indigo-600 hover:bg-indigo-700'
                                        : 'bg-gray-300 cursor-not-allowed'"
                                class="w-full py-3 text-white font-semibold rounded-xl transition-colors text-sm">
                                <span x-show="!uploading">{{ __('payments.upload_receipt_btn') }}</span>
                                <span x-show="uploading">{{ __('common.uploading') }}...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection