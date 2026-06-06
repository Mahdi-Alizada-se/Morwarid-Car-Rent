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
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:pickup_date'],
            'payment_method' => ['nullable', 'string', 'in:cash,bank_transfer,mastercard,counter,online'],
            'bank_reference' => ['nullable', 'string', 'max:255'],
            'bank_sender_name' => ['nullable', 'string', 'max:255'],
            'card_name' => ['nullable', 'string', 'max:255'],
            'card_last_four' => ['nullable', 'string', 'max:4'],
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