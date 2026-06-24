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
                        <th class="pg-th" style="text-align:left;">Cliente / Empresa</th>
                        <th class="pg-th" style="text-align:center;">Plano</th>
                        <th class="pg-th" style="text-align:right;">Vidas</th>
                        <th class="pg-th" style="text-align:right;">Vlr Plano</th>
                        <th class="pg-th" style="text-align:center;">Parcela</th>
                        <th class="pg-th" style="text-align:right; border-left:1px solid #2a3d55; color:#fbbf24;">Corretor · A Pagar</th>
                        <th class="pg-th" style="text-align:right; color:#4ade80;">Corretor · Pago</th>
                        <th class="pg-th" style="text-align:right; border-left:1px solid #2a3d55; color:#fbbf24;">Corretora · A Receber</th>
                        <th class="pg-th" style="text-align:right; color:#34d399;">Corretora · Recebido</th>
                    </tr>
                </thead>
                <tbody>

                {{-- Individual (plano_id=1) --}}
                @foreach($individual as $c)
                @if($c->plano_id == 1)
                <tr class="cliente-row plano-individual">
                    <td class="pg-td" style="font-weight:600;">{{ $c->nome }}</td>
                    <td class="pg-td" style="text-align:center;"><span class="plano-badge plano-1">Individual</span></td>
                    <td class="pg-td" style="text-align:right; color:#888;">{{ $c->qtd_vidas }}</td>
                    <td class="pg-td" style="text-align:right; color:#888;">R$ {{ number_format($c->valor_plano, 2, ',', '.') }}</td>
                    <td class="pg-td" style="text-align:center; color:#888; font-size:11px;">{{ $c->parcela }}ª</td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->status_gerente == 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->finalizado == 0 ? 'R$ '.number_format($c->valor, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->finalizado == 1 ? '#4ade80' : '#444' }};">
                        {{ $c->finalizado == 1 ? 'R$ '.number_format($c->valor, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->status_gerente == 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->status_gerente == 0 ? 'R$ '.number_format($c->valor_corretora, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->status_gerente == 1 ? '#34d399' : '#444' }};">
                        {{ $c->status_gerente == 1 ? 'R$ '.number_format($c->valor_corretora, 2, ',', '.') : '—' }}
                    </td>
                </tr>
                @endif
                @endforeach

                {{-- Coletivo (plano_id=3) --}}
                @foreach($individual as $c)
                @if($c->plano_id == 3)
                <tr class="cliente-row plano-coletivo" style="display:none;">
                    <td class="pg-td" style="font-weight:600;">{{ $c->nome }}</td>
                    <td class="pg-td" style="text-align:center;"><span class="plano-badge plano-3">Coletivo</span></td>
                    <td class="pg-td" style="text-align:right; color:#888;">{{ $c->qtd_vidas }}</td>
                    <td class="pg-td" style="text-align:right; color:#888;">R$ {{ number_format($c->valor_plano, 2, ',', '.') }}</td>
                    <td class="pg-td" style="text-align:center; color:#888; font-size:11px;">{{ $c->parcela }}ª</td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->status_gerente == 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->finalizado == 0 ? 'R$ '.number_format($c->valor, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->finalizado == 1 ? '#4ade80' : '#444' }};">
                        {{ $c->finalizado == 1 ? 'R$ '.number_format($c->valor, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->status_gerente == 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->status_gerente == 0 ? 'R$ '.number_format($c->valor_corretora, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->status_gerente == 1 ? '#34d399' : '#444' }};">
                        {{ $c->status_gerente == 1 ? 'R$ '.number_format($c->valor_corretora, 2, ',', '.') : '—' }}
                    </td>
                </tr>
                @endif
                @endforeach

                {{-- Empresarial --}}
                @foreach($empresarial as $c)
                <tr class="cliente-row plano-empresarial" style="display:none;">
                    <td class="pg-td" style="font-weight:600;">{{ $c->nome }}</td>
                    <td class="pg-td" style="text-align:center;"><span class="plano-badge plano-emp">Empresarial</span></td>
                    <td class="pg-td" style="text-align:right; color:#888;">—</td>
                    <td class="pg-td" style="text-align:right; color:#888;">R$ {{ number_format($c->valor_plano, 2, ',', '.') }}</td>
                    <td class="pg-td" style="text-align:center; color:#888; font-size:11px;">{{ $c->parcela }}ª</td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->status_gerente == 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->finalizado == 0 ? 'R$ '.number_format($c->valor, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->finalizado == 1 ? '#4ade80' : '#444' }};">
                        {{ $c->finalizado == 1 ? 'R$ '.number_format($c->valor, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; border-left:1px solid rgba(42,61,85,.3); color:{{ $c->status_gerente == 0 ? '#fbbf24' : '#444' }};">
                        {{ $c->status_gerente == 0 ? 'R$ '.number_format($c->valor_corretora, 2, ',', '.') : '—' }}
                    </td>
                    <td class="pg-td" style="text-align:right; color:{{ $c->status_gerente == 1 ? '#34d399' : '#444' }};">
                        {{ $c->status_gerente == 1 ? 'R$ '.number_format($c->valor_corretora, 2, ',', '.') : '—' }}
                    </td>
                </tr>
                @endforeach

                @if($individual->isEmpty() && $empresarial->isEmpty())
                <tr><td colspan="9" style="padding:30px; text-align:center; color:#888; font-size:13px;">Nenhuma parcela encontrada</td></tr>
                @endif

                </tbody>
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
</script>
</x-app-layout>
