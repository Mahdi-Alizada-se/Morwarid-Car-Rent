@extends('layouts.admin')

@section('page-title', __('common.nav_vehicles'))
@section('breadcrumb')
    <a href="{{ route('admin.vehicles.index') }}" class="hover:text-gray-700">{{ __('common.nav_vehicles') }}</a>
    <span>/</span>
    <span class="text-gray-900 font-medium">{{ $vehicle->full_name }}</span>
@endsection

@section('content')
    @php
        if (!function_exists('vehT')) {
            function vehT($en, $fa, $ps)
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

    <div class="max-w-5xl" x-data="{
        images: @js($vehicle->images->map(fn($i) => ['url' => asset('storage/' . $i->path), 'name' => basename($i->path), 'existing' => true])->toArray()),
        rules: @js($vehicle->pricingRules->map(fn($r) => ['type' => $r->type, 'base_rate' => $r->base_rate, 'currency' => $r->currency, 'date_from' => $r->date_from?->format('Y-m-d') ?? '', 'date_to' => $r->date_to?->format('Y-m-d') ?? '', 'multiplier' => $r->multiplier, 'is_active' => (bool) $r->is_active])->toArray()),
        addRule() { this.rules.push({ type: 'daily', base_rate: '', currency: 'AFN', date_from: '', date_to: '', multiplier: '1.00', is_active: true }) },
        removeRule(i) { this.rules.splice(i, 1) },
        handleImages(event) {
            const newImgs = [];
            Array.from(event.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => newImgs.push({ url: e.target.result, name: file.name, existing: false });
                reader.readAsDataURL(file);
            });
            setTimeout(() => this.images = [...this.images.filter(i => i.existing), ...newImgs], 100);
        }
    }">

        <form method="POST" action="{{ route('admin.vehicles.update', $vehicle) }}" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-red-800 mb-2">
                        {{ vehT('Please fix the following errors:', 'لطفاً خطاهای زیر را برطرف کنید:', 'مهرباني وکړئ لاندې غلطۍ سمې کړئ:') }}
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-sm text-red-700">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ─── Basic Info ──────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-5">
                    {{ vehT('Basic Information', 'اطلاعات اساسی', 'بنسټیز معلومات') }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Brand', 'برند', 'برانډ') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('brand') border-red-400 @enderror">
                        @error('brand') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Model', 'مدل', 'ماډل') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="model" value="{{ old('model', $vehicle->model) }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('model') border-red-400 @enderror">
                        @error('model') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Year', 'سال', 'کال') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" min="1990"
                            max="{{ date('Y') + 1 }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Category', 'دسته‌بندی', 'کټاګورۍ') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $vehicle->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('License Plate', 'پلیت', 'پلیټ') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate) }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('license_plate') border-red-400 @enderror">
                        @error('license_plate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Color', 'رنگ', 'رنګ') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="color" value="{{ old('color', $vehicle->color) }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Seats', 'صندلی‌ها', 'سیټونه') }}
                        </label>
                        <select name="seats"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @for($i = 1; $i <= 9; $i++)
                                <option value="{{ $i }}" {{ old('seats', $vehicle->seats) == $i ? 'selected' : '' }}>{{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Fuel Type', 'نوع سوخت', 'د تیلو ډول') }}
                        </label>
                        <select name="fuel_type"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @php
                                $fuelLabels = [
                                    'petrol' => vehT('Petrol', 'پطرول', 'پطرول'),
                                    'diesel' => vehT('Diesel', 'دیزل', 'ډیزل'),
                                    'electric' => vehT('Electric', 'برقی', 'بریښنایی'),
                                    'hybrid' => vehT('Hybrid', 'هیبرید', 'هایبرید'),
                                ];
                            @endphp
                            @foreach($fuelLabels as $fuel => $label)
                                <option value="{{ $fuel }}" {{ old('fuel_type', $vehicle->fuel_type) === $fuel ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Transmission', 'گیربکس', 'ګیربکس') }}
                        </label>
                        <select name="transmission"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="automatic" {{ old('transmission', $vehicle->transmission) === 'automatic' ? 'selected' : '' }}>
                                {{ vehT('Automatic', 'اتومات', 'اتومات') }}
                            </option>
                            <option value="manual" {{ old('transmission', $vehicle->transmission) === 'manual' ? 'selected' : '' }}>
                                {{ vehT('Manual', 'دستی', 'لاسي') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Status', 'وضعیت', 'حالت') }}
                        </label>
                        <select name="status"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="available" {{ old('status', $vehicle->status) === 'available' ? 'selected' : '' }}>
                                {{ vehT('Available', 'موجود', 'شتون لري') }}
                            </option>
                            <option value="maintenance" {{ old('status', $vehicle->status) === 'maintenance' ? 'selected' : '' }}>
                                {{ vehT('Maintenance', 'تعمیر', 'ترمیم') }}
                            </option>
                            <option value="booked" {{ old('status', $vehicle->status) === 'booked' ? 'selected' : '' }}>
                                {{ vehT('Booked', 'بکینگ شده', 'بکینګ شوی') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Odometer (km)', 'کیلومتراژ (کم)', 'اودومیټر (کم)') }}
                        </label>
                        <input type="number" name="odometer" value="{{ old('odometer', $vehicle->odometer) }}" min="0"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Features', 'ویژگی‌ها', 'ځانګړنې') }}
                        </label>
                        <input type="text" name="features"
                            value="{{ old('features', is_array($vehicle->features) ? implode(', ', $vehicle->features) : $vehicle->features) }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="{{ vehT('GPS, Bluetooth, Sunroof', 'GPS، بلوتوث، سان‌روف', 'GPS، بلوتوث، سن‌روف') }}">
                        <p class="text-xs text-gray-400 mt-1">
                            {{ vehT('Comma-separated', 'با کاما جدا شده', 'د کوما سره جلا شوی') }}
                        </p>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ vehT('Description', 'توضیحات', 'تفصیل') }}
                        </label>
                        <textarea name="description" rows="3"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $vehicle->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ─── Images ──────────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-5">
                    {{ vehT('Images', 'تصاویر', 'انځورونه') }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ vehT('Thumbnail', 'عکس اصلی', 'اصلي انځور') }}
                        </label>
                        @if($vehicle->thumbnail)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $vehicle->thumbnail) }}"
                                    class="w-32 h-24 object-cover rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ vehT('Current thumbnail', 'عکس فعلی', 'اوسنی انځور') }}
                                </p>
                            </div>
                        @endif
                        <input type="file" name="thumbnail" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-400 mt-1">
                            {{ vehT('Leave blank to keep current thumbnail.', 'برای حفظ عکس فعلی خالی بگذارید.', 'د اوسني انځور د ساتلو لپاره تش پریږدئ.') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ vehT('Gallery', 'گالری', 'ګالري') }}
                        </label>
                        <div class="flex flex-wrap gap-3 mb-3">
                            <template x-for="(img, i) in images" :key="i">
                                <div class="relative">
                                    <img :src="img.url" class="w-20 h-16 object-cover rounded-lg border border-gray-200">
                                    <span x-show="!img.existing"
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-gray-800 text-white text-xs rounded-full flex items-center justify-center cursor-pointer"
                                        @click="images.splice(i, 1)">×</span>
                                    <span x-show="img.existing"
                                        class="absolute bottom-0 left-0 right-0 text-center text-xs bg-black/50 text-white rounded-b-lg py-0.5">
                                        {{ vehT('Saved', 'ذخیره شده', 'خوندي شوی') }}
                                    </span>
                                </div>
                            </template>
                        </div>
                        <input type="file" name="images[]" accept="image/*" multiple @change="handleImages($event)"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                        <p class="text-xs text-gray-400 mt-1">
                            {{ vehT('Uploading new images will replace all existing gallery images.', 'آپلود تصاویر جدید، همه تصاویر گالری فعلی را جایگزین می‌کند.', 'د نویو انځورونو اپلوډ کول ټول اوسني ګالري انځورونه ځای په ځای کوي.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ─── Pricing Rules ───────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold text-gray-900">
                        {{ vehT('Pricing Rules', 'قوانین قیمت‌گذاری', 'د نرخونو قواعد') }}
                    </h3>
                    <button type="button" @click="addRule()"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ vehT('Add Rule', 'افزودن قانون', 'قاعده زیاتول') }}
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(rule, i) in rules" :key="i">
                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ vehT('Type', 'نوع', 'ډول') }}
                                </label>
                                <select :name="`pricing_rules[${i}][type]`" x-model="rule.type"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="hourly">{{ vehT('Hourly', 'ساعتی', 'ساعتی') }}</option>
                                    <option value="daily">{{ vehT('Daily', 'روزانه', 'ورځنی') }}</option>
                                    <option value="weekly">{{ vehT('Weekly', 'هفتگی', 'اونیز') }}</option>
                                    <option value="monthly">{{ vehT('Monthly', 'ماهانه', 'میاشتنی') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ vehT('Base Rate', 'نرخ پایه', 'بنسټیز نرخ') }}
                                </label>
                                <input type="number" :name="`pricing_rules[${i}][base_rate]`" x-model="rule.base_rate"
                                    min="0" step="0.01"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ vehT('Multiplier', 'ضریب', 'مضرب') }}
                                </label>
                                <input type="number" :name="`pricing_rules[${i}][multiplier]`" x-model="rule.multiplier"
                                    min="0.01" step="0.01"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ vehT('From', 'از', 'له') }}
                                </label>
                                <input type="date" :name="`pricing_rules[${i}][date_from]`" x-model="rule.date_from"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ vehT('To', 'تا', 'تر') }}
                                </label>
                                <input type="date" :name="`pricing_rules[${i}][date_to]`" x-model="rule.date_to"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ vehT('Active', 'فعال', 'فعال') }}
                                    </label>
                                    <input type="hidden" :name="`pricing_rules[${i}][is_active]`" value="0">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" :name="`pricing_rules[${i}][is_active]`" value="1"
                                            x-model="rule.is_active"
                                            class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                                        <span class="text-sm text-gray-600">{{ vehT('Yes', 'بله', 'هو') }}</span>
                                    </label>
                                </div>
                                <button type="button" @click="removeRule(i)" x-show="rules.length > 1"
                                    class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            <input type="hidden" :name="`pricing_rules[${i}][currency]`" value="AFN">
                        </div>
                    </template>
                </div>
            </div>

            {{-- ─── GPS Tracker ────────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    {{ vehT('GPS Tracker', 'ردیاب GPS', 'GPS ردیاب') }}
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
                            <button type="button"
                                onclick="navigator.clipboard.writeText('{{ route('gps.tracker', [$vehicle->id, $vehicle->tracker_token]) }}'); this.textContent='{{ vehT('Copied!', 'کپی شد!', 'کاپي شو!') }}';"
                                class="text-xs bg-blue-600 text-white px-3 py-2 rounded-lg
                                               flex-shrink-0 hover:bg-blue-700 transition-colors">
                                {{ vehT('Copy', 'کپی', 'کاپي') }}
                            </button>
                        </div>
                    </div>

                    {{-- GPS Status --}}
                    @if($vehicle->last_seen_at && $vehicle->last_seen_at->gt(now()->subHour()))
                        <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-4">
                            <p class="text-sm font-semibold text-green-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span>
                                {{ vehT('GPS Active', 'GPS فعال', 'GPS فعال') }}
                            </p>
                            <p class="text-xs text-green-700 mt-1">
                                {{ vehT('Last update:', 'آخرین بروزرسانی:', 'وروستی تازه کول:') }}
                                <strong>{{ $vehicle->last_seen_at->diffForHumans() }}</strong>
                                · {{ $vehicle->last_latitude }}, {{ $vehicle->last_longitude }}
                                · {{ $vehicle->last_speed }} km/h
                            </p>
                            <a href="https://maps.google.com/?q={{ $vehicle->last_latitude }},{{ $vehicle->last_longitude }}"
                                target="_blank" class="text-xs text-green-700 underline mt-1 inline-block">
                                {{ vehT('View on Google Maps →', 'مشاهده در گوگل مپ ←', 'په ګوګل نقشه کې وګورئ ←') }}
                            </a>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4">
                            <p class="text-sm text-yellow-700">
                                ⏳
                                {{ $vehicle->last_seen_at
                        ? vehT('GPS signal lost — last seen ', 'سیگنال GPS قطع شد — آخرین بار دیده شده ', 'GPS سیګنال ورک شو — وروستی لیدل شوی ') . $vehicle->last_seen_at->diffForHumans()
                        : vehT('Waiting for first GPS signal', 'در انتظار اولین سیگنال GPS', 'د لومړي GPS سیګنال تمه') }}
                            </p>
                        </div>
                    @endif

                @else
                    <p class="text-sm text-gray-500 mb-4">
                        {{ vehT('No tracker URL generated yet.', 'هنوز لینک ردیابی ایجاد نشده است.', 'تر اوسه د ردیاب لینک نه دی جوړ شوی.') }}
                    </p>
                @endif

                {{-- Generate / Regenerate --}}
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

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    {{ vehT('Update Vehicle', 'بروزرسانی موتر', 'موټر تازه کول') }}
                </button>
                <a href="{{ route('admin.vehicles.index') }}"
                    class="px-6 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ vehT('Cancel', 'لغو', 'لغول') }}
                </a>
            </div>
        </form>
    </div>
@endsection