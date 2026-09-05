@php
    $prefillRecipient = $prefillRecipient ?? null;
    $prefillName = $prefillName ?? null;
    $prefillLastname = $prefillLastname ?? null;
    $prefillDepartmentId = $prefillDepartmentId ?? null;
    $prefillLocality = $prefillLocality ?? null;
    $prefillQuickProduct = $prefillQuickProduct ?? null;
    $prefillQuickProductPayload = $prefillQuickProduct ? [
        'name' => $prefillQuickProduct->name,
        'package_type' => $prefillQuickProduct->package_type,
        'price' => (int) $prefillQuickProduct->price,
    ] : null;

    $previewBrand = $previewBrand ?? [
        'name' => 'Tus Envios',
        'logo_path' => null,
        'color' => '#022a8c',
        'whatsapp' => null,
        'instagram' => null,
        'facebook' => null,
        'tiktok' => null,
        'website' => 'tusenvios.com.co',
        'message' => 'Gracias por tu compra.',
        'phone' => '',
        'address' => '',
        'neighborhood' => '',
        'locality' => '',
        'template' => 'classic',
    ];
    $previewTemplate = in_array(($previewBrand['template'] ?? 'classic'), ['classic', 'modern', 'advance'], true) ? $previewBrand['template'] : 'classic';
    $previewGuideNumber = $previewGuideNumber ?? 'XXX-0000-00000';
    $previewLogoUrl = $previewBrand['logo_path'] ? \Illuminate\Support\Facades\Storage::url($previewBrand['logo_path']) : null;

    $previewSocials = collect([
        ['type' => 'whatsapp', 'value' => $previewBrand['whatsapp'] ?? null],
        ['type' => 'instagram', 'value' => $previewBrand['instagram'] ?? null],
        ['type' => 'facebook', 'value' => $previewBrand['facebook'] ?? null],
        ['type' => 'tiktok', 'value' => $previewBrand['tiktok'] ?? null],
    ])->filter(fn ($s) => filled($s['value']))->values();

    if (!function_exists('tusDisplayName')) {
        function tusDisplayName($name) {
            $n = strtoupper(trim($name ?? ''));
            $m = null;
            if (preg_match('/IP(?:HONE)?\s*(\d+\s*(?:PRO\s*MAX|PRO|PLUS|MINI)?)/', $n, $mm)) $m = $mm[1];
            elseif (preg_match('/(?:PARA\s+)?(\d+\s*(?:PRO\s*MAX|PRO|PLUS|MINI))\b/', $n, $mm)) $m = $mm[1];
            elseif (preg_match('/\b(\d{2,})\b/', $n, $mm)) $m = $mm[1];
            if (!$m) return $n;
            $m = trim(preg_replace('/\s+/', ' ', $m));
            preg_match('/(AZUL|ROJO|NEGRO|BLANCO|VERDE|ROSADO|MORADO|AMARILLO|TRANSPARENTE|NARANJA|GRIS|DORADO|CELESTE)/', $n, $cm);
            preg_match('/(DIAMANTE|ELECTRO|SILICONA|CARCAZA|TPU|ACRILICO|DEGRADE)/', $n, $dm);
            $out = 'IP ' . $m;
            if (!empty($cm[1])) $out .= ' ' . $cm[1];
            if (!empty($dm[1]) && strpos($out, $dm[1]) === false) $out .= ' ' . $dm[1];
            if ($out === 'IP ' . $m) return $n;
            return $out;
        }
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            eyebrow="Guias"
            title="Nueva guia"
            description="Registra un envio con datos del cliente, productos y valor a recaudar."
        />
    </x-slot>

    <div class="h-full flex flex-col p-3 lg:p-4" x-data="shipmentCreateForm()" x-init="init()">
        <form method="POST" action="{{ route('shipments.store') }}" class="te-create-form flex-1 grid grid-cols-1 gap-3 lg:h-full lg:min-h-0 lg:grid-cols-3 lg:grid-rows-[minmax(0,1fr)] lg:overflow-hidden">
            @csrf

            <input type="hidden" name="service_type" value="{{ old('service_type', 'standard') }}">
            <input id="sender_name" type="hidden" name="sender_name" value="{{ old('sender_name', $senderPresets['default']['name'] ?? $senderPresets['rci']['name'] ?? 'Tus Envios') }}">
            <input id="sender_phone" type="hidden" name="sender_phone" value="{{ old('sender_phone', $senderPresets['default']['phone'] ?? $senderPresets['rci']['phone'] ?? '3000000000') }}">
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

            {{-- COLUMN 1: Customer Info --}}
            <div data-step-panel="client" class="te-create-col te-col-client rounded-xl border border-gray-200 shadow-sm bg-white p-4">
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-800 shadow-sm">
                        <div class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1">
                                <p class="font-bold text-red-900">Revisa los siguientes campos antes de guardar:</p>
                                <ul class="mt-1 list-disc list-inside space-y-0.5 text-red-700 font-semibold">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <h3 class="text-[13px] font-black uppercase tracking-wider text-gray-950">Datos del cliente</h3>
                    <p class="mt-0.5 text-xs font-semibold text-gray-500">Solo necesitamos nombre, telefono y direccion para empezar.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="relative" x-data="recipientAutocomplete()" x-init="init()">
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombres</label>
                        <input id="recipient_name" name="recipient_name" x-model="preview.recipient" value="{{ old('recipient_name', $prefillName) }}" required class="uppercase w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
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
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Apellidos <span class="font-normal text-gray-400">(Opcional)</span></label>
                        <input id="recipient_lastname" name="recipient_lastname" value="{{ old('recipient_lastname', $prefillLastname) }}" class="uppercase w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Telefono</label>
                        <input id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone', $prefillRecipient?->phone) }}" required inputmode="tel" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Telefono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Whatsapp</label>
                        <input name="recipient_alt_phone" value="{{ old('recipient_alt_phone', $prefillRecipient?->alt_phone) }}" inputmode="tel" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Whatsapp">
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
                                <option value="{{ $dept->id }}" @selected((old('recipient_department') ?? $prefillDepartmentId) == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Ciudad</label>
                        <select id="recipient_locality" name="recipient_locality" x-model="preview.locality" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Barrio <span class="font-normal text-gray-400">(Opcional)</span></label>
                        <input id="recipient_neighborhood" name="recipient_neighborhood" value="{{ old('recipient_neighborhood', $prefillRecipient?->neighborhood) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Localidad</label>
                        <select id="recipient_city" name="recipient_city" x-model="preview.city" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-0.5">
                        <label class="block text-xs font-semibold text-gray-600">Direccion</label>
                        <span class="text-[10px] font-bold text-gray-400"><span id="recipient_address_count">0</span>/100</span>
                    </div>
                    <input id="recipient_address" name="recipient_address" maxlength="100" x-model="preview.address" value="{{ old('recipient_address', $prefillRecipient?->address) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Calle, carrera, torre, apto (máx. 100 caracteres)">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Observaciones</label>
                    <textarea id="recipient_notes" name="recipient_notes" rows="1" maxlength="90" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">{{ old('recipient_notes', $prefillRecipient?->notes) }}</textarea>
                    <p class="mt-0.5 text-right text-[10px] font-bold text-gray-400"><span id="recipient_notes_count">0</span>/90</p>
                </div>
            </div>

            {{-- COLUMN 2: Products + Config --}}
            <div data-step-panel="product" class="te-create-col te-col-product rounded-xl border border-gray-200 shadow-sm bg-white p-4">
                <div>
                    <h3 class="text-[13px] font-black uppercase tracking-wider text-gray-950">Producto, cobro y envio</h3>
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
                    @php
                        $serializedProducts = $quickProducts->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'sku' => $p->sku,
                            'package_type' => $p->package_type,
                            'price' => (int) $p->price,
                            'stock' => (int) $p->stock,
                        ])->values()->toArray();
                    @endphp
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Productos rapidos</p>
                                <p class="mt-0.5 text-xs font-semibold text-blue-800">Agrega tus productos frecuentes con un clic.</p>
                            </div>
                            <a href="{{ route('quick-products.index') }}" class="shrink-0 rounded-md bg-white px-2.5 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100">Editar</a>
                        </div>
                        <div class="mt-3 relative" x-data="{
                            open: false,
                            search: '',
                            products: {{ json_encode($serializedProducts) }},
                            init() {
                                const btn = document.getElementById('add_quick_product');
                                if (btn) {
                                    btn.addEventListener('click', () => {
                                        setTimeout(() => {
                                            this.search = '';
                                        }, 50);
                                    });
                                }
                            },
                            get filteredProducts() {
                                if (!this.search) return this.products;
                                const nc = v => (v||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]/g,'');
                                const needle = nc(this.search);
                                if (!needle) return this.products;
                                const scored = this.products.map(p => {
                                    const hay = nc(p.name + ' ' + (p.sku||''));
                                    let score = 999;
                                    if (hay.includes(needle)) score = 0;
                                    else if (needle.includes(hay)) score = 1;
                                    else { let i=0, ok=true; for(const ch of needle){ const idx=hay.indexOf(ch,i); if(idx===-1){ ok=false; break; } i=idx+1; } if(ok) score=2; }
                                    return {p, score};
                                }).filter(x=>x.score<999).sort((a,b)=>a.score-b.score || a.p.name.localeCompare(b.p.name)).map(x=>x.p);
                                return scored;
                            },
                            displayName(name){
                                const n=(name||'').toUpperCase().trim();
                                let m=n.match(/IP(?:HONE)?\s*(\d+\s*(?:PRO\s*MAX|PRO|PLUS|MINI)?)/);
                                if(!m){ const m2=n.match(/(?:PARA\s+)?(\d+\s*(?:PRO\s*MAX|PRO|PLUS|MINI))\b/); if(m2) m=[m2[0],m2[1]]; }
                                if(!m) return n;
                                const colors=n.match(/(AZUL|ROJO|NEGRO|BLANCO|VERDE|ROSADO|MORADO|AMARILLO|TRANSPARENTE|NARANJA|GRIS|DORADO|CELESTE)/);
                                const mats=n.match(/(DIAMANTE|ELECTRO|SILICONA|CARCAZA|TPU|ACRILICO|DEGRADE)/);
                                let out=`IP ${m[1].replace(/\s+/g,' ').trim()}`;
                                if(colors) out+=` ${colors[1]}`;
                                if(mats && !out.includes(mats[1])) out+=` ${mats[1]}`;
                                if(out===`IP ${m[1].replace(/\s+/g,' ').trim()}`) return n;
                                return out;
                            },
                            selectProduct(product) {
                                this.search = product.name;
                                this.open = false;
                                
                                const select = document.getElementById('quick_product_select');
                                if (select) {
                                    select.value = product.name;
                                    for (let i = 0; i < select.options.length; i++) {
                                        if (select.options[i].value === product.name) {
                                            select.selectedIndex = i;
                                            break;
                                        }
                                    }
                                    select.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            }
                        }" @click.away="open = false">
                            <div class="flex gap-2 w-full">
                                <div style="position: relative;" class="flex-1 min-w-0">
                                    <input 
                                        type="text" 
                                        placeholder="Buscar producto frecuente..." 
                                        x-model="search"
                                        @focus="open = true"
                                        @keydown.escape="open = false"
                                        class="w-full min-w-0 rounded-lg border border-blue-200 pl-3 pr-8 py-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 placeholder-blue-300"
                                    />
                                    <button type="button" @click="open = !open; $event.stopPropagation()" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; padding: 0;" class="text-blue-400 focus:outline-none">
                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                                <button type="button" id="add_quick_product" class="rounded-lg bg-blue-700 w-8 h-8 text-sm font-bold text-white hover:bg-blue-800 shrink-0 flex items-center justify-center">+</button>
                            </div>

                            <select id="quick_product_select" class="hidden">
                                <option value="">Producto frecuente...</option>
                                @foreach ($quickProducts as $product)
                                    <option value="{{ $product->name }}" data-id="{{ $product->id }}" data-sku="{{ $product->sku }}" data-package-type="{{ $product->package_type }}" data-price="{{ (int) $product->price }}" data-stock="{{ (int) $product->stock }}">{{ $product->name }} ({{ $product->stock ?? 0 }} UND)</option>
                                @endforeach
                            </select>

                            <div 
                                x-show="open" 
                                x-transition
                                class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg py-0.5 text-xs"
                                style="display: none;"
                            >
                                <template x-if="filteredProducts.length === 0">
                                    <div class="px-3 py-1.5 text-gray-500 text-[11px]">No se encontraron productos</div>
                                </template>
                                <template x-for="p in filteredProducts" :key="p.id">
                                    <button 
                                        type="button" 
                                        @mousedown="selectProduct(p)"
                                        class="w-full text-left px-2 py-0.5 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none flex justify-between items-center"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <span class="font-semibold text-gray-800" style="font-size:9px" x-text="p.name"></span>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-1.5" style="font-size:9px">
                                            <span class="bg-blue-50 text-blue-700 px-1 py-0.5 rounded font-bold" style="font-size:9px" x-text="p.stock + ' UND'"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
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
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Valor envio ($) (Opcional)</label>
                        <input id="shipping_value" name="shipping_value" x-model.number="preview.shipping" type="number" min="0" step="100" value="{{ old('shipping_value', 0) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Valor recaudar ($)</label>
                        <input id="collection_value" name="collection_value" x-model.number="preview.collection" type="number" min="0" step="100" value="{{ old('collection_value', 0) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            {{-- COLUMN 3: Resumen / checklist --}}
            <div class="te-create-col te-col-preview rounded-xl border border-gray-200 shadow-sm bg-white p-4 flex flex-col min-h-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-[13px] font-black uppercase tracking-wider text-gray-950">Resumen de la guia</h3>
                        <p class="mt-0.5 text-xs font-semibold text-gray-500">Verifica los datos antes de crear.</p>
                    </div>
                    <span id="te-ready-percent" class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-800">0%</span>
                </div>
                <p id="te-ready-label" class="mt-1.5 text-[11px] font-bold text-blue-900">Completa los datos clave para habilitar la guia.</p>
                <div class="mt-1.5 h-1.5 rounded-full bg-gray-100">
                    <div id="te-ready-bar" class="h-1.5 rounded-full bg-blue-700 transition-all duration-300" style="width: 0%"></div>
                </div>

                {{-- RESUMEN: checklist de datos para crear la guia --}}
                <div class="mt-2 space-y-1 text-xs">
                    <div data-resume-item="client" class="te-resume-item">
                        <span class="te-resume-check">✓</span>
                        <span class="te-resume-label">Cliente</span>
                        <span id="res-client-name" class="te-resume-value">Sin datos</span>
                    </div>

                    <div data-resume-item="address" class="te-resume-item">
                        <span class="te-resume-check">✓</span>
                        <span class="te-resume-label">Direccion</span>
                        <span id="res-address" class="te-resume-value">Sin datos</span>
                    </div>

                    <div data-resume-item="product" class="te-resume-item">
                        <span class="te-resume-check">✓</span>
                        <span class="te-resume-label">Producto</span>
                        <span id="res-products" class="te-resume-value">Sin productos</span>
                    </div>

                    <div data-resume-item="tariff" class="te-resume-item">
                        <span class="te-resume-check">✓</span>
                        <span class="te-resume-label">Tarifa</span>
                        <span id="res-zone" class="te-resume-value">Sin zona</span>
                    </div>

                    <div data-resume-item="money" class="te-resume-item">
                        <span class="te-resume-check">✓</span>
                        <span class="te-resume-label">Cobro</span>
                        <span id="res-collection" class="te-resume-value">$0</span>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 text-sm">
                    <div class="flex justify-between px-3 py-2">
                        <span class="text-gray-600">Recaudo</span>
                        <span class="font-bold" x-text="money(preview.collection)">$0</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 px-3 py-2">
                        <span class="text-gray-600">Envio</span>
                        <span class="font-bold" x-text="money(preview.shipping)">$0</span>
                    </div>
                    <div class="flex justify-between rounded-b-lg bg-emerald-50 px-3 py-2 font-bold text-emerald-700">
                        <span>Total</span>
                        <span x-text="money((preview.collection || 0) + (preview.shipping || 0))">$0</span>
                    </div>
                </div>

                <div id="te-money-hint" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">
                    Si es contraentrega, confirma el valor a recaudar antes de crear la guia.
                </div>

                <div class="mt-auto pt-3">
                    <button class="w-full rounded-lg bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</button>
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

        .te-create-col {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            min-width: 0;
        }

        @media (min-width: 1024px) {
            .te-create-form { height: 100%; min-height: 0; }
            .te-create-col { height: 100%; min-height: 0; overflow-y: auto; overscroll-behavior: contain; }
            .te-create-col::-webkit-scrollbar { width: 8px; }
            .te-create-col::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
            .te-create-col::-webkit-scrollbar-track { background: transparent; }
        }

        @media (min-width: 1024px) and (max-width: 1535px) {
            .te-create-col .sm\:grid-cols-2 { grid-template-columns: minmax(0, 1fr) !important; }
        }

        #product_lines { display: grid; gap: 6px; }
        #product_lines .te-product-row { display: grid; grid-template-columns: minmax(0, 1fr) 80px 60px 30px; gap: 4px; align-items: center; }
        #product_lines .te-product-row input { width: 100%; height: 34px; border-radius: 6px; padding: 4px 8px; font-size: 13px; border: 1px solid #d1d5db; }
        #product_lines .te-product-row input:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.12); outline: none; }
        #product_lines .te-product-row button { width: 30px; height: 34px; border-radius: 6px; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 1px solid #d1d5db; background: white; color: #6b7280; cursor: pointer; }
        #product_lines .te-product-row button:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
        .te-products-head { display: grid; grid-template-columns: minmax(0, 1fr) 80px 60px 30px; gap: 4px; margin: 6px 0 4px; color: #6b7280; font-size: 10px; font-weight: 700; text-transform: uppercase; }

        /* Resumen: checklist de datos */
        .te-resume-item {
            display: grid;
            grid-template-columns: 18px 72px minmax(0, 1fr);
            gap: 6px;
            align-items: center;
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #ffffff;
            transition: border-color .18s ease, background .18s ease;
        }
        .te-resume-check {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #e5e7eb;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
        }
        .te-resume-label {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #9ca3af;
            white-space: nowrap;
        }
        .te-resume-value {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .te-resume-ok { border-color: #a7f3d0; background: #f0fdf6; }
        .te-resume-ok .te-resume-check { background: #059669; }
        .dark .te-resume-item { border-color: #374151; background: #1f2937; }
        .dark .te-resume-check { background: #4b5563; }
        .dark .te-resume-label { color: #9ca3af; }
        .dark .te-resume-value { color: #f3f4f6; }
        .dark .te-resume-ok { border-color: #065f46; background: #064e3b; }
        .dark .te-resume-ok .te-resume-check { background: #34d399; }
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
            oldRecipientCity: @json(old('recipient_city', $prefillRecipient?->city ?? '')),
            prefillDepartmentId: @json($prefillDepartmentId),
            prefillLocality: @json($prefillLocality),
            oldRecipientName: @json(old('recipient_name', $prefillName ?? '')),
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
