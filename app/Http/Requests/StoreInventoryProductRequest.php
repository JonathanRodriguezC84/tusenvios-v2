<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\InventoryProduct::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:80'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'stock_minimum' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
