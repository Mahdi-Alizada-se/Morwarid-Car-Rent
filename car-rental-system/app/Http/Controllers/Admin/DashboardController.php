<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    // ─── JSON: Stats ──────────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->analytics->getDashboardStats()
        );
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