@extends('layouts.admin')

@section('page-title', __('common.settings'))
@section('breadcrumb')
    <span class="text-gray-900 dark:text-gray-100 font-medium">
        {{ app()->getLocale() === 'fa' ? 'تنظیمات حساب' : (app()->getLocale() === 'ps' ? 'د حساب تنظیمات' : 'Account Settings') }}
    </span>
@endsection

@section('content')
    @php
        if (!function_exists('sT')) {
            function sT($en, $fa, $ps)
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

    <div class="max-w-xl">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                {{ sT('Account Settings', 'تنظیمات حساب', 'د حساب تنظیمات') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                {{ sT('Update your email and password', 'ایمیل و رمز عبور خود را بروزرسانی کنید', 'خپل بریښنالیک او پاسورډ تازه کړئ') }}
            </p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3
                            text-green-800 dark:text-green-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-3">
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ sT('Full Name', 'نام کامل', 'بشپړ نوم') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                  dark:text-gray-100 rounded-xl px-4 py-2.5
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ sT('Email', 'ایمیل', 'بریښنالیک') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                  dark:text-gray-100 rounded-xl px-4 py-2.5
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <hr class="border-gray-100 dark:border-gray-800">

                <p class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                    ℹ️
                    {{ sT('Leave password fields blank if you do not want to change it.', 'اگر نمی‌خواهید رمز عبور را تغییر دهید، فیلدها را خالی بگذارید.', 'که نه غواړئ پاسورډ بدل کړئ، ساحې تشې پریږدئ.') }}
                </p>

                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ sT('Current Password', 'پاسورد فعلی', 'اوسنی پاسورډ') }}
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="current_password" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                      dark:text-gray-100 rounded-xl px-4 py-2.5 pr-12
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0
                                      8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12
                                      19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ sT('New Password', 'پاسورد جدید', 'نوی پاسورډ') }}
                    </label>
                    <input type="password" name="password" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                  dark:text-gray-100 rounded-xl px-4 py-2.5
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        {{ sT('Confirm New Password', 'تأیید پاسورد جدید', 'د نوي پاسورډ تایید') }}
                    </label>
                    <input type="password" name="password_confirmation" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                  dark:text-gray-100 rounded-xl px-4 py-2.5
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold
                               rounded-xl hover:bg-indigo-700 transition-colors">
                    {{ sT('Save Changes', 'ذخیره تغییرات', 'بدلونونه خوندي کول') }}
                </button>
            </form>
        </div>
    </div>
@endsection