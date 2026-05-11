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
            background: #10b981;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            color: white;
            font-size: 22px;
            margin: 0;
        }

        .header p {
            color: #d1fae5;
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

        .reference-box {
            background: #ecfdf5;
            border: 2px solid #6ee7b7;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .reference-box .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reference-box .code {
            font-size: 24px;
            font-weight: 800;
            color: #059669;
            font-family: monospace;
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

        .pickup-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }

        .pickup-box h3 {
            color: #1d4ed8;
            font-size: 14px;
            margin-bottom: 8px;
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
            <h1>✅ Payment Confirmed!</h1>
            <p>Your booking is now confirmed</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $customer->name }}</strong>,</p>
            <p>Great news! Your payment has been confirmed and your booking is now active.</p>

            <div class="reference-box">
                <div class="label">Booking Reference</div>
                <div class="code">{{ $booking->reference_code }}</div>
            </div>

            <div class="info-box">
                <div class="info-row">
                    <span>Vehicle</span>
                    <span>{{ $booking->vehicle?->full_name }}</span>
                </div>
                <div class="info-row">
                    <span>Pickup Date</span>
                    <span>{{ $booking->pickup_date->format('M d, Y — H:i') }}</span>
                </div>
                <div class="info-row">
                    <span>Return Date</span>
                    <span>{{ $booking->return_date->format('M d, Y — H:i') }}</span>
                </div>
                <div class="info-row">
                    <span>Duration</span>
                    <span>{{ $booking->duration_in_days }} days</span>
                </div>
                <div class="info-row">
                    <span>Amount Paid</span>
                    <span>AFN {{ number_format($payment->amount) }}</span>
                </div>
                <div class="info-row">
                    <span>Payment Method</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                </div>
            </div>

            <div class="pickup-box">
                <h3>📍 Pickup Instructions</h3>
                <p style="margin:0; font-size: 13px; color: #374151;">
                    Please bring this email and a valid ID when picking up your vehicle.
                    @if($booking->pickup_location)
                        Pickup location: <strong>{{ $booking->pickup_location }}</strong>
                    @endif
                </p>
            </div>

            <p>Your invoice is attached to this email as a PDF.</p>

            <a href="{{ url('/my-bookings') }}" class="btn">
                View My Bookings →
            </a>
        </div>
        <div class="footer">
            {{ config('app.name') }} · Kabul, Afghanistan · info@carrental.com<br>
            Thank you for choosing us!
        </div>
    </div>
</body>

</html>