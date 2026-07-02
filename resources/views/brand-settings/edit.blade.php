@php
    $brand = $brandOwner->brandData();
    $logoUrl = $brand['logo_path'] ? Storage::url($brand['logo_path']) : null;
    $templates = [
        'classic' => ['name' => 'Clasica', 'description' => 'Marca, redes, codigo, destinatario y cierre.'],
        'modern' => ['name' => 'Moderna', 'description' => 'Destinatario y QR primero, codigo al centro bajo observaciones.'],
        'advance' => ['name' => 'Advance', 'description' => 'Codigo superior y lectura rapida desde el inicio.'],
    ];
    $selectedTemplate = old('label_template', in_array($brand['template'] ?? 'classic', array_keys($templates), true) ? $brand['template'] : 'classic');

    $printFormats = [
        '100x150' => 'Termica 100 x 150 mm',
        '100x100' => 'Termica 100 x 100 mm',
        '80x50' => 'Termica 50 x 80 mm (vertical)',
        'half-letter' => 'Media carta',
        'letter' => 'Carta (1 por hoja)',
    ];
    $selectedPrintFormat = old('default_print_format', $brand['default_print_format'] ?? '100x150');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Diseño de guia</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Mi marca</h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Elige como se vera la etiqueta final.</p>
            </div>
            <a href="{{ route('store-settings.edit') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Configuracion</a>
        </div>
    </x-slot>

    <style>
        .brand-settings-layout { display: grid; gap: 1.25rem; }
        .brand-settings-preview { min-width: 0; }
        .label-preview-scroll { display: flex; justify-content: center; max-height: none; overflow: hidden; }
        .label-preview-fit { height: 108mm; margin: 0 auto; position: relative; width: 72mm; }
        .label-preview-fit-inner { height: 150mm; left: 0; position: absolute; top: 0; transform: scale(0.72); transform-origin: top left; width: 100mm; }
        @media (min-width: 1280px) {
            .label-preview-fit { height: 117mm; width: 78mm; }
            .label-preview-fit-inner { transform: scale(0.78); }
        }
        @media (min-width: 1024px) {
            .brand-settings-layout { grid-template-columns: minmax(0, 1fr) 390px; align-items: start; }
            .brand-settings-preview { position: sticky; top: 1.5rem; }
        }
    </style>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <form method="POST" action="{{ route('brand-settings.update') }}" class="brand-settings-layout">
                @csrf
                @method('PATCH')

                <div class="grid content-start gap-5">
                    @if (session('status'))
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">Revisa los campos marcados antes de guardar.</div>
                    @endif

                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-gray-500">Tipo de guia</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Selecciona una plantilla</h3>
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            @foreach ($templates as $value => $templateInfo)
                                <label class="cursor-pointer rounded-md border border-gray-200 bg-white p-4 shadow-sm transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="label_template" value="{{ $value }}" @checked($selectedTemplate === $value) data-template-radio data-template-name="{{ $templateInfo['name'] }}" class="sr-only">
                                    <span class="block text-sm font-black text-gray-950">{{ $templateInfo['name'] }}</span>
                                    <span class="mt-1 block text-xs leading-5 text-gray-500">{{ $templateInfo['description'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Formato de impresion</p>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900">Formato predeterminado</h3>
                        <p class="mt-1 text-sm text-gray-500">Se usa al imprimir guias. Puedes cambiarlo en cada impresion.</p>
                        <div class="mt-4">
                            <select name="default_print_format" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                @foreach ($printFormats as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedPrintFormat === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3">
                            <p class="text-xs font-semibold text-blue-800">Formatos "por hoja" incluyen lineas de corte para recortar con facilidad.</p>
                        </div>
                    </section>

                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h3 class="mt-1 text-lg font-black text-gray-950">{{ $brand['name'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600">Logo, redes, color y mensaje se editan desde Configuracion.</p>
                        <a href="{{ route('store-settings.edit') }}" class="mt-4 inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Editar datos de tienda</a>
                    </section>

                    <button class="w-fit rounded-md bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">Guardar diseño</button>
                </div>

                <aside class="brand-settings-preview">
                    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase text-gray-500">Vista previa</p>
                                <h3 class="mt-1 text-lg font-black text-gray-950">Etiqueta final</h3>
                                <p class="mt-1 text-xs font-semibold text-emerald-700">100mm x 150mm</p>
                            </div>
                            <span id="preview-template-name" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800">{{ $templates[$selectedTemplate]['name'] ?? 'Clasica' }}</span>
                        </div>

                        <div class="label-preview-scroll mt-5 rounded-lg bg-gray-100 p-3">
                            <style>{!! file_get_contents(resource_path('views/brand-settings/partials/label-demo.css')) !!}</style>
                            <div class="label-preview-fit"><div class="label-preview-fit-inner">
                                @foreach ($templates as $value => $templateInfo)
                                    <div data-template-preview="{{ $value }}" class="{{ $selectedTemplate === $value ? '' : 'hidden' }}">
                                        @include('brand-settings.partials.label-demo', ['brand' => $brand, 'logoUrl' => $logoUrl, 'template' => $value])
                                    </div>
                                @endforeach
                            </div></div>
                        </div>

                        <a href="{{ route('brand-settings.preview') }}" target="_blank" class="mt-4 inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Ver etiqueta real</a>
                    </section>
                </aside>
            </form>
        </div>
    </div>

    <script>
        const previewTemplateName = document.getElementById('preview-template-name');
        const applyTemplatePreview = (template, templateName = '') => {
            document.querySelectorAll('[data-template-preview]').forEach((preview) => {
                preview.classList.toggle('hidden', preview.dataset.templatePreview !== template);
            });
            if (previewTemplateName && templateName) previewTemplateName.textContent = templateName;
        };
        document.querySelectorAll('[data-template-radio]').forEach((radio) => {
            radio.addEventListener('change', () => applyTemplatePreview(radio.value, radio.dataset.templateName));
            if (radio.checked) applyTemplatePreview(radio.value, radio.dataset.templateName);
        });
    </script>
</x-app-layout>