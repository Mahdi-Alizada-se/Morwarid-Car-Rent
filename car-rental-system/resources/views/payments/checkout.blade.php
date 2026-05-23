@extends('layouts.app')

@section('title', __('payments.checkout'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8" x-data="{
                 selectedMethod: null,
                 showCardForm: false,
                 processing: false,
                 paymentSuccess: false,
                 cardNumber: '',
                 cardName: '',
                 cardExpiry: '',
                 cardCvv: '',
                 cardType: 'unknown',

                 formatCard(e) {
                     let v = e.target.value.replace(/\D/g, '').substring(0, 16);
                     v = v.replace(/(.{4})/g, '$1 ').trim();
                     this.cardNumber = v;
                     if (this.cardNumber.startsWith('4')) this.cardType = 'visa';
                     else if (this.cardNumber.startsWith('5')) this.cardType = 'mastercard';
                     else this.cardType = 'unknown';
                 },

                 formatExpiry(e) {
                     let v = e.target.value.replace(/\D/g, '').substring(0, 4);
                     if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2);
                     this.cardExpiry = v;
                 },

                 async processPayment() {
                     this.processing = true;
                     await new Promise(r => setTimeout(r, 2500));
                     this.processing = false;
                     this.paymentSuccess = true;
                     setTimeout(() => document.getElementById('onlinePaymentForm').submit(), 1500);
                 }
             }">

        {{-- ─── Page Title ──────────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
            <p class="text-sm text-gray-500 mt-1">Choose how you would like to pay for your booking</p>
        </div>

        {{-- ─── Booking Summary ─────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                Booking Summary
            </h2>
            <div class="flex items-start gap-4">

                {{-- Vehicle Thumbnail --}}
                @if($booking->vehicle->thumbnail)
                    <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                        class="w-24 h-18 object-cover rounded-xl border border-gray-200 flex-shrink-0">
                @else
                    <div class="w-24 h-18 bg-gray-100 rounded-xl flex items-center
                                            justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                    </div>
                @endif

                <div class="flex-1">
                    <p class="font-bold text-gray-900 text-lg">
                        {{ $booking->vehicle->full_name }}
                    </p>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-1 mt-2 text-sm text-gray-500">
                        <div>
                            <span class="text-gray-400">Pickup:</span>
                            <span class="font-medium text-gray-700 ml-1">
                                {{ $booking->pickup_date->format('M d, Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Return:</span>
                            <span class="font-medium text-gray-700 ml-1">
                                {{ $booking->return_date->format('M d, Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Duration:</span>
                            <span class="font-medium text-gray-700 ml-1">
                                {{ $booking->pickup_date->diffInDays($booking->return_date) }} days
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Ref:</span>
                            <code class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded ml-1">
                                    {{ $booking->reference_code }}
                                </code>
                        </div>
                    </div>
                </div>

                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-400">Total Amount</p>
                    <p class="text-2xl font-bold text-blue-600">
                        AFN {{ number_format($booking->total_amount, 0) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ─── Payment Method Selection ────────────────────────────────────────── --}}
        <h2 class="text-lg font-bold text-gray-800 mb-4">How would you like to pay?</h2>

        <div class="flex flex-col sm:flex-row gap-4 mb-6">

            {{-- ── Card 1: Pay at Office (Cash) ──────────────────────────────────── --}}
            <div :class="selectedMethod === 'cash'
                        ? 'ring-2 ring-green-500 border-green-500'
                        : 'border-gray-200 hover:border-green-400'"
                class="border-2 rounded-2xl p-6 cursor-pointer transition-all flex-1"
                @click="selectedMethod = 'cash'; showCardForm = false">

                <div class="text-4xl mb-3">💵</div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Pay at Office</h3>
                <p class="text-gray-500 text-sm mb-4">Pay cash when you visit our office</p>

                <div class="bg-green-50 rounded-xl p-4 text-sm space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="text-green-600 font-bold flex-shrink-0">1.</span>
                        <span class="text-gray-700">Complete your booking now for free</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-600 font-bold flex-shrink-0">2.</span>
                        <span class="text-gray-700">
                            Visit <strong>Morwarid Car Hub</strong>,
                            Dasht-e-Barchi, Kabul
                        </span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-600 font-bold flex-shrink-0">3.</span>
                        <span class="text-gray-700">
                            Pay <strong>AFN {{ number_format($booking->total_amount, 0) }}</strong>
                            in cash at our desk
                        </span>
                    </div>
                    <div class="flex items-start gap-2 bg-orange-50 rounded-lg p-2 mt-2">
                        <span class="text-lg flex-shrink-0">⚠️</span>
                        <span class="text-xs text-orange-700">
                            <strong>Important:</strong> You have exactly 5 hours to visit and pay.
                            If you do not come within 5 hours, your booking is automatically
                            cancelled and the vehicle becomes available for others.
                        </span>
                    </div>
                </div>

                <div x-show="selectedMethod === 'cash'" class="mt-4" x-cloak>
                    <form method="POST" action="{{ route('payments.cash', $booking) }}">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white
                                           font-semibold py-3 rounded-xl transition-colors">
                            Book Now — Pay Cash at Office
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Card 2: Online Payment ──────────────────────────────────────────── --}}
            <div :class="selectedMethod === 'online'
                        ? 'ring-2 ring-blue-500 border-blue-500'
                        : 'border-gray-200 hover:border-blue-400'"
                class="border-2 rounded-2xl p-6 cursor-pointer transition-all flex-1"
                @click="selectedMethod = 'online'; showCardForm = true">

                <div class="flex items-center gap-2 mb-3">
                    <span class="text-3xl">💳</span>
                    <div class="bg-blue-700 text-white text-xs font-bold px-2 py-1 rounded italic">
                        VISA
                    </div>
                    <div class="flex -space-x-2">
                        <div class="w-7 h-7 rounded-full bg-red-500 opacity-90"></div>
                        <div class="w-7 h-7 rounded-full bg-yellow-400 opacity-90"></div>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-2">Pay Online</h3>
                <p class="text-gray-500 text-sm mb-4">Pay securely with your card</p>

                <div class="bg-blue-50 rounded-xl p-4 text-sm space-y-2">
                    <div class="flex items-center gap-2 text-blue-700">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span><strong>Instant confirmation</strong> after payment</span>
                    </div>
                    <div class="flex items-center gap-2 text-blue-700">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <span>256-bit SSL encrypted</span>
                    </div>
                </div>

                <div x-show="selectedMethod === 'online' && !showCardForm === false" class="mt-4 text-center" x-cloak>
                    <p class="text-sm text-blue-600 font-medium">
                        Click to enter card details →
                    </p>
                </div>
            </div>

        </div>

        {{-- ─── Card Payment Modal ───────────────────────────────────────────────── --}}
        <div x-show="showCardForm" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">

            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <h3 class="font-bold text-gray-900 text-lg">Secure Payment</h3>
                    </div>
                    <button @click="showCardForm = false; selectedMethod = null"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Live Card Preview --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-4
                                text-white mb-5 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full
                                    bg-white/10 -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 rounded-full
                                    bg-white/5 translate-y-1/2 -translate-x-1/2"></div>

                    <div x-show="cardType === 'visa'" class="text-right mb-2 font-bold italic text-xl tracking-wider">
                        VISA
                    </div>
                    <div x-show="cardType === 'mastercard'" class="text-right mb-2 flex justify-end gap-1">
                        <div class="w-8 h-8 rounded-full bg-red-500 opacity-90"></div>
                        <div class="w-8 h-8 rounded-full bg-yellow-400 opacity-90 -ml-4"></div>
                    </div>
                    <div x-show="cardType === 'unknown'" class="text-right mb-2 text-white/50 text-sm">
                        Card
                    </div>

                    <p class="text-lg tracking-widest font-mono mb-3 relative z-10"
                        x-text="cardNumber || '•••• •••• •••• ••••'"></p>
                    <div class="flex justify-between text-xs relative z-10">
                        <span x-text="cardName || 'CARD HOLDER'"></span>
                        <span x-text="cardExpiry || 'MM/YY'"></span>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="bg-gray-50 rounded-xl p-3 text-center mb-4">
                    <p class="text-sm text-gray-500">Amount to pay</p>
                    <p class="text-2xl font-bold text-gray-800">
                        AFN {{ number_format($booking->total_amount, 0) }}
                    </p>
                </div>

                {{-- Card Form Fields --}}
                <div class="space-y-4">

                    {{-- Card Number --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Card Number
                        </label>
                        <input type="text" x-model="cardNumber" @input="formatCard($event)"
                            placeholder="1234 5678 9012 3456" maxlength="19" class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                          font-mono text-sm focus:ring-2 focus:ring-blue-500
                                          focus:outline-none">
                    </div>

                    {{-- Cardholder Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Cardholder Name
                        </label>
                        <input type="text" x-model="cardName" @input="cardName = $event.target.value.toUpperCase()"
                            placeholder="JOHN DOE" class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                          text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    {{-- Expiry + CVV --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Expiry Date
                            </label>
                            <input type="text" x-model="cardExpiry" @input="formatExpiry($event)" placeholder="MM/YY"
                                maxlength="5" class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                              text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                CVV
                            </label>
                            <input type="password" x-model="cardCvv" placeholder="CVV" maxlength="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                              text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                </div>

                {{-- Pay Button --}}
                <button @click="processPayment()"
                    :disabled="processing || paymentSuccess || !cardNumber || !cardName || !cardExpiry || !cardCvv" class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white font-semibold
                                   py-3 rounded-xl transition-all disabled:opacity-50
                                   flex items-center justify-center gap-2">

                    <template x-if="!processing && !paymentSuccess">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            Pay AFN {{ number_format($booking->total_amount, 0) }} Securely
                        </span>
                    </template>

                    <template x-if="processing">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Processing Payment...
                        </span>
                    </template>

                    <template x-if="paymentSuccess">
                        <span class="text-green-300 flex items-center gap-2">
                            ✓ Payment Successful! Redirecting...
                        </span>
                    </template>

                </button>

                {{-- SSL Badge --}}
                <p class="text-center text-xs text-gray-400 mt-2 flex items-center justify-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd" />
                    </svg>
                    256-bit SSL Secure Payment
                </p>

            </div>
        </div>

        {{-- Hidden form for online payment confirmation --}}
        <form id="onlinePaymentForm" method="POST" action="{{ route('payments.online.confirm', $booking) }}"
            style="display:none">
            @csrf
        </form>

    </div>
@endsection