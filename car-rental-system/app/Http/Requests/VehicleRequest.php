<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicleId)->whereNull('deleted_at'),
            ],
            'color' => ['required', 'string', 'max:50'],
            'seats' => ['required', 'integer', 'min:1', 'max:9'],
            'fuel_type' => ['required', Rule::in(['petrol', 'diesel', 'electric', 'hybrid'])],
            'transmission' => ['required', Rule::in(['manual', 'automatic'])],
            'status' => ['required', Rule::in(['available', 'booked', 'maintenance'])],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            // Pricing rules
            'pricing_rules' => ['nullable', 'array'],
            'pricing_rules.*.type' => ['required', Rule::in(['hourly', 'daily', 'weekly', 'monthly'])],
            'pricing_rules.*.base_rate' => ['required', 'numeric', 'min:0'],
            'pricing_rules.*.currency' => ['nullable', 'string', 'size:3'],
            'pricing_rules.*.date_from' => ['nullable', 'date'],
            'pricing_rules.*.date_to' => ['nullable', 'date', 'after_or_equal:pricing_rules.*.date_from'],
            'pricing_rules.*.multiplier' => ['nullable', 'numeric', 'min:0.01', 'max:99.99'],
            'pricing_rules.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_plate.unique' => 'This license plate is already registered.',
            'category_id.exists' => 'The selected category does not exist.',
            'year.min' => 'Vehicle year must be 1990 or later.',
        ];
    }
}