@extends('layouts.admin')

@section('page-title', __('Add Vehicle'))
@section('breadcrumb')
    <a href="{{ route('admin.vehicles.index') }}" class="hover:text-gray-700">{{ __('Vehicles') }}</a>
    <span>/</span>
    <span class="text-gray-900 font-medium">{{ __('Add New') }}</span>
@endsection

@section('content')
    <div class="max-w-5xl" x-data="{
            images: [],
            rules: [{ type: 'daily', base_rate: '', currency: 'AFN', date_from: '', date_to: '', multiplier: '1.00', is_active: true }],
            addRule() { this.rules.push({ type: 'daily', base_rate: '', currency: 'AFN', date_from: '', date_to: '', multiplier: '1.00', is_active: true }) },
            removeRule(i) { this.rules.splice(i, 1) },
            handleImages(event) {
                this.images = [];
                Array.from(event.target.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => this.images.push({ url: e.target.result, name: file.name });
                    reader.readAsDataURL(file);
                });
            }
         }">

        <form method="POST" action="{{ route('admin.vehicles.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-red-800 mb-2">{{ __('Please fix the following errors:') }}</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-sm text-red-700">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ─── Basic Info ──────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-5">{{ __('Basic Information') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Brand') }} <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="brand" value="{{ old('brand') }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('brand') border-red-400 @enderror"
                            placeholder="Toyota">
                        @error('brand') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Model') }} <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="model" value="{{ old('model') }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('model') border-red-400 @enderror"
                            placeholder="Corolla">
                        @error('model') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Year') }} <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="year" value="{{ old('year', date('Y')) }}" min="1990"
                            max="{{ date('Y') + 1 }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('year') border-red-400 @enderror">
                        @error('year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }} <span
                                class="text-red-500">*</span></label>
                        <select name="category_id"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('category_id') border-red-400 @enderror">
                            <option value="">{{ __('Select category') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('License Plate') }} <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="license_plate" value="{{ old('license_plate') }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('license_plate') border-red-400 @enderror"
                            placeholder="KBL-1234">
                        @error('license_plate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Color') }} <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="color" value="{{ old('color') }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('color') border-red-400 @enderror"
                            placeholder="White">
                        @error('color') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Seats') }} <span
                                class="text-red-500">*</span></label>
                        <select name="seats"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @for($i = 1; $i <= 9; $i++)
                                <option value="{{ $i }}" {{ old('seats', 5) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Fuel Type') }} <span
                                class="text-red-500">*</span></label>
                        <select name="fuel_type"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="petrol" {{ old('fuel_type') === 'petrol' ? 'selected' : '' }}>{{ __('Petrol') }}
                            </option>
                            <option value="diesel" {{ old('fuel_type') === 'diesel' ? 'selected' : '' }}>{{ __('Diesel') }}
                            </option>
                            <option value="electric" {{ old('fuel_type') === 'electric' ? 'selected' : '' }}>
                                {{ __('Electric') }}</option>
                            <option value="hybrid" {{ old('fuel_type') === 'hybrid' ? 'selected' : '' }}>{{ __('Hybrid') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Transmission') }} <span
                                class="text-red-500">*</span></label>
                        <select name="transmission"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="automatic" {{ old('transmission') === 'automatic' ? 'selected' : '' }}>
                                {{ __('Automatic') }}</option>
                            <option value="manual" {{ old('transmission') === 'manual' ? 'selected' : '' }}>{{ __('Manual') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }} <span
                                class="text-red-500">*</span></label>
                        <select name="status"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>
                                {{ __('Available') }}</option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>
                                {{ __('Maintenance') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Odometer (km)') }}</label>
                        <input type="number" name="odometer" value="{{ old('odometer', 0) }}" min="0"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Features') }}</label>
                        <input type="text" name="features" value="{{ old('features') }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="{{ __('GPS, Bluetooth, Sunroof, Backup Camera') }}">
                        <p class="text-xs text-gray-400 mt-1">{{ __('Comma-separated list of features') }}</p>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <textarea name="description" rows="3"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="{{ __('Optional vehicle description...') }}">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ─── Images ──────────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-5">{{ __('Images') }}</h3>

                <div class="space-y-4">
                    {{-- Thumbnail --}}
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('Thumbnail (Main Photo)') }}</label>
                        <input type="file" name="thumbnail" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-xs text-gray-400 mt-1">{{ __('Will be resized to 800×600. Max 5MB.') }}</p>
                    </div>

                    {{-- Gallery --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Gallery Images') }}</label>
                        <input type="file" name="images[]" accept="image/*" multiple @change="handleImages($event)"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                        <p class="text-xs text-gray-400 mt-1">{{ __('Up to 10 images. Each max 5MB.') }}</p>

                        {{-- Preview --}}
                        <div class="flex flex-wrap gap-3 mt-3">
                            <template x-for="(img, i) in images" :key="i">
                                <div class="relative">
                                    <img :src="img.url" class="w-20 h-16 object-cover rounded-lg border border-gray-200">
                                    <span
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-gray-800 text-white text-xs rounded-full flex items-center justify-center cursor-pointer"
                                        @click="images.splice(i, 1)">×</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Pricing Rules ───────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold text-gray-900">{{ __('Pricing Rules') }}</h3>
                    <button type="button" @click="addRule()"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Add Rule') }}
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(rule, i) in rules" :key="i">
                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 relative">

                            {{-- Type --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Type') }}</label>
                                <select :name="`pricing_rules[${i}][type]`" x-model="rule.type"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="hourly">{{ __('Hourly') }}</option>
                                    <option value="daily">{{ __('Daily') }}</option>
                                    <option value="weekly">{{ __('Weekly') }}</option>
                                    <option value="monthly">{{ __('Monthly') }}</option>
                                </select>
                            </div>

                            {{-- Rate --}}
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1">{{ __('Base Rate (AFN)') }}</label>
                                <input type="number" :name="`pricing_rules[${i}][base_rate]`" x-model="rule.base_rate"
                                    min="0" step="0.01" placeholder="0.00"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>

                            {{-- Multiplier --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Multiplier') }}</label>
                                <input type="number" :name="`pricing_rules[${i}][multiplier]`" x-model="rule.multiplier"
                                    min="0.01" max="99.99" step="0.01"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>

                            {{-- Date From --}}
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1">{{ __('From (optional)') }}</label>
                                <input type="date" :name="`pricing_rules[${i}][date_from]`" x-model="rule.date_from"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>

                            {{-- Date To --}}
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1">{{ __('To (optional)') }}</label>
                                <input type="date" :name="`pricing_rules[${i}][date_to]`" x-model="rule.date_to"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            </div>

                            {{-- Active + Remove --}}
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Active') }}</label>
                                    <input type="hidden" :name="`pricing_rules[${i}][is_active]`" value="0">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" :name="`pricing_rules[${i}][is_active]`" value="1"
                                            x-model="rule.is_active"
                                            class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                                        <span class="text-sm text-gray-600">{{ __('Yes') }}</span>
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

                            {{-- Hidden currency --}}
                            <input type="hidden" :name="`pricing_rules[${i}][currency]`" value="AFN">
                        </div>
                    </template>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    {{ __('Create Vehicle') }}
                </button>
                <a href="{{ route('admin.vehicles.index') }}"
                    class="px-6 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('Cancel') }}
                </a>
            </div>

        </form>
    </div>
@endsection