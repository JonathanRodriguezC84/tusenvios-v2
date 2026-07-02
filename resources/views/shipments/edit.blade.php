<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Guias</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">
                Editar {{ $shipment->guide_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('shipments.update', $shipment) }}" class="grid gap-4">
                @csrf
                @method('PATCH')

                @if ($errors->any())
                    <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Revisa los campos marcados antes de guardar la guia.
                    </div>
                @endif

                @if ($usesInventory)
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Esta guia desconto inventario. Puedes editar datos de entrega, remitente y tarifa, pero los productos, piezas y recaudo quedan protegidos para mantener el stock correcto.
                    </div>
                @endif

                <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-950">Datos generales</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            <span class="flex items-center justify-between gap-2">
                                <span>Remitente</span>
                                <a href="{{ route('sender-profiles.index') }}" class="text-xs font-semibold text-blue-700 hover:underline">Administrar</a>
                            </span>
                            <select id="sender_preset" name="sender_preset" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                                @foreach ($senderPresets as $presetKey => $preset)
                                    <option value="{{ $presetKey }}" @selected(old('sender_preset', 'current') === $presetKey)>{{ $preset['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Empresa afiliada
                            <select name="affiliated_company_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                                <option value="">Tus Envios</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" @selected(old('affiliated_company_id', $shipment->affiliated_company_id) == $company->id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <input type="hidden" name="service_type" value="{{ old('service_type', $shipment->service_type ?: 'standard') }}">
                    <input type="hidden" name="zone" value="{{ old('zone', $shipment->zone) }}">
                    <input id="sender_name" type="hidden" name="sender_name" value="{{ old('sender_name', $shipment->sender_name) }}">
                    <input id="sender_phone" type="hidden" name="sender_phone" value="{{ old('sender_phone', $shipment->sender_phone) }}">
                    <input id="sender_address" type="hidden" name="sender_address" value="{{ old('sender_address', $shipment->sender_address) }}">
                    <input id="sender_neighborhood" type="hidden" name="sender_neighborhood" value="{{ old('sender_neighborhood', $shipment->sender_neighborhood) }}">
                    <input id="sender_locality" type="hidden" name="sender_locality" value="{{ old('sender_locality', $shipment->sender_locality) }}">
                </section>

                <div class="grid gap-4 lg:grid-cols-2">
                <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-950">Destinatario</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Nombre
                            <input name="recipient_name" value="{{ old('recipient_name', $shipment->recipient_name) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Apellidos
                            <input name="recipient_lastname" value="{{ old('recipient_lastname', $shipment->recipient_lastname) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Telefono
                            <input name="recipient_phone" value="{{ old('recipient_phone', $shipment->recipient_phone) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Telefono alterno
                            <input name="recipient_alt_phone" value="{{ old('recipient_alt_phone', $shipment->recipient_alt_phone) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Localidad
                            <input id="recipient_locality" name="recipient_locality" list="colombia_city_suggestions" value="{{ old('recipient_locality', $shipment->recipient_locality) }}" autocomplete="address-level2" placeholder="Ej. Bogota, Medellin, Cali" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                            Direccion
                            <input name="recipient_address" value="{{ old('recipient_address', $shipment->recipient_address) }}" required class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Barrio
                            <input id="recipient_neighborhood" name="recipient_neighborhood" value="{{ old('recipient_neighborhood', $shipment->recipient_neighborhood) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                            Observaciones
                            <textarea name="recipient_notes" rows="3" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('recipient_notes', $shipment->recipient_notes) }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-950">Envio y pago</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Tipo de paquete
                            @if ($usesInventory)
                                <input type="hidden" name="package_type" value="{{ $shipment->package_type }}">
                            @endif
                            <select name="package_type" @disabled($usesInventory) class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 disabled:bg-gray-100 disabled:text-gray-500">
                                <option value="package" @selected(old('package_type', $shipment->package_type) === 'package')>Paquete</option>
                                <option value="document" @selected(old('package_type', $shipment->package_type) === 'document')>Documento</option>
                                <option value="merchandise" @selected(old('package_type', $shipment->package_type) === 'merchandise')>Mercancia</option>
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Piezas
                            <input name="pieces" type="number" min="1" value="{{ old('pieces', $shipment->pieces) }}" required @readonly($usesInventory) class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 read-only:bg-gray-100 read-only:text-gray-500">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Forma de pago
                            <select name="payment_method" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                                <option value="cash" @selected(old('payment_method', $shipment->payment_method) === 'cash')>Contado</option>
                                <option value="credit" @selected(old('payment_method', $shipment->payment_method) === 'credit')>Credito</option>
                                <option value="cod" @selected(old('payment_method', $shipment->payment_method) === 'cod')>Contraentrega</option>
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                            Descripcion del contenido
                            <input name="content_description" value="{{ old('content_description', $shipment->content_description) }}" @readonly($usesInventory) class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 read-only:bg-gray-100 read-only:text-gray-500">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Valor declarado
                            <input name="declared_value" type="number" min="0" step="100" value="{{ old('declared_value', $shipment->declared_value) }}" @readonly($usesInventory) class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 read-only:bg-gray-100 read-only:text-gray-500">
                        </label>
                        <select id="delivery_zone_id" name="delivery_zone_id" class="hidden">
                            <option value="">Manual</option>
                            @foreach ($deliveryZones as $deliveryZone)
                                <option
                                    value="{{ $deliveryZone->id }}"
                                    data-price="{{ (int) $deliveryZone->price }}"
                                    @selected(old('delivery_zone_id', $shipment->delivery_zone_id) == $deliveryZone->id)
                                >
                                    {{ $deliveryZone->name }} - ${{ number_format($deliveryZone->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Valor envio
                            <input id="shipping_value" name="shipping_value" type="number" min="0" step="100" value="{{ old('shipping_value', $shipment->shipping_value) }}" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">
                        </label>
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Valor a recaudar
                            <input name="collection_value" type="number" min="0" step="100" value="{{ old('collection_value', $shipment->collection_value) }}" @readonly($usesInventory) class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600 read-only:bg-gray-100 read-only:text-gray-500">
                        </label>
                    </div>
                </section>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('shipments.show', $shipment) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const deliveryZones = @json($deliveryZoneSuggestions);
        const senderPresets = @json($senderPresets);
        const companySenderPresetKeys = @json($companySenderPresetKeys);


        const upperCaseGuideFields = [
            'recipient_name',
            'recipient_address',
            'recipient_neighborhood',
            'recipient_locality',
            'content_description',
            'recipient_notes',
        ];

        const toGuideUpperCase = (value) => (value || '').toLocaleUpperCase('es-CO');

        upperCaseGuideFields.forEach((fieldName) => {
            document.querySelectorAll(`[name="${fieldName}"]`).forEach((field) => {
                field.classList.add('uppercase');

                let normalizing = false;
                field.addEventListener('input', () => {
                    if (normalizing) {
                        return;
                    }

                    const start = field.selectionStart;
                    const end = field.selectionEnd;
                    const upperValue = toGuideUpperCase(field.value);

                    if (field.value === upperValue) {
                        return;
                    }

                    normalizing = true;
                    field.value = upperValue;

                    if (typeof field.setSelectionRange === 'function' && start !== null && end !== null) {
                        field.setSelectionRange(start, end);
                    }

                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    normalizing = false;
                });
            });
        });
        const normalizeText = (value) => (value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

        const applySelectedRate = () => {
            const select = document.getElementById('delivery_zone_id');
            const selected = select?.options[select.selectedIndex];
            const price = selected?.dataset.price;

            if (price) {
                document.getElementById('shipping_value').value = price;
            }
        };

        const senderFields = {
            name: document.getElementById('sender_name'),
            phone: document.getElementById('sender_phone'),
            address: document.getElementById('sender_address'),
            neighborhood: document.getElementById('sender_neighborhood'),
            locality: document.getElementById('sender_locality'),
        };

        const senderPresetSelect = document.getElementById('sender_preset');
        const affiliatedCompanySelect = document.querySelector('[name="affiliated_company_id"]');
        let syncingSenderPreset = false;

        const applySenderPreset = (presetKey) => {
            const preset = senderPresets[presetKey];

            if (!preset) {
                return;
            }

            senderFields.name.value = preset.name || '';
            senderFields.phone.value = preset.phone || '';
            senderFields.address.value = preset.address || '';
            senderFields.neighborhood.value = preset.neighborhood || '';
            senderFields.locality.value = preset.locality || '';

            if (affiliatedCompanySelect) {
                syncingSenderPreset = true;
                affiliatedCompanySelect.value = preset.affiliated_company_id || '';
                affiliatedCompanySelect.dispatchEvent(new Event('change', { bubbles: true }));
                syncingSenderPreset = false;
            }
        };

        const suggestDeliveryZone = () => {
            const locality = document.getElementById('recipient_locality')?.value;
            const neighborhood = document.getElementById('recipient_neighborhood')?.value;
            const text = normalizeText(`${locality} ${neighborhood}`);

            if (!text) {
                return;
            }

            const matched = deliveryZones.find((zone) => {
                return normalizeText(zone.keywords)
                    .split(',')
                    .map((keyword) => keyword.trim())
                    .filter(Boolean)
                    .some((keyword) => text.includes(keyword));
            });

            if (matched) {
                document.getElementById('delivery_zone_id').value = matched.id;
                applySelectedRate();
            }
        };

        document.getElementById('delivery_zone_id')?.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const price = selected?.dataset.price;

            if (price) {
                document.getElementById('shipping_value').value = price;
            }
        });

        document.getElementById('recipient_locality')?.addEventListener('blur', suggestDeliveryZone);
        document.getElementById('recipient_neighborhood')?.addEventListener('blur', suggestDeliveryZone);
        document.getElementById('recipient_locality')?.addEventListener('input', suggestDeliveryZone);
        document.getElementById('recipient_neighborhood')?.addEventListener('input', suggestDeliveryZone);

        senderPresetSelect?.addEventListener('change', function () {
            applySenderPreset(this.value);
        });

        affiliatedCompanySelect?.addEventListener('change', function () {
            if (syncingSenderPreset) {
                return;
            }

            const presetKey = companySenderPresetKeys[this.value || ''] || (this.value ? `company_${this.value}` : 'rci');

            if (senderPresetSelect && senderPresetSelect.value !== presetKey && senderPresets[presetKey]) {
                senderPresetSelect.value = presetKey;
                applySenderPreset(presetKey);
            }
        });
    </script>

<!-- Tus Envios: sugerencias de ciudad y localidades de Bogota -->
<datalist id="colombia_city_suggestions">
    <option value="Bogota">
    <option value="Medellin">
    <option value="Cali">
    <option value="Barranquilla">
    <option value="Cartagena">
    <option value="Cucuta">
    <option value="Bucaramanga">
    <option value="Pereira">
    <option value="Santa Marta">
    <option value="Ibague">
    <option value="Manizales">
    <option value="Pasto">
    <option value="Monteria">
    <option value="Neiva">
    <option value="Armenia">
    <option value="Villavicencio">
    <option value="Valledupar">
    <option value="Popayan">
    <option value="Sincelejo">
    <option value="Tunja">
    <option value="Riohacha">
    <option value="Quibdo">
    <option value="Florencia">
    <option value="Yopal">
</datalist>
<datalist id="bogota_locality_suggestions">
    <option value="Usaquen">
    <option value="Chapinero">
    <option value="Santa Fe">
    <option value="San Cristobal">
    <option value="Usme">
    <option value="Tunjuelito">
    <option value="Bosa">
    <option value="Kennedy">
    <option value="Fontibon">
    <option value="Engativa">
    <option value="Suba">
    <option value="Barrios Unidos">
    <option value="Teusaquillo">
    <option value="Los Martires">
    <option value="Antonio Narino">
    <option value="Puente Aranda">
    <option value="La Candelaria">
    <option value="Rafael Uribe Uribe">
    <option value="Ciudad Bolivar">
    <option value="Sumapaz">
</datalist>

<!-- Tus Envios: barrio, ciudad y localidad separados -->
<script id="tus-envios-barrio-ciudad-localidad-v39">
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action*="shipments"]') || document.querySelector('form');
    const barrioInput = document.querySelector('[name="recipient_neighborhood"]');
    let cityInput = document.querySelector('[name="recipient_city"]');
    let oldLocalityInput = document.querySelector('[name="recipient_locality"]');

    if (!form || !barrioInput) {
        return;
    }

    const localidades = [
        'USAQUEN',
        'CHAPINERO',
        'SANTA FE',
        'SAN CRISTOBAL',
        'USME',
        'TUNJUELITO',
        'BOSA',
        'KENNEDY',
        'FONTIBON',
        'ENGATIVA',
        'SUBA',
        'BARRIOS UNIDOS',
        'TEUSAQUILLO',
        'LOS MARTIRES',
        'ANTONIO NARINO',
        'PUENTE ARANDA',
        'LA CANDELARIA',
        'RAFAEL URIBE URIBE',
        'CIUDAD BOLIVAR',
        'SUMAPAZ'
    ];

    const ciudades = [
        'BOGOTA',
        'MEDELLIN',
        'CALI',
        'BARRANQUILLA',
        'CARTAGENA',
        'CUCUTA',
        'BUCARAMANGA',
        'PEREIRA',
        'SANTA MARTA',
        'IBAGUE',
        'MANIZALES',
        'PASTO',
        'MONTERIA',
        'NEIVA',
        'ARMENIA',
        'VILLAVICENCIO',
        'VALLEDUPAR',
        'POPAYAN',
        'SINCELEJO',
        'TUNJA'
    ];

    const normalize = function (value) {
        return (value || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toUpperCase();
    };

    const ensureDatalist = function (id, options) {
        let datalist = document.getElementById(id);
        if (!datalist) {
            datalist = document.createElement('datalist');
            datalist.id = id;
            document.body.appendChild(datalist);
        }
        datalist.innerHTML = options.map(function (item) {
            return '<option value="' + item + '"></option>';
        }).join('');
        return datalist;
    };

    ensureDatalist('colombia_city_suggestions_v39', ciudades);

    const makeInput = function (name, id, value, placeholder) {
        const input = document.createElement('input');
        input.name = name;
        input.id = id;
        input.value = value || '';
        input.placeholder = placeholder || '';
        input.autocomplete = 'off';
        input.className = barrioInput.className;
        return input;
    };

    const makeLabel = function (text, field) {
        const label = document.createElement('label');
        label.className = 'grid gap-1 text-sm font-semibold text-gray-700';
        const span = document.createElement('span');
        span.textContent = text;
        label.appendChild(span);
        label.appendChild(field);
        return label;
    };

    barrioInput.style.display = '';
    barrioInput.removeAttribute('list');
    barrioInput.placeholder = barrioInput.placeholder || 'Barrio del destinatario';

    if (!cityInput) {
        cityInput = oldLocalityInput || makeInput('recipient_city', 'recipient_city', '', 'Ej. BOGOTA, MEDELLIN, CALI');
        cityInput.name = 'recipient_city';
        cityInput.id = 'recipient_city';
    }

    cityInput.setAttribute('list', 'colombia_city_suggestions_v39');
    cityInput.placeholder = 'Ej. BOGOTA, MEDELLIN, CALI';
    cityInput.style.display = '';

    let cityLabel = cityInput.closest('label');
    if (cityLabel) {
        const labelText = cityLabel.querySelector('span') || cityLabel.firstChild;
        if (labelText && labelText.textContent !== undefined) {
            labelText.textContent = 'Ciudad';
        }
    }

    let localityHidden = document.querySelector('input[type="hidden"][name="recipient_locality"]');
    if (!localityHidden) {
        localityHidden = document.createElement('input');
        localityHidden.type = 'hidden';
        localityHidden.name = 'recipient_locality';
        form.appendChild(localityHidden);
    }

    if (oldLocalityInput && oldLocalityInput !== cityInput && oldLocalityInput.type !== 'hidden') {
        oldLocalityInput.removeAttribute('name');
    }

    let localityWrapper = document.getElementById('recipient_locality_wrapper_v39');
    let localityText = document.getElementById('recipient_locality_text_v39');
    let localitySelect = document.getElementById('recipient_locality_select_v39');

    if (!localityWrapper) {
        localityText = makeInput('', 'recipient_locality_text_v39', localityHidden.value, 'Localidad, comuna o sector');

        localitySelect = document.createElement('select');
        localitySelect.id = 'recipient_locality_select_v39';
        localitySelect.className = barrioInput.className;
        localitySelect.innerHTML = '<option value="">Selecciona localidad de Bogota</option>' + localidades.map(function (localidad) {
            return '<option value="' + localidad + '">' + localidad + '</option>';
        }).join('');

        localityWrapper = document.createElement('label');
        localityWrapper.id = 'recipient_locality_wrapper_v39';
        localityWrapper.className = 'grid gap-1 text-sm font-semibold text-gray-700';
        localityWrapper.innerHTML = '<span>Localidad</span>';
        localityWrapper.appendChild(localityText);
        localityWrapper.appendChild(localitySelect);

        const cityParent = cityLabel || cityInput.parentElement;
        cityParent.insertAdjacentElement('afterend', localityWrapper);
    }

    const syncHidden = function () {
        const isBogota = normalize(cityInput.value).includes('BOGOTA');
        localityHidden.value = isBogota ? localitySelect.value : localityText.value;
        localityHidden.dispatchEvent(new Event('input', { bubbles: true }));
        localityHidden.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const applyMode = function () {
        const isBogota = normalize(cityInput.value).includes('BOGOTA');

        if (isBogota) {
            localityText.style.display = 'none';
            localitySelect.style.display = '';
            const current = normalize(localityHidden.value || localityText.value);
            const match = localidades.find(function (localidad) {
                return normalize(localidad) === current;
            });
            localitySelect.value = match || localitySelect.value || '';
        } else {
            localitySelect.style.display = 'none';
            localityText.style.display = '';
            if (!localityText.value && localityHidden.value) {
                localityText.value = localityHidden.value;
            }
        }

        syncHidden();
    };

    cityInput.addEventListener('input', applyMode);
    cityInput.addEventListener('change', applyMode);
    localitySelect.addEventListener('change', syncHidden);
    localityText.addEventListener('input', syncHidden);
    localityText.addEventListener('change', syncHidden);

    applyMode();
});
</script>
</x-app-layout>
