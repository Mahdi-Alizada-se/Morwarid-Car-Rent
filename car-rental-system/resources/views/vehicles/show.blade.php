@extends('layouts.app')

@section('title', $vehicle->full_name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('vehicles.index') }}"
                class="hover:text-indigo-600 transition-colors">{{ __('common.nav_vehicles') }}</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">{{ $vehicle->full_name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{
                        currentImage: {{ $vehicle->thumbnail ? '\'' . asset('storage/' . $vehicle->thumbnail) . '\'' : '\'\''}},
                        images: @js(
                            collect($vehicle->images)->map(fn($i) => asset('storage/' . $i->path))->prepend($vehicle->thumbnail ? asset('storage/' . $vehicle->thumbnail) : null)->filter()->values()
                        ),
                        selectedFrom: '{{ request('date_from', '') }}',
                        selectedTo: '{{ request('date_to', '') }}',
                        pricing: null,
                        loadingPrice: false,
                        async fetchPrice() {
                            if (!this.selectedFrom || !this.selectedTo) return;
                            this.loadingPrice = true;
                            try {
                                const res = await fetch(`/api/v1/availability/check`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                    body: JSON.stringify({ vehicle_id: {{ $vehicle->id }}, pickup_date: this.selectedFrom, return_date: this.selectedTo })
                                });
                                this.pricing = await res.json();
                            } catch(e) {}
                            this.loadingPrice = false;
                        }
                     }" x-init="if (selectedFrom && selectedTo) fetchPrice()">

            {{-- ─── Left: Images + Specs ─────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Image Carousel --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    {{-- Main Image --}}
                    <div class="aspect-video bg-gray-100 relative overflow-hidden">
                        <template x-if="currentImage">
                            <img :src="currentImage" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!currentImage">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </template>

                        {{-- Status overlay --}}
                        @if($vehicle->status !== 'available')
                            <div class="absolute top-4 right-4">
                                <span class="bg-red-600 text-white text-sm font-bold px-3 py-1.5 rounded-lg">
                                    {{ $vehicle->status === 'booked' ? __('Currently Booked') : __('Under Maintenance') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    <template x-if="images.length > 1">
                        <div class="flex gap-2 p-3 overflow-x-auto">
                            <template x-for="(img, i) in images" :key="i">
                                <button @click="currentImage = img"
                                    :class="currentImage === img ? 'ring-2 ring-indigo-500' : 'ring-1 ring-gray-200'"
                                    class="flex-shrink-0 w-16 h-12 rounded-lg overflow-hidden">
                                    <img :src="img" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Vehicle Title + Category --}}
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $vehicle->full_name }}</h1>
                            @if($vehicle->category)
                                <span
                                    class="inline-flex mt-2 items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-50 text-indigo-700">
                                    {{ $vehicle->category->name }}
                                </span>
                            @endif
                        </div>
                        <div class="text-right">
                            @php $dailyRate = $vehicle->pricingRules->where('type', 'daily')->where('is_active', true)->first(); @endphp
                            @if($dailyRate)
                                <p class="text-2xl font-bold text-gray-900">AFN {{ number_format($dailyRate->base_rate) }}</p>
                                <p class="text-sm text-gray-400">{{ __('per day') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Specs Table --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">{{ __('Specifications') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @php
                            $specs = [
                                ['label' => __('Brand'), 'value' => $vehicle->brand],
                                ['label' => __('Model'), 'value' => $vehicle->model],
                                ['label' => __('Year'), 'value' => $vehicle->year],
                                ['label' => __('Color'), 'value' => $vehicle->color],
                                ['label' => __('Seats'), 'value' => $vehicle->seats],
                                ['label' => __('Fuel Type'), 'value' => ucfirst($vehicle->fuel_type)],
                                ['label' => __('Transmission'), 'value' => ucfirst($vehicle->transmission)],
                                ['label' => __('Odometer'), 'value' => number_format($vehicle->odometer) . ' km'],
                                ['label' => __('License Plate'), 'value' => $vehicle->license_plate],
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
                        <h2 class="font-bold text-gray-900 mb-4">{{ __('Features') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($vehicle->features as $feature)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 text-sm font-medium rounded-full">
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
                        <h2 class="font-bold text-gray-900 mb-3">{{ __('About This Vehicle') }}</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $vehicle->description }}</p>
                    </div>
                @endif

                {{-- Availability Calendar --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">{{ __('Availability') }}</h2>
                    <div class="flex flex-wrap gap-3 text-xs mb-4">
                        <span class="flex items-center gap-1.5"><span
                                class="w-3 h-3 rounded bg-green-100 border border-green-300"></span>{{ __('Available') }}</span>
                        <span class="flex items-center gap-1.5"><span
                                class="w-3 h-3 rounded bg-red-100 border border-red-300"></span>{{ __('Booked') }}</span>
                    </div>

                    @if(count($bookedDates) > 0)
                        <div id="availability-calendar"></div>
                    @else
                        <p class="text-sm text-green-600 font-medium">✓ {{ __('Fully available for the next 90 days') }}</p>
                    @endif
                </div>
            </div>

            {{-- ─── Right: Booking Widget ────────────────────────────────────────── --}}
            <div class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                    <h2 class="font-bold text-gray-900 mb-5">{{ __('Book This Vehicle') }}</h2>

                    {{-- Date Selection --}}
                    <div class="space-y-3 mb-5">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">{{ __('Pick-up Date') }}</label>
                            <input type="text" id="v-pickup-date" x-model="selectedFrom"
                                placeholder="{{ __('Select date') }}" @change="fetchPrice()"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">{{ __('Return Date') }}</label>
                            <input type="text" id="v-return-date" x-model="selectedTo" placeholder="{{ __('Select date') }}"
                                @change="fetchPrice()"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Price Breakdown --}}
                    <div x-show="selectedFrom && selectedTo" x-cloak>
                        <div x-show="loadingPrice" class="text-center py-4">
                            <div
                                class="inline-block w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin">
                            </div>
                        </div>
                        <template x-if="pricing && !loadingPrice">
                            <div class="bg-gray-50 rounded-lg p-4 mb-5 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Duration') }}</span>
                                    <span class="font-medium"
                                        x-text="`${pricing.price?.days ?? 0} {{ __('days') }}`"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Calculation') }}</span>
                                    <span class="font-medium text-right text-xs"
                                        x-text="pricing.price?.breakdown ?? '—'"></span>
                                </div>
                                <div class="border-t border-gray-200 pt-2 flex justify-between">
                                    <span class="font-bold text-gray-900">{{ __('Total') }}</span>
                                    <span class="font-bold text-indigo-600 text-lg"
                                        x-text="pricing.price ? 'AFN ' + new Intl.NumberFormat().format(pricing.price.amount) : '—'"></span>
                                </div>
                                <div x-show="!pricing.available" class="mt-2 text-xs text-red-600 font-medium">
                                    ⚠ {{ __('Not available for selected dates') }}
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Book Button --}}
                    @auth
                        <form method="POST" action="{{ route('customer.bookings.store') }}">
                            @csrf
                            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                            <input type="hidden" name="pickup_date" x-model="selectedFrom" :value="selectedFrom">
                            <input type="hidden" name="return_date" x-model="selectedTo" :value="selectedTo">
                            <button type="submit" :disabled="!pricing?.available || !selectedFrom || !selectedTo" :class="pricing?.available && selectedFrom && selectedTo
                                                            ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer'
                                                            : 'bg-gray-300 cursor-not-allowed'"
                                class="w-full py-3 text-white text-sm font-bold rounded-lg transition-colors">
                                {{ __('bookings.confirm_booking') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="block w-full py-3 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-colors text-center">
                            {{ __('Login to Book') }}
                        </a>
                        <p class="text-xs text-center text-gray-400 mt-2">
                            {{ __("Don't have an account?") }}
                            <a href="{{ route('register') }}"
                                class="text-indigo-600 hover:underline">{{ __('common.register') }}</a>
                        </p>
                    @endauth

                    {{-- Pricing Rules Summary --}}
                    @if($vehicle->pricingRules->isNotEmpty())
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                {{ __('Pricing Options') }}
                            </p>
                            <div class="space-y-2">
                                @foreach($vehicle->pricingRules->where('is_active', true) as $rule)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 capitalize">{{ __($rule->type) }}</span>
                                        <span class="font-semibold text-gray-900">AFN {{ number_format($rule->base_rate) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const bookedDates = @json($bookedDates);

        flatpickr('#v-pickup-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: bookedDates,
            onChange: function (selectedDates, dateStr) {
                returnFP.set('minDate', dateStr);
            }
        });
        const returnFP = flatpickr('#v-return-date', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            disable: bookedDates,
        });
    </script>
@endpush