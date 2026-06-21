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
@php
    if (!function_exists('vehT')) {
        function vehT($en, $fa, $ps) {
            $l = app()->getLocale();
            if ($l === 'fa') return $fa;
            if ($l === 'ps') return $ps;
            return $en;
        }
    }

    $transLabel = match($vehicle->transmission) {
        'automatic' => vehT('Automatic', 'اتومات', 'اتومات'),
        'manual'    => vehT('Manual', 'دستی', 'لاسي'),
        default     => ucfirst($vehicle->transmission),
    };

    $fuelLabel = match($vehicle->fuel_type) {
        'petrol'   => vehT('Petrol', 'پطرول', 'پطرول'),
        'diesel'   => vehT('Diesel', 'دیزل', 'ډیزل'),
        'electric' => vehT('Electric', 'برقی', 'بریښنایی'),
        'hybrid'   => vehT('Hybrid', 'هیبرید', 'هایبرید'),
        default    => ucfirst($vehicle->fuel_type),
    };

    $statusLabel = match($vehicle->status) {
        'available'   => vehT('Available', 'موجود', 'شتون لري'),
        'booked'      => vehT('Booked', 'بکینگ شده', 'بکینګ شوی'),
        'active'      => vehT('Active', 'فعال', 'فعال'),
        'maintenance' => vehT('Maintenance', 'تعمیر', 'ترمیم'),
        default       => ucfirst($vehicle->status),
    };
@endphp

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
                            'available'   => 'bg-green-50 text-green-700',
                            'booked'      => 'bg-orange-50 text-orange-700',
                            'active'      => 'bg-blue-50 text-blue-700',
                            'maintenance' => 'bg-gray-100 text-gray-600',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                 {{ $statusColors[$vehicle->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">
                            {{ vehT('Category', 'دسته‌بندی', 'کټاګورۍ') }}
                        </p>
                        <p class="font-medium text-gray-900">{{ $vehicle->category?->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">
                            {{ vehT('License Plate', 'پلیت', 'پلیټ') }}
                        </p>
                        <p class="font-mono font-bold text-gray-900">{{ $vehicle->license_plate }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">
                            {{ vehT('Transmission', 'گیربکس', 'ګیربکس') }}
                        </p>
                        <p class="font-medium text-gray-900">{{ $transLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">
                            {{ vehT('Fuel Type', 'نوع سوخت', 'د تیلو ډول') }}
                        </p>
                        <p class="font-medium text-gray-900">{{ $fuelLabel }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-5">
            <a href="{{ route('admin.vehicles.edit', $vehicle) }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold
                      rounded-lg hover:bg-indigo-700 transition-colors">
                {{ vehT('Edit Vehicle', 'ویرایش موتر', 'موټر سمول') }}
            </a>
        </div>
    </div>

    {{-- ─── GPS Tracker Setup ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            {{ vehT('GPS Tracker Setup', 'تنظیم ردیاب GPS', 'GPS ردیاب تنظیم') }}
        </h3>

        @if($vehicle->tracker_token)

            {{-- Tracker URL --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <p class="text-xs text-gray-500 mb-2">
                    {{ vehT('Samsung phone tracker URL:', 'لینک ردیاب گوشی سامسونگ:', 'د سامسونګ ګوشي ردیاب لینک:') }}
                </p>
                <div class="flex items-center gap-2">
                    <code class="text-xs bg-white border border-gray-200 rounded-lg px-3 py-2
                                 flex-1 break-all text-gray-700" dir="ltr">
                        {{ route('gps.tracker', [$vehicle->id, $vehicle->tracker_token]) }}
                    </code>
                    <button onclick="navigator.clipboard.writeText('{{ route('gps.tracker', [$vehicle->id, $vehicle->tracker_token]) }}'); this.textContent='{{ vehT('Copied!', 'کپی شد!', 'کاپي شو!') }}';"
                            class="text-xs bg-blue-600 text-white px-3 py-2 rounded-lg
                                   flex-shrink-0 hover:bg-blue-700 transition-colors">
                        {{ vehT('Copy', 'کپی', 'کاپي') }}
                    </button>
                </div>
            </div>

            {{-- Setup Instructions --}}
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
                <p class="text-sm font-semibold text-blue-800 mb-2">
                    📱 {{ vehT('Setup instructions for the Samsung phone:', 'دستورالعمل راه‌اندازی برای گوشی سامسونگ:', 'د سامسونګ ګوشي لپاره د تنظیم لارښوونې:') }}
                </p>
                <ol class="space-y-1.5 text-sm text-blue-700">
                    <li>1. {{ vehT('Insert a SIM card with mobile data into the phone', 'یک سیم‌کارت با اینترنت موبایل در گوشی قرار دهید', 'په ګوشي کې د موبایل ډیټا سره سیم کارت ولرئ') }}</li>
                    <li>2. {{ vehT('Open Chrome browser on the Samsung phone', 'مرورگر کروم را در گوشی سامسونگ باز کنید', 'په سامسونګ ګوشي کې Chrome براوزر خلاص کړئ') }}</li>
                    <li>3. {{ vehT('Go to the URL above (or send via WhatsApp)', 'به لینک بالا بروید (یا از طریق واتساپ ارسال کنید)', 'پورتني لینک ته لاړ شئ (یا یې د واټساپ له لارې ولیږئ)') }}</li>
                    <li>4. {{ vehT('Tap Allow when Chrome asks for location permission', 'وقتی کروم اجازه موقعیت می‌خواهد، روی Allow بزنید', 'کله چې Chrome د موقعیت اجازه غواړي، Allow کېکاږئ') }}</li>
                    <li>5. {{ vehT('You will see a dark screen — the phone is now sending GPS every 15 seconds', 'یک صفحه تیره می‌بینید — گوشی اکنون هر ۱۵ ثانیه GPS ارسال می‌کند', 'تاسو به یوه تیاره سکرین وګورئ — ګوشی اوس هر ۱۵ ثانیې GPS لیږي') }}</li>
                    <li>6. {{ vehT('Plug the phone into the car charger and place in the glove box', 'گوشی را به شارژر موتر وصل کرده و در داشبورد قرار دهید', 'ګوشی د موټر چارجر سره وصل کړئ او په ډشبورډ کې یې کېږدئ') }}</li>
                </ol>
            </div>

            {{-- GPS Status --}}
            @if($vehicle->last_seen_at && $vehicle->last_seen_at->gt(now()->subHour()))
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                    <p class="text-sm font-semibold text-green-800 mb-1">
                        ✅ {{ vehT('GPS is active', 'GPS فعال است', 'GPS فعال دی') }}
                    </p>
                    <div class="space-y-1 text-xs text-green-700">
                        <p>{{ vehT('Last update:', 'آخرین بروزرسانی:', 'وروستی تازه کول:') }}
                            <strong>{{ $vehicle->last_seen_at->diffForHumans() }}</strong></p>
                        <p>
                            {{ vehT('Coordinates:', 'مختصات:', 'انډول:') }}
                            <strong>{{ $vehicle->last_latitude }}, {{ $vehicle->last_longitude }}</strong>
                        </p>
                        <p>{{ vehT('Speed:', 'سرعت:', 'سرعت:') }} <strong>{{ $vehicle->last_speed }} km/h</strong></p>
                    </div>
                    <a href="https://maps.google.com/?q={{ $vehicle->last_latitude }},{{ $vehicle->last_longitude }}"
                       target="_blank" class="inline-block mt-2 text-xs text-green-700 underline font-medium">
                        {{ vehT('View on Google Maps →', 'مشاهده در گوگل مپ ←', 'په ګوګل نقشه کې وګورئ ←') }}
                    </a>
                </div>
            @elseif($vehicle->last_seen_at)
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                    <p class="text-sm font-semibold text-yellow-800">
                        ⚠️ {{ vehT('GPS signal lost', 'سیگنال GPS قطع شد', 'GPS سیګنال ورک شو') }}
                    </p>
                    <p class="text-xs text-yellow-700 mt-1">
                        {{ vehT('Last seen:', 'آخرین بار دیده شده:', 'وروستی لیدل شوی:') }} {{ $vehicle->last_seen_at->diffForHumans() }}
                    </p>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                    <p class="text-sm font-semibold text-yellow-800">
                        ⏳ {{ vehT('Waiting for first GPS signal', 'در انتظار اولین سیگنال GPS', 'د لومړي GPS سیګنال تمه') }}
                    </p>
                    <p class="text-xs text-yellow-700 mt-1">
                        {{ vehT('Open the tracker URL on the Samsung phone to start', 'لینک ردیاب را در گوشی سامسونگ باز کنید تا شروع شود', 'د پیل کولو لپاره د سامسونګ ګوشي کې ردیاب لینک خلاص کړئ') }}
                    </p>
                </div>
            @endif

        @else
            <p class="text-sm text-gray-500 mb-4">
                {{ vehT('No tracker URL generated yet. Click the button below to generate one.', 'هنوز لینک ردیابی ایجاد نشده. روی دکمه زیر کلیک کنید.', 'تر اوسه د ردیاب لینک نه دی جوړ شوی. لاندې تڼۍ کېکاږئ.') }}
            </p>
        @endif

        {{-- Generate / Regenerate Button --}}
        <form method="POST" action="{{ route('admin.vehicles.regenerate-token', $vehicle) }}"
              onsubmit="return confirm('{{ $vehicle->tracker_token
                  ? vehT('Regenerate tracker URL? The old URL will stop working.', 'لینک ردیاب دوباره ایجاد شود؟ لینک قبلی کار نخواهد کرد.', 'د ردیاب لینک بیا جوړ شي؟ زوړ لینک به کار ونکړي.')
                  : vehT('Generate a tracker URL for this vehicle?', 'لینک ردیاب برای این موتر ایجاد شود؟', 'د دې موټر لپاره د ردیاب لینک جوړ شي؟') }}')">
            @csrf
            <button type="submit" class="text-sm bg-gray-800 text-white px-4 py-2 rounded-lg
                                   hover:bg-gray-700 transition-colors">
                {{ $vehicle->tracker_token
                    ? '🔄 ' . vehT('Regenerate Tracker URL', 'بازسازی لینک ردیاب', 'د ردیاب لینک بیا جوړول')
                    : '🔑 ' . vehT('Generate Tracker URL', 'ایجاد لینک ردیاب', 'د ردیاب لینک جوړول') }}
            </button>
        </form>

    </div>

</div>
@endsection