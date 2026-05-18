<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #ef4444;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            color: white;
            font-size: 22px;
            margin: 0;
        }

        .header p {
            color: #fecaca;
            margin: 8px 0 0;
            font-size: 14px;
        }

        .body {
            padding: 32px;
        }

        .body p {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .fee-box {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .fee-box .amount {
            font-size: 36px;
            font-weight: 800;
            color: #dc2626;
        }

        .fee-box .label {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }

        .info-box h3 {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-row span:first-child {
            color: #6b7280;
        }

        .info-row span:last-child {
            font-weight: 600;
            color: #111827;
        }

        .option {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 10px;
        }

        .option-title {
            font-weight: 700;
            color: #15803d;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .option p {
            margin: 0;
            color: #374151;
            font-size: 13px;
            line-height: 1.5;
        }

        .footer {
            background: #f9fafb;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="header">
            <h1>❌ Booking Cancelled</h1>
            <p>A cancellation fee applies to your booking</p>
        </div>

        <div class="body">
            <p>Dear <strong>{{ $customer->name }}</strong>,</p>

            <p>
                Your booking <strong>{{ $booking->reference_code }}</strong> for
                <strong>{{ $booking->vehicle?->full_name }}</strong> has been cancelled.
            </p>

            <p>
                Since this cancellation was made <strong>more than 5 hours</strong> after
                your original booking time, a cancellation fee applies as per our policy.
            </p>

            {{-- Fee Box --}}
            <div class="fee-box">
                <div class="amount">AFN {{ $fee }}</div>
                <div class="label">Cancellation Fee</div>
            </div>

            {{-- Booking Details --}}
            <div class="info-box">
                <h3>Booking Details</h3>
                <div class="info-row">
                    <span>Reference</span>
                    <span>{{ $booking->reference_code }}</span>
                </div>
                <div class="info-row">
                    <span>Vehicle</span>
                    <span>{{ $booking->vehicle?->full_name }}</span>
                </div>
                <div class="info-row">
                    <span>Pickup Date</span>
                    <span>{{ $booking->pickup_date?->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span>Cancellation Reason</span>
                    <span>{{ $booking->cancellation_reason }}</span>
                </div>
            </div>

            {{-- Payment Options --}}
            <p><strong>How to pay the cancellation fee:</strong></p>

            <div class="option">
                <div class="option-title">Option 1 — Pay at Counter</div>
                <p>
                    Visit us at <strong>{{ config('company.pickup_name') }}</strong><br>
                    {{ config('company.address') }}<br>
                    Working Hours: {{ config('company.working_hours') }}
                </p>
            </div>

            <div class="option">
                <div class="option-title">Option 2 — Bank Transfer</div>
                <p>
                    Bank: <strong>{{ config('company.bank_name') }}</strong><br>
                    Account Name: <strong>{{ config('company.account_name') }}</strong><br>
                    Account Number: <strong>{{ config('company.account_number') }}</strong><br>
                    @if(config('company.branch'))
                        Branch: {{ config('company.branch') }}
                    @endif
                </p>
            </div>

            <p style="color:#6b7280;font-size:13px;margin-top:20px;">
                For questions or assistance please contact us at:
                <strong>{{ config('company.phone') }}</strong>
            </p>
        </div>

        <div class="footer">
            {{ config('company.name') }} · {{ config('company.address') }} · {{ config('company.phone') }}
        </div>

    </div>
</body>

</html>