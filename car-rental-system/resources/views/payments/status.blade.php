@extends('layouts.app')

@section('title', __('payments.payment_status'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @php
            $statusConfig = [
                'pending' => [
                    'icon' => '🕐',
                    'color' => 'yellow',
                    'title' => __('payments.status_pending'),
                    'desc' => __('payments.status_pending_desc'),
                ],
                'receipt_uploaded' => [
                    'icon' => '⏳',
                    'color' => 'blue',
                    'title' => __('payments.status_review'),
                    'desc' => __('payments.status_review_desc'),
                ],
                'paid' => [
                    'icon' => '✅',
                    'color' => 'green',
                    'title' => __('payments.status_paid'),
                    'desc' => __('payments.status_paid_desc'),
                ],
                'rejected' => [
                    'icon' => '❌',
                    'color' => 'red',
                    'title' => __('payments.status_rejected'),
                    'desc' => __('payments.status_rejected_desc'),
                ],
            ];
            $config = $statusConfig[$payment->status] ?? $statusConfig['pending'];
            $colorClasses = [
                'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'blue' => 'bg-blue-50 border-blue-200 text-blue-800',
                'green' => 'bg-green-50 border-green-200 text-green-800',
                'red' => 'bg-red-50 border-red-200 text-red-800',
            ];
        @endphp

        {{-- Status Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">

            {{-- Status Banner --}}
            <div class="p-6 border-b border-gray-100 text-center
                        {{ $colorClasses[$config['color']] }} border">
                <div class="text-4xl mb-3">{{ $config['icon'] }}</div>
                <h2 class="text-xl font-bold">{{ $config['title'] }}</h2>
                <p class="text-sm mt-1 opacity-80">{{ $config['desc'] }}</p>
            </div>

            <div class="p-6 space-y-4">

                {{-- Payment Details --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500">{{ __('bookings.reference') }}</p>
                        <p class="font-mono font-bold text-gray-900 text-sm mt-0.5">
                            {{ $payment->booking->reference_code }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500">{{ __('vehicles.total') }}</p>
                        <p class="font-bold text-indigo-600 mt-0.5">
                            AFN {{ number_format($payment->amount) }}
                        </p>
                    </div>
                </div>

                {{-- Rejection Reason --}}
                @if($payment->status === 'rejected' && $payment->rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <p class="text-sm font-semibold text-red-800 mb-1">{{ __('payments.rejection_reason') }}</p>
                        <p class="text-sm text-red-700">{{ $payment->rejection_reason }}</p>
                    </div>
                @endif

                {{-- Invoice Download --}}
                @if($payment->status === 'paid' && $payment->invoice_path)
                    <a href="{{ Storage::disk('public')->url($payment->invoice_path) }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 bg-green-600 text-white
                                  font-semibold rounded-xl hover:bg-green-700 transition-colors text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('payments.download_invoice') }}
                    </a>
                @endif

                {{-- Re-upload Form (for rejected) --}}
                @if($payment->status === 'rejected')
                    <div x-data="{
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
                                }
                            },
                            async submitReceipt() {
                                if (!this.receiptFile) return;
                                this.uploading = true;
                                this.error = '';
                                const formData = new FormData();
                                formData.append('receipt', this.receiptFile);
                                formData.append('bank_reference', this.bankReference);
                                formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
                                try {
                                    const res = await fetch('/api/v1/payments/{{ $payment->id }}/upload-receipt', {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'Accept': 'application/json' }
                                    });
                                    const data = await res.json();
                                    if (res.ok) { this.success = true; }
                                    else { this.error = data.message ?? 'Upload failed.'; }
                                } catch(e) { this.error = 'Upload failed.'; }
                                this.uploading = false;
                            }
                        }">
                        <div x-show="success" class="p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                            <p class="font-semibold text-green-800">{{ __('payments.receipt_submitted') }}</p>
                            <p class="text-sm text-green-600 mt-1">{{ __('payments.receipt_review_time') }}</p>
                        </div>
                        <div x-show="!success" class="border border-gray-200 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 mb-3">{{ __('payments.reupload_title') }}</h3>
                            <div x-show="error" class="mb-3 p-3 bg-red-50 rounded-lg text-sm text-red-700" x-text="error"></div>
                            <div class="mb-3">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('payments.bank_reference') }}</label>
                                <input type="text" x-model="bankReference"
                                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="mb-3">
                                <label
                                    class="block border-2 border-dashed border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:border-indigo-400 transition-colors">
                                    <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                                        @change="handleFile($event)">
                                    <template x-if="!receiptFile">
                                        <p class="text-sm text-gray-500">{{ __('payments.click_to_upload') }}</p>
                                    </template>
                                    <template x-if="receiptFile">
                                        <p class="text-sm text-indigo-600 font-medium" x-text="receiptFile.name"></p>
                                    </template>
                                </label>
                            </div>
                            <button @click="submitReceipt()" :disabled="!receiptFile || uploading"
                                class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                                <span x-show="!uploading">{{ __('payments.reupload_btn') }}</span>
                                <span x-show="uploading">{{ __('common.uploading') }}...</span>
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('customer.bookings.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← {{ __('bookings.back_to_bookings') }}
            </a>
        </div>

    </div>
@endsection