<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Http\Resources\VehicleCollection;
use App\Http\Resources\VehicleResource;
use App\Models\PricingRule;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Services\PricingCalculator;
use App\Services\VehicleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;


class VehicleController extends Controller
{
    public function __construct(
        private PricingCalculator $pricing,
        private VehicleAvailabilityService $availability,
    ) {
    }

    // ─── Public: List Vehicles ────────────────────────────────────────────────────

    public function index(Request $request): VehicleCollection
    {
        $query = Vehicle::with(['category', 'images', 'pricingRules' => fn($q) => $q->where('is_active', true)])
            ->whereNull('deleted_at');

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Availability filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->date_from);
            $to = Carbon::parse($request->date_to);

            $bookedIds = \App\Models\Booking::whereNotIn('status', [\App\Models\Booking::STATUS_CANCELLED])
                ->where('pickup_date', '<', $to)
                ->where('return_date', '>', $from)
                ->pluck('vehicle_id');

            $query->whereNotIn('id', $bookedIds);
        }

        // Price filter via daily pricing rules
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('pricingRules', function ($q) use ($request) {
                $q->where('type', 'daily')->where('is_active', true);
                if ($request->filled('min_price')) {
                    $q->where('base_rate', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('base_rate', '<=', $request->max_price);
                }
            });
        }

        // Sorting
        switch ($request->sort_by) {
            case 'price_asc':
                $query->leftJoin('pricing_rules', function ($join) {
                    $join->on('vehicles.id', '=', 'pricing_rules.vehicle_id')
                        ->where('pricing_rules.type', 'daily')
                        ->where('pricing_rules.is_active', true);
                })->orderBy('pricing_rules.base_rate', 'asc')->select('vehicles.*');
                break;
            case 'price_desc':
                $query->leftJoin('pricing_rules', function ($join) {
                    $join->on('vehicles.id', '=', 'pricing_rules.vehicle_id')
                        ->where('pricing_rules.type', 'daily')
                        ->where('pricing_rules.is_active', true);
                })->orderBy('pricing_rules.base_rate', 'desc')->select('vehicles.*');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        return new VehicleCollection($query->paginate(12));
    }

    // ─── Public: Show Vehicle ─────────────────────────────────────────────────────

    public function show(Request $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle->load(['category', 'images', 'pricingRules' => fn($q) => $q->where('is_active', true)]);

        $calendar = $this->availability->getVehicleCalendar($vehicle);

        $pricePreview = null;
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->date_from);
            $to = Carbon::parse($request->date_to);
            $pricePreview = $this->pricing->calculate($vehicle, $from, $to);
        }

        return response()->json([
            'vehicle' => new VehicleResource($vehicle),
            'calendar' => $calendar,
            'price_preview' => $pricePreview,
        ]);
    }

    // ─── Admin: Create Vehicle ────────────────────────────────────────────────────

    public function store(VehicleRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Handle features string → array
            if (isset($data['features']) && is_string($data['features'])) {
                $data['features'] = array_map('trim', explode(',', $data['features']));
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadThumbnail($request->file('thumbnail'));
            }

            unset($data['images'], $data['pricing_rules']);

            $vehicle = Vehicle::create($data);

            // Handle gallery images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $this->uploadImage($image);
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id,
                        'path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Handle pricing rules
            if (!empty($request->pricing_rules)) {
                foreach ($request->pricing_rules as $rule) {
                    PricingRule::create([
                        'vehicle_id' => $vehicle->id,
                        'type' => $rule['type'],
                        'base_rate' => $rule['base_rate'],
                        'currency' => $rule['currency'] ?? 'AFN',
                        'date_from' => $rule['date_from'] ?? null,
                        'date_to' => $rule['date_to'] ?? null,
                        'multiplier' => $rule['multiplier'] ?? 1.00,
                        'is_active' => $rule['is_active'] ?? true,
                    ]);
                }
            }

            $vehicle->load(['category', 'images', 'pricingRules']);

            return response()->json([
                'message' => 'Vehicle created successfully.',
                'vehicle' => new VehicleResource($vehicle),
            ], 201);
        });
    }

    // ─── Admin: Update Vehicle ────────────────────────────────────────────────────

    public function update(VehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        return DB::transaction(function () use ($request, $vehicle) {
            $data = $request->validated();

            if (isset($data['features']) && is_string($data['features'])) {
                $data['features'] = array_map('trim', explode(',', $data['features']));
            }

            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($vehicle->thumbnail) {
                    Storage::disk('public')->delete($vehicle->thumbnail);
                }
                $data['thumbnail'] = $this->uploadThumbnail($request->file('thumbnail'));
            }

            unset($data['images'], $data['pricing_rules']);
            $vehicle->update($data);

            // Replace gallery images if new ones uploaded
            if ($request->hasFile('images')) {
                foreach ($vehicle->images as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
                foreach ($request->file('images') as $index => $image) {
                    $path = $this->uploadImage($image);
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id,
                        'path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Sync pricing rules
            if (isset($request->pricing_rules)) {
                $vehicle->pricingRules()->delete();
                foreach ($request->pricing_rules as $rule) {
                    PricingRule::create([
                        'vehicle_id' => $vehicle->id,
                        'type' => $rule['type'],
                        'base_rate' => $rule['base_rate'],
                        'currency' => $rule['currency'] ?? 'AFN',
                        'date_from' => $rule['date_from'] ?? null,
                        'date_to' => $rule['date_to'] ?? null,
                        'multiplier' => $rule['multiplier'] ?? 1.00,
                        'is_active' => $rule['is_active'] ?? true,
                    ]);
                }
            }

            $vehicle->load(['category', 'images', 'pricingRules']);

            return response()->json([
                'message' => 'Vehicle updated successfully.',
                'vehicle' => new VehicleResource($vehicle),
            ]);
        });
    }

    // ─── Admin: Delete Vehicle ────────────────────────────────────────────────────

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete(); // soft delete

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
        ]);
    }

    // ─── Public: Check Availability ──────────────────────────────────────────────

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
        ]);

        $vehicle = Vehicle::with(['pricingRules' => fn($q) => $q->where('is_active', true)])->findOrFail($request->vehicle_id);
        $from = Carbon::parse($request->pickup_date);
        $to = Carbon::parse($request->return_date);
        $available = $this->availability->isAvailable($vehicle->id, $from, $to);
        $price = $available ? $this->pricing->calculate($vehicle, $from, $to) : null;

        return response()->json([
            'available' => $available,
            'price' => $price,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    private function uploadThumbnail($file): string
    {
        $filename = 'vehicles/thumb_' . uniqid() . '.webp';
        $image = Image::read($file)->cover(800, 600)->toWebp(85);
        Storage::disk('public')->put($filename, $image);
        return $filename;
    }

    private function uploadImage($file): string
    {
        // Create directory if it does not exist
        $directory = storage_path('app/public/vehicles');
        if (!file_exists($directory)) {
            mkdir($directory, 0775, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'vehicles/' . uniqid() . '.' . $extension;

        Storage::disk('public')->putFileAs(
            'vehicles',
            $file,
            basename($filename)
        );

        return $filename;
    }
}