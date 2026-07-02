<section>
    <h3 class="text-sm font-bold text-gray-900 mb-2">Actualizar contrasena</h3>
    <p class="text-xs text-gray-600 mb-3">Usa una contrasena larga y segura.</p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-3">
        @csrf @method('put')

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Contrasena actual</label>
            <input name="current_password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <x-input-error class="mt-1" :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nueva contrasena</label>
            <input name="password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <x-input-error class="mt-1" :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Confirmar contrasena</label>
            <input name="password_confirmation" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <x-input-error class="mt-1" :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button class="rounded-lg bg-blue-700 px-4 py-1.5 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Guardar</button>
            @if (session('status') === 'password-updated')
                <span class="text-xs text-gray-600">Guardado.</span>
            @endif
        </div>
    </form>
</section>
