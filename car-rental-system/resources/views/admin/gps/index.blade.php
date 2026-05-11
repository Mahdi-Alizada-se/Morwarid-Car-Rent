@extends('layouts.admin')

@section('page-title', __('common.nav_gps_tracking'))
@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('common.nav_gps_tracking') }}</span>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #gps-map {
            height: calc(100vh - 240px);
            min-height: 400px;
            border-radius: 12px;
        }

        .leaflet-popup-content {
            font-size: 13px;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="flex gap-5" x-data="gpsTracker()" x-init="init()">

        {{-- ─── Map ──────────────────────────────────────────────────────────────── --}}
        <div class="flex-1">
            <div class="bg-white rounded-xl border border-gray-200 p-3">
                <div id="gps-map"></div>
            </div>
        </div>

        {{-- ─── Sidebar ──────────────────────────────────────────────────────────── --}}
        <div class="w-72 flex-shrink-0 space-y-4">

            {{-- Header --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-gray-900">{{ __('chat.active_vehicles') }}</h3>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold"
                        x-text="`${vehicles.length} {{ __('chat.active') }}`">
                    </span>
                </div>
                <p class="text-xs text-gray-400">{{ __('chat.live_tracking') }}</p>
            </div>

            {{-- Vehicle List --}}
            <div class="space-y-2 overflow-y-auto" style="max-height: calc(100vh - 320px);">

                <template x-if="vehicles.length === 0">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-400 text-sm">
                        {{ __('chat.no_active_vehicles') }}
                    </div>
                </template>

                <template x-for="vehicle in vehicles" :key="vehicle.vehicle_id">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:border-indigo-300 transition-colors"
                        @click="focusVehicle(vehicle)">

                        <div class="flex items-center justify-between mb-2">
                            <p class="font-semibold text-gray-900 text-sm" x-text="vehicle.vehicle_name"></p>
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        </div>

                        <p class="text-xs text-gray-500 mb-2" x-text="vehicle.customer"></p>

                        <div class="space-y-1 text-xs text-gray-600">
                            <div class="flex justify-between">
                                <span class="text-gray-400">{{ __('chat.lat') }}</span>
                                <span x-text="vehicle.latitude?.toFixed(6)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">{{ __('chat.lng') }}</span>
                                <span x-text="vehicle.longitude?.toFixed(6)"></span>
                            </div>
                            <div class="flex justify-between" x-show="vehicle.speed !== null">
                                <span class="text-gray-400">{{ __('chat.speed') }}</span>
                                <span x-text="`${vehicle.speed} km/h`"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">{{ __('chat.updated') }}</span>
                                <span x-text="vehicle.recorded_ago"></span>
                            </div>
                        </div>

                        <button @click.stop="loadHistory(vehicle)"
                            class="mt-3 w-full py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                            {{ __('chat.view_route') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        const gpsEcho = new Echo({
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

        function gpsTracker() {
            return {
                map: null,
                markers: {},
                polylines: {},
                vehicles: [],
                echoChannels: [],

                async init() {
                    // Initialize Leaflet map
                    this.map = L.map('gps-map').setView([34.5553, 69.2075], 12); // Kabul

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(this.map);

                    await this.loadActiveVehicles();
                },

                async loadActiveVehicles() {
                    try {
                        const res = await fetch('/api/v1/gps/active-locations', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                        const data = await res.json();
                        this.vehicles = data.vehicles ?? [];

                        // Plot markers and subscribe to each vehicle
                        this.vehicles.forEach(vehicle => {
                            this.addOrUpdateMarker(vehicle);
                            this.subscribeToVehicle(vehicle.vehicle_id);
                        });

                        // Fit map to markers
                        if (this.vehicles.length > 0) {
                            const bounds = this.vehicles.map(v => [v.latitude, v.longitude]);
                            this.map.fitBounds(bounds, { padding: [40, 40] });
                        }
                    } catch (e) {
                        console.error('Failed to load vehicle locations:', e);
                    }
                },

                addOrUpdateMarker(vehicle) {
                    const latlng = [vehicle.latitude, vehicle.longitude];

                    const icon = L.divIcon({
                        html: `<div style="background:#4f46e5;color:white;padding:4px 8px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.3);">🚗 ${vehicle.vehicle_name}</div>`,
                        className: '',
                        iconAnchor: [40, 10],
                    });

                    if (this.markers[vehicle.vehicle_id]) {
                        this.markers[vehicle.vehicle_id].setLatLng(latlng);
                    } else {
                        const marker = L.marker(latlng, { icon }).addTo(this.map);
                        marker.bindPopup(this.buildPopup(vehicle));
                        this.markers[vehicle.vehicle_id] = marker;
                    }

                    // Update popup content
                    if (this.markers[vehicle.vehicle_id]) {
                        this.markers[vehicle.vehicle_id].setPopupContent(this.buildPopup(vehicle));
                    }
                },

                buildPopup(vehicle) {
                    return `
                    <div style="min-width: 180px;">
                        <p style="font-weight:700;font-size:14px;margin-bottom:6px;">${vehicle.vehicle_name}</p>
                        <p style="color:#6b7280;font-size:12px;">Customer: ${vehicle.customer}</p>
                        <p style="color:#6b7280;font-size:12px;">Ref: ${vehicle.reference}</p>
                        ${vehicle.speed !== null ? `<p style="color:#6b7280;font-size:12px;">Speed: ${vehicle.speed} km/h</p>` : ''}
                        <p style="color:#9ca3af;font-size:11px;margin-top:4px;">Updated: ${vehicle.recorded_ago}</p>
                    </div>
                `;
                },

                subscribeToVehicle(vehicleId) {
                    const channel = `gps.${vehicleId}`;
                    this.echoChannels.push(channel);

                    gpsEcho.private(channel)
                        .listen('.location-updated', (event) => {
                            // Update vehicle in list
                            const index = this.vehicles.findIndex(v => v.vehicle_id === vehicleId);
                            if (index !== -1) {
                                this.vehicles[index] = {
                                    ...this.vehicles[index],
                                    latitude: event.latitude,
                                    longitude: event.longitude,
                                    speed: event.speed,
                                    heading: event.heading,
                                    recorded_ago: 'just now',
                                };
                            }

                            // Update marker
                            this.addOrUpdateMarker({
                                vehicle_id: vehicleId,
                                vehicle_name: event.vehicle_name,
                                customer: this.vehicles[index]?.customer ?? '',
                                reference: this.vehicles[index]?.reference ?? '',
                                latitude: event.latitude,
                                longitude: event.longitude,
                                speed: event.speed,
                                recorded_ago: 'just now',
                            });
                        });
                },

                focusVehicle(vehicle) {
                    if (this.markers[vehicle.vehicle_id]) {
                        this.map.setView([vehicle.latitude, vehicle.longitude], 15);
                        this.markers[vehicle.vehicle_id].openPopup();
                    }
                },

                async loadHistory(vehicle) {
                    try {
                        const res = await fetch(`/api/v1/gps/vehicles/${vehicle.vehicle_id}/history`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        const coords = data.locations?.map(l => [l.latitude, l.longitude]) ?? [];

                        if (coords.length < 2) return;

                        // Remove existing polyline
                        if (this.polylines[vehicle.vehicle_id]) {
                            this.map.removeLayer(this.polylines[vehicle.vehicle_id]);
                        }

                        // Draw route
                        const polyline = L.polyline(coords, {
                            color: '#4f46e5',
                            weight: 3,
                            opacity: 0.8,
                        }).addTo(this.map);

                        this.polylines[vehicle.vehicle_id] = polyline;
                        this.map.fitBounds(polyline.getBounds(), { padding: [30, 30] });
                    } catch (e) {
                        console.error('Failed to load history:', e);
                    }
                }
            };
        }
    </script>
@endpush