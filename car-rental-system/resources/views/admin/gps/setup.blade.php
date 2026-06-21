@extends('layouts.admin')

@php
    $locale = app()->getLocale();
    if (!function_exists('gpsT')) {
        function gpsT($en, $fa, $ps)
        {
            $l = app()->getLocale();
            if ($l === 'fa')
                return $fa;
            if ($l === 'ps')
                return $ps;
            return $en;
        }
    }
@endphp

@section('page-title', gpsT('GPS Tracker Setup', 'راه‌اندازی ردیاب GPS', 'د GPS ردیاب تنظیم'))
@section('breadcrumb')
    <a href="{{ route('admin.gps.index') }}" class="hover:text-gray-700">
        {{ gpsT('GPS Tracking', 'ردیابی GPS', 'GPS ردیابی') }}
    </a>
    <span>/</span>
    <span class="text-gray-900 font-medium">
        {{ gpsT('Setup —', 'راه‌اندازی —', 'تنظیم —') }}
        {{ $vehicle->brand }} {{ $vehicle->model }}
    </span>
@endsection

@section('content')
    <div class="max-w-5xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                {{ gpsT('Set Up GPS Tracker —', 'راه‌اندازی ردیاب GPS —', 'د GPS ردیاب تنظیم —') }}
                {{ $vehicle->brand }} {{ $vehicle->model }}
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ gpsT(
        'Configure a Samsung phone with Traccar Client to track this vehicle',
        'یک گوشی سامسونگ با Traccar Client را برای ردیابی این موتر پیکربندی کنید',
        'د دې موټر د ردیابي لپاره یو سامسونګ ګوشی د Traccar Client سره تنظیم کړئ'
    ) }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ─── Left: Device ID Config ──────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full
                                 flex items-center justify-center text-sm font-bold">
                        1
                    </span>
                    {{ gpsT('Configure Tracker App', 'پیکربندی برنامه ردیاب', 'د ردیاب اپلیکیشن تنظیم') }}
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
                            {{ gpsT('Device Identifier', 'شناسه دستگاه', 'د وسیلې پیژندنه') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="traccar_device_id"
                            value="{{ old('traccar_device_id', $vehicle->traccar_device_id ?? $vehicle->license_plate) }}"
                            placeholder="{{ $vehicle->license_plate }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                      font-mono text-sm focus:ring-2 focus:ring-blue-500
                                      focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1">
                            {{ gpsT(
        'Must exactly match the Device Identifier in the Traccar Client app. We suggest using the license plate:',
        'باید دقیقاً با شناسه دستگاه در برنامه Traccar Client مطابقت داشته باشد. پیشنهاد می‌شود از شماره پلیت استفاده کنید:',
        'باید دقیقاً د Traccar Client اپلیکیشن کې د وسیلې پیژندنې سره مطابقت ولري. وړاندیز کیږي چې د پلیټ شمیره وکاروئ:'
    ) }}
                            <strong class="text-gray-600">{{ $vehicle->license_plate }}</strong>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ gpsT('Device Name', 'نام دستگاه', 'د وسیلې نوم') }}
                        </label>
                        <input type="text" name="traccar_device_name"
                            value="{{ old('traccar_device_name', $vehicle->traccar_device_name ?? $vehicle->brand . ' ' . $vehicle->model) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5
                                      text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold
                                   py-3 rounded-xl transition-colors">
                        {{ gpsT('Save GPS Configuration', 'ذخیره تنظیمات GPS', 'د GPS تنظیمات خوندي کول') }}
                    </button>
                </form>

                @if($vehicle->traccar_device_id)
                    <div class="mt-4 bg-green-50 border border-green-200 rounded-xl p-3">
                        <p class="text-sm text-green-700 font-medium">
                            ✅ {{ gpsT('Tracker configured', 'ردیاب پیکربندی شده', 'ردیاب تنظیم شوی') }}
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            {{ gpsT('Device ID:', 'شناسه دستگاه:', 'د وسیلې پیژندنه:') }}
                            <code>{{ $vehicle->traccar_device_id }}</code>
                        </p>
                        @if($vehicle->last_seen_at)
                            <p class="text-xs text-green-600">
                                {{ gpsT('Last seen:', 'آخرین بار دیده شده:', 'وروستی لیدل شوی:') }}
                                {{ $vehicle->last_seen_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.gps.remove-device', $vehicle) }}" class="mt-3"
                        onsubmit="return confirm('{{ gpsT('Remove GPS tracker from this vehicle?', 'ردیاب GPS از این موتر حذف شود؟', 'د دې موټر څخه GPS ردیاب لرې شي؟') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">
                            {{ gpsT('Remove Tracker', 'حذف ردیاب', 'ردیاب لرې کول') }}
                        </button>
                    </form>
                @endif
            </div>

            {{-- ─── Right: Setup Instructions ────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full
                                 flex items-center justify-center text-sm font-bold">
                        2
                    </span>
                    {{ gpsT('Install Traccar on Samsung Phone', 'نصب Traccar روی گوشی سامسونگ', 'په سامسونګ ګوشی کې د Traccar نصبول') }}
                </h3>

                <div class="space-y-5">

                    {{-- Step 1 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                {{ gpsT('Install Traccar Client on Samsung phone', 'نصب Traccar Client روی گوشی سامسونگ', 'په سامسونګ ګوشی کې د Traccar Client نصبول') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ gpsT('Open Google Play Store → Search for', 'Google Play Store را باز کنید ← جستجو کنید', 'Google Play Store خلاص کړئ ← لټون وکړئ') }}
                                <strong>"Traccar Client"</strong>
                                {{ gpsT('→ Install (completely free)', '← نصب کنید (کاملاً رایگان)', '← نصب کړئ (په بشپړ ډول وړیا)') }}
                            </p>
                            <div class="mt-2 bg-gray-50 rounded-lg px-3 py-2 text-xs font-mono text-gray-600">
                                App: Traccar Client<br>
                                Developer: Traccar<br>
                                {{ gpsT('Cost: Free', 'قیمت: رایگان', 'لګښت: وړیا') }}
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                {{ gpsT('Configure the Traccar Client app', 'پیکربندی برنامه Traccar Client', 'د Traccar Client اپلیکیشن تنظیم') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ gpsT('Open the app and enter these settings:', 'برنامه را باز کنید و این تنظیمات را وارد کنید:', 'اپلیکیشن خلاص کړئ او دا تنظیمات ولیکئ:') }}
                            </p>
                            <div class="mt-2 bg-gray-800 rounded-lg p-3 text-xs font-mono space-y-1.5">
                                <p>
                                    <span class="text-gray-400">
                                        {{ gpsT('Device Identifier:', 'شناسه دستگاه:', 'د وسیلې پیژندنه:') }}
                                    </span>
                                    <span class="text-green-400">
                                        {{ $vehicle->traccar_device_id ?? $vehicle->license_plate }}
                                    </span>
                                </p>
                                <p>
                                    <span class="text-gray-400">
                                        {{ gpsT('Server URL:', 'آدرس سرور:', 'د سرور پته:') }}
                                    </span>
                                    <span class="text-blue-400">{{ config('traccar.url') }}/</span>
                                </p>
                                <p>
                                    <span class="text-gray-400">
                                        {{ gpsT('Frequency:', 'فرکانس:', 'مهالویش:') }}
                                    </span>
                                    <span class="text-yellow-400">
                                        300 {{ gpsT('seconds (5 min)', 'ثانیه (۵ دقیقه)', 'ثانیې (۵ دقیقې)') }}
                                    </span>
                                </p>
                                <p>
                                    <span class="text-gray-400">
                                        {{ gpsT('Distance:', 'فاصله:', 'واټن:') }}
                                    </span>
                                    <span class="text-yellow-400">0 {{ gpsT('meters', 'متر', 'متره') }}</span>
                                </p>
                            </div>
                            <p class="text-xs text-red-600 mt-2 font-medium">
                                ⚠️
                                {{ gpsT('Device Identifier must exactly match:', 'شناسه دستگاه باید دقیقاً مطابقت داشته باشد:', 'د وسیلې پیژندنه باید دقیقاً مطابقت ولري:') }}
                                <strong>{{ $vehicle->traccar_device_id ?? $vehicle->license_plate }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                {{ gpsT('Start the tracker', 'ردیاب را شروع کنید', 'ردیاب پیل کړئ') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ gpsT(
        'Tap the START button in the app. It will send location every 5 minutes.',
        'دکمه START را در برنامه بزنید. هر ۵ دقیقه موقعیت ارسال می‌شود.',
        'په اپلیکیشن کې د START تڼۍ کېکاږئ. هر ۵ دقیقې به موقعیت لیږي.'
    ) }}
                            </p>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">4</div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                {{ gpsT('Place phone in the vehicle', 'گوشی را در موتر قرار دهید', 'ګوشی موټر کې کېږدئ') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ gpsT(
        'Put the Samsung phone in the glove box or under the seat. Connect to the car USB charger. Screen can turn off — the app keeps running in the background.',
        'گوشی سامسونگ را در داشبورد یا زیر صندلی قرار دهید. به شارژر USB موتر وصل کنید. صفحه می‌تواند خاموش شود — برنامه در پس‌زمینه ادامه می‌دهد.',
        'سامسونګ ګوشی په ډشبورډ کې یا د سیټ لاندې کېږدئ. د موټر USB چارجر سره وصل کړئ. سکرین کیدای شي بند شي — اپلیکیشن به په شاتنۍ برخه کې روان وي.'
    ) }}
                            </p>
                        </div>
                    </div>

                    {{-- Step 5 --}}
                    <div class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">5</div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">
                                {{ gpsT('Verify on the GPS map', 'تأیید در نقشه GPS', 'په GPS نقشه کې تایید') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ gpsT(
        'After starting the app, wait 5 minutes then check the GPS map. The vehicle should appear as a marker.',
        'پس از شروع برنامه، ۵ دقیقه صبر کنید سپس نقشه GPS را بررسی کنید. موتر باید به عنوان یک نشانگر ظاهر شود.',
        'د اپلیکیشن پیل کولو وروسته، ۵ دقیقې صبر وکړئ بیا GPS نقشه وګورئ. موټر باید د یوې نښې په توګه ښکاره شي.'
    ) }}
                            </p>
                            <a href="{{ route('admin.gps.index') }}"
                                class="inline-block mt-2 text-sm text-blue-600 font-medium hover:underline">
                                {{ gpsT('View GPS Map →', 'مشاهده نقشه GPS ←', 'GPS نقشه ولیدئ ←') }}
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Requirements --}}
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-yellow-800 mb-2">
                        📋
                        {{ gpsT('Requirements for GPS tracking', 'پیش‌نیازهای ردیابی GPS', 'د GPS ردیابي لپاره اړتیاوې') }}
                    </p>
                    <ul class="text-xs text-yellow-700 space-y-1">
                        <li>✓
                            {{ gpsT('Samsung phone with Android 6.0 or higher', 'گوشی سامسونگ با Android 6.0 یا بالاتر', 'سامسونګ ګوشی د Android 6.0 یا لوړ سره') }}
                        </li>
                        <li>✓
                            {{ gpsT('Active SIM card with mobile data (any Afghan carrier)', 'سیم‌کارت فعال با اینترنت (هر اپراتور افغانی)', 'فعال سیم کارت د موبایل ډیټا سره (هر افغان آپریټر)') }}
                        </li>
                        <li>✓
                            {{ gpsT('Location permission granted to Traccar Client app', 'مجوز موقعیت برای برنامه Traccar Client', 'د Traccar Client اپلیکیشن ته د موقعیت اجازه ورکړل شوې') }}
                        </li>
                        <li>✓
                            {{ gpsT('Battery optimization disabled for Traccar Client', 'بهینه‌سازی باتری برای Traccar Client غیرفعال شده باشد', 'د Traccar Client لپاره د بیټرۍ غوره توب غیرفعال شوی') }}
                        </li>
                        <li>✓
                            {{ gpsT('Phone connected to car charger for continuous tracking', 'گوشی به شارژر موتر وصل باشد', 'ګوشی د موټر چارجر سره وصل وي د دوامداره ردیابۍ لپاره') }}
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection