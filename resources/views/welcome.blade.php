<!doctype html>
<!-- 
  design-taste-frontend configuration:
  DESIGN_VARIANCE: 9
  MOTION_INTENSITY: 2
  VISUAL_DENSITY: 5
-->
<html lang="es" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#022a8c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Tus Envios">
        <title>Tus Envios | Gestión de despachos y etiquetas de envío profesionales</title>
        @vite(['resources/css/app.css'])
        
        <style>
            :root {
                --brand-blue: #022a8c;
                --brand-blue-dark: #011f69;
                --brand-orange: #ff7a00;
                --brand-orange-hover: #e06c00;
                --emerald: #10b981;
                --slate-950: #0f172a;
                --slate-900: #1e293b;
                --slate-600: #64748b;
                --slate-100: #f1f5f9;
                --white: #ffffff;
                --bg-soft: #f8fafc;
            }
            body {
                background-color: var(--white);
                color: var(--slate-950);
                margin: 0;
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                -webkit-font-smoothing: antialiased;
            }
            .container {
                max-width: 1100px;
                width: 100%;
                margin: 0 auto;
                padding: 0 20px;
                box-sizing: border-box;
            }

            /* Header */
            .header {
                background: var(--white);
                border-bottom: 1px solid var(--slate-100);
                position: sticky;
                top: 0;
                z-index: 100;
            }
            .header-inner {
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .logo img {
                height: 54px;
                width: auto;
                display: block;
            }
            .nav-menu {
                display: flex;
                gap: 32px;
            }
            .nav-menu a {
                color: var(--slate-900);
                font-size: 14px;
                font-weight: 500;
                text-decoration: none;
                transition: color 0.15s;
            }
            .nav-menu a:hover {
                color: var(--brand-blue);
            }
            .auth-buttons {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 13.5px;
                font-weight: 700;
                padding: 10px 20px;
                border-radius: 6px;
                text-decoration: none;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                border: none;
                cursor: pointer;
                box-sizing: border-box;
            }
            .btn-primary {
                background: var(--brand-blue);
                color: var(--white);
            }
            .btn-primary:hover {
                background: var(--brand-blue-dark);
            }
            .btn:active {
                transform: scale(0.97);
            }
            .btn-orange {
                background: var(--brand-orange);
                color: var(--white);
            }
            .btn-orange:hover {
                background: var(--brand-orange-hover);
            }
            .btn-secondary {
                background: transparent;
                color: var(--slate-900);
            }
            .btn-secondary:hover {
                color: var(--brand-blue);
            }

            /* Hero (Split Screen) */
            .hero {
                background: var(--white);
                padding: 70px 0 90px 0;
            }
            .hero-grid {
                display: grid;
                grid-template-columns: 1.15fr 1fr;
                gap: 56px;
                align-items: center;
            }
            .hero-text {
                text-align: left;
            }
            .hero-text h1 {
                font-size: 46px;
                font-weight: 900;
                color: var(--slate-950);
                letter-spacing: -0.03em;
                margin: 0 0 20px 0;
                line-height: 1.15;
            }
            .hero-text h1 span.highlight {
                color: var(--brand-orange);
            }
            .hero-text p {
                font-size: 17.5px;
                color: var(--slate-600);
                margin: 0 0 36px 0;
                line-height: 1.6;
            }
            .hero-actions {
                display: flex;
                justify-content: flex-start;
                gap: 16px;
            }

            /* Dashboard Mockup (Front-facing, Flat Clean View) */
            .hero-mockup {
                position: relative;
                width: 100%;
            }
            .mockup-front-image {
                width: 100%;
                height: auto;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 20px 45px -10px rgba(2, 42, 140, 0.12), 
                            0 10px 20px -5px rgba(0, 0, 0, 0.04);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
                display: block;
            }
            .hero-mockup:hover .mockup-front-image {
                transform: translateY(-4px);
                box-shadow: 0 28px 55px -12px rgba(2, 42, 140, 0.16), 
                            0 12px 24px -6px rgba(0, 0, 0, 0.06);
            }

            /* Bento Grid Section */
            .bento-section {
                padding: 80px 0;
                background: var(--bg-soft);
                border-top: 1px solid var(--slate-100);
                border-bottom: 1px solid var(--slate-100);
            }
            .section-header {
                text-align: center;
                margin-bottom: 56px;
            }
            .section-header h2 {
                font-size: 32px;
                font-weight: 900;
                color: var(--slate-950);
                margin: 0 0 12px 0;
                letter-spacing: -0.02em;
            }
            .section-header p {
                font-size: 16px;
                color: var(--slate-600);
                margin: 0;
            }

            /* Symmetrical Bento Grid (3 Columns) */
            .bento-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }
            .bento-card {
                background: var(--white);
                border: 1px solid var(--slate-100);
                border-radius: 16px;
                padding: 28px;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .app-icon-box {
                width: 44px;
                height: 44px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 20px;
            }
            .bento-card h3 {
                font-size: 18px;
                font-weight: 800;
                margin: 0 0 10px 0;
                color: var(--slate-950);
            }
            .bento-card p {
                font-size: 13.5px;
                line-height: 1.55;
                color: var(--slate-600);
                margin: 0;
            }

            /* Steps Section */
            .steps-section {
                background: var(--white);
                padding: 80px 0;
                border-bottom: 1px solid var(--slate-100);
            }
            .steps-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }
            .step-card {
                padding: 28px;
                border: 1px solid var(--slate-100);
                border-radius: 12px;
                background: var(--bg-soft);
            }
            .step-badge {
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
                color: var(--brand-orange);
                margin-bottom: 12px;
            }
            .step-card h3 {
                font-size: 17px;
                font-weight: 800;
                margin: 0 0 10px 0;
            }
            .step-card p {
                font-size: 13.5px;
                line-height: 1.55;
                color: var(--slate-600);
                margin: 0;
            }

            /* Pricing Section */
            .pricing-section {
                padding: 88px 0;
                background: var(--white);
            }
            .pricing-box {
                max-width: 580px;
                margin: 0 auto;
                background: var(--bg-soft);
                border: 1px solid var(--slate-100);
                border-radius: 16px;
                padding: 40px;
                box-sizing: border-box;
            }
            .pricing-title-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            .pricing-title-row h3 {
                font-size: 22px;
                font-weight: 900;
                margin: 0;
            }
            .trial-tag {
                background: var(--brand-orange);
                color: var(--white);
                font-size: 10px;
                font-weight: 800;
                padding: 4px 10px;
                border-radius: 6px;
                text-transform: uppercase;
            }
            .pricing-cost {
                font-size: 38px;
                font-weight: 900;
                color: var(--brand-blue);
                margin: 20px 0;
            }
            .pricing-features-list {
                list-style: none;
                padding: 20px 0;
                margin: 20px 0;
                border-top: 1px solid var(--slate-100);
                border-bottom: 1px solid var(--slate-100);
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .pricing-feature-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13.5px;
            }
            .pricing-feature-item svg {
                color: var(--emerald);
                width: 16px;
                height: 16px;
                flex-shrink: 0;
            }

            /* FAQ Section */
            .faq-section {
                background: var(--bg-soft);
                border-top: 1px solid var(--slate-100);
                padding: 80px 0;
            }
            .faq-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 40px;
            }
            .faq-item h3 {
                font-size: 16px;
                font-weight: 800;
                margin: 0 0 8px 0;
            }
            .faq-item p {
                font-size: 13.5px;
                line-height: 1.6;
                color: var(--slate-600);
                margin: 0;
            }

            /* Footer */
            .footer {
                background: var(--slate-950);
                color: #94a3b8;
                padding: 48px 0;
            }
            .footer-inner {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .footer-logo img {
                height: 44px;
                opacity: 0.8;
                filter: brightness(0) invert(1);
            }
            .footer-links {
                display: flex;
                gap: 24px;
                font-size: 13px;
                font-weight: 600;
            }
            .footer-links a {
                color: #94a3b8;
                text-decoration: none;
            }
            .footer-links a:hover {
                color: var(--white);
            }

            /* Responsive */
            @media (max-width: 992px) {
                .hero {
                    padding: 50px 0 60px 0;
                }
                .hero-grid {
                    grid-template-columns: 1fr;
                    text-align: center;
                    gap: 40px;
                }
                .hero-text {
                    text-align: center;
                }
                .hero-text h1 {
                    font-size: 36px;
                    margin: 0 auto 20px auto;
                }
                .hero-text p {
                    margin: 0 auto 36px auto;
                }
                .hero-actions {
                    justify-content: center;
                }
                .bento-grid {
                    grid-template-columns: 1fr;
                }
                .steps-grid {
                    grid-template-columns: 1fr;
                }
                .pricing-features-list {
                    grid-template-columns: 1fr;
                }
                .faq-grid {
                    grid-template-columns: 1fr;
                }
            }
            @media (max-width: 768px) {
                .hero-text h1 {
                    font-size: 30px;
                }
                .header-inner {
                    height: 56px;
                }
                .nav-menu {
                    display: none;
                }
                .footer-inner {
                    flex-direction: column;
                    gap: 20px;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
        
        <!-- Header -->
        <header class="header">
            <div class="container header-inner">
                <a href="#" class="logo">
                    <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios">
                </a>
                <nav class="nav-menu" aria-label="Navegación principal">
                    <a href="#como-funciona">Cómo funciona</a>
                    <a href="#planes">Precios</a>
                    <a href="#preguntas">Ayuda</a>
                </nav>
                <div class="auth-buttons">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            Ir al panel
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary">
                            Ingresar
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            Crear cuenta
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <!-- Hero (Split Screen Layout) -->
            <section class="hero">
                <div class="container">
                    <div class="hero-grid">
                        <div class="hero-text">
                            <h1>Gestiona tus despachos locales desde <span class="highlight">un solo lugar</span></h1>
                            <p>Sencillo, rápido y con tu propia marca. Automatiza tus envíos e imprime etiquetas profesionales.</p>
                            <div class="hero-actions">
                                <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 12px 28px; font-size: 14.5px;">Comienza ahora, es gratis</a>
                            </div>
                        </div>
                        <div class="hero-mockup">
                            <img src="{{ asset('images/dashboard_front.jpg') }}" alt="Tus Envíos Dashboard" class="mockup-front-image">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bento Grid Section -->
            <section class="bento-section">
                <div class="container">
                    <div class="section-header">
                        <h2>Todo lo que ofrece Tus Envíos</h2>
                        <p>Una cuadrícula modular, limpia y compacta enfocada al 100% en las características del sistema.</p>
                    </div>

                    <div class="bento-grid">
                        
                        <!-- Card 1: Productos rápidos -->
                        <div class="bento-card">
                            <div>
                                <div class="app-icon-box" style="background: rgba(245, 158, 11, 0.08);">
                                    <svg style="width:24px; height:24px; color:#d97706;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <h3>Productos rápidos</h3>
                                <p>Guarda tus artículos estrella con precios y pesos predefinidos para agregarlos a cualquier etiqueta en un instante.</p>
                            </div>
                            <!-- Clean CSS/HTML Product table markup -->
                            <div style="margin-top: 20px; font-size: 11px; border: 1px solid var(--slate-100); border-radius: 8px; overflow: hidden; background: var(--bg-soft);">
                                <div style="display: flex; justify-content: space-between; padding: 6px 10px; border-bottom: 1px solid var(--slate-100); font-weight: 700; background: #fff;">
                                    <span>Producto</span>
                                    <span>Stock</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 6px 10px;">
                                    <span>Termo TermoPro</span>
                                    <span style="color: var(--emerald); font-weight: 700;">14 u</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 6px 10px; border-top: 1px solid var(--slate-100);">
                                    <span>Audífonos Air</span>
                                    <span style="color: var(--emerald); font-weight: 700;">8 u</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Etiquetas Pro -->
                        <div class="bento-card">
                            <div>
                                <div class="app-icon-box" style="background: rgba(37, 99, 235, 0.08);">
                                    <svg style="width:24px; height:24px; color:var(--brand-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3>Etiquetas listas</h3>
                                <p>Genera plantillas PDF listas para impresión en papel clásico o formato autoadhesivo térmico profesional de 100 x 150 mm.</p>
                            </div>
                        </div>

                        <!-- Card 3: Despacho flexible -->
                        <div class="bento-card">
                            <div>
                                <div class="app-icon-box" style="background: rgba(234, 88, 12, 0.08);">
                                    <svg style="width:24px; height:24px; color:var(--brand-orange);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1" />
                                    </svg>
                                </div>
                                <h3>Despacho flexible</h3>
                                <p>Organiza la ruta con tus mensajeros de confianza o asigna los despachos a tus aliados logísticos locales de forma simple.</p>
                            </div>
                        </div>

                        <!-- Card 4: Historial de guías -->
                        <div class="bento-card">
                            <div>
                                <div class="app-icon-box" style="background: rgba(14, 165, 233, 0.08);">
                                    <svg style="width:24px; height:24px; color:#0284c7;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h3>Historial de guías</h3>
                                <p>Consulta estados de entrega, reportes históricos de recaudos contra entrega y datos financieros desde tu panel.</p>
                            </div>
                            <!-- Clean CSS/HTML Guia log markup -->
                            <div style="margin-top: 20px; font-size: 11px; border: 1px solid var(--slate-100); border-radius: 8px; overflow: hidden; background: #fff;">
                                <div style="display: flex; justify-content: space-between; padding: 6px 10px; border-bottom: 1px solid var(--slate-100); align-items: center;">
                                    <span style="font-weight: 700;">Guía #1002</span>
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--emerald); padding: 2px 6px; border-radius: 4px; font-weight: 700;">Entregado</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 6px 10px; align-items: center;">
                                    <span style="font-weight: 700;">Guía #1003</span>
                                    <span style="background: rgba(37, 99, 235, 0.1); color: var(--brand-blue); padding: 2px 6px; border-radius: 4px; font-weight: 700;">En camino</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 5: Marca propia -->
                        <div class="bento-card">
                            <div>
                                <div class="app-icon-box" style="background: rgba(244, 63, 94, 0.08);">
                                    <svg style="width:24px; height:24px; color:#e11d48;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21" />
                                    </svg>
                                </div>
                                <h3>Tu marca destacada</h3>
                                <p>Sube tu logotipo corporativo, número de WhatsApp e Instagram para que se agreguen en cada etiqueta automáticamente.</p>
                            </div>
                        </div>

                        <!-- Card 6: Inventario -->
                        <div class="bento-card">
                            <div>
                                <div class="app-icon-box" style="background: rgba(16, 185, 129, 0.08);">
                                    <svg style="width:24px; height:24px; color:var(--emerald);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                                    </svg>
                                </div>
                                <h3>Stock sincronizado</h3>
                                <p>Evita vender unidades agotadas. La plataforma descuenta stock de tu catálogo al emitir la guía de envío física.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Steps Section -->
            <section id="como-funciona" class="steps-section">
                <div class="container">
                    <div class="steps-header">
                        <h2>Despacha de forma eficiente en 3 pasos</h2>
                    </div>
                    <div class="steps-grid">
                        <!-- Step 1 -->
                        <div class="step-card">
                            <div class="step-badge">Paso 1</div>
                            <h3>Configura tu marca</h3>
                            <p>Sube tu logotipo y tus redes de contacto que irán impresas en cada guía que despaches.</p>
                        </div>
                        <!-- Step 2 -->
                        <div class="step-card">
                            <div class="step-badge">Paso 2</div>
                            <h3>Genera la guía</h3>
                            <p>Ingresa la dirección del destinatario y selecciona tus productos de catálogo en segundos.</p>
                        </div>
                        <!-- Step 3 -->
                        <div class="step-card">
                            <div class="step-badge">Paso 3</div>
                            <h3>Imprime y despacha</h3>
                            <p>Imprime la etiqueta autoadhesiva, pégala en el paquete y contrólalo en tu panel administrativo.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section id="planes" class="pricing-section">
                <div class="container">
                    <div class="steps-header">
                        <h2>Un solo plan con todo incluido</h2>
                    </div>

                    @php $featuredPlan = $plans->first(); @endphp
                    <div class="pricing-box">
                        <div class="pricing-title-row">
                            <h3>Plan {{ $featuredPlan?->name ?: 'Emprende' }}</h3>
                            <span class="trial-tag">10 guías gratis</span>
                        </div>
                        <div class="pricing-cost">
                            ${{ number_format($featuredPlan?->monthly_price ?? 19900, 0, ',', '.') }} <span style="font-size: 14px; color: var(--slate-600); font-weight: 500;">/ mes</span>
                        </div>
                        <ul class="pricing-features-list">
                            <li class="pricing-feature-item">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Guías ilimitadas mensuales
                            </li>
                            <li class="pricing-feature-item">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Etiquetas térmicas con tu logo
                            </li>
                            <li class="pricing-feature-item">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Catálogo de productos rápidos
                            </li>
                            <li class="pricing-feature-item">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Control simple de inventario
                            </li>
                            <li class="pricing-feature-item">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Historial de guías y reportes
                            </li>
                        </ul>
                        <div style="margin-top: 28px;">
                            <a href="{{ route('register') }}" class="btn btn-primary" style="width: 100%; height: 44px;">Empezar mi prueba gratis</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="preguntas" class="faq-section">
                <div class="container">
                    <div class="steps-header" style="margin-bottom: 56px;">
                        <h2>Preguntas frecuentes</h2>
                    </div>
                    <div class="faq-grid">
                        <div class="faq-item">
                            <h3>¿Tiene límites de etiquetas?</h3>
                            <p>No. Con tu plan activo puedes emitir e imprimir todas las etiquetas térmicas que requiera la logística diaria de tu tienda.</p>
                        </div>
                        <div class="faq-item">
                            <h3>¿Qué sucede al acabar las guías gratis?</h3>
                            <p>Conservas el acceso para consultar los reportes. Para emitir nuevas etiquetas térmicas, debes activar la suscripción mensual.</p>
                        </div>
                        <div class="faq-item">
                            <h3>¿Se integra con transportadoras nacionales?</h3>
                            <p>Tus Envíos está diseñado para despachos con domiciliarios y mensajería local propia o aliada. La integración con envíos nacionales está en desarrollo.</p>
                        </div>
                        <div class="faq-item">
                            <h3>¿Puedo usar mi logo propio?</h3>
                            <p>Sí, la etiqueta autoadhesiva final llevará tu logo, número de WhatsApp corporativo e Instagram.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container footer-inner">
                <div class="footer-info">
                    <div class="footer-logo">
                        <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios">
                    </div>
                    <p class="footer-desc">Etiquetas y guías profesionales para emprendimientos colombianos.</p>
                </div>
                <div class="footer-links">
                    <a href="{{ route('login') }}">Ingresar</a>
                    <a href="{{ route('register') }}" style="color: var(--brand-orange);">Crear cuenta</a>
                </div>
            </div>
            <div class="footer-copyright">
                &copy; {{ date('Y') }} Tus Envíos. Todos los derechos reservados.
            </div>
        </footer>
    </body>
</html>
