<section>
    <h3 class="text-sm font-bold text-gray-900 mb-2">Informacion del perfil</h3>
    <p class="text-xs text-gray-600 mb-3">Actualiza tu nombre y correo electronico.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-3">
        @csrf @method('patch')

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Nombre</label>
            <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-0.5">Email</label>
            <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <x-input-error class="mt-1" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button class="rounded-lg bg-blue-700 px-4 py-1.5 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">Guardar</button>
            @if (session('status') === 'profile-updated')
                <span class="text-xs text-gray-600">Guardado.</span>
            @endif
        </div>
    </form>
</section>
