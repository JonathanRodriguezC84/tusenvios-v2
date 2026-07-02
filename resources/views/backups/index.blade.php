<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Sistema</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Backups</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-gray-500">Exportacion local</p>
                <h3 class="mt-1 text-lg font-semibold text-gray-950">Descargar respaldo JSON</h3>
                <p class="mt-3 text-sm leading-6 text-gray-600">
                    Descarga un respaldo de las tablas principales del sistema. Este archivo sirve como copia de seguridad
                    local antes de hacer cambios grandes o preparar despliegues.
                </p>

                <div class="mt-5">
                    <a href="{{ route('backups.export') }}" class="inline-flex rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                        Descargar backup
                    </a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
