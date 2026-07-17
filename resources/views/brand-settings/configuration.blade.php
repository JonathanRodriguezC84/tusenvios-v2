@php
    $brand = $brandOwner->brandData();
    $logoUrl = $brand['logo_path'] ? Storage::url($brand['logo_path']) : null;
    $brandPalettes = [
        ['name' => 'Azul', 'color' => '#022a8c'],
        ['name' => 'Azul cielo', 'color' => '#0284c7'],
        ['name' => 'Naranja', 'color' => '#ff7a00'],
        ['name' => 'Naranja claro', 'color' => '#f97316'],
        ['name' => 'Verde', 'color' => '#059669'],
        ['name' => 'Verde lima', 'color' => '#65a30d'],
        ['name' => 'Rosa', 'color' => '#db2777'],
        ['name' => 'Rosa claro', 'color' => '#f472b6'],
        ['name' => 'Morado', 'color' => '#7c3aed'],
        ['name' => 'Morado claro', 'color' => '#a855f7'],
        ['name' => 'Rojo', 'color' => '#dc2626'],
        ['name' => 'Rojo oscuro', 'color' => '#b91c1c'],
        ['name' => 'Turquesa', 'color' => '#0891b2'],
        ['name' => 'Cian', 'color' => '#06b6d4'],
        ['name' => 'Amarillo', 'color' => '#eab308'],
        ['name' => 'Ambar', 'color' => '#d97706'],
        ['name' => 'Gris', 'color' => '#6b7280'],
        ['name' => 'Negro', 'color' => '#111827'],
        ['name' => 'Teal', 'color' => '#0d9488'],
        ['name' => 'Indigo', 'color' => '#4f46e5'],
        ['name' => 'Coral', 'color' => '#e11d48'],
        ['name' => 'Vino', 'color' => '#9f1239'],
        ['name' => 'Marron', 'color' => '#92400e'],
        ['name' => 'Oliva', 'color' => '#3f6212'],
    ];
    $selectedBrandColor = strtolower(old('brand_color', $brand['color'] ?? '#022a8c'));
@endphp

<x-app-layout>
        <style>
        .configuration-form-grid {
            display: grid;
            width: 100%;
            max-width: none;
            gap: 1rem;
            align-items: start;
        }

        .configuration-column {
            display: grid;
            gap: 1rem;
            align-content: start;
        }

        .configuration-card {
            min-height: 0;
            padding: 1rem !important;
        }

        .configuration-save-card {
            min-height: 72px;
            align-items: center;
        }

        .configuration-save-card button {
            min-width: 210px;
        }

        .settings-checkbox {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            max-width: 18px !important;
            min-height: 18px !important;
            max-height: 18px !important;
            flex: 0 0 18px !important;
            aspect-ratio: 1 / 1;
            padding: 0 !important;
        }

        .store-logo-preview {
            min-height: 7.75rem;
        }

        .store-logo-preview img {
            display: block;
            width: auto;
            height: auto;
            max-width: min(100%, 280px);
            max-height: 12rem;
            object-fit: contain;
        }

        .brand-palette-grid {
            display: grid;
            gap: 0.4rem;
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }

        .brand-palette-option {
            cursor: pointer;
        }

        .brand-palette-card {
            display: grid;
            min-height: 52px;
            place-items: center;
            gap: 0.25rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #fff;
            padding: 0.4rem 0.25rem;
            text-align: center;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .dark .brand-palette-card {
            background: #1f2937;
            border-color: #374151;
        }

        .dark .brand-palette-card span {
            color: #e5e7eb;
        }

        .brand-palette-option input:checked + .brand-palette-card {
            border-color: var(--palette-color);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--palette-color) 18%, transparent);
        }

        .brand-palette-option input:focus + .brand-palette-card {
            outline: 2px solid var(--palette-color);
            outline-offset: 2px;
        }

        .brand-palette-swatch {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            background: var(--palette-color);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.38), 0 1px 2px rgba(15,23,42,.18);
        }

        .brand-palette-preview {
            display: grid;
            gap: .75rem;
            border: 1px solid #dbe3ef;
            border-radius: .5rem;
            background: #f8fafc;
            padding: .875rem;
        }

        .dark .brand-palette-preview {
            background: #1f2937;
            border-color: #374151;
        }

        .dark .brand-palette-preview .text-gray-500 {
            color: #9ca3af;
        }

        .brand-palette-preview-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .65rem;
        }

        .brand-preview-button {
            border-radius: .45rem;
            background: var(--preview-color);
            color: var(--preview-text);
            font-weight: 800;
            padding: .65rem .9rem;
        }

        .brand-preview-selected {
            border: 1px solid var(--preview-color);
            border-radius: .45rem;
            background: color-mix(in srgb, var(--preview-color) 10%, white);
            color: var(--preview-color);
            font-weight: 850;
            padding: .55rem .75rem;
        }

        .brand-preview-pill {
            border-radius: 999px;
            background: color-mix(in srgb, var(--preview-color) 14%, white);
            color: var(--preview-color);
            font-weight: 850;
            padding: .4rem .65rem;
        }

        @media (min-width: 1024px) {
            .configuration-form-grid {
                grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
            }
        }

        @media (max-width: 1023px) {
            .configuration-save-card button {
                width: 100%;
            }
        }

        @media (max-width: 520px) {
            .brand-palette-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 420px) {
            .brand-palette-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .store-logo-preview {
                min-height: 8rem;
                padding: 1rem;
            }

            .store-logo-preview img {
                max-width: min(100%, 230px);
                max-height: 5.75rem;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Datos de tienda</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Configuracion</h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Informacion que se usa en tus etiquetas y en la identidad de la tienda.</p>
            </div>
            <a href="{{ route('brand-settings.edit') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Diseño de guia
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-5 lg:px-6">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">Revisa los campos marcados antes de guardar.</div>
            @endif

                        <form method="POST" action="{{ route('store-settings.update') }}" enctype="multipart/form-data" class="configuration-form-grid">
                @csrf
                @method('PATCH')
                <input type="hidden" name="brand_color_fallback" value="{{ $selectedBrandColor }}">

                <div class="configuration-column">
                    <section class="configuration-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-gray-500">Identidad</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Informacion principal</h3>

                        <div class="mt-4 grid gap-4">
                            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                Nombre de la tienda
                                <input name="name" value="{{ old('name', $brandOwner->name) }}" required class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>

                            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                Mensaje en etiqueta
                                <input name="brand_message" value="{{ old('brand_message', $brand['message']) }}" maxlength="120" placeholder="Gracias por tu compra" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>
                            <div class="grid gap-4 sm:grid-cols-2 te-company-data-v25">
                                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                    Telefono de la tienda
                                    <input name="brand_phone" value="{{ old('brand_phone', data_get($brand, 'phone') ?? '') }}" placeholder="3001234567" inputmode="tel" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                </label>

                                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                    Barrio / sector
                                    <input name="brand_neighborhood" value="{{ old('brand_neighborhood', data_get($brand, 'neighborhood') ?? '') }}" placeholder="Chapinero" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                </label>

                                <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                                    Direccion de la tienda
                                    <input name="brand_address" value="{{ old('brand_address', data_get($brand, 'address') ?? '') }}" placeholder="Bodega principal - Calle 100 #15-20" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                </label>

                                <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                                    Ciudad / localidad
                                    <input name="brand_locality" value="{{ old('brand_locality', data_get($brand, 'locality') ?? '') }}" placeholder="Bogota" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="configuration-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-gray-500">Redes sociales</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Canales de contacto</h3>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                WhatsApp
                                <input name="brand_whatsapp" value="{{ old('brand_whatsapp', $brand['whatsapp']) }}" placeholder="3001234567" inputmode="tel" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>

                            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                Instagram
                                <input name="brand_instagram" value="{{ old('brand_instagram', $brand['instagram']) }}" placeholder="@tumarca" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>

                            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                Facebook
                                <input name="brand_facebook" value="{{ old('brand_facebook', $brand['facebook'] ?? '') }}" placeholder="tumarca" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>

                            <label class="grid gap-1 text-sm font-semibold text-gray-700">
                                TikTok
                                <input name="brand_tiktok" value="{{ old('brand_tiktok', $brand['tiktok'] ?? '') }}" placeholder="@tumarca" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>

                            <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                                Sitio web
                                <input name="brand_website" value="{{ old('brand_website', $brand['website']) }}" placeholder="https://tumarca.com" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            </label>
                        </div>
                    </section>

                </div>

                <div class="configuration-column">
                    <section class="configuration-card configuration-logo-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-gray-500">Logo</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Imagen de la tienda</h3>

                        <div class="mt-4 flex gap-4 items-start">
                            <div class="flex flex-col gap-3 shrink-0">
                                <label class="cursor-pointer inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Seleccionar archivo
                                    <input name="logo" type="file" accept="image/*" class="hidden">
                                </label>
                                @if ($logoUrl)
                                    <label id="remove-logo-btn" class="cursor-pointer inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50" onclick="toggleRemoveLogo()">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span id="remove-logo-label">Quitar logo</span>
                                    </label>
                                    <input name="remove_logo" type="checkbox" value="1" id="remove-logo-checkbox" class="hidden">
                                @endif
                            </div>

                            <div class="store-logo-preview flex-1 grid place-items-center rounded-lg border border-gray-200 bg-gray-50 p-4" style="min-height: 260px;">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $brandOwner->name }}">
                                @else
                                    <span class="text-sm font-semibold text-gray-500">Sin logo</span>
                                @endif
                            </div>
                        </div>
                    </section>

                    <section class="configuration-card rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-gray-500">Color de marca</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Tema visual</h3>
                        <p class="mt-1 text-sm text-gray-500">Elige una paleta para botones, seleccion y detalles de tu panel.</p>

                        <div class="brand-palette-grid mt-4">
                            @foreach ($brandPalettes as $palette)
                                <label class="brand-palette-option" style="--palette-color: {{ $palette['color'] }}">
                                    <input type="radio" name="brand_color" value="{{ $palette['color'] }}" class="sr-only" @checked(strtolower($palette['color']) === $selectedBrandColor)>
                                    <span class="brand-palette-card">
                                        <span class="brand-palette-swatch"></span>
                                        <span class="text-xs font-black text-gray-700">{{ $palette['name'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                            @php
                                $customColor = $selectedBrandColor;
                                $isCustom = collect($brandPalettes)->every(fn ($p) => strtolower($p['color']) !== $selectedBrandColor);
                            @endphp
                            <label class="brand-palette-option" style="--palette-color: {{ $customColor }}">
                                <input type="radio" name="brand_color" value="{{ $customColor }}" class="sr-only" id="brand-color-custom-radio" @checked($isCustom)>
                                <input type="color" id="brand-color-custom-picker" value="{{ $customColor }}" class="sr-only">
                                <span class="brand-palette-card" onclick="document.getElementById('brand-color-custom-picker').click()">
                                    <span class="brand-palette-swatch" style="background: conic-gradient(red, yellow, lime, aqua, blue, magenta, red)"></span>
                                    <span class="text-xs font-black text-gray-700">Personalizado</span>
                                </span>
                            </label>
                        </div>

                        <div id="brand-color-preview" class="brand-palette-preview mt-4" style="--preview-color: {{ $selectedBrandColor }}; --preview-text: #ffffff;">
                            <p class="text-xs font-black uppercase text-gray-500">Vista rapida</p>
                            <div class="brand-palette-preview-row">
                                <span class="brand-preview-button">Boton principal</span>
                                <span class="brand-preview-selected">Seleccion activa</span>
                                <span class="brand-preview-pill">Estado</span>
                            </div>
                        </div>
                    </section>

                    <div class="configuration-save-card flex justify-end rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <button class="rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                            Guardar configuracion
                        </button>
                    </div>
                </div>
</form>

            {{-- Categories Management --}}
        </div>
    </div>
<script>
        document.addEventListener('DOMContentLoaded', () => {
            const preview = document.getElementById('brand-color-preview');
            const radios = Array.from(document.querySelectorAll('input[name="brand_color"]'));
            const customRadio = document.getElementById('brand-color-custom-radio');
            const customPicker = document.getElementById('brand-color-custom-picker');

            const contrastText = (hex) => {
                const clean = hex.replace('#', '');
                const r = parseInt(clean.substring(0, 2), 16);
                const g = parseInt(clean.substring(2, 4), 16);
                const b = parseInt(clean.substring(4, 6), 16);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b);
                return luminance > 165 ? '#111827' : '#ffffff';
            };

            const refresh = () => {
                const selected = radios.find((radio) => radio.checked);
                if (! selected || ! preview) return;
                const color = selected.value;
                preview.style.setProperty('--preview-color', color);
                preview.style.setProperty('--preview-text', contrastText(color));
                if (customRadio && customPicker) {
                    const swatch = customRadio.closest('.brand-palette-option')?.querySelector('.brand-palette-swatch');
                    if (swatch && customRadio.checked) {
                        swatch.style.background = color;
                        swatch.style.boxShadow = 'inset 0 0 0 1px rgba(255,255,255,.38), 0 1px 2px rgba(15,23,42,.18)';
                    }
                }
            };

            radios.forEach((radio) => radio.addEventListener('change', refresh));

            if (customPicker) {
                customPicker.addEventListener('input', () => {
                    const color = customPicker.value;
                    customRadio.value = color;
                    customRadio.checked = true;
                    customRadio.closest('.brand-palette-option').style.setProperty('--palette-color', color);
                    refresh();
                });
            }

            refresh();
        });

        function toggleRemoveLogo() {
            const cb = document.getElementById('remove-logo-checkbox');
            const btn = document.getElementById('remove-logo-btn');
            const label = document.getElementById('remove-logo-label');
            const preview = document.querySelector('.store-logo-preview img');
            if (!cb) return;

            cb.checked = !cb.checked;

            if (cb.checked) {
                btn.classList.add('border-red-400', 'bg-red-50');
                label.textContent = 'Restaurar logo';
                if (preview) preview.style.display = 'none';
            } else {
                btn.classList.remove('border-red-400', 'bg-red-50');
                label.textContent = 'Quitar logo';
                if (preview) preview.style.display = '';
            }
        }

        function categoriesManager() {
            return {
                categories: [],
                loading: true,
                newName: '',
                editingId: null,
                editName: '',

                async init() {
                    await this.load();
                },

                async load() {
                    this.loading = true;
                    try {
                        const res = await fetch('/categories', { headers: { 'Accept': 'application/json' } });
                        if (res.ok) {
                            this.categories = await res.json();
                        }
                    } catch (e) {
                        console.error('Error loading categories', e);
                    }
                    this.loading = false;
                },

                async addCategory() {
                    const name = this.newName.trim();
                    if (!name) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        const res = await fetch('/categories', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ name }),
                        });
                        if (res.ok) {
                            const cat = await res.json();
                            this.categories.push(cat);
                            this.categories.sort((a, b) => a.name.localeCompare(b.name));
                            this.newName = '';
                        } else if (res.status === 409) {
                            alert('Esta categoria ya existe.');
                        }
                    } catch (e) {
                        console.error('Error adding category', e);
                    }
                },

                startEdit(cat) {
                    this.editingId = cat.id;
                    this.editName = cat.name;
                },

                cancelEdit() {
                    this.editingId = null;
                    this.editName = '';
                },

                async updateCategory(cat) {
                    const name = this.editName.trim();
                    if (!name) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        const res = await fetch(`/categories/${cat.id}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ name }),
                        });
                        if (res.ok) {
                            const updated = await res.json();
                            const idx = this.categories.findIndex(c => c.id === cat.id);
                            if (idx !== -1) this.categories[idx] = updated;
                            this.categories.sort((a, b) => a.name.localeCompare(b.name));
                            this.cancelEdit();
                        } else if (res.status === 409) {
                            alert('Esta categoria ya existe.');
                        }
                    } catch (e) {
                        console.error('Error updating category', e);
                    }
                },

                async deleteCategory(cat) {
                    if (!confirm(`Eliminar la categoria "${cat.name}"?`)) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    try {
                        const res = await fetch(`/categories/${cat.id}`, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        });
                        if (res.ok) {
                            this.categories = this.categories.filter(c => c.id !== cat.id);
                        }
                    } catch (e) {
                        console.error('Error deleting category', e);
                    }
                },
            };
        }
    </script>

    <style id="te-config-mobile-width-v13">
        .configuration-form-grid,
        .configuration-column,
        .configuration-card,
        .configuration-save-card,
        .brand-palette-grid,
        .brand-palette-preview,
        .store-logo-preview {
            box-sizing: border-box;
            max-width: 100%;
            min-width: 0;
        }

        .configuration-card,
        .configuration-save-card {
            overflow: hidden;
        }

        .configuration-card label,
        .configuration-card input,
        .configuration-card textarea,
        .configuration-card select,
        .configuration-card button {
            box-sizing: border-box;
            max-width: 100%;
            min-width: 0;
        }

        .configuration-card input[type="file"] {
            width: 100%;
        }

        .store-logo-preview img {
            display: block;
            height: auto;
            max-height: 8rem;
            max-width: min(100%, 250px);
            object-fit: contain;
            object-position: center;
        }

        @media (max-width: 767px) {
            body {
                overflow-x: hidden;
            }

            .configuration-form-grid {
                width: 100%;
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .configuration-column {
                width: 100%;
            }

            .configuration-card,
            .configuration-save-card {
                width: 100%;
                padding: 1rem;
            }

            .configuration-logo-card {
                min-height: 0 !important;
            }

            .store-logo-preview {
                padding: 1rem;
            }

            .store-logo-preview img {
                max-height: 120px;
                max-width: min(100%, 220px);
            }

            .configuration-card input[type="file"] {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .configuration-card input[type="file"]::file-selector-button {
                margin-right: 0.5rem;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .brand-palette-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .brand-palette-preview-row {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr);
                gap: 0.5rem;
            }

            .configuration-save-card button {
                width: 100%;
            }
        }
    </style>

<!-- TE_CONFIG_COMPACTA_V01_START -->
<style>
    @media (min-width: 1024px) {
        main .py-6 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        main .max-w-7xl {
            max-width: none !important;
        }

        .configuration-form-grid {
            gap: 0.75rem !important;
        }

        .configuration-column {
            gap: 0.75rem !important;
        }

        .configuration-card {
            padding: 0.85rem 1rem !important;
        }

        .configuration-card h3 {
            margin-top: 0.15rem !important;
        }

        .configuration-card .mt-4 {
            margin-top: 0.75rem !important;
        }

        .configuration-card label {
            gap: 0.2rem !important;
        }

        .configuration-card input:not([type="checkbox"]):not([type="radio"]) {
            min-height: 2.25rem !important;
            padding-top: 0.42rem !important;
            padding-bottom: 0.42rem !important;
        }

        .configuration-card input[type="file"] {
            min-height: 2.35rem !important;
            padding-top: 0.32rem !important;
            padding-bottom: 0.32rem !important;
        }

        .configuration-card input[type="file"]::file-selector-button {
            padding-top: 0.42rem !important;
            padding-bottom: 0.42rem !important;
        }

        .configuration-card .grid.gap-4,
        .configuration-card .te-company-data-v25 {
            gap: 0.7rem !important;
        }

        .store-logo-preview {
            min-height: 5.4rem !important;
            padding: 0.65rem !important;
        }

        .store-logo-preview img {
            max-height: 10rem !important;
            max-width: min(100%, 220px) !important;        }

        .brand-palette-grid {
            gap: 0.45rem !important;
            margin-top: 0.65rem !important;
        }

        .brand-palette-card {
            min-height: 44px !important;
            gap: 0.15rem !important;
            padding: 0.3rem 0.2rem !important;
        }

        .brand-palette-swatch {
            width: 16px !important;
            height: 16px !important;
        }

        .brand-palette-preview {
            gap: 0.45rem !important;
            margin-top: 0.65rem !important;
            padding: 0.65rem !important;
        }

        .brand-preview-button,
        .brand-preview-selected,
        .brand-preview-pill {
            padding-top: 0.45rem !important;
            padding-bottom: 0.45rem !important;
        }

        .configuration-save-card {
            min-height: 0 !important;
            padding: 0.75rem 1rem !important;
        }

        .configuration-save-card button {
            padding-top: 0.65rem !important;
            padding-bottom: 0.65rem !important;
        }
    }
</style>
<!-- TE_CONFIG_COMPACTA_V01_END -->
</x-app-layout>
