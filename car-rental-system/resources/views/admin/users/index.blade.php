@extends('layouts.admin')

@section('page-title', __('common.nav_users'))
@section('breadcrumb')
    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ __('common.customers') }}</span>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('users.all_customers') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ __('users.manage_customers') }}</p>
            </div>
        </div>

        {{-- Success Alert --}}
        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3
                            text-green-800 dark:text-green-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 flex-wrap">

                <div class="flex-1 min-w-48 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('users.search_placeholder') }}" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700
                                  dark:bg-gray-800 dark:text-gray-100 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <select name="license_status" class="text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100
                               rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">{{ __('users.all_license_statuses') }}</option>
                    <option value="verified" {{ request('license_status') === 'verified' ? 'selected' : '' }}>
                        ✓ {{ __('users.verified') }}
                    </option>
                    <option value="pending" {{ request('license_status') === 'pending' ? 'selected' : '' }}>
                        ⏳ {{ __('users.pending_review') }}
                    </option>
                    <option value="missing" {{ request('license_status') === 'missing' ? 'selected' : '' }}>
                        ✗ {{ __('users.missing') }}
                    </option>
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium
                               rounded-lg hover:bg-indigo-700 transition-colors">
                    {{ __('common.filter') }}
                </button>

                @if(request()->hasAny(['search', 'license_status']))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700
                                  rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        {{ __('common.clear') }}
                    </a>
                @endif

            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <th class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ __('common.customer') }}
                            </th>
                            <th
                                class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 hidden md:table-cell">
                                {{ __('common.phone') }}
                            </th>
                            <th
                                class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 hidden lg:table-cell">
                                {{ __('users.license_no') }}
                            </th>
                            <th class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ __('users.license_status') }}
                            </th>
                            <th
                                class="text-left font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 hidden md:table-cell">
                                {{ __('users.joined') }}
                            </th>
                            <th class="text-right font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ __('common.nav_bookings') }}
                            </th>
                            <th class="text-right font-semibold text-gray-600 dark:text-gray-300 px-4 py-3">
                                {{ __('vehicles.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">

                                {{-- Customer --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center
                                                        justify-center text-indigo-700 dark:text-indigo-300 font-semibold text-sm flex-shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Phone --}}
                                <td class="px-4 py-3 hidden md:table-cell text-gray-600 dark:text-gray-300" dir="ltr">
                                    {{ $user->phone ?? '—' }}
                                </td>

                                {{-- License Number --}}
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    @if($user->driver_license_number)
                                        <code
                                            class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded font-mono">
                                                    {{ $user->driver_license_number }}
                                                </code>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- License Status --}}
                                <td class="px-4 py-3">
                                    @if($user->driver_license_verified)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                                             bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                                             text-xs font-semibold rounded-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ __('users.verified') }}
                                        </span>

                                    @elseif($user->driver_license_image)
                                        <div class="space-y-1.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1
                                                                 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                                                                 text-xs font-semibold rounded-full">
                                                ⏳ {{ __('users.pending_review') }}
                                            </span>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.users.license', $user) }}" target="_blank"
                                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                                    {{ __('users.view_license') }} →
                                                </a>
                                                <form method="POST" action="{{ route('admin.users.verify-license', $user) }}"
                                                    onsubmit="return confirm('{{ __('users.verify_confirm', ['name' => addslashes($user->name)]) }}')">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-green-600 text-white text-xs
                                                                           font-semibold rounded-lg hover:bg-green-700
                                                                           transition-colors">
                                                        ✓ {{ __('users.verify') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1
                                                             bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                                             text-xs font-semibold rounded-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            {{ __('users.missing') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Joined --}}
                                <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $user->created_at->translatedFormat('M d, Y') }}
                                </td>

                                {{-- Bookings --}}
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $user->bookings()->count() }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400
                                                  border border-indigo-200 dark:border-indigo-800 rounded-lg
                                                  hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors">
                                        {{ __('common.edit') }}
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7
                                                  20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002
                                                  0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <p class="font-medium">{{ __('users.no_customers') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection