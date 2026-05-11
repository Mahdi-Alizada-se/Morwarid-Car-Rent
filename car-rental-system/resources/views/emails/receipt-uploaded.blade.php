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
            background: #f59e0b;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            color: white;
            font-size: 22px;
            margin: 0;
        }

        .body {
            padding: 32px;
        }

        .body p {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .info-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
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
            <h1>📎 Receipt Uploaded</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $admin->name }}</strong>,</p>
            <p>A customer has uploaded a bank transfer receipt and is awaiting payment confirmation.</p>

            <div class="info-box">
                <div class="info-row">
                    <span>Customer</span>
                    <span>{{ $booking->customer?->name }}</span>
                </div>
                <div class="info-row">
                    <span>Booking Reference</span>
                    <span>{{ $booking->reference_code }}</span>
                </div>
                <div class="info-row">
                    <span>Vehicle</span>
                    <span>{{ $booking->vehicle?->full_name }}</span>
                </div>
                <div class="info-row">
                    <span>Amount</span>
                    <span>AFN {{ number_format($payment->amount) }}</span>
                </div>
                @if($payment->bank_reference)
                    <div class="info-row">
                        <span>Bank Reference</span>
                        <span>{{ $payment->bank_reference }}</span>
                    </div>
                @endif
            </div>

            <p>Please review the receipt and confirm or reject the payment.</p>

            <a href="{{ url('/admin/payments/' . $payment->id) }}" class="btn">
                Review Receipt →
            </a>
        </div>
        <div class="footer">
            {{ config('app.name') }} · Kabul, Afghanistan · info@carrental.com
        </div>
    </div>
</body>

</html>