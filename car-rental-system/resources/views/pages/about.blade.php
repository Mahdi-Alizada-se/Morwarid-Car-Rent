@extends('layouts.app')

@section('title', __('common.about_title'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                {{ __('common.about_title') }}
            </h1>
            <p class="text-lg text-gray-500">{{ __('common.about_subtitle') }}</p>
        </div>

        {{-- Story --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('common.our_story') }}</h2>
            <p class="text-gray-600 leading-relaxed mb-4">{{ __('common.our_story_p1') }}</p>
            <p class="text-gray-600 leading-relaxed">{{ __('common.our_story_p2') }}</p>
        </div>

        {{-- Why Choose Us --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('common.why_choose_us') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background-color: #EEF2FF;">
                        <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('common.well_maintained_fleet') }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ __('common.well_maintained_desc') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background-color: #EEF2FF;">
                        <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3
                                  2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11
                                  0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('common.affordable_prices') }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ __('common.affordable_desc') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background-color: #EEF2FF;">
                        <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('common.support_247') }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ __('common.support_desc') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background-color: #EEF2FF;">
                        <svg class="w-5 h-5" style="color: #4F46E5;" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8
                                  8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('common.clear_address') }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ __('common.clear_address_desc') }}</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- CTA --}}
        <div class="text-center">
            <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-semibold
                      rounded-xl transition-colors" style="background-color: #4F46E5;">
                {{ __('common.browse_our_vehicles') }} →
            </a>
        </div>

    </div>
@endsection