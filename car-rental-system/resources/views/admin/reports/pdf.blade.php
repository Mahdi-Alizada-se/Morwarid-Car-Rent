<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bookings Report — {{ config('app.name') }}</title>
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
            text-align: left;
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
            <div class="report-title">Bookings Report</div>
            <div class="report-meta">
                @if($from || $to)
                    Period:
                    {{ $from ? \Carbon\Carbon::parse($from)->format('M d, Y') : 'Beginning' }}
                    →
                    {{ $to ? \Carbon\Carbon::parse($to)->format('M d, Y') : 'Today' }}
                    &nbsp;|&nbsp;
                @endif
                Generated: {{ $generatedAt }}
                &nbsp;|&nbsp;
                Total Records: {{ count($bookings) }}
            </div>
        </div>

        {{-- Summary --}}
        <div class="summary">
            <div class="summary-card">
                <div class="number">{{ count($bookings) }}</div>
                <div class="label">Total Bookings</div>
            </div>
            <div class="summary-card">
                <div class="number">AFN {{ number_format($totalRevenue) }}</div>
                <div class="label">Total Revenue</div>
            </div>
            <div class="summary-card">
                <div class="number">
                    {{ $bookings->where('status', 'completed')->count() }}
                </div>
                <div class="label">Completed</div>
            </div>
            <div class="summary-card">
                <div class="number">
                    {{ $bookings->where('status', 'cancelled')->count() }}
                </div>
                <div class="label">Cancelled</div>
            </div>
        </div>

        {{-- Table --}}
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Pickup</th>
                    <th>Return</th>
                    <th>Days</th>
                    <th>Amount AFN</th>
                    <th>Status</th>
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
                        <td>{{ $booking->pickup_date?->format('M d, Y') }}</td>
                        <td>{{ $booking->return_date?->format('M d, Y') }}</td>
                        <td>{{ $days }}</td>
                        <td><strong>{{ number_format($booking->total_amount) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $booking->status }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">
                            No bookings found for selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            {{ config('app.name') }} · Kabul, Afghanistan · info@carrental.com · +93 700 000 000<br>
            This report was automatically generated on {{ $generatedAt }}
        </div>

    </div>
</body>

</html>