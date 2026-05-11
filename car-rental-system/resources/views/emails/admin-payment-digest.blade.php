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

        .header p {
            color: #fef3c7;
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

        .summary-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item .number {
            font-size: 28px;
            font-weight: 800;
            color: #92400e;
        }

        .summary-item .label {
            font-size: 12px;
            color: #78350f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        thead tr {
            background: #1f2937;
            color: white;
        }

        thead th {
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody td {
            padding: 10px 12px;
            font-size: 13px;
            color: #374151;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #fef3c7;
            color: #92400e;
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
            <h1>⏳ Daily Payment Digest</h1>
            <p>{{ $generatedAt }}</p>
        </div>

        <div class="body">
            <p>Hello <strong>{{ $admin->name }}</strong>,</p>
            <p>Here is your daily summary of payments awaiting review.</p>

            {{-- Summary --}}
            <div class="summary-box">
                <div class="summary-item">
                    <div class="number">{{ $count }}</div>
                    <div class="label">Payments Awaiting</div>
                </div>
                <div class="summary-item">
                    <div class="number">AFN {{ number_format($totalAmount) }}</div>
                    <div class="label">Total Amount</div>
                </div>
            </div>

            {{-- Payments Table --}}
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>
                                <code style="font-size: 11px; background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">
                                    {{ $payment->booking?->reference_code }}
                                </code>
                            </td>
                            <td>{{ $payment->booking?->customer?->name }}</td>
                            <td>{{ $payment->booking?->vehicle?->full_name }}</td>
                            <td><strong>AFN {{ number_format($payment->amount) }}</strong></td>
                            <td><span class="badge">Needs Review</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Please review and confirm or reject these payments as soon as possible.</p>

            <a href="{{ url('/admin/payments?status=receipt_uploaded') }}" class="btn">
                Review All Payments →
            </a>
        </div>

        <div class="footer">
            {{ config('app.name') }} · Admin Panel · {{ $generatedAt }}
        </div>

    </div>
</body>

</html>