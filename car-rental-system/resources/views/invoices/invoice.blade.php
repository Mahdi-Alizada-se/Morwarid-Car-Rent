<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice — {{ $booking->reference_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #fff;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 40px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 2px solid #4f46e5;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            color: #4f46e5;
        }

        .company-details {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
            line-height: 1.5;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }

        .invoice-title .reference {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .invoice-title .date {
            font-size: 12px;
            color: #6b7280;
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #d1fae5;
            color: #065f46;
        }

        /* Info Grid */
        .info-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 32px;
        }

        .info-card {
            flex: 1;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
        }

        .info-card h3 {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .info-card p {
            font-size: 13px;
            color: #111827;
            margin-bottom: 3px;
        }

        .info-card .label {
            font-size: 11px;
            color: #6b7280;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        thead tr {
            background: #4f46e5;
            color: white;
        }

        thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        tbody td {
            padding: 12px 14px;
            font-size: 13px;
            color: #374151;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        /* Totals */
        .totals {
            margin-left: auto;
            width: 280px;
            margin-bottom: 32px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .total-row.final {
            font-size: 16px;
            font-weight: 700;
            color: #4f46e5;
            border-bottom: none;
            border-top: 2px solid #4f46e5;
            padding-top: 12px;
            margin-top: 4px;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .thank-you {
            font-size: 16px;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 6px;
        }

        .footer-note {
            font-size: 11px;
            color: #9ca3af;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- Header --}}
        <div class="header">
            <div>
                <div class="company-name">{{ config('app.name') }}</div>
                <div class="company-details">
                    Kabul, Afghanistan<br>
                    info@carrental.com | +93 700 000 000
                </div>
                <div style="margin-top: 8px;">
                    <span class="badge">PAID</span>
                </div>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="reference">#{{ $booking->reference_code }}</div>
                <div class="date">Issued: {{ $issuedAt }}</div>
            </div>
        </div>

        {{-- Customer + Booking Info --}}
        <div class="info-grid">
            <div class="info-card">
                <h3>Bill To</h3>
                <p><strong>{{ $customer->name }}</strong></p>
                <p class="label">{{ $customer->email }}</p>
                @if($customer->phone)
                    <p class="label">{{ $customer->phone }}</p>
                @endif
            </div>
            <div class="info-card">
                <h3>Booking Info</h3>
                <p><strong>{{ $booking->reference_code }}</strong></p>
                <p class="label">{{ $booking->pickup_date->format('M d, Y') }} →
                    {{ $booking->return_date->format('M d, Y') }}</p>
                <p class="label">Duration: {{ $booking->duration_in_days }} days</p>
            </div>
            <div class="info-card">
                <h3>Payment Info</h3>
                <p><strong>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</strong></p>
                @if($payment->bank_reference)
                    <p class="label">Ref: {{ $payment->bank_reference }}</p>
                @endif
                <p class="label">{{ $payment->paid_at?->format('M d, Y H:i') }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Rate</th>
                    <th>Duration</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})</strong><br>
                        <span style="font-size: 11px; color: #6b7280;">
                            License: {{ $vehicle->license_plate }} · {{ ucfirst($vehicle->transmission) }} ·
                            {{ $vehicle->seats }} seats
                        </span>
                    </td>
                    <td>
                        @if($dailyRate)
                            AFN {{ number_format($dailyRate->base_rate) }}/day
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $booking->duration_in_days }} days</td>
                    <td style="text-align: right; font-weight: 600;">
                        AFN {{ number_format($booking->total_amount) }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>AFN {{ number_format($booking->total_amount) }}</span>
            </div>
            <div class="total-row">
                <span>Tax</span>
                <span>AFN 0</span>
            </div>
            <div class="total-row final">
                <span>Total Paid</span>
                <span>AFN {{ number_format($payment->amount) }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="thank-you">Thank you for choosing {{ config('app.name') }}!</div>
            <div class="footer-note">
                Please keep this invoice for your records.<br>
                For any questions, contact us at info@carrental.com or +93 700 000 000.
            </div>
        </div>

    </div>
</body>

</html>