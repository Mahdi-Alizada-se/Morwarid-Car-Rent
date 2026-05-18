@extends('layouts.app')

@section('title', __('common.create_booking'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
                    step: 1,
                    totalSteps: 3,
                    pickupDate: '',
                    returnDate: '',
                    pickupLocation: '',
                    returnLocation: '',
                    paymentMethod: 'counter',
                    notes: '',
                    pricing: null,
                    loading: false,

                    async checkPrice() {
                        if (!this.pickupDate || !this.returnDate) return;
                        this.loading = true;
                        try {
                            const res = await fetch('/api/v1/availability/check', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    vehicle_id: {{ $vehicle->id }},
                                    pickup_date: this.pickupDate,
                                    return_date: this.returnDate,
                                })
                            });
                            this.pricing = await res.json();
                        } catch(e) { console.error(e); }
                        this.loading = false;
                    },

                    nextStep() {
                        if (this.step < this.totalSteps) this.step++;
                    },

                    prevStep() {
                        if (this.step > 1) this.step--;
                    },

                    formatMoney(amount) {
                        return 'AFN ' + new Intl.NumberFormat().format(amount);
                    }
                 }">

        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">
                    {{ __('common.step') }} <span x-text="step"></span> {{ __('common.of') }} 3
                </span>
                <span class="text-sm text-gray-400">
                    <span
                        x-text="step === 1 ? '{{ __('bookings.dates_location') }}' : step === 2 ? '{{ __('bookings.review') }}' : '{{ __('bookings.confirmed') }}'"></span>
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                    :style="`width: ${(step / totalSteps) * 100}%`">
                </div>
            </div>

            {{-- Step Indicators --}}
            <div class="flex justify-between mt-3">
                @foreach([1 => __('bookings.dates_location'), 2 => __('bookings.review'), 3 => __('bookings.confirmed')] as $num => $label)
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all"
                            :class="{{ $num }} <= step
                                                     ? 'bg-indigo-600 text-white'
                                                     : 'bg-gray-200 text-gray-500'">
                            {{ $num }}
                        </div>
                        <span class="text-xs text-gray-500 hidden sm:block">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ─── STEP 1: Dates & Location ────────────────────────────────────────── --}}
        <div x-show="step === 1" x-cloak>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

                {{-- Vehicle Summary --}}
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                    @if($vehicle->thumbnail)
                        <img src="{{ asset('storage/' . $vehicle->thumbnail) }}"
                            class="w-20 h-14 object-cover rounded-xl border border-gray-200" alt="{{ $vehicle->full_name }}">
                    @endif
                    <div>
                        <h2 class="font-bold text-gray-900 text-lg">{{ $vehicle->full_name }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ ucfirst($vehicle->transmission) }} · {{ $vehicle->seats }} {{ __('vehicles.seats') }} ·
                            {{ ucfirst($vehicle->fuel_type) }}
                        </p>
                        @php $dailyRate = $vehicle->pricingRules->where('type', 'daily')->where('is_active', true)->first(); @endphp
                        @if($dailyRate)
                            <p class="text-sm font-semibold text-indigo-600 mt-1">
                                AFN {{ number_format($dailyRate->base_rate) }} / {{ __('vehicles.per_day') }}
                            </p>
                        @endif
                    </div>
                </div>

                <h3 class="font-semibold text-gray-900 mb-5">{{ __('bookings.select_dates') }}</h3>

                <div class="space-y-4">

                    {{-- Pickup Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('vehicles.pickup_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create-pickup-date" x-model="pickupDate" @change="checkPrice()"
                            placeholder="{{ __('vehicles.select_date') }}" class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Return Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('vehicles.return_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create-return-date" x-model="returnDate" @change="checkPrice()"
                            placeholder="{{ __('vehicles.select_date') }}" class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Price Preview --}}
                    <div x-show="pickupDate && returnDate" x-cloak>
                        <div x-show="loading" class="flex items-center gap-2 text-sm text-gray-500 py-2">
                            <div class="w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin">
                            </div>
                            {{ __('common.calculating') }}...
                        </div>
                        <template x-if="pricing && !loading">
                            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                                <div x-show="!pricing.available" class="text-red-600 text-sm font-medium mb-2">
                                    ⚠ {{ __('vehicles.not_available_dates') }}
                                </div>
                                <div x-show="pricing.available" class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ __('vehicles.duration') }}</span>
                                        <span class="font-medium"
                                            x-text="`${pricing.price?.days} {{ __('vehicles.days') }}`"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ __('vehicles.calculation') }}</span>
                                        <span class="text-xs font-medium text-right"
                                            x-text="pricing.price?.breakdown"></span>
                                    </div>
                                    <div class="flex justify-between font-bold pt-2 border-t border-indigo-200">
                                        <span class="text-gray-900">{{ __('vehicles.total') }}</span>
                                        <span class="text-indigo-600" x-text="formatMoney(pricing.price?.amount)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Pickup Location --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('bookings.pickup_location') }}
                            <span class="text-gray-400 text-xs">({{ __('common.optional') }})</span>
                        </label>
                        <input type="text" x-model="pickupLocation" placeholder="{{ __('bookings.location_placeholder') }}"
                            class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Return Location --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('bookings.return_location') }}
                            <span class="text-gray-400 text-xs">({{ __('common.optional') }})</span>
                        </label>
                        <input type="text" x-model="returnLocation" placeholder="{{ __('bookings.location_placeholder') }}"
                            class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('bookings.notes') }}
                            <span class="text-gray-400 text-xs">({{ __('common.optional') }})</span>
                        </label>
                        <textarea x-model="notes" rows="2" placeholder="{{ __('bookings.notes_placeholder') }}" class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </textarea>
                    </div>
                </div>
            </div>


            {{-- Pickup Location Info Card --}}
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-green-800 text-sm mb-1">
                            {{ __('bookings.pickup_location_title') }}
                        </h4>
                        <p class="text-green-700 text-sm font-semibold">
                            {{ config('company.pickup_name') }}
                        </p>
                        <p class="text-green-600 text-sm">
                            {{ config('company.address') }}
                        </p>
                        <p class="text-green-600 text-xs mt-1">
                            🕐 {{ config('company.working_hours') }}
                        </p>
                        <div class="flex items-center gap-3 mt-3">
                            <a href="{{ config('company.maps_url') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white
                              text-xs font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                {{ __('bookings.view_on_maps') }}
                            </a>
                            <p class="text-xs text-green-600">
                                📋 {{ __('bookings.bring_documents') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>



            <div class="flex justify-between">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-200
                                  rounded-xl hover:bg-gray-50 transition-colors">
                    {{ __('common.back') }}
                </a>
                <button @click="nextStep()" :disabled="!pickupDate || !returnDate || !pricing?.available" :class="pickupDate && returnDate && pricing?.available
                                    ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer'
                                    : 'bg-gray-300 cursor-not-allowed'"
                    class="px-6 py-2.5 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ __('common.next') }} →
                </button>
            </div>
        </div>

        {{-- ─── STEP 2: Review & Payment ─────────────────────────────────────────── --}}
        <div x-show="step === 2" x-cloak>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">

                <h3 class="font-semibold text-gray-900 mb-5">{{ __('bookings.review_booking') }}</h3>

                {{-- Vehicle --}}
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl mb-5">
                    @if($vehicle->thumbnail)
                        <img src="{{ asset('storage/' . $vehicle->thumbnail) }}" class="w-16 h-12 object-cover rounded-lg"
                            alt="{{ $vehicle->full_name }}">
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900">{{ $vehicle->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $vehicle->license_plate }}</p>
                    </div>
                </div>

                {{-- Booking Details --}}
                <div class="space-y-3 mb-5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('vehicles.pickup_date') }}</span>
                        <span class="font-medium text-gray-900" x-text="pickupDate"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('vehicles.return_date') }}</span>
                        <span class="font-medium text-gray-900" x-text="returnDate"></span>
                    </div>
                    <div class="flex justify-between text-sm" x-show="pickupLocation">
                        <span class="text-gray-500">{{ __('bookings.pickup_location') }}</span>
                        <span class="font-medium text-gray-900" x-text="pickupLocation"></span>
                    </div>
                    <div class="flex justify-between text-sm" x-show="returnLocation">
                        <span class="text-gray-500">{{ __('bookings.return_location') }}</span>
                        <span class="font-medium text-gray-900" x-text="returnLocation"></span>
                    </div>
                </div>

                {{-- Price Breakdown --}}
                <template x-if="pricing?.price">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                            {{ __('vehicles.pricing_options') }}
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('vehicles.duration') }}</span>
                                <span class="font-medium" x-text="`${pricing.price.days} {{ __('vehicles.days') }}`"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('vehicles.calculation') }}</span>
                                <span class="text-xs font-medium text-right" x-text="pricing.price.breakdown"></span>
                            </div>
                            <div class="flex justify-between font-bold text-base pt-2 border-t border-indigo-200">
                                <span class="text-gray-900">{{ __('vehicles.total') }}</span>
                                <span class="text-indigo-600" x-text="formatMoney(pricing.price.amount)"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Payment Method --}}
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3">
                        {{ __('bookings.payment_method') }}
                    </p>
                    <div class="space-y-2">

                        <label
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-indigo-300 transition-colors"
                            :class="paymentMethod === 'counter' ? 'border-indigo-400 bg-indigo-50' : ''">
                            <input type="radio" x-model="paymentMethod" value="counter"
                                class="w-4 h-4 text-indigo-600 border-gray-300">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ __('bookings.pay_at_counter') }}</p>
                                <p class="text-xs text-gray-500">{{ __('bookings.pay_at_counter_desc') }}</p>
                            </div>
                        </label>

                        <label
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-indigo-300 transition-colors"
                            :class="paymentMethod === 'stripe' ? 'border-indigo-400 bg-indigo-50' : ''">
                            <input type="radio" x-model="paymentMethod" value="stripe"
                                class="w-4 h-4 text-indigo-600 border-gray-300">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ __('bookings.pay_online') }}</p>
                                <p class="text-xs text-gray-500">{{ __('bookings.pay_online_desc') }}</p>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <button @click="prevStep()" class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-200
                                       rounded-xl hover:bg-gray-50 transition-colors">
                    ← {{ __('common.back') }}
                </button>

                {{-- Hidden form that submits --}}
                <form method="POST" action="{{ route('customer.bookings.store') }}" id="booking-form">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    <input type="hidden" name="pickup_date" :value="pickupDate">
                    <input type="hidden" name="return_date" :value="returnDate">
                    <input type="hidden" name="pickup_location" :value="pickupLocation">
                    <input type="hidden" name="return_location" :value="returnLocation">
                    <input type="hidden" name="payment_method" :value="paymentMethod">
                    <input type="hidden" name="notes" :value="notes">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold
                                           rounded-xl hover:bg-indigo-700 transition-colors">
                        {{ __('bookings.confirm_booking') }} ✓
                    </button>
                </form>
            </div>
        </div>

        {{-- ─── STEP 3: Success ──────────────────────────────────────────────────── --}}
        {{-- This step shows after redirect, handled by bookings/show.blade.php --}}

    </div>
@endsection

@push('scripts')
    <script>
        const bookedDates = @json($bookedDates ?? []);

        const pickupFP = flatpickr('#create-pickup-date', {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            minDate: new Date(Date.now() + 60 * 60 * 1000), // +1 hour
            disable: bookedDates,
            onChange: function (selectedDates, dateStr) {
                returnFP.set('minDate', selectedDates[0]);
            }
        });

        const returnFP = flatpickr('#create-return-date', {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            minDate: new Date(Date.now() + 2 * 60 * 60 * 1000),
            disable: bookedDates,
        });
    </script>
@endpush