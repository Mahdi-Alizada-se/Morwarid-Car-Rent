<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // ─── Dashboard Stats ──────────────────────────────────────────────────────

    public function getDashboardStats(): array
    {
        return Cache::remember('analytics:dashboard_stats', 120, function () {
            $today = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();

            return [
                // Vehicles
                'total_vehicles' => Vehicle::whereNull('deleted_at')->count(),
                'available_vehicles' => Vehicle::where('status', 'available')->whereNull('deleted_at')->count(),
                'booked_vehicles' => Vehicle::where('status', 'booked')->whereNull('deleted_at')->count(),
                'maintenance_vehicles' => Vehicle::where('status', 'maintenance')->whereNull('deleted_at')->count(),

                // Bookings
                'bookings_today' => Booking::whereDate('created_at', $today)->count(),
                'bookings_this_month' => Booking::where('created_at', '>=', $monthStart)->count(),
                'active_rentals' => Booking::where('status', 'active')->count(),
                'pending_confirmations' => Booking::where('status', 'pending')->count(),
                'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
                'completed_bookings' => Booking::where('status', 'completed')->count(),
                'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),

                // Revenue
                'revenue_today_afn' => (float) Payment::where('status', 'paid')
                    ->whereDate('paid_at', $today)
                    ->sum('amount'),

                'revenue_this_month_afn' => (float) Payment::where('status', 'paid')
                    ->where('paid_at', '>=', $monthStart)
                    ->sum('amount'),

                'revenue_total_afn' => (float) Payment::where('status', 'paid')
                    ->sum('amount'),

                // Alerts
                'pending_receipts' => Payment::where('status', 'receipt_uploaded')
                    ->whereHas('booking', fn($q) => $q->whereNotIn('status', ['cancelled', 'completed']))
                    ->count(),
                'unread_chats' => Message::where('is_read', false)
                    ->whereHas('sender', fn($q) => $q->where('role', 'customer'))
                    ->count(),

                // Customers
                'new_customers_this_month' => User::where('role', 'customer')
                    ->where('created_at', '>=', $monthStart)
                    ->count(),
            ];
        });
    }

    // ─── Revenue Chart ────────────────────────────────────────────────────────

    public function getRevenueChart(string $period = 'monthly', int $limit = 12): array
    {
        $cacheKey = "analytics:revenue_chart_{$period}_{$limit}";

        return Cache::remember($cacheKey, 600, function () use ($period, $limit) {
            $query = Payment::where('status', 'paid')
                ->whereNotNull('paid_at');

            switch ($period) {
                case 'daily':
                    $results = $query
                        ->select(
                            DB::raw('DATE(paid_at) as period'),
                            DB::raw('SUM(amount) as total')
                        )
                        ->where('paid_at', '>=', now()->subDays($limit))
                        ->groupBy('period')
                        ->orderBy('period')
                        ->get();

                    $labels = $results->pluck('period')
                        ->map(fn($d) => Carbon::parse($d)->format('M d'))
                        ->toArray();
                    break;

                case 'weekly':
                    $results = $query
                        ->select(
                            DB::raw('YEARWEEK(paid_at, 1) as period'),
                            DB::raw('SUM(amount) as total'),
                            DB::raw('MIN(paid_at) as week_start')
                        )
                        ->where('paid_at', '>=', now()->subWeeks($limit))
                        ->groupBy('period')
                        ->orderBy('period')
                        ->get();

                    $labels = $results->pluck('week_start')
                        ->map(fn($d) => 'W/C ' . Carbon::parse($d)->format('M d'))
                        ->toArray();
                    break;

                case 'monthly':
                default:
                    $results = $query
                        ->select(
                            DB::raw('DATE_FORMAT(paid_at, "%Y-%m") as period'),
                            DB::raw('SUM(amount) as total')
                        )
                        ->where('paid_at', '>=', now()->subMonths($limit))
                        ->groupBy('period')
                        ->orderBy('period')
                        ->get();

                    $labels = $results->pluck('period')
                        ->map(fn($d) => Carbon::parse($d . '-01')->format('M Y'))
                        ->toArray();
                    break;
            }

            $data = $results->pluck('total')->map(fn($v) => (float) $v)->toArray();
            $total = array_sum($data);

            return [
                'labels' => $labels,
                'data' => $data,
                'total' => $total,
                'period' => $period,
            ];
        });
    }

    // ─── Booking Status Chart ─────────────────────────────────────────────────

    public function getBookingStatusChart(): array
    {
        return Cache::remember('analytics:booking_status_chart', 300, function () {
            $statuses = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
            $counts = [];

            foreach ($statuses as $status) {
                $counts[] = Booking::where('status', $status)->count();
            }

            return [
                'labels' => ['Pending', 'Confirmed', 'Active', 'Completed', 'Cancelled'],
                'data' => $counts,
                'colors' => [
                    '#F59E0B',
                    '#3B82F6',
                    '#10B981',
                    '#6B7280',
                    '#EF4444',
                ],
            ];
        });
    }

    // ─── Top Vehicles ─────────────────────────────────────────────────────────

    public function getTopVehicles(int $limit = 5): array
    {
        return Cache::remember("analytics:top_vehicles_{$limit}", 600, function () use ($limit) {
            $totalDays = Carbon::now()->startOfYear()->diffInDays(now()) ?: 1;

            $vehicles = Booking::select(
                'vehicle_id',
                DB::raw('COUNT(*) as bookings_count'),
                DB::raw('SUM(total_amount) as revenue_afn'),
                DB::raw('SUM(DATEDIFF(return_date, pickup_date)) as total_days_booked')
            )
                ->whereNotIn('status', ['cancelled'])
                ->groupBy('vehicle_id')
                ->orderByDesc('bookings_count')
                ->limit($limit)
                ->with('vehicle')
                ->get();

            return $vehicles->map(function ($item) use ($totalDays) {
                $vehicle = $item->vehicle;
                $utilization = $totalDays > 0
                    ? round(($item->total_days_booked / $totalDays) * 100, 1)
                    : 0;
                $utilization = min($utilization, 100);

                return [
                    'vehicle_name' => $vehicle?->full_name ?? 'Unknown',
                    'brand' => $vehicle?->brand ?? '',
                    'model' => $vehicle?->model ?? '',
                    'bookings_count' => (int) $item->bookings_count,
                    'revenue_afn' => (float) $item->revenue_afn,
                    'utilization' => $utilization,
                ];
            })->toArray();
        });
    }

    // ─── Monthly Comparison ───────────────────────────────────────────────────

    public function getMonthlyComparison(): array
    {
        return Cache::remember('analytics:monthly_comparison', 3600, function () {
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Bookings
            $bookingsThis = Booking::where('created_at', '>=', $thisMonth)->count();
            $bookingsLast = Booking::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

            // Revenue
            $revenueThis = (float) Payment::where('status', 'paid')
                ->where('paid_at', '>=', $thisMonth)
                ->sum('amount');
            $revenueLast = (float) Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$lastMonth, $lastMonthEnd])
                ->sum('amount');

            // New Customers
            $customersThis = User::where('role', 'customer')
                ->where('created_at', '>=', $thisMonth)->count();
            $customersLast = User::where('role', 'customer')
                ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

            return [
                'bookings' => $this->buildComparison($bookingsThis, $bookingsLast),
                'revenue' => $this->buildComparison($revenueThis, $revenueLast),
                'customers' => $this->buildComparison($customersThis, $customersLast),
            ];
        });
    }

    // ─── Recent Bookings ──────────────────────────────────────────────────────

    public function getRecentBookings(int $limit = 10): Collection
    {
        return Booking::with(['customer', 'vehicle'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function buildComparison(float|int $this_val, float|int $last_val): array
    {
        $changePercent = 0;

        if ($last_val > 0) {
            $changePercent = round((($this_val - $last_val) / $last_val) * 100, 1);
        } elseif ($this_val > 0) {
            $changePercent = 100.0;
        }

        return [
            'this' => $this_val,
            'last' => $last_val,
            'change_percent' => $changePercent,
            'up' => $changePercent >= 0,
        ];
    }
}