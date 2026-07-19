@php
    $prefillRecipient = $prefillRecipient ?? null;
    $prefillQuickProduct = $prefillQuickProduct ?? null;
    $prefillQuickProductPayload = $prefillQuickProduct ? [
        'name' => $prefillQuickProduct->name,
        'package_type' => $prefillQuickProduct->package_type,
        'price' => (int) $prefillQuickProduct->price,
    ] : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            eyebrow="Guias"
            title="Nueva guia"
            description="Registra un envio con datos del cliente, productos y valor a recaudar."
        />
    </x-slot>

    <div class="h-full flex flex-col p-3 lg:p-5" x-data="shipmentCreateForm()" x-init="init()">
        <form method="POST" action="{{ route('shipments.store') }}" class="flex-1 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-4 te-create-form">
            @csrf

            <input type="hidden" name="service_type" value="{{ old('service_type', 'standard') }}">
            <input id="sender_name" type="hidden" name="sender_name" value="{{ old('sender_name', $senderPresets['default']['name'] ?? $senderPresets['rci']['name'] ?? 'Tus Envios') }}">
            <input id="sender_phone" type="hidden" name="sender_phone" value="{{ old('sender_phone', $senderPresets['default']['phone'] ?? $senderPresets['rci']['phone'] ?? '') }}">
            <input id="sender_address" type="hidden" name="sender_address" value="{{ old('sender_address', $senderPresets['default']['address'] ?? $senderPresets['rci']['address'] ?? 'Direccion principal') }}">
            <input id="sender_neighborhood" type="hidden" name="sender_neighborhood" value="{{ old('sender_neighborhood', $senderPresets['default']['neighborhood'] ?? $senderPresets['rci']['neighborhood'] ?? '') }}">
            <input id="sender_locality" type="hidden" name="sender_locality" value="{{ old('sender_locality', $senderPresets['default']['locality'] ?? $senderPresets['rci']['locality'] ?? 'Bogota') }}">
            <input type="hidden" name="affiliated_company_id" value="{{ old('affiliated_company_id', '') }}">
            <input type="hidden" id="sender_preset" name="sender_preset" value="{{ old('sender_preset', old('affiliated_company_id') ? 'company_'.old('affiliated_company_id') : 'default') }}">
            <input type="hidden" name="declared_value" value="{{ old('declared_value', 0) }}">
            <input type="hidden" id="pieces" name="pieces" value="{{ old('pieces', 1) }}">
            <input type="hidden" id="content_description" name="content_description" value="{{ old('content_description') }}">
            <input type="hidden" id="inventory_items" name="inventory_items" value="{{ old('inventory_items', '[]') }}">
            <select id="delivery_zone_id" name="delivery_zone_id" class="hidden">
                <option value="">Manual</option>
                @foreach ($deliveryZones as $zone)
                    <option value="{{ $zone->id }}" data-price="{{ (int) $zone->price }}" @selected(old('delivery_zone_id') == $zone->id)>{{ $zone->name }} - ${{ number_format($zone->price, 0, ',', '.') }}</option>
                @endforeach
            </select>

            <div class="min-w-0 space-y-3">
                <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-blue-700">Crear guia</p>
                            <h2 class="text-lg font-black text-gray-950">Completa la guia en 2 pasos</h2>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5 text-xs font-black" aria-label="Pasos para crear guia">
                            <template x-for="step in steps" :key="step.key">
                                <span class="rounded-lg border border-blue-100 bg-blue-50 px-2 py-2 text-center text-blue-800" x-text="step.short"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="te-step-columns">
            {{-- COLUMN 1: Customer Info --}}
            <div data-step-panel="client" class="te-create-column rounded-xl border border-gray-200 shadow-sm bg-white p-4">
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ $errors->first('inventory_items') ?: $errors->first() ?: 'Revisa los campos antes de guardar.' }}</div>
                @endif

                <div>
                    <h3 class="text-sm font-black text-gray-950">1. Datos del cliente</h3>
                    <p class="mt-0.5 text-xs font-semibold text-gray-500">Solo necesitamos nombre, telefono y direccion para empezar.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="relative" x-data="recipientAutocomplete()" x-init="init()">
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombres</label>
                        <input name="recipient_name" x-model="preview.recipient" value="{{ old('recipient_name', $prefillRecipient?->name) }}" required class="uppercase w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <div id="recipient-suggestions" x-show="showSuggestions && suggestions.length > 0" @click.away="showSuggestions = false" class="absolute z-50 left-0 right-0 top-full mt-1 max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg" style="display:none">
                            <template x-for="s in suggestions" :key="s.id">
                                <button type="button" @click="fillRecipient(s)" class="flex w-full items-center gap-3 px-4 py-2.5 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="s.name + ' ' + (s.lastname || '')"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="s.phone + (s.city ? ' · ' + s.city : '')"></p>
                                    </div>
                                    <span class="shrink-0 text-xs text-gray-400" x-text="s.use_count + ' envios'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Apellidos</label>
                        <input name="recipient_lastname" value="{{ old('recipient_lastname', $prefillRecipient?->lastname) }}" required class="uppercase w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Telefono</label>
                    <div class="flex gap-1.5">
                        <select class="w-20 rounded-lg border border-gray-300 px-2 py-1.5 text-sm bg-gray-50"><option value="57">🇨🇴 +57</option></select>
                        <input name="recipient_phone" value="{{ old('recipient_phone', $prefillRecipient?->phone) }}" required inputmode="tel" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Telefono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Email</label>
                    <input name="recipient_email" type="email" value="{{ old('recipient_email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="correo@ejemplo.com">
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Departamento</label>
                        <select name="recipient_department" id="recipient_department" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white" onchange="loadCities(this.value)">
                            <option value="">Seleccionar</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('recipient_department') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Ciudad</label>
                        <select id="recipient_locality" name="recipient_locality" x-model="preview.locality" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
                            <option value="">Selecciona un departamento</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Direccion</label>
                    <input name="recipient_address" x-model="preview.address" value="{{ old('recipient_address', $prefillRecipient?->address) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Calle, carrera, complementos">
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Barrio</label>
                        <input id="recipient_neighborhood" name="recipient_neighborhood" value="{{ old('recipient_neighborhood', $prefillRecipient?->neighborhood) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Notas entrega</label>
                        <textarea name="recipient_notes" rows="1" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">{{ old('recipient_notes', $prefillRecipient?->notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- COLUMN 2: Products + Config --}}
            <div data-step-panel="product" class="te-create-column rounded-xl border border-gray-200 shadow-sm bg-white p-4">
                <div>
                    <h3 class="text-sm font-black text-gray-950">2. Producto, cobro y envio</h3>
                    <p class="mt-0.5 text-xs font-semibold text-gray-500">Elige el producto y confirma cuanto debe pagar el cliente.</p>
                </div>

                @if ($useInventory)
                    {{-- Inventario habilitado solo para cuentas internas/fundador --}}
                    @if ($inventoryProducts->count())
                        <div class="flex gap-2">
                            <select id="inventory_product_select" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                                <option value="">Seleccionar producto del inventario...</option>
                                @foreach ($inventoryProducts as $product)
                                    <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ (int) $product->price }}" data-stock="{{ (int) $product->stock }}" data-cost="{{ (int) $product->cost }}" data-package-type="merchandise">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                                @endforeach
                            </select>
                            <button type="button" id="add_inventory_product" class="rounded-lg bg-emerald-700 w-8 h-8 text-sm font-bold text-white hover:bg-emerald-800 shrink-0 flex items-center justify-center">+</button>
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-center">
                            <p class="text-sm font-semibold text-gray-600">Inventario vacio</p>
                            <a href="{{ route('inventory.index') }}" class="text-sm font-bold text-blue-700 hover:underline">Agregar productos</a>
                        </div>
                    @endif
                @else
                    {{-- Emprende: Quick products --}}
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Productos rapidos</p>
                                <p class="mt-0.5 text-xs font-semibold text-blue-800">Agrega tus productos frecuentes con un clic.</p>
                            </div>
                            <a href="{{ route('quick-products.index') }}" class="shrink-0 rounded-md bg-white px-2.5 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100">Editar</a>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <select id="quick_product_select" class="flex-1 rounded-lg border border-blue-200 px-3 py-1.5 text-sm bg-white">
                                <option value="">Producto frecuente...</option>
                                @foreach ($quickProducts as $product)
                                    <option value="{{ $product->name }}" data-package-type="{{ $product->package_type }}" data-price="{{ (int) $product->price }}">{{ $product->name }} @if ((float) $product->price > 0)- ${{ number_format((float) $product->price, 0, ',', '.') }}@endif</option>
                                @endforeach
                            </select>
                            <button type="button" id="add_quick_product" class="rounded-lg bg-blue-700 w-8 h-8 text-sm font-bold text-white hover:bg-blue-800 shrink-0 flex items-center justify-center">+</button>
                        </div>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($quickProducts->take(4) as $product)
                                <button
                                    type="button"
                                    class="te-quick-product-card rounded-lg border border-blue-100 bg-white p-2 text-left hover:border-blue-300 hover:bg-blue-50"
                                    data-name="{{ $product->name }}"
                                    data-package-type="{{ $product->package_type }}"
                                    data-price="{{ (int) $product->price }}"
                                >
                                    <span class="block truncate text-sm font-black text-gray-950">{{ $product->name }}</span>
                                    <span class="mt-0.5 block text-xs font-semibold text-gray-500">{{ $product->package_type === 'document' ? 'Documento' : ($product->package_type === 'package' ? 'Paquete' : 'Mercancia') }} @if ((float) $product->price > 0)- ${{ number_format((float) $product->price, 0, ',', '.') }}@endif</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div id="product_lines" class="space-y-1"></div>

                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Cobro y envio</p>
                    <p class="mt-0.5 text-xs font-semibold text-gray-500">Confirma el tipo de paquete, el envio y el recaudo.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Tipo paquete</label>
                        <select id="package_type" name="package_type" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                            <option value="package">Paquete</option>
                            <option value="document">Documento</option>
                            <option value="merchandise">Mercancia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Forma de pago</label>
                        <select id="payment_method" name="payment_method" x-model="preview.paymentMethod" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                            <option value="cash">Contado</option>
                            <option value="credit">Credito</option>
                            <option value="cod" selected>Contraentrega</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Valor envio ($)</label>
                        <input id="shipping_value" name="shipping_value" x-model.number="preview.shipping" type="number" min="0" step="100" value="{{ old('shipping_value', 0) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Valor recaudar ($)</label>
                        <input id="collection_value" name="collection_value" x-model.number="preview.collection" type="number" min="0" step="100" value="{{ old('collection_value', 0) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
            </div>

                </div>

            </div>

            {{-- COLUMN 3: Summary + Submit --}}
            <div class="te-create-column te-summary-column rounded-xl border border-gray-200 shadow-sm bg-white p-4 lg:sticky lg:top-5 lg:self-start">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Resumen</h3>
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-blue-700">Preparacion</p>
                            <p id="te-ready-label" class="mt-1 text-sm font-black text-blue-950">Completa los datos clave</p>
                        </div>
                        <span id="te-ready-percent" class="rounded-full bg-white px-2 py-0.5 text-xs font-black text-blue-700">0%</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-white">
                        <div id="te-ready-bar" class="h-2 rounded-full bg-blue-700" style="width: 0%"></div>
                    </div>
                    <div class="mt-3 grid gap-2 text-xs font-bold">
                        <div data-ready-item="client" class="flex items-center justify-between gap-2 rounded-md bg-white px-2.5 py-2 text-gray-500">
                            <span>Cliente y telefono</span>
                            <span class="te-ready-dot text-gray-400">Pendiente</span>
                        </div>
                        <div data-ready-item="address" class="flex items-center justify-between gap-2 rounded-md bg-white px-2.5 py-2 text-gray-500">
                            <span>Direccion y ciudad</span>
                            <span class="te-ready-dot text-gray-400">Pendiente</span>
                        </div>
                        <div data-ready-item="tariff" class="flex items-center justify-between gap-2 rounded-md bg-white px-2.5 py-2 text-gray-500">
                            <span>Tarifa de envio</span>
                            <span class="te-ready-dot text-gray-400">Pendiente</span>
                        </div>
                        <div data-ready-item="product" class="flex items-center justify-between gap-2 rounded-md bg-white px-2.5 py-2 text-gray-500">
                            <span>Producto</span>
                            <span class="te-ready-dot text-gray-400">Pendiente</span>
                        </div>
                        <div data-ready-item="money" class="flex items-center justify-between gap-2 rounded-md bg-white px-2.5 py-2 text-gray-500">
                            <span>Cobro y envio</span>
                            <span class="te-ready-dot text-gray-400">Pendiente</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Vista rapida</p>
                    <div class="mt-2 grid gap-2 text-sm">
                        <div>
                            <p class="text-xs font-bold text-gray-500">Producto</p>
                            <p id="te-summary-product" class="truncate font-black text-gray-950">Sin producto agregado</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500">Destino</p>
                            <p id="te-summary-destination" class="truncate font-semibold text-gray-700">Sin destinatario</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-500">Zona / tarifa</p>
                            <p id="te-summary-zone" class="truncate font-semibold text-gray-700">Sin tarifa asignada</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100 text-sm">
                    <div class="flex justify-between px-3 py-2">
                        <span class="text-gray-600">Recaudo</span>
                        <span class="font-bold" x-text="money(preview.collection)">$0</span>
                    </div>
                    <div class="flex justify-between px-3 py-2">
                        <span class="text-gray-600">Envio</span>
                        <span class="font-bold" x-text="money(preview.shipping)">$0</span>
                    </div>
                    <div class="flex justify-between px-3 py-2 bg-emerald-50 font-bold text-emerald-700">
                        <span>Total</span>
                        <span x-text="money((preview.collection || 0) + (preview.shipping || 0))">$0</span>
                    </div>
                </div>

                <div id="te-money-hint" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">
                    Si es contraentrega, confirma el valor a recaudar antes de crear la guia.
                </div>

                <div class="mt-auto rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-gray-600">
                    Completa los pasos de la izquierda y confirma la guia.
                </div>
            </div>

            {{-- Mobile bottom bar --}}
            <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white px-4 py-3 shadow-lg lg:hidden">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900" x-text="preview.recipient || 'Nueva guia'"></p>
                        <p class="text-xs text-gray-500" x-text="'Recaudo: ' + money(preview.collection)"></p>
                    </div>
                    <button class="rounded-lg bg-blue-700 px-5 py-3 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Crear guia</button>
                </div>
            </div>
        </form>
    </div>

    <datalist id="colombia_city_suggestions">
        @foreach (['Acacias','Aguazul','Anserma','Apartado','Arauca','Arjona','Armenia','Barrancabermeja','Barranquilla','Bello','Bogota','Bucaramanga','Buenaventura','Buga','Cajica','Caldas','Cali','Cartagena','Cartago','Chia','Cucuta','Dosquebradas','Duitama','Envigado','Espinal','Facatativa','Florencia','Floridablanca','Funza','Fusagasuga','Giron','Ibague','Ipiales','Itagui','Jamundi','La Ceja','La Dorada','Manizales','Medellin','Monteria','Mosquera','Neiva','Palmira','Pasto','Pereira','Piedecuesta','Popayan','Riohacha','Rionegro','Sabaneta','San Gil','Santa Marta','Sincelejo','Soacha','Sogamoso','Soledad','Tunja','Turbo','Valledupar','Villavicencio','Yopal','Zipaquira'] as $city)
            <option value="{{ $city }}">
        @endforeach
    </datalist>

    <style>
        [x-cloak] { display: none !important; }
        @media (min-width: 1024px) {
            .te-create-form { grid-template-columns: minmax(0, 1fr) 320px !important; }
        }
        .te-step-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(320px, 1fr));
            gap: 0.75rem;
            align-items: stretch;
            overflow-x: auto;
            padding-bottom: 0.25rem;
            scroll-snap-type: x proximity;
        }
        .te-create-column {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            min-width: 0;
        }
        .te-step-columns > [data-step-panel] {
            min-width: 0;
            height: 100%;
            scroll-snap-align: start;
        }
        .te-summary-column {
            gap: 0.75rem;
        }
        @media (min-width: 1024px) {
            .te-step-columns {
                grid-auto-rows: max(calc(100vh - 13.75rem), 49rem);
            }
            .te-create-column {
                min-height: max(calc(100vh - 13.75rem), 49rem);
            }
            .te-summary-column {
                margin-top: 5.15rem;
            }
        }
        @media (max-width: 1023px) {
            .te-step-columns {
                grid-template-columns: 1fr;
                overflow-x: visible;
                scroll-snap-type: none;
            }
            .te-create-column {
                min-height: 0;
            }
            .te-summary-column {
                margin-top: 0;
            }
        }
        @media (min-width: 1024px) and (max-width: 1535px) {
            .te-step-columns .sm\:grid-cols-2,
            .te-step-columns .sm\:grid-cols-3 {
                grid-template-columns: minmax(0, 1fr) !important;
            }
        }
        #product_lines { display: grid; gap: 6px; }
#product_lines .te-product-row { display: grid; grid-template-columns: minmax(0, 1fr) 80px 60px 30px; gap: 4px; align-items: center; }
        #product_lines .te-product-row input { width: 100%; height: 34px; border-radius: 6px; padding: 4px 8px; font-size: 13px; border: 1px solid #d1d5db; }
        #product_lines .te-product-row input:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.12); outline: none; }
        #product_lines .te-product-row button { width: 30px; height: 34px; border-radius: 6px; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 1px solid #d1d5db; background: white; color: #6b7280; cursor: pointer; }
        #product_lines .te-product-row button:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
        .te-products-head { display: grid; grid-template-columns: minmax(0, 1fr) 80px 60px 30px; gap: 4px; margin: 6px 0 4px; color: #6b7280; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    </style>

    <script>
        window.TE_createForm = {
            deliveryZones: @json($deliveryZoneSuggestions),
            companyTerms: @json($companyTerms),
            senderPresets: @json($senderPresets),
            companySenderPresetKeys: @json($companySenderPresetKeys),
            hasOldSender: @json(old('sender_name') !== null),
            quickProductPrefill: @json($prefillQuickProductPayload),
            recipientsSearchUrl: '{{ route('recipients.search') }}',
            errorsExist: @json($errors->any()),
            oldRecipientDepartment: @json(old('recipient_department')),
            oldRecipientLocality: @json(old('recipient_locality')),
            oldRecipientName: @json(old('recipient_name', $prefillRecipient?->name ?? '')),
            oldRecipientAddress: @json(old('recipient_address', $prefillRecipient?->address ?? '')),
            oldLocality: @json(old('recipient_locality', $prefillRecipient?->locality ?? $prefillRecipient?->city ?? '')),
            oldContentDesc: @json(old('content_description', '')),
            oldPaymentMethod: @json(old('payment_method', 'cod')),
            oldShippingValue: Number(@json(old('shipping_value', 0))),
            oldCollectionValue: Number(@json(old('collection_value', 0))),
        };
    </script>
    <script src="{{ asset('js/shipments-create.js') }}?v={{ filemtime(public_path('js/shipments-create.js')) }}"></script>
</x-app-layout>
