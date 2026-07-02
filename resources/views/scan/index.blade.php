<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Mensajeria" title="Escaneo" description="Registra el estado de una guia escaneando el codigo de barras." />
    </x-slot>
    <div class="p-4 h-full flex flex-col items-center justify-center text-center">
        <div class="max-w-md">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5ZM14.25 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5ZM14.25 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5Z" /></svg>
            <h3 class="text-lg font-bold text-gray-900">Escanea tus guias</h3>
            <p class="mt-2 text-sm text-gray-600">Escanea el codigo de barras o ingresa el numero de guia para buscar y gestionar tus envios rapidamente.</p>
            <div class="mt-6 flex gap-2">
                <input type="text" placeholder="Numero de guia..." class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" onkeydown="if(event.key==='Enter') window.location.href='/track/'+this.value">
                <button onclick="const v=this.previousElementSibling.value; if(v) window.location.href='/track/'+v" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Buscar</button>
            </div>
        </div>
    </div>
</x-app-layout>
