@extends('layouts.app')

@section('title', __('common.how_it_works'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-3">
                {{ __('common.how_it_works') }}
            </h1>
            <p class="text-gray-500 text-lg">{{ __('common.how_it_works_subtitle') }}</p>
        </div>

        {{-- Steps --}}
        <div class="space-y-6">

            {{-- Step 1 --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-start gap-5">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold text-lg">1</span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ __('common.step_1_title') }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ __('common.step_1_desc') }}</p>
                </div>
            </div>

            <div class="flex justify-center">
                <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>

            {{-- Step 2 --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-start gap-5">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold text-lg">2</span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ __('common.step_2_title') }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ __('common.step_2_desc') }}</p>
                </div>
            </div>

            <div class="flex justify-center">
                <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>

            {{-- Step 3 --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-start gap-5">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold text-lg">3</span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ __('common.step_3_title') }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-3">{{ __('common.step_3_desc') }}</p>
                    <div class="space-y-2">
                        <div class="flex items-start gap-2 text-sm">
                            <span class="text-green-600 font-bold flex-shrink-0">💵
                                {{ __('common.nav_payments') === 'پرداخت‌ها' ? 'نقدی' : 'Cash' }}</span>
                            <span class="text-gray-500">{{ __('common.cash_desc') }}</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm">
                            <span class="text-blue-600 font-bold flex-shrink-0">🏦
                                {{ app()->getLocale() === 'fa' ? 'انتقال بانکی' : 'Bank Transfer' }}</span>
                            <span class="text-gray-500">{{ __('common.bank_transfer_desc') }}</span>
                        </div>
                        <div class="flex items-start gap-2 text-sm">
                            <span class="text-purple-600 font-bold flex-shrink-0">💳
                                {{ app()->getLocale() === 'fa' ? 'کارت' : 'Card' }}</span>
                            <span class="text-gray-500">{{ __('common.card_desc') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>

            {{-- Step 4 --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-start gap-5">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold text-lg">4</span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ __('common.step_4_title') }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ __('common.step_4_desc') }}</p>
                </div>
            </div>

            <div class="flex justify-center">
                <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>

            {{-- Step 5 --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-start gap-5">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ __('common.step_5_title') }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ __('common.step_5_desc') }}</p>
                </div>
            </div>

        </div>

        {{-- Info Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-10">
            <div class="bg-indigo-50 rounded-xl p-5 text-center">
                <p class="text-2xl mb-2">📋</p>
                <p class="font-semibold text-gray-900 text-sm">{{ __('common.requirements') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('common.requirements_desc') }}</p>
            </div>
            <div class="bg-indigo-50 rounded-xl p-5 text-center">
                <p class="text-2xl mb-2">⏰</p>
                <p class="font-semibold text-gray-900 text-sm">{{ __('common.working_hours') }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ __('common.hours_8_to_8') }}<br>{{ __('common.sat_to_thu') }}
                </p>
            </div>
            <div class="bg-indigo-50 rounded-xl p-5 text-center">
                <p class="text-2xl mb-2">📞</p>
                <p class="font-semibold text-gray-900 text-sm">{{ __('common.need_help') }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    +93 730 751 894<br>{{ __('common.kabul_afghanistan') }}
                </p>
            </div>
        </div>

        {{-- CTA --}}
        <div class="text-center mt-10">
            <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white
                      font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                {{ __('common.browse_vehicles') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

    </div>
@endsection