<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_code' => $this->reference_code,
            'status' => $this->status,
            'pickup_date' => $this->pickup_date?->toISOString(),
            'return_date' => $this->return_date?->toISOString(),
            'actual_return_date' => $this->actual_return_date?->toISOString(),
            'pickup_location' => $this->pickup_location,
            'return_location' => $this->return_location,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'payment_method' => $this->payment_method,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'can_be_cancelled' => $this->canBeCancelled(),
            'duration_days' => $this->duration_in_days,
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ]),
            'vehicle' => $this->whenLoaded('vehicle', fn() => [
                'id' => $this->vehicle->id,
                'full_name' => $this->vehicle->full_name,
                'brand' => $this->vehicle->brand,
                'model' => $this->vehicle->model,
                'year' => $this->vehicle->year,
                'thumbnail' => $this->vehicle->thumbnail
                    ? asset('storage/' . $this->vehicle->thumbnail)
                    : null,
                'license_plate' => $this->vehicle->license_plate,
            ]),
            'latest_payment' => $this->whenLoaded(
                'latestPayment',
                fn() =>
                $this->latestPayment ? [
                    'id' => $this->latestPayment->id,
                    'status' => $this->latestPayment->status,
                    'method' => $this->latestPayment->method,
                    'amount' => (float) $this->latestPayment->amount,
                    'transaction_id' => $this->latestPayment->transaction_id,
                    'paid_at' => $this->latestPayment->paid_at?->toISOString(),
                ] : null
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}