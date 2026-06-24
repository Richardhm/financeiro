<x-app-layout>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

<style>
:root { --dk-bg:#111; --dk-card:#1a1a1a; --dk-card2:#1e1e1e; --dk-border:#2a3d55; --dk-text:#e0e0e0; --dk-muted:#888; --dk-blue:#3d7ab5; --dk-accent:#6366f1; }
body, .min-h-screen { background: var(--dk-bg) !important; color: var(--dk-text); }

.db-kpi { background:var(--dk-card2); border:1px solid var(--dk-border); border-radius:12px; padding:18px 20px; }
.db-kpi-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--dk-muted); margin-bottom:6px; }
.db-kpi-value { font-size:22px; font-weight:800; line-height:1; }

.db-section { background:var(--dk-card2); border:1px solid var(--dk-border); border-radius:12px; }
.db-section-header { padding:14px 20px; border-bottom:1px solid var(--dk-border); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--dk-blue); display:flex; align-items:center; gap:12px; }

.db-table th { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--dk-muted); padding:8px 12px; border-bottom:1px solid var(--dk-border); white-space:nowrap; }
.db-table td { font-size:12px; padding:9px 12px; border-bottom:1px solid rgba(42,61,85,.3); color:var(--dk-text); }
.db-table tr:last-child td { border-bottom:none; }
.db-table tbody tr { cursor:pointer; transition:background .15s; }
.db-table tbody tr:hover { background:rgba(99,102,241,.08); }

.tipo-badge { font-size:9px; font-weight:700; text-transform:uppercase; padding:2px 7px; border-radius:20px; }
.tipo-clt      { background:rgba(34,197,94,.15);  color:#4ade80; }
.tipo-pj       { background:rgba(251,191,36,.12); color:#fbbf24; }
.tipo-parceiro { background:rgba(168,85,247,.15); color:#c084fc; }

.filter-tab { font-size:11px; font-weight:600; padding:5px 14px; border-radius:20px; border:1px solid var(--dk-border); cursor:pointer; transition:all .2s; color:var(--dk-muted); background:transparent; }
.filter-tab.active, .filter-tab:hover { background:var(--dk-accent); border-color:var(--dk-accent); color:#fff; }

#modalDetalhe { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.7); align-items:center; justify-content:center; padding:16px; }
#modalDetalhe.open { display:flex; }
.modal-det-card { background:var(--dk-card2); border:1px solid var(--dk-border); border-radius:14px; width:100%; max-width:900px; max-height:88vh; display:flex; flex-direction:column; overflow:hidden; }
.modal-det-header { padding:14px 20px; border-bottom:1px solid var(--dk-border); background:#0e1a28; display:flex; justify-content:space-between; align-items:center; flex-shrink:0; }
.modal-det-body { overflow-y:auto; padding:20px; flex:1; }
.det-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px; }
.det-kpi { background:rgba(42,61,85,.2); border:1px solid rgba(42,61,85,.4); border-radius:8px; padding:10px 14px; }
.det-kpi label { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--dk-muted); display:block; margin-bottom:3px; }
.det-kpi span { font-size:15px; font-weight:800; }
.det-tab { font-size:10px; font-weight:700; text-transform:uppercase; padding:4px 12px; border-radius:6px; border:1px solid var(--dk-border); cursor:pointer; color:var(--dk-muted); background:transparent; transition:all .15s; }
.det-tab.active { background:var(--dk-accent); border-color:var(--dk-accent); color:#fff; }
.det-table th { font-size:9px; font-weight:700; text-transform:uppercase; color:var(--dk-muted); padding:6px 10px; border-bottom:1px solid var(--dk-border); }
.det-table td { font-size:11px; padding:7px 10px; border-bottom:1px solid rgba(42,61,85,.3); color:var(--dk-text); }
.det-table tr:last-child td { border-bottom:none; }
.plano-badge { font-size:8px; font-weight:700; padding:2px 6px; border-radius:10px; }
.plano-1 { background:rgba(59,130,246,.15); color:#60a5fa; }
.plano-3 { background:rgba(168,85,247,.15); color:#c084fc; }
</style>

<div style="padding:20px 24px; max-width:1400px; margin:0 auto;">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 style="font-size:20px; font-weight:800; color:#e0e0e0; margin:0;">Balanço Financeiro</h1>
            <p style="font-size:11px; color:#888; margin:3px 0 0;">Resumo consolidado de comissões · {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('dashboard.balanco') }}" style="font-size:11px;font-weight:600;color:#34d399;text-decoration:none;padding:6px 14px;border:1px solid #2a3d55;border-radius:8px;">↗ Balanço da Corretora</a>
            <a href="{{ route('financeiro.index') }}" style="font-size:11px;font-weight:600;color:#3d7ab5;text-decoration:none;padding:6px 14px;border:1px solid #2a3d55;border-radius:8px;">↗ Ver Financeiro</a>
        </div>
    </div>

    {{-- KPIs --}}
    <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:20px;">
        <div class="db-kpi">
            <div class="db-kpi-label">Recebido da Operadora</div>
            <div class="db-kpi-value" style="color:#4ade80;">R$ {{ number_format($kpis['recebido_operadora'],2,',','.') }}</div>
            <div style="font-size:9px;color:#888;margin-top:4px;">Confirmado operadora</div>
        </div>
        <div class="db-kpi">
            <div class="db-kpi-label">Pago aos Corretores</div>
            <div class="db-kpi-value" style="color:#60a5fa;">R$ {{ number_format($kpis['pago_corretores'],2,',','.') }}</div>
            <div style="font-size:9px;color:#888;margin-top:4px;">Folhas finalizadas</div>
        </div>
        <div class="db-kpi">
            <div class="db-kpi-label">A Pagar Corretores</div>
            <div class="db-kpi-value" style="color:#fbbf24;">R$ {{ number_format($kpis['a_pagar_corretores'],2,',','.') }}</div>
            <div style="font-size:9px;color:#888;margin-top:4px;">Aguardando folha</div>
        </div>
        <div class="db-kpi" style="border-color:#7f1d1d;">
            <div class="db-kpi-label">Adiantamentos Expostos</div>
            <div class="db-kpi-value" style="color:#f87171;">R$ {{ number_format($kpis['adiantados_expostos'],2,',','.') }}</div>
            <div style="font-size:9px;color:#888;margin-top:4px;">Pago sem cobertura</div>
        </div>
        <div class="db-kpi">
            <div class="db-kpi-label">Comissões Futuras</div>
            <div class="db-kpi-value" style="color:#c084fc;">R$ {{ number_format($kpis['futuras'],2,',','.') }}</div>
            <div style="font-size:9px;color:#888;margin-top:4px;">Parcelas não quitadas</div>
        </div>
        <div class="db-kpi" style="border-color:#1a3a5c;background:#0e1a28;">
            <div class="db-kpi-label">Comissão da Corretora</div>
            <div class="db-kpi-value" style="color:#34d399;">R$ {{ number_format($kpis['comissao_corretora'],2,',','.') }}</div>
            <div style="font-size:9px;color:#888;margin-top:4px;">Bruto operadora − repasse corretores</div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px;">
        <div class="db-section">
            <div class="db-section-header">Evolução Mensal — Últimos 12 Meses</div>
            <div style="padding:16px; height:230px;">
                <canvas id="chartMensal"></canvas>
            </div>
        </div>
        <div class="db-section">
            <div class="db-section-header">Distribuição por Tipo</div>
            <div style="padding:16px; height:230px; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartPizza"></canvas>
            </div>
        </div>
    </div>

    {{-- Ranking --}}
    <div class="db-section">
        <div class="db-section-header">
            <span>Ranking de Corretores</span>
            <div style="display:flex; gap:6px; margin-left:auto;">
                <button class="filter-tab active" data-tipo="todos">Todos</button>
                <button class="filter-tab" data-tipo="clt">CLT</button>
                <button class="filter-tab" data-tipo="pj">PJ</button>
                <button class="filter-tab" data-tipo="parceiro">Parceiro</button>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="db-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align:left;">#</th>
                        <th rowspan="2" style="text-align:left;">Corretor</th>
                        <th rowspan="2" style="text-align:center;">Tipo</th>
                        <th rowspan="2" style="text-align:right;">Contratos</th>
                        <th rowspan="2" style="text-align:right;">Qtd Vidas</th>
                        <th colspan="2" style="text-align:center;border-left:1px solid #2a3d55;padding-bottom:4px;color:#60a5fa;">Corretor</th>
                        <th colspan="2" style="text-align:center;border-left:1px solid #2a3d55;padding-bottom:4px;color:#34d399;">Corretora</th>
                    </tr>
                    <tr>
                        <th style="text-align:right;border-left:1px solid #2a3d55;color:#fbbf24;">A PAGAR</th>
                        <th style="text-align:right;color:#60a5fa;">PAGO</th>
                        <th style="text-align:right;border-left:1px solid #2a3d55;color:#fbbf24;">A RECEBER</th>
                        <th style="text-align:right;color:#34d399;">RECEBIDO</th>
                    </tr>
                </thead>
                <tbody id="rankingBody">
                @foreach($ranking as $i => $r)
                <tr class="ranking-row" data-tipo="{{ $r->tipo_contrato }}" onclick="window.location.href='{{ route('dashboard.corretor.perfil', $r->id) }}'" style="cursor:pointer;">
                    <td style="color:#888;font-size:11px;">{{ $i+1 }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:26px;height:26px;border-radius:50%;background:#1e3a5c;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#60a5fa;flex-shrink:0;">{{ strtoupper(substr($r->name,0,1)) }}</div>
                            <span style="font-weight:600;">{{ $r->name }}</span>
                        </div>
                    </td>
                    <td style="text-align:center;"><span class="tipo-badge tipo-{{ $r->tipo_contrato }}">{{ strtoupper($r->tipo_contrato) }}</span></td>
                    <td style="text-align:right;">{{ $r->total_contratos }}</td>
                    <td style="text-align:right;color:#888;">{{ $r->qtd_vidas }}</td>
                    <td style="text-align:right;color:#fbbf24;border-left:1px solid #2a3d55;">R$ {{ number_format($r->valor_a_pagar,2,',','.') }}</td>
                    <td style="text-align:right;color:#60a5fa;">R$ {{ number_format($r->valor_pago,2,',','.') }}</td>
                    <td style="text-align:right;color:#fbbf24;border-left:1px solid #2a3d55;">R$ {{ number_format($r->corretora_a_pagar,2,',','.') }}</td>
                    <td style="text-align:right;color:#34d399;">R$ {{ number_format($r->corretora_pago,2,',','.') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal Detalhe Corretor --}}
<div id="modalDetalhe" onclick="fecharFora(event)">
    <div class="modal-det-card">
        <div class="modal-det-header">
            <div>
                <div id="detNome" style="font-size:14px;font-weight:800;color:#e0e0e0;"></div>
                <div id="detTipo" style="font-size:10px;color:#888;margin-top:2px;"></div>
            </div>
            <button onclick="fecharDetalhe()" style="background:none;border:none;color:#888;font-size:22px;cursor:pointer;line-height:1;">&times;</button>
        </div>
        <div class="modal-det-body">
            <div id="detLoading" style="text-align:center;padding:40px;color:#888;">Carregando...</div>
            <div id="detConteudo" style="display:none;">
                <div class="det-grid" id="detResumo"></div>
                <div style="display:flex;gap:6px;margin-bottom:12px;">
                    <button class="det-tab active" onclick="trocarAba('individual')">Individual / Coletivo</button>
                    <button class="det-tab" onclick="trocarAba('empresarial')">Empresarial</button>
                    <button class="det-tab" onclick="trocarAba('historico')">Histórico</button>
                </div>
                <div id="abaIndividual">
                    <table class="det-table" style="width:100%;border-collapse:collapse;">
                        <thead><tr>
                            <th style="text-align:left;">Cliente</th>
                            <th style="text-align:center;">Plano</th>
                            <th style="text-align:right;">Vlr Plano</th>
                            <th style="text-align:right;">Pago</th>
                            <th style="text-align:right;">A Pagar</th>
                            <th style="text-align:right;">Futuras</th>
                        </tr></thead>
                        <tbody id="tbIndividual"></tbody>
                    </table>
                    <div id="semIndividual" style="display:none;padding:20px;text-align:center;color:#888;font-size:12px;">Nenhum contrato individual/coletivo</div>
                </div>
                <div id="abaEmpresarial" style="display:none;">
                    <table class="det-table" style="width:100%;border-collapse:collapse;">
                        <thead><tr>
                            <th style="text-align:left;">Empresa</th>
                            <th style="text-align:right;">Vlr Plano</th>
                            <th style="text-align:right;">Pago</th>
                            <th style="text-align:right;">A Pagar</th>
                            <th style="text-align:right;">Futuras</th>
                        </tr></thead>
                        <tbody id="tbEmpresarial"></tbody>
                    </table>
                    <div id="semEmpresarial" style="display:none;padding:20px;text-align:center;color:#888;font-size:12px;">Nenhum contrato empresarial</div>
                </div>
                <div id="abaHistorico" style="display:none;">
                    <table class="det-table" style="width:100%;border-collapse:collapse;">
                        <thead><tr>
                            <th style="text-align:left;">Mês</th>
                            <th style="text-align:right;">Recebido Operadora</th>
                            <th style="text-align:right;">Comissão Corretor</th>
                        </tr></thead>
                        <tbody id="tbHistorico"></tbody>
                    </table>
                    <div id="semHistorico" style="display:none;padding:20px;text-align:center;color:#888;font-size:12px;">Nenhum histórico</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const labelsG       = @json($labelsGrafico);
const individualG   = @json($individualGrafico);
const coletivoG     = @json($coletivoGrafico);
const empresarialG  = @json($empresarialGrafico);
const pizzaD        = @json($pizza);
const rotaDet       = '{{ route("dashboard.corretor.detalhe", ":id") }}';

// Chart linha — evolução mensal por tipo de plano
new Chart(document.getElementById('chartMensal'), {
    type:'line',
    data:{
        labels:labelsG,
        datasets:[
            {label:'Individual',  data:individualG,  borderColor:'#60a5fa', backgroundColor:'rgba(96,165,250,.07)',  tension:.35, pointRadius:3, borderWidth:2, fill:true},
            {label:'Coletivo',    data:coletivoG,    borderColor:'#c084fc', backgroundColor:'rgba(192,132,252,.06)', tension:.35, pointRadius:3, borderWidth:2, fill:true},
            {label:'Empresarial', data:empresarialG, borderColor:'#fb923c', backgroundColor:'rgba(251,146,60,.06)',  tension:.35, pointRadius:3, borderWidth:2, fill:true},
        ]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{labels:{color:'#888',font:{size:10}}},
            tooltip:{callbacks:{label:c=>' R$ '+c.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2})}}
        },
        scales:{
            x:{ticks:{color:'#666',font:{size:9}},grid:{color:'rgba(42,61,85,.3)'}},
            y:{ticks:{color:'#666',font:{size:9},callback:v=>'R$ '+v.toLocaleString('pt-BR')},grid:{color:'rgba(42,61,85,.3)'}}
        }
    }
});

// Chart pizza
const tLabel = {clt:'CLT',pj:'PJ',parceiro:'Parceiro'};
const tColor = {clt:'#4ade80',pj:'#fbbf24',parceiro:'#c084fc'};
new Chart(document.getElementById('chartPizza'), {
    type:'doughnut',
    data:{
        labels:pizzaD.map(p=>(tLabel[p.tipo_contrato]||'N/D')+' ('+p.vendedores+')'),
        datasets:[{data:pizzaD.map(p=>parseFloat(p.total||0)), backgroundColor:pizzaD.map(p=>tColor[p.tipo_contrato]||'#888'), borderWidth:0, hoverOffset:4}]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{position:'bottom',labels:{color:'#888',font:{size:10},padding:10}},
            tooltip:{callbacks:{label:c=>' R$ '+parseFloat(c.parsed).toLocaleString('pt-BR',{minimumFractionDigits:2})}}
        },
        cutout:'62%'
    }
});

// Filtro
document.querySelectorAll('.filter-tab').forEach(btn=>{
    btn.addEventListener('click',function(){
        document.querySelectorAll('.filter-tab').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        const tipo=this.dataset.tipo;
        document.querySelectorAll('.ranking-row').forEach(row=>{
            row.style.display=(tipo==='todos'||row.dataset.tipo===tipo)?'':'none';
        });
    });
});

// Modal
const fmt = v=>'R$ '+parseFloat(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2});

function abrirDetalhe(id){
    document.getElementById('modalDetalhe').classList.add('open');
    document.getElementById('detLoading').style.display='block';
    document.getElementById('detConteudo').style.display='none';
    fetch(rotaDet.replace(':id',id),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json())
    .then(data=>{
        document.getElementById('detNome').textContent=data.user.name;
        document.getElementById('detTipo').textContent=(data.user.tipo_contrato||'').toUpperCase()+' · '+data.resumo.total_contratos+' contratos';
        const r=data.resumo;
        document.getElementById('detResumo').innerHTML=`
            <div class="det-kpi"><label>Total Gerado</label><span style="color:#e0e0e0;">${fmt(r.total_comissao)}</span></div>
            <div class="det-kpi"><label>Pago ao Corretor</label><span style="color:#60a5fa;">${fmt(r.total_pago)}</span></div>
            <div class="det-kpi"><label>A Pagar</label><span style="color:#fbbf24;">${fmt(r.a_pagar)}</span></div>
            <div class="det-kpi"><label>Adiantado s/ Cobertura</label><span style="color:#f87171;">${fmt(r.adiantado)}</span></div>
            <div class="det-kpi"><label>Futuras</label><span style="color:#c084fc;">${fmt(r.futuras)}</span></div>
            <div class="det-kpi" style="background:rgba(26,58,92,.3);"><label>Comissão Corretora</label><span style="color:#34d399;">${fmt(r.comissao_corretora)}</span></div>
        `;
        const plNome={1:'Individual',3:'Coletivo'};
        const plCls ={1:'plano-1',3:'plano-3'};
        document.getElementById('tbIndividual').innerHTML=data.individual.map(c=>`
            <tr><td>${c.nome}</td>
            <td style="text-align:center;"><span class="plano-badge ${plCls[c.plano_id]||'plano-1'}">${plNome[c.plano_id]||'Plano '+c.plano_id}</span></td>
            <td style="text-align:right;color:#888;">${fmt(c.valor_plano)}</td>
            <td style="text-align:right;color:#60a5fa;">${fmt(c.pago)}</td>
            <td style="text-align:right;color:#fbbf24;">${fmt(c.a_pagar)}</td>
            <td style="text-align:right;color:#c084fc;">${fmt(c.futuras)}</td></tr>
        `).join('');
        document.getElementById('semIndividual').style.display=data.individual.length?'none':'block';
        document.getElementById('tbEmpresarial').innerHTML=data.empresarial.map(c=>`
            <tr><td><b>${c.nome}</b></td>
            <td style="text-align:right;color:#888;">${fmt(c.valor_plano)}</td>
            <td style="text-align:right;color:#60a5fa;">${fmt(c.pago)}</td>
            <td style="text-align:right;color:#fbbf24;">${fmt(c.a_pagar)}</td>
            <td style="text-align:right;color:#c084fc;">${fmt(c.futuras)}</td></tr>
        `).join('');
        document.getElementById('semEmpresarial').style.display=data.empresarial.length?'none':'block';
        document.getElementById('tbHistorico').innerHTML=data.historico.map(h=>`
            <tr><td>${h.mes}</td>
            <td style="text-align:right;color:#4ade80;">${fmt(h.recebido)}</td>
            <td style="text-align:right;color:#60a5fa;">${fmt(h.comissao)}</td></tr>
        `).join('');
        document.getElementById('semHistorico').style.display=data.historico.length?'none':'block';
        trocarAba('individual');
        document.getElementById('detLoading').style.display='none';
        document.getElementById('detConteudo').style.display='block';
    })
    .catch(()=>{ document.getElementById('detLoading').textContent='Erro ao carregar.'; });
}

function fecharDetalhe(){ document.getElementById('modalDetalhe').classList.remove('open'); }
function fecharFora(e){ if(e.target===document.getElementById('modalDetalhe')) fecharDetalhe(); }

function trocarAba(aba){
    ['individual','empresarial','historico'].forEach(a=>{
        document.getElementById('aba'+a.charAt(0).toUpperCase()+a.slice(1)).style.display=a===aba?'block':'none';
    });
    document.querySelectorAll('.det-tab').forEach((b,i)=>{
        b.classList.toggle('active',['individual','empresarial','historico'][i]===aba);
    });
}
</script>
</x-app-layout>
