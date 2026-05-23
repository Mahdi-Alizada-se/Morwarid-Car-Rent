@extends('layouts.admin')

@section('page-title', 'GPS Tracking')
@section('breadcrumb')
    <span class="text-gray-900 font-medium">GPS Tracking</span>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 100%;
            min-height: 600px;
            border-radius: 12px;
            z-index: 1;
        }

        .vehicle-marker {
            background: transparent;
            border: none;
        }

        .marker-inner {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .marker-inner:hover {
            transform: scale(1.2);
        }

        .marker-green {
            background: #22C55E;
        }

        .marker-orange {
            background: #F97316;
        }

        .marker-gray {
            background: #9CA3AF;
        }

        .speed-badge {
            background: #22C55E;
            color: white;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            border-radius: 10px;
            padding: 1px 6px;
            margin-top: 2px;
            white-space: nowrap;
        }

        .leaflet-popup-content {
            min-width: 220px;
        }

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

        {{-- Main Layout --}}
        <div class="flex gap-4 gps-layout">

            {{-- ─── Left Sidebar ────────────────────────────────────────────────── --}}
            <div class="w-80 flex-shrink-0 bg-white rounded-xl border border-gray-200
                                    flex flex-col overflow-hidden">

                {{-- Sidebar Header --}}
                <div class="p-4 border-b border-gray-100 flex-shrink-0">
                    <h2 class="font-bold text-gray-800 text-lg">GPS Tracking</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $trackedVehicles->count() }} vehicles tracked
                    </p>
                    <a href="{{ route('admin.vehicles.index') }}" class="block w-full text-center bg-blue-600 text-white py-2.5
                                          rounded-xl text-sm font-medium mt-3 hover:bg-blue-700
                                          transition-colors">
                        + Add GPS Tracker to Vehicle
                    </a>
                </div>

                {{-- Legend --}}
                <div class="px-4 py-2 border-b border-gray-100 bg-gray-50 flex-shrink-0">
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                            Moving
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span>
                            Stopped
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-gray-400 inline-block"></span>
                            Offline
                        </span>
                    </div>
                </div>

                {{-- Vehicles List --}}
                <div class="flex-1 overflow-y-auto">

                    @forelse($trackedVehicles as $vehicle)
                                <div class="px-4 py-3 border-b border-gray-50 cursor-pointer
                                                                                                hover:bg-blue-50 transition-colors vehicle-list-item"
                                    onclick="focusVehicle(
                                                                                             {{ $vehicle->id }},
                                                                                             {{ $vehicle->last_longitude ?? 69.2075 }},
                                                                                             {{ $vehicle->last_latitude ?? 34.5553 }},
                                                                                             event
                                                                                         )">

                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-3 h-3 rounded-full flex-shrink-0 mt-1
                                                                                                    {{ $vehicle->status_color === 'green'
                        ? 'bg-green-500 animate-pulse'
                        : ($vehicle->status_color === 'orange'
                            ? 'bg-orange-400'
                            : 'bg-gray-300') }}">
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-800 truncate">
                                                    {{ $vehicle->brand }} {{ $vehicle->model }}
                                                </p>
                                                <p class="text-xs text-gray-400 font-mono">
                                                    {{ $vehicle->license_plate }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            @if($vehicle->last_speed > 0)
                                                <span class="text-xs font-bold text-green-600">
                                                    {{ $vehicle->last_speed }} km/h
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">Stopped</span>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-0.5" id="sidebar-time-{{ $vehicle->id }}">
                                                {{ $vehicle->last_seen_at_human ?? 'Never' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($vehicle->last_address)
                                        <p class="text-xs text-gray-400 mt-1 ml-5 truncate">
                                            📍 {{ \Illuminate\Support\Str::limit($vehicle->last_address, 35) }}
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
                    @empty
                        <div class="p-6 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" stroke-width="1"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <p class="text-sm font-medium">No tracked vehicles</p>
                            <p class="text-xs mt-1">Add a GPS tracker to a vehicle first</p>
                        </div>
                    @endforelse

                    {{-- Untracked Vehicles --}}
                    @if($unTrackedVehicles->count() > 0)
                        <div class="px-4 py-3 bg-yellow-50 border-t border-yellow-100">
                            <p class="text-xs font-semibold text-yellow-700 mb-2">
                                {{ $unTrackedVehicles->count() }} VEHICLES WITHOUT TRACKER
                            </p>
                            @foreach($unTrackedVehicles as $v)
                                <div class="flex items-center justify-between py-1.5">
                                    <span class="text-sm text-gray-600 truncate">
                                        {{ $v->brand }} {{ $v->model }}
                                    </span>
                                    <a href="{{ route('admin.gps.setup', $v) }}"
                                        class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1
                                                                                  rounded-lg hover:bg-yellow-200 flex-shrink-0 ml-2">
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
                        Updates every 5 min via Traccar Client
                    </p>
                </div>
            </div>

            {{-- ─── Map ─────────────────────────────────────────────────────────── --}}
            <div class="flex-1 min-w-0 rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                <div id="map" style="height: calc(100vh - 160px);"></div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <script>

        // ─── Echo Setup ───────────────────────────────────────────────────────────────



        // ─── Leaflet Map Setup ────────────────────────────────────────────────────────

        const map = L.map('map', {
            center: [34.5553, 69.2075],
            zoom: 12,
            zoomControl: false,
        });

        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        // ─── Vehicle Data ─────────────────────────────────────────────────────────────

        const vehicles = @json($trackedVehicles);
        const markers = {};

        // ─── Helpers ──────────────────────────────────────────────────────────────────

        function getMarkerColor(vehicle) {
            if (!vehicle.last_seen_at) return 'gray';
            const diff = (Date.now() - new Date(vehicle.last_seen_at)) / 60000;
            if (diff > 10) return 'gray';
            return parseFloat(vehicle.last_speed) > 5 ? 'green' : 'orange';
        }

        function createCarIcon(color, speed) {
            const speedBadge = parseFloat(speed) > 5
                ? `<div class="speed-badge">${parseFloat(speed).toFixed(0)} km/h</div>`
                : '';

            return L.divIcon({
                className: 'vehicle-marker',
                html: `
                            <div style="display:flex;flex-direction:column;align-items:center;">
                                <div class="marker-inner marker-${color}">
                                    <svg width="20" height="20" fill="white" viewBox="0 0 24 24">
                                        <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3
                                        12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55
                                        0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13
                                        6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5
                                        1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                    </svg>
                                </div>
                                ${speedBadge}
                            </div>
                        `,
                iconSize: [40, 50],
                iconAnchor: [20, 50],
                popupAnchor: [0, -50],
            });
        }

        function buildPopupContent(v) {
            const speed = parseFloat(v.last_speed || 0).toFixed(0);
            const lat = parseFloat(v.last_latitude || 0).toFixed(6);
            const lng = parseFloat(v.last_longitude || 0).toFixed(6);
            const mapsUrl = `https://www.google.com/maps?q=${v.last_latitude},${v.last_longitude}`;

            return `
                        <div style="font-family:-apple-system,sans-serif;min-width:220px;">
                            <div style="font-weight:700;font-size:14px;margin-bottom:8px;color:#111;">
                                ${v.brand} ${v.model}
                            </div>
                            <div style="display:flex;flex-direction:column;gap:4px;font-size:12px;color:#6B7280;">
                                <span>🚗 ${v.license_plate || ''}</span>
                                <span>⚡ <strong style="color:#111;">${speed} km/h</strong></span>
                                ${v.last_address
                    ? `<span>📍 ${v.last_address}</span>`
                    : `<span>📍 ${lat}, ${lng}</span>`}
                                <span>🕐 ${v.last_seen_at_human || 'Never'}</span>
                            </div>
                            <div style="display:flex;gap:8px;margin-top:12px;">
                                <a href="${mapsUrl}" target="_blank"
                                   style="flex:1;text-align:center;background:#2563EB;color:white;
                                          padding:6px 8px;border-radius:8px;font-size:11px;
                                          text-decoration:none;font-weight:500;">
                                   Google Maps
                                </a>
                                <button onclick="showHistory(${v.id})"
                                   style="flex:1;background:#F3F4F6;color:#374151;padding:6px 8px;
                                          border-radius:8px;font-size:11px;border:none;cursor:pointer;font-weight:500;">
                                   Trip History
                                </button>
                            </div>
                        </div>
                    `;
        }

        // ─── Place Initial Markers ────────────────────────────────────────────────────

        vehicles.forEach(v => {
            if (!v.last_latitude || !v.last_longitude) return;

            const color = getMarkerColor(v);
            const icon = createCarIcon(color, v.last_speed || 0);
            const marker = L.marker([v.last_latitude, v.last_longitude], { icon })
                .addTo(map)
                .bindPopup(buildPopupContent(v), { maxWidth: 280 });

            markers[v.id] = { marker, data: v };
        });

        // Fit map to show all markers
        const markerKeys = Object.keys(markers);
        if (markerKeys.length > 0) {
            const group = L.featureGroup(markerKeys.map(k => markers[k].marker));
            map.fitBounds(group.getBounds().pad(0.3));
        }

        // ─── Focus Vehicle From Sidebar ───────────────────────────────────────────────

        function focusVehicle(id, lng, lat, evt) {
            if (!lat || !lng) return;
            map.flyTo([lat, lng], 16, { duration: 1.5 });
            if (markers[id]) {
                setTimeout(() => markers[id].marker.openPopup(), 1500);
            }
            document.querySelectorAll('.vehicle-list-item').forEach(el => {
                el.classList.remove('bg-blue-100');
            });
            if (evt && evt.currentTarget) {
                evt.currentTarget.classList.add('bg-blue-100');
            }
        }

        // ─── Trip History ─────────────────────────────────────────────────────────────

        let historyLayer = null;

        async function showHistory(vehicleId) {
            if (historyLayer) { map.removeLayer(historyLayer); historyLayer = null; }

            try {
                const res = await fetch(`/admin/gps/vehicles/${vehicleId}/history`);
                const data = await res.json();
                const points = data.data || [];

                if (!points.length) {
                    alert('No trip history found for the last 24 hours.');
                    return;
                }

                const coords = points.map(p => [p.lat, p.lng]);
                historyLayer = L.layerGroup();

                // Route line
                L.polyline(coords, {
                    color: '#3B82F6',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '8, 4',
                }).addTo(historyLayer);

                // Start marker
                L.circleMarker(coords[0], {
                    radius: 8, color: '#fff', fillColor: '#22C55E',
                    fillOpacity: 1, weight: 3,
                }).bindPopup('🟢 Trip Start: ' + points[0].recorded_at).addTo(historyLayer);

                // End marker
                L.circleMarker(coords[coords.length - 1], {
                    radius: 8, color: '#fff', fillColor: '#EF4444',
                    fillOpacity: 1, weight: 3,
                }).bindPopup('🔴 Last Point: ' + points[points.length - 1].recorded_at).addTo(historyLayer);

                historyLayer.addTo(map);
                map.fitBounds(L.polyline(coords).getBounds().pad(0.2));

            } catch (e) {
                console.error('Failed to load history:', e);
                alert('Failed to load trip history.');
            }
        }

        // ─── Real-time Pusher Updates ─────────────────────────────────────────────────

        vehicles.forEach(v => {
            window.Echo.private('gps.' + v.id)
                .listen('.VehicleLocationUpdated', (data) => {
                    const lat = parseFloat(data.latitude);
                    const lng = parseFloat(data.longitude);
                    const speed = parseFloat(data.speed);
                    const color = speed > 5 ? 'green' : 'orange';

                    if (markers[data.vehicle_id]) {
                        const newIcon = createCarIcon(color, speed);
                        markers[data.vehicle_id].marker.setLatLng([lat, lng]);
                        markers[data.vehicle_id].marker.setIcon(newIcon);
                        markers[data.vehicle_id].marker.setPopupContent(
                            buildPopupContent({
                                ...markers[data.vehicle_id].data,
                                last_latitude: lat,
                                last_longitude: lng,
                                last_speed: speed,
                                last_seen_at_human: 'Just now',
                            })
                        );
                    } else {
                        const nameParts = (data.vehicle_name || '').split(' ');
                        const icon = createCarIcon(color, speed);
                        const marker = L.marker([lat, lng], { icon })
                            .addTo(map)
                            .bindPopup(buildPopupContent({
                                id: data.vehicle_id,
                                brand: nameParts[0] || '',
                                model: nameParts.slice(1).join(' ') || '',
                                last_latitude: lat,
                                last_longitude: lng,
                                last_speed: speed,
                                last_seen_at_human: 'Just now',
                                license_plate: '',
                                color: '',
                                last_address: '',
                            }));
                        markers[data.vehicle_id] = { marker, data: {} };
                    }

                    const timeEl = document.getElementById('sidebar-time-' + data.vehicle_id);
                    if (timeEl) timeEl.textContent = 'Just now';
                });
        });

    </script>
@endpush