@extends('layouts.admin')

@section('title', $tenant->name)
@section('eyebrow', 'Cliente')
@section('page-title', $tenant->name)
@section('page-description', $tenant->email ?? 'Sin correo')

@section('page-actions')
    <a href="{{ route('admin.clients') }}" class="admin-outline-link">Volver a clientes</a>
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Info del negocio --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Negocio</h3>
            <dl class="mt-4 grid gap-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Nombre</dt>
                    <dd class="mt-0.5 font-semibold text-gray-950">{{ $tenant->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Correo</dt>
                    <dd class="mt-0.5 font-semibold text-gray-950">{{ $tenant->email ?? 'No registrado' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Telefono</dt>
                    <dd class="mt-0.5 font-semibold text-gray-950">{{ $tenant->phone ?? 'No registrado' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Subdominio</dt>
                    <dd class="mt-0.5 font-semibold text-gray-950">{{ $tenant->subdomain ?? 'No asignado' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Prefijo guias</dt>
                    <dd class="mt-0.5 font-semibold text-gray-950">{{ $tenant->guide_prefix ?? 'No asignado' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Estado</dt>
                    <dd class="mt-0.5">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $tenant->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($tenant->status === 'paused' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ $tenant->status }}</span>
                    </dd>
                </div>
            </dl>
        </section>

        {{-- Suscripcion --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Suscripcion actual</h3>
            @if ($sub = $tenant->currentSubscription)
                <dl class="mt-4 grid gap-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Plan</dt>
                        <dd class="mt-0.5 font-semibold text-gray-950">{{ $sub->plan?->name ?? 'Sin plan' }} — ${{ number_format($sub->plan?->monthly_price ?? 0, 0, ',', '.') }}/mes</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Estado</dt>
                        <dd class="mt-0.5 font-semibold text-gray-950">{{ $sub->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Inicio</dt>
                        <dd class="mt-0.5 font-semibold text-gray-950">{{ $sub->starts_at?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Proximo pago</dt>
                        <dd class="mt-0.5 font-semibold text-gray-950">{{ $sub->next_payment_at?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    @if ($sub->isTrial())
                        <div>
                            <dt class="text-xs font-semibold uppercase text-blue-700">Guias de prueba</dt>
                            <dd class="mt-0.5 font-semibold text-blue-800">{{ $sub->trial_guide_used }} / {{ $sub->trial_guide_limit }}</dd>
                        </div>
                    @endif
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500">Sin suscripcion activa.</p>
            @endif
        </section>

        {{-- Metricas --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Metricas</h3>
            <div class="mt-4 grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">Guias</p>
                    <p class="mt-1 text-2xl font-bold text-gray-950">{{ $tenant->shipments_count }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">Usuarios</p>
                    <p class="mt-1 text-2xl font-bold text-gray-950">{{ $tenant->users_count }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">Creado</p>
                    <p class="mt-1 text-sm font-bold text-gray-950">{{ $tenant->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </section>

        {{-- API Token --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">API Token</h3>
            <p class="text-sm text-gray-500 mt-1">Token para conectar sistemas externos via API.</p>
            <div class="mt-3 flex items-center gap-3">
                @if ($tenant->api_token)
                    <code class="rounded bg-gray-100 px-3 py-1.5 text-sm font-mono text-gray-700">{{ substr($tenant->api_token, 0, 8) }}••••••••</code>
                @else
                    <span class="text-sm text-gray-400">Sin token generado</span>
                @endif
                <form method="POST" action="{{ route('admin.clients.token', $tenant) }}" class="inline">
                    @csrf
                    <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $tenant->api_token ? 'Regenerar' : 'Generar token' }}
                    </button>
                </form>
            </div>
            @if (session('new_token'))
                <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3">
                    <p class="text-xs font-semibold text-amber-800 mb-1">Token generado. Copialo ahora, no se mostrara de nuevo:</p>
                    <code class="rounded bg-white px-3 py-1.5 text-sm font-mono text-gray-950 break-all">{{ session('new_token') }}</code>
                </div>
            @endif
        </section>

        {{-- Webhook --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Webhook</h3>
            <p class="text-sm text-gray-500 mt-1">TusEnvios notificara a esta URL cuando cambie el estado de una guia.</p>
            <form method="POST" action="{{ route('admin.clients.webhook', $tenant) }}" class="mt-3 grid gap-3">
                @csrf
                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    URL del webhook
                    <input name="webhook_url" value="{{ $tenant->webhook_url }}" type="url" placeholder="https://tuecommerce.com/webhook/tusenvios" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700">
                </label>
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">Notificar en estos eventos:</p>
                    <div class="flex flex-wrap gap-4">
                        @php $currentEvents = $tenant->webhook_events ?? ['delivered']; @endphp
                        @foreach (['delivered' => 'Entregada', 'failed_delivery' => 'Novedad', 'cancelled' => 'Cancelada', 'on_route' => 'En camino', 'returned' => 'Devuelta'] as $k => $l)
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-600">
                                <input name="webhook_events[]" type="checkbox" value="{{ $k }}" class="rounded border-gray-300 text-blue-700" @checked(in_array($k, $currentEvents))>
                                {{ $l }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="admin-btn text-xs">Guardar webhook</button>
                </div>
            </form>
        </section>

        {{-- Billetera --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Billetera</h3>
            <div class="mt-3 flex items-center gap-4">
                <div>
                    <p class="text-xs text-gray-500">Saldo actual</p>
                    <p class="text-2xl font-bold {{ $tenant->balance > 0 ? 'text-emerald-700' : 'text-gray-950' }}">${{ number_format($tenant->balance, 0, ',', '.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.clients.wallet', $tenant) }}" class="flex items-end gap-2">
                    @csrf
                    <div class="grid gap-1">
                        <label class="text-xs font-semibold text-gray-600">Monto</label>
                        <input name="amount" type="number" required min="100" step="100" class="w-28 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="$">
                    </div>
                    <div class="grid gap-1">
                        <label class="text-xs font-semibold text-gray-600">Nota</label>
                        <input name="notes" class="w-32 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" placeholder="Recarga">
                    </div>
                    <button name="type" value="deposit" class="admin-btn text-xs">Recargar</button>
                </form>
            </div>
            @php($txs = $tenant->walletTransactions()->latest()->take(5)->get())
            @if ($txs->count())
                <div class="mt-4 border-t border-gray-100 pt-3 grid gap-1 text-xs">
                    @foreach ($txs as $tx)
                        <div class="flex justify-between text-gray-600">
                            <span>{{ $tx->created_at->format('d/m/Y H:i') }} · {{ $tx->type }} · {{ $tx->notes ?: '—' }}</span>
                            <span class="font-semibold {{ $tx->amount > 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $tx->amount > 0 ? '+' : '' }}${{ number_format($tx->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Ultimos pagos --}}
        <section class="admin-card p-5">
            <h3 class="text-sm font-black uppercase text-gray-500">Historial de pagos</h3>
            @php($payments = $tenant->subscriptionPayments()->latest()->take(5)->get())
            @if ($payments->count())
                <div class="mt-4 divide-y divide-gray-100">
                    @foreach ($payments as $payment)
                        <div class="py-2 flex items-center justify-between text-sm">
                            <div>
                                <p class="font-semibold text-gray-950">${{ number_format($payment->amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->provider }} — {{ $payment->reference }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $payment->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($payment->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ $payment->status }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">Sin pagos registrados.</p>
            @endif
        </section>
    </div>
@endsection
