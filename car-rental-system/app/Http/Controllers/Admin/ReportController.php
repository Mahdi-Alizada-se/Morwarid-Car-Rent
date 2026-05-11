<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // ─── Reports Index ────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Booking::with(['customer', 'vehicle', 'latestPayment'])
            ->latest();

        if ($request->filled('from')) {
            $query->whereDate('pickup_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('pickup_date', '<=', $request->to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20)->withQueryString();

        $totalRevenue = Booking::when($request->from, fn($q) => $q->whereDate('pickup_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('pickup_date', '<=', $request->to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->whereHas('payments', fn($q) => $q->where('status', 'paid'))
            ->with(['payments' => fn($q) => $q->where('status', 'paid')])
            ->get()
            ->sum(fn($b) => $b->payments->sum('amount'));

        $totalBookings = $bookings->total();
        $completedCount = Booking::when($request->from, fn($q) => $q->whereDate('pickup_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('pickup_date', '<=', $request->to))
            ->where('status', 'completed')
            ->count();

        return view('admin.reports.index', compact(
            'bookings',
            'totalRevenue',
            'totalBookings',
            'completedCount'
        ));
    }

    // ─── Export CSV ───────────────────────────────────────────────────────────

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Booking::with(['customer', 'vehicle', 'latestPayment'])
            ->latest();

        if ($request->filled('from')) {
            $query->whereDate('pickup_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('pickup_date', '<=', $request->to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->get();

        $filename = 'bookings-report-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($bookings) {
            $handle = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($handle, [
                'Reference',
                'Customer Name',
                'Customer Email',
                'Vehicle',
                'Pickup Date',
                'Return Date',
                'Days',
                'Amount AFN',
                'Status',
                'Payment Method',
            ]);

            // CSV Rows
            foreach ($bookings as $booking) {
                $days = (int) Carbon::parse($booking->pickup_date)
                    ->diffInDays($booking->return_date) ?: 1;

                fputcsv($handle, [
                    $booking->reference_code,
                    $booking->customer?->name ?? '—',
                    $booking->customer?->email ?? '—',
                    $booking->vehicle?->full_name ?? '—',
                    $booking->pickup_date?->format('Y-m-d H:i'),
                    $booking->return_date?->format('Y-m-d H:i'),
                    $days,
                    number_format($booking->total_amount, 2),
                    ucfirst($booking->status),
                    ucfirst(str_replace('_', ' ', $booking->latestPayment?->method ?? '—')),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Export PDF ───────────────────────────────────────────────────────────

    public function exportPdf(Request $request): Response
    {
        $query = Booking::with(['customer', 'vehicle', 'latestPayment'])
            ->latest();

        if ($request->filled('from')) {
            $query->whereDate('pickup_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('pickup_date', '<=', $request->to);
        }

        $bookings = $query->get();

        $totalRevenue = $bookings
            ->filter(fn($b) => $b->latestPayment?->status === 'paid')
            ->sum('total_amount');

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'bookings' => $bookings,
            'totalRevenue' => $totalRevenue,
            'from' => $request->from,
            'to' => $request->to,
            'generatedAt' => now()->format('M d, Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'bookings-report-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}