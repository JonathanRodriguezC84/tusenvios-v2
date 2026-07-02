<x-app-layout>
    <x-slot name="header">
        <x-page-header
            eyebrow="Guias"
            title="Nueva guia"
            description="Registra un envio con datos del cliente, productos y valor a recaudar."
        />
    </x-slot>

    <div class="h-full flex flex-col p-3 lg:p-5" x-data="shipmentCreateForm()" x-init="init()">
        <form method="POST" action="{{ route('shipments.store') }}" class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-4 te-create-form">
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

            {{-- COLUMN 1: Customer Info --}}
            <div class="overflow-y-auto rounded-xl border border-gray-200 shadow-sm bg-white p-4 space-y-3">
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ $errors->first('inventory_items') ?: $errors->first() ?: 'Revisa los campos antes de guardar.' }}</div>
                @endif

                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Datos del cliente</h3>                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="relative" x-data="recipientAutocomplete()" x-init="init()">
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombres</label>
                        <input name="recipient_name" x-model="preview.recipient" value="{{ old('recipient_name') }}" required class="uppercase w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
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
                        <input name="recipient_lastname" value="{{ old('recipient_lastname') }}" required class="uppercase w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Telefono</label>
                    <div class="flex gap-1.5">
                        <select class="w-20 rounded-lg border border-gray-300 px-2 py-1.5 text-sm bg-gray-50"><option value="57">🇨🇴 +57</option></select>
                        <input name="recipient_phone" value="{{ old('recipient_phone') }}" required inputmode="tel" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Telefono">
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
                    <input name="recipient_address" x-model="preview.address" value="{{ old('recipient_address') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Calle, carrera, complementos">
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Barrio</label>
                        <input id="recipient_neighborhood" name="recipient_neighborhood" value="{{ old('recipient_neighborhood') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-0.5">Notas entrega</label>
                        <textarea name="recipient_notes" rows="1" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">{{ old('recipient_notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- COLUMN 2: Products + Config --}}
            <div class="overflow-y-auto rounded-xl border border-gray-200 shadow-sm bg-white p-4 space-y-3">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Productos</h3>

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
                    <div class="flex gap-2">
                        <select id="quick_product_select" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm bg-white">
                            <option value="">Producto frecuente...</option>
                            @foreach ($quickProducts as $product)
                                <option value="{{ $product->name }}" data-package-type="{{ $product->package_type }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="add_quick_product" class="rounded-lg bg-blue-700 w-8 h-8 text-sm font-bold text-white hover:bg-blue-800 shrink-0 flex items-center justify-center">+</button>
                    </div>
                @endif
                <div id="product_lines" class="space-y-1"></div>

                <hr class="border-gray-200">

                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Configuracion</h3>

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
                        <input name="collection_value" x-model.number="preview.collection" type="number" min="0" step="100" value="{{ old('collection_value', 0) }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            {{-- COLUMN 3: Summary + Submit --}}
            <div class="rounded-xl border border-gray-200 shadow-sm bg-white p-4 flex flex-col gap-3">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Resumen</h3>
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

                <div class="mt-auto space-y-2 pt-3 border-t border-gray-200">
                    <button class="w-full rounded-lg bg-blue-700 py-2.5 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Crear guia</button>
                    <a href="{{ route('shipments.index') }}" class="block w-full text-center rounded-lg border border-gray-300 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">Cancelar</a>
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
        @media (min-width: 1024px) {
            .te-create-form { grid-template-columns: 1fr 1.4fr 0.6fr !important; }
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
                preview: { recipient: @json(old('recipient_name', '')), address: @json(old('recipient_address', '')), locality: @json(old('recipient_locality', '')), content: @json(old('content_description', '')), paymentMethod: @json(old('payment_method', 'cod')), shipping: Number(@json(old('shipping_value', 0))), collection: Number(@json(old('collection_value', 0))) },
                init() { this.$nextTick(() => window.dispatchEvent(new CustomEvent('shipment-form-ready'))); },
                money(v) { return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(v || 0)); },
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
            const invSelect = document.getElementById('inventory_product_select');
            const invBtn = document.getElementById('add_inventory_product');
            if (!lines || !content) return;

            const up = v => String(v || '').toLocaleUpperCase('es-CO');
            const money = v => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(v || 0));
            const esc = v => String(v || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

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
            };

            const makeRow = (name = '', price = 0, qty = 1, inventoryId = null) => {
                const r = document.createElement('div');
                r.className = 'te-product-row';
                if (inventoryId) r.dataset.inventoryId = inventoryId;
                r.innerHTML = `<input type="text" value="${esc(up(name))}" placeholder="Producto" class="te-product-name uppercase"><input type="number" min="0" step="100" value="${price}" placeholder="Precio" class="te-product-price"><input type="number" min="1" step="1" value="${qty}" placeholder="Cant" class="te-product-quantity"><button type="button" title="Eliminar">×</button>`;
                r.querySelectorAll('.te-product-price, .te-product-quantity').forEach(i => i.addEventListener('input', sync));
                r.querySelector('.te-product-name')?.addEventListener('change', sync);
                r.querySelector('button')?.addEventListener('click', () => { r.remove(); sync(); });
                return r;
            };

            if (quickBtn) {
                quickBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const name = quickSelect?.value || '';
                    if (!name) return;
                    const emptyRow = [...lines.querySelectorAll('.te-product-row')].find(r => !r.querySelector('.te-product-name')?.value.trim());
                    if (emptyRow) {
                        emptyRow.querySelector('.te-product-name').value = up(name);
                    } else {
                        lines.appendChild(makeRow(name));
                    }
                    if (quickSelect) quickSelect.value = '';
                    sync();
                });
            }

            if (invBtn) {
                invBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sel = invSelect;
                    const opt = sel?.options[sel.selectedIndex];
                    const name = opt?.dataset?.name;
                    const price = opt?.dataset?.price || 0;
                    const invId = opt?.value || null;
                    if (!name) return;
                    const emptyRow = [...lines.querySelectorAll('.te-product-row')].find(r => !r.querySelector('.te-product-name')?.value.trim());
                    if (emptyRow) {
                        emptyRow.querySelector('.te-product-name').value = up(name);
                        emptyRow.querySelector('.te-product-price').value = price;
                        if (invId) emptyRow.dataset.inventoryId = invId;
                    } else {
                        lines.appendChild(makeRow(name, price, 1, invId));
                    }
                    if (sel) sel.value = '';
                    sync();
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

            const form = lines.closest('form');
            if (form) {
                form.addEventListener('submit', () => { sync(); }, true);
            }
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
