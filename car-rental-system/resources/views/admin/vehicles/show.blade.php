@extends('layouts.admin')

@section('page-title', $vehicle->full_name)
@section('breadcrumb')
    <a href="{{ route('admin.vehicles.index') }}" class="hover:text-gray-700">
        {{ __('common.nav_vehicles') }}
    </a>
    <span>/</span>
    <span class="text-gray-900 font-medium">{{ $vehicle->full_name }}</span>
@endsection

@section('content')
    <div class="max-w-4xl space-y-6">

        {{-- ─── Vehicle Info Card ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start gap-5">

                {{-- Thumbnail --}}
                @if($vehicle->thumbnail)
                    <img src="{{ asset('storage/' . $vehicle->thumbnail) }}"
                        class="w-32 h-24 object-cover rounded-xl border border-gray-200 flex-shrink-0">
                @endif

                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-xl font-bold text-gray-900">{{ $vehicle->full_name }}</h2>
                        @php
                            $statusColors = [
                                'available' => 'bg-green-50 text-green-700',
                                'booked' => 'bg-orange-50 text-orange-700',
                                'active' => 'bg-blue-50 text-blue-700',
                                'maintenance' => 'bg-gray-100 text-gray-600',
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                                     {{ $statusColors[$vehicle->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($vehicle->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-400">Category</p>
                            <p class="font-medium text-gray-900">{{ $vehicle->category?->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">License Plate</p>
                            <p class="font-mono font-bold text-gray-900">{{ $vehicle->license_plate }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Transmission</p>
                            <p class="font-medium text-gray-900">{{ ucfirst($vehicle->transmission) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Fuel Type</p>
                            <p class="font-medium text-gray-900">{{ ucfirst($vehicle->fuel_type) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold
                          rounded-lg hover:bg-indigo-700 transition-colors">
                    Edit Vehicle
                </a>
            </div>
        </div>

        {{-- ─── GPS Tracker Setup ───────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                GPS Tracker Setup
            </h3>

            @if($vehicle->tracker_token)

                {{-- Tracker URL --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <p class="text-xs text-gray-500 mb-2">Samsung phone tracker URL:</p>
                    <div class="flex items-center gap-2">
                        <code class="text-xs bg-white border border-gray-200 rounded-lg px-3 py-2
                                         flex-1 break-all text-gray-700">
                                {{ route('gps.tracker', [$vehicle->id, $vehicle->tracker_token]) }}
                            </code>
                        <button
                            onclick="navigator.clipboard.writeText('{{ route('gps.tracker', [$vehicle->id, $vehicle->tracker_token]) }}'); this.textContent='Copied!';"
                            class="text-xs bg-blue-600 text-white px-3 py-2 rounded-lg
                                           flex-shrink-0 hover:bg-blue-700 transition-colors">
                            Copy
                        </button>
                    </div>
                </div>

                {{-- Setup Instructions --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
                    <p class="text-sm font-semibold text-blue-800 mb-2">
                        📱 Setup instructions for the Samsung phone:
                    </p>
                    <ol class="space-y-1.5 text-sm text-blue-700">
                        <li>1. Insert a SIM card with mobile data into the phone</li>
                        <li>2. Open <strong>Chrome browser</strong> on the Samsung phone</li>
                        <li>3. Go to the URL above (or send via WhatsApp)</li>
                        <li>4. Tap <strong>Allow</strong> when Chrome asks for location permission</li>
                        <li>5. You will see a dark screen — the phone is now sending GPS every 15 seconds</li>
                        <li>6. Plug the phone into the car charger and place in the glove box</li>
                    </ol>
                </div>

                {{-- GPS Status --}}
                @if($vehicle->last_seen_at && $vehicle->last_seen_at->gt(now()->subHour()))
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                        <p class="text-sm font-semibold text-green-800 mb-1">✅ GPS is active</p>
                        <div class="space-y-1 text-xs text-green-700">
                            <p>Last update: <strong>{{ $vehicle->last_seen_at->diffForHumans() }}</strong></p>
                            <p>
                                Coordinates:
                                <strong>{{ $vehicle->last_latitude }}, {{ $vehicle->last_longitude }}</strong>
                            </p>
                            <p>Speed: <strong>{{ $vehicle->last_speed }} km/h</strong></p>
                        </div>
                        <a href="https://maps.google.com/?q={{ $vehicle->last_latitude }},{{ $vehicle->last_longitude }}"
                            target="_blank" class="inline-block mt-2 text-xs text-green-700 underline font-medium">
                            View on Google Maps →
                        </a>
                    </div>
                @elseif($vehicle->last_seen_at)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                        <p class="text-sm font-semibold text-yellow-800">
                            ⚠️ GPS signal lost
                        </p>
                        <p class="text-xs text-yellow-700 mt-1">
                            Last seen: {{ $vehicle->last_seen_at->diffForHumans() }}
                        </p>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                        <p class="text-sm font-semibold text-yellow-800">
                            ⏳ Waiting for first GPS signal
                        </p>
                        <p class="text-xs text-yellow-700 mt-1">
                            Open the tracker URL on the Samsung phone to start
                        </p>
                    </div>
                @endif

            @else
                <p class="text-sm text-gray-500 mb-4">
                    No tracker URL generated yet. Click the button below to generate one.
                </p>
            @endif

            {{-- Generate / Regenerate Button --}}
            <form method="POST" action="{{ route('admin.vehicles.regenerate-token', $vehicle) }}"
                onsubmit="return confirm('{{ $vehicle->tracker_token ? 'Regenerate tracker URL? The old URL will stop working.' : 'Generate a tracker URL for this vehicle?' }}')">
                @csrf
                <button type="submit" class="text-sm bg-gray-800 text-white px-4 py-2 rounded-lg
                               hover:bg-gray-700 transition-colors">
                    {{ $vehicle->tracker_token ? '🔄 Regenerate Tracker URL' : '🔑 Generate Tracker URL' }}
                </button>
            </form>

        </div>

    </div>
@endsection