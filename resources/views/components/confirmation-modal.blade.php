@props(['id' => '', 'title' => '¿Estas seguro?', 'message' => '', 'confirmText' => 'Eliminar', 'cancelText' => 'Cancelar', 'confirmClass' => 'bg-red-700 text-white hover:bg-red-800'])
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden bg-black/30 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h4 class="text-base font-bold text-gray-900">{{ $title }}</h4>
        @if ($message)
            <p class="text-sm text-gray-600 mt-2">{!! $message !!}</p>
        @endif
        <div class="mt-5 flex gap-3 justify-end">
            <button type="button" class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50" onclick="document.getElementById('{{ $id }}').classList.add('hidden')">{{ $cancelText }}</button>
            <button type="button" class="rounded-lg px-4 py-1.5 text-sm font-bold shadow-sm {{ $confirmClass }}" onclick="document.getElementById('{{ $id }}-form')?.submit()">{{ $confirmText }}</button>
        </div>
    </div>
</div>
