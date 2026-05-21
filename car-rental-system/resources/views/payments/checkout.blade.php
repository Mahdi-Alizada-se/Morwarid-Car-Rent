@extends('layouts.app')

@section('title', __('payments.checkout'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ─── Booking Summary Card ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="font-bold text-gray-900 text-lg mb-4">
                {{ __('bookings.booking_details') }}
            </h2>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">

                @if($booking->vehicle?->thumbnail)
                    <img src="{{ asset('storage/' . $booking->vehicle->thumbnail) }}"
                        class="w-28 h-20 object-cover rounded-xl border border-gray-200 flex-shrink-0"
                        alt="{{ $booking->vehicle?->full_name }}">
                @endif

                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 text-xl">
                        {{ $booking->vehicle?->full_name }}
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-3">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.pickup_date') }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ $booking->pickup_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.return_date') }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ $booking->return_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.duration') }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">
                                {{ $booking->pickup_date->diffInDays($booking->return_date) }}
                                {{ __('vehicles.days') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">
                                {{ __('vehicles.total') }}
                            </p>
                            <p class="text-2xl font-black text-indigo-600 mt-0.5">
                                AFN {{ number_format($booking->total_amount, 0) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Heading ─────────────────────────────────────────────────────────── --}}
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">
            {{ __('payments.choose_method') }}
        </h2>

        {{-- ─── Two Payment Cards ───────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            {{-- ─── Pay at Counter ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border-2 border-green-200 shadow-sm
                                hover:border-green-400 hover:shadow-md transition-all flex flex-col">

                {{-- Header --}}
                <div class="bg-green-50 px-6 py-5 border-b border-green-100 rounded-t-2xl">
                    <div class="mb-3 flex justify-center">
                        <svg class="w-14 h-14" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <path style="fill:#507C5C;"
                                d="M126.465,262.758v-36.906c14.73,2.582,16.946,10.749,17.28,15.551 c0.085,1.231,1.101,2.182,2.335,2.182h16.089c1.352,0,2.415-1.143,2.345-2.493c-1.044-20.242-14.918-33.266-38.049-36.191v-12.411 c0-1.294-1.049-2.342-2.342-2.342h-16.088c-1.294,0-2.342,1.049-2.342,2.342v12.411c-24.066,3.043-38.112,17.019-38.112,38.684 s14.045,35.639,38.112,38.684v36.906c-14.73-2.584-16.946-10.749-17.28-15.551c-0.085-1.231-1.101-2.182-2.335-2.182H69.989 c-1.352,0-2.415,1.143-2.345,2.493c1.044,20.242,14.918,33.266,38.049,36.191v12.411c0,1.294,1.049,2.342,2.342,2.342h16.088 c1.294,0,2.342-1.049,2.342-2.342v-12.411c24.066-3.043,38.112-17.019,38.112-38.684 C164.577,279.778,150.532,265.802,126.465,262.758z M105.691,261.316c-17.335-3.039-17.339-13.809-17.339-17.732 c0-3.921,0.003-14.693,17.339-17.732V261.316z M126.465,319.174v-35.465c17.335,3.04,17.339,13.811,17.339,17.732 C143.804,305.363,143.801,316.134,126.465,319.174z" />
                            <rect x="235.661" y="260.478" style="fill:#CFF09E;" width="198.608" height="83.563" />
                            <path style="fill:#507C5C;"
                                d="M354.661,215.376H231.824c-7.95,0-14.395-6.446-14.395-14.395s6.445-14.395,14.395-14.395h122.837 c7.95,0,14.395,6.446,14.395,14.395S362.611,215.376,354.661,215.376z" />
                            <rect x="434.27" y="41.032" style="fill:#CFF09E;" width="63.338" height="103.889" />
                            <path style="fill:#507C5C;"
                                d="M497.605,159.313c7.95,0,14.395-6.446,14.395-14.395V41.03c0-7.949-6.445-14.395-14.395-14.395 h-63.338l0,0l0,0c-7.95,0-14.395,6.446-14.395,14.395v88.571H14.395C6.445,129.601,0,136.048,0,143.997v257.032 c0,7.949,6.445,14.395,14.395,14.395h233.326c7.95,0,14.395-6.446,14.395-14.395s-6.445-14.395-14.395-14.395H28.79V158.393h391.08 v87.695H235.663c-7.95,0-14.395,6.446-14.395,14.395v83.565c0,7.949,6.445,14.395,14.395,14.395H419.87v28.193h-78.81 c-7.95,0-14.395,6.446-14.395,14.395s6.445,14.395,14.395,14.395h78.81v25.321c0,3.934,1.611,7.698,4.457,10.413l31.669,30.224 c2.781,2.654,6.36,3.982,9.938,3.982c3.579,0,7.157-1.327,9.938-3.982l31.669-30.224c2.846-2.716,4.457-6.479,4.457-10.413V214.934 c0-7.949-6.445-14.395-14.395-14.395c-7.95,0-14.395,6.446-14.395,14.395v219.651l-17.274,16.485l-17.274-16.485v-33.542 c0-0.004,0-0.009,0-0.009c0-0.01,0-0.014,0-0.014v-56.96c0,0,0-0.009,0-0.014v-83.565v-0.014V159.311h48.945V159.313z M483.21,55.425v74.176h-34.548V55.425H483.21z M250.058,329.651v-54.775H419.87v54.775H250.058z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-green-800">
                        {{ __('payments.pay_at_counter') }}
                    </h3>
                    <p class="text-green-600 text-sm mt-1">
                        Pay cash when you arrive at our office
                    </p>
                </div>

                {{-- Steps --}}
                <div class="px-6 py-5 flex-1">
                    <ol class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">1</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Complete your booking now — no payment needed yet
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">2</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Come to <strong>{{ config('company.pickup_name') }}</strong>,
                                {{ config('company.address') }}
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">3</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Pay <strong class="text-green-700 text-base">
                                    AFN {{ number_format($booking->total_amount, 0) }}
                                </strong> cash at our desk
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-green-100 text-green-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">4</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Receive your vehicle keys and drive away! 🎉
                            </span>
                        </li>
                    </ol>
                </div>

                {{-- Button --}}
                <div class="px-6 pb-6">
                    <form method="POST" action="{{ route('customer.payments.counter') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <button type="submit" class="w-full py-4 bg-green-600 text-white font-bold text-base
                                               rounded-xl hover:bg-green-700 active:bg-green-800 transition-colors">
                            <svg class="w-5 h-5 inline-block mr-1" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                <path style="fill:#ffffff;"
                                    d="M126.465,262.758v-36.906c14.73,2.582,16.946,10.749,17.28,15.551 c0.085,1.231,1.101,2.182,2.335,2.182h16.089c1.352,0,2.415-1.143,2.345-2.493c-1.044-20.242-14.918-33.266-38.049-36.191v-12.411 c0-1.294-1.049-2.342-2.342-2.342h-16.088c-1.294,0-2.342,1.049-2.342,2.342v12.411c-24.066,3.043-38.112,17.019-38.112,38.684 s14.045,35.639,38.112,38.684v36.906c-14.73-2.584-16.946-10.749-17.28-15.551c-0.085-1.231-1.101-2.182-2.335-2.182H69.989 c-1.352,0-2.415,1.143-2.345,2.493c1.044,20.242,14.918,33.266,38.049,36.191v12.411c0,1.294,1.049,2.342,2.342,2.342h16.088 c1.294,0,2.342-1.049,2.342-2.342v-12.411c24.066-3.043,38.112-17.019,38.112-38.684 C164.577,279.778,150.532,265.802,126.465,262.758z M105.691,261.316c-17.335-3.039-17.339-13.809-17.339-17.732 c0-3.921,0.003-14.693,17.339-17.732V261.316z M126.465,319.174v-35.465c17.335,3.04,17.339,13.811,17.339,17.732 C143.804,305.363,143.801,316.134,126.465,319.174z" />
                                <path style="fill:#ffffff;"
                                    d="M497.605,159.313c7.95,0,14.395-6.446,14.395-14.395V41.03c0-7.949-6.445-14.395-14.395-14.395 h-63.338c-7.95,0-14.395,6.446-14.395,14.395v88.571H14.395C6.445,129.601,0,136.048,0,143.997v257.032 c0,7.949,6.445,14.395,14.395,14.395h233.326c7.95,0,14.395-6.446,14.395-14.395s-6.445-14.395-14.395-14.395H28.79V158.393h391.08 v87.695H235.663c-7.95,0-14.395,6.446-14.395,14.395v83.565c0,7.949,6.445,14.395,14.395,14.395H419.87v28.193h-78.81 c-7.95,0-14.395,6.446-14.395,14.395s6.445,14.395,14.395,14.395h78.81v25.321c0,3.934,1.611,7.698,4.457,10.413l31.669,30.224 c2.781,2.654,6.36,3.982,9.938,3.982c3.579,0,7.157-1.327,9.938-3.982l31.669-30.224c2.846-2.716,4.457-6.479,4.457-10.413V214.934 c0-7.949-6.445-14.395-14.395-14.395c-7.95,0-14.395,6.446-14.395,14.395v219.651l-17.274,16.485l-17.274-16.485V159.311h48.945V159.313z M483.21,55.425v74.176h-34.548V55.425H483.21z M250.058,329.651v-54.775H419.87v54.775H250.058z" />
                            </svg> Book Now — Pay at Counter
                        </button>
                    </form>
                </div>
            </div>

            {{-- ─── Bank Transfer ───────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border-2 border-blue-200 shadow-sm
                                hover:border-blue-400 hover:shadow-md transition-all flex flex-col">

                {{-- Header --}}
                <div class="bg-blue-50 px-6 py-5 border-b border-blue-100 rounded-t-2xl">
                    <div class="mb-3 flex justify-center">
                        <svg class="w-14 h-14" viewBox="0 0 496 496" xmlns="http://www.w3.org/2000/svg" fill="#4f46e5">
                            <path
                                d="M208,120c0-13.232,10.768-24,24-24h40V80h-40c-22.056,0-40,17.944-40,40h-9.888L0,211.056V256h16v32h16v128H16v32H0v48h400v-48h-16v-32h-16V288h16v-32h16v-44.944L217.888,120H208z M32,432h48v16H32V432z M128,288v128h-16v32H96v-32H80V288h16v-32h16v32H128z M224,288v128h-16v32h-16v-32h-16V288h16v-32h16v32H224z M320,288v128h-16v32h-16v-32h-16V288h16v-32h16v32H320z M368,432v16h-48v-16H368z M336,416V288h16v128H336z M272,272h-48v-16h48V272z M256,288v128h-16V288H256z M272,432v16h-48v-16H272z M176,272h-48v-16h48V272z M160,288v128h-16V288H160z M176,432v16h-48v-16H176z M80,272H32v-16h48V272z M64,288v128H48V288H64z M384,464v16H16v-16H384z M368,272h-48v-16h48V272z M384,240h-80h-16h-80h-16h-80H96H16v-19.056L185.888,136H192v56h16v-56h6.112L384,220.944V240z" />
                            <rect x="64" y="208" width="272" height="16" />
                            <path
                                d="M408,0c-48.52,0-88,39.48-88,88s39.48,88,88,88c48.52,0,88-39.48,88-88S456.52,0,408,0z M408,160c-39.704,0-72-32.296-72-72s32.296-72,72-72c39.704,0,72,32.296,72,72S447.704,160,408,160z" />
                            <path
                                d="M400,64h16c4.416,0,8,3.584,8,8h16c0-13.232-10.768-24-24-24V32h-16v16c-13.232,0-24,10.768-24,24s10.768,24,24,24h16c4.416,0,8,3.584,8,8s-3.584,8-8,8h-16c-4.416,0-8-3.584-8-8h-16c0,13.232,10.768,24,24,24v16h16v-16c13.232,0,24-10.768,24-24s-10.768-24-24-24h-16c-4.416,0-8-3.584-8-8S395.584,64,400,64z" />
                            <rect x="288" y="80" width="16" height="16" />
                            <path d="M448,312c0,13.232-10.768,24-24,24h-8v16h8c22.056,0,40-17.944,40-40V184h-16V312z" />
                            <rect x="384" y="336" width="16" height="16" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-blue-800">
                        {{ __('payments.bank_transfer') }}
                    </h3>
                    <p class="text-blue-600 text-sm mt-1">
                        Transfer money then upload your receipt
                    </p>
                </div>

                {{-- Steps --}}
                <div class="px-6 py-5 flex-1">
                    <ol class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">1</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Complete your booking now
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">2</span>
                            <div class="text-sm text-gray-700 pt-0.5 flex-1">
                                <p class="mb-2">
                                    Transfer <strong class="text-blue-700 text-base">
                                        AFN {{ number_format($booking->total_amount, 0) }}
                                    </strong> to:
                                </p>
                                {{-- Bank Details Box --}}
                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 space-y-2 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Bank</span>
                                        <span class="font-bold text-gray-900">{{ config('company.bank_name') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Account Name</span>
                                        <span class="font-bold text-gray-900">{{ config('company.account_name') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Account Number</span>
                                        <span class="font-bold text-blue-700 font-mono text-sm">
                                            {{ config('company.account_number') }}
                                        </span>
                                    </div>
                                    @if(config('company.branch'))
                                        <div class="flex justify-between">
                                            <span class="text-gray-500 font-medium">Branch</span>
                                            <span class="font-bold text-gray-900">{{ config('company.branch') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">3</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Take a clear photo of your bank receipt
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">4</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Upload the receipt photo on the next screen
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-7 h-7 bg-blue-100 text-blue-700 rounded-full flex items-center
                                                 justify-center text-sm font-bold flex-shrink-0 mt-0.5">5</span>
                            <span class="text-sm text-gray-700 pt-0.5">
                                Admin confirms within 2 hours ✓
                            </span>
                        </li>
                    </ol>
                </div>

                {{-- Button --}}
                <div class="px-6 pb-6">
                    <form method="POST" action="{{ route('customer.payments.bank-transfer') }}">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-bold text-base
                                               rounded-xl hover:bg-blue-700 active:bg-blue-800 transition-colors">
                            <svg class="w-5 h-5 inline-block mr-1" viewBox="0 0 496 496" xmlns="http://www.w3.org/2000/svg" fill="#4f46e5">
    <path d="M208,120c0-13.232,10.768-24,24-24h40V80h-40c-22.056,0-40,17.944-40,40h-9.888L0,211.056V256h16v32h16v128H16v32H0v48h400v-48h-16v-32h-16V288h16v-32h16v-44.944L217.888,120H208z M32,432h48v16H32V432z M128,288v128h-16v32H96v-32H80V288h16v-32h16v32H128z M224,288v128h-16v32h-16v-32h-16V288h16v-32h16v32H224z M320,288v128h-16v32h-16v-32h-16V288h16v-32h16v32H320z M368,432v16h-48v-16H368z M336,416V288h16v128H336z M272,272h-48v-16h48V272z M256,288v128h-16V288H256z M272,432v16h-48v-16H272z M176,272h-48v-16h48V272z M160,288v128h-16V288H160z M176,432v16h-48v-16H176z M80,272H32v-16h48V272z M64,288v128H48V288H64z M384,464v16H16v-16H384z M368,272h-48v-16h48V272z M384,240h-80h-16h-80h-16h-80H96H16v-19.056L185.888,136H192v56h16v-56h6.112L384,220.944V240z"/>
    <rect x="64" y="208" width="272" height="16"/>
    <path d="M408,0c-48.52,0-88,39.48-88,88s39.48,88,88,88c48.52,0,88-39.48,88-88S456.52,0,408,0z M408,160c-39.704,0-72-32.296-72-72s32.296-72,72-72c39.704,0,72,32.296,72,72S447.704,160,408,160z"/>
    <path d="M400,64h16c4.416,0,8,3.584,8,8h16c0-13.232-10.768-24-24-24V32h-16v16c-13.232,0-24,10.768-24,24s10.768,24,24,24h16c4.416,0,8,3.584,8,8s-3.584,8-8,8h-16c-4.416,0-8-3.584-8-8h-16c0,13.232,10.768,24,24,24v16h16v-16c13.232,0,24-10.768,24-24s-10.768-24-24-24h-16c-4.416,0-8-3.584-8-8S395.584,64,400,64z"/>
    <rect x="288" y="80" width="16" height="16"/>
    <path d="M448,312c0,13.232-10.768,24-24,24h-8v16h8c22.056,0,40-17.944,40-40V184h-16V312z"/>
    <rect x="384" y="336" width="16" height="16"/>
</svg> Book Now — Bank Transfer
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- ─── Location Reminder ───────────────────────────────────────────────── --}}
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div class="flex-1">
                    <p class="font-bold text-green-900 text-base">
                        📍 Pickup at {{ config('company.pickup_name') }}
                    </p>
                    <p class="text-green-700 text-sm mt-0.5">{{ config('company.address') }}</p>
                    <p class="text-green-600 text-xs mt-1">
                        🕐 {{ config('company.working_hours') }}
                    </p>
                    <a href="{{ config('company.maps_url') }}" target="_blank"
                        class="inline-block mt-2 text-sm text-green-700 hover:underline font-medium">
                        Open in Google Maps →
                    </a>
                </div>
            </div>
        </div>

    </div>
@endsection