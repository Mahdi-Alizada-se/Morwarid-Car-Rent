@extends('layouts.admin')

@section('page-title', __('common.nav_users'))
@section('breadcrumb')
    <span class="text-gray-900 dark:text-gray-100 font-medium">
        {{ app()->getLocale() === 'fa' ? 'درخواست‌های پروفایل' : (app()->getLocale() === 'ps' ? 'د پروفایل غوښتنې' : 'Profile Requests') }}
    </span>
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
    @endphp

    <div class="space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                {{ prT('Profile Change Requests', 'درخواست‌های تغییر پروفایل', 'د پروفایل د بدلون غوښتنې') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                {{ prT('Review and approve customer profile changes', 'بررسی و تأیید تغییرات پروفایل مشتریان', 'د پیرودونکو د پروفایل بدلونونه بیاکتنه او تایید کړئ') }}
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3
                            text-green-800 dark:text-green-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        {{-- Status Tabs --}}
        <div class="flex gap-2">
            @foreach(['pending' => prT('Pending', 'در انتظار', 'تمه'), 'approved' => prT('Approved', 'تأیید شده', 'تایید شوی'), 'rejected' => prT('Rejected', 'رد شده', 'رد شوی'), 'all' => prT('All', 'همه', 'ټول')] as $key => $label)
                <a href="{{ route('admin.profile-requests.index', ['status' => $key]) }}"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                              {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    {{ $label }}
                    @if($key === 'pending' && $pendingCount > 0)
                        <span class="ml-1">({{ $pendingCount }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <th class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ prT('Customer', 'مشتری', 'پیرودونکی') }}
                            </th>
                            <th class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ prT('Fields Changed', 'فیلدهای تغییر یافته', 'بدل شوي فیلډونه') }}
                            </th>
                            <th class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ prT('Status', 'وضعیت', 'حالت') }}
                            </th>
                            <th class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ prT('Submitted', 'ارسال شده', 'لیږل شوی') }}
                            </th>
                            <th class="text-right font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ prT('Actions', 'عملیات', 'کړنې') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($requests as $req)
                            @php
                                $statusColors = [
                                    'pending' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
                                    'approved' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                    'rejected' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $req->user?->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $req->user?->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ implode(', ', array_keys($req->changes)) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                     {{ $statusColors[$req->status] }}">
                                        {{ prT(ucfirst($req->status), $req->status === 'pending' ? 'در انتظار' : ($req->status === 'approved' ? 'تأیید شده' : 'رد شده'), $req->status === 'pending' ? 'تمه' : ($req->status === 'approved' ? 'تایید شوی' : 'رد شوی')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $req->created_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.profile-requests.show', $req) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400
                                                  border border-indigo-200 dark:border-indigo-800 rounded-lg
                                                  hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors">
                                        {{ __('common.view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                    {{ prT('No requests found.', 'درخواستی یافت نشد.', 'هیڅ غوښتنه ونه موندل شوه.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection