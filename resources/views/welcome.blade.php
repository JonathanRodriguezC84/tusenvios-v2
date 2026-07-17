<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#022a8c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Tus Envios">
        <title>Tus Envios | Guias y etiquetas para emprendimientos</title>
        @vite(['resources/css/app.css'])
        <style>
            :root{--brand-blue:#022a8c;--brand-blue-dark:#011f69;--brand-orange:#ff7a00;--ink:#07111f;--muted:#5f6b7a;--line:#dbe2ea;--soft:#f4f7fb}
            *{box-sizing:border-box}
            body{margin:0;background:#fff;color:var(--ink)}
            a{color:inherit;text-decoration:none}
            .site-header{position:sticky;top:0;z-index:20;border-bottom:1px solid var(--line);background:rgba(255,255,255,.96);backdrop-filter:blur(12px)}
            .container{width:min(1180px,calc(100% - 32px));margin:0 auto}
            .header-inner{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 0}
            .logo{display:inline-flex;align-items:center}
            .logo img{width:168px;height:auto;display:block}
            .nav{display:flex;align-items:center;gap:18px;color:#344054;font-size:14px;font-weight:750}
            .nav-links{display:flex;align-items:center;gap:18px}
            .nav a:hover{color:var(--brand-blue)}
            .btn{display:inline-flex;min-height:44px;align-items:center;justify-content:center;gap:8px;border-radius:7px;border:1px solid transparent;padding:0 18px;font-weight:850;line-height:1;transition:transform .15s ease,background .15s ease,border .15s ease}
            .btn:hover{transform:translateY(-1px)}
            .btn-primary{background:var(--brand-blue);color:#fff;box-shadow:0 10px 22px rgba(2,42,140,.18)}
            .btn-primary:hover{background:var(--brand-blue-dark)}
            .btn-secondary{border-color:#cfd8e3;background:#fff;color:var(--ink)}
            .btn-orange{background:var(--brand-orange);color:#fff;box-shadow:0 10px 22px rgba(255,122,0,.18)}
            .hero{overflow:hidden;border-bottom:1px solid var(--line);background:radial-gradient(circle at 12% 8%,rgba(255,122,0,.13),transparent 26%),linear-gradient(180deg,#fff 0%,#f6f9ff 100%)}
            .hero-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(420px,520px);gap:52px;align-items:center;padding:64px 0 58px}
            .eyebrow{color:var(--brand-blue);font-size:13px;font-weight:950;letter-spacing:0;text-transform:uppercase}
            h1{max-width:720px;margin:14px 0 0;font-size:clamp(36px,4.2vw,58px);line-height:1.04;font-weight:950;text-wrap:balance}
            .hero-copy{max-width:650px;margin:22px 0 0;color:#405064;font-size:18px;line-height:1.7}
            .hero-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}
            .trial-note{display:inline-flex;align-items:center;gap:10px;margin-top:20px;border:1px solid rgba(2,42,140,.2);border-radius:8px;background:#fff;padding:12px 14px;color:#263447;font-weight:750;box-shadow:0 12px 30px rgba(15,23,42,.06)}
            .trial-note span{display:grid;width:28px;height:28px;place-items:center;border-radius:50%;background:var(--brand-orange);color:#fff;font-size:13px;font-weight:950}
            .proof-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:28px}
            .proof{border:1px solid var(--line);border-radius:8px;background:rgba(255,255,255,.8);padding:14px;font-size:13px;font-weight:850;color:#263447}
            .product-showcase{position:relative;display:grid;justify-items:center}
            .showcase-card{width:min(100%,470px);border:1px solid #d6deea;border-radius:8px;background:#fff;padding:22px;box-shadow:0 24px 60px rgba(2,42,140,.13)}
            .showcase-caption{margin-bottom:14px;color:#536071;font-size:13px;font-weight:850;text-transform:uppercase}
            .label-demo{aspect-ratio:100/150;border:1px solid #cbd5e1;background:#fff;padding:18px;display:flex;flex-direction:column;gap:12px;font-weight:900;color:#020617}
            .label-head{display:grid;grid-template-columns:132px 1fr;gap:14px;align-items:center}
            .label-logo img{width:128px;height:auto;display:block}
            .label-company{font-size:21px;line-height:1.1}
            .label-company small{display:block;margin-top:4px;font-size:10px;line-height:1.35;text-transform:uppercase}
            .social-line{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;border-bottom:1px solid #cbd5e1;padding:2px 0 10px;font-size:10px}
            .social-line span{display:inline-flex;align-items:center;gap:5px}
            .social-icon{display:grid;width:18px;height:18px;place-items:center;border-radius:999px;background:#020617;color:#fff;font-size:8px}
            .guide-number{text-align:center;font-size:18px}
            .barcode{height:74px;border-bottom:1px solid #111827;background:#fff;overflow:hidden}
            .barcode svg{display:block;width:100%;height:100%}
            .recipient-block{display:grid;grid-template-columns:minmax(0,1fr) 92px;gap:14px;align-items:center;border-bottom:1px solid #cbd5e1;padding:10px 0 14px}
            .label-field{color:#334155;font-size:10px;line-height:1;text-transform:uppercase}
            .recipient-name,.recipient-address{margin-top:4px;font-size:21px;line-height:1.05}
            .recipient-small{margin-top:6px;font-size:14px;line-height:1.1}
            .qr{width:86px;height:86px;display:grid;place-items:center;background:#fff;justify-self:end}
            .qr img{display:block;width:100%;height:100%}
            .label-observations{border-bottom:1px solid #cbd5e1;padding-bottom:12px;font-size:13px}
            .meta-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;border-bottom:1px solid #cbd5e1;padding-bottom:10px;text-align:center}
            .meta-row p{margin:0}.meta-label{color:#334155;font-size:10px}.meta-value{margin-top:6px;font-size:15px}
            .label-footer{display:grid;grid-template-columns:1fr 1fr 1fr;margin-top:auto;font-size:9px;text-align:center}
            .section{padding:56px 0;border-bottom:1px solid var(--line);scroll-margin-top:90px}
            .section-soft{background:var(--soft)}
            .section-title{max-width:720px}
            .section-title h2{margin:8px 0 0;font-size:clamp(30px,3.2vw,44px);line-height:1.05;font-weight:950;text-wrap:balance}
            .section-title p{margin:14px 0 0;color:var(--muted);font-size:16px;line-height:1.7}
            .steps-grid,.benefits-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:28px}
            .card{border:1px solid var(--line);border-radius:8px;background:#fff;padding:20px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
            .card-number{display:grid;width:32px;height:32px;place-items:center;border-radius:8px;background:#e8efff;color:var(--brand-blue);font-size:14px;font-weight:950}
            .card h3{margin:14px 0 0;font-size:18px;line-height:1.25;font-weight:950}
            .card p{margin:10px 0 0;color:var(--muted);font-size:14px;line-height:1.6}
            .benefits-grid{grid-template-columns:repeat(5,minmax(0,1fr))}
            .benefit{border:1px solid #d9e2f0;border-radius:8px;background:#fff;padding:18px;min-height:132px}
            .benefit strong{display:block;font-size:15px;line-height:1.25}
            .benefit span{display:block;margin-top:10px;color:var(--muted);font-size:13px;line-height:1.5}
            .audience-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-top:28px}
            .audience-item{display:grid;min-height:112px;place-items:center;gap:10px;border:1px solid var(--line);border-radius:8px;background:#fff;padding:16px;text-align:center;font-weight:900;color:#263447}
            .landing-icon{display:inline-grid;width:38px;height:38px;place-items:center;border-radius:8px;background:#eef4ff;color:var(--brand-blue)}
            .landing-icon svg{width:22px;height:22px;stroke:currentColor}
            .plans-grid{display:grid;grid-template-columns:minmax(0,760px);justify-content:center;gap:18px;margin-top:30px}
            .plan{display:flex;min-height:420px;flex-direction:column;border:1px solid var(--line);border-radius:8px;background:#fff;padding:22px}
            .plan-featured{border:2px solid var(--brand-blue);box-shadow:0 18px 44px rgba(2,42,140,.12)}
            .plan-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
            .badge{border-radius:999px;background:#e8efff;color:var(--brand-blue);padding:6px 10px;font-size:11px;font-weight:950}
            .plan h3{margin:0;font-size:22px;font-weight:950}
            .plan-purpose{margin:10px 0 0;color:var(--muted);font-size:14px;line-height:1.5}
            .price{margin:20px 0 0;font-size:34px;line-height:1;font-weight:950}
            .price small{color:var(--muted);font-size:13px;font-weight:800}
            .plan ul{display:grid;gap:10px;margin:22px 0;padding:0;list-style:none;color:#344054;font-size:14px;line-height:1.45}
            .plan li::before{content:"";display:inline-block;width:7px;height:7px;margin-right:8px;border-radius:50%;background:var(--brand-orange);vertical-align:1px}
            .plan .btn{width:100%;margin-top:auto}
            .trust-row{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-top:28px}
            .trust{display:grid;align-content:start;gap:12px;min-height:120px;border:1px solid rgba(2,42,140,.16);border-radius:8px;background:#fff;padding:16px;color:#243248;font-size:14px;font-weight:850;line-height:1.4}
            .faq-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:32px;align-items:start}
            .faq-list{display:grid;gap:12px}
            .cta-band{background:var(--brand-blue);color:#fff}
            .cta-inner{display:flex;align-items:center;justify-content:space-between;gap:28px;padding:44px 0}
            .cta-inner h2{font-size:clamp(28px,3.5vw,44px);line-height:1.05;font-weight:950;text-wrap:balance;margin:0}
            .cta-inner p{margin:12px 0 0;color:rgba(255,255,255,.8);line-height:1.6}
            .footer{background:#fff;border-top:1px solid var(--line)}
            .footer-inner{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:22px 0;color:#5f6b7a;font-size:14px;font-weight:750}
            .footer img{width:136px;height:auto}
            .trust-row .trust{align-content:center;justify-items:center;text-align:center}
            .trust-row .landing-icon{margin-left:auto;margin-right:auto}
            .cta-band .btn{min-width:210px;padding-left:28px;padding-right:28px;white-space:nowrap}
            .cta-inner{grid-template-columns:minmax(0,1fr) auto}
            @media(min-width:981px){.cta-inner>div{max-width:780px}}
            @media(max-width:980px){.nav-links{display:none}.hero-grid,.faq-grid{grid-template-columns:1fr}.product-showcase{order:-1}.benefits-grid,.trust-row{grid-template-columns:repeat(2,minmax(0,1fr))}.audience-grid,.plans-grid,.steps-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
            @media(max-width:640px){.container{width:min(100% - 24px,1180px)}.header-inner{align-items:center}.logo img{width:132px}.nav{gap:8px}.nav .btn-secondary{display:none}.btn{min-height:42px;padding:0 13px;font-size:13px}.hero-grid{gap:24px;padding:28px 0 36px}.showcase-card{padding:12px}.label-demo{padding:11px;gap:8px}.label-head{grid-template-columns:88px 1fr;gap:8px}.label-logo img{width:86px}.label-company{font-size:14px}.label-company small{font-size:7px}.guide-number{font-size:13px}.barcode{height:46px}.recipient-block{grid-template-columns:1fr 62px;gap:8px;padding:6px 0 9px}.recipient-name,.recipient-address{font-size:14px}.recipient-small,.meta-value{font-size:10px}.qr{width:58px;height:58px}.label-observations{font-size:9px}.social-line,.label-field,.meta-label,.label-footer{font-size:7px}.social-icon{width:14px;height:14px;font-size:6px}.proof-row,.benefits-grid,.audience-grid,.plans-grid,.steps-grid,.trust-row{grid-template-columns:1fr}.hero-actions,.cta-inner,.footer-inner{align-items:stretch;flex-direction:column}.hero-actions .btn,.cta-inner .btn{width:100%}.section{padding:40px 0}.plan{min-height:0}.site-header .header-inner{display:flex;align-items:center;justify-content:space-between;gap:8px}.site-header .logo{min-width:0;flex:1 1 auto}.site-header .logo img{width:clamp(104px,34vw,132px);max-width:100%;height:auto}.site-header .nav{display:flex;flex:0 0 auto;align-items:center;gap:6px}.site-header .nav .btn-secondary{display:inline-flex!important}.site-header .nav .btn{min-height:38px;border-radius:7px;padding:0 10px;font-size:12px;line-height:1;white-space:nowrap}.site-header .nav .btn-primary{padding-left:11px;padding-right:11px}}@media(max-width:380px){.site-header .logo img{width:96px}.site-header .nav .btn{padding-left:8px;padding-right:8px;font-size:11px}}
        </style>
        <link rel="icon" href="/favicon.ico?v=20260521v15" sizes="any">
    </head>
    <body>
        <header class="site-header">
            <div class="container header-inner">
                <a href="/" class="logo" aria-label="Tus Envios">
                    <img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios">
                </a>
                <nav class="nav" aria-label="Navegacion principal">
                    <div class="nav-links">
                        <a href="#como-funciona">Como funciona</a>
                        <a href="#planes">Plan Emprende</a>
                        <a href="#preguntas">Preguntas</a>
                        <a href="{{ route('tracking.index') }}">Rastrear guia</a>
                    </div>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" style="margin:0">
                            @csrf
                            <input type="hidden" name="redirect_to" value="/login">
                            <button type="submit" class="btn btn-secondary">Ingresar</button>
                        </form>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0">
                            @csrf
                            <input type="hidden" name="redirect_to" value="/register">
                            <button type="submit" class="btn btn-primary">Crear cuenta</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary">Ingresar</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Crear cuenta</a>
                    @endauth
                </nav>
            </div>
        </header>
        <main>
            <section class="hero">
                <div class="container hero-grid">
                    <div>
                        <p class="eyebrow">Plan Emprende para negocios locales</p>
                        <h1>Organiza tus entregas locales sin volverte una transportadora.</h1>
                        <p class="hero-copy">Tus Envios esta hecho para emprendedores que venden por WhatsApp, Instagram o tienda propia y despachan con mensajeria propia o aliados de confianza. Crea guias, imprime etiquetas con tu marca y lleva control de cada entrega desde un solo lugar.</p>
                        <div class="hero-actions">
                            <a href="{{ route('register') }}" class="btn btn-primary">Probar 10 guias gratis</a>
                            <a href="#como-funciona" class="btn btn-secondary">Ver como funciona</a>
                        </div>
                        <div class="trial-note"><span>10</span><strong>Crea tus primeras 10 guias gratis. Luego activa tu plan mensual.</strong></div>
                        <div class="proof-row" aria-label="Beneficios principales">
                            <div class="proof">Para mensajeria propia o terceros</div>
                            <div class="proof">Etiquetas con tu marca</div>
                            <div class="proof">Pensado para emprendedores colombianos</div>
                        </div>
                    </div>
                    <div class="product-showcase">
                        <div class="showcase-card">
                            <p class="showcase-caption">Asi se vera tu guia impresa</p>
                            <div class="label-demo" aria-label="Etiqueta demo">
                                <div class="label-head">
                                    <div class="label-logo"><img src="{{ asset('images/logotusenvios.png') }}" alt="Logo demo"></div>
                                    <div class="label-company">DULCE AROMA STORE<small>Bodega principal - Calle 100 #15-20<br>Chapinero / Bogota<br>Gracias por tu compra.</small></div>
                                </div>
                                <div class="social-line">
                                    <span><span class="social-icon">WA</span>3001234567</span>
                                    <span><span class="social-icon">IG</span>@dulcearomastore</span>
                                </div>
                                <div class="guide-number">DAS202600001</div>
                                <div class="barcode real-barcode"><svg class="barcode-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 422 74" role="img" aria-label="Codigo de barras"><rect x="8" y="0" width="2" height="74" fill="#111827"/><rect x="15" y="0" width="2" height="74" fill="#111827"/><rect x="19" y="0" width="5" height="74" fill="#111827"/><rect x="26" y="0" width="5" height="74" fill="#111827"/><rect x="33" y="0" width="2" height="74" fill="#111827"/><rect x="37" y="0" width="2" height="74" fill="#111827"/><rect x="41" y="0" width="2" height="74" fill="#111827"/><rect x="45" y="0" width="5" height="74" fill="#111827"/><rect x="55" y="0" width="2" height="74" fill="#111827"/><rect x="59" y="0" width="5" height="74" fill="#111827"/><rect x="66" y="0" width="5" height="74" fill="#111827"/><rect x="73" y="0" width="2" height="74" fill="#111827"/><rect x="77" y="0" width="2" height="74" fill="#111827"/><rect x="84" y="0" width="2" height="74" fill="#111827"/><rect x="88" y="0" width="5" height="74" fill="#111827"/><rect x="95" y="0" width="2" height="74" fill="#111827"/><rect x="99" y="0" width="5" height="74" fill="#111827"/><rect x="106" y="0" width="2" height="74" fill="#111827"/><rect x="110" y="0" width="5" height="74" fill="#111827"/><rect x="120" y="0" width="2" height="74" fill="#111827"/><rect x="124" y="0" width="2" height="74" fill="#111827"/><rect x="128" y="0" width="5" height="74" fill="#111827"/><rect x="138" y="0" width="2" height="74" fill="#111827"/><rect x="142" y="0" width="2" height="74" fill="#111827"/><rect x="146" y="0" width="5" height="74" fill="#111827"/><rect x="153" y="0" width="2" height="74" fill="#111827"/><rect x="157" y="0" width="2" height="74" fill="#111827"/><rect x="164" y="0" width="5" height="74" fill="#111827"/><rect x="171" y="0" width="5" height="74" fill="#111827"/><rect x="178" y="0" width="2" height="74" fill="#111827"/><rect x="182" y="0" width="2" height="74" fill="#111827"/><rect x="186" y="0" width="5" height="74" fill="#111827"/><rect x="196" y="0" width="2" height="74" fill="#111827"/><rect x="200" y="0" width="2" height="74" fill="#111827"/><rect x="204" y="0" width="5" height="74" fill="#111827"/><rect x="211" y="0" width="2" height="74" fill="#111827"/><rect x="215" y="0" width="5" height="74" fill="#111827"/><rect x="225" y="0" width="5" height="74" fill="#111827"/><rect x="232" y="0" width="2" height="74" fill="#111827"/><rect x="236" y="0" width="2" height="74" fill="#111827"/><rect x="240" y="0" width="2" height="74" fill="#111827"/><rect x="244" y="0" width="2" height="74" fill="#111827"/><rect x="251" y="0" width="5" height="74" fill="#111827"/><rect x="258" y="0" width="5" height="74" fill="#111827"/><rect x="265" y="0" width="2" height="74" fill="#111827"/><rect x="269" y="0" width="2" height="74" fill="#111827"/><rect x="273" y="0" width="2" height="74" fill="#111827"/><rect x="280" y="0" width="5" height="74" fill="#111827"/><rect x="287" y="0" width="5" height="74" fill="#111827"/><rect x="294" y="0" width="2" height="74" fill="#111827"/><rect x="298" y="0" width="2" height="74" fill="#111827"/><rect x="302" y="0" width="2" height="74" fill="#111827"/><rect x="309" y="0" width="5" height="74" fill="#111827"/><rect x="316" y="0" width="5" height="74" fill="#111827"/><rect x="323" y="0" width="2" height="74" fill="#111827"/><rect x="327" y="0" width="2" height="74" fill="#111827"/><rect x="331" y="0" width="2" height="74" fill="#111827"/><rect x="338" y="0" width="5" height="74" fill="#111827"/><rect x="345" y="0" width="5" height="74" fill="#111827"/><rect x="352" y="0" width="2" height="74" fill="#111827"/><rect x="356" y="0" width="5" height="74" fill="#111827"/><rect x="363" y="0" width="2" height="74" fill="#111827"/><rect x="370" y="0" width="2" height="74" fill="#111827"/><rect x="374" y="0" width="2" height="74" fill="#111827"/><rect x="378" y="0" width="5" height="74" fill="#111827"/><rect x="385" y="0" width="2" height="74" fill="#111827"/><rect x="392" y="0" width="2" height="74" fill="#111827"/><rect x="396" y="0" width="5" height="74" fill="#111827"/><rect x="403" y="0" width="5" height="74" fill="#111827"/><rect x="410" y="0" width="2" height="74" fill="#111827"/></svg></div>
                                <div class="recipient-block">
                                    <div>
                                        <div class="label-field">Nombre</div><div class="recipient-name">SOFIA CARDENAS</div>
                                        <div class="label-field" style="margin-top:8px;">Direccion</div><div class="recipient-address">CALLE 140 #12-44</div>
                                        <div class="recipient-small">CEDRITOS / BOGOTA</div>
                                        <div class="recipient-small">3203332211</div>
                                    </div>
                                    <div class="qr"><img src="https://api.qrserver.com/v1/create-qr-code/?size=174x174&margin=14&data=DAS202600001" alt="QR" width="174" height="174"></div>
                                </div>
                                <div class="label-observations"><div class="label-field">Observaciones</div><div style="margin-top:6px;">ENTREGAR EN HORARIO DE OFICINA</div></div>
                                <div class="meta-row">
                                    <div><p class="meta-label">Zona</p><p class="meta-value">NORTE</p></div>
                                    <div><p class="meta-label">Piezas</p><p class="meta-value">1</p></div>
                                    <div><p class="meta-label">Recaudo</p><p class="meta-value">$74.000</p></div>
                                </div>
                                <div class="label-footer"><span>TUSENVIOS.COM.CO</span><span>DAS202600001</span><span>2026-05-19 10:24</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section id="como-funciona" class="section">
                <div class="container"><div class="section-title"><p class="eyebrow">Como funciona</p><h2>De pedido recibido a entrega organizada, sin complicarte.</h2><p>El flujo esta pensado para negocios que hoy trabajan con domiciliarios propios, mensajeros aliados o servicios locales de terceros.</p></div>
                <div class="steps-grid">
                    <article class="card"><span class="card-number">1</span><h3>Crea tu marca</h3><p>Sube logo, WhatsApp, redes sociales y el mensaje que ira en tus etiquetas.</p></article>
                    <article class="card"><span class="card-number">2</span><h3>Registra la guia</h3><p>Escribe cliente, direccion, producto, observaciones y valor a recaudar.</p></article>
                    <article class="card"><span class="card-number">3</span><h3>Entrega y controla</h3><p>Imprime una o varias etiquetas, asigna tu operacion local y consulta tus envios desde computador o celular.</p></article>
                </div></div>
            </section>
            <section class="section section-soft">
                <div class="container"><div class="section-title"><p class="eyebrow">Por que ayuda</p><h2>Tu emprendimiento se ve mas ordenado desde la primera entrega.</h2><p>En Colombia existen grandes transportadoras como Interrapidisimo, Coordinadora y Envia. Tus Envios empieza donde muchos negocios realmente estan: entregas locales, mensajeria propia y terceros cercanos. Luego podremos sumar convenios con esas transportadoras como valor agregado.</p></div>
                <div class="benefits-grid">
                    <div class="benefit"><strong>Tu marca se ve mas profesional.</strong><span>Cada paquete sale con logo, datos claros y una etiqueta consistente.</span></div>
                    <div class="benefit"><strong>Ahorras tiempo creando guias repetidas.</strong><span>Guarda productos frecuentes y evita escribir lo mismo una y otra vez.</span></div>
                    <div class="benefit"><strong>Tus clientes reciben paquetes mejor identificados.</strong><span>Direccion, telefono, zona y observaciones quedan faciles de leer.</span></div>
                    <div class="benefit"><strong>Puedes trabajar desde el celular.</strong><span>Crea, revisa e imprime cuando lo necesites desde el navegador.</span></div>
                    <div class="benefit"><strong>No necesitas operar como transportadora.</strong><span>Empieza simple con guias, etiquetas, zonas y control basico de tus entregas.</span></div>
                </div></div>
            </section>
            <section class="section">
                <div class="container"><div class="section-title"><p class="eyebrow">Para quien es</p><h2>Hecho para negocios que venden todos los dias y necesitan verse mejor.</h2></div>
                <div class="audience-grid">
                    <div class="audience-item"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M8 4 5 6 3 10l4 2v8h10v-8l4-2-2-4-3-2-2.5 2h-3L8 4Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span>Tiendas de ropa</span></div>
                    <div class="audience-item"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 12h16v8H4v-8Z" stroke-width="2" stroke-linejoin="round"/><path d="M6 12V9h12v3M8 9V6m4 3V6m4 3V6M7 16h.01M12 16h.01M17 16h.01" stroke-width="2" stroke-linecap="round"/></svg></span><span>Reposteria</span></div>
                    <div class="audience-item"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="m12 3 1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3ZM19 14l.9 2.1L22 17l-2.1.9L19 20l-.9-2.1L16 17l2.1-.9L19 14ZM5 14l.9 2.1L8 17l-2.1.9L5 20l-.9-2.1L2 17l2.1-.9L5 14Z" stroke-width="2" stroke-linejoin="round"/></svg></span><span>Cosmetica</span></div>
                    <div class="audience-item"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M6 8h12l1 12H5L6 8Z" stroke-width="2" stroke-linejoin="round"/><path d="M9 8a3 3 0 0 1 6 0" stroke-width="2" stroke-linecap="round"/></svg></span><span>Accesorios</span></div>
                    <div class="audience-item"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 10h16v10H4V10ZM3 7h18v3H3V7Z" stroke-width="2" stroke-linejoin="round"/><path d="M12 7v13M8.5 7C7 7 6 6.2 6 5.2S7 3.5 8.2 4.1C9.4 4.7 10.4 6 12 7c1.6-1 2.6-2.3 3.8-2.9C17 3.5 18 4.2 18 5.2S17 7 15.5 7" stroke-width="2" stroke-linecap="round"/></svg></span><span>Detalles y regalos</span></div>
                    <div class="audience-item"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M8 3h8a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke-width="2" stroke-linejoin="round"/><path d="M10 18h4" stroke-width="2" stroke-linecap="round"/></svg></span><span>Ventas por WhatsApp</span></div>
                </div></div>
            </section>
            <section id="planes" class="section section-soft">
                <div class="container"><div class="section-title"><p class="eyebrow">Plan unico</p><h2>Un solo plan para empezar simple: Emprende.</h2><p>La prueba no se pierde por dias. Usas tus primeras guias cuando realmente tengas pedidos y luego activas una mensualidad clara.</p></div>
                <div class="plans-grid">
                    <article class="plan plan-featured"><div class="plan-top"><div><h3>Emprende</h3><p class="plan-purpose">Para negocios locales que quieren ordenar sus entregas sin depender de un sistema complejo.</p></div><span class="badge">10 guias gratis</span></div><p class="price">$19.900 <small>/ mes</small></p><ul><li>Etiquetas y guias ilimitadas al activar</li><li>Logo, redes y mensaje personalizado en cada etiqueta</li><li>Productos frecuentes para crear guias mas rapido</li><li>Impresion individual o por lote</li><li>Control de entregas para mensajeria propia o terceros</li><li>Base lista para futuras alianzas con transportadoras nacionales</li></ul><a href="{{ route('register') }}" class="btn btn-primary">Probar gratis</a></article>
                </div></div>
            </section>
            <section class="section">
                <div class="container"><div class="section-title"><p class="eyebrow">Confianza</p><h2>Simple para empezar, serio para operar.</h2></div>
                <div class="trust-row">
                    <div class="trust"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7.5 8.5c-2 0-3.5 1.5-3.5 3.5s1.5 3.5 3.5 3.5c1.6 0 2.8-1 4.5-3.5 1.7-2.5 2.9-3.5 4.5-3.5 2 0 3.5 1.5 3.5 3.5s-1.5 3.5-3.5 3.5c-1.6 0-2.8-1-4.5-3.5-1.7-2.5-2.9-3.5-4.5-3.5Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span>Sin limite de etiquetas al activar tu plan.</span></div>
                    <div class="trust"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 7h10M7 12h10M7 17h6" stroke-width="2" stroke-linecap="round"/><path d="M5 4h14v16H5V4Z" stroke-width="2" stroke-linejoin="round"/></svg></span><span>Un solo plan, sin enredos para elegir.</span></div>
                    <div class="trust"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M20 13 13 20 4 11V4h7l9 9Z" stroke-width="2" stroke-linejoin="round"/><path d="M8 8h.01" stroke-width="3" stroke-linecap="round"/></svg></span><span>Compatible con varios tamanos de etiquetas, incluyendo 100 x 150 mm.</span></div>
                    <div class="trust"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M3 5h13v10H3V5Z" stroke-width="2" stroke-linejoin="round"/><path d="M8 19h7M11 15v4M17 9h4v10h-4V9Z" stroke-width="2" stroke-linejoin="round"/></svg></span><span>Funciona desde celular y computador.</span></div>
                    <div class="trust"><span class="landing-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M5 6c0-1.7 3.1-3 7-3s7 1.3 7 3-3.1 3-7 3-7-1.3-7-3Z" stroke-width="2"/><path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6" stroke-width="2"/></svg></span><span>Tus datos quedan organizados.</span></div>
                </div></div>
            </section>
            <section id="preguntas" class="section section-soft">
                <div class="container faq-grid">
                    <div class="section-title"><p class="eyebrow">Preguntas frecuentes</p><h2>Lo importante antes de empezar.</h2><p>Tus Envios esta pensado para negocios pequenos que necesitan imprimir etiquetas y organizar pedidos sin montar un sistema complejo.</p></div>
                    <div class="faq-list">
                        <article class="card"><h3>Tiene limite de etiquetas?</h3><p>No. Al activar el plan Emprende puedes crear e imprimir guias y etiquetas sin limite.</p></article>
                        <article class="card"><h3>Que pasa despues de las 10 guias gratis?</h3><p>Puedes seguir entrando a tu cuenta y revisar lo creado. Para crear nuevas guias debes activar el plan mensual.</p></article>
                        <article class="card"><h3>Funciona con Interrapidisimo, Coordinadora o Envia?</h3><p>Hoy esta pensado para mensajeria propia o terceros locales. La idea es que mas adelante podamos sumar convenios con grandes transportadoras como valor agregado.</p></article>
                        <article class="card"><h3>Puedo usar mi logo y redes?</h3><p>Si. Puedes subir logo, WhatsApp, Instagram, Facebook, TikTok, pagina web y mensaje para la etiqueta.</p></article>
                    </div>
                </div>
            </section>
            <section class="cta-band">
                <div class="container cta-inner"><div><p class="eyebrow" style="color:rgba(255,255,255,.78)">Listo para probar</p><h2>Empieza hoy con tu primera etiqueta personalizada.</h2><p>Crea tus primeras 10 guias gratis. Luego activa tu plan mensual cuando quieras seguir creando.</p></div><a href="{{ route('register') }}" class="btn btn-orange">Crear cuenta gratis</a></div>
            </section>
        </main>
        <footer class="footer">
            <div class="container footer-inner">
                <div><img src="{{ asset('images/logotusenvios.png') }}" alt="Tus Envios"><div>Etiquetas y guias para emprendimientos</div></div>
                <div style="display:flex;flex-wrap:wrap;gap:16px"><a href="{{ route('tracking.index') }}">Rastrear guia</a><a href="{{ route('login') }}">Ingresar</a><a href="{{ route('register') }}" style="color:var(--brand-blue)">Crear cuenta</a></div>
            </div>
        </footer>
    </body>
</html>
