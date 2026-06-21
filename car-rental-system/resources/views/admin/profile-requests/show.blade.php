@extends('layouts.admin')

@section('page-title', __('common.nav_users'))
@section('breadcrumb')
    <a href="{{ route('admin.profile-requests.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">
        {{ app()->getLocale() === 'fa' ? 'درخواست‌های پروفایل' : (app()->getLocale() === 'ps' ? 'د پروفایل غوښتنې' : 'Profile Requests') }}
    </a>
    <span>/</span>
    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $profileChangeRequest->user?->name }}</span>
@endsection

@section('content')
    @php
        if (!function_exists('prT')) {
            function prT($en, $fa, $ps)
            {
                $l = app()->getLocale();
                if ($l === 'fa')
                    return $fa;
                if ($l === 'ps')
                    return $ps;
                return $en;
            }
        }

        $fieldLabels = [
            'name' => prT('Full Name', 'نام کامل', 'بشپړ نوم'),
            'email' => prT('Email', 'ایمیل', 'بریښنالیک'),
            'phone' => prT('Phone', 'تلفن', 'تلیفون'),
            'driver_license_number' => prT('License Number', 'شماره گواهینامه', 'د جواز شمیره'),
            'driver_license_image' => prT('License Photo', 'عکس گواهینامه', 'د جواز عکس'),
        ];

        $statusColors = [
            'pending' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
            'approved' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400',
            'rejected' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
        ];

        $statusLabels = [
            'pending' => prT('Pending', 'در انتظار', 'تمه'),
            'approved' => prT('Approved', 'تأیید شده', 'تایید شوی'),
            'rejected' => prT('Rejected', 'رد شده', 'رد شوی'),
        ];
    @endphp

    <div class="max-w-3xl space-y-6">

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3
                            text-green-800 dark:text-green-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3
                            text-red-800 dark:text-red-300 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center
                                text-indigo-700 dark:text-indigo-300 font-semibold flex-shrink-0">
                        {{ strtoupper(substr($profileChangeRequest->user?->name ?? '?', 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-gray-100">{{ $profileChangeRequest->user?->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $profileChangeRequest->user?->email }}</p>
                    </div>
                </div>
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$profileChangeRequest->status] }}">
                    {{ $statusLabels[$profileChangeRequest->status] }}
                </span>
            </div>

            <p class="text-xs text-gray-400 dark:text-gray-500">
                {{ prT('Submitted', 'ارسال شده', 'لیږل شوی') }}: {{ $profileChangeRequest->created_at->diffForHumans() }}
            </p>

            @if($profileChangeRequest->reviewed_at)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    {{ prT('Reviewed by', 'بررسی شده توسط', 'بیاکتنه شوې د') }}
                    {{ $profileChangeRequest->reviewer?->name }} — {{ $profileChangeRequest->reviewed_at->diffForHumans() }}
                </p>
                @if($profileChangeRequest->admin_notes)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
                        {{ prT('Notes', 'یادداشت', 'یادښتونه') }}: {{ $profileChangeRequest->admin_notes }}
                    </p>
                @endif
            @endif
        </div>

        {{-- Field Diff --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-4">
                {{ prT('Requested Changes', 'تغییرات درخواستی', 'غوښتل شوي بدلونونه') }}
            </h3>

            <div class="space-y-4">
                @foreach($profileChangeRequest->changes as $field => $newValue)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            {{ $fieldLabels[$field] ?? $field }}
                        </p>

                        @if($field === 'driver_license_image')
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">
                                        {{ prT('Current', 'فعلی', 'اوسنی') }}
                                    </p>
                                    @if($profileChangeRequest->old_values['driver_license_image'] ?? null)
                                        <img src="{{ asset('storage/' . $profileChangeRequest->old_values['driver_license_image']) }}"
                                            class="w-full h-28 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    @else
                                        <p class="text-sm text-gray-400 dark:text-gray-500">—</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-indigo-500 dark:text-indigo-400 mb-1">
                                        {{ prT('Requested', 'درخواستی', 'غوښتل شوی') }}
                                    </p>
                                    <img src="{{ asset('storage/' . $newValue) }}"
                                        class="w-full h-28 object-cover rounded-lg border-2 border-indigo-300 dark:border-indigo-700">
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">
                                        {{ prT('Current', 'فعلی', 'اوسنی') }}
                                    </p>
                                    <p class="text-gray-700 dark:text-gray-300" dir="{{ $field === 'phone' ? 'ltr' : 'auto' }}">
                                        {{ $profileChangeRequest->old_values[$field] ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-indigo-500 dark:text-indigo-400 mb-1">
                                        {{ prT('Requested', 'درخواستی', 'غوښتل شوی') }}
                                    </p>
                                    <p class="font-semibold text-indigo-700 dark:text-indigo-300"
                                        dir="{{ $field === 'phone' ? 'ltr' : 'auto' }}">
                                        {{ $newValue }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        @if($profileChangeRequest->isPending())
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-bold text-gray-900 dark:text-gray-100 mb-4">
                    {{ prT('Review Decision', 'تصمیم بررسی', 'د بیاکتنې پریکړه') }}
                </h3>

                <div class="flex flex-col sm:flex-row gap-3">

                    {{-- Approve --}}
                    <form method="POST" action="{{ route('admin.profile-requests.approve', $profileChangeRequest) }}"
                        class="flex-1"
                        onsubmit="return confirm('{{ prT('Approve these changes? They will be applied immediately.', 'این تغییرات تأیید شود؟ بلافاصله اعمال خواهد شد.', 'دا بدلونونه تایید شي؟ سمدلاسه به پلي شي.') }}')">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-green-600 text-white text-sm font-semibold
                                           rounded-xl hover:bg-green-700 transition-colors">
                            ✓ {{ prT('Approve', 'تأیید', 'تایید') }}
                        </button>
                    </form>

                    {{-- Reject --}}
                    <div class="flex-1" x-data="{ showReject: false, reason: '' }">
                        <button @click="showReject = !showReject" type="button" class="w-full py-2.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                           border border-red-200 dark:border-red-800 text-sm font-semibold
                                           rounded-xl hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                            ✗ {{ prT('Reject', 'رد کردن', 'رد کول') }}
                        </button>

                        <div x-show="showReject" x-cloak class="mt-3">
                            <form method="POST" action="{{ route('admin.profile-requests.reject', $profileChangeRequest) }}">
                                @csrf
                                <textarea name="reason" x-model="reason" rows="2" required
                                    placeholder="{{ prT('Reason for rejection...', 'دلیل رد...', 'د رد کولو دلیل...') }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                                     dark:text-gray-100 rounded-xl px-3 py-2 mb-2
                                                     focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                                <button type="submit" :disabled="!reason.trim()" class="w-full py-2 bg-red-600 text-white text-sm font-semibold
                                                   rounded-xl hover:bg-red-700 transition-colors
                                                   disabled:opacity-40 disabled:cursor-not-allowed">
                                    {{ prT('Confirm Rejection', 'تأیید رد', 'د رد کولو تایید') }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        @endif

    </div>
@endsection