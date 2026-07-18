<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('movement', \App\Models\InventoryProduct::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:manual_in,manual_out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:180'],
        ];
    }
}
