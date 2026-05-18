@extends('layouts.app')

@section('title', __('common.nav_vehicles'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('vehicles.available_vehicles') }}</h1>
            <p class="text-gray-500 mt-1 text-sm">{{ __('vehicles.browse_subtitle') }}</p>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 flex-wrap">

                <div class="flex-1 min-w-48 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('vehicles.search_placeholder') }}" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <select name="category_id" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('vehicles.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="transmission" class="text-sm border border-gray-200 rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('vehicles.all_transmissions') }}</option>
                    <option value="automatic" {{ request('transmission') === 'automatic' ? 'selected' : '' }}>
                        Automatic
                    </option>
                    <option value="manual" {{ request('transmission') === 'manual' ? 'selected' : '' }}>
                        Manual
                    </option>
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium
                               rounded-lg hover:bg-indigo-700 transition-colors">
                    {{ __('common.filter') }}
                </button>

                @if(request()->hasAny(['search', 'category_id', 'transmission']))
                    <a href="{{ route('vehicles.index') }}" class="px-4 py-2 text-sm text-gray-600 border border-gray-200
                                  rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('common.clear') }}
                    </a>
                @endif
            </form>
        </div>

        {{-- Vehicle Grid --}}
        @if($vehicles->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg class="w-14 h-14 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="font-semibold text-gray-600 text-lg">{{ __('vehicles.no_vehicles_found') }}</p>
                <p class="text-sm text-gray-400 mt-2">{{ __('vehicles.try_different_filters') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($vehicles as $vehicle)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

                        {{-- Vehicle Image --}}
                        <div class="relative h-48 bg-gray-100">
                            @if($vehicle->thumbnail)
                                <img src="{{ asset('storage/' . $vehicle->thumbnail) }}"
                                    alt="{{ $vehicle->brand }} {{ $vehicle->model }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            @endif

                            {{-- Status Badge --}}
                            <span class="absolute top-3 left-3 text-xs font-semibold px-2.5 py-1 rounded-full
                                    {{ $vehicle->status === 'available'
                        ? 'bg-green-100 text-green-700'
                        : ($vehicle->status === 'booked' || $vehicle->status === 'active'
                            ? 'bg-orange-100 text-orange-700'
                            : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </div>

                        {{-- Vehicle Info --}}
                        <div class="p-4 flex-1">
                            <h3 class="font-bold text-gray-900 text-lg">
                                {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}
                            </h3>
                            <p class="text-gray-500 text-sm mt-0.5">{{ $vehicle->category?->name }}</p>

                            <div class="flex items-center gap-4 mt-3 text-sm text-gray-600">
                                <span>💺 {{ $vehicle->seats }} {{ __('vehicles.seats') }}</span>
                                <span>⚙️ {{ ucfirst($vehicle->transmission) }}</span>
                                <span>⛽ {{ ucfirst($vehicle->fuel_type) }}</span>
                            </div>

                            @php
                                $dailyRate = $vehicle->pricingRules
                                    ->where('type', 'daily')
                                    ->where('is_active', true)
                                    ->first()?->base_rate ?? 0;
                            @endphp

                            <p class="mt-3 font-bold text-indigo-600 text-xl">
                                AFN {{ number_format($dailyRate, 0) }}
                                <span class="text-sm font-normal text-gray-400">/{{ __('vehicles.per_day') }}</span>
                            </p>
                        </div>

                        {{-- Location Section --}}
                        <div class="border-t border-gray-100">

                            @if($vehicle->status === 'available')

                                {{-- Map --}}
                                <div class="p-0">
                                    <iframe width="100%" height="160" frameborder="0" scrolling="no"
                                        src="{{ config('company.osm_embed_url') }}" class="w-full" loading="lazy">
                                    </iframe>
                                </div>

                                {{-- Location Info --}}
                                <div class="px-4 py-3 bg-green-50">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-green-800">
                                                {{ config('company.pickup_name') }}
                                            </p>
                                            <p class="text-xs text-green-600">{{ config('company.address') }}</p>
                                        </div>
                                        <a href="{{ config('company.maps_url') }}" target="_blank"
                                            class="text-xs text-green-700 underline flex-shrink-0 font-medium">
                                            Maps →
                                        </a>
                                    </div>
                                </div>

                            @elseif(in_array($vehicle->status, ['booked', 'active']))

                                {{-- On Trip --}}
                                <div class="px-4 py-3 bg-orange-50">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-semibold text-orange-700">
                                                Currently on a trip
                                            </p>
                                            @php
                                                $nextBooking = $vehicle->bookings()
                                                    ->whereIn('status', ['confirmed', 'active'])
                                                    ->orderBy('return_date')
                                                    ->first();
                                            @endphp
                                            @if($nextBooking)
                                                <p class="text-xs text-orange-500 mt-0.5">
                                                    Available from {{ $nextBooking->return_date->format('M j, g:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            @else

                                {{-- Maintenance --}}
                                <div class="px-4 py-3 bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                                        </svg>
                                        <p class="text-sm text-gray-500">Under maintenance — not available</p>
                                    </div>
                                </div>

                            @endif
                        </div>

                        {{-- Book Now Button --}}
                        @if($vehicle->status === 'available')
                            <div class="px-4 pb-4 pt-3">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700
                                                  text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">
                                    {{ __('vehicles.book_now') }}
                                </a>
                            </div>
                        @else
                            <div class="px-4 pb-4 pt-3">
                                <button disabled class="block w-full text-center bg-gray-200 text-gray-400
                                                       font-semibold py-2.5 rounded-xl text-sm cursor-not-allowed">
                                    {{ __('vehicles.not_available') }}
                                </button>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($vehicles->hasPages())
                <div class="mt-8">
                    {{ $vehicles->links() }}
                </div>
            @endif
        @endif

    </div>
@endsection