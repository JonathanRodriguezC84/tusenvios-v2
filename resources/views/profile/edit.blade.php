<x-app-layout>
    <x-slot name="header">
        <x-page-header eyebrow="Cuenta" title="Mi perfil" description="Actualiza tu nombre, correo y contrasena." />
    </x-slot>

    <div class="p-4 h-full overflow-y-auto">
        <div class="space-y-4 max-w-xl mx-auto">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                @include('profile.partials.update-password-form')
            </div>
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
