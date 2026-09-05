<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Shipment::class);
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        $tenant = $user?->tenant;

        $this->merge([
            'sender_name' => $this->filled('sender_name') ? $this->input('sender_name') : ($tenant?->name ?: 'Tus Envios'),
            'sender_phone' => $this->filled('sender_phone') ? $this->input('sender_phone') : ($tenant?->phone ?: '3000000000'),
            'sender_address' => $this->filled('sender_address') ? $this->input('sender_address') : 'Bodega principal Bogota',
            'sender_locality' => $this->filled('sender_locality') ? $this->input('sender_locality') : 'Bogota',
            'package_type' => $this->input('package_type') ?: 'package',
            'pieces' => $this->filled('pieces') ? max((int) $this->input('pieces'), 1) : 1,
            'payment_method' => $this->input('payment_method') ?: 'cod',
        ]);
    }

    public function rules(): array
    {
        return [
            'sender_name' => ['nullable', 'string', 'max:255'],
            'sender_phone' => ['nullable', 'string', 'max:50'],
            'sender_address' => ['nullable', 'string', 'max:255'],
            'sender_neighborhood' => ['nullable', 'string', 'max:255'],
            'sender_locality' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_lastname' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:50'],
            'recipient_alt_phone' => ['nullable', 'string', 'max:50'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'recipient_address' => ['required', 'string', 'max:100'],
            'recipient_neighborhood' => ['nullable', 'string', 'max:255'],
            'recipient_department' => ['nullable', 'string', 'max:255'],
            'recipient_locality' => ['required', 'string', 'max:255'],
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
            'recipient_notes' => ['nullable', 'string', 'max:90'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'max' => 'El campo :attribute no debe superar los :max caracteres.',
            'min' => 'El campo :attribute debe ser de al menos :min.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'recipient_name.required' => 'El nombre del cliente es obligatorio.',
            'recipient_phone.required' => 'El teléfono del cliente es obligatorio.',
            'recipient_address.required' => 'La dirección de entrega es obligatoria.',
            'recipient_address.max' => 'La dirección de entrega no puede superar los 100 caracteres.',
            'recipient_locality.required' => 'Debes seleccionar la ciudad de entrega.',
            'package_type.required' => 'El tipo de paquete es obligatorio.',
            'payment_method.required' => 'La forma de pago es obligatoria.',
            'pieces.required' => 'La cantidad de piezas es obligatoria.',
            'pieces.min' => 'La cantidad de piezas debe ser al menos 1.',
        ];
    }

    public function attributes(): array
    {
        return [
            'recipient_name' => 'nombre del cliente',
            'recipient_lastname' => 'apellidos del cliente',
            'recipient_phone' => 'teléfono del cliente',
            'recipient_alt_phone' => 'whatsapp del cliente',
            'recipient_address' => 'dirección de entrega',
            'recipient_neighborhood' => 'barrio',
            'recipient_locality' => 'ciudad de entrega',
            'recipient_department' => 'departamento',
            'recipient_city' => 'localidad',
            'package_type' => 'tipo de paquete',
            'pieces' => 'piezas',
            'payment_method' => 'forma de pago',
            'collection_value' => 'valor a recaudar',
            'shipping_value' => 'valor del envío',
            'declared_value' => 'valor declarado',
            'content_description' => 'descripción del contenido',
            'sender_name' => 'nombre del remitente',
            'sender_phone' => 'teléfono del remitente',
            'sender_address' => 'dirección del remitente',
        ];
    }
}
