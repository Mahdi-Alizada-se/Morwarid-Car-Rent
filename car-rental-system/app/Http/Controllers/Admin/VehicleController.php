<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\PricingRule;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

class VehicleController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Vehicle::with(['category', 'pricingRules' => fn($q) => $q->where('type', 'daily')->where('is_active', true)])
            ->withTrashed(false);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $vehicles = $query->latest()->paginate(15)->withQueryString();
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.vehicles.index', compact('vehicles', 'categories'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────────

    public function create(): View
    {
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.vehicles.create', compact('categories'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────────

    public function store(VehicleRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Parse features string to array
            if (!empty($data['features']) && is_string($data['features'])) {
                $data['features'] = array_filter(array_map('trim', explode(',', $data['features'])));
            }

            // Upload thumbnail
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->processAndStore($request->file('thumbnail'));
            }

            unset($data['images'], $data['pricing_rules']);
            $vehicle = Vehicle::create($data);

            // Upload gallery images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $this->processAndStore($image);
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id,
                        'path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Save pricing rules
            if (!empty($request->pricing_rules)) {
                foreach ($request->pricing_rules as $rule) {
                    if (empty($rule['base_rate'])) {
                        continue;
                    }
                    PricingRule::create([
                        'vehicle_id' => $vehicle->id,
                        'type' => $rule['type'],
                        'base_rate' => $rule['base_rate'],
                        'currency' => $rule['currency'] ?? 'AFN',
                        'date_from' => $rule['date_from'] ?? null,
                        'date_to' => $rule['date_to'] ?? null,
                        'multiplier' => $rule['multiplier'] ?? 1.00,
                        'is_active' => isset($rule['is_active']) ? true : false,
                    ]);
                }
            }

            return redirect()
                ->route('admin.vehicles.index')
                ->with('success', __('Vehicle created successfully.'));
        });
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────────

    public function edit(Vehicle $vehicle): View
    {
        $vehicle->load(['images', 'pricingRules']);
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.vehicles.edit', compact('vehicle', 'categories'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────────

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        return DB::transaction(function () use ($request, $vehicle) {
            $data = $request->validated();

            if (!empty($data['features']) && is_string($data['features'])) {
                $data['features'] = array_filter(array_map('trim', explode(',', $data['features'])));
            }

            if ($request->hasFile('thumbnail')) {
                if ($vehicle->thumbnail) {
                    Storage::disk('public')->delete($vehicle->thumbnail);
                }
                $data['thumbnail'] = $this->processAndStore($request->file('thumbnail'));
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
                    $path = $this->processAndStore($image);
                    VehicleImage::create([
                        'vehicle_id' => $vehicle->id,
                        'path' => $path,
                        'order' => $index,
                    ]);
                }
            }

            // Sync pricing rules
            if ($request->has('pricing_rules')) {
                $vehicle->pricingRules()->delete();
                foreach ($request->pricing_rules ?? [] as $rule) {
                    if (empty($rule['base_rate'])) {
                        continue;
                    }
                    PricingRule::create([
                        'vehicle_id' => $vehicle->id,
                        'type' => $rule['type'],
                        'base_rate' => $rule['base_rate'],
                        'currency' => $rule['currency'] ?? 'AFN',
                        'date_from' => $rule['date_from'] ?? null,
                        'date_to' => $rule['date_to'] ?? null,
                        'multiplier' => $rule['multiplier'] ?? 1.00,
                        'is_active' => isset($rule['is_active']) ? true : false,
                    ]);
                }
            }

            return redirect()
                ->route('admin.vehicles.index')
                ->with('success', __('Vehicle updated successfully.'));
        });
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────────

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete(); // soft delete

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', __('Vehicle deleted successfully.'));
    }

    // ─── Toggle Status ────────────────────────────────────────────────────────────

    public function toggleStatus(Vehicle $vehicle): RedirectResponse
    {
        $newStatus = $vehicle->status === 'available' ? 'maintenance' : 'available';

        // Don't toggle if vehicle is currently booked
        if ($vehicle->status === 'booked') {
            return back()->with('error', __('Cannot change status of a currently booked vehicle.'));
        }

        $vehicle->update(['status' => $newStatus]);

        return back()->with('success', __('Vehicle status updated to :status.', ['status' => $newStatus]));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    private function processAndStore($file): string
    {
        $filename = 'vehicles/' . uniqid() . '.webp';
        $image = Image::read($file)->cover(800, 600)->toWebp(85);
        Storage::disk('public')->put($filename, $image);
        return $filename;
    }
}