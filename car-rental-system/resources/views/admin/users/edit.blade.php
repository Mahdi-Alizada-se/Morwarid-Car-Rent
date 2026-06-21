@extends('layouts.admin')

@section('page-title', __('common.nav_users'))
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">
        {{ app()->getLocale() === 'fa' ? 'کاربران' : (app()->getLocale() === 'ps' ? 'کاروونکي' : 'Users') }}
    </a>
    <span>/</span>
    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $user->name }}</span>
@endsection

@section('content')
@php
    if (!function_exists('uT')) {
        function uT($en, $fa, $ps) {
            $l = app()->getLocale();
            if ($l === 'fa') return $fa;
            if ($l === 'ps') return $ps;
            return $en;
        }
    }
@endphp

<div class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ uT('Edit Customer', 'ویرایش مشتری', 'پیرودونکی سمول') }}
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            {{ uT('Changes apply immediately — no approval needed', 'تغییرات بلافاصله اعمال می‌شود — نیازی به تأیید نیست', 'بدلونونه سمدلاسه پلي کیږي — تایید ته اړتیا نشته') }}
        </p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-3">
            @foreach($errors->all() as $error)
                <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">

        @if($user->driver_license_image)
            <div class="mb-5">
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-2">
                    {{ uT('Current License Photo', 'عکس فعلی گواهینامه', 'اوسنی د جواز عکس') }}
                </p>
                <img src="{{ asset('storage/' . $user->driver_license_image) }}"
                     class="w-40 h-28 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    {{ uT('Full Name', 'نام کامل', 'بشپړ نوم') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                              dark:text-gray-100 rounded-xl px-4 py-2.5
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    {{ uT('Email', 'ایمیل', 'بریښنالیک') }} <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                              dark:text-gray-100 rounded-xl px-4 py-2.5
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    {{ uT('Phone', 'تلفن', 'تلیفون') }}
                </label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr"
                       class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                              dark:text-gray-100 rounded-xl px-4 py-2.5
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    {{ uT('License Number', 'شماره گواهینامه', 'د جواز شمیره') }}
                </label>
                <input type="text" name="driver_license_number" value="{{ old('driver_license_number', $user->driver_license_number) }}"
                       class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                              dark:text-gray-100 rounded-xl px-4 py-2.5
                              focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    {{ uT('Replace License Photo', 'جایگزینی عکس گواهینامه', 'د جواز عکس بدلول') }}
                </label>
                <input type="file" name="driver_license_image" accept="image/*"
                       class="block w-full text-sm text-gray-500 dark:text-gray-400
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/40
                              file:text-indigo-700 dark:file:text-indigo-300
                              hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/60">
            </div>

            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2.5">
                <input type="checkbox" id="driver_license_verified" name="driver_license_verified" value="1"
                       {{ old('driver_license_verified', $user->driver_license_verified) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600">
                <label for="driver_license_verified" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    {{ uT('License Verified', 'گواهینامه تأیید شده', 'جواز تایید شوی') }}
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold
                               rounded-xl hover:bg-indigo-700 transition-colors">
                    {{ uT('Save Changes', 'ذخیره تغییرات', 'بدلونونه خوندي کول') }}
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="px-6 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300
                          border border-gray-200 dark:border-gray-700 rounded-xl
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    {{ uT('Cancel', 'لغو', 'لغول') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection