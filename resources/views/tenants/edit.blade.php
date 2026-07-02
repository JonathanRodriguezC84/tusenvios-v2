<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700">Clientes</p>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Editar cliente</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        Revisa los campos marcados antes de guardar.
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Nombre comercial
                        <input name="name" value="{{ old('name', $tenant->name) }}" required class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Razon social
                        <input name="legal_name" value="{{ old('legal_name', $tenant->legal_name) }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        NIT / documento
                        <input name="document_number" value="{{ old('document_number', $tenant->document_number) }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Subdominio
                        <input name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Prefijo guia
                        <input name="guide_prefix" value="{{ old('guide_prefix', $tenant->guide_prefix) }}" maxlength="3" class="uppercase rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Correo
                        <input name="email" type="email" value="{{ old('email', $tenant->email) }}" data-uppercase="false" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Telefono
                        <input name="phone" value="{{ old('phone', $tenant->phone) }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Estado
                        <select name="status" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                            <option value="active" @selected(old('status', $tenant->status) === 'active')>Activo</option>
                            <option value="paused" @selected(old('status', $tenant->status) === 'paused')>Pausado</option>
                        </select>
                    </label>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-5">
                    <h3 class="text-base font-semibold text-gray-950">Suscripcion</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Plan
                            <select name="subscription_plan_id" required class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('subscription_plan_id', $tenant->currentSubscription?->subscription_plan_id) == $plan->id)>
                                        {{ $plan->name }} - ${{ number_format($plan->monthly_price, 0, ',', '.') }}/mes
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Estado suscripcion
                            <select name="subscription_status" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                <option value="active" @selected(old('subscription_status', $tenant->currentSubscription?->status ?? 'active') === 'active')>Activa</option>
                                <option value="past_due" @selected(old('subscription_status', $tenant->currentSubscription?->status) === 'past_due')>Pago pendiente</option>
                                <option value="paused" @selected(old('subscription_status', $tenant->currentSubscription?->status) === 'paused')>Pausada</option>
                                <option value="cancelled" @selected(old('subscription_status', $tenant->currentSubscription?->status) === 'cancelled')>Cancelada</option>
                            </select>
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Inicio
                            <input name="starts_at" type="date" value="{{ old('starts_at', $tenant->currentSubscription?->starts_at?->toDateString() ?? now()->toDateString()) }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Proximo pago
                            <input name="next_payment_at" type="date" value="{{ old('next_payment_at', $tenant->currentSubscription?->next_payment_at?->toDateString()) }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700">
                            Fin del acceso
                            <input name="ends_at" type="date" value="{{ old('ends_at', $tenant->currentSubscription?->ends_at?->toDateString()) }}" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        </label>

                        <label class="grid gap-1 text-sm font-semibold text-gray-700 sm:col-span-2">
                            Notas internas
                            <textarea name="subscription_notes" rows="3" class="rounded-md border-gray-300 text-base shadow-sm focus:border-blue-700 focus:ring-blue-700">{{ old('subscription_notes', $tenant->currentSubscription?->notes) }}</textarea>
                        </label>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button class="rounded-md bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
