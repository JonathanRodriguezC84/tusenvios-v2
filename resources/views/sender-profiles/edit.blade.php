<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Remitentes</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Editar remitente</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('sender-profiles.update', $senderProfile) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
                @include('sender-profiles.form', ['senderProfile' => $senderProfile])
            </form>
        </div>
    </div>
</x-app-layout>
