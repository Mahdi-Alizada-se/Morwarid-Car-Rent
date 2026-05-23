@extends('layouts.admin')

@section('page-title', 'GPS Tracker Setup')
@section('breadcrumb')
    <a href="{{ route('admin.gps.index') }}" class="hover:text-gray-700">GPS Tracking</a>
    <span>/</span>
    <span class="text-gray-900 font-medium">
        Setup — {{ $vehicle->brand }} {{ $vehicle->model }}
    </span>
@endsection

@section('content')
    <div class="max-w-5xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                Set Up GPS Tracker — {{ $vehicle->brand }} {{ $vehicle->model }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Configure a Samsung phone with Traccar Client to track this vehicle
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ─── Left: Device ID Config ─────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full
                                 flex items-center justify-center text-sm font-bold">
                        1
                    </span>
                    Configure Tracker App
                </h3>

                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-3">
                        <p class="text-sm text-green-700 font-medium">✅ {{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-3">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.gps.save-device', $vehicle) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Device Identifier <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="traccar_device_id"
                            value="{{ old('traccar_device_id', $vehicle->traccar_device_id ?? $vehicle->license_plate) }}"
                            placeholder="{{ $vehicle->license_plate }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                      font-mono text-sm focus:ring-2 focus:ring-blue-500
                                      focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1">
                            Must exactly match the Device Identifier in the Traccar Client app.
                            We suggest using the license plate:
                            <strong class="text-gray-600">{{ $vehicle->license_plate }}</strong>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Device Name
                        </label>
                        <input type="text" name="traccar_device_name"
                            value="{{ old('traccar_device_name', $vehicle->traccar_device_name ?? $vehicle->brand . ' ' . $vehicle->model) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                      text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold
                                   py-3 rounded-xl transition-colors">
                        Save GPS Configuration
                    </button>
                </form>

                @if($vehicle->traccar_device_id)
                    <div class="mt-4 bg-green-50 border border-green-200 rounded-xl p-3">
                        <p class="text-sm text-green-700 font-medium">✅ Tracker configured</p>
                        <p class="text-xs text-green-600 mt-1">
                            Device ID: <code>{{ $vehicle->traccar_device_id }}</code>
                        </p>
                        @if($vehicle->last_seen_at)
                            <p class="text-xs text-green-600">
                                Last seen: {{ $vehicle->last_seen_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.gps.remove-device', $vehicle) }}" class="mt-3"
                        onsubmit="return confirm('Remove GPS tracker from this vehicle?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">
                            Remove Tracker
                        </button>
                    </form>
                @endif
            </div>

            {{-- ─── Right: Setup Instructions ──────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full
                                 flex items-center justify-center text-sm font-bold">
                        2
                    </span>
                    Install Traccar on Samsung Phone
                </h3>

                <div class="space-y-5">

                    {{-- Step 1 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            1
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                Install Traccar Client on Samsung phone
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Open Google Play Store → Search for
                                <strong>"Traccar Client"</strong> → Install (completely free)
                            </p>
                            <div class="mt-2 bg-gray-50 rounded-lg px-3 py-2 text-xs font-mono text-gray-600">
                                App: Traccar Client<br>
                                Developer: Traccar<br>
                                Cost: Free
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            2
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                Configure the Traccar Client app
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Open the app and enter these settings:
                            </p>
                            <div class="mt-2 bg-gray-800 rounded-lg p-3 text-xs font-mono space-y-1.5">
                                <p>
                                    <span class="text-gray-400">Device Identifier:</span>
                                    <span class="text-green-400">
                                        {{ $vehicle->traccar_device_id ?? $vehicle->license_plate }}
                                    </span>
                                </p>
                                <p>
                                    <span class="text-gray-400">Server URL:</span>
                                    <span class="text-blue-400">{{ config('traccar.url') }}/</span>
                                </p>
                                <p>
                                    <span class="text-gray-400">Frequency:</span>
                                    <span class="text-yellow-400">300 seconds (5 min)</span>
                                </p>
                                <p>
                                    <span class="text-gray-400">Distance:</span>
                                    <span class="text-yellow-400">0 meters</span>
                                </p>
                            </div>
                            <p class="text-xs text-red-600 mt-2 font-medium">
                                ⚠️ Device Identifier must exactly match:
                                <strong>{{ $vehicle->traccar_device_id ?? $vehicle->license_plate }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            3
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">Start the tracker</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Tap the <strong>START</strong> button in the app.
                                It will send location every 5 minutes.
                            </p>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            4
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">Place phone in the vehicle</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Put the Samsung phone in the glove box or under the seat.
                                Connect to the car USB charger. Screen can turn off —
                                the app keeps running in the background.
                            </p>
                        </div>
                    </div>

                    {{-- Step 5 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            5
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">Verify on the GPS map</p>
                            <p class="text-xs text-gray-500 mt-1">
                                After starting the app, wait 5 minutes then check the GPS map.
                                The vehicle should appear as a marker.
                            </p>
                            <a href="{{ route('admin.gps.index') }}"
                                class="inline-block mt-2 text-sm text-blue-600 font-medium hover:underline">
                                View GPS Map →
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Requirements --}}
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-yellow-800 mb-2">
                        📋 Requirements for GPS tracking
                    </p>
                    <ul class="text-xs text-yellow-700 space-y-1">
                        <li>✓ Samsung phone with Android 6.0 or higher</li>
                        <li>✓ Active SIM card with mobile data (any Afghan carrier)</li>
                        <li>✓ Location permission granted to Traccar Client app</li>
                        <li>✓ Battery optimization disabled for Traccar Client</li>
                        <li>✓ Phone connected to car charger for continuous tracking</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection