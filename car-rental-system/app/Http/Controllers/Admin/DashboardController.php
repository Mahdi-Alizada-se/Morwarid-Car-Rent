<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {
    }

    // ─── Main Dashboard View ──────────────────────────────────────────────────

    public function index(): View
    {
        $stats = $this->analytics->getDashboardStats();
        $topVehicles = $this->analytics->getTopVehicles(5);
        $recentBookings = $this->analytics->getRecentBookings(10);
        $monthlyComparison = $this->analytics->getMonthlyComparison();
        $revenueData = $this->analytics->getRevenueChart('monthly', 12);
        $statusData = $this->analytics->getBookingStatusChart();

        return view('admin.dashboard', compact(
            'stats',
            'topVehicles',
            'recentBookings',
            'monthlyComparison',
            'revenueData',
            'statusData'
        ));
    }

    // ─── JSON: Stats (used by refresh button) ────────────────────────────────

    public function stats(): JsonResponse
    {
        // Clear cache so data is always fresh
        Cache::forget('analytics:dashboard_stats');

        $stats = $this->analytics->getDashboardStats();

        return response()->json([
            'success' => true,
            'data' => [
                'total_vehicles' => $stats['total_vehicles'] ?? 0,
                'available_vehicles' => $stats['available_vehicles'] ?? 0,
                'booked_vehicles' => $stats['booked_vehicles'] ?? 0,
                'maintenance_vehicles' => $stats['maintenance_vehicles'] ?? 0,
                'bookings_today' => $stats['bookings_today'] ?? 0,
                'bookings_this_month' => $stats['bookings_this_month'] ?? 0,
                'pending_confirmations' => $stats['pending_confirmations'] ?? 0,
                'confirmed_bookings' => $stats['confirmed_bookings'] ?? 0,
                'active_rentals' => $stats['active_rentals'] ?? 0,
                'completed_bookings' => $stats['completed_bookings'] ?? 0,
                'cancelled_bookings' => $stats['cancelled_bookings'] ?? 0,
                'revenue_today_afn' => $stats['revenue_today_afn'] ?? 0,
                'revenue_this_month_afn' => $stats['revenue_this_month_afn'] ?? 0,
                'revenue_total_afn' => $stats['revenue_total_afn'] ?? 0,
                'pending_receipts' => $stats['pending_receipts'] ?? 0,
                'unread_chats' => $stats['unread_chats'] ?? 0,
                'new_customers_this_month' => $stats['new_customers_this_month'] ?? 0,
            ],
        ]);
    }

    // ─── JSON: Revenue Chart ──────────────────────────────────────────────────

    public function revenueChart(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');

        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'monthly';
        }

        return response()->json(
            $this->analytics->getRevenueChart($period)
        );
    }

    // ─── JSON: Bookings Chart ─────────────────────────────────────────────────

    public function bookingsChart(): JsonResponse
    {
        return response()->json(
            $this->analytics->getBookingStatusChart()
        );
    }
}