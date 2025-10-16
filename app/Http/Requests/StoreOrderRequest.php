<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest to validate order creation
 *
 * Expected request body:
 *  - client_id: int (client FK)
 *  - items: array with items {name, quantity, unit_price}
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // For the purposes of this challenge, every user can create orders
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
