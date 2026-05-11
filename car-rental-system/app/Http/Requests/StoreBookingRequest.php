<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'pickup_date' => [
                'required',
                'date',
                'after:' . now()->addHour()->toDateTimeString(),
            ],
            'return_date' => [
                'required',
                'date',
                'after:pickup_date',
                'before:' . now()->addDays(90)->toDateTimeString(),
            ],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'return_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', Rule::in(['stripe', 'paypal', 'counter'])],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_date.after' => 'Pickup date must be at least 1 hour from now.',
            'return_date.after' => 'Return date must be after pickup date.',
            'return_date.before' => 'Booking cannot exceed 90 days.',
            'vehicle_id.exists' => 'The selected vehicle does not exist.',
        ];
    }
}