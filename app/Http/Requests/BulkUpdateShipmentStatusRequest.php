<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bulkUpdateStatus', \App\Models\Shipment::class);
    }

    public function rules(): array
    {
        return [
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['integer', 'exists:shipments,id'],
            'status' => ['required', 'string', 'max:50'],
        ];
    }
}
