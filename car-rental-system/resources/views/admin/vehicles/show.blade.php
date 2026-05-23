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

@extends('layouts.admin')

@section('page-title', 'GPS Tracking')
    @section('breadcrumb')
        <span class="text-gray-900 font-medium">GPS Tracking</span>
    @endsection

    @push('styles')
        <link href='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css' rel='stylesheet'>
        <style>
            .gps-layout {
                height: calc(100vh - 130px);
            }

            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

    @section('content')
        <div class="space-y-4">

            {{-- Header --}}
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">GPS Tracking</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $trackedVehicles->count() }} vehicle{{ $trackedVehicles->count() !== 1 ? 's' : '' }} tracked
                    </p>
                </div>
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 text-sm text-green-700">
                        ✅ {{ session('success') }}
                    </div>
                @endif
            </div>

            @if($trackedVehicles->count() === 0)

                {{-- Empty State --}}
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <p class="font-semibold text-gray-600 text-lg">No GPS Trackers Set Up</p>
                    <p class="text-sm text-gray-400 mt-2 max-w-md mx-auto">
                        Go to a vehicle's edit page and set up a Traccar device ID,
                        then install the free Traccar Client app on a Samsung phone in each car.
                    </p>
                    <a href="{{ route('admin.vehicles.index') }}" class="inline-block mt-5 px-5 py-2.5 bg-indigo-600 text-white
                                  text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                        Go to Vehicles →
                    </a>
                </div>

            @else

                {{-- Map + Sidebar --}}
                <div class="flex gap-4 gps-layout">

                    {{-- ─── Left Sidebar ────────────────────────────────────────────── --}}
                    <div class="w-80 flex-shrink-0 bg-white rounded-xl border border-gray-200
                                    flex flex-col overflow-hidden">

                        {{-- Sidebar Header --}}
                        <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0">
                            <h3 class="font-bold text-gray-900">Live Vehicles</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $trackedVehicles->where('is_online', true)->count() }} online
                                · {{ $trackedVehicles->count() }} total
                            </p>
                        </div>

                        {{-- Add tracker button --}}
                        @if($unTrackedVehicles->count() > 0)
                            <div class="px-3 pt-3 pb-0 flex-shrink-0">
                                <a href="{{ route('admin.vehicles.index') }}" class="block w-full text-center bg-blue-600 text-white py-2
                                                  rounded-lg text-sm font-medium hover:bg-blue-700
                                                  transition-colors">
                                    + Set Up New GPS Tracker
                                </a>
                            </div>
                        @endif

                        {{-- Tracked Vehicles List --}}
                        <div class="flex-1 overflow-y-auto">

                            @foreach($trackedVehicles as $vehicle)
                                    <div class="p-3 border-b border-gray-50 cursor-pointer
                                                        hover:bg-gray-50 transition-colors vehicle-item" data-id="{{ $vehicle->id }}"
                                        onclick="focusVehicle(
                                                     {{ $vehicle->id }},
                                                     {{ $vehicle->last_longitude ?? 69.2075 }},
                                                     {{ $vehicle->last_latitude ?? 34.5553 }}
                                                 )">

                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-3 h-3 rounded-full flex-shrink-0 mt-0.5
                                                            {{ $vehicle->status_color === 'green'
                                ? 'bg-green-500 animate-pulse'
                                : ($vehicle->status_color === 'orange'
                                    ? 'bg-orange-400'
                                    : 'bg-gray-300') }}">
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-gray-800 truncate">
                                                        {{ $vehicle->brand }} {{ $vehicle->model }}
                                                    </p>
                                                    <p class="text-xs text-gray-400 font-mono">
                                                        {{ $vehicle->license_plate }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right flex-shrink-0 ml-2">
                                                @if($vehicle->last_speed > 0)
                                                    <span class="text-xs font-semibold text-green-600">
                                                        {{ $vehicle->last_speed }} km/h
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">Stopped</span>
                                                @endif
                                                <p class="text-xs text-gray-400 mt-0.5" id="time-{{ $vehicle->id }}">
                                                    {{ $vehicle->last_seen_at_human ?? 'Never' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($vehicle->last_address)
                                            <p class="text-xs text-gray-400 mt-1 ml-5 truncate">
                                                📍 {{ $vehicle->last_address }}
                                            </p>
                                        @endif

                                        <div class="flex gap-3 mt-2 ml-5">
                                            <button onclick="event.stopPropagation(); showHistory({{ $vehicle->id }})"
                                                class="text-xs text-blue-600 hover:underline">
                                                Trip History
                                            </button>
                                            <a href="{{ route('admin.gps.setup', $vehicle) }}" onclick="event.stopPropagation()"
                                                class="text-xs text-gray-500 hover:underline">
                                                Edit Tracker
                                            </a>
                                        </div>
                                    </div>
                            @endforeach

                            {{-- Untracked Vehicles --}}
                            @if($unTrackedVehicles->count() > 0)
                                <div class="p-3 border-t bg-gray-50">
                                    <p class="text-xs font-semibold text-gray-500 uppercase
                                                      tracking-wide mb-2">
                                        Without Tracker ({{ $unTrackedVehicles->count() }})
                                    </p>
                                    @foreach($unTrackedVehicles as $v)
                                        <div class="flex items-center justify-between py-1.5">
                                            <span class="text-sm text-gray-600 truncate">
                                                {{ $v->brand }} {{ $v->model }}
                                            </span>
                                            <a href="{{ route('admin.gps.setup', $v) }}" class="text-xs bg-blue-100 text-blue-700 px-2 py-1
                                                                  rounded-lg hover:bg-blue-200 flex-shrink-0 ml-2">
                                                Set Up
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>

                        {{-- Footer --}}
                        <div class="px-4 py-2 border-t border-gray-100 flex-shrink-0">
                            <p class="text-xs text-gray-400 text-center">
                                Updates every 5 min via Traccar
                            </p>
                        </div>
                    </div>

                    {{-- ─── Map ─────────────────────────────────────────────────────── --}}
                    <div class="flex-1 min-w-0">
                        <div id="map" class="w-full h-full rounded-xl border border-gray-200 shadow-sm"></div>
                    </div>

                </div>

            @endif

        </div>
    @endsection

    @push('scripts')
        @if($trackedVehicles->count() > 0)
            <script src='https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js'></script>
            <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
            <script>
                const MAPBOX_TOKEN = '{{ config('traccar.mapbox_token') }}';

                if (MAPBOX_TOKEN) {

                    // ─── Echo Setup ───────────────────────────────────────────────────────────

                    const echo = new Echo({
                        broadcaster: 'pusher',
                        key: '{{ config('broadcasting.connections.pusher.key') }}',
                        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                        forceTLS: true,
                        authEndpoint: '/broadcasting/auth',
                        auth: {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        }
                    });

                    // ─── Mapbox Setup ─────────────────────────────────────────────────────────

                    mapboxgl.accessToken = MAPBOX_TOKEN;

                    const map = new mapboxgl.Map({
                        container: 'map',
                        style: 'mapbox://styles/mapbox/streets-v12',
                        center: [69.2075, 34.5553],
                        zoom: 12,
                    });

                    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
                    map.addControl(new mapboxgl.FullscreenControl(), 'top-right');

                    const vehicles = @json($trackedVehicles);
                    const markers = {};

                    // ─── Helpers ──────────────────────────────────────────────────────────────

                    function markerColor(vehicle) {
                        if (!vehicle.last_seen_at) return '#9CA3AF';
                        const diff = (Date.now() - new Date(vehicle.last_seen_at)) / 60000;
                        if (diff > 10) return '#9CA3AF';
                        return (vehicle.last_speed || 0) > 5 ? '#22C55E' : '#F97316';
                    }

                    function createCarMarker(color, speed) {
                        const el = document.createElement('div');
                        el.innerHTML = `
                        <div style="
                            background:${color};border:3px solid white;border-radius:50%;
                            width:40px;height:40px;display:flex;align-items:center;
                            justify-content:center;box-shadow:0 3px 10px rgba(0,0,0,0.3);
                            cursor:pointer;transition:transform 0.2s;
                        " onmouseover="this.style.transform='scale(1.2)'"
                           onmouseout="this.style.transform='scale(1)'">
                            <svg width="22" height="22" fill="white" viewBox="0 0 24 24">
                                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3
                                12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55
                                0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13
                                6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5
                                1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                            </svg>
                        </div>
                        ${speed > 5
                                ? `<div style="background:#22c55e;color:white;font-size:10px;font-weight:bold;
                                           text-align:center;border-radius:10px;padding:1px 6px;margin-top:2px;">
                                   ${speed} km/h
                               </div>`
                                : ''}
                    `;
                        return el;
                    }

                    function buildPopup(v) {
                        return `
                        <div style="font-family:sans-serif;min-width:220px;padding:4px;">
                            <p style="font-weight:700;font-size:14px;margin:0 0 8px;">
                                ${v.brand} ${v.model}
                            </p>
                            <div style="display:flex;flex-direction:column;gap:4px;
                                        font-size:12px;color:#6B7280;">
                                <span>🚗 ${v.license_plate}</span>
                                <span>⚡ <strong style="color:#111">${v.last_speed || 0} km/h</strong></span>
                                ${v.last_address ? `<span>📍 ${v.last_address}</span>` : ''}
                                <span>🕐 ${v.last_seen_at_human || 'Never'}</span>
                            </div>
                            <div style="display:flex;gap:8px;margin-top:10px;">
                                ${v.last_latitude ? `
                                    <a href="https://maps.google.com/?q=${v.last_latitude},${v.last_longitude}"
                                       target="_blank"
                                       style="flex:1;text-align:center;background:#2563EB;color:white;
                                              padding:5px;border-radius:6px;font-size:11px;text-decoration:none;">
                                       Google Maps
                                    </a>` : ''}
                                <button onclick="showHistory(${v.id})"
                                        style="flex:1;background:#F3F4F6;color:#374151;padding:5px;
                                               border-radius:6px;font-size:11px;border:none;cursor:pointer;">
                                    Trip History
                                </button>
                            </div>
                        </div>
                    `;
                    }

                    // ─── Place Initial Markers ────────────────────────────────────────────────

                    map.on('load', function () {
                        vehicles.forEach(v => {
                            if (!v.last_latitude || !v.last_longitude) return;

                            const el = createCarMarker(markerColor(v), v.last_speed || 0);
                            const popup = new mapboxgl.Popup({ offset: 25, maxWidth: '280px' })
                                .setHTML(buildPopup(v));
                            const marker = new mapboxgl.Marker(el)
                                .setLngLat([v.last_longitude, v.last_latitude])
                                .setPopup(popup)
                                .addTo(map);

                            markers[v.id] = { marker, el, data: v };
                        });
                    });

                    // ─── Focus Vehicle ────────────────────────────────────────────────────────

                    function focusVehicle(id, lng, lat) {
                        map.flyTo({ center: [lng, lat], zoom: 15, duration: 1000 });
                        if (markers[id]) markers[id].marker.togglePopup();
                    }

                    // ─── Trip History ─────────────────────────────────────────────────────────

                    async function showHistory(vehicleId) {
                        try {
                            const res = await fetch(`/admin/gps/vehicles/${vehicleId}/history`);
                            const data = await res.json();
                            const points = data.data;

                            if (!points.length) {
                                alert('No trip history in the last 24 hours for this vehicle.');
                                return;
                            }

                            const coords = points.map(p => [p.lng, p.lat]);
                            const srcId = 'history-' + vehicleId;
                            const layId = 'history-line-' + vehicleId;

                            if (map.getSource(srcId)) {
                                map.removeLayer(layId);
                                map.removeSource(srcId);
                            }

                            map.addSource(srcId, {
                                type: 'geojson',
                                data: {
                                    type: 'Feature',
                                    geometry: { type: 'LineString', coordinates: coords }
                                }
                            });

                            map.addLayer({
                                id: layId,
                                type: 'line',
                                source: srcId,
                                layout: { 'line-join': 'round', 'line-cap': 'round' },
                                paint: {
                                    'line-color': '#3B82F6',
                                    'line-width': 3,
                                    'line-dasharray': [2, 1],
                                }
                            });

                            const bounds = coords.reduce(
                                (b, c) => b.extend(c),
                                new mapboxgl.LngLatBounds(coords[0], coords[0])
                            );
                            map.fitBounds(bounds, { padding: 60 });

                        } catch (e) {
                            console.error('Failed to load history:', e);
                            alert('Failed to load trip history.');
                        }
                    }

                    // ─── Real-time Pusher Updates ─────────────────────────────────────────────

                    vehicles.forEach(v => {
                        echo.private('gps.' + v.id)
                            .listen('.VehicleLocationUpdated', (data) => {
                                const lat = data.latitude;
                                const lng = data.longitude;
                                const speed = data.speed || 0;
                                const color = speed > 5 ? '#22C55E' : '#F97316';

                                if (markers[data.vehicle_id]) {
                                    markers[data.vehicle_id].marker.setLngLat([lng, lat]);
                                    markers[data.vehicle_id].el.querySelector('div').style.background = color;

                                    const timeEl = document.getElementById('time-' + data.vehicle_id);
                                    if (timeEl) timeEl.textContent = 'Just now';

                                } else {
                                    const el = createCarMarker(color, speed);
                                    const marker = new mapboxgl.Marker(el)
                                        .setLngLat([lng, lat])
                                        .addTo(map);
                                    markers[data.vehicle_id] = { marker, el };
                                }
                            });
                    });

                } else {
                    document.getElementById('map').innerHTML = `
                    <div style="height:100%;display:flex;align-items:center;justify-content:center;
                                background:#f9fafb;border-radius:12px;">
                        <div style="text-align:center;color:#9ca3af;">
                            <p style="font-size:14px;font-weight:600;margin:0;">
                                Mapbox token not configured
                            </p>
                            <p style="font-size:12px;margin-top:4px;">
                                Add MAPBOX_TOKEN to your .env file
                            </p>
                        </div>
                    </div>
                `;
                }
            </script>
        @endif
    @endpush
@endsection