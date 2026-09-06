<x-app-layout>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

<style>
body, .min-h-screen { background:#111 !important; color:#e0e0e0; }

.pg-card  { background:#1e1e1e; border:1px solid #2a3d55; border-radius:12px; }
.pg-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#888; margin-bottom:5px; }
.pg-value { font-size:22px; font-weight:800; line-height:1; }
.pg-sub   { font-size:9px; color:#666; margin-top:4px; }

.pg-th { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#888; padding:8px 12px; border-bottom:1px solid #2a3d55; white-space:nowrap; }
.pg-td { font-size:12px; padding:9px 12px; border-bottom:1px solid rgba(42,61,85,.3); color:#e0e0e0; }
.pg-table tbody tr:last-child td { border-bottom:none; }

.tab-btn { font-size:11px; font-weight:600; padding:5px 16px; border-radius:20px; border:1px solid #2a3d55; cursor:pointer; color:#888; background:transparent; transition:all .2s; }
.tab-btn.active { background:#6366f1; border-color:#6366f1; color:#fff; }

.plano-badge { font-size:8px; font-weight:700; padding:2px 7px; border-radius:10px; }
.plano-1 { background:rgba(59,130,246,.15); color:#60a5fa; }
.plano-3 { background:rgba(168,85,247,.15); color:#c084fc; }
.plano-emp { background:rgba(251,146,60,.15); color:#fb923c; }

.tipo-clt      { background:rgba(34,197,94,.15);  color:#4ade80; }
.tipo-pj       { background:rgba(251,191,36,.12); color:#fbbf24; }
.tipo-parceiro { background:rgba(168,85,247,.15); color:#c084fc; }
</style>

<div style="padding:20px 24px; max-width:1300px; margin:0 auto;">

    {{-- ── Cabeçalho ── --}}
    <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
        <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; background:#1e1e1e; border:1px solid #2a3d55; color:#888; text-decoration:none; font-size:18px; flex-shrink:0; transition:color .2s;" onmouseover="this.style.color='#e0e0e0'" onmouseout="this.style.color='#888'">←</a>

        <div style="width:48px; height:48px; border-radius:50%; background:#1e3a5c; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:800; color:#60a5fa; flex-shrink:0;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <div>
            <div style="display:flex; align-items:center; gap:10px;">
                <h1 style="font-size:20px; font-weight:800; color:#e0e0e0; margin:0;">{{ $user->name }}</h1>
                <span style="font-size:9px; font-weight:700; padding:3px 9px; border-radius:20px;" class="tipo-{{ $user->tipo_contrato }}">{{ strtoupper($user->tipo_contrato) }}</span>
            </div>
            <p style="font-size:11px; color:#888; margin:2px 0 0;">{{ $user->email ?? '' }} · Perfil do corretor</p>
        </div>
    </div>

    {{-- ── KPI cards ── --}}
    <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:20px;">

        <div class="pg-card" style="padding:16px 18px;">
            <div class="pg-label">Contratos</div>
            <div class="pg-value" style="color:#e0e0e0;">{{ $kpis->total_contratos ?? 0 }}</div>
            <div class="pg-sub">Total de carteira</div>
        </div>

        <div class="pg-card" style="padding:16px 18px;">
            <div class="pg-label">Qtd Vidas</div>
            <div class="pg-value" style="color:#60a5fa;">{{ $kpis->qtd_vidas ?? 0 }}</div>
            <div class="pg-sub">Titular + dependentes</div>
        </div>

        <div class="pg-card" style="padding:16px 18px;">
            <div class="pg-label">Corretor · A Pagar</div>
            <div class="pg-value" style="color:#fbbf24;">R$ {{ number_format($kpis->valor_a_pagar ?? 0, 2, ',', '.') }}</div>
            <div class="pg-sub">Não finalizado</div>
        </div>

        <div class="pg-card" style="padding:16px 18px;">
            <div class="pg-label">Corretor · Pago</div>
            <div class="pg-value" style="color:#4ade80;">R$ {{ number_format($kpis->valor_pago ?? 0, 2, ',', '.') }}</div>
            <div class="pg-sub">Folhas finalizadas</div>
        </div>

        <div class="pg-card" style="padding:16px 18px;">
            <div class="pg-label">Corretora · A Receber</div>
            <div class="pg-value" style="color:#fbbf24;">R$ {{ number_format($kpis->corretora_a_pagar ?? 0, 2, ',', '.') }}</div>
            <div class="pg-sub">Não finalizado</div>
        </div>

        <div class="pg-card" style="padding:16px 18px; border-color:#1a3a5c; background:#0e1a28;">
            <div class="pg-label">Corretora · Recebido</div>
            <div class="pg-value" style="color:#34d399;">R$ {{ number_format($kpis->corretora_pago ?? 0, 2, ',', '.') }}</div>
            <div class="pg-sub">Comissão realizada</div>
        </div>

    </div>

    {{-- ── Gráfico ── --}}
    <div class="pg-card" style="margin-bottom:20px;">
        <div style="padding:14px 20px; border-bottom:1px solid #2a3d55; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#3d7ab5;">
            Evolução Mensal — Últimos 12 Meses (Recebido da Operadora)
        </div>
        <div style="padding:16px; height:240px;">
            <canvas id="chartCorretor"></canvas>
        </div>
    </div>

    {{-- ── Tabela de clientes ── --}}
    <div class="pg-card">
        <div style="padding:14px 20px; border-bottom:1px solid #2a3d55; display:flex; align-items:center; gap:10px;">
            <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#3d7ab5;">Carteira de Clientes</span>
            <div style="display:flex; gap:6px; margin-left:auto;">
                <button class="tab-btn active" onclick="trocarTab('individual')">Individual</button>
                <button class="tab-btn" onclick="trocarTab('coletivo')">Coletivo</button>
                <button class="tab-btn" onclick="trocarTab('empresarial')">Empresarial</button>
                <button class="tab-btn" onclick="trocarTab('todos')">Todos</button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="pg-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th class="pg-th" style="text-align:left;">Cadastro</th>
                        <th class="pg-th" style="text-align:left;">Cliente / Empresa</th>
                        <th class="pg-th" style="text-align:center;">Plano</th>
                        <th class="pg-th" style="text-align:right;">Vidas</th>
                        <th class="pg-th" style="text-align:right;">Valor Plano</th>
                        <th class="pg-th" style="text-align:right; border-left:1px solid #2a3d55; color:#fbbf24;">Corretor · A Pagar</th>
                        <th class="pg-th" style="text-align:right; color:#4ade80;">Corretor · Pago</th>
                        <th class="pg-th" style="text-align:right; border-left:1px solid #2a3d55; color:#fbbf24;">Corretora · A Receber</th>
                        <th class="pg-th" style="text-align:right; color:#34d399;">Corretora · Recebido</th>
                        <th class="pg-th" style="text-align:center;">Ver</th>
                    </tr>
                </thead>
                <tbody>

                @php
                    $linhas = collect();
                    foreach ($individual as $c) {
                        $c->tipo_modal = 'i';
                        $c->grupo = $c->plano_id == 1 ? 'individual' : 'coletivo';
                        $linhas->push($c);
                    }
                    foreach ($empresarial as $c) {
                        $c->tipo_modal = 'e';
                        $c->grupo = 'empresarial';
                        $linhas->push($c);
                    }
                    // Mais novo primeiro (vale tambem para a aba "Todos")
                    $linhas = $linhas->sortByDesc('cadastro')->values();
                @endphp

                @foreach($linhas as $c)
                <tr class="cliente-row plano-{{ $c->grupo }}" @if($c->grupo !== 'individual') style="display:none;" @endif>
                    <td class="pg-td" style="color:#888; white-space:nowrap;">{{ $c->cadastro ? \Carbon\Carbon::parse($c->cadastro)->format('d/m/Y') : '—' }}</td>
                    <td class="pg-td" style="font-weight:600;">{{ $c->nome }}</td>
                    <td class="pg-td" style="text-align:center;">
                        @if($c->grupo === 'individual')<span class="plano-badge plano-1">Individual</span>
                        @elseif($c->grupo === 'coletivo')<span class="plano-badge plano-3">Coletivo</span>
                        @else<span class="plano-badge plano-emp">Empresarial</span>@endif
                    </td>
                    <td class="pg-td" style="text-align:right; color:#888;">{{ $c->qtd_vidas ?: '—' }}</td>
                    <td class="pg-td" style="text-align:right; color:#888;">R$ {{ number_format($c->valor_plano, 2, ',', '.') }}</td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->corretor_a_pagar > 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->corretor_a_pagar > 0 ? 'R$ '.number_format($c->corretor_a_pagar, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->corretor_pago > 0 ? '#4ade80' : '#444' }};">
                        {{ $c->corretor_pago > 0 ? 'R$ '.number_format($c->corretor_pago, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->corretora_a_receber > 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->corretora_a_receber > 0 ? 'R$ '.number_format($c->corretora_a_receber, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->corretora_recebido > 0 ? '#34d399' : '#444' }};">
                        {{ $c->corretora_recebido > 0 ? 'R$ '.number_format($c->corretora_recebido, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:center;">
                        <button class="ver-parcelas" title="Ver parcelas" data-tipo="{{ $c->tipo_modal }}" data-id="{{ $c->contrato_id }}" data-nome="{{ $c->nome }}"
                                style="background:none; border:none; cursor:pointer; color:#60a5fa; padding:2px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach

                @if($individual->isEmpty() && $empresarial->isEmpty())
                <tr><td colspan="10" style="padding:30px; text-align:center; color:#888; font-size:13px;">Nenhum cliente encontrado</td></tr>
                @endif

                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Modal de parcelas ── --}}
<div id="modal-parcelas" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.7); align-items:center; justify-content:center; padding:16px;">
    <div style="width:100%; max-width:680px; border-radius:12px; overflow:hidden; background:#1e1e1e; border:1px solid #2a3d55; box-shadow:0 20px 60px rgba(0,0,0,.5);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 20px; background:#0e1a28; border-bottom:1px solid #2a3d55;">
            <h5 id="modal-titulo" style="margin:0; font-size:13px; font-weight:700; color:#e0e0e0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; padding-right:12px;">Parcelas</h5>
            <button id="fechar-modal" style="background:none; border:none; font-size:22px; color:#888; cursor:pointer; line-height:1;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#888'">&times;</button>
        </div>
        <div style="padding:14px 18px; max-height:70vh; overflow-y:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th class="pg-th" style="text-align:left;">Parcela</th>
                        <th class="pg-th" style="text-align:left;">Vencimento</th>
                        <th class="pg-th" style="text-align:center;">Cliente Pagou</th>
                        <th class="pg-th" style="text-align:right; color:#fbbf24;">Corretor</th>
                        <th class="pg-th" style="text-align:right; color:#34d399;">Corretora</th>
                    </tr>
                </thead>
                <tbody id="modal-corpo"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
const labelsG      = @json($labelsGrafico);
const individualG  = @json($individualGrafico);
const coletivoG    = @json($coletivoGrafico);
const empresarialG = @json($empresarialGrafico);

new Chart(document.getElementById('chartCorretor'), {
    type: 'line',
    data: {
        labels: labelsG,
        datasets: [
            { label:'Individual',  data:individualG,  borderColor:'#60a5fa', backgroundColor:'rgba(96,165,250,.07)',  tension:.35, pointRadius:3, borderWidth:2, fill:true },
            { label:'Coletivo',    data:coletivoG,    borderColor:'#c084fc', backgroundColor:'rgba(192,132,252,.06)', tension:.35, pointRadius:3, borderWidth:2, fill:true },
            { label:'Empresarial', data:empresarialG, borderColor:'#fb923c', backgroundColor:'rgba(251,146,60,.06)',  tension:.35, pointRadius:3, borderWidth:2, fill:true },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color:'#888', font:{ size:10 } } },
            tooltip: { callbacks: { label: c => ' R$ ' + c.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits:2 }) } }
        },
        scales: {
            x: { ticks:{ color:'#666', font:{size:9} }, grid:{ color:'rgba(42,61,85,.3)' } },
            y: { ticks:{ color:'#666', font:{size:9}, callback: v => 'R$ '+v.toLocaleString('pt-BR') }, grid:{ color:'rgba(42,61,85,.3)' } }
        }
    }
});

function trocarTab(tipo) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    document.querySelectorAll('.cliente-row').forEach(row => row.style.display = 'none');

    if (tipo === 'todos') {
        document.querySelectorAll('.cliente-row').forEach(row => row.style.display = '');
    } else {
        document.querySelectorAll('.plano-' + tipo).forEach(row => row.style.display = '');
    }

    // Mostrar mensagem se aba vazia
    const visíveis = document.querySelectorAll('.cliente-row:not([style*="display: none"]):not([style*="display:none"])');
    const semDados = document.getElementById('semDados');
    if (semDados) semDados.style.display = visíveis.length === 0 ? '' : 'none';
}

// ── Modal de parcelas ─────────────────────────────────────────────
const modalParcelas = document.getElementById('modal-parcelas');
const fmt = v => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 });

document.querySelectorAll('.ver-parcelas').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('modal-titulo').textContent = 'Parcelas — ' + btn.dataset.nome;
        const corpo = document.getElementById('modal-corpo');
        corpo.innerHTML = '<tr><td colspan="5" style="padding:24px; text-align:center; color:#888;">Carregando...</td></tr>';
        modalParcelas.style.display = 'flex';
        fetch(`{{ url('/dashboard/contrato') }}/${btn.dataset.tipo}/${btn.dataset.id}/parcelas`, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(j => {
                if (!j.success) { corpo.innerHTML = '<tr><td colspan="5" style="padding:24px; text-align:center; color:#f87171;">Erro ao carregar.</td></tr>'; return; }
                const totalCorretor  = j.parcelas.reduce((s, p) => s + p.valor_corretor, 0);
                const totalCorretora = j.parcelas.reduce((s, p) => s + p.valor_corretora, 0);
                corpo.innerHTML = j.parcelas.map(p => {
                    const cli = p.cliente_pagou
                        ? `<span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; background:rgba(34,197,94,.15); color:#4ade80;">Sim${p.data_baixa ? ' · ' + p.data_baixa : ''}</span>`
                        : '<span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; background:rgba(255,255,255,.06); color:#888;">Não</span>';
                    const cor = p.valor_corretor > 0
                        ? `<span style="color:${p.corretor_pago ? '#4ade80' : '#fbbf24'};">${fmt(p.valor_corretor)}</span><div style="font-size:9px; color:#666;">${p.corretor_pago ? 'pago' : 'a pagar'}</div>`
                        : '<span style="color:#444;">—</span>';
                    const cra = p.valor_corretora > 0
                        ? `<span style="color:${p.corretora_recebeu ? '#34d399' : '#fbbf24'};">${fmt(p.valor_corretora)}</span><div style="font-size:9px; color:#666;">${p.corretora_recebeu ? 'recebido' + (p.data_gerente ? ' · ' + p.data_gerente : '') : 'a receber'}</div>`
                        : '<span style="color:#444;">—</span>';
                    return `<tr>
                        <td class="pg-td" style="font-weight:600;">${p.rotulo}</td>
                        <td class="pg-td">${p.vencimento}</td>
                        <td class="pg-td" style="text-align:center;">${cli}</td>
                        <td class="pg-td" style="text-align:right;">${cor}</td>
                        <td class="pg-td" style="text-align:right;">${cra}</td>
                    </tr>`;
                }).join('') + `
                    <tr style="background:rgba(42,61,85,.25);">
                        <td class="pg-td" colspan="3" style="font-weight:800; text-transform:uppercase; font-size:11px; letter-spacing:.05em; color:#e0e0e0;">Total</td>
                        <td class="pg-td" style="text-align:right; font-weight:800; color:#fbbf24;">${fmt(totalCorretor)}<div style="font-size:9px; font-weight:600; color:#666;">corretor</div></td>
                        <td class="pg-td" style="text-align:right; font-weight:800; color:#34d399;">${fmt(totalCorretora)}<div style="font-size:9px; font-weight:600; color:#666;">corretora</div></td>
                    </tr>`;
            })
            .catch(() => { corpo.innerHTML = '<tr><td colspan="5" style="padding:24px; text-align:center; color:#f87171;">Erro ao carregar.</td></tr>'; });
    });
});

document.getElementById('fechar-modal').addEventListener('click', () => modalParcelas.style.display = 'none');
modalParcelas.addEventListener('click', e => { if (e.target === modalParcelas) modalParcelas.style.display = 'none'; });
</script>
</x-app-layout>
