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
                    <a href="{{ route('vehicles.index') }}"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200
                                                                                          rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('common.clear') }}
                    </a>
                @endif
            </form>
        </div>

        {{-- Vehicle Grid --}}
        @if($vehicles->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor"
     stroke-width="1.5" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
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
                                            />
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
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 240.235 240.235"
                                        xmlns="http://www.w3.org/2000/svg" transform="matrix(-1, 0, 0, 1, 0, 0)">
                                        <path
                                            d="M211.744,6.089C208.081,2.163,203.03,0,197.52,0h-15.143c-11.16,0-21.811,8.942-23.74,19.934l-0.955,5.436 c-0.96,5.47,0.332,10.651,3.639,14.589c3.307,3.938,8.186,6.106,13.74,6.106h19.561c2.714,0,5.339-0.542,7.778-1.504l-2.079,17.761 c-2.001-0.841-4.198-1.289-6.507-1.289h-22.318c-9.561,0-18.952,7.609-20.936,16.961l-19.732,93.027l-93.099-6.69 c-5.031-0.36-9.231,1.345-11.835,4.693c-2.439,3.136-3.152,7.343-2.009,11.847l10.824,42.618 c2.345,9.233,12.004,16.746,21.53,16.746h78.049h1.191h39.729c9.653,0,18.336-7.811,19.354-17.411l15.272-143.981 c0.087-0.823,0.097-1.634,0.069-2.437l5.227-44.648c0.738-1.923,1.207-3.967,1.354-6.087l0.346-4.97 C217.214,15.205,215.407,10.016,211.744,6.089z"
                                            fill="#4f46e5" />
                                    </svg>
                                    {{ $vehicle->seats }} {{ __('vehicles.seats') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 231.233 231.233"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M230.505,102.78c-0.365-3.25-4.156-5.695-7.434-5.695c-10.594,0-19.996-6.218-23.939-15.842 c-4.025-9.855-1.428-21.346,6.465-28.587c2.486-2.273,2.789-6.079,0.705-8.721c-5.424-6.886-11.586-13.107-18.316-18.498 c-2.633-2.112-6.502-1.818-8.787,0.711c-6.891,7.632-19.27,10.468-28.836,6.477c-9.951-4.187-16.232-14.274-15.615-25.101 c0.203-3.403-2.285-6.36-5.676-6.755c-8.637-1-17.35-1.029-26.012-0.068c-3.348,0.37-5.834,3.257-5.723,6.617 c0.375,10.721-5.977,20.63-15.832,24.667c-9.451,3.861-21.744,1.046-28.621-6.519c-2.273-2.492-6.074-2.798-8.725-0.731 c-6.928,5.437-13.229,11.662-18.703,18.492c-2.133,2.655-1.818,6.503,0.689,8.784c8.049,7.289,10.644,18.879,6.465,28.849 c-3.99,9.505-13.859,15.628-25.156,15.628c-3.666-0.118-6.275,2.345-6.68,5.679c-1.016,8.683-1.027,17.535-0.049,26.289 c0.365,3.264,4.268,5.688,7.582,5.688c10.07-0.256,19.732,5.974,23.791,15.841c4.039,9.855,1.439,21.341-6.467,28.592 c-2.473,2.273-2.789,6.07-0.701,8.709c5.369,6.843,11.537,13.068,18.287,18.505c2.65,2.134,6.504,1.835,8.801-0.697 c6.918-7.65,19.295-10.481,28.822-6.482c9.98,4.176,16.258,14.262,15.645,25.092c-0.201,3.403,2.293,6.369,5.672,6.755 c4.42,0.517,8.863,0.773,13.32,0.773c4.23,0,8.461-0.231,12.692-0.702c3.352-0.37,5.834-3.26,5.721-6.621 c-0.387-10.716,5.979-20.626,15.822-24.655c9.514-3.886,21.752-1.042,28.633,6.512c2.285,2.487,6.063,2.789,8.725,0.73 c6.916-5.423,13.205-11.645,18.703-18.493c2.135-2.65,1.832-6.503-0.689-8.788c-8.047-7.284-10.656-18.879-6.477-28.839 c3.928-9.377,13.43-15.673,23.65-15.673l1.43,0.038c3.318,0.269,6.367-2.286,6.768-5.671 C231.476,120.379,231.487,111.537,230.505,102.78z M115.616,182.27c-36.813,0-66.654-29.841-66.654-66.653 s29.842-66.653,66.654-66.653s66.654,29.841,66.654,66.653c0,12.495-3.445,24.182-9.428,34.176l-29.186-29.187 c2.113-4.982,3.229-10.383,3.228-15.957c0-10.915-4.251-21.176-11.97-28.893c-7.717-7.717-17.978-11.967-28.891-11.967 c-3.642,0-7.267,0.484-10.774,1.439c-1.536,0.419-2.792,1.685-3.201,3.224c-0.418,1.574,0.053,3.187,1.283,4.418 c0,0,14.409,14.52,19.23,19.34c0.505,0.505,0.504,1.71,0.433,2.144l-0.045,0.317c-0.486,5.3-1.423,11.662-2.196,14.107 c-0.104,0.103-0.202,0.19-0.308,0.296c-0.111,0.111-0.213,0.218-0.32,0.328c-2.477,0.795-8.937,1.743-14.321,2.225l0.001-0.029 l-0.242,0.061c-0.043,0.005-0.123,0.011-0.229,0.011c-0.582,0-1.438-0.163-2.216-0.94c-5.018-5.018-18.862-18.763-18.862-18.763 c-1.242-1.238-2.516-1.498-3.365-1.498c-1.979,0-3.751,1.43-4.309,3.481c-3.811,14.103,0.229,29.273,10.546,39.591 c7.719,7.718,17.981,11.968,28.896,11.968c5.574,0,10.975-1.115,15.956-3.228l29.503,29.503 C141.125,178.412,128.825,182.27,115.616,182.27z"
                                            fill="#4f46e5" />
                                    </svg>
                                    {{ ucfirst($vehicle->transmission) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="m 10.78125,0 -0.625,0.71875 1.1875,1.09375 c 0.03621,0.036212 0.0856,0.084693 0.125,0.125 l -0.25,0.28125 C 10.818532,2.6189681 11.105689,3.1369332 11.25,3.28125 L 12,4.03125 12,10 c 0,1 -0.392136,1 -0.5,1 C 11.392136,11 11,11 11,10 L 11,6 C 11,4.7190916 10,4 9,4 L 9,2 C 9,1.4486964 8.575273,1 8,1 L 2,1 C 1.400757,1 1,1.4247267 1,2 l 0,12 8,0 0,-9 c 0,0 1,0 1,1 l 0,4 c 0,2 1.239698,2 1.5,2 0.275652,0 1.5,0 1.5,-2 L 13,3 C 13,2 12.713983,1.7907839 12.375,1.46875 L 10.78125,0 z M 2,3 8,3 8,6 2,6 2,3 z"
                                            fill="#4f46e5" />
                                    </svg>
                                    {{ ucfirst($vehicle->fuel_type) }}
                                </span>
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

                        {{-- ─── Location Section ──────────────────────────────────────────────────── --}}
<div class="border-t border-gray-100">

    @if($vehicle->status === 'available')

      <div class="p-0">
    <iframe
        width="100%"
        height="150"
        frameborder="0"
        scrolling="no"
        loading="lazy"
        src="https://www.openstreetmap.org/export/embed.html?bbox=69.1875%2C34.5253%2C69.2275%2C34.5853&layer=mapnik&marker=34.5553%2C69.2075"
        class="w-full">
    </iframe>
</div>
<div class="px-4 py-3 bg-green-50 flex items-start justify-between gap-2">
    <div class="flex items-start gap-2">
        <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-green-800">Morwarid Car Hub</p>
            <p class="text-xs text-green-600">Dasht-e-Barchi, Kabul, Afghanistan</p>
        </div>
    </div>
    <a href="https://maps.google.com/?q=Dasht-e-Barchi+Kabul+Afghanistan"
       target="_blank"
       class="text-xs text-green-700 underline flex-shrink-0 mt-0.5 font-medium">
        Maps →
    </a>
</div>

    @elseif(in_array($vehicle->status, ['booked', 'active']))

        @if($vehicle->last_latitude && $vehicle->last_seen_at?->gt(now()->subMinutes(10)))

            @php
                $lat    = $vehicle->last_latitude;
                $lng    = $vehicle->last_longitude;
                $delta  = 0.015;
                $osmUrl = "https://www.openstreetmap.org/export/embed.html?bbox=" .
                    ($lng - $delta) . "%2C" . ($lat - $delta) . "%2C" .
                    ($lng + $delta) . "%2C" . ($lat + $delta) .
                    "&layer=mapnik&marker={$lat}%2C{$lng}";
            @endphp

            <iframe
    width="100%"
    height="150"
    frameborder="0"
    scrolling="no"
    loading="lazy"
    src="{{ $osmUrl }}"
    class="w-full">
</iframe>

<div class="px-4 py-2.5 bg-blue-50">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse
                         inline-block flex-shrink-0"></span>
            <p class="text-xs font-semibold text-blue-800">Live location</p>
        </div>
        <p class="text-xs text-blue-400">
            {{ $vehicle->last_seen_at->diffForHumans() }}
        </p>
    </div>
    <div class="flex items-center gap-3 mt-1 flex-wrap">
        <p class="text-xs font-medium text-blue-600">
            🚗 {{ $vehicle->last_speed }} km/h
        </p>
        @if($vehicle->last_address)
            <p class="text-xs text-blue-500 truncate">
                {{ \Illuminate\Support\Str::limit($vehicle->last_address, 30) }}
            </p>
        @endif
        <a href="https://www.google.com/maps?q={{ $vehicle->last_latitude }},{{ $vehicle->last_longitude }}"
           target="_blank"
           class="text-xs text-blue-600 underline flex-shrink-0 font-medium">
            Open in Maps →
        </a>
    </div>
</div>

        @else

            <div class="px-4 py-3 bg-orange-50">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-orange-700">
                            Currently on a rental trip
                        </p>
                        @php
                            $next = $vehicle->bookings()
                                ->whereIn('status', ['confirmed', 'active'])
                                ->orderBy('return_date')
                                ->first();
                        @endphp
                        @if($next)
                            <p class="text-xs text-orange-500 mt-0.5">
                                Available {{ $next->return_date->format('M j, g:i A') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        @endif

    @else

        <div class="px-4 py-3 bg-gray-50 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none"
                 stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
            </svg>
            <p class="text-sm text-gray-500">Under maintenance — not available</p>
        </div>

    @endif

</div>

{{-- Book Now Button --}}
@if($vehicle->status === 'available')
    <div class="px-4 pb-4 pt-3">
        @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('admin.vehicles.show', $vehicle) }}"
               class="block w-full text-center bg-gray-100 text-gray-600
                      font-medium py-2.5 rounded-xl text-sm hover:bg-gray-200
                      transition-colors">
                View Details (Admin)
            </a>
        @else
            <a href="{{ route('vehicles.show', $vehicle) }}"
               class="block w-full text-center bg-indigo-600 hover:bg-indigo-700
                      text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">
                {{ __('vehicles.book_now') }}
            </a>
        @endif
    </div>
@else
    <div class="px-4 pb-4 pt-3">
        <button disabled
                class="block w-full text-center bg-gray-200 text-gray-400
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