<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
            'full_name' => $this->full_name,
            'year' => $this->year,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->icon,
            ]),
            'license_plate' => $this->license_plate,
            'color' => $this->color,
            'seats' => $this->seats,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'status' => $this->status,
            'odometer' => $this->odometer,
            'description' => $this->description,
            'features' => $this->features ?? [],
            'thumbnail' => $this->thumbnail
                ? asset('storage/' . $this->thumbnail)
                : null,
            'images' => $this->whenLoaded(
                'images',
                fn() =>
                $this->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->path),
                    'order' => $img->order,
                ])
            ),
            'pricing_rules' => $this->whenLoaded(
                'pricingRules',
                fn() =>
                $this->pricingRules->map(fn($rule) => [
                    'id' => $rule->id,
                    'type' => $rule->type,
                    'base_rate' => (float) $rule->base_rate,
                    'currency' => $rule->currency,
                    'multiplier' => (float) $rule->multiplier,
                    'date_from' => $rule->date_from?->toDateString(),
                    'date_to' => $rule->date_to?->toDateString(),
                    'is_active' => $rule->is_active,
                ])
            ),
            'daily_rate' => $this->whenLoaded('pricingRules', fn() => (float) (
                $this->pricingRules
                    ->where('type', 'daily')
                    ->where('is_active', true)
                    ->first()
                        ?->base_rate ?? 0
            )),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}