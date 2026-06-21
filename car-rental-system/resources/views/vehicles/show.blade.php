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
                                                 selectedFrom:    '',
                                                 selectedTo:      '',
                                                 pricing:         null,
                                                 loadingPrice:    false,
                                                 paymentMethod:   'cash',
                                                 bankReference:   '',
                                                 bankSenderName:  '',
                                                 cardName:        '',
                                                 get canSubmit() {
                                                     if (!this.pricing?.available || !this.selectedFrom || !this.selectedTo) return false;
                                                     if (this.paymentMethod === 'bank_transfer') {
                                                         return this.bankReference.trim() !== '' && this.bankSenderName.trim() !== '';
                                                     }
                                                     if (this.paymentMethod === 'mastercard') {
                return this.cardName.trim().length > 1;
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
                                                                 vehicle_id:  {{ $vehicle->id }},
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

            {{-- ─── Left: Images + Specs ─────────────────────────────────────────--}}
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
                                              2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177
                                              v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0
                                              00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677
                                              v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            </div>
                        </template>
                        @if($vehicle->status !== 'available')
                                        <div class="absolute top-4 right-4">
                                            <span class="bg-red-600 text-white text-sm font-bold px-3 py-1.5 rounded-lg">
                                                {{ $vehicle->status === 'booked'
                            ? __('vehicles.currently_booked')
                            : __('vehicles.under_maintenance') }}
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
                            <span class="inline-flex mt-2 items-center px-3 py-1 rounded-full
                                                 text-sm font-medium bg-indigo-50 text-indigo-700">
                                {{ $vehicle->category->name }}
                            </span>
                        @endif
                    </div>
                    <div class="text-right">
                        @php
                            $dailyRate = $vehicle->pricingRules
                                ->where('type', 'daily')
                                ->where('is_active', true)
                                ->first();
                        @endphp
                        @if($dailyRate)
                            <p class="text-2xl font-bold text-gray-900">
                                AFN {{ number_format($dailyRate->base_rate) }}
                            </p>
                            <p class="text-sm text-gray-400">{{ __('vehicles.per_day') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Specs --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">{{ __('vehicles.specifications') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @php
                            $fuelLabel = match ($vehicle->fuel_type) {
                                'petrol' => __('vehicles.petrol'),
                                'diesel' => __('vehicles.diesel'),
                                'electric' => __('vehicles.electric'),
                                'hybrid' => __('vehicles.hybrid'),
                                default => ucfirst($vehicle->fuel_type),
                            };
                            $transLabel = match ($vehicle->transmission) {
                                'automatic' => __('vehicles.automatic'),
                                'manual' => __('vehicles.manual'),
                                default => ucfirst($vehicle->transmission),
                            };
                            $colorMap = [
                                'black' => __('vehicles.color_black'),
                                'white' => __('vehicles.color_white'),
                                'silver' => __('vehicles.color_silver'),
                                'gray' => __('vehicles.color_gray'),
                                'grey' => __('vehicles.color_gray'),
                                'red' => __('vehicles.color_red'),
                                'blue' => __('vehicles.color_blue'),
                                'green' => __('vehicles.color_green'),
                                'yellow' => __('vehicles.color_yellow'),
                                'orange' => __('vehicles.color_orange'),
                                'brown' => __('vehicles.color_brown'),
                                'gold' => __('vehicles.color_gold'),
                                'beige' => __('vehicles.color_beige'),
                                'steel' => __('vehicles.color_steel'),
                                'pearl' => __('vehicles.color_pearl'),
                                'maroon' => __('vehicles.color_maroon'),
                            ];

                            $colorKey = strtolower(trim($vehicle->color ?? ''));
                            $colorLabel = $colorMap[$colorKey] ?? $vehicle->color;

                            $specs = [
                                ['label' => __('vehicles.brand'), 'value' => $vehicle->brand],
                                ['label' => __('vehicles.model'), 'value' => $vehicle->model],
                                ['label' => __('vehicles.year'), 'value' => $vehicle->year],
                                ['label' => __('vehicles.color'), 'value' => $colorLabel],
                                ['label' => __('vehicles.seats'), 'value' => $vehicle->seats],
                                ['label' => __('vehicles.fuel_type'), 'value' => $fuelLabel],
                                ['label' => __('vehicles.transmission'), 'value' => $transLabel],
                                ['label' => __('vehicles.odometer'), 'value' => number_format($vehicle->odometer) . ' km'],
                                ['label' => __('vehicles.plate'), 'value' => $vehicle->license_plate],
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
                        <h2 class="font-bold text-gray-900 mb-4">{{ __('vehicles.features') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($vehicle->features as $feature)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5
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
                        <h2 class="font-bold text-gray-900 mb-3">{{ __('vehicles.about_vehicle') }}</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $vehicle->description }}</p>
                    </div>
                @endif

                {{-- Availability --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">{{ __('vehicles.availability') }}</h2>
                    <div class="flex flex-wrap gap-3 text-xs mb-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span>
                            {{ __('vehicles.available') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-red-100 border border-red-300"></span>
                            {{ __('vehicles.booked') }}
                        </span>
                    </div>
                    @if(count($bookedDates) > 0)
                        <div id="availability-calendar"></div>
                    @else
                        <p class="text-sm text-green-600 font-medium">
                            ✓ {{ __('vehicles.fully_available') }}
                        </p>
                    @endif
                </div>

            </div>

            {{-- ─── Right: Booking Widget ──────────────────────────────────────── --}}
            <div class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                    <h2 class="font-bold text-gray-900 mb-4">{{ __('vehicles.book_this_vehicle') }}</h2>

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm font-medium text-yellow-800">
                                        {{ __('common.administrator') }}
                                    </p>
                                </div>
                                <p class="text-xs text-yellow-700 mt-1 ml-7">
                                    {{ __('vehicles.login_to_book') }}
                                </p>
                            </div>
                        @else

                                {{-- Date Selection --}}
                                <div class="space-y-3 mb-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500
                                                                              uppercase tracking-wide mb-1.5">
                                            {{ __('vehicles.pickup_date') }}
                                        </label>
                                        <input type="text" id="v-pickup-date" placeholder="{{ __('vehicles.select_date') }}" readonly
                                            class="w-full text-sm border border-gray-200 rounded-lg
                                                                              px-3 py-2.5 focus:outline-none focus:ring-2
                                                                              focus:ring-indigo-500 bg-white cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500
                                                                              uppercase tracking-wide mb-1.5">
                                            {{ __('vehicles.return_date') }}
                                        </label>
                                        <input type="text" id="v-return-date" placeholder="{{ __('vehicles.select_date') }}" readonly
                                            class="w-full text-sm border border-gray-200 rounded-lg
                                                                              px-3 py-2.5 focus:outline-none focus:ring-2
                                                                              focus:ring-indigo-500 bg-white cursor-pointer">
                                    </div>
                                </div>

                                {{-- Price Breakdown --}}
                                <div x-show="selectedFrom && selectedTo" x-cloak class="mb-4">
                                    <div x-show="loadingPrice" class="text-center py-4">
                                        <div class="inline-block w-6 h-6 border-2 border-indigo-600
                                                                            border-t-transparent rounded-full animate-spin">
                                        </div>
                                    </div>
                                    <template x-if="pricing && !loadingPrice">
                                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">{{ __('vehicles.duration') }}</span>
                                                <span class="font-medium"
                                                    x-text="`${pricing.price?.days ?? 0} {{ __('vehicles.days') }}`">
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">{{ __('vehicles.calculation') }}</span>
                                                <span class="font-medium text-right text-xs" x-text="pricing.price?.breakdown ?? '—'">
                                                </span>
                                            </div>
                                            <div class="border-t border-gray-200 pt-2 flex justify-between">
                                                <span class="font-bold text-gray-900">{{ __('vehicles.total') }}</span>
                                                <span class="font-bold text-indigo-600 text-lg" x-text="pricing.price
                                                                                  ? 'AFN ' + new Intl.NumberFormat().format(pricing.price.amount)
                                                                                  : '—'">
                                                </span>
                                            </div>
                                            <div x-show="pricing && !pricing.available" class="mt-2 text-xs text-red-600 font-medium">
                                                ⚠ {{ __('vehicles.not_available_dates') }}
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Payment Method --}}
                                <div x-show="pricing && pricing.available && selectedFrom && selectedTo" x-cloak class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                        {{ __('payments.method') }}
                                    </p>
                                    <div class="space-y-2">

                                        {{-- Cash --}}
                                        <label class="flex items-center gap-3 p-3 border rounded-xl
                                                                              cursor-pointer hover:bg-gray-50 transition-colors"
                                            :class="paymentMethod === 'cash'
                                                                           ? 'border-indigo-400 bg-indigo-50'
                                                                           : 'border-gray-200'">
                                            <input type="radio" x-model="paymentMethod" value="cash" class="text-indigo-600">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">💵
                                                    {{ app()->getLocale() === 'fa' ? 'نقدی' : 'Cash' }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ app()->getLocale() === 'fa' ? 'در دفتر ما هنگام تحویل بپردازید' : 'Pay at our office on pickup' }}
                                                </p>
                                            </div>
                                        </label>

                                        {{-- Cash warning --}}
                                        <div x-show="paymentMethod === 'cash'" x-cloak class="p-3 bg-orange-50 border border-orange-200
                                                                            rounded-xl text-xs text-orange-700">
                                            ⚠️
                                            {{ app()->getLocale() === 'fa'
                            ? 'پرداخت باید ظرف ۵ ساعت انجام شود وگرنه رزرو لغو می‌شود.'
                            : 'Your payment must be completed within 5 hours or the system will automatically cancel your booking.' }}
                                        </div>

                                        {{-- Bank Transfer --}}
                                        <label class="flex items-center gap-3 p-3 border rounded-xl
                                                                              cursor-pointer hover:bg-gray-50 transition-colors"
                                            :class="paymentMethod === 'bank_transfer'
                                                                           ? 'border-indigo-400 bg-indigo-50'
                                                                           : 'border-gray-200'">
                                            <input type="radio" x-model="paymentMethod" value="bank_transfer" class="text-indigo-600">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">🏦
                                                    {{ app()->getLocale() === 'fa' ? 'انتقال بانکی' : 'Bank Transfer' }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ app()->getLocale() === 'fa' ? 'انتقال به حساب بانکی ما' : 'Transfer to our bank account' }}
                                                </p>
                                            </div>
                                        </label>

                                        {{-- Credit/Debit Card --}}
                                        <label class="flex items-center gap-3 p-3 border rounded-xl
                                                                              cursor-pointer hover:bg-gray-50 transition-colors"
                                            :class="paymentMethod === 'mastercard'
                                                                           ? 'border-indigo-400 bg-indigo-50'
                                                                           : 'border-gray-200'">
                                            <input type="radio" x-model="paymentMethod" value="mastercard" class="text-indigo-600">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">
                                                    💳 {{ app()->getLocale() === 'fa' ? 'کارت اعتباری / دبیت' : 'Credit / Debit Card' }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ app()->getLocale() === 'fa' ? 'ویزا، مسترکارت، امکس — توسط Stripe' : 'Visa, Mastercard, Amex — powered by Stripe' }}
                                                </p>
                                            </div>
                                        </label>

                                    </div>

                                    {{-- Bank Transfer Details --}}
                                    <div x-show="paymentMethod === 'bank_transfer'" x-cloak
                                        class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
                                        <p class="text-sm font-semibold text-blue-800">
                                            {{ app()->getLocale() === 'fa' ? 'اطلاعات انتقال بانکی' : 'Bank Transfer Details' }}
                                        </p>
                                        <div class="bg-white rounded-lg p-3 text-xs space-y-1.5 border border-blue-100">
                                            <p><span
                                                    class="text-gray-500">{{ app()->getLocale() === 'fa' ? 'نام بانک:' : 'Bank Name:' }}</span>
                                                <strong>Afghan United Bank</strong>
                                            </p>
                                            <p><span
                                                    class="text-gray-500">{{ app()->getLocale() === 'fa' ? 'نام حساب:' : 'Account Name:' }}</span>
                                                <strong>Morwarid Car Rental</strong>
                                            </p>
                                            <p><span
                                                    class="text-gray-500">{{ app()->getLocale() === 'fa' ? 'شماره حساب:' : 'Account Number:' }}</span>
                                                <strong class="font-mono">1234-5678-9012</strong>
                                            </p>
                                            <p><span
                                                    class="text-gray-500">{{ app()->getLocale() === 'fa' ? 'شعبه:' : 'Branch:' }}</span>
                                                <strong>Dasht-e-Barchi, Kabul</strong>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                {{ app()->getLocale() === 'fa' ? 'شماره مرجع / رسید' : 'Transfer Reference / Receipt Number' }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="bankReference" placeholder="e.g. TRF-2026-123456"
                                                class="w-full text-sm border border-gray-300 rounded-lg
                                                                                  px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                {{ app()->getLocale() === 'fa' ? 'نام کامل شما' : 'Your Full Name' }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="bankSenderName"
                                                placeholder="{{ app()->getLocale() === 'fa' ? 'نامی که برای انتقال استفاده کردید' : 'Name used for the transfer' }}"
                                                class="w-full text-sm border border-gray-300 rounded-lg
                                                                                  px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    {{-- Stripe Card Details --}}
                                    <div x-show="paymentMethod === 'mastercard'" x-cloak
                                        class="mt-3 p-4 bg-purple-50 border border-purple-200 rounded-xl space-y-3">
                                        <p class="text-sm font-semibold text-purple-800">
                                            💳
                                            {{ app()->getLocale() === 'fa' ? 'پرداخت با کارت اعتباری / دبیت' : 'Credit / Debit Card Payment' }}
                                        </p>
                                        <p class="text-xs text-purple-600">
                                            {{ app()->getLocale() === 'fa'
                            ? 'پرداخت امن توسط Stripe. پس از تأیید رزرو از شما دریافت می‌شود.'
                            : 'Secure payment powered by Stripe. You will be charged after confirming your booking.' }}
                                        </p>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                {{ app()->getLocale() === 'fa' ? 'نام دارنده کارت' : 'Cardholder Name' }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="cardName"
                                                placeholder="{{ app()->getLocale() === 'fa' ? 'نام روی کارت' : 'Name on card' }}"
                                                class="w-full text-sm border border-gray-300 rounded-lg
                                                                                  px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                                {{ app()->getLocale() === 'fa' ? 'اطلاعات کارت' : 'Card Details' }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <div id="stripe-card-element" style="width:100%;padding:10px 12px;border:1px solid #D1D5DB;
                                                                                border-radius:8px;background:white;min-height:42px;">
                                            </div>
                                            <p id="stripe-card-errors" class="text-xs text-red-600 mt-1"></p>
                                        </div>
                                        <p class="text-xs text-purple-500">
                                            🔒 {{ app()->getLocale() === 'fa'
                            ? 'امنیت توسط Stripe — اطلاعات کارت هرگز ذخیره نمی‌شود'
                            : 'Secured by Stripe — card details never stored on our servers' }}
                                        </p>
                                    </div>

                                </div>

                                {{-- Confirm Booking Form --}}
                                <form id="booking-form" method="POST" action="{{ route('customer.bookings.store') }}">
                                    @csrf
                                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                                    <input type="hidden" name="pickup_date" id="hidden-pickup" x-bind:value="selectedFrom">
                                    <input type="hidden" name="return_date" id="hidden-return" x-bind:value="selectedTo">
                                    <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
                                    <input type="hidden" name="bank_reference" x-bind:value="bankReference">
                                    <input type="hidden" name="bank_sender_name" x-bind:value="bankSenderName">
                                    <input type="hidden" name="card_name" x-bind:value="cardName">
                                    <button type="submit" id="booking-submit-btn" :disabled="!canSubmit" :class="canSubmit
                                                                        ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer'
                                                                        : 'bg-gray-300 cursor-not-allowed'" class="w-full py-3 text-white text-sm font-bold
                                                                           rounded-lg transition-colors">
                                        <span
                                            x-text="paymentMethod === 'cash'
                                                                    ? '{{ app()->getLocale() === 'fa' ? 'تأیید رزرو — پرداخت هنگام تحویل' : 'Confirm Booking — Pay on Pickup' }}'
                                                                    : paymentMethod === 'bank_transfer'
                                                                    ? '{{ app()->getLocale() === 'fa' ? 'تأیید رزرو — انتقال بانکی' : 'Confirm Booking — Bank Transfer' }}'
                                                                    : '{{ app()->getLocale() === 'fa' ? 'تأیید رزرو — پرداخت با کارت' : 'Confirm Booking — Pay by Card' }}'">
                                        </span>
                                    </button>
                                </form>

                                {{-- Pricing Options --}}
                                @if($vehicle->pricingRules->isNotEmpty())
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-xs font-semibold text-gray-500
                                                                              uppercase tracking-wide mb-3">
                                            {{ __('vehicles.pricing_options') }}
                                        </p>
                                        <div class="space-y-2">
                                            @foreach($vehicle->pricingRules->where('is_active', true) as $rule)
                                                <div class="flex justify-between items-center text-sm">
                                                    <span class="text-gray-600 capitalize">
                                                        {{ __('vehicles.' . $rule->type) }}
                                                    </span>
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
                        {{-- Guest — not logged in --}}
                        <a href="{{ route('login') }}" class="block w-full py-3 bg-indigo-600 text-white text-sm font-bold
                                          rounded-lg hover:bg-indigo-700 transition-colors text-center">
                            {{ __('vehicles.login_to_book') }}
                        </a>
                        <p class="text-xs text-center text-gray-400 mt-2">
                            {{ __('vehicles.no_account') }}
                            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">
                                {{ __('common.register') }}
                            </a>
                        </p>
                    @endauth

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const rawBookedDates = @json($bookedDates);

        const disabledDates = rawBookedDates.map(d => {
            if (typeof d === 'string') return new Date(d);
            if (d && d.from && d.to) return { from: new Date(d.from), to: new Date(d.to) };
            return d;
        });

        const vehicleId = {{ $vehicle->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const stripe = Stripe('{{ config('services.stripe.key') }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card', {
            hidePostalCode: true,
            style: {
                base: {
                    fontSize: '15px',
                    color: '#374151',
                    fontFamily: 'system-ui, sans-serif',
                    lineHeight: '1.5',
                    '::placeholder': { color: '#9CA3AF' },
                },
                invalid: { color: '#EF4444' }
            }
        });

        cardElement.mount('#stripe-card-element');

        cardElement.on('change', function (event) {
            const errors = document.getElementById('stripe-card-errors');
            errors.textContent = event.error ? event.error.message : '';
        });

        function getAlpine() {
            const el = document.getElementById('vehicle-page');
            return el ? Alpine.$data(el) : null;
        }

        flatpickr('#v-pickup-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: disabledDates,
            onChange: function (selectedDates, dateStr) {
                returnPicker.set('minDate', dateStr);
                const data = getAlpine();
                if (data) {
                    data.selectedFrom = dateStr;
                    if (data.selectedTo) data.fetchPrice();
                }
                document.getElementById('hidden-pickup').value = dateStr;
            }
        });

        const returnPicker = flatpickr('#v-return-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: disabledDates,
            onChange: function (selectedDates, dateStr) {
                const data = getAlpine();
                if (data) {
                    data.selectedTo = dateStr;
                    if (data.selectedFrom) data.fetchPrice();
                }
                document.getElementById('hidden-return').value = dateStr;
            }
        });

        document.getElementById('booking-form').addEventListener('submit', async function (e) {
            e.preventDefault();

            const alpineData = getAlpine();
            const paymentMethod = alpineData ? alpineData.paymentMethod : 'cash';

            if (paymentMethod !== 'mastercard') {
                this.submit();
                return;
            }

            const submitBtn = document.getElementById('booking-submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = '{{ app()->getLocale() === 'fa' ? 'در حال پردازش...' : 'Processing payment...' }}';

            try {
                const bookingRes = await fetch('{{ route('customer.bookings.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        vehicle_id: vehicleId,
                        pickup_date: alpineData.selectedFrom,
                        return_date: alpineData.selectedTo,
                        payment_method: 'mastercard',
                    }),
                });

                const bookingData = await bookingRes.json();

                if (!bookingData.booking_id) {
                    throw new Error(bookingData.message || bookingData.error || 'Booking failed.');
                }

                const intentRes = await fetch('{{ route('stripe.create-intent') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ booking_id: bookingData.booking_id }),
                });

                const intentData = await intentRes.json();

                if (!intentData.client_secret) {
                    throw new Error('Payment setup failed.');
                }

                const result = await stripe.confirmCardPayment(intentData.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: alpineData.cardName },
                    },
                });

                if (result.error) {
                    throw new Error(result.error.message);
                }

                const confirmRes = await fetch('{{ route('stripe.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: bookingData.booking_id,
                        payment_intent_id: result.paymentIntent.id,
                    }),
                });

                const confirmData = await confirmRes.json();

                if (confirmData.success) {
                    window.location.href = '/bookings/' + bookingData.booking_id + '/confirmed';
                } else {
                    throw new Error(confirmData.message || 'Confirmation failed.');
                }

            } catch (error) {
                const errEl = document.getElementById('stripe-card-errors');
                if (errEl) errEl.textContent = error.message;
                submitBtn.disabled = false;
                submitBtn.textContent = '{{ app()->getLocale() === 'fa' ? 'تأیید رزرو — پرداخت با کارت' : 'Confirm Booking — Pay by Card' }}';
            }
        });
    </script>
@endpush