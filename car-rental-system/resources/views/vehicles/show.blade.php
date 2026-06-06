@extends('layouts.app')

@section('title', $vehicle->full_name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('vehicles.index') }}" class="hover:text-indigo-600 transition-colors">
                {{ __('common.nav_vehicles') }}
            </a>
            <span>/</span>
            <span class="text-gray-900 font-medium">{{ $vehicle->full_name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" id="vehicle-page" x-data="{
                                     currentImage: {{ $vehicle->thumbnail ? '\'' . asset('storage/' . $vehicle->thumbnail) . '\'' : '\'\'' }},
                                     images: @js(
                                        collect($vehicle->images)
                                            ->map(fn($i) => asset('storage/' . $i->path))
                                            ->prepend($vehicle->thumbnail ? asset('storage/' . $vehicle->thumbnail) : null)
                                            ->filter()->values()
                                    ),
                                     selectedFrom: '',
                                     selectedTo: '',
                                     pricing: null,
                                     loadingPrice: false,
                                     paymentMethod:   'cash',
                bankReference:   '',
                bankSenderName:  '',
                cardName:        '',
                cardNumber:      '',
                cardExpiry:      '',
                cardCvv:         '',
                get canSubmit() {
                    if (!this.pricing?.available || !this.selectedFrom || !this.selectedTo) return false;
                    if (this.paymentMethod === 'bank_transfer') {
                        return this.bankReference.trim() !== '' && this.bankSenderName.trim() !== '';
                    }
                    if (this.paymentMethod === 'mastercard') {
                        return this.cardName.trim() !== '' &&
                               this.cardNumber.replace(/\s/g,'').length === 16 &&
                               this.cardExpiry.length === 5 &&
                               this.cardCvv.length === 3;
                    }
                    return true;
                },
                                     async fetchPrice() {
                                         if (!this.selectedFrom || !this.selectedTo) return;
                                         this.loadingPrice = true;
                                         this.pricing = null;
                                         try {
                                             const res = await fetch('/api/v1/availability/check', {
                                                 method: 'POST',
                                                 headers: {
                                                     'Content-Type': 'application/json',
                                                     'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                 },
                                                 body: JSON.stringify({
                                                     vehicle_id: {{ $vehicle->id }},
                                                     pickup_date: this.selectedFrom,
                                                     return_date: this.selectedTo
                                                 })
                                             });
                                             this.pricing = await res.json();
                                         } catch(e) {
                                             console.error('Price fetch error:', e);
                                         }
                                         this.loadingPrice = false;
                                     }
                                 }">

            {{-- ─── Left: Images + Specs ────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Image Carousel --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="aspect-video bg-gray-100 relative overflow-hidden">
                        <template x-if="currentImage">
                            <img :src="currentImage" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!currentImage">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3
                                                              0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25
                                                              4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621
                                                              0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193
                                                              2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554
                                                              48.554 0 00-10.026 0 1.106 1.106 0 00-.987
                                                              1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            </div>
                        </template>
                        @if($vehicle->status !== 'available')
                            <div class="absolute top-4 right-4">
                                <span class="bg-red-600 text-white text-sm font-bold px-3 py-1.5 rounded-lg">
                                    {{ $vehicle->status === 'booked' ? 'Currently Booked' : 'Under Maintenance' }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <template x-if="images.length > 1">
                        <div class="flex gap-2 p-3 overflow-x-auto">
                            <template x-for="(img, i) in images" :key="i">
                                <button @click="currentImage = img" :class="currentImage === img
                                                                ? 'ring-2 ring-indigo-500'
                                                                : 'ring-1 ring-gray-200'"
                                    class="flex-shrink-0 w-16 h-12 rounded-lg overflow-hidden">
                                    <img :src="img" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Vehicle Title --}}
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $vehicle->full_name }}</h1>
                        @if($vehicle->category)
                            <span
                                class="inline-flex mt-2 items-center px-3 py-1 rounded-full
                                                                                     text-sm font-medium bg-indigo-50 text-indigo-700">
                                {{ $vehicle->category->name }}
                            </span>
                        @endif
                    </div>
                    <div class="text-right">
                        @php $dailyRate = $vehicle->pricingRules->where('type', 'daily')->where('is_active', true)->first(); @endphp
                        @if($dailyRate)
                            <p class="text-2xl font-bold text-gray-900">
                                AFN {{ number_format($dailyRate->base_rate) }}
                            </p>
                            <p class="text-sm text-gray-400">per day</p>
                        @endif
                    </div>
                </div>

                {{-- Specs --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Specifications</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @php
                            $specs = [
                                ['label' => 'Brand', 'value' => $vehicle->brand],
                                ['label' => 'Model', 'value' => $vehicle->model],
                                ['label' => 'Year', 'value' => $vehicle->year],
                                ['label' => 'Color', 'value' => $vehicle->color],
                                ['label' => 'Seats', 'value' => $vehicle->seats],
                                ['label' => 'Fuel Type', 'value' => ucfirst($vehicle->fuel_type)],
                                ['label' => 'Transmission', 'value' => ucfirst($vehicle->transmission)],
                                ['label' => 'Odometer', 'value' => number_format($vehicle->odometer) . ' km'],
                                ['label' => 'Plate', 'value' => $vehicle->license_plate],
                            ];
                        @endphp
                        @foreach($specs as $spec)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 font-medium">{{ $spec['label'] }}</p>
                                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $spec['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Features --}}
                @if($vehicle->features && count($vehicle->features) > 0)
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-bold text-gray-900 mb-4">Features</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($vehicle->features as $feature)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5
                                                                                                                 bg-green-50 text-green-700 text-sm font-medium rounded-full">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $feature }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                @if($vehicle->description)
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-bold text-gray-900 mb-3">About This Vehicle</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $vehicle->description }}</p>
                    </div>
                @endif

                {{-- Availability --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Availability</h2>
                    <div class="flex flex-wrap gap-3 text-xs mb-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span>
                            Available
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-red-100 border border-red-300"></span>
                            Booked
                        </span>
                    </div>
                    @if(count($bookedDates) > 0)
                        <div id="availability-calendar"></div>
                    @else
                        <p class="text-sm text-green-600 font-medium">
                            ✓ Fully available for the next 90 days
                        </p>
                    @endif
                </div>

            </div>

            {{-- ─── Right: Booking Widget ───────────────────────────────────────── --}}
            <div class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                    <h2 class="font-bold text-gray-900 mb-4">Book This Vehicle</h2>

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm font-medium text-yellow-800">You are logged in as Admin</p>
                                </div>
                                <p class="text-xs text-yellow-700 mt-1 ml-7">Booking is for customers only.</p>
                            </div>
                        @else

                                {{-- Date Selection --}}
                                <div class="space-y-3 mb-4">
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500
                                                                                                                          uppercase tracking-wide mb-1.5">
                                            Pick-up Date
                                        </label>
                                        <input type="text" id="v-pickup-date" placeholder="Select date"
                                            class="w-full text-sm border border-gray-200 rounded-lg
                                                                                                                          px-3 py-2.5 focus:outline-none focus:ring-2
                                                                                                                          focus:ring-indigo-500 bg-white cursor-pointer">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-gray-500
                                                                                                                          uppercase tracking-wide mb-1.5">
                                            Return Date
                                        </label>
                                        <input type="text" id="v-return-date" placeholder="Select date"
                                            class="w-full text-sm border border-gray-200 rounded-lg
                                                                                                                          px-3 py-2.5 focus:outline-none focus:ring-2
                                                                                                                          focus:ring-indigo-500 bg-white cursor-pointer">
                                    </div>
                                </div>

                                {{-- Price Breakdown --}}
                                <div x-show="selectedFrom && selectedTo" x-cloak class="mb-4">
                                    <div x-show="loadingPrice" class="text-center py-4">
                                        <div
                                            class="inline-block w-6 h-6 border-2 border-indigo-600
                                                                                                                        border-t-transparent rounded-full animate-spin">
                                        </div>
                                    </div>
                                    <template x-if="pricing && !loadingPrice">
                                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">Duration</span>
                                                <span class="font-medium" x-text="`${pricing.price?.days ?? 0} days`">
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">Calculation</span>
                                                <span class="font-medium text-right text-xs" x-text="pricing.price?.breakdown ?? '—'">
                                                </span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-2 flex justify-between">
                                                <span class="font-bold text-gray-900">Total</span>
                                                <span class="font-bold text-indigo-600 text-lg" x-text="pricing.price
                                                                                                                              ? 'AFN ' + new Intl.NumberFormat().format(pricing.price.amount)
                                                                                                                              : '—'">
                                                </span>
                                            </div>
                                            <div x-show="pricing && !pricing.available" class="mt-2 text-xs text-red-600 font-medium">
                                                ⚠ Not available for selected dates
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Payment Method --}}
                                <div x-show="pricing && pricing.available && selectedFrom && selectedTo" x-cloak class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                        Payment Method
                                    </p>
                                    <div class="space-y-2">

                                        {{-- Cash --}}
                                        <label class="flex items-center gap-3 p-3 border rounded-xl
              cursor-pointer hover:bg-gray-50 transition-colors"
       :class="paymentMethod === 'cash'
           ? 'border-indigo-400 bg-indigo-50'
           : 'border-gray-200'">
    <input type="radio" x-model="paymentMethod" value="cash"
           class="text-indigo-600">
    <div>
        <p class="text-sm font-semibold text-gray-900">💵 Cash</p>
        <p class="text-xs text-gray-500">Pay at our office on pickup</p>
    </div>
</label>

{{-- Cash warning --}}
<div x-show="paymentMethod === 'cash'" x-cloak
     class="p-3 bg-orange-50 border border-orange-200 rounded-xl text-xs text-orange-700">
    ⚠️ Your payment must be completed within <strong>5 hours</strong>
    or the system will automatically cancel your booking and release
    the dates for other customers.
</div>

                                        {{-- Bank Transfer --}}
                                        <label
                                            class="flex items-center gap-3 p-3 border rounded-xl
                                                                                      cursor-pointer hover:bg-gray-50 transition-colors"
                                            :class="paymentMethod === 'bank_transfer'
                                                                                   ? 'border-indigo-400 bg-indigo-50'
                                                                                   : 'border-gray-200'">
                                            <input type="radio" x-model="paymentMethod" value="bank_transfer" class="text-indigo-600">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">🏦 Bank Transfer</p>
                                                <p class="text-xs text-gray-500">Transfer to our bank account</p>
                                            </div>
                                        </label>

                                        {{-- Mastercard --}}
                                        <label
                                            class="flex items-center gap-3 p-3 border rounded-xl
                                                                                      cursor-pointer hover:bg-gray-50 transition-colors"
                                            :class="paymentMethod === 'mastercard'
                                                                                   ? 'border-indigo-400 bg-indigo-50'
                                                                                   : 'border-gray-200'">
                                            <input type="radio" x-model="paymentMethod" value="mastercard" class="text-indigo-600">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">💳 Mastercard</p>
                                                <p class="text-xs text-gray-500">Pay with your Mastercard</p>
                                            </div>
                                        </label>

                                    </div>

                                    {{-- Bank Transfer Details --}}
                                    <div x-show="paymentMethod === 'bank_transfer'" x-cloak
                                        class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
                                        <p class="text-sm font-semibold text-blue-800">Bank Transfer Details</p>
                                        <p class="text-xs text-blue-600">
                                            Please transfer the total amount to our bank account and
                                            enter your transfer reference below.
                                        </p>
                                        <div class="bg-white rounded-lg p-3 text-xs space-y-1.5 border border-blue-100">
                                            <p><span class="text-gray-500">Bank Name:</span>
                                                <strong class="text-gray-900">Afghan United Bank</strong>
                                            </p>
                                            <p><span class="text-gray-500">Account Name:</span>
                                                <strong class="text-gray-900">Morwarid Car Rental</strong>
                                            </p>
                                            <p><span class="text-gray-500">Account Number:</span>
                                                <strong class="text-gray-900 font-mono">1234-5678-9012</strong>
                                            </p>
                                            <p><span class="text-gray-500">Branch:</span>
                                                <strong class="text-gray-900">Dasht-e-Barchi, Kabul</strong>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                Transfer Reference / Receipt Number
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="bank-reference" x-model="bankReference"
                                                placeholder="e.g. TRF-2026-123456" class="w-full text-sm border border-gray-300 rounded-lg
                                                                                          px-3 py-2 focus:outline-none focus:ring-2
                                                                                          focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                Your Full Name
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="bank-sender-name" x-model="bankSenderName"
                                                placeholder="Name used for the transfer" class="w-full text-sm border border-gray-300 rounded-lg
                                                                                          px-3 py-2 focus:outline-none focus:ring-2
                                                                                          focus:ring-blue-500">
                                        </div>
                                    </div>

                                    {{-- Mastercard Details --}}
                                    <div x-show="paymentMethod === 'mastercard'" x-cloak
                                        class="mt-3 p-4 bg-purple-50 border border-purple-200 rounded-xl space-y-3">
                                        <p class="text-sm font-semibold text-purple-800">Mastercard Details</p>
                                        <p class="text-xs text-purple-600">
                                            Enter your card details below to complete payment.
                                        </p>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                Cardholder Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="cardName" placeholder="Name on card" class="w-full text-sm border border-gray-300 rounded-lg
                                                                                          px-3 py-2 focus:outline-none focus:ring-2
                                                                                          focus:ring-purple-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                Card Number <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19"
                                                @input="cardNumber = cardNumber.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim()"
                                                class="w-full text-sm border border-gray-300 rounded-lg
                                                                                          px-3 py-2 focus:outline-none focus:ring-2
                                                                                          focus:ring-purple-500 font-mono">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                    Expiry Date <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" x-model="cardExpiry" placeholder="MM/YY" maxlength="5"
                                                    @input="cardExpiry = cardExpiry.replace(/\D/g,'').replace(/^(\d{2})(\d)/,'$1/$2')"
                                                    class="w-full text-sm border border-gray-300 rounded-lg
                                                                                              px-3 py-2 focus:outline-none focus:ring-2
                                                                                              focus:ring-purple-500 font-mono">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                    CVV <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" x-model="cardCvv" placeholder="123" maxlength="3"
                                                    @input="cardCvv = cardCvv.replace(/\D/g,'')" class="w-full text-sm border border-gray-300 rounded-lg
                                                                                              px-3 py-2 focus:outline-none focus:ring-2
                                                                                              focus:ring-purple-500 font-mono">
                                            </div>
                                        </div>
                                        <p class="text-xs text-purple-500">
                                            🔒 Your card details are encrypted and secure
                                        </p>
                                    </div>

                                </div>

                                {{-- Confirm Booking --}}
                                <form method="POST" action="{{ route('customer.bookings.store') }}">
                                    @csrf
                                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                                    <input type="hidden" name="pickup_date" id="hidden-pickup" x-bind:value="selectedFrom">
                                    <input type="hidden" name="return_date" id="hidden-return" x-bind:value="selectedTo">
                                    <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
                                    <input type="hidden" name="bank_reference" x-bind:value="bankReference">
                                    <input type="hidden" name="bank_sender_name" x-bind:value="bankSenderName">
                                    <input type="hidden" name="card_name" x-bind:value="cardName">
                                    <input type="hidden" name="card_last_four" x-bind:value="cardNumber.slice(-4)">
                                    <button type="submit" :disabled="!canSubmit" :class="canSubmit
                                ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer'
                                : 'bg-gray-300 cursor-not-allowed'" class="w-full py-3 text-white text-sm font-bold
                                   rounded-lg transition-colors">
                                        <span x-text="paymentMethod === 'cash'
                            ? 'Confirm Booking — Pay on Pickup'
                            : paymentMethod === 'bank_transfer'
                            ? 'Confirm Booking — Bank Transfer'
                            : 'Confirm Booking — Pay by Card'">
                                        </span>
                                    </button>
                                </form>

                                {{-- Pricing Options --}}
                                @if($vehicle->pricingRules->isNotEmpty())
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                            Pricing Options
                                        </p>
                                        <div class="space-y-2">
                                            @foreach($vehicle->pricingRules->where('is_active', true) as $rule)
                                                <div class="flex justify-between items-center text-sm">
                                                    <span class="text-gray-600 capitalize">{{ $rule->type }}</span>
                                                    <span class="font-semibold text-gray-900">
                                                        AFN {{ number_format($rule->base_rate) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="block w-full py-3 bg-indigo-600 text-white text-sm font-bold
                                                                              rounded-lg hover:bg-indigo-700 transition-colors text-center">
                            Login to Book
                        </a>
                        <p class="text-xs text-center text-gray-400 mt-2">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Register</a>
                        </p>
                    @endauth

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const bookedDates = @json($bookedDates);

        // Get Alpine component
        function getAlpine() {
            const el = document.getElementById('vehicle-page');
            return el ? Alpine.$data(el) : null;
        }

        // Pickup date picker
        flatpickr('#v-pickup-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: bookedDates,
            onChange: function (selectedDates, dateStr) {
                // Update return date minimum
                returnPicker.set('minDate', dateStr);

                // Update Alpine data directly
                const data = getAlpine();
                if (data) {
                    data.selectedFrom = dateStr;
                    // Fetch price if return date already selected
                    if (data.selectedTo) {
                        data.fetchPrice();
                    }
                }

                // Also update hidden input directly
                document.getElementById('hidden-pickup').value = dateStr;
            }
        });

        // Return date picker
        const returnPicker = flatpickr('#v-return-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: bookedDates,
            onChange: function (selectedDates, dateStr) {
                // Update Alpine data directly
                const data = getAlpine();
                if (data) {
                    data.selectedTo = dateStr;
                    // Fetch price if pickup date already selected
                    if (data.selectedFrom) {
                        data.fetchPrice();
                    }
                }

                // Also update hidden input directly
                document.getElementById('hidden-return').value = dateStr;
            }
        });
    </script>
@endpush