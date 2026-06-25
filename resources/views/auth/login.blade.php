<x-guest-layout>
<style>
    .lp-wrap { display:flex; min-height:460px; }

    /* ── Painel esquerdo ── */
    .lp-left {
        flex: 1;
        position: relative;
        background: linear-gradient(135deg, #0d1520 0%, #111c2e 60%, #0d1117 100%);
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 40px 32px;
        overflow: hidden;
    }
    .lp-left::before {
        content:'';
        position:absolute; inset:0;
        background: radial-gradient(ellipse at 30% 50%, rgba(249,115,22,0.08) 0%, transparent 65%),
                    radial-gradient(ellipse at 80% 20%, rgba(59,130,246,0.07) 0%, transparent 60%);
        pointer-events:none;
    }
    .lp-left-blur {
        position:absolute; width:220px; height:220px;
        border-radius:50%;
        background: rgba(249,115,22,0.06);
        filter: blur(60px);
        top: 10%; left: 10%;
    }
    .lp-left-blur2 {
        position:absolute; width:160px; height:160px;
        border-radius:50%;
        background: rgba(59,130,246,0.06);
        filter: blur(50px);
        bottom: 15%; right: 5%;
    }
    .lp-tagline { text-align:center; margin-top:24px; position:relative; z-index:1; }
    .lp-tagline h2 { color:#f97316; font-size:1.1rem; font-weight:700; margin:0 0 6px; letter-spacing:0.04em; }
    .lp-tagline p { color:#4a6080; font-size:0.78rem; margin:0; line-height:1.5; }

    /* ── Painel direito ── */
    .lp-right {
        width: 360px;
        flex-shrink: 0;
        background: rgba(20,24,36,0.85);
        backdrop-filter: blur(24px) saturate(1.4);
        -webkit-backdrop-filter: blur(24px) saturate(1.4);
        border-left: 1px solid rgba(249,115,22,0.18);
        display: flex; flex-direction: column; justify-content: center;
        padding: 44px 36px;
    }
    .lp-right-title { margin-bottom:28px; }
    .lp-right-title h1 { color:#f0f4ff; font-size:1.15rem; font-weight:700; margin:0 0 4px; }
    .lp-right-title p { color:#4a6080; font-size:0.78rem; margin:0; }

    .lp-field { display:flex; flex-direction:column; gap:5px; margin-bottom:16px; }
    .lp-field label { color:#f97316; font-size:0.7rem; font-weight:600; letter-spacing:0.06em; text-transform:uppercase; }
    .lp-field input {
        width:100%; box-sizing:border-box;
        background: rgba(15,20,35,0.7);
        backdrop-filter: blur(8px);
        border: 1px solid #1e2a40; border-radius:7px;
        color:#dde6f5; font-size:0.86rem; padding:10px 13px;
        outline:none; transition:border-color .2s, box-shadow .2s;
    }
    .lp-field input:focus {
        border-color: rgba(249,115,22,0.55);
        box-shadow: 0 0 0 3px rgba(249,115,22,0.08);
    }
    .lp-field input::placeholder { color:#3a4d68; }

    .lp-remember { display:flex; align-items:center; gap:8px; margin-bottom:24px; }
    .lp-remember input[type=checkbox] { width:14px; height:14px; accent-color:#f97316; cursor:pointer; }
    .lp-remember label { color:#4a6080; font-size:0.76rem; cursor:pointer; }

    .lp-btn {
        width:100%; padding:10px; border:none; border-radius:7px; cursor:pointer;
        background: linear-gradient(135deg, #c2560a 0%, #f97316 100%);
        color:#fff; font-size:0.86rem; font-weight:700;
        letter-spacing:0.06em; text-transform:uppercase;
        box-shadow: 0 4px 18px rgba(249,115,22,0.28);
        transition: opacity .2s, transform .15s, box-shadow .2s;
    }
    .lp-btn:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 6px 22px rgba(249,115,22,0.38); }
    .lp-btn:active { transform:translateY(0); }

    .lp-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:6px; padding:8px 12px; margin-bottom:16px; }
    .lp-error p { color:#f87171; font-size:0.76rem; margin:0; }

    @media(max-width:640px){
        .lp-left { display:none; }
        .lp-right { width:100%; border-left:none; }
    }
</style>

<div class="lp-wrap">

    {{-- ── Painel esquerdo: ilustração SVG ── --}}
    <div class="lp-left">
        <div class="lp-left-blur"></div>
        <div class="lp-left-blur2"></div>

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 340 280" style="width:100%;max-width:320px;position:relative;z-index:1" fill="none">

            <!-- Grade de fundo -->
            <defs>
                <pattern id="grid" width="30" height="30" patternUnits="userSpaceOnUse">
                    <path d="M 30 0 L 0 0 0 30" fill="none" stroke="rgba(249,115,22,0.07)" stroke-width="0.5"/>
                </pattern>
                <linearGradient id="barGrad1" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#f97316" stop-opacity="0.9"/>
                    <stop offset="100%" stop-color="#c2560a" stop-opacity="0.4"/>
                </linearGradient>
                <linearGradient id="barGrad2" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.8"/>
                    <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.3"/>
                </linearGradient>
                <linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#f97316" stop-opacity="0.2"/>
                    <stop offset="100%" stop-color="#f97316" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <rect width="340" height="280" fill="url(#grid)"/>

            <!-- Eixos -->
            <line x1="40" y1="220" x2="300" y2="220" stroke="rgba(249,115,22,0.25)" stroke-width="1"/>
            <line x1="40" y1="40"  x2="40"  y2="220" stroke="rgba(249,115,22,0.25)" stroke-width="1"/>

            <!-- Linhas de referência horizontais -->
            <line x1="40" y1="170" x2="300" y2="170" stroke="rgba(255,255,255,0.04)" stroke-width="1" stroke-dasharray="4 4"/>
            <line x1="40" y1="120" x2="300" y2="120" stroke="rgba(255,255,255,0.04)" stroke-width="1" stroke-dasharray="4 4"/>
            <line x1="40" y1="70"  x2="300" y2="70"  stroke="rgba(255,255,255,0.04)" stroke-width="1" stroke-dasharray="4 4"/>

            <!-- Barras -->
            <rect x="60"  y="160" width="22" height="60" rx="3" fill="url(#barGrad2)" opacity="0.7"/>
            <rect x="100" y="140" width="22" height="80" rx="3" fill="url(#barGrad1)"/>
            <rect x="140" y="110" width="22" height="110" rx="3" fill="url(#barGrad2)" opacity="0.7"/>
            <rect x="180" y="90"  width="22" height="130" rx="3" fill="url(#barGrad1)"/>
            <rect x="220" y="65"  width="22" height="155" rx="3" fill="url(#barGrad2)" opacity="0.7"/>
            <rect x="260" y="48"  width="22" height="172" rx="3" fill="url(#barGrad1)"/>

            <!-- Área sob a linha de tendência -->
            <path d="M71 160 L111 140 L151 110 L191 90 L231 65 L271 48 L271 220 L71 220 Z"
                  fill="url(#lineGrad)" opacity="0.5"/>

            <!-- Linha de tendência -->
            <polyline points="71,160 111,140 151,110 191,90 231,65 271,48"
                      stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

            <!-- Pontos da linha -->
            <circle cx="71"  cy="160" r="3.5" fill="#f97316" opacity="0.9"/>
            <circle cx="111" cy="140" r="3.5" fill="#f97316" opacity="0.9"/>
            <circle cx="151" cy="110" r="3.5" fill="#f97316" opacity="0.9"/>
            <circle cx="191" cy="90"  r="3.5" fill="#f97316" opacity="0.9"/>
            <circle cx="231" cy="65"  r="3.5" fill="#f97316" opacity="0.9"/>
            <circle cx="271" cy="48"  r="5"   fill="#f97316"/>

            <!-- Moeda flutuante -->
            <circle cx="285" cy="52" r="18" fill="rgba(249,115,22,0.12)" stroke="rgba(249,115,22,0.4)" stroke-width="1.5"/>
            <text x="285" y="57" text-anchor="middle" font-size="14" font-weight="bold" fill="#f97316" font-family="serif">$</text>

            <!-- Mini moedas decorativas -->
            <circle cx="50"  cy="50"  r="10" fill="rgba(59,130,246,0.08)" stroke="rgba(59,130,246,0.3)" stroke-width="1"/>
            <text x="50" y="54" text-anchor="middle" font-size="9" fill="#3b82f6" font-family="serif">$</text>

            <circle cx="315" cy="160" r="13" fill="rgba(249,115,22,0.07)" stroke="rgba(249,115,22,0.25)" stroke-width="1"/>
            <text x="315" y="165" text-anchor="middle" font-size="10" fill="#f97316" font-family="serif">$</text>

            <!-- Seta de crescimento no canto superior direito -->
            <g transform="translate(295,30)" opacity="0.6">
                <line x1="0" y1="12" x2="12" y2="0" stroke="#f97316" stroke-width="1.5" stroke-linecap="round"/>
                <polyline points="7,0 12,0 12,5" stroke="#f97316" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </g>

            <!-- Labels dos eixos Y -->
            <text x="33" y="224" text-anchor="end" font-size="9" fill="rgba(249,115,22,0.4)" font-family="sans-serif">0</text>
            <text x="33" y="174" text-anchor="end" font-size="9" fill="rgba(249,115,22,0.4)" font-family="sans-serif">25k</text>
            <text x="33" y="124" text-anchor="end" font-size="9" fill="rgba(249,115,22,0.4)" font-family="sans-serif">50k</text>
            <text x="33" y="74"  text-anchor="end" font-size="9" fill="rgba(249,115,22,0.4)" font-family="sans-serif">75k</text>
        </svg>

        <div class="lp-tagline">
            <h2>Gestão Financeira</h2>
            <p>Comissões, contratos e folhas<br>em um único painel de controle.</p>
        </div>
    </div>

    {{-- ── Painel direito: formulário ── --}}
    <div class="lp-right">
        <div class="lp-right-title">
            <h1>Bem-vindo de volta</h1>
            <p>Faça login para acessar o sistema</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        @if($errors->any())
        <div class="lp-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="lp-field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="seu@email.com" required autofocus autocomplete="username">
            </div>

            <div class="lp-field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password">
            </div>

            <div class="lp-remember">
                <input type="checkbox" id="remember_me" name="remember">
                <label for="remember_me">Lembrar acesso</label>
            </div>

            <button type="submit" class="lp-btn">Entrar</button>
        </form>
    </div>

</div>

</x-guest-layout>
