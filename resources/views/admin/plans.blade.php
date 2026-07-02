@extends('layouts.admin')

@section('title', 'Planes')
@section('eyebrow', 'Producto')
@section('page-title', 'Planes comerciales')
@section('page-description', 'Plan unico disponible para vender el servicio.')

@section('page-actions')
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    <section class="grid gap-4 md:grid-cols-3">
        @foreach ($plans as $plan)
            <article class="admin-card p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-800">{{ $plan->code }}</p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-950">{{ $plan->name }}</h3>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $plan->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ $plan->is_active ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                </div>
                <p class="mt-4 text-3xl font-semibold text-gray-950">${{ number_format($plan->monthly_price, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500">mensual</p>
                <div class="mt-5 grid gap-2">
                    @forelse (($plan->features ?? []) as $feature)
                        <p class="rounded-md bg-gray-50 px-3 py-2 text-sm font-bold text-gray-700">{{ $feature }}</p>
                    @empty
                        <p class="text-sm text-gray-400">Sin caracteristicas</p>
                    @endforelse
                </div>
                <div class="mt-5 border-t border-gray-200 pt-4 flex items-center justify-between">
                    <p class="text-sm text-gray-600"><strong>{{ $plan->subscriptions_count }}</strong> suscripciones historicas</p>
                    <div class="flex gap-2">
                        <button type="button" onclick="openPlanModal({{ $plan->id }})" class="rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-bold text-gray-700 hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    {{-- Modal crear/editar --}}
    <div id="plan-modal" class="fixed inset-0 z-50 hidden bg-black/30 flex items-center justify-center p-4" onclick="if(event.target===this)closePlanModal()">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6" onclick="event.stopPropagation()">
            <h4 class="text-base font-bold text-gray-900" id="plan-modal-title">Nuevo plan</h4>
            <form method="POST" id="plan-form" class="mt-4 grid gap-4">
                @csrf
                <div id="plan-method"></div>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Codigo
                    <input name="code" id="plan-code" required maxlength="30" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="emprende">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Nombre
                    <input name="name" id="plan-name" required maxlength="120" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Emprende">
                </label>
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Precio mensual ($)
                    <input name="monthly_price" id="plan-price" required type="number" min="0" step="100" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="19900">
                </label>
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-1">Caracteristicas</p>
                    <div id="plan-features-container" class="grid gap-2">
                        <div class="flex gap-2">
                            <input name="features[]" class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Ej. Guias ilimitadas">
                            <button type="button" onclick="addFeatureField()" class="shrink-0 rounded-md border border-gray-300 px-2 py-1 text-sm font-bold text-gray-600 hover:bg-gray-50">+</button>
                        </div>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input name="is_active" id="plan-active" type="checkbox" value="1" checked class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-700">
                    Plan activo
                </label>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" onclick="closePlanModal()" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const plansData = @json($plans->keyBy('id'));

        function openPlanModal(planId) {
            const modal = document.getElementById('plan-modal');
            const form = document.getElementById('plan-form');
            const title = document.getElementById('plan-modal-title');
            const methodDiv = document.getElementById('plan-method');

            if (planId) {
                const plan = plansData[planId];
                if (!plan) return;
                title.textContent = 'Editar plan';
                form.action = '/admin/plans/' + planId;
                methodDiv.innerHTML = '@method("PATCH")';
                document.getElementById('plan-code').value = plan.code;
                document.getElementById('plan-name').value = plan.name;
                document.getElementById('plan-price').value = plan.monthly_price;
                document.getElementById('plan-active').checked = plan.is_active;

                const container = document.getElementById('plan-features-container');
                container.innerHTML = '';
                if (plan.features && plan.features.length) {
                    plan.features.forEach(f => {
                        container.appendChild(featureRow(f));
                    });
                }
                container.appendChild(featureRow(''));
            } else {
                title.textContent = 'Nuevo plan';
                form.action = '/admin/plans';
                methodDiv.innerHTML = '';
                document.getElementById('plan-code').value = '';
                document.getElementById('plan-name').value = '';
                document.getElementById('plan-price').value = '';
                document.getElementById('plan-active').checked = true;

                const container = document.getElementById('plan-features-container');
                container.innerHTML = '';
                container.appendChild(featureRow(''));
            }

            modal.classList.remove('hidden');
        }

        function closePlanModal() {
            document.getElementById('plan-modal').classList.add('hidden');
        }

        function addFeatureField() {
            document.getElementById('plan-features-container').appendChild(featureRow(''));
        }

        function featureRow(value) {
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `<input name="features[]" value="${value}" class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Ej. Guias ilimitadas"><button type="button" onclick="this.parentElement.remove()" class="shrink-0 rounded-md border border-gray-300 px-2 py-1 text-sm font-bold text-red-600 hover:bg-red-50">-</button>`;
            return div;
        }
    </script>
@endsection
