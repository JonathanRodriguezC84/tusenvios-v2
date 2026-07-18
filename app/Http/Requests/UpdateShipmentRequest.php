<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('shipment'));
    }

    public function rules(): array
    {
        return [
            'affiliated_company_id' => ['nullable', 'exists:affiliated_companies,id'],
            'service_type' => ['required', 'string', 'max:50'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['nullable', 'string', 'max:50'],
            'sender_address' => ['required', 'string', 'max:255'],
            'sender_neighborhood' => ['nullable', 'string', 'max:255'],
            'sender_locality' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_lastname' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'recipient_alt_phone' => ['nullable', 'string', 'max:50'],
            'recipient_address' => ['required', 'string', 'max:255'],
            'recipient_neighborhood' => ['nullable', 'string', 'max:255'],
            'recipient_locality' => ['nullable', 'string', 'max:255'],
            'recipient_city' => ['nullable', 'string', 'max:255'],
            'package_type' => ['required', 'string', 'max:50'],
            'pieces' => ['required', 'integer', 'min:1'],
            'content_description' => ['nullable', 'string', 'max:1000'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'shipping_value' => ['nullable', 'numeric', 'min:0'],
            'delivery_zone_id' => ['nullable', 'exists:delivery_zones,id'],
            'payment_method' => ['required', 'string', 'max:50'],
            'collection_value' => ['nullable', 'numeric', 'min:0'],
            'zone' => ['nullable', 'string', 'max:255'],
            'recipient_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
