<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generateInvoice(Payment $payment): string
    {
        $payment->load([
            'booking.customer',
            'booking.vehicle.pricingRules',
        ]);

        $booking = $payment->booking;
        $customer = $booking->customer;
        $vehicle = $booking->vehicle;

        $dailyRate = $vehicle->pricingRules
            ->where('type', 'daily')
            ->where('is_active', true)
            ->first();

        $data = [
            'payment' => $payment,
            'booking' => $booking,
            'customer' => $customer,
            'vehicle' => $vehicle,
            'dailyRate' => $dailyRate,
            'issuedAt' => now()->format('M d, Y'),
        ];

        $pdf = Pdf::loadView('invoices.invoice', $data);
        $filename = 'invoices/' . $booking->reference_code . '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }
}