@extends('layouts.app')

@section('title', __('profile.my_profile'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('profile.my_profile') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ __('profile.manage_profile') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-4 flex items-start gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/30 border
                            border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-xl text-sm">
                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border
                            border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl text-sm">
                <svg class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Pending Request Banner --}}
        @if($pendingRequest)
            <div
                class="mb-6 bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-300 dark:border-orange-800 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-orange-500 dark:text-orange-400 flex-shrink-0 mt-0.5" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73
                                  0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898
                                  0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <div>
                        <p class="font-bold text-orange-800 dark:text-orange-300">
                            {{ __('profile.pending_request_title') }}
                        </p>
                        <p class="text-sm text-orange-700 dark:text-orange-400 mt-1">
                            {{ __('profile.pending_request_desc') }}
                        </p>
                        <p class="text-xs text-orange-600 dark:text-orange-500 mt-2">
                            {{ __('profile.pending_since') }}: {{ $pendingRequest->created_at->diffForHumans() }}
                        </p>

                        <div
                            class="mt-3 bg-white dark:bg-gray-800 rounded-lg p-3 border border-orange-200 dark:border-orange-800">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-gray-400 dark:text-gray-500">
                                        <th class="text-left font-medium pb-1.5">{{ __('profile.field_label') }}</th>
                                        <th class="text-left font-medium pb-1.5">{{ __('profile.current_value') }}</th>
                                        <th class="text-left font-medium pb-1.5">{{ __('profile.requested_value') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($pendingRequest->changes as $field => $value)
                                        <tr>
                                            <td class="py-1.5 font-medium text-gray-700 dark:text-gray-300">
                                                {{ $field === 'driver_license_image' ? __('profile.license_photo') : __('profile.' . str_replace('driver_license_', 'license_', $field)) }}
                                            </td>
                                            <td class="py-1.5 text-gray-500 dark:text-gray-400">
                                                {{ $field === 'driver_license_image' ? '—' : ($pendingRequest->old_values[$field] ?? '—') }}
                                            </td>
                                            <td class="py-1.5 text-indigo-600 dark:text-indigo-400 font-medium">
                                                {{ $field === 'driver_license_image' ? __('profile.new_license_photo_note') : $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Last Decision Banner --}}
        @if(!$pendingRequest && $lastReviewed)
            <div class="mb-6 {{ $lastReviewed->status === 'approved'
                ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}
                    border rounded-xl p-4">
                <p class="text-sm font-semibold {{ $lastReviewed->status === 'approved'
                ? 'text-green-800 dark:text-green-300'
                : 'text-red-800 dark:text-red-300' }}">
                    {{ $lastReviewed->status === 'approved'
                ? __('profile.last_decision_approved')
                : __('profile.last_decision_rejected') }}
                </p>
                @if($lastReviewed->status === 'rejected' && $lastReviewed->admin_notes)
                    <p class="text-xs text-red-700 dark:text-red-400 mt-1">
                        {{ __('profile.admin_notes') }}: {{ $lastReviewed->admin_notes }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Current Info Card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
            <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('profile.current_info') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                        {{ __('profile.full_name') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide">{{ __('profile.email') }}
                    </p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide">{{ __('profile.phone') }}
                    </p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5" dir="ltr">{{ $user->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                        {{ __('profile.license_number') }}</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mt-0.5">
                        {{ $user->driver_license_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                        {{ __('profile.license_status') }}</p>
                    <p class="mt-0.5">
                        @if($user->driver_license_verified)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 dark:bg-green-900/30
                                             text-green-700 dark:text-green-400 text-xs font-semibold rounded-full">
                                ✓ {{ __('profile.verified') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 dark:bg-orange-900/30
                                             text-orange-700 dark:text-orange-400 text-xs font-semibold rounded-full">
                                ⏳ {{ __('profile.pending_review') }}
                            </span>
                        @endif
                    </p>
                </div>
                @if($user->driver_license_image)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">
                            {{ __('profile.license_photo') }}</p>
                        <img src="{{ asset('storage/' . $user->driver_license_image) }}"
                            class="w-32 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                    </div>
                @endif
            </div>
        </div>

        {{-- Edit Form --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">

            @if($pendingRequest)
                <div class="text-center py-6">
                    <p class="text-sm text-gray-400 dark:text-gray-500">
                        {{ __('profile.pending_request_desc') }}
                    </p>
                </div>
            @else
                <p class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 mb-5">
                    ℹ️ {{ __('profile.changes_require_approval') }}
                </p>

                @if($errors->any())
                    <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-3">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('profile.full_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                          dark:text-gray-100 rounded-xl px-4 py-2.5
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('profile.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                          dark:text-gray-100 rounded-xl px-4 py-2.5
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('profile.phone') }}
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                          dark:text-gray-100 rounded-xl px-4 py-2.5
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('profile.license_number') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="driver_license_number"
                            value="{{ old('driver_license_number', $user->driver_license_number) }}" class="w-full text-sm border border-gray-200 dark:border-gray-700 dark:bg-gray-800
                                          dark:text-gray-100 rounded-xl px-4 py-2.5
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('profile.license_photo') }}
                        </label>
                        <input type="file" name="driver_license_image" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400
                                          file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/40
                                          file:text-indigo-700 dark:file:text-indigo-300
                                          hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/60">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ __('profile.new_license_photo_note') }}
                        </p>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold
                                       rounded-xl hover:bg-indigo-700 transition-colors">
                        {{ __('profile.save_changes') }}
                    </button>
                </form>
            @endif
        </div>

    </div>
@endsection