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
                        html: `<div style="background:#4f46e5;color:white;padding:4px 8px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.3);"><div style="display:flex;justify-content:center;margin-bottom:12px;">
        <svg width="52" height="52" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#4f46e5" stroke="#4f46e5">
            <path d="M15.7639,4 C16.9002,4 17.939,4.64201 18.4472,5.65836 L19.8297,8.42332 C20.0735,8.32394 20.3168,8.22155 20.5532,8.10538 C21.0471,7.85869 21.6475,8.05894 21.8944,8.55279 C22.1414,9.04676 21.9412,9.64744 21.4472,9.89443 C20.9532,10.1414 20.7265,10.2169 20.7265,10.2169 L21.6833,12.1305 C21.8915,12.5471 22,13.0064 22,13.4721 L22,16 C22,16.8885 21.6137,17.6868 21,18.2361 L21,19.5 C21,20.3284 20.3284,21 19.5,21 C18.6715,21 18,20.3284 18,19.5 L18,19 L5.99998,19 L5.99998,19.5 C5.99998,20.3284 5.3284,21 4.49997,21 C3.67155,21 2.99997,20.3284 2.99997,19.5 L2.99997,18.2361 C2.38623,17.6868 1.99997,16.8885 1.99997,16 L1.99997,13.4721 C1.99997,13.0064 2.10841,12.5471 2.31669,12.1305 L3.2735,10.2169 C3.03141,10.116 2.79108,10.0105 2.55525,9.89567 C2.05878,9.64744 1.85856,9.04676 2.10555,8.55279 C2.35213,8.05962 2.96121,7.86667 3.4517,8.10779 C3.68712,8.22182 3.92811,8.3246 4.17028,8.42332 L5.55276,5.65836 C6.06094,4.64201 7.09973,4 8.23604,4 Z M18.8341,10.9044 C17.1339,11.4406 14.715,12 12,12 C9.28499,12 6.86601,11.4406 5.16583,10.9044 L4.10555,13.0249 C4.03612,13.1638 3.99997,13.3169 3.99997,13.4721 L3.99997,16 C3.99997,16.5523 4.44769,17 4.99997,17 L19,17 C19.5523,17 20,16.5523 20,16 L20,13.4721 C20,13.3169 19.9638,13.1638 19.8944,13.0249 Z M7.49997,13 C8.3284,13 8.99997,13.6716 8.99997,14.5 C8.99997,15.3284 8.3284,16 7.49997,16 C6.67155,16 5.99997,15.3284 5.99997,14.5 C5.99997,13.6716 6.67155,13 7.49997,13 Z M16.5,13 C17.3284,13 18,13.6716 18,14.5 C18,15.3284 17.3284,16 16.5,16 C15.6715,16 15,15.3284 15,14.5 C15,13.6716 15.6715,13 16.5,13 Z M15.7639,6 L8.23604,6 C7.85727,6 7.51101,6.214 7.34162,6.55279 L6.07258,9.09086 C7.61992,9.55498 9.70503,10 12,10 C14.2949,10 16.38,9.55498 17.9274,9.09086 L16.6583,6.55279 C16.4889,6.214 16.1427,6 15.7639,6 Z" fill="#4f46e5"/>
        </svg>
    </div> ${vehicle.vehicle_name}</div>`,
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