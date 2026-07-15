<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Clientes frecuentes" description="Compradores que ya usaste en guias anteriores para crear nuevos envios mas rapido.">
            <x-slot name="eyebrow">Agenda comercial</x-slot>
            <x-slot name="actions">
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Clientes guardados</p>
                <p class="mt-2 text-3xl font-black text-gray-950">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Compradores repetidos</p>
                <p class="mt-2 text-3xl font-black text-blue-900">{{ $summary['repeat'] }}</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">Usos acumulados</p>
                <p class="mt-2 text-3xl font-black text-emerald-900">{{ $summary['uses'] }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Ciudad mas frecuente</p>
                <p class="mt-2 truncate text-2xl font-black text-gray-950">{{ $summary['topCity'] ?: 'Sin datos' }}</p>
            </div>
        </section>

        <section class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('recipients.index') }}" class="flex flex-col gap-2 sm:flex-row">
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Buscar por nombre, telefono, ciudad o direccion..." class="min-h-10 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Buscar</button>
                <a href="{{ route('recipients.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50">Limpiar</a>
            </form>
        </section>

        <section class="mt-5 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            @if ($recipients->count())
                <div class="hidden grid-cols-[minmax(160px,1.2fr)_minmax(130px,0.8fr)_minmax(180px,1.5fr)_100px_120px] gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-black uppercase tracking-wider text-gray-500 lg:grid">
                    <span>Cliente</span>
                    <span>Telefono</span>
                    <span>Direccion</span>
                    <span>Usos</span>
                    <span>Accion</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($recipients as $recipient)
                        @php
                            $fullName = trim($recipient->name.' '.$recipient->lastname);
                            $addressLine = collect([$recipient->address, $recipient->neighborhood, $recipient->city ?: $recipient->locality])->filter()->join(' - ');
                            $copyText = collect([$fullName, $recipient->phone, $addressLine])->filter()->join(PHP_EOL);
                        @endphp
                        <article class="grid gap-3 px-5 py-4 lg:grid-cols-[minmax(160px,1.2fr)_minmax(130px,0.8fr)_minmax(180px,1.5fr)_100px_120px] lg:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-gray-950">{{ $fullName ?: 'Cliente sin nombre' }}</p>
                                @if ($recipient->document)
                                    <p class="mt-0.5 text-xs font-semibold text-gray-500">Doc. {{ $recipient->document }}</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $recipient->phone ?: 'Sin telefono' }}</p>
                                @if ($recipient->alt_phone)
                                    <p class="text-xs font-semibold text-gray-500">{{ $recipient->alt_phone }}</p>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $addressLine ?: 'Sin direccion guardada' }}</p>
                                @if ($recipient->notes)
                                    <p class="mt-0.5 truncate text-xs text-gray-500">{{ $recipient->notes }}</p>
                                @endif
                            </div>
                            <div>
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-800">{{ $recipient->use_count }} guia(s)</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" data-copy-recipient="{{ $copyText }}" onclick="copyRecipient(this)" class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar</button>
                                @if (Auth::user()->canCreateShipments())
                                    <a href="{{ route('shipments.create', ['recipient' => $recipient->id]) }}" class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-700 px-3 py-2 text-xs font-black text-white shadow-sm hover:bg-blue-800">Guia</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($recipients->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">{{ $recipients->links() }}</div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <p class="text-lg font-black text-gray-950">Aun no hay clientes frecuentes</p>
                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold text-gray-500">Cuando crees guias, el sistema ira guardando destinatarios para ayudarte a repetir datos rapidamente.</p>
                    @if (Auth::user()->canCreateShipments())
                        <a href="{{ route('shipments.create') }}" class="mt-5 inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Crear primera guia</a>
                    @endif
                </div>
            @endif
        </section>
    </div>

    <script>
        function copyRecipient(button) {
            const text = button.dataset.copyRecipient || '';
            const copy = navigator.clipboard
                ? navigator.clipboard.writeText(text)
                : new Promise((resolve) => {
                    const input = document.createElement('textarea');
                    input.value = text;
                    input.setAttribute('readonly', 'readonly');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    resolve();
                });

            copy.then(() => {
                const original = button.textContent;
                button.textContent = 'Copiado';
                button.disabled = true;
                setTimeout(() => {
                    button.textContent = original;
                    button.disabled = false;
                }, 1600);
            });
        }
    </script>
</x-app-layout>
