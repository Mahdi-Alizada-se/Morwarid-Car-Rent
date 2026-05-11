<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\VehicleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct(
        private VehicleAvailabilityService $availability,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Vehicle::with([
            'category',
            'pricingRules' => fn($q) => $q->where('type', 'daily')->where('is_active', true),
        ])->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_ids')) {
            $query->whereIn('category_id', $request->category_ids);
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->filled('fuel_types')) {
            $query->whereIn('fuel_type', $request->fuel_types);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('pricingRules', function ($q) use ($request) {
                $q->where('type', 'daily')->where('is_active', true);
                if ($request->filled('min_price'))
                    $q->where('base_rate', '>=', $request->min_price);
                if ($request->filled('max_price'))
                    $q->where('base_rate', '<=', $request->max_price);
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->date_from);
            $to = Carbon::parse($request->date_to);

            $bookedIds = \App\Models\Booking::whereNotIn('status', [\App\Models\Booking::STATUS_CANCELLED])
                ->where('pickup_date', '<', $to)
                ->where('return_date', '>', $from)
                ->pluck('vehicle_id');

            $query->whereNotIn('id', $bookedIds);
        }

        switch ($request->sort_by) {
            case 'price_asc':
                $query->leftJoin(
                    'pricing_rules as pr',
                    fn($j) =>
                    $j->on('vehicles.id', '=', 'pr.vehicle_id')
                        ->where('pr.type', 'daily')->where('pr.is_active', true)
                )->orderBy('pr.base_rate')->select('vehicles.*');
                break;
            case 'price_desc':
                $query->leftJoin(
                    'pricing_rules as pr',
                    fn($j) =>
                    $j->on('vehicles.id', '=', 'pr.vehicle_id')
                        ->where('pr.type', 'daily')->where('pr.is_active', true)
                )->orderByDesc('pr.base_rate')->select('vehicles.*');
                break;
            default:
                $query->latest();
        }

        $vehicles = $query->paginate(12)->withQueryString();
        $categories = VehicleCategory::withCount('vehicles')->where('is_active', true)->orderBy('name')->get();

        return view('vehicles.index', compact('vehicles', 'categories'));
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load(['category', 'images', 'pricingRules' => fn($q) => $q->where('is_active', true)]);
        $bookedDates = $this->availability->getBookedDates($vehicle);

        return view('vehicles.show', compact('vehicle', 'bookedDates'));
    }
}