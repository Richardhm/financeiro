<x-app-layout>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

<style>
body, .min-h-screen { background:#111 !important; color:#e0e0e0; }
.bl-card  { background:#1e1e1e; border:1px solid #2a3d55; border-radius:12px; }
.bl-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#888; margin-bottom:5px; }
.bl-value { font-size:22px; font-weight:800; line-height:1.1; }
.bl-sub   { font-size:9px; color:#555; margin-top:4px; }
.bl-sec-header { padding:13px 18px; border-bottom:1px solid #2a3d55; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#3d7ab5; display:flex; align-items:center; gap:10px; }
.bl-th { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#888; padding:8px 12px; border-bottom:1px solid #2a3d55; white-space:nowrap; }
.bl-td { font-size:12px; padding:9px 12px; border-bottom:1px solid rgba(42,61,85,.3); color:#e0e0e0; }
.bl-table tbody tr:last-child td { border-bottom:none; }
.bl-table tbody tr { transition:background .15s; }
.bl-table tbody tr:hover { background:rgba(99,102,241,.07); }
.tipo-clt      { background:rgba(34,197,94,.15);  color:#4ade80; }
.tipo-pj       { background:rgba(251,191,36,.12); color:#fbbf24; }
.tipo-parceiro { background:rgba(168,85,247,.15); color:#c084fc; }
.tipo-badge    { font-size:9px; font-weight:700; text-transform:uppercase; padding:2px 7px; border-radius:20px; }
.prod-card { background:#181818; border:1px solid #2a3d55; border-radius:10px; padding:16px 18px; }
.prod-bar-bg { background:#2a3d55; border-radius:4px; height:6px; margin-top:8px; }
.prod-bar-fill { height:6px; border-radius:4px; transition:width .4s; }
</style>

<div style="padding:20px 24px; max-width:1300px; margin:0 auto;">

    {{-- ── Cabeçalho ── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#1e1e1e;border:1px solid #2a3d55;color:#888;text-decoration:none;font-size:18px;transition:color .2s;" onmouseover="this.style.color='#e0e0e0'" onmouseout="this.style.color='#888'">←</a>
            <div>
                <h1 style="font-size:20px;font-weight:800;color:#e0e0e0;margin:0;">Balanço da Corretora</h1>
                <p style="font-size:11px;color:#888;margin:3px 0 0;">Receita, margem e posição por produto · {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" style="font-size:11px;font-weight:600;color:#3d7ab5;text-decoration:none;padding:6px 14px;border:1px solid #2a3d55;border-radius:8px;">↗ Ver Ranking</a>
    </div>

    {{-- ── KPIs ── --}}
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px;">
        <div class="bl-card" style="padding:16px 18px; border-color:#1a4a2e; background:#0d2318;">
            <div class="bl-label">Recebido</div>
            <div class="bl-value" style="color:#4ade80;">R$ {{ number_format($kpis['recebido'],2,',','.') }}</div>
            <div class="bl-sub">Comissão já realizada</div>
        </div>
        <div class="bl-card" style="padding:16px 18px;">
            <div class="bl-label">A Receber</div>
            <div class="bl-value" style="color:#fbbf24;">R$ {{ number_format($kpis['a_receber'],2,',','.') }}</div>
            <div class="bl-sub">Folhas pendentes</div>
        </div>
        <div class="bl-card" style="padding:16px 18px; border-color:#1a3a5c;">
            <div class="bl-label">Total Carteira</div>
            <div class="bl-value" style="color:#60a5fa;">R$ {{ number_format($kpis['total_carteira'],2,',','.') }}</div>
            <div class="bl-sub">Recebido + a receber</div>
        </div>
        <div class="bl-card" style="padding:16px 18px;">
            <div class="bl-label">Pago Corretores</div>
            <div class="bl-value" style="color:#c084fc;">R$ {{ number_format($kpis['pago_corretores'],2,',','.') }}</div>
            <div class="bl-sub">Folhas finalizadas</div>
        </div>
        <div class="bl-card" style="padding:16px 18px;">
            <div class="bl-label">A Pagar Corretores</div>
            <div class="bl-value" style="color:#fb923c;">R$ {{ number_format($kpis['a_pagar_corretores'],2,',','.') }}</div>
            <div class="bl-sub">Aguardando folha</div>
        </div>
        <div class="bl-card" style="padding:16px 18px; border-color:#1a4a2e; background:#0a1f10;">
            <div class="bl-label">Margem Realizada</div>
            <div class="bl-value" style="color:#34d399;">R$ {{ number_format($kpis['margem_realizada'],2,',','.') }}</div>
            <div class="bl-sub">Recebido − pago corretores</div>
        </div>
    </div>

    {{-- ── Por Produto ── --}}
    @php
        $produtos = [
            'Individual'  => ['cor' => '#60a5fa', 'icon' => '👤'],
            'Coletivo'    => ['cor' => '#c084fc', 'icon' => '👥'],
            'Empresarial' => ['cor' => '#fb923c', 'icon' => '🏢'],
        ];
        $totalCorretora = max($kpis['total_carteira'], 1);
    @endphp
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        @foreach($produtos as $nome => $cfg)
        @php $p = $porProduto[$nome] ?? null; @endphp
        <div class="prod-card">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <span style="font-size:16px;">{{ $cfg['icon'] }}</span>
                <span style="font-size:12px;font-weight:700;color:{{ $cfg['cor'] }};text-transform:uppercase;letter-spacing:.06em;">{{ $nome }}</span>
                <span style="margin-left:auto;font-size:10px;color:#666;">{{ $p->contratos ?? 0 }} contratos</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:6px;">
                <div>
                    <div style="font-size:9px;color:#888;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Recebido</div>
                    <div style="font-size:16px;font-weight:800;color:#4ade80;">R$ {{ number_format($p->recebido ?? 0,2,',','.') }}</div>
                </div>
                <div>
                    <div style="font-size:9px;color:#888;font-weight:700;text-transform:uppercase;margin-bottom:3px;">A Receber</div>
                    <div style="font-size:16px;font-weight:800;color:#fbbf24;">R$ {{ number_format($p->a_receber ?? 0,2,',','.') }}</div>
                </div>
            </div>
            <div class="prod-bar-bg">
                @php
                    $total = ($p->recebido ?? 0) + ($p->a_receber ?? 0);
                    $pct = $total > 0 ? round(($p->recebido ?? 0) / $total * 100) : 0;
                @endphp
                <div class="prod-bar-fill" style="width:{{ $pct }}%;background:{{ $cfg['cor'] }};"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:4px;">
                <span style="font-size:9px;color:#666;">{{ $pct }}% realizado</span>
                <span style="font-size:9px;color:#555;">Margem: R$ {{ number_format(($p->recebido ?? 0) - ($p->pago_corretor ?? 0),2,',','.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Gráficos ── --}}
    <div style="display:grid;grid-template-columns:3fr 2fr;gap:16px;margin-bottom:20px;">

        {{-- Linha por produto --}}
        <div class="bl-card">
            <div class="bl-sec-header">Receita Mensal por Produto — Últimos 12 Meses</div>
            <div style="padding:16px;height:230px;">
                <canvas id="chartProduto"></canvas>
            </div>
        </div>

        {{-- Linha corretora vs corretores --}}
        <div class="bl-card">
            <div class="bl-sec-header">Corretora vs Repasse Corretores</div>
            <div style="padding:16px;height:230px;">
                <canvas id="chartMargem"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Vendedores ── --}}
    <div class="bl-card">
        <div class="bl-sec-header">
            Vendedores — Contribuição para a Corretora
            <span style="margin-left:auto;font-size:10px;color:#555;">{{ $vendedores->count() }} vendedores</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="bl-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th class="bl-th" style="text-align:left;">#</th>
                        <th class="bl-th" style="text-align:left;">Vendedor</th>
                        <th class="bl-th" style="text-align:center;">Tipo</th>
                        <th class="bl-th" style="text-align:right;">Contratos</th>
                        <th class="bl-th" style="text-align:right;border-left:1px solid #2a3d55;color:#4ade80;">Corretora Recebido</th>
                        <th class="bl-th" style="text-align:right;color:#fbbf24;">Corretora A Receber</th>
                        <th class="bl-th" style="text-align:right;border-left:1px solid #2a3d55;color:#c084fc;">Corretor Pago</th>
                        <th class="bl-th" style="text-align:right;color:#fb923c;">Corretor A Pagar</th>
                        <th class="bl-th" style="text-align:right;border-left:1px solid #2a3d55;color:#34d399;">Margem Realizada</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($vendedores as $i => $v)
                <tr onclick="window.location.href='{{ route('dashboard.corretor.perfil', $v->id) }}'" style="cursor:pointer;">
                    <td class="bl-td" style="color:#555;font-size:11px;">{{ $i+1 }}</td>
                    <td class="bl-td">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:26px;height:26px;border-radius:50%;background:#1e3a5c;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#60a5fa;flex-shrink:0;">{{ strtoupper(substr($v->name,0,1)) }}</div>
                            <span style="font-weight:600;">{{ $v->name }}</span>
                        </div>
                    </td>
                    <td class="bl-td" style="text-align:center;"><span class="tipo-badge tipo-{{ $v->tipo_contrato }}">{{ strtoupper($v->tipo_contrato) }}</span></td>
                    <td class="bl-td" style="text-align:right;color:#888;">{{ $v->contratos }}</td>
                    <td class="bl-td" style="text-align:right;color:#4ade80;border-left:1px solid rgba(42,61,85,.3);">R$ {{ number_format($v->corretora_recebido,2,',','.') }}</td>
                    <td class="bl-td" style="text-align:right;color:#fbbf24;">R$ {{ number_format($v->corretora_a_receber,2,',','.') }}</td>
                    <td class="bl-td" style="text-align:right;color:#c084fc;border-left:1px solid rgba(42,61,85,.3);">R$ {{ number_format($v->corretor_pago,2,',','.') }}</td>
                    <td class="bl-td" style="text-align:right;color:#fb923c;">R$ {{ number_format($v->corretor_a_pagar,2,',','.') }}</td>
                    <td class="bl-td" style="text-align:right;font-weight:700;border-left:1px solid rgba(42,61,85,.3);color:{{ $v->margem_realizada >= 0 ? '#34d399' : '#f87171' }};">
                        R$ {{ number_format($v->margem_realizada,2,',','.') }}
                    </td>
                </tr>
                @endforeach
                @if($vendedores->isEmpty())
                <tr><td colspan="9" style="padding:30px;text-align:center;color:#888;font-size:13px;">Nenhum dado encontrado</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const labels   = @json($labelsG);
const indG     = @json($indG);
const colG     = @json($colG);
const empG     = @json($empG);
const totalG   = @json($totalG);
const corrG    = @json($corretorG);

// Gráfico por produto
new Chart(document.getElementById('chartProduto'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            { label:'Individual',  data:indG, borderColor:'#60a5fa', backgroundColor:'rgba(96,165,250,.07)',  tension:.35, pointRadius:3, borderWidth:2, fill:true },
            { label:'Coletivo',    data:colG, borderColor:'#c084fc', backgroundColor:'rgba(192,132,252,.06)', tension:.35, pointRadius:3, borderWidth:2, fill:true },
            { label:'Empresarial', data:empG, borderColor:'#fb923c', backgroundColor:'rgba(251,146,60,.06)',  tension:.35, pointRadius:3, borderWidth:2, fill:true },
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{labels:{color:'#888',font:{size:10}}}, tooltip:{callbacks:{label:c=>' R$ '+c.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2})}} },
        scales:{ x:{ticks:{color:'#666',font:{size:9}},grid:{color:'rgba(42,61,85,.3)'}}, y:{ticks:{color:'#666',font:{size:9},callback:v=>'R$ '+v.toLocaleString('pt-BR')},grid:{color:'rgba(42,61,85,.3)'}} }
    }
});

// Gráfico corretora vs repasse
new Chart(document.getElementById('chartMargem'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label:'Receita Corretora', data:totalG, backgroundColor:'rgba(52,211,153,.25)', borderColor:'#34d399', borderWidth:1.5, borderRadius:3 },
            { label:'Repasse Corretores', data:corrG, backgroundColor:'rgba(192,132,252,.2)', borderColor:'#c084fc', borderWidth:1.5, borderRadius:3 },
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{labels:{color:'#888',font:{size:10}}}, tooltip:{callbacks:{label:c=>' R$ '+c.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2})}} },
        scales:{
            x:{ ticks:{color:'#666',font:{size:9}}, grid:{color:'rgba(42,61,85,.3)'} },
            y:{ ticks:{color:'#666',font:{size:9},callback:v=>'R$ '+v.toLocaleString('pt-BR')}, grid:{color:'rgba(42,61,85,.3)'} }
        }
    }
});
</script>
</x-app-layout>
