<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isFa = $locale === 'fa';
    $isPs = $locale === 'ps';
    $isRtl = $isFa || $isPs;

    function pdfT($en, $fa, $ps)
    {
        $l = app()->getLocale();
        if ($l === 'fa')
            return $fa;
        if ($l === 'ps')
            return $ps;
        return $en;
    }
@endphp
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>{{ pdfT('Bookings Report', 'گزارش رزروها', 'د بکینګونو راپور') }} — {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #fff;
            direction:
                {{ $isRtl ? 'rtl' : 'ltr' }}
            ;
        }

        .container {
            padding: 30px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #4f46e5;
        }

        .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-top: 4px;
        }

        .report-meta {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Summary */
        .summary {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }

        .summary-card {
            flex: 1;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .summary-card .number {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        .summary-card .label {
            font-size: 10px;
            color: #6b7280;
            margin-top: 3px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        thead tr {
            background: #4f46e5;
        }

        thead th {
            color: white;
            padding: 8px 10px;
            text-align: {{ $isRtl ? 'right' : 'left' }};
            font-weight: 600;
            font-size: 10px;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody td {
            padding: 7px 10px;
            color: #374151;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-confirmed {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-completed {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Footer */
        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- Header --}}
        <div class="header">
            <div class="company-name">{{ config('app.name') }}</div>
            <div class="report-title">
                {{ pdfT('Bookings Report', 'گزارش رزروها', 'د بکینګونو راپور') }}
            </div>
            <div class="report-meta">
                @if($from || $to)
                    {{ pdfT('Period:', 'دوره:', 'موده:') }}
                    {{ $from ? \Carbon\Carbon::parse($from)->translatedFormat('M d, Y') : pdfT('Beginning', 'ابتدا', 'پیل') }}
                    →
                    {{ $to ? \Carbon\Carbon::parse($to)->translatedFormat('M d, Y') : pdfT('Today', 'امروز', 'نن') }}
                    &nbsp;|&nbsp;
                @endif
                {{ pdfT('Generated:', 'تولید شده در:', 'جوړ شوی:') }} {{ $generatedAt }}
                &nbsp;|&nbsp;
                {{ pdfT('Total Records:', 'مجموع رکوردها:', 'ټول ریکارډونه:') }} {{ count($bookings) }}
            </div>
        </div>

        {{-- Summary --}}
        <div class="summary">
            <div class="summary-card">
                <div class="number">{{ count($bookings) }}</div>
                <div class="label">{{ pdfT('Total Bookings', 'مجموع رزروها', 'ټول بکینګونه') }}</div>
            </div>
            <div class="summary-card">
                <div class="number">AFN {{ number_format($totalRevenue) }}</div>
                <div class="label">{{ pdfT('Total Revenue', 'مجموع درآمد', 'ټول عاید') }}</div>
            </div>
            <div class="summary-card">
                <div class="number">{{ $bookings->where('status', 'completed')->count() }}</div>
                <div class="label">{{ pdfT('Completed', 'تکمیل شده', 'بشپړ شوی') }}</div>
            </div>
            <div class="summary-card">
                <div class="number">{{ $bookings->where('status', 'cancelled')->count() }}</div>
                <div class="label">{{ pdfT('Cancelled', 'لغو شده', 'لغو شوی') }}</div>
            </div>
        </div>

        {{-- Table --}}
        @php
            $statusLabels = [
                'pending' => pdfT('Pending', 'در انتظار', 'تمه'),
                'confirmed' => pdfT('Confirmed', 'تأیید شده', 'تایید شوی'),
                'active' => pdfT('Active', 'فعال', 'فعال'),
                'completed' => pdfT('Completed', 'تکمیل شده', 'بشپړ شوی'),
                'cancelled' => pdfT('Cancelled', 'لغو شده', 'لغو شوی'),
            ];
        @endphp

        <table>
            <thead>
                <tr>
                    <th>{{ pdfT('Reference', 'کد رزرو', 'راجع') }}</th>
                    <th>{{ pdfT('Customer', 'مشتری', 'پیرودونکی') }}</th>
                    <th>{{ pdfT('Vehicle', 'موتر', 'موټر') }}</th>
                    <th>{{ pdfT('Pickup', 'تحویل', 'تحویل') }}</th>
                    <th>{{ pdfT('Return', 'بازگشت', 'بیرته راستنیدل') }}</th>
                    <th>{{ pdfT('Days', 'روز', 'ورځې') }}</th>
                    <th>{{ pdfT('Amount AFN', 'مبلغ (افغانی)', 'مقدار (افغانۍ)') }}</th>
                    <th>{{ pdfT('Status', 'وضعیت', 'حالت') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $days = (int) \Carbon\Carbon::parse($booking->pickup_date)
                            ->diffInDays($booking->return_date) ?: 1;
                    @endphp
                    <tr>
                        <td><strong>{{ $booking->reference_code }}</strong></td>
                        <td>{{ $booking->customer?->name ?? '—' }}</td>
                        <td>{{ $booking->vehicle?->full_name ?? '—' }}</td>
                        <td>{{ $booking->pickup_date?->translatedFormat('M d, Y') }}</td>
                        <td>{{ $booking->return_date?->translatedFormat('M d, Y') }}</td>
                        <td>{{ $days }}</td>
                        <td><strong>{{ number_format($booking->total_amount) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $booking->status }}">
                                {{ $statusLabels[$booking->status] ?? ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                                <tr>
                                    <td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">
                                        {{ pdfT(
                        'No bookings found for selected criteria.',
                        'هیچ رزروی برای معیارهای انتخابی یافت نشد.',
                        'د ټاکل شویو معیارونو لپاره هیڅ بکینګ ونه موندل شو.'
                    ) }}
                                    </td>
                                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            {{ config('app.name') }} · {{ pdfT('Kabul, Afghanistan', 'کابل، افغانستان', 'کابل، افغانستان') }}
            · info@carrental.com · +93 730 751 894<br>
            {{ pdfT(
    'This report was automatically generated on ' . $generatedAt,
    'این گزارش به صورت خودکار در تاریخ ' . $generatedAt . ' تولید شده است.',
    'دا راپور په اتومات ډول د ' . $generatedAt . ' نیټه جوړ شوی دی.'
) }}
        </div>

    </div>
</body>

</html>