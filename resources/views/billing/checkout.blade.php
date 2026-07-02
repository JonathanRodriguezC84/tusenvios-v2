<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Pago inicial - Tus Envios</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
            <style id="te-force-inter-font">
            :root {
                --te-font-family: "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            html,
            body,
            body *,
            button,
            input,
            select,
            textarea {
                font-family: var(--te-font-family) !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <main class="min-h-screen bg-gray-100 px-4 py-10 text-gray-950">
            <section class="mx-auto max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios" class="h-auto max-h-12 w-auto max-w-[170px] object-contain">

                @if (session('status'))
                    <div class="mt-5 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-900">{{ session('status') }}</div>
                @endif

                <p class="mt-6 text-xs font-black uppercase text-blue-700">Pago inicial</p>
                <h1 class="mt-2 text-2xl font-black">Activa tu mensualidad</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">Tu cuenta ya fue creada. Completa el pago o continua al panel si estas en prueba.</p>

                <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm"><strong>Negocio:</strong> {{ $tenant->name }}</p>
                    <p class="mt-2 text-sm"><strong>Plan:</strong> {{ $subscription->plan?->name }}</p>
                    <p class="mt-2 text-sm"><strong>Valor:</strong> ${{ number_format($subscription->plan?->monthly_price ?? 0, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm"><strong>Proximo pago:</strong> {{ $subscription->next_payment_at?->format('d/m/Y') ?: 'Sin fecha' }}</p>
                </div>

                @if ($lastPayment?->payment_url && in_array($lastPayment->status, ['pending', 'processing'], true))
                    <a href="{{ $lastPayment->payment_url }}" target="_blank" class="mt-5 inline-flex w-full items-center justify-center rounded-md bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-800">
                        Ir a pagar con Bold
                    </a>
                @else
                    <form method="POST" action="{{ route('billing.payment-link') }}" class="mt-5">
                        @csrf
                        <button class="w-full rounded-md bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-800">
                            Generar link de pago
                        </button>
                    </form>
                @endif

                <a href="{{ route('dashboard') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
                    Entrar al panel
                </a>
            </section>
        </main>
    </body>
</html>