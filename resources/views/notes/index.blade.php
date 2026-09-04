@php
    $toastMessages = [];
    if (session('status')) { $toastMessages[] = ['text' => session('status'), 'type' => 'success']; }
    if ($errors->any()) { $toastMessages[] = ['text' => $errors->first() ?: 'Revisa los campos.', 'type' => 'error']; }
@endphp

<x-app-layout>
    <script id="note-toast-data" type="application/json">{{ json_encode($toastMessages) }}</script>

    <x-slot name="header">
        <x-page-header eyebrow="{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}" title="Mis notas" description="Escribe y consulta todo lo que necesites tener en cuenta.">
            <x-slot name="actions">
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-3 lg:p-5 h-full flex flex-col">
        {{-- Nueva nota --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-3 lg:p-4 shrink-0">
            <form id="nueva-nota-form" method="POST" action="{{ route('notes.store') }}" class="flex flex-wrap gap-2 items-center">
                @csrf
                <input name="title" value="{{ old('title') }}" required maxlength="200" placeholder="Titulo de la nota..." class="flex-1 min-w-[200px] rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <input name="content" value="{{ old('content') }}" maxlength="10000" placeholder="Escribe aqui lo que necesitas recordar..." class="flex-1 min-w-[260px] rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <button class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-1.5 text-sm font-bold text-white hover:bg-blue-800 min-h-[36px] shadow-sm">Guardar nota</button>
            </form>
        </div>

        {{-- Buscar --}}
        <div class="mt-3 rounded-xl border border-gray-200 bg-white shadow-sm p-3 lg:p-4 shrink-0">
            <form method="GET" action="{{ route('notes.index') }}" class="flex flex-wrap gap-2 items-center">
                <input name="search" value="{{ $search }}" type="search" placeholder="Buscar por titulo o contenido..." class="flex-1 min-w-[200px] rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <button class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 min-h-[36px]">Filtrar</button>
                <a href="{{ route('notes.index') }}" class="bg-white inline-flex items-center rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 min-h-[36px]">Limpiar</a>
            </form>
        </div>

        {{-- Lista tipo tabla --}}
        <div class="mt-3 flex-1 min-h-0 overflow-y-auto">
            <style>
                .nt-list-header, .nt-list-row {
                    display: grid;
                    grid-template-columns: minmax(0,1fr) minmax(0,1.3fr) minmax(105px,0.55fr) 190px;
                    align-items: center;
                    gap: 6px;
                }
                .nt-list-row {
                    min-height: 44px;
                    padding: 6px 12px;
                    border-bottom: 1px solid #f3f4f6;
                    transition: background 0.1s;
                }
                .nt-list-row:hover { background: #f9fafb; }
                .nt-list-cell {
                    display: flex;
                    align-items: center;
                    min-width: 0;
                }
                .nt-mobile-row { display: none; }

                @media (max-width: 1023px) {
                    .nt-list-header, .nt-desktop-row { display: none; }
                    .nt-mobile-row { display: flex; align-items: center; }
                }
            </style>

            @if ($notes->count())
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="nt-list-header bg-gray-50 px-3 py-2 text-xs font-bold uppercase text-gray-500 border-b border-gray-200">
                        <span class="nt-list-cell">Nota</span>
                        <span class="nt-list-cell">Contenido</span>
                        <span class="nt-list-cell">Actualizada</span>
                        <span class="nt-list-cell">Accion</span>
                    </div>

                    @foreach ($notes as $note)
                        @php
                            $noteColor = '#6366f1';
                            $noteBadge = 'bg-indigo-100 text-indigo-800';
                        @endphp
                        {{-- Desktop --}}
                        <form method="POST" action="{{ route('notes.update', $note) }}" class="nt-list-row nt-desktop-row" data-note-row style="border-left:3px solid {{ $noteColor }}">
                            @csrf
                            @method('PATCH')

                            <div class="nt-list-cell">
                                <input name="title" value="{{ old('title', $note->title) }}" required maxlength="200" class="note-field w-full rounded-lg border border-transparent px-1.5 py-1 text-sm font-semibold text-gray-950 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" disabled>
                            </div>
                            <div class="nt-list-cell">
                                <textarea name="content" rows="2" maxlength="10000" class="note-field w-full resize-none rounded-lg border border-transparent px-1.5 py-1 text-sm leading-relaxed text-gray-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" disabled>{{ old('content', $note->content) }}</textarea>
                            </div>
                            <div class="nt-list-cell" style="flex-direction:column;align-items:stretch">
                                <p class="text-sm font-semibold text-gray-700">{{ $note->updated_at->format('d/m/y') }}</p>
                                <p class="text-xs text-gray-400">{{ $note->updated_at->format('H:i') }}</p>
                            </div>
                            <div class="nt-list-cell" style="gap:3px">
                                <button type="button" class="note-row-edit-btn flex items-center justify-center rounded-md border border-gray-300 bg-white w-8 h-8 text-gray-500 hover:bg-gray-50 hover:text-gray-700" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <button type="submit" class="note-row-save-btn hidden flex items-center justify-center rounded-md bg-blue-700 w-8 h-8 text-white hover:bg-blue-800" title="Guardar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </button>
                                <button type="button" class="note-row-delete-btn flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-red-600 hover:bg-red-50 hover:text-red-700" title="Eliminar" onclick="document.getElementById('confirm-note-{{ $note->id }}').classList.remove('hidden')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </div>
                        </form>

                        {{-- Mobile --}}
                        <form method="POST" action="{{ route('notes.update', $note) }}" class="nt-list-row nt-mobile-row" data-note-row style="border-left:3px solid {{ $noteColor }}">
                            @csrf
                            @method('PATCH')

                            <div class="nt-list-cell" style="flex-direction:column;align-items:stretch;gap:6px;flex:1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $noteBadge }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5" style="background:{{ $noteColor }}"></span>
                                        Nota
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="note-row-edit-btn flex items-center justify-center rounded border border-gray-300 bg-white w-8 h-8 text-gray-500 hover:bg-gray-50" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </button>
                                        <button type="button" class="note-row-delete-btn flex items-center justify-center rounded border border-red-200 bg-white w-8 h-8 text-red-600 hover:bg-red-50" title="Eliminar" onclick="document.getElementById('confirm-note-{{ $note->id }}').classList.remove('hidden')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <input name="title" value="{{ old('title', $note->title) }}" required maxlength="200" class="note-field w-full rounded-lg border border-transparent px-1.5 py-1 text-sm font-semibold text-gray-950 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" disabled>
                                <textarea name="content" rows="2" maxlength="10000" class="note-field w-full resize-none rounded-lg border border-transparent px-1.5 py-1 text-sm leading-relaxed text-gray-700 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600" disabled>{{ old('content', $note->content) }}</textarea>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                                    <div>
                                        <p class="font-semibold text-gray-500">Actualizada</p>
                                        <p class="font-semibold text-gray-700">{{ $note->updated_at->format('d/m/y') }}</p>
                                        <p class="text-gray-400">{{ $note->updated_at->format('H:i') }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-500">Estado</p>
                                        <p class="font-semibold text-gray-900">Nota guardada</p>
                                    </div>
                                </div>
                                <button type="submit" class="note-row-save-btn hidden w-full rounded border border-blue-700 bg-blue-700 py-2 text-xs font-bold text-white hover:bg-blue-800">
                                    <span class="inline-flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        Guardar cambios
                                    </span>
                                </button>
                            </div>
                        </form>

                        <x-confirmation-modal id="confirm-note-{{ $note->id }}" title="Eliminar nota" message="Se eliminara permanentemente la nota &quot;{{ $note->title }}&quot;." confirmText="Eliminar" cancelText="Cancelar" />
                        <form id="confirm-note-{{ $note->id }}-form" method="POST" action="{{ route('notes.destroy', $note) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-20 text-center px-6 h-full">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    </div>
                    <p class="text-lg font-bold text-gray-950">{{ $search !== '' ? 'No hay notas que coincidan' : 'No hay notas' }}</p>
                    <p class="mt-1 text-sm text-gray-500 max-w-xs">{{ $search !== '' ? 'Prueba con otro termino de busqueda.' : 'Agrega tu primera nota en el cuadro de arriba.' }}</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .note-field {
            opacity: 0.8;
            cursor: default;
        }
        .note-row.is-editing .note-field {
            opacity: 1;
            cursor: auto;
        }
        .note-row.is-editing .note-row-edit-btn {
            display: none;
        }
        .note-row-save-btn {
            display: none;
        }
        .note-row.is-editing .note-row-save-btn {
            display: inline-flex;
        }
        @media (max-width: 1023px) {
            .note-row.is-editing .note-row-save-btn {
                display: flex;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastData = document.getElementById('note-toast-data');
            if (toastData) {
                let messages = [];
                try { messages = JSON.parse(toastData.textContent); } catch (e) {}
                if (messages.length && window.Alpine) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: messages[0].text, type: messages[0].type } }));
                }
            }

            document.querySelectorAll('[data-note-row]').forEach(function (form) {
                const editBtn = form.querySelector('.note-row-edit-btn');
                const saveBtn = form.querySelector('.note-row-save-btn');
                const inputs = form.querySelectorAll('.note-field');

                if (editBtn) {
                    editBtn.addEventListener('click', function () {
                        form.classList.add('is-editing');
                        editBtn.classList.add('hidden');
                        saveBtn.classList.remove('hidden');
                        inputs.forEach(function (input) {
                            input.disabled = false;
                            input.classList.remove('border-transparent');
                            input.classList.add('border-gray-300');
                        });
                        const first = form.querySelector('input[name="title"]');
                        if (first) first.focus();
                    });
                }
            });
        });
    </script>
</x-app-layout>
