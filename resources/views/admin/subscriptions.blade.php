@extends('layouts.admin')

@section('title', 'Suscripciones')
@section('eyebrow', 'Pagos')
@section('page-title', 'Suscripciones y pagos')
@section('page-description', 'Planes, pagos, vencimientos y control de suscripciones.')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    <section class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="admin-card p-4"><p class="text-xs font-black uppercase text-gray-500">Vencidas</p><p class="mt-1 text-2xl font-semibold text-red-700">{{ $subscriptionMetrics['overdue'] ?? 0 }}</p></div>
        <div class="admin-card p-4"><p class="text-xs font-black uppercase text-gray-500">Por vencer</p><p class="mt-1 text-2xl font-semibold text-amber-700">{{ $subscriptionMetrics['due_soon'] ?? 0 }}</p></div>
        <div class="admin-card p-4"><p class="text-xs font-black uppercase text-gray-500">Activas</p><p class="mt-1 text-2xl font-semibold text-emerald-700">{{ $subscriptionMetrics['active'] ?? 0 }}</p></div>
        <div class="admin-card p-4"><p class="text-xs font-black uppercase text-gray-500">Total suscritos</p><p class="mt-1 text-2xl font-semibold text-gray-950">{{ collect($subscriptionMetrics)->only(['overdue','due_soon','active'])->sum() }}</p></div>
    </section>

    <section class="admin-card p-4 mb-4">
        <form method="GET" action="{{ route('admin.subscriptions') }}" class="flex flex-wrap gap-3 items-end">
            <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos los estados</option>
                @foreach (['active' => 'Al dia', 'past_due' => 'Pendiente', 'paused' => 'Pausada', 'cancelled' => 'Cancelada'] as $v => $l)
                    <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="plan_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <option value="">Todos los planes</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected(($filters['plan_id'] ?? '') == $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
            <button class="admin-btn">Filtrar</button>
            <a href="{{ route('admin.subscriptions') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpiar</a>
        </form>
    </section>

    <section class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Estado y vigencia</th>
                        <th>Pagos</th>
                        <th width="80">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $s)
                        @php
                            $isOverdue = $s->next_payment_at && $s->next_payment_at->isPast() && !$s->next_payment_at->isToday();
                            $isDueSoon = $s->next_payment_at && !$isOverdue && $s->next_payment_at->between(today(), today()->addDays(5));
                            $lp = $s->payments->first();
                            $tenant = $s->tenant;
                            $isLifetime = !$s->next_payment_at && $s->status === 'active';
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50/30' : '' }}">
                            {{-- CLIENTE --}}
                            <td>
                                <p class="font-semibold text-gray-950">{{ $tenant?->name ?: 'Cliente eliminado' }}</p>
                                <p class="text-xs text-gray-500">{{ $tenant?->email ?: 'Sin correo' }}</p>
                                <div class="mt-1 flex items-center gap-3 text-3xs text-gray-400">
                                    <span>{{ $tenant?->shipments_count ?? 0 }} guias</span>
                                    <span>{{ $tenant?->users_count ?? 0 }} usuarios</span>
                                    <span>{{ $tenant?->status ?? '?' }}</span>
                                </div>
                            </td>

                            {{-- PLAN --}}
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-950">{{ $s->plan?->name ?: 'Sin plan' }}</span>
                                    @if ($isLifetime)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-3xs font-semibold text-amber-700">Vitalicio</span>
                                    @endif
                                </div>
                                @if ($s->plan?->monthly_price > 0)
                                    <p class="text-xs text-gray-500">${{ number_format($s->plan->monthly_price, 0, ',', '.') }}/mes</p>
                                @else
                                    <p class="text-xs font-semibold text-amber-600">Gratuito</p>
                                @endif
                                @if ($s->isTrial())
                                    <p class="mt-0.5 text-3xs font-semibold text-blue-700">🧪 Prueba: {{ $s->trial_guide_used }}/{{ $s->trial_guide_limit }}</p>
                                @endif
                            </td>

                            {{-- ESTADO Y VIGENCIA --}}
                            <td>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if ($isOverdue)
                                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Vencida</span>
                                    @elseif ($isDueSoon)
                                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">Por vencer</span>
                                    @elseif ($s->status === 'active')
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Activa</span>
                                    @elseif ($s->status === 'paused')
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Pausada</span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">{{ $s->status }}</span>
                                    @endif
                                </div>
                                <div class="mt-1.5 grid grid-cols-2 gap-x-3 text-xs">
                                    <div>
                                        <p class="text-gray-400">Inicio</p>
                                        <p class="font-semibold text-gray-700">{{ $s->starts_at?->format('d/m/Y') ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400">Proximo pago</p>
                                        <p class="font-semibold {{ $isOverdue ? 'text-red-700' : 'text-gray-700' }}">{{ $s->next_payment_at?->format('d/m/Y') ?: '—' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- PAGOS --}}
                            <td>
                                @if ($lp)
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-950">${{ number_format($lp->amount, 0, ',', '.') }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-3xs font-semibold {{ $lp->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($lp->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">{{ strtoupper($lp->status) }}</span>
                                    </div>
                                    <p class="text-3xs text-gray-400 mt-0.5">{{ $lp->provider }} · {{ $lp->reference }}</p>
                                    @if ($lp->payment_url)
                                        <a href="{{ $lp->payment_url }}" target="_blank" class="text-3xs font-semibold text-blue-700 hover:underline">Abrir link de pago</a>
                                    @endif
                                @else
                                    <p class="text-xs text-gray-400">Sin pagos registrados</p>
                                @endif

                                <form method="POST" action="{{ route('admin.subscriptions.manual-payment', $s) }}" class="mt-1.5 flex items-center gap-1" onsubmit="return confirm('Registrar pago manual?')">
                                    @csrf
                                    <input name="amount" type="number" min="0" value="{{ $s->plan?->monthly_price ?? 0 }}" class="w-20 rounded border-gray-300 text-xs shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="$">
                                    <input name="notes" placeholder="Nota" class="w-24 rounded border-gray-300 text-xs shadow-sm focus:border-blue-700 focus:ring-blue-700">
                                    <button class="rounded bg-blue-700 px-2 py-1 text-3xs font-semibold text-white hover:bg-blue-800">Pagar</button>
                                </form>
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="flex gap-1">
                                    <form method="POST" action="{{ route('admin.payment-links.create', $s) }}">
                                        @csrf
                                        <button title="Generar link de pago" class="rounded border border-gray-300 bg-white p-1.5 text-gray-500 hover:bg-gray-50">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </button>
                                    </form>
                                    <button onclick="openEdit({{ $s->id }})" title="Editar suscripcion" class="rounded border border-gray-300 bg-white p-1.5 text-gray-500 hover:bg-gray-50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-gray-500 py-10">No hay suscripciones para los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4">{{ $subscriptions->links() }}</div>
    </section>

    {{-- Modal editar --}}
    <div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/30 flex items-center justify-center p-4" onclick="if(event.target===this)closeEdit()">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6" onclick="event.stopPropagation()">
            <h3 class="text-base font-semibold text-gray-900">Editar suscripcion</h3>
            <form method="POST" id="edit-form" class="mt-4 grid gap-3" onsubmit="return confirm('Actualizar suscripcion?')">
                @csrf
                @method('PATCH')
                <select name="subscription_plan_id" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" onchange="const cb=this.form.querySelector('[name=is_lifetime]');const dt=this.form.querySelector('[name=next_payment_at]');if(cb&&dt){const isFundador=this.options[this.selectedIndex].text.includes('Fundador');cb.checked=isFundador;dt.disabled=isFundador;}">
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                        @foreach (['active' => 'Activa', 'past_due' => 'Pendiente', 'paused' => 'Pausada', 'cancelled' => 'Cancelada'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <input name="is_lifetime" type="checkbox" value="1" class="rounded border-gray-300 text-blue-700" onchange="this.form.querySelector('[name=next_payment_at]').disabled=this.checked"> Vitalicio
                    </label>
                </div>
                <input name="next_payment_at" type="date" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" onclick="closeEdit()" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button class="admin-btn text-sm">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const subs = @json($subscriptions->keyBy('id'));

        function openEdit(id) {
            const s = subs[id];
            if (!s) return;
            const f = document.getElementById('edit-form');
            f.action = '/admin/subscriptions/' + id;
            f.querySelector('[name=subscription_plan_id]').value = s.subscription_plan_id;
            f.querySelector('[name=status]').value = s.status;
            const lt = f.querySelector('[name=is_lifetime]');
            const dt = f.querySelector('[name=next_payment_at]');
            lt.checked = !s.next_payment_at;
            dt.disabled = !s.next_payment_at;
            dt.value = s.next_payment_at ? s.next_payment_at.split('T')[0] : '';
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeEdit() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
    </script>
@endsection
