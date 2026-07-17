<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#022a8c">
    <title>Crear negocio — Tus Envios</title>
    @vite(['resources/css/app.css'])
    <style>
        :root { --brand-blue: #022a8c; --brand-orange: #ff7a00; --ink: #07111f; --muted: #5f6b7a; --line: #dbe2ea; --soft: #f4f7fb; }
        * { box-sizing: border-box; margin: 0; }
        body { color: var(--ink); background: var(--soft); overflow: hidden; }
        a { color: var(--brand-blue); text-decoration: none; font-weight: 700; }

        .reg-shell { display: grid; height: 100vh; }
        @media (min-width: 900px) { .reg-shell { grid-template-columns: 380px 1fr; } }

        .reg-hero {
            background: radial-gradient(circle at 12% 8%, rgba(255,122,0,.13), transparent 26%), linear-gradient(180deg, #fff 0%, #f6f9ff 100%);
            border-right: 1px solid var(--line); padding: 1.75rem 1.5rem; display: flex; flex-direction: column; overflow: hidden;
        }
        .reg-hero img { width: 148px; height: auto; }
        .reg-hero h1 { font-size: 1.7rem; font-weight: 950; line-height: 1.08; margin: 1.25rem 0 0.5rem; }
        .reg-hero > p { color: #405064; font-size: 0.85rem; line-height: 1.6; margin: 0 0 auto; }
        @media (max-width: 900px) { .reg-shell { grid-template-columns: 1fr; grid-template-rows: auto 1fr; overflow: auto; } .reg-hero { border-right: 0; border-bottom: 1px solid var(--line); padding: 1.25rem 1.25rem 0.75rem; } .reg-hero h1 { font-size: 1.25rem; margin: 0.75rem 0 0.25rem; } .reg-hero > p { font-size: 0.8rem; } }

        .reg-form { padding: 1.5rem; overflow-y: auto; display: flex; align-items: center; }
        @media (max-width: 900px) { .reg-form { padding: 1rem; align-items: start; } }

        .reg-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .reg-top h2 { font-size: 1.2rem; font-weight: 800; }

        .reg-step {
            background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 0.65rem;
            border-left: 4px solid var(--brand-blue);
        }
        .reg-step.step-plan { border-left-color: var(--brand-blue); }
        .reg-step.step-biz  { border-left-color: #7c3aed; }
        .reg-step.step-auth { border-left-color: #059669; }

        .reg-step-head { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.65rem; }
        .reg-step-num {
            width: 24px; height: 24px; border-radius: 6px; display: grid; place-items: center;
            font-size: 0.72rem; font-weight: 900; color: #fff; flex-shrink: 0;
        }
        .step-plan .reg-step-num { background: var(--brand-blue); }
        .step-biz  .reg-step-num { background: #7c3aed; }
        .step-auth .reg-step-num { background: #059669; }
        .reg-step-head h3 { font-size: 0.85rem; font-weight: 800; line-height: 1.2; }
        .reg-step-head p { font-size: 0.7rem; color: var(--muted); margin: 0; }

        .reg-input {
            width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 0.45rem 0.6rem;
            font-size: 0.82rem; min-height: 36px; transition: border-color .15s; background: #fff;
        }
        .reg-input:focus { outline: none; border-color: var(--brand-blue); box-shadow: 0 0 0 2px rgba(2,42,140,.08); }
        .reg-label { display: grid; gap: 0.2rem; font-size: 0.75rem; font-weight: 700; color: var(--ink); }

        .plan-card {
            display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer;
            border: 1.5px solid var(--line); border-radius: 6px; padding: 0.55rem 0.7rem; transition: all .15s;
        }
        .plan-card:hover { border-color: #93c5fd; }
        .plan-card.selected { border-color: var(--brand-blue); background: #eef4ff; }
        .plan-card input { width: 16px; height: 16px; min-width: 16px; border-radius: 999px; accent-color: var(--brand-blue); margin-top: 2px; }
        .plan-card .price { font-size: 0.9rem; font-weight: 800; white-space: nowrap; }
        .plan-card .feat { font-size: 0.68rem; color: var(--muted); line-height: 1.4; margin-top: 0.2rem; }
        .plan-name { font-weight: 800; font-size: 0.85rem; }

        .mode-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.5rem; }
        .mode-card {
            display: flex; align-items: center; gap: 0.4rem; cursor: pointer;
            border: 1.5px solid var(--line); border-radius: 6px; padding: 0.5rem 0.7rem; transition: all .15s;
        }
        .mode-card:hover { border-color: #93c5fd; }
        .mode-card.selected { border-color: var(--brand-blue); background: #eef4ff; }
        .mode-card input { width: 16px; height: 16px; min-width: 16px; border-radius: 999px; accent-color: var(--brand-blue); }
        .mode-card span { font-size: 0.78rem; font-weight: 700; }
        .mode-card small { font-size: 0.65rem; color: var(--muted); display: block; }

        .reg-submit {
            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
            width: 100%; border: 0; border-radius: 7px; background: var(--brand-blue); color: #fff;
            font-size: 0.88rem; font-weight: 800; padding: 0.7rem; cursor: pointer; transition: background .15s;
            box-shadow: 0 8px 18px rgba(2,42,140,.15);
        }
        .reg-submit:hover { background: #011f68; }
        .reg-submit-sub { font-size: 0.7rem; color: var(--muted); text-align: center; margin-top: 0.4rem; }
        .reg-error { color: #dc2626; font-size: 0.7rem; margin-top: 0.15rem; }

        .reg-feat-item { border: 1px solid var(--line); border-radius: 6px; padding: 0.55rem 0.7rem; background: rgba(255,255,255,.8); font-size: 0.78rem; }
        .reg-feat-item strong { display: block; margin-bottom: 0.1rem; }
        .reg-feat-item span { color: var(--muted); font-size: 0.72rem; line-height: 1.4; }
        .reg-features { display: grid; gap: 0.4rem; }
    </style>
    <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
</head>
<body>
<main class="reg-shell">

    <aside class="reg-hero">
        <a href="/"><img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios"></a>
        <h1>Empieza a imprimir etiquetas con tu marca.</h1>
        <p>Crea guias ilimitadas, guarda productos frecuentes y organiza tus envios desde el celular.</p>
        <div class="reg-features">
            <div class="reg-feat-item"><strong>Etiquetas ilimitadas</strong><span>Imprime todas las que necesites.</span></div>
            <div class="reg-feat-item"><strong>Logo, redes y mensaje</strong><span>Tu marca visible en cada paquete.</span></div>
            <div class="reg-feat-item"><strong>Sin inventario obligatorio</strong><span>Productos frecuentes, simple.</span></div>
        </div>
    </aside>

    <section class="reg-form">
        <div style="max-width:480px;width:100%;margin:auto;">
            <div class="reg-top">
                <div>
                    <p style="font-size:0.7rem;font-weight:800;color:var(--brand-blue);text-transform:uppercase;">Registro</p>
                    <h2>Elige tu plan y empieza</h2>
                </div>
                <a href="{{ route('login') }}" style="font-size:0.8rem;">Ingresar</a>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="reg-step step-plan">
                    <div class="reg-step-head">
                        <span class="reg-step-num">1</span>
                        <div><h3>Plan comercial</h3><p>10 guias gratis o pago inicial.</p></div>
                    </div>
                    <div class="grid gap-1.5">
                        @foreach ($plans as $plan)
                            <label class="plan-card {{ old('subscription_plan_id', $plans->first()?->id) == $plan->id ? 'selected' : '' }}">
                                <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" @checked(old('subscription_plan_id', $plans->first()?->id) == $plan->id)>
                                <div class="flex-1 min-w-0">
                                    <div style="display:flex;justify-content:space-between;align-items:baseline;">
                                        <span class="plan-name">{{ $plan->name }}</span>
                                        <span class="price">${{ number_format($plan->monthly_price, 0, ',', '.') }}/mes</span>
                                    </div>
                                    <div class="feat">{{ collect($plan->features ?? [])->take(3)->join(' · ') }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('subscription_plan_id')<p class="reg-error">{{ $message }}</p>@enderror

                    <div class="mode-row">
                        <label class="mode-card {{ old('start_mode', 'trial_guides') === 'trial_guides' ? 'selected' : '' }}">
                            <input type="radio" name="start_mode" value="trial_guides" @checked(old('start_mode', 'trial_guides') === 'trial_guides')>
                            <div><span>10 guias gratis</span><small>Empieza sin pagar</small></div>
                        </label>
                        <label class="mode-card {{ old('start_mode') === 'pay_now' ? 'selected' : '' }}">
                            <input type="radio" name="start_mode" value="pay_now" @checked(old('start_mode') === 'pay_now')>
                            <div><span>Pagar ahora</span><small>Plan mensual activo</small></div>
                        </label>
                    </div>
                    @error('start_mode')<p class="reg-error">{{ $message }}</p>@enderror
                </div>

                <div class="reg-step step-biz">
                    <div class="reg-step-head">
                        <span class="reg-step-num">2</span>
                        <div><h3>Negocio</h3><p>Datos de tu marca.</p></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                        <label class="reg-label">
                            Nombre del emprendimiento
                            <input name="business_name" value="{{ old('business_name') }}" required autofocus placeholder="Ej. Dulce Aroma" class="reg-input">
                            @error('business_name')<p class="reg-error">{{ $message }}</p>@enderror
                        </label>
                        <label class="reg-label">
                            WhatsApp
                            <input name="business_phone" value="{{ old('business_phone') }}" required inputmode="tel" placeholder="3001234567" class="reg-input">
                            @error('business_phone')<p class="reg-error">{{ $message }}</p>@enderror
                        </label>
                    </div>
                </div>

                <div class="reg-step step-auth">
                    <div class="reg-step-head">
                        <span class="reg-step-num">3</span>
                        <div><h3>Acceso</h3><p>Usuario administrador.</p></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                        <label class="reg-label">
                            Tu nombre
                            <input name="name" value="{{ old('name') }}" required placeholder="Admin" class="reg-input">
                            @error('name')<p class="reg-error">{{ $message }}</p>@enderror
                        </label>
                        <label class="reg-label">
                            Correo
                            <input name="email" type="email" value="{{ old('email') }}" required placeholder="correo@negocio.com" class="reg-input">
                            @error('email')<p class="reg-error">{{ $message }}</p>@enderror
                        </label>
                        <label class="reg-label">
                            Contrasena
                            <input name="password" type="password" required placeholder="Min. 8 caracteres" class="reg-input">
                        </label>
                        <label class="reg-label">
                            Confirmar
                            <input name="password_confirmation" type="password" required placeholder="Repetir" class="reg-input">
                        </label>
                    </div>
                    @error('password')<p class="reg-error">{{ $message }}</p>@enderror
                </div>

                <button class="reg-submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Crear mi negocio
                </button>
                <p class="reg-submit-sub">Guias ilimitadas, etiquetas con marca y acceso desde celular.</p>
            </form>
        </div>
    </section>

</main>

<script>
    document.querySelectorAll('.plan-card input, .mode-card input').forEach(r => {
        r.addEventListener('change', function() {
            document.querySelectorAll(`input[name="${this.name}"]`).forEach(s => {
                s.closest('.plan-card')?.classList.toggle('selected', s.checked);
                s.closest('.mode-card')?.classList.toggle('selected', s.checked);
            });
        });
    });
</script>
</body>
</html>
