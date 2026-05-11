@extends('layouts.app')

@section('title', __('Browse Vehicles'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Browse Vehicles') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('Find the perfect car for your journey') }}</p>
    </div>

    {{-- Date Range Picker --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('vehicles.index') }}" id="filter-form">
            <div class="flex flex-col sm:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Search') }}</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Search brand, model...') }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Pick-up Date') }}</label>
                    <input type="text" id="pickup-date" name="date_from" value="{{ request('date_from') }}"
                           placeholder="{{ __('Select date') }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Return Date') }}</label>
                    <input type="text" id="return-date" name="date_to" value="{{ request('date_to') }}"
                           placeholder="{{ __('Select date') }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors whitespace-nowrap">
                    {{ __('Search') }}
                </button>
            </div>
        </form>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- ─── Sidebar Filters ─────────────────────────────────────────────── --}}
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-6 sticky top-24">
                <h3 class="font-semibold text-gray-900">{{ __('Filters') }}</h3>

                {{-- Category --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Category') }}</p>
                    <div class="space-y-2">
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                       form="filter-form"
                                       {{ in_array($cat->id, (array) request('category_ids', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300"
                                       onchange="document.getElementById('filter-form').submit()">
                                <span class="text-sm text-gray-700">{{ $cat->name }}</span>
                                <span class="ml-auto text-xs text-gray-400">{{ $cat->vehicles_count ?? '' }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Price Range --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Daily Rate (AFN)') }}</p>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" form="filter-form" value="{{ request('min_price') }}"
                               placeholder="{{ __('Min') }}" min="0"
                               class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="number" name="max_price" form="filter-form" value="{{ request('max_price') }}"
                               placeholder="{{ __('Max') }}" min="0"
                               class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Transmission --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Transmission') }}</p>
                    <div class="space-y-2">
                        @foreach(['automatic' => __('Automatic'), 'manual' => __('Manual')] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="transmission" form="filter-form" value="{{ $val }}"
                                       {{ request('transmission') === $val ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 border-gray-300"
                                       onchange="document.getElementById('filter-form').submit()">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Fuel Type --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Fuel Type') }}</p>
                    <div class="space-y-2">
                        @foreach(['petrol' => __('Petrol'), 'diesel' => __('Diesel'), 'electric' => __('Electric'), 'hybrid' => __('Hybrid')] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="fuel_types[]" form="filter-form" value="{{ $val }}"
                                       {{ in_array($val, (array) request('fuel_types', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300"
                                       onchange="document.getElementById('filter-form').submit()">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sort --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Sort By') }}</p>
                    <select name="sort_by" form="filter-form"
                            onchange="document.getElementById('filter-form').submit()"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="newest" {{ request('sort_by', 'newest') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                        <option value="price_asc" {{ request('sort_by') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                        <option value="price_desc" {{ request('sort_by') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'date_from', 'date_to', 'category_ids', 'min_price', 'max_price', 'transmission', 'fuel_types', 'sort_by']))
                    <a href="{{ route('vehicles.index') }}"
                       class="block text-center text-sm text-red-600 hover:text-red-700 font-medium py-2 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                        {{ __('Clear All Filters') }}
                    </a>
                @endif
            </div>
        </aside>

        {{-- ─── Vehicle Grid ─────────────────────────────────────────────────── --}}
        <div class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500">
                    {{ __(':count vehicles found', ['count' => $vehicles->total()]) }}
                </p>
            </div>

            @if($vehicles->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="font-semibold text-gray-600">{{ __('No vehicles found') }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ __('Try adjusting your filters') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($vehicles as $vehicle)
                        @php
                            $dailyRate = $vehicle->pricingRules->where('type', 'daily')->where('is_active', true)->first();
                        @endphp
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
                            {{-- Image --}}
                            <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
                                @if($vehicle->thumbnail)
                                    <img src="{{ asset('storage/' . $vehicle->thumbnail) }}"
                                         alt="{{ $vehicle->full_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                @endif

                                {{-- Category Badge --}}
                                @if($vehicle->category)
                                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-xs font-semibold text-gray-700 px-2.5 py-1 rounded-full border border-white/50">
                                        {{ $vehicle->category->name }}
                                    </span>
                                @endif

                                {{-- Status --}}
                                @if($vehicle->status !== 'available')
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                        <span class="bg-red-600 text-white text-sm font-bold px-4 py-2 rounded-lg">
                                            {{ $vehicle->status === 'booked' ? __('Booked') : __('Unavailable') }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-4">
                                <h3 class="font-bold text-gray-900">{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $vehicle->year }} · {{ ucfirst($vehicle->transmission) }} · {{ $vehicle->seats }} {{ __('seats') }}</p>

                                {{-- Quick Specs --}}
                                <div class="flex items-center gap-3 mt-3 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        {{ ucfirst($vehicle->fuel_type) }}
                                    </span>
                                    <span>·</span>
                                    <span>{{ $vehicle->color }}</span>
                                </div>

                                {{-- Price + CTA --}}
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                    <div>
                                        @if($dailyRate)
                                            <span class="text-lg font-bold text-gray-900">AFN {{ number_format($dailyRate->base_rate) }}</span>
                                            <span class="text-xs text-gray-400">/{{ __('day') }}</span>
                                        @else
                                            <span class="text-sm text-gray-400">{{ __('Price on request') }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('vehicles.show', $vehicle) }}"
                                       class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                        {{ __('Book Now') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($vehicles->hasPages())
                    <div class="mt-8">
                        {{ $vehicles->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    flatpickr('#pickup-date', {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        onChange: function(selectedDates, dateStr) {
            returnPicker.set('minDate', dateStr);
        }
    });
    const returnPicker = flatpickr('#return-date', {
        dateFormat: 'Y-m-d',
        minDate: 'today',
    });
</script>
@endpush