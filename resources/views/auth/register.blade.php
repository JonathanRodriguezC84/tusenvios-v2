<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#022a8c">
    <title>Crear negocio — Tus Envios</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --brand-blue: #022a8c; --brand-orange: #ff7a00; --ink: #07111f; --muted: #5f6b7a; --line: #dbe2ea; --soft: #f4f7fb; }
        * { box-sizing: border-box; margin: 0; }
        body { color: var(--ink); background: var(--soft); font-family: 'Inter', sans-serif; overflow: hidden; }
        a { color: var(--brand-blue); text-decoration: none; font-weight: 700; }

        .reg-shell { display: grid; height: 100vh; grid-template-columns: 1fr; }
        @media (min-width: 1024px) { .reg-shell { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }

        /* ---- Columna izquierda: formulario ---- */
        .reg-left { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.25rem; background: var(--soft); overflow-y: auto; }
        .reg-left-inner { width: 100%; max-width: 440px; }
        .reg-logo { margin-bottom: 1rem; }
        .reg-logo a { display: inline-flex; align-items: center; gap: 0.75rem; }
        .reg-logo .logo-box { background: #fff; border-radius: 12px; padding: 0.5rem 0.65rem; box-shadow: 0 1px 2px rgba(0,0,0,.06); border: 1px solid var(--line); display: inline-block; }
        .reg-logo img { height: 2rem; width: auto; object-fit: contain; }

        .reg-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 1.25rem; box-shadow: 0 20px 50px -20px rgba(2,42,140,.18); }
        .reg-card-head { margin-bottom: 0.85rem; }
        .reg-card-head p { font-size: 0.65rem; font-weight: 800; color: var(--brand-blue); text-transform: uppercase; letter-spacing: .05em; }
        .reg-card-head h2 { font-size: 1.25rem; font-weight: 900; line-height: 1.15; margin-top: 0.15rem; color: #0b1526; }
        .reg-card-head .sub { font-size: 0.75rem; color: var(--muted); line-height: 1.45; margin-top: 0.25rem; }

        .reg-step { border: 1px solid var(--line); border-radius: 8px; padding: 0.55rem 0.7rem; margin-bottom: 0.5rem; border-left: 4px solid var(--brand-blue); }
        .reg-step.step-plan { border-left-color: var(--brand-blue); }
        .reg-step.step-biz  { border-left-color: #7c3aed; }
        .reg-step.step-auth { border-left-color: #059669; }

        .reg-step-head { display: flex; align-items: center; gap: 0.45rem; margin-bottom: 0.4rem; }
        .reg-step-num { width: 19px; height: 19px; border-radius: 5px; display: grid; place-items: center; font-size: 0.62rem; font-weight: 900; color: #fff; flex-shrink: 0; }
        .step-plan .reg-step-num { background: var(--brand-blue); }
        .step-biz  .reg-step-num { background: #7c3aed; }
        .step-auth .reg-step-num { background: #059669; }
        .reg-step-head h3 { font-size: 0.78rem; font-weight: 800; line-height: 1.15; }
        .reg-step-head p { font-size: 0.65rem; color: var(--muted); margin: 0; }

        .reg-input { width: 100%; border: 1px solid #d1d5db; border-radius: 7px; padding: 0.35rem 0.6rem; font-size: 0.78rem; min-height: 33px; transition: border-color .15s; background: #fff; font-family: inherit; }
        .reg-input:focus { outline: none; border-color: var(--brand-blue); box-shadow: 0 0 0 3px rgba(2,42,140,.08); }
        .reg-label { display: grid; gap: 0.15rem; font-size: 0.7rem; font-weight: 700; color: var(--ink); }

        .plan-card { display: flex; align-items: flex-start; gap: 0.45rem; cursor: pointer; border: 1.5px solid var(--line); border-radius: 7px; padding: 0.4rem 0.6rem; transition: all .15s; }
        .plan-card:hover { border-color: #93c5fd; }
        .plan-card.selected { border-color: var(--brand-blue); background: #eef4ff; }
        .plan-card input { width: 15px; height: 15px; min-width: 15px; border-radius: 999px; accent-color: var(--brand-blue); margin-top: 2px; }
        .plan-card .price { font-size: 0.8rem; font-weight: 800; white-space: nowrap; }
        .plan-card .feat { font-size: 0.63rem; color: var(--muted); line-height: 1.3; margin-top: 0.1rem; }
        .plan-name { font-weight: 800; font-size: 0.78rem; }

        .mode-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; margin-top: 0.4rem; }
        .mode-card { display: flex; align-items: center; gap: 0.35rem; cursor: pointer; border: 1.5px solid var(--line); border-radius: 7px; padding: 0.4rem 0.6rem; transition: all .15s; }
        .mode-card:hover { border-color: #93c5fd; }
        .mode-card.selected { border-color: var(--brand-blue); background: #eef4ff; }
        .mode-card input { width: 15px; height: 15px; min-width: 15px; border-radius: 999px; accent-color: var(--brand-blue); }
        .mode-card span { font-size: 0.72rem; font-weight: 700; }
        .mode-card small { font-size: 0.6rem; color: var(--muted); display: block; }

        .reg-submit { display: flex; align-items: center; justify-content: center; gap: 0.4rem; width: 100%; border: 0; border-radius: 8px; background: linear-gradient(135deg, #022a8c, #011f69); color: #fff; font-size: 0.8rem; font-weight: 800; padding: 0.6rem; cursor: pointer; transition: transform .15s, box-shadow .15s; box-shadow: 0 8px 18px rgba(2,42,140,.22); font-family: inherit; }
        .reg-submit:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(2,42,140,.28); }
        .reg-submit-sub { font-size: 0.65rem; color: var(--muted); text-align: center; margin-top: 0.4rem; }
        .reg-error { color: #dc2626; font-size: 0.65rem; margin-top: 0.1rem; }

        .reg-card-foot { margin-top: 0.85rem; padding-top: 0.7rem; border-top: 1px solid #eef1f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem; }
        .reg-card-foot a { font-size: 0.75rem; }

        /* ---- Columna derecha: imagen de fondo + frase ---- */
        .reg-right { position: relative; display: none; overflow: hidden; align-items: center; justify-content: center; }
        @media (min-width: 1024px) { .reg-right { display: flex; } }
        .reg-right-bg { position: absolute; inset: 0; background: linear-gradient(135deg, #011f69 0%, #022a8c 50%, #00103b 100%); }
        .reg-right-glow { position: absolute; border-radius: 9999px; opacity: .4; filter: blur(120px); pointer-events: none; }
        .reg-right-burger { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: .2; font-size: 22rem; line-height: 1; filter: saturate(1.1); pointer-events: none; }
        .reg-right-content { position: relative; z-index: 2; max-width: 32rem; padding: 1.5rem; text-align: center; color: #fff; }
        .reg-right-tag { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .18em; color: rgba(147,197,253,.9); }
        .reg-right-quote { font-size: 2.1rem; font-weight: 900; line-height: 1.28; margin: 1rem 0; text-shadow: 0 4px 30px rgba(0,0,0,.35); }
        .reg-right-divider { display: flex; align-items: center; justify-content: center; gap: 0.75rem; }
        .reg-right-divider .line { height: 1px; width: 2.5rem; background: rgba(147,197,253,.5); }
        .reg-right-divider span { font-size: 0.8rem; font-weight: 700; color: #bfdbfe; }
        .reg-right-foot { position: absolute; bottom: 1.5rem; left: 0; right: 0; text-align: center; font-size: 0.72rem; color: rgba(156,163,175,.9); font-weight: 600; z-index: 2; }
    </style>
    <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
</head>
<body>
<main class="reg-shell">

    <!-- Columna izquierda: formulario -->
    <section class="reg-left">
        <div class="reg-left-inner">
            <div class="reg-logo">
                <a href="/" class="inline-flex items-center gap-2.5">
                    <span class="logo-box"><img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios"></span>
                </a>
            </div>

            <div class="reg-card">
                <div class="reg-card-head">
                    <p>Registro</p>
                    <h2>Elige tu plan y empieza</h2>
                    <p class="sub">Crea tus primeras 10 guías gratis. Cuando quieras, activa tu plan mensual y dale identidad a tu marca.</p>
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

                <div class="reg-card-foot">
                    <a href="{{ url('/') }}">Volver al inicio</a>
                    <a href="{{ route('login') }}">¿Ya tienes cuenta? Ingresar</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Columna derecha: fondo + frase -->
    <aside class="reg-right">
        <div class="reg-right-bg"></div>
        <div class="reg-right-glow" style="top:-10rem;left:-10rem;width:32rem;height:32rem;background:radial-gradient(circle,rgba(255,122,0,.16),transparent 70%);"></div>
        <div class="reg-right-glow" style="bottom:-10rem;right:-8rem;width:26rem;height:26rem;background:radial-gradient(circle,rgba(31,115,255,.2),transparent 70%);"></div>
        <div class="reg-right-burger" aria-hidden="true">🍔</div>

        <div class="reg-right-content">
            <span class="reg-right-tag">Emprendimiento en acción</span>
            <p class="reg-right-quote">Lo que gastas en una comida rápida puede darle identidad a la marca de tu negocio.</p>
            <div class="reg-right-divider">
                <span class="line"></span>
                <span>Etiquetas propias desde el primer envío</span>
                <span class="line"></span>
            </div>
        </div>

        <div class="reg-right-foot">© 2026 Tus Envíos. Todos los derechos reservados.</div>
    </aside>

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