@extends('layouts.app')

@section('title', __('common.contact_title'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('common.contact_title') }}</h1>
            <p class="text-lg text-gray-500">{{ __('common.contact_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Contact Info --}}
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">{{ __('common.get_in_touch') }}</h2>
                    <div class="space-y-4">

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background-color: #EEF2FF;">
                                <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827
                                                  0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('common.address') }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ __('common.kabul_afghanistan') }}
                                </p>
                                <a href="https://maps.google.com/?q=Dasht-e-Barchi+Kabul" target="_blank"
                                    class="text-xs mt-1 inline-block" style="color: #4F46E5;">
                                    {{ __('common.view_on_maps') }} →
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background-color: #EEF2FF;">
                                <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1
                                                  1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516
                                                  5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0
                                                  01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('common.phone') }}</p>
                                <p class="text-sm text-gray-500 mt-0.5" dir="ltr">+93 730 751 894</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ __('common.available_247') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background-color: #EEF2FF;">
                                <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0
                                                  002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('common.email') }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">info@carrental.com</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background-color: #EEF2FF;">
                                <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('common.working_hours') }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">{{ __('common.sat_to_thu') }}</p>
                                <p class="text-sm text-gray-500">{{ __('common.hours_8_to_8') }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Quick Contact --}}
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">{{ __('common.quick_contact') }}</h2>
                    <div class="space-y-3">

                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-center">
                            <p class="text-sm text-indigo-700 font-medium mb-2">
                                {{ __('common.chat_with_support') }}
                            </p>
                            @auth
                                @if(auth()->user()->isCustomer())
                                    <p class="text-xs text-indigo-500">
                                        {{ __('common.use_chat_button') }}
                                    </p>
                                @endif
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2 text-white
                                                              text-sm font-medium rounded-lg"
                                    style="background-color: #4F46E5;">
                                    {{ __('common.register_to_chat') }}
                                </a>
                            @endauth
                        </div>

                        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
                            <p class="text-sm text-green-700 font-medium mb-1">
                                {{ __('common.call_us_now') }}
                            </p>
                            <p class="text-xl font-bold text-green-800" dir="ltr">+93 730 751 894</p>
                            <p class="text-xs text-green-600 mt-1">{{ __('common.available_247') }}</p>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                            <p class="text-sm font-semibold text-gray-900 mb-3">
                                {{ __('common.find_us') }}
                            </p>
                            <iframe src="https://maps.google.com/maps?q=Dasht-e-Barchi,Kabul,Afghanistan&output=embed"
                                class="w-full h-48 rounded-lg border border-gray-200" style="border:0;" allowfullscreen=""
                                loading="lazy">
                            </iframe>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection