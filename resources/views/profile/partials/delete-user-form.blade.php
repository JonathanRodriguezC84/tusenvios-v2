<section>
    <h3 class="text-sm font-bold text-gray-900 mb-2">Eliminar cuenta</h3>
    <p class="text-xs text-gray-600 mb-3">Una vez eliminada, todos tus datos se borraran permanentemente.</p>

    <button type="button" class="rounded-lg border border-red-300 text-red-700 px-4 py-1.5 text-sm font-semibold hover:bg-red-50" onclick="document.getElementById('delete-modal').classList.remove('hidden')">Eliminar cuenta</button>

    <div id="delete-modal" class="hidden fixed inset-0 z-50 bg-black/30 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h4 class="text-base font-bold text-gray-900">Estas seguro?</h4>
            <p class="text-sm text-gray-600 mt-2">Una vez eliminada tu cuenta, todos tus datos se eliminaran permanentemente. Ingresa tu contrasena para confirmar.</p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-4 space-y-3">
                @csrf @method('delete')
                <input name="password" type="password" required placeholder="Contrasena" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                <x-input-error :messages="$errors->userDeletion->get('password')" />

                <div class="flex gap-3 justify-end pt-1">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50" onclick="document.getElementById('delete-modal').classList.add('hidden')">Cancelar</button>
                    <button class="rounded-lg bg-red-700 px-4 py-1.5 text-sm font-bold text-white hover:bg-red-800">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</section>
