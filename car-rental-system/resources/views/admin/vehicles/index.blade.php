@extends('layouts.admin')

@section('page-title', __('common.nav_vehicles'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_vehicles') }}</span>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('All Vehicles') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage your fleet') }}</p>
            </div>
            <a href="{{ route('admin.vehicles.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Add Vehicle') }}
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('Search brand, model, plate...') }}"
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <select name="status"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>{{ __('Available') }}
                    </option>
                    <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>{{ __('Booked') }}</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>
                        {{ __('Maintenance') }}
                    </option>
                </select>
                <select name="category_id"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    {{ __('Filter') }}
                </button>
                @if(request()->hasAny(['search', 'status', 'category_id']))
                    <a href="{{ route('admin.vehicles.index') }}"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('Clear') }}
                    </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 w-16">{{ __('Photo') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('Vehicle') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden md:table-cell">
                                {{ __('Category') }}
                            </th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3">{{ __('Status') }}</th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">
                                {{ __('Daily Rate') }}
                            </th>
                            <th class="text-left font-semibold text-gray-600 px-4 py-3 hidden lg:table-cell">
                                {{ __('Plate') }}
                            </th>
                            <th class="text-right font-semibold text-gray-600 px-4 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($vehicles as $vehicle)
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Thumbnail --}}
                                <td class="px-4 py-3">
                                    @if($vehicle->thumbnail)
                                        <img src="{{ asset('storage/' . $vehicle->thumbnail) }}" alt="{{ $vehicle->full_name }}"
                                            class="w-12 h-10 object-cover rounded-lg border border-gray-200">
                                    @else
                                        <div class="w-12 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>

                                {{-- Vehicle Info --}}
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                                    <p class="text-gray-500 text-xs">{{ $vehicle->year }} ·
                                        {{ ucfirst($vehicle->transmission) }} · {{ $vehicle->seats }} {{ __('seats') }}
                                    </p>
                                </td>

                                {{-- Category --}}
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ $vehicle->category?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-4 py-3">
                                    @if($vehicle->status === 'available')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            {{ __('Available') }}
                                        </span>
                                    @elseif($vehicle->status === 'maintenance')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                            {{ __('Maintenance') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ __('Booked') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Daily Rate --}}
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    @php
                                        $dailyRule = $vehicle->pricingRules->first();
                                    @endphp
                                    @if($dailyRule)
                                        <span class="font-semibold text-gray-900">AFN
                                            {{ number_format($dailyRule->base_rate) }}</span>
                                        <span class="text-gray-400 text-xs">/{{ __('day') }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Plate --}}
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <code
                                        class="text-xs bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $vehicle->license_plate }}</code>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Toggle Status --}}
                                        @if($vehicle->status !== 'booked')
                                            <form method="POST" action="{{ route('admin.vehicles.toggle-status', $vehicle) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    title="{{ $vehicle->status === 'available' ? __('Set Maintenance') : __('Set Available') }}"
                                                    class="p-1.5 text-gray-400 hover:text-orange-500 transition-colors rounded-lg hover:bg-orange-50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif


                                        {{-- GPS Status --}}
                                        @if($vehicle->tracker_token)
                                            <span
                                                title="{{ $vehicle->last_seen_at ? 'GPS: ' . $vehicle->last_seen_at->diffForHumans() : 'GPS: No signal' }}"
                                                class="p-1.5 rounded-lg inline-flex">
                                                <span
                                                    class="w-2 h-2 rounded-full {{ $vehicle->last_seen_at && $vehicle->last_seen_at->gt(now()->subMinutes(10)) ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }}"></span>
                                            </span>
                                        @endif

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors rounded-lg hover:bg-indigo-50"></a>
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}"
                                            class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors rounded-lg hover:bg-indigo-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this vehicle?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                    </svg>
                                    <p class="font-medium">{{ __('No vehicles found.') }}</p>
                                    <p class="text-sm mt-1">{{ __('Add your first vehicle to get started.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($vehicles->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection