<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
        $query = Booking::with([
            'customer:id,name,email,phone,driver_license_number',
            'vehicle:id,brand,model,year,license_plate',
            'payments',
        ]);

        // Default date range: first day of current month to today
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to = $request->to ?? now()->format('Y-m-d');

        $query->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->whereHas(
                'payments',
                fn($q) =>
                $q->where('method', $request->payment_method)
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_code', 'like', '%' . $search . '%')
                    ->orWhereHas(
                        'customer',
                        fn($q) =>
                        $q->where('name', 'like', '%' . $search . '%')
                    );
            });
        }

        $bookings = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats on filtered set (before pagination)
        $statsQuery = Booking::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if ($request->filled('status') && $request->status !== 'all') {
            $statsQuery->where('status', $request->status);
        }

        $totalRevenue = (clone $statsQuery)
            ->whereHas('payments', fn($q) => $q->where('status', 'paid'))
            ->with(['payments' => fn($q) => $q->where('status', 'paid')])
            ->get()
            ->sum(fn($b) => $b->payments->sum('amount'));

        $confirmedCount = (clone $statsQuery)->where('status', 'confirmed')->count();

        return view('admin.reports.index', compact(
            'bookings',
            'totalRevenue',
            'confirmedCount',
            'from',
            'to',
        ));
    }

    // ─── Export CSV ───────────────────────────────────────────────────────────

    public function exportCsv(Request $request): StreamedResponse
    {
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to = $request->to ?? now()->format('Y-m-d');

        $bookings = Booking::with(['customer', 'vehicle', 'payments'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when(
                $request->filled('status') && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('payment_method') && $request->payment_method !== 'all',
                fn($q) => $q->whereHas(
                    'payments',
                    fn($q2) =>
                    $q2->where('method', $request->payment_method)
                )
            )
            ->orderByDesc('created_at')
            ->get();

        $filename = 'bookings-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Reference',
                'Customer Name',
                'Email',
                'Phone',
                'License Number',
                'Vehicle',
                'License Plate',
                'Pickup Date',
                'Return Date',
                'Days',
                'Total AFN',
                'Payment Method',
                'Payment Status',
                'Booking Status',
                'Created At',
            ]);

            foreach ($bookings as $b) {
                $payment = $b->payments->first();
                $days = $b->pickup_date?->diffInDays($b->return_date) ?? 0;

                fputcsv($handle, [
                    $b->reference_code,
                    $b->customer?->name ?? '',
                    $b->customer?->email ?? '',
                    $b->customer?->phone ?? '',
                    $b->customer?->driver_license_number ?? '',
                    ($b->vehicle?->brand ?? '') . ' ' . ($b->vehicle?->model ?? ''),
                    $b->vehicle?->license_plate ?? '',
                    $b->pickup_date?->format('Y-m-d H:i'),
                    $b->return_date?->format('Y-m-d H:i'),
                    $days,
                    number_format($b->total_amount, 2),
                    ucfirst(str_replace('_', ' ', $payment?->method ?? 'N/A')),
                    ucfirst($payment?->status ?? 'N/A'),
                    ucfirst($b->status),
                    $b->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ─── Export PDF ───────────────────────────────────────────────────────────

    public function exportPdf(Request $request): Response
    {
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to = $request->to ?? now()->format('Y-m-d');

        $bookings = Booking::with(['customer', 'vehicle', 'payments'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when(
                $request->filled('status') && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status)
            )
            ->orderByDesc('created_at')
            ->get();

        $totalRevenue = $bookings
            ->filter(fn($b) => $b->payments->where('status', 'paid')->isNotEmpty())
            ->sum(fn($b) => $b->payments->where('status', 'paid')->sum('amount'));

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'bookings' => $bookings,
            'totalRevenue' => $totalRevenue,
            'from' => $from,
            'to' => $to,
            'generatedAt' => now()->format('M d, Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('bookings-report-' . now()->format('Y-m-d') . '.pdf');
    }
}