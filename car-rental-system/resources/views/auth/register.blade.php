@extends('layouts.app')

@section('title', __('common.register'))

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md" x-data="registerForm()">

            {{-- Logo --}}
            <div class="flex items-center justify-center mx-auto mb-4">
                <div class="rounded-xl px-2 py-1" style="background-color: #4F46E5;">
                    <img src="{{ asset('images/logo.png') }}" alt="Morwarid Car Rental" class="h-12 w-auto object-contain">
                </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 text-center mb-6">
                {{ config('app.name') }}
            </h1>

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

                {{-- Errors --}}
                @if($errors->any())
                    <div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50
                                        border border-red-200 text-red-800 rounded-xl text-sm">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="space-y-5"
                    @submit="localStorage.removeItem('chatbot_session_id'); if (!isFormValid()) { $event.preventDefault(); touchAll(); }">

                    {{-- Full Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.full_name') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="name" name="name" x-model="name" @input="touched.name = true"
                                value="{{ old('name') }}" autofocus autocomplete="name"
                                placeholder="{{ __('common.full_name_placeholder') }}" class="w-full text-sm border rounded-xl px-4 py-2.5 pr-10
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:border-transparent transition-colors"
                                :class="touched.name ? (errors.name ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50') : 'border-gray-200'">
                            <template x-if="touched.name && !errors.name && name.length > 0">
                                <svg class="w-5 h-5 text-green-500 absolute right-3 top-1/2 -translate-y-1/2" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </div>
                        <p class="text-xs text-red-600 mt-1" x-show="touched.name && errors.name" x-text="errors.name"
                            x-cloak></p>
                        @error('name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.email') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email" x-model="email" @input="touched.email = true"
                                value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" class="w-full text-sm border rounded-xl px-4 py-2.5 pr-10
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:border-transparent transition-colors"
                                :class="touched.email ? (errors.email ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50') : 'border-gray-200'">
                            <template x-if="touched.email && !errors.email && email.length > 0">
                                <svg class="w-5 h-5 text-green-500 absolute right-3 top-1/2 -translate-y-1/2" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </div>
                        <p class="text-xs text-red-600 mt-1" x-show="touched.email && errors.email" x-text="errors.email"
                            x-cloak></p>
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.phone') }}
                            <span class="text-gray-400 text-xs font-normal">
                                ({{ __('common.optional') }})
                            </span>
                        </label>
                        <input type="text" id="phone" name="phone" x-model="phone" @input="touched.phone = true"
                            value="{{ old('phone') }}" placeholder="+93 700 000 000" dir="ltr" class="w-full text-sm border rounded-xl px-4 py-2.5
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  focus:border-transparent transition-colors"
                            :class="touched.phone ? (errors.phone ? 'border-red-400 bg-red-50' : (phone.length > 0 ? 'border-green-400 bg-green-50' : 'border-gray-200')) : 'border-gray-200'">
                        <p class="text-xs text-red-600 mt-1" x-show="touched.phone && errors.phone" x-text="errors.phone"
                            x-cloak></p>
                        @error('phone')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Driver License Number --}}
                    <div>
                        <label for="driver_license_number" class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.driver_license_number') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="driver_license_number" name="driver_license_number"
                                x-model="licenseNumber" @input="touched.licenseNumber = true"
                                value="{{ old('driver_license_number') }}" required
                                placeholder="{{ __('common.license_placeholder') }}" class="w-full text-sm border rounded-xl px-4 py-2.5 pr-10
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                :class="touched.licenseNumber ? (errors.licenseNumber ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50') : 'border-gray-200'">
                            <template x-if="touched.licenseNumber && !errors.licenseNumber && licenseNumber.length > 0">
                                <svg class="w-5 h-5 text-green-500 absolute right-3 top-1/2 -translate-y-1/2" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                        </div>
                        <p class="text-xs text-red-600 mt-1" x-show="touched.licenseNumber && errors.licenseNumber"
                            x-text="errors.licenseNumber" x-cloak></p>
                        @error('driver_license_number')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Driver License Photo --}}
                    <div x-data="{ preview: null, fileName: '' }">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.driver_license_photo') }}
                            <span class="text-red-500">*</span>
                        </label>

                        <label class="block border-2 border-dashed border-gray-300 rounded-xl p-4
                                      text-center cursor-pointer hover:border-indigo-400 transition-colors"
                            :class="fileName ? 'border-indigo-400 bg-indigo-50' : ''">

                            <input type="file" name="driver_license_image"
                                accept="image/jpeg,image/png,image/jpg,application/pdf" required class="hidden" @change="
                                       const f = $event.target.files[0];
                                       if (f) {
                                           fileName = f.name;
                                           preview = f.type.startsWith('image/')
                                               ? URL.createObjectURL(f)
                                               : null;
                                       }
                                   ">

                            <template x-if="!fileName">
                                <div>
                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0
                                              011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm text-gray-500">
                                        {{ __('common.click_to_upload_license') }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ __('common.license_file_types') }}
                                    </p>
                                </div>
                            </template>

                            <template x-if="fileName && preview">
                                <div>
                                    <img :src="preview" class="h-28 mx-auto rounded-lg border border-gray-200
                                                        object-cover mb-2">
                                    <p class="text-xs text-indigo-600 font-medium" x-text="fileName"></p>
                                </div>
                            </template>

                            <template x-if="fileName && !preview">
                                <div>
                                    <svg class="w-8 h-8 text-indigo-500 mx-auto mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0
                                              012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0
                                              01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-xs text-indigo-600 font-medium" x-text="fileName"></p>
                                </div>
                            </template>
                        </label>

                        <p class="text-xs text-gray-400 mt-1">
                            {{ __('common.license_photo_help') }}
                        </p>

                        @error('driver_license_image')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.password') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                                x-model="password" @input="touched.password = true" autocomplete="new-password"
                                placeholder="••••••••" class="w-full text-sm border rounded-xl
                                      px-4 py-2.5 pr-12
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:border-transparent transition-colors"
                                :class="touched.password ? (errors.password ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50') : 'border-gray-200'">

                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2
                                           text-gray-400 hover:text-gray-600 transition-colors">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5
                                          12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0
                                          .639C20.577 16.49 16.64 19.5 12 19.5c-4.638
                                          0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338
                                          7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228
                                          6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065
                                          7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3
                                          3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0
                                          0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>

                        {{-- Password strength meter --}}
                        <div class="flex gap-1 mt-1.5" x-show="password.length > 0" x-cloak>
                            <div class="h-1 flex-1 rounded-full transition-colors"
                                :class="passwordStrength() >= 1 ? 'bg-red-400' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors"
                                :class="passwordStrength() >= 2 ? 'bg-orange-400' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors"
                                :class="passwordStrength() >= 3 ? 'bg-yellow-400' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors"
                                :class="passwordStrength() >= 4 ? 'bg-green-500' : 'bg-gray-200'"></div>
                        </div>

                        <p class="text-xs text-red-600 mt-1" x-show="touched.password && errors.password"
                            x-text="errors.password" x-cloak></p>
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div x-data="{ showConfirm: false }">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('common.confirm_password') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation"
                                name="password_confirmation" x-model="passwordConfirmation"
                                @input="touched.passwordConfirmation = true" autocomplete="new-password"
                                placeholder="••••••••" class="w-full text-sm border rounded-xl
                                      px-4 py-2.5 pr-12
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:border-transparent transition-colors"
                                :class="touched.passwordConfirmation ? (errors.passwordConfirmation ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50') : 'border-gray-200'">

                            <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2
                                           text-gray-400 hover:text-gray-600 transition-colors">
                                <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5
                                          12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0
                                          .639C20.577 16.49 16.64 19.5 12 19.5c-4.638
                                          0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338
                                          7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228
                                          6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065
                                          7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3
                                          3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0
                                          0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-red-600 mt-1"
                            x-show="touched.passwordConfirmation && errors.passwordConfirmation"
                            x-text="errors.passwordConfirmation" x-cloak></p>
                    </div>

                    {{-- Avatar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('common.profile_photo') }}
                            <span class="text-gray-400 text-xs">({{ __('common.optional') }})</span>
                        </label>
                        <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                      file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="w-full py-2.5 text-white text-sm font-semibold
                                   rounded-xl transition-colors"
                        :class="isFormValid() ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'">
                        {{ __('common.create_account') }}
                    </button>

                </form>

                {{-- Divider --}}
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-400 font-medium">{{ __('common.or') }}</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                {{-- Social Login --}}
                <div class="space-y-3">
                    <a href="{{ route('social.redirect', 'google') }}" class="flex items-center justify-center gap-3 w-full py-2.5
                              border border-gray-200 rounded-xl text-sm font-medium
                              text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04
                                          2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71
                                          1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43
                                          8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12
                                          1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        {{ __('common.continue_with_google') }}
                    </a>

                    <a href="{{ route('social.redirect', 'facebook') }}" class="flex items-center justify-center gap-3 w-full py-2.5
                              border border-gray-200 rounded-xl text-sm font-medium
                              text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388
                                             10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007
                                             1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491
                                             0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612
                                             23.027 24 18.062 24 12.073z" />
                        </svg>
                        {{ __('common.continue_with_facebook') }}
                    </a>
                </div>

            </div>

            {{-- Login Link --}}
            <p class="text-center text-sm text-gray-500 mt-6">
                {{ __('common.already_have_account') }}
                <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">
                    {{ __('common.login') }}
                </a>
            </p>

        </div>
    </div>

    <script>
        function registerForm() {
            return {
                name: '{{ old('name') }}',
                email: '{{ old('email') }}',
                phone: '{{ old('phone') }}',
                licenseNumber: '{{ old('driver_license_number') }}',
                password: '',
                passwordConfirmation: '',

                touched: {
                    name: false,
                    email: false,
                    phone: false,
                    licenseNumber: false,
                    password: false,
                    passwordConfirmation: false,
                },

                get errors() {
                    const e = {};

                    if (this.name.length > 0) {
                        if (!/^[\p{L}\s]+$/u.test(this.name)) {
                            e.name = 'Name may only contain letters and spaces.';
                        } else if (this.name.trim().length < 3) {
                            e.name = 'Name must be at least 3 characters.';
                        } else if (this.name.length > 32) {
                            e.name = 'Name may not exceed 32 characters.';
                        }
                    } else if (this.touched.name) {
                        e.name = 'Name is required.';
                    }

                    if (this.email.length > 0) {
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
                            e.email = 'Please enter a valid email address.';
                        }
                    } else if (this.touched.email) {
                        e.email = 'Email is required.';
                    }

                    if (this.phone.length > 0 && !/^\+?[0-9\s\-]{7,20}$/.test(this.phone)) {
                        e.phone = 'Please enter a valid phone number.';
                    }

                    if (this.licenseNumber.length > 0) {
                        if (!/^[A-Za-z0-9\-]+$/.test(this.licenseNumber)) {
                            e.licenseNumber = 'Only letters, numbers, and hyphens allowed.';
                        } else if (this.licenseNumber.length < 4) {
                            e.licenseNumber = 'License number must be at least 4 characters.';
                        } else if (this.licenseNumber.length > 30) {
                            e.licenseNumber = 'License number may not exceed 30 characters.';
                        }
                    } else if (this.touched.licenseNumber) {
                        e.licenseNumber = 'License number is required.';
                    }

                    if (this.password.length > 0) {
                        if (this.password.length < 8) {
                            e.password = 'Password must be at least 8 characters.';
                        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(this.password)) {
                            e.password = 'Password needs an uppercase letter, a lowercase letter, and a number.';
                        } else if (this.password.length > 64) {
                            e.password = 'Password may not exceed 64 characters.';
                        }
                    } else if (this.touched.password) {
                        e.password = 'Password is required.';
                    }

                    if (this.passwordConfirmation.length > 0) {
                        if (this.passwordConfirmation !== this.password) {
                            e.passwordConfirmation = 'Passwords do not match.';
                        }
                    } else if (this.touched.passwordConfirmation) {
                        e.passwordConfirmation = 'Please confirm your password.';
                    }

                    return e;
                },

                passwordStrength() {
                    let score = 0;
                    if (this.password.length >= 8) score++;
                    if (/[a-z]/.test(this.password) && /[A-Z]/.test(this.password)) score++;
                    if (/\d/.test(this.password)) score++;
                    if (/[^A-Za-z0-9]/.test(this.password)) score++;
                    return score;
                },

                isFormValid() {
                    return this.name.length > 0
                        && this.email.length > 0
                        && this.licenseNumber.length > 0
                        && this.password.length > 0
                        && this.passwordConfirmation.length > 0
                        && Object.keys(this.errors).length === 0;
                },

                touchAll() {
                    Object.keys(this.touched).forEach(k => this.touched[k] = true);
                },
            };
        }
    </script>
@endsection