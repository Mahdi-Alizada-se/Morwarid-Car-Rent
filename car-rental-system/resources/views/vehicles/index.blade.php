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

                                {{-- Available — show pickup location map --}}
                                <div class="p-0">
                                    <iframe width="100%" height="150" frameborder="0" scrolling="no"
                                        src="{{ config('company.osm_embed_url') }}" class="w-full" loading="lazy">
                                    </iframe>
                                </div>
                                <div class="px-4 py-3 bg-green-50 flex items-start justify-between gap-2">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-semibold text-green-800">
                                                {{ config('company.pickup_name') }}
                                            </p>
                                            <p class="text-xs text-green-600">{{ config('company.address') }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ config('company.maps_url') }}" target="_blank"
                                        class="text-xs text-green-700 underline flex-shrink-0 mt-0.5 font-medium">
                                        Maps →
                                    </a>
                                </div>

                            @elseif(in_array($vehicle->status, ['booked', 'active']))

                                @if($vehicle->last_latitude && $vehicle->last_seen_at?->gt(now()->subMinutes(60)))

                                    {{-- Has recent GPS data — show live location map --}}
                                    @php
                                        $lat = $vehicle->last_latitude;
                                        $lng = $vehicle->last_longitude;
                                        $delta = 0.015;
                                        $osmUrl = "https://www.openstreetmap.org/export/embed.html?bbox=" .
                                            ($lng - $delta) . "%2C" . ($lat - $delta) . "%2C" .
                                            ($lng + $delta) . "%2C" . ($lat + $delta) .
                                            "&layer=mapnik&marker={$lat}%2C{$lng}";
                                    @endphp

                                    <iframe width="100%" height="150" frameborder="0" scrolling="no" src="{{ $osmUrl }}" class="w-full"
                                        loading="lazy">
                                    </iframe>

                                    <div class="px-4 py-2.5 bg-blue-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <span
                                                    class="w-2 h-2 rounded-full bg-green-500 animate-pulse
                                                                                                                                                             inline-block flex-shrink-0"></span>
                                                <p class="text-xs font-semibold text-blue-800">Live location</p>
                                            </div>
                                            <p class="text-xs text-blue-400">
                                                {{ $vehicle->last_seen_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1">
                                            <p class="text-xs text-blue-600">
                                                <svg viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                                    xmlns:xlink="http://www.w3.org/1999/xlink" fill="#4f46e5" stroke="#4f46e5">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <title>car_line</title>
                                                        <g id="页面-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                            <g id="Transport" transform="translate(-288.000000, 0.000000)"
                                                                fill-rule="nonzero">
                                                                <g id="car_line" transform="translate(288.000000, 0.000000)">
                                                                    <path
                                                                        d="M24,0 L24,24 L0,24 L0,0 L24,0 Z M12.5934901,23.257841 L12.5819402,23.2595131 L12.5108777,23.2950439 L12.4918791,23.2987469 L12.4918791,23.2987469 L12.4767152,23.2950439 L12.4056548,23.2595131 C12.3958229,23.2563662 12.3870493,23.2590235 12.3821421,23.2649074 L12.3780323,23.275831 L12.360941,23.7031097 L12.3658947,23.7234994 L12.3769048,23.7357139 L12.4804777,23.8096931 L12.4953491,23.8136134 L12.4953491,23.8136134 L12.5071152,23.8096931 L12.6106902,23.7357139 L12.6232938,23.7196733 L12.6232938,23.7196733 L12.6266527,23.7031097 L12.609561,23.275831 C12.6075724,23.2657013 12.6010112,23.2592993 12.5934901,23.257841 L12.5934901,23.257841 Z M12.8583906,23.1452862 L12.8445485,23.1473072 L12.6598443,23.2396597 L12.6498822,23.2499052 L12.6498822,23.2499052 L12.6471943,23.2611114 L12.6650943,23.6906389 L12.6699349,23.7034178 L12.6699349,23.7034178 L12.678386,23.7104931 L12.8793402,23.8032389 C12.8914285,23.8068999 12.9022333,23.8029875 12.9078286,23.7952264 L12.9118235,23.7811639 L12.8776777,23.1665331 C12.8752882,23.1545897 12.8674102,23.1470016 12.8583906,23.1452862 L12.8583906,23.1452862 Z M12.1430473,23.1473072 C12.1332178,23.1423925 12.1221763,23.1452606 12.1156365,23.1525954 L12.1099173,23.1665331 L12.0757714,23.7811639 C12.0751323,23.7926639 12.0828099,23.8018602 12.0926481,23.8045676 L12.108256,23.8032389 L12.3092106,23.7104931 L12.3186497,23.7024347 L12.3186497,23.7024347 L12.3225043,23.6906389 L12.340401,23.2611114 L12.337245,23.2485176 L12.337245,23.2485176 L12.3277531,23.2396597 L12.1430473,23.1473072 Z"
                                                                        id="MingCute" fill-rule="nonzero"> </path>
                                                                    <path
                                                                        d="M15.7639,4 C16.9002,4 17.939,4.64201 18.4472,5.65836 L18.4472,5.65836 L19.8297,8.42332 C20.0735,8.32394 20.3168,8.22155 20.5532,8.10538 C21.0471,7.85869 21.6475,8.05894 21.8944,8.55279 C22.1414,9.04676 21.9412,9.64744 21.4472,9.89443 C20.9532,10.1414 20.7265,10.2169 20.7265,10.2169 L20.7265,10.2169 L21.6833,12.1305 C21.8915,12.5471 22,13.0064 22,13.4721 L22,13.4721 L22,16 C22,16.8885 21.6137,17.6868 21,18.2361 L21,18.2361 L21,19.5 C21,20.3284 20.3284,21 19.5,21 C18.6715,21 18,20.3284 18,19.5 L18,19.5 L18,19 L5.99998,19 L5.99998,19.5 C5.99998,20.3284 5.3284,21 4.49997,21 C3.67155,21 2.99997,20.3284 2.99997,19.5 L2.99997,19.5 L2.99997,18.2361 C2.38623,17.6868 1.99997,16.8885 1.99997,16 L1.99997,16 L1.99997,13.4721 C1.99997,13.0064 2.10841,12.5471 2.31669,12.1305 L2.31669,12.1305 L3.2735,10.2169 C3.03141,10.116 2.79108,10.0105 2.55525,9.89567 L2.55525,9.89567 C2.05878,9.64744 1.85856,9.04676 2.10555,8.55279 C2.35213,8.05962 2.96121,7.86667 3.4517,8.10779 C3.68712,8.22182 3.92811,8.3246 4.17028,8.42332 L4.17028,8.42332 L5.55276,5.65836 C6.06094,4.64201 7.09973,4 8.23604,4 L8.23604,4 Z M18.8341,10.9044 C17.1339,11.4406 14.715,12 12,12 C9.28499,12 6.86601,11.4406 5.16583,10.9044 L4.10555,13.0249 C4.03612,13.1638 3.99997,13.3169 3.99997,13.4721 L3.99997,16 C3.99997,16.5523 4.44769,17 4.99997,17 L19,17 C19.5523,17 20,16.5523 20,16 L20,13.4721 C20,13.3169 19.9638,13.1638 19.8944,13.0249 L18.8341,10.9044 Z M7.49997,13 C8.3284,13 8.99997,13.6716 8.99997,14.5 C8.99997,15.3284 8.3284,16 7.49997,16 C6.67155,16 5.99997,15.3284 5.99997,14.5 C5.99997,13.6716 6.67155,13 7.49997,13 Z M16.5,13 C17.3284,13 18,13.6716 18,14.5 C18,15.3284 17.3284,16 16.5,16 C15.6715,16 15,15.3284 15,14.5 C15,13.6716 15.6715,13 16.5,13 Z M15.7639,6 L8.23604,6 C7.85727,6 7.51101,6.214 7.34162,6.55279 L6.07258,9.09086 C7.61992,9.55498 9.70503,10 12,10 C14.2949,10 16.38,9.55498 17.9274,9.09086 L16.6583,6.55279 C16.4889,6.214 16.1427,6 15.7639,6 Z"
                                                                        id="形状结合" fill="#09244B"> </path>
                                                                </g>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg> {{ $vehicle->last_speed }} km/h
                                            </p>
                                            <a href="https://maps.google.com/?q={{ $vehicle->last_latitude }},{{ $vehicle->last_longitude }}"
                                                target="_blank" class="text-xs text-blue-600 underline font-medium">
                                                Open in Maps →
                                            </a>
                                        </div>
                                    </div>

                                @else

                                    {{-- On trip but no GPS data --}}
                                    <div class="px-4 py-3 bg-orange-50">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                            </svg>
                                            <div>
                                                <p class="text-sm font-semibold text-orange-700">On a rental trip</p>
                                                @php
                                                    $next = $vehicle->bookings()
                                                        ->whereIn('status', ['confirmed', 'active'])
                                                        ->orderBy('return_date')
                                                        ->first();
                                                @endphp
                                                @if($next)
                                                    <p class="text-xs text-orange-500 mt-0.5">
                                                        Back {{ $next->return_date->format('M j, g:i A') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                @endif

                            @else

                                {{-- Maintenance --}}
                                <div class="px-4 py-3 bg-gray-50 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                        stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Under maintenance</p>
                                </div>

                            @endif

                        </div>

                        {{-- Book Now Button --}}
                        @if($vehicle->status === 'available')
                            <div class="px-4 pb-4 pt-3">
                                <a href="{{ route('vehicles.show', $vehicle) }}"
                                    class="block w-full text-center bg-indigo-600 hover:bg-indigo-700
                                                                                                                                                  text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">
                                    {{ __('vehicles.book_now') }}
                                </a>
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