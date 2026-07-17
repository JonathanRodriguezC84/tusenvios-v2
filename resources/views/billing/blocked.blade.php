<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Activa tu plan - Tus Envios</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $tenant = Auth::user()->tenant;
            $subscription = $tenant?->currentSubscription;
            $plan = $subscription?->plan;
            $lastPayment = $tenant?->subscriptionPayments()?->latest()->first();
        @endphp

        <main class="min-h-screen bg-gray-100 px-4 py-10 text-gray-950">
            <section class="mx-auto max-w-xl rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm">
                <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios" class="mx-auto h-auto max-h-12 w-auto max-w-[170px] object-contain">
                <p class="mt-6 text-xs font-black uppercase text-blue-700">Activa tu plan</p>
                <h1 class="mt-2 text-2xl font-black">Ya usaste tus 10 guias gratis</h1>
                <p class="mt-3 text-sm leading-6 text-gray-600">
                    Para seguir creando e imprimiendo guias, activa tu plan mensual.
                </p>

                <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 text-left">
                    <p class="text-sm"><strong>Negocio:</strong> {{ $tenant?->name ?: 'Tu negocio' }}</p>
                    <p class="mt-2 text-sm"><strong>Plan:</strong> {{ $plan?->name ?: 'Sin plan' }}</p>
                    <p class="mt-2 text-sm"><strong>Valor:</strong> ${{ number_format($plan?->monthly_price ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm"><strong>Guias gratis usadas:</strong> {{ $subscription?->trial_guide_used ?? 0 }} / {{ $subscription?->trial_guide_limit ?? 10 }}</p>
                </div>

                @if ($lastPayment?->payment_url && in_array($lastPayment->status, ['pending', 'processing'], true))
                    <a href="{{ $lastPayment->payment_url }}" target="_blank" class="mt-5 inline-flex w-full items-center justify-center rounded-md bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-800">
                        Pagar mensualidad
                    </a>
                @else
                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                        Aun no hay un link de pago activo. Solicitalo al administrador de Tus Envios.
                    </div>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button class="w-full rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        Cerrar sesion
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>