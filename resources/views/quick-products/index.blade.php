@php
    use App\Services\InventoryService;
    $theme = app(InventoryService::class)->themeVariables();
    $qpColor = $theme['color'];
    $qpText = $theme['text'];
    $qpBorder = $theme['border'];
    $qpTint = $theme['tint'];
    $qpSoft = $theme['soft'];

    $packageLabels = [
        'package' => 'Paquete', 'document' => 'Documento', 'merchandise' => 'Mercancia',
    ];

    $toastMessages = [];
    if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }
    if ($errors->any()) { $toastMessages[] = ['text' => $errors->first() ?: 'Revisa los campos.', 'type' => 'error']; }
@endphp

<x-app-layout>
    @vite(['resources/css/quick-products.css', 'resources/js/quick-products.js'])

    <script id="qp-toast-data" type="application/json">{{ json_encode($toastMessages) }}</script>

    <style>
        .qp-page { --qp-color: {{ $qpColor }}; --qp-color-text: {{ $qpText }}; --qp-color-border: {{ $qpBorder }}; --qp-color-tint: {{ $qpTint }}; --qp-color-soft: {{ $qpSoft }}; }
    </style>

    <x-slot name="header">
        <x-page-header eyebrow="Productos" title="Productos rapidos" description="Administra tus productos frecuentes para llenar guias mas rapido." />
    </x-slot>

    <div class="qp-page p-3 lg:p-5 h-full flex flex-col space-y-3">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-3 lg:p-4">
            <form method="POST" action="{{ route('quick-products.store') }}" class="flex flex-wrap gap-2 items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombre</label>
                    <input name="name" value="{{ old('name') }}" required placeholder="Ej. Camiseta, kit skincare" class="qp-field">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-gray-600 mb-0.5">Tipo</label>
                    <select name="package_type" class="qp-field">
                        <option value="package">Paquete</option>
                        <option value="document">Documento</option>
                        <option value="merchandise" selected>Mercancia</option>
                    </select>
                </div>
                <button class="qp-btn qp-btn-primary mt-auto">Agregar producto</button>
            </form>
        </div>

        <div class="flex-1 min-h-0 rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500">Atajos disponibles</p>
                    <h3 class="text-base font-bold text-gray-900">{{ $products->total() }} producto(s)</h3>
                </div>
            </div>

            <div class="qp-head border-b border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-bold uppercase text-gray-500">
                <span>Producto</span>
                <span>Tipo</span>
                <span>Estado</span>
                <span>Accion</span>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-200">
                @if ($products->count() && $products->total() > 0)
                    @foreach ($products as $product)
                        <form method="POST" action="{{ route('quick-products.update', $product) }}" class="qp-row">
                            @csrf
                            @method('PATCH')

                            <div>
                                <p class="qp-mobile-label">Producto</p>
                                <input name="name" value="{{ old('name', $product->name) }}" required class="qp-field">
                            </div>

                            <div>
                                <p class="qp-mobile-label">Tipo</p>
                                <select name="package_type" class="qp-field">
                                    @foreach ($packageLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('package_type', $product->package_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <p class="qp-mobile-label">Estado</p>
                                <select name="status" class="qp-field">
                                    <option value="active" @selected(old('status', $product->status) === 'active')>Activo</option>
                                    <option value="paused" @selected(old('status', $product->status) === 'paused')>Pausado</option>
                                </select>
                            </div>

                            <div>
                                <p class="qp-mobile-label">Accion</p>
                                <div class="flex gap-1.5">
                                    <button class="qp-btn qp-btn-primary">Guardar</button>
                                    <button type="button" class="qp-btn qp-btn-secondary" onclick="document.getElementById('confirm-qp-{{ $product->id }}').classList.remove('hidden')">Eliminar</button>
                                </div>
                            </div>
                        </form>

                        <x-confirmation-modal id="confirm-qp-{{ $product->id }}" title="Eliminar producto" message="Se eliminara permanentemente el producto &quot;{{ $product->name }}&quot;." confirmText="Eliminar" cancelText="Cancelar" />
                        <form id="confirm-qp-{{ $product->id }}-form" method="POST" action="{{ route('quick-products.destroy', $product) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M6 7l1 13h10l1-13M9 7V5a3 3 0 0 1 6 0v2" /></svg>
                        <p class="text-base font-bold text-gray-950">Todavia no tienes productos rapidos</p>
                        <p class="mt-1 text-sm text-gray-500">Agrega los productos que mas repites para crear guias mas rapido.</p>
                    </div>
                @endif
            </div>

            @if ($products->hasPages())
                <div class="border-t border-gray-200 px-5 py-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
