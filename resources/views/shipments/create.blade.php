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
        function recipientAutocomplete() {
            return {
                suggestions: [],
                showSuggestions: false,
                init() {
                    const nameInput = document.querySelector('[name="recipient_name"]');
                    if (!nameInput) return;
                    let debounce = null;
                    nameInput.addEventListener('input', () => {
                        clearTimeout(debounce);
                        debounce = setTimeout(() => {
                            const q = nameInput.value.trim();
                            if (q.length < 2) { this.suggestions = []; this.showSuggestions = false; return; }
                            fetch('{{ route('recipients.search') }}?q=' + encodeURIComponent(q), {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(r => r.json())
                            .then(data => { this.suggestions = data; this.showSuggestions = data.length > 0; })
                            .catch(() => { this.suggestions = []; this.showSuggestions = false; });
                        }, 300);
                    });
                    nameInput.addEventListener('blur', () => { setTimeout(() => { this.showSuggestions = false; }, 200); });
                },
                fillRecipient(s) {
                    const fill = (name, val) => { const el = document.querySelector(`[name="${name}"]`); if (el && val) { el.value = val; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); } };
                    fill('recipient_name', s.name);
                    fill('recipient_lastname', s.lastname);
                    fill('recipient_phone', s.phone);
                    fill('recipient_alt_phone', s.alt_phone);
                    fill('recipient_document', s.document);
                    fill('recipient_address', s.address);
                    fill('recipient_neighborhood', s.neighborhood);
                    fill('recipient_locality', s.locality);
                    fill('recipient_city', s.city);
                    if (s.city) {
                        const dept = document.querySelector('[name="recipient_department"]');
                        if (dept) dept.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    this.showSuggestions = false;
                }
            };
        }

        const deliveryZones = @json($deliveryZoneSuggestions);
        const companyTerms = @json($companyTerms);
        const senderPresets = @json($senderPresets);
        const companySenderPresetKeys = @json($companySenderPresetKeys);
        const hasOldSender = @json(old('sender_name') !== null);

        function shipmentCreateForm() {
            return {
                currentStep: @json($errors->any() ? 'client' : 'client'),
                steps: [
                    { key: 'client', short: 'Cliente', label: 'Cliente y direccion' },
                    { key: 'product', short: 'Producto', label: 'Producto, cobro y envio' },
                ],
                preview: { recipient: @json(old('recipient_name', $prefillRecipient?->name ?? '')), address: @json(old('recipient_address', $prefillRecipient?->address ?? '')), locality: @json(old('recipient_locality', $prefillRecipient?->locality ?? $prefillRecipient?->city ?? '')), content: @json(old('content_description', '')), paymentMethod: @json(old('payment_method', 'cod')), shipping: Number(@json(old('shipping_value', 0))), collection: Number(@json(old('collection_value', 0))) },
                init() { this.$nextTick(() => window.dispatchEvent(new CustomEvent('shipment-form-ready'))); },
                money(v) { return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(v || 0)); },
                currentStepIndex() { return this.steps.findIndex(step => step.key === this.currentStep); },
                currentStepLabel() { return this.steps[this.currentStepIndex()]?.label || 'Crear guia'; },
                goToStep(stepKey) { this.currentStep = stepKey; this.$nextTick(() => document.querySelector('[data-step-panel]:not([style*="display: none"])')?.scrollIntoView({ behavior: 'smooth', block: 'start' })); },
                previousStep() {
                    const index = Math.max(this.currentStepIndex() - 1, 0);
                    this.goToStep(this.steps[index].key);
                },
                nextStep() {
                    if (!this.stepReady(this.currentStep)) return;
                    const index = Math.min(this.currentStepIndex() + 1, this.steps.length - 1);
                    this.goToStep(this.steps[index].key);
                },
                stepReady(step) {
                    const requiredByStep = {
                        client: ['recipient_name', 'recipient_lastname', 'recipient_phone', 'recipient_department', 'recipient_locality', 'recipient_address', 'recipient_neighborhood'],
                        product: ['content_description', 'shipping_value', 'collection_value'],
                    };
                    const missing = (requiredByStep[step] || []).find((name) => {
                        const el = document.querySelector(`[name="${name}"]`);
                        return !el || String(el.value || '').trim() === '';
                    });
                    if (!missing) return true;
                    const el = missing === 'content_description'
                        ? document.querySelector('.te-product-name')
                        : document.querySelector(`[name="${missing}"]`);
                    el?.focus?.();
                    el?.classList?.add('ring-2', 'ring-amber-300', 'border-amber-400');
                    window.setTimeout(() => el?.classList?.remove('ring-2', 'ring-amber-300', 'border-amber-400'), 1400);
                    return false;
                },
            };
        }

        const upperCaseFields = ['recipient_name','recipient_lastname','recipient_address','recipient_neighborhood','content_description','recipient_notes'];
        upperCaseFields.forEach((name) => {
            document.querySelectorAll(`[name="${name}"]`).forEach((el) => {
                el.classList.add('uppercase');
                let busy = false;
                el.addEventListener('input', () => {
                    if (busy) return;
                    const s = el.selectionStart, e = el.selectionEnd;
                    const up = (el.value || '').toLocaleUpperCase('es-CO');
                    if (el.value === up) return;
                    busy = true; el.value = up;
                    if (typeof el.setSelectionRange === 'function' && s != null) el.setSelectionRange(s, e);
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    busy = false;
                });
            });
        });

        document.getElementById('delivery_zone_id')?.addEventListener('change', () => {
            const sel = document.getElementById('delivery_zone_id');
            const opt = sel?.options[sel.selectedIndex];
            const price = opt?.dataset.price;
            const ship = document.getElementById('shipping_value');
            if (price && ship) { ship.value = price; ship.dispatchEvent(new Event('input', { bubbles: true })); }
        });

        document.getElementById('recipient_locality')?.addEventListener('blur', () => {
            const loc = document.getElementById('recipient_locality')?.value;
            const nbh = document.getElementById('recipient_neighborhood')?.value;
            const txt = (loc + ' ' + nbh).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
            if (!txt) return;
            const match = deliveryZones.find(z => z.keywords.toLowerCase().split(',').map(k => k.trim()).some(k => txt.includes(k)));
            if (match) { document.getElementById('delivery_zone_id').value = match.id; document.getElementById('delivery_zone_id')?.dispatchEvent(new Event('change')); }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lines = document.getElementById('product_lines');
            const content = document.getElementById('content_description');
            const pieces = document.getElementById('pieces');
            const collect = document.querySelector('[name="collection_value"]');
            const quickSelect = document.getElementById('quick_product_select');
            const quickBtn = document.getElementById('add_quick_product');
            const quickCards = document.querySelectorAll('.te-quick-product-card');
            const quickProductPrefill = @json($prefillQuickProductPayload);
            const packageType = document.getElementById('package_type');
            const invSelect = document.getElementById('inventory_product_select');
            const invBtn = document.getElementById('add_inventory_product');
            if (!lines || !content) return;

            const up = v => String(v || '').toLocaleUpperCase('es-CO');
            const money = v => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(v || 0));
            const esc = v => String(v || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const form = lines.closest('form');
            const fieldValue = name => form?.querySelector(`[name="${name}"]`)?.value?.trim() || '';
            const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
            const updateReadyItem = (key, ready) => {
                const item = document.querySelector(`[data-ready-item="${key}"]`);
                if (!item) return;
                const dot = item.querySelector('.te-ready-dot');
                item.className = `flex items-center justify-between gap-2 rounded-md px-2.5 py-2 ${ready ? 'bg-emerald-50 text-emerald-800' : 'bg-white text-gray-500'}`;
                if (dot) {
                    dot.textContent = ready ? 'Listo' : 'Pendiente';
                    dot.className = `te-ready-dot ${ready ? 'text-emerald-700' : 'text-gray-400'}`;
                }
            };
            const updateReadiness = () => {
                const paymentMethod = fieldValue('payment_method');
                const shippingValue = Number(document.getElementById('shipping_value')?.value || 0);
                const collectionValue = Number(fieldValue('collection_value') || 0);
                const zoneSelect = document.getElementById('delivery_zone_id');
                const selectedZone = zoneSelect?.options[zoneSelect.selectedIndex];
                const zoneName = selectedZone?.value ? (selectedZone.textContent || '').split(' - ')[0].trim() : '';
                const moneyReady = paymentMethod === 'cod'
                    ? collectionValue > 0 && shippingValue >= 0
                    : Boolean(paymentMethod && shippingValue >= 0);
                const checks = {
                    client: Boolean(fieldValue('recipient_name') && fieldValue('recipient_phone')),
                    address: Boolean(fieldValue('recipient_address') && fieldValue('recipient_neighborhood') && fieldValue('recipient_locality')),
                    tariff: shippingValue > 0,
                    product: Boolean(content.value.trim()),
                    money: moneyReady,
                };
                const readyCount = Object.values(checks).filter(Boolean).length;
                const totalChecks = Object.keys(checks).length;
                const percent = Math.round((readyCount / totalChecks) * 100);
                Object.entries(checks).forEach(([key, ready]) => updateReadyItem(key, ready));
                const bar = document.getElementById('te-ready-bar');
                if (bar) bar.style.width = `${percent}%`;
                setText('te-ready-percent', `${percent}%`);
                setText('te-ready-label', percent === 100 ? 'Guia lista para crear' : `${totalChecks - readyCount} paso(s) pendiente(s)`);
                setText('te-summary-product', content.value.trim() || 'Sin producto agregado');
                setText('te-confirm-product', content.value.trim() || 'Pendiente');
                const recipient = [fieldValue('recipient_name'), fieldValue('recipient_lastname')].filter(Boolean).join(' ');
                const destination = [recipient, fieldValue('recipient_locality')].filter(Boolean).join(' - ');
                setText('te-summary-destination', destination || 'Sin destinatario');
                setText('te-summary-zone', shippingValue > 0
                    ? `${zoneName || 'Tarifa manual'} - ${money(shippingValue)}`
                    : 'Sin tarifa asignada');
                const hint = document.getElementById('te-money-hint');
                if (hint) {
                    if (paymentMethod === 'cod' && collectionValue <= 0) {
                        hint.className = 'rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800';
                        hint.textContent = 'Contraentrega sin recaudo: agrega el valor que debe pagar el cliente.';
                    } else if (paymentMethod === 'cod') {
                        hint.className = 'rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800';
                        hint.textContent = `Recaudo confirmado: ${money(collectionValue)}.`;
                    } else {
                        hint.className = 'rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-800';
                        hint.textContent = 'Pago sin recaudo contraentrega. Revisa solo el valor del envio.';
                    }
                }
            };

            const sync = () => {
                let qty = 0, price = 0;
                const invItems = [];
                const prods = [...lines.querySelectorAll('.te-product-row')].map(row => {
                    const n = row.querySelector('.te-product-name'), p = row.querySelector('.te-product-price'), q = row.querySelector('.te-product-quantity');
                    const name = up(n?.value).trim(), pv = Number(p?.value || 0), qv = Math.max(Number(q?.value || 1), 1);
                    if (n && n.value !== name) n.value = name;
                    if (!name) return null;
                    qty += qv; price += pv * qv;
                    const invId = row.dataset.inventoryId;
                    if (invId) invItems.push({ id: Number(invId), quantity: qv });
                    return `${name} x ${qv}${pv > 0 ? ' - ' + money(pv) : ''}`;
                }).filter(Boolean);
                content.value = prods.join(' + ');
                content.dispatchEvent(new Event('input', { bubbles: true }));
                if (pieces) pieces.value = Math.max(qty, 1);
                if (collect && price > 0) { collect.value = price; collect.dispatchEvent(new Event('input', { bubbles: true })); }
                const invField = document.getElementById('inventory_items');
                if (invField) invField.value = JSON.stringify(invItems);
                updateReadiness();
            };

            const makeRow = (name = '', price = 0, qty = 1, inventoryId = null) => {
                const r = document.createElement('div');
                r.className = 'te-product-row';
                if (inventoryId) r.dataset.inventoryId = inventoryId;
                r.innerHTML = `<input type="text" value="${esc(up(name))}" placeholder="Producto" class="te-product-name uppercase"><input type="number" min="0" step="100" value="${price}" placeholder="Precio" class="te-product-price"><input type="number" min="1" step="1" value="${qty}" placeholder="Cant" class="te-product-quantity"><button type="button" title="Eliminar">×</button>`;
                r.querySelectorAll('.te-product-price, .te-product-quantity').forEach(i => i.addEventListener('input', sync));
                r.querySelector('.te-product-name')?.addEventListener('input', sync);
                r.querySelector('.te-product-name')?.addEventListener('change', sync);
                r.querySelector('button')?.addEventListener('click', () => { r.remove(); sync(); });
                return r;
            };

            const addProductLine = (name, price = 0, qty = 1, inventoryId = null, type = null) => {
                if (!name) return;
                const normalizedName = up(name).trim();
                const matchingRow = [...lines.querySelectorAll('.te-product-row')].find((row) => {
                    const rowName = up(row.querySelector('.te-product-name')?.value || '').trim();
                    const rowInventoryId = row.dataset.inventoryId || null;
                    return rowName === normalizedName && String(rowInventoryId || '') === String(inventoryId || '');
                });
                if (matchingRow) {
                    const quantity = matchingRow.querySelector('.te-product-quantity');
                    if (quantity) quantity.value = Math.max(Number(quantity.value || 1), 1) + Number(qty || 1);
                    if (type && packageType) {
                        packageType.value = type;
                        packageType.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    sync();
                    return;
                }
                const emptyRow = [...lines.querySelectorAll('.te-product-row')].find(r => !r.querySelector('.te-product-name')?.value.trim());
                if (emptyRow) {
                    emptyRow.querySelector('.te-product-name').value = normalizedName;
                    emptyRow.querySelector('.te-product-price').value = price;
                    emptyRow.querySelector('.te-product-quantity').value = qty;
                    if (inventoryId) emptyRow.dataset.inventoryId = inventoryId;
                } else {
                    lines.appendChild(makeRow(name, price, qty, inventoryId));
                }
                if (type && packageType) {
                    packageType.value = type;
                    packageType.dispatchEvent(new Event('change', { bubbles: true }));
                }
                sync();
            };

            if (quickBtn) {
                quickBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const opt = quickSelect?.options[quickSelect.selectedIndex];
                    const name = quickSelect?.value || '';
                    const type = opt?.dataset?.packageType || null;
                    const price = opt?.dataset?.price || 0;
                    addProductLine(name, price, 1, null, type);
                    if (quickSelect) quickSelect.value = '';
                });
            }

            if (quickProductPrefill?.name) {
                addProductLine(quickProductPrefill.name, quickProductPrefill.price || 0, 1, null, quickProductPrefill.package_type || null);
            }

            quickCards.forEach((card) => {
                card.addEventListener('click', () => {
                    addProductLine(card.dataset.name || '', card.dataset.price || 0, 1, null, card.dataset.packageType || null);
                });
            });

            if (invBtn) {
                invBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sel = invSelect;
                    const opt = sel?.options[sel.selectedIndex];
                    const name = opt?.dataset?.name;
                    const price = opt?.dataset?.price || 0;
                    const invId = opt?.value || null;
                    const type = opt?.dataset?.packageType || 'merchandise';
                    if (!name) return;
                    addProductLine(name, price, 1, invId, type);
                    if (sel) sel.value = '';
                });
            }

            if (!lines.querySelector('.te-product-row')) {
                lines.appendChild(makeRow());
                sync();
            }

            const head = document.createElement('div');
            head.className = 'te-products-head';
            head.innerHTML = '<span>Producto</span><span>Precio</span><span>Cant</span><span></span>';
            lines.before(head);

            if (form) {
                form.querySelectorAll('input, select, textarea').forEach((el) => {
                    el.addEventListener('input', updateReadiness);
                    el.addEventListener('change', updateReadiness);
                });
                form.addEventListener('submit', () => { sync(); }, true);
            }
            updateReadiness();
        });

        async function loadCities(departmentId) {
            const citySelect = document.getElementById('recipient_locality');
            citySelect.innerHTML = '<option value="">Cargando...</option>';
            if (!departmentId) {
                citySelect.innerHTML = '<option value="">Selecciona un departamento</option>';
                return;
            }
            try {
                const res = await fetch('/api/cities?department_id=' + encodeURIComponent(departmentId));
                const cities = await res.json();
                citySelect.innerHTML = '<option value="">Seleccionar</option>';
                cities.forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = city.name;
                    opt.textContent = city.name;
                    citySelect.appendChild(opt);
                });
            } catch (e) {
                citySelect.innerHTML = '<option value="">Error al cargar</option>';
            }
        }

        @if(old('recipient_department') && old('recipient_locality'))
        document.addEventListener('DOMContentLoaded', async () => {
            await loadCities({{ old('recipient_department') }});
            const citySelect = document.getElementById('recipient_locality');
            const options = Array.from(citySelect.options);
            const match = options.find(opt => opt.value === '{{ old('recipient_locality') }}');
            if (match) citySelect.value = match.value;
        });
        @endif
    </script>
</x-app-layout>
