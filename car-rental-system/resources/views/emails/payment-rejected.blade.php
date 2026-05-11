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
        }

        .reason-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }

        .reason-box h3 {
            color: #dc2626;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .reason-box p {
            margin: 0;
            color: #374151;
            font-size: 14px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-row span:first-child {
            color: #6b7280;
        }

        .info-row span:last-child {
            font-weight: 600;
            color: #111827;
        }

        .btn {
            display: inline-block;
            background: #4f46e5;
            color: white;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }

        .steps {
            margin: 20px 0;
        }

        .step {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .step-num {
            background: #4f46e5;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .step-text {
            font-size: 13px;
            color: #374151;
            padding-top: 3px;
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
            <h1>❌ Receipt Not Accepted</h1>
            <p>Action required to complete your booking</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $customer->name }}</strong>,</p>
            <p>Unfortunately, we could not verify your bank transfer receipt for booking
                <strong>{{ $booking->reference_code }}</strong>.</p>

            <div class="reason-box">
                <h3>Reason for Rejection</h3>
                <p>{{ $payment->rejection_reason ?? 'The receipt could not be verified. Please ensure you upload a clear image of the bank transfer confirmation.' }}
                </p>
            </div>

            <div class="info-box">
                <div class="info-row">
                    <span>Booking Reference</span>
                    <span>{{ $booking->reference_code }}</span>
                </div>
                <div class="info-row">
                    <span>Amount Required</span>
                    <span>AFN {{ number_format($payment->amount) }}</span>
                </div>
            </div>

            <p><strong>What to do next:</strong></p>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text">Check that the transfer amount matches exactly: <strong>AFN
                            {{ number_format($payment->amount) }}</strong></div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text">Make sure your receipt shows the bank's stamp or confirmation number clearly.
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-text">Upload a new clear receipt using the button below.</div>
                </div>
            </div>

            <a href="{{ url('/payments/' . $payment->id . '/status') }}" class="btn">
                Re-upload Receipt →
            </a>

            <p style="font-size: 13px; color: #6b7280;">
                Need help? Contact us at info@carrental.com or +93 700 000 000.
            </p>
        </div>
        <div class="footer">
            {{ config('app.name') }} · Kabul, Afghanistan · info@carrental.com
        </div>
    </div>
</body>

</html>