<x-app-layout>
@section('css')
<link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
<style>
    .pdf-upload-wrap { max-width: 980px; margin: 30px auto; }
    .card-escuro { background: #1e2230; border-radius: 10px; padding: 28px; color: #e0e6f0; }
    .card-escuro h2 { font-size: 1.15rem; margin-bottom: 20px; color: #7eb8f7; border-bottom: 1px solid #2e3550; padding-bottom: 10px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 0.82rem; color: #8a9bbb; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 9px 12px; border: 1px solid #2e3550;
        border-radius: 6px; background: #252a3a; color: #e0e6f0; font-size: 0.9rem; box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #4e7ab5; }
    .form-group input[readonly] { opacity: 0.55; cursor: not-allowed; }
    .form-row { display: grid; gap: 12px; }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-row.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
    .form-row.cols-5 { grid-template-columns: 1fr 1fr 1fr 1fr 1fr; }
    .section-title { font-size: 0.92rem; color: #7eb8f7; margin: 22px 0 10px; font-weight: 600; border-left: 3px solid #3b6fd4; padding-left: 10px; }
    .btn-upload  { background: #3b6fd4; color: #fff; border: none; padding: 10px 28px; border-radius: 7px; cursor: pointer; font-size: 0.95rem; }
    .btn-upload:hover  { background: #2c58b5; }
    .btn-upload:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn-salvar  { background: #27ae60; color: #fff; border: none; padding: 11px 32px; border-radius: 7px; cursor: pointer; font-size: 1rem; }
    .btn-salvar:hover  { background: #1e9950; }
    .btn-salvar:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn-back    { background: transparent; color: #8a9bbb; border: 1px solid #2e3550; padding: 10px 22px; border-radius: 7px; cursor: pointer; font-size: 0.9rem; margin-right: 8px; }
    .alert-box   { padding: 12px 16px; border-radius: 7px; margin-bottom: 16px; font-size: 0.9rem; }
    .alert-danger  { background: #3a1a1a; border: 1px solid #c0392b; color: #e74c3c; }
    .alert-success { background: #1a3a22; border: 1px solid #27ae60; color: #2ecc71; }
    .alert-info    { background: #1a2640; border: 1px solid #3b6fd4; color: #7eb8f7; }
    #preview-section { display: none; }
    .file-drop {
        border: 2px dashed #2e3550; border-radius: 10px; padding: 36px;
        text-align: center; cursor: pointer; color: #8a9bbb; transition: border-color 0.2s;
        margin-top: 18px;
    }
    .file-drop:hover, .file-drop.dragover { border-color: #3b6fd4; color: #7eb8f7; }
    .spinner { display: inline-block; width: 16px; height: 16px; border: 3px solid #3b6fd4; border-top-color: transparent; border-radius: 50%; animation: spin 0.7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .vendedor-found    { color: #2ecc71; font-size: 0.82rem; margin-top: 4px; }
    .vendedor-notfound { color: #e74c3c; font-size: 0.82rem; margin-top: 4px; }
    .divider { border: none; border-top: 1px solid #2e3550; margin: 22px 0; }
    .tag-manual { display:inline-block; background:#2a1e10; border:1px solid #e67e22; color:#f39c12; padding:2px 8px; border-radius:5px; font-size:0.75rem; margin-left:6px; }
    .tag-auto   { display:inline-block; background:#1a2640; border:1px solid #3b6fd4; color:#7eb8f7; padding:2px 8px; border-radius:5px; font-size:0.75rem; margin-left:6px; }

    /* Beneficiários table */
    .benef-table { width:100%; border-collapse:collapse; margin-top:8px; font-size:0.82rem; }
    .benef-table th { background:#252a3a; color:#8a9bbb; padding:8px 6px; text-align:left; border-bottom:1px solid #2e3550; }
    .benef-table td { padding:6px; border-bottom:1px solid #1a1f2e; color:#e0e6f0; }
    .benef-table input { background:#1e2230; border:1px solid #2e3550; color:#e0e6f0; padding:5px 8px; border-radius:4px; width:100%; box-sizing:border-box; }
    .tag-T { background:#1a3040; color:#4fc3f7; padding:2px 6px; border-radius:4px; font-size:0.78rem; }
    .tag-D { background:#2d1f3a; color:#b07fe0; padding:2px 6px; border-radius:4px; font-size:0.78rem; }
    .btn-add-benef { background:#1e2a38; color:#7eb8f7; border:1px solid #3b6fd4; border-radius:6px; padding:7px 16px; cursor:pointer; font-size:0.85rem; margin-top:8px; }
    .btn-rem-benef { background:#3a1a1a; color:#e74c3c; border:none; border-radius:4px; padding:5px 9px; cursor:pointer; }
</style>
@endsection

<div class="pdf-upload-wrap">
<div class="card-escuro">
<h2>
    <svg style="width:18px;vertical-align:middle;margin-right:6px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
    Cadastrar Contrato Empresarial via PDF (Hapvida)
</h2>

<div id="msg-area"></div>

{{-- ===================== ETAPA 1: UPLOAD ===================== --}}
<div id="upload-section">
    <div style="background:#252a3a;border-radius:8px;padding:18px;margin-bottom:20px;border:1px solid #2e3550;">
        <div style="font-size:0.78rem;color:#8a9bbb;margin-bottom:12px;text-transform:uppercase;letter-spacing:.04em;">Upload da Proposta Hapvida (Coletivo Empresarial)</div>
        <div class="file-drop" id="file-drop-area" onclick="document.getElementById('pdf-input').click()">
            <svg style="width:36px;margin-bottom:8px;opacity:0.4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            <p id="drop-label">Clique ou arraste o PDF da proposta aqui</p>
            <p style="font-size:0.78rem;margin-top:4px;opacity:0.5">Apenas .pdf — máx. 30MB</p>
        </div>
        <input type="file" id="pdf-input" accept=".pdf" style="display:none">
    </div>
    <div style="text-align:right;margin-top:8px">
        <a href="{{ route('financeiro.index') }}?ac=empresarial" class="btn-back">Voltar</a>
        <button class="btn-upload" id="btn-processar" onclick="processarPdf()">Processar PDF</button>
    </div>
</div>

{{-- ===================== ETAPA 2: PREVIEW ===================== --}}
<form id="preview-section" onsubmit="salvarContrato(event)">
    @csrf
    <input type="hidden" name="pdf_token" id="h-pdf-token">

    <div class="alert-box alert-info" id="info-fonte">PDF processado. Revise e complete os campos antes de salvar.</div>

    {{-- CORRETOR --}}
    <p class="section-title">Corretor <span class="tag-auto">auto</span></p>
    <div class="form-row cols-2">
        <div class="form-group">
            <label>Código Vendedor (PDF)</label>
            <input type="text" id="disp-codigo-vendedor" readonly>
            <span id="vendedor-status"></span>
        </div>
        <div class="form-group">
            <label>Corretor <span style="color:#e74c3c">*</span></label>
            <select name="usuario_id" id="sel-corretor" required>
                <option value="">Selecione...</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- CONTRATO --}}
    <p class="section-title">Contrato</p>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Código Externo <span class="tag-manual">manual</span> <span style="color:#e74c3c">*</span></label>
            <input type="text" name="codigo_externo" id="f-codigo-externo" placeholder="Ex: T717525" required>
        </div>
        <div class="form-group">
            <label>Nº Proposta <span class="tag-auto">auto</span></label>
            <input type="text" id="disp-proposta-nr" readonly>
        </div>
        <div class="form-group">
            <label>Plano <span class="tag-auto">auto</span></label>
            <input type="text" value="Super Simples (5)" readonly>
            <input type="hidden" name="plano_id" value="5">
        </div>
        <div class="form-group">
            <label>Tabela / Origem <span class="tag-auto">auto</span></label>
            <input type="text" id="disp-tabela-origem" readonly>
            <input type="hidden" name="tabela_origem_id" id="f-tabela-origem" required>
        </div>
    </div>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Código Corretora <span class="tag-auto">auto</span></label>
            <input type="text" name="codigo_corretora" id="f-codigo-corretora">
        </div>
        <div class="form-group">
            <label>Código Vendedor <span class="tag-auto">auto</span></label>
            <input type="text" name="codigo_vendedor" id="f-codigo-vendedor">
        </div>
        <div class="form-group">
            <label>Vigência <span class="tag-auto">auto</span></label>
            <input type="date" name="data_vigencia" id="f-data-vigencia" required>
        </div>
        <div class="form-group">
            <label>Data 1º Boleto <span class="tag-auto">auto</span></label>
            <input type="date" name="data_boleto" id="f-data-boleto" required>
        </div>
    </div>

    {{-- EMPRESA --}}
    <p class="section-title">Empresa Contratante <span class="tag-auto">auto</span></p>
    <div class="form-row cols-3">
        <div class="form-group">
            <label>CNPJ <span style="color:#e74c3c">*</span></label>
            <input type="text" name="cnpj" id="f-cnpj" required>
        </div>
        <div class="form-group" style="grid-column:span 2">
            <label>Razão Social <span style="color:#e74c3c">*</span></label>
            <input type="text" name="razao_social" id="f-razao-social" required>
        </div>
    </div>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Cidade</label>
            <input type="text" name="cidade" id="f-cidade">
        </div>
        <div class="form-group">
            <label>UF</label>
            <input type="text" name="uf" id="f-uf" maxlength="2">
        </div>
        <div class="form-group">
            <label>Responsável <span style="color:#e74c3c">*</span></label>
            <input type="text" name="responsavel" id="f-responsavel" required>
        </div>
        <div class="form-group">
            <label>Celular</label>
            <input type="text" name="celular" id="f-celular">
        </div>
    </div>
    <div class="form-row cols-2">
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" id="f-email">
        </div>
        <div class="form-group">
            <label>Nº de Vidas <span class="tag-auto">auto</span></label>
            <input type="number" name="vidas" id="f-vidas" min="1">
        </div>
    </div>

    {{-- PLANO --}}
    <p class="section-title">Plano Contratado</p>
    <div class="form-row cols-2">
        <div class="form-group" style="grid-column:span 2">
            <label>Nome Comercial do Plano (Saúde) <span class="tag-auto">auto</span></label>
            <input type="text" name="plano_contrado" id="f-plano-contrado">
        </div>
    </div>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Cód. Comercial Saúde <span class="tag-auto">auto</span></label>
            <input type="text" name="codigo_saude" id="f-codigo-saude">
        </div>
        <div class="form-group">
            <label>Cód. ANS Saúde</label>
            <input type="text" id="disp-ans-saude" readonly>
        </div>
        <div class="form-group">
            <label>Cód. Comercial Odonto <span class="tag-auto">auto</span></label>
            <input type="text" name="codigo_odonto" id="f-codigo-odonto">
        </div>
        <div class="form-group">
            <label>Cód. ANS Odonto</label>
            <input type="text" id="disp-ans-odonto" readonly>
        </div>
    </div>
    <div class="form-row cols-3">
        <div class="form-group">
            <label>Código Cliente <span class="tag-manual">manual</span></label>
            <input type="text" name="codigo_cliente" id="f-codigo-cliente" placeholder="Ex: IPM0C">
        </div>
        <div class="form-group">
            <label>Senha Cliente <span class="tag-manual">manual</span></label>
            <input type="text" name="senha_cliente" id="f-senha-cliente" placeholder="Ex: R0AWW">
        </div>
        <div class="form-group"></div>
    </div>

    {{-- VALORES --}}
    <p class="section-title">Valores <span class="tag-manual">manual</span></p>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Valor Plano Saúde (R$) <span style="color:#e74c3c">*</span></label>
            <input type="number" step="0.01" name="valor_plano_saude" id="f-vpl-saude" required oninput="calcTotais()">
        </div>
        <div class="form-group">
            <label>Valor Plano Odonto (R$) <span style="color:#e74c3c">*</span></label>
            <input type="number" step="0.01" name="valor_plano_odonto" id="f-vpl-odonto" required oninput="calcTotais()">
        </div>
        <div class="form-group">
            <label>Taxa Adesão (R$) <span style="color:#e74c3c">*</span></label>
            <input type="number" step="0.01" name="taxa_adesao" id="f-taxa-adesao" required oninput="calcTotais()">
        </div>
        <div class="form-group">
            <label>Valor Total (R$)</label>
            <input type="number" step="0.01" id="disp-valor-total" readonly>
        </div>
    </div>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Valor 1º Boleto (R$) <span style="color:#e74c3c">*</span></label>
            <input type="number" step="0.01" name="valor_boleto" id="f-valor-boleto" required>
        </div>
        <div class="form-group">
            <label>Desconto Operadora (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="desconto" id="f-desconto" value="0">
        </div>
        <div class="form-group">
            <label>Qtd. Parcelas com Desconto</label>
            <input type="number" min="0" max="12" name="quantidade_parcelas" id="f-qtd-parcelas" value="0">
        </div>
        <div class="form-group">
            <label>Desconto Corretor (R$)</label>
            <input type="number" step="0.01" min="0" name="desconto_corretor" id="f-desconto-corretor" value="0">
        </div>
    </div>

    {{-- BENEFICIÁRIOS --}}
    <p class="section-title">Beneficiários (Usuários do Plano) <span class="tag-auto">auto</span></p>
    <table class="benef-table" id="benef-table">
        <thead>
            <tr>
                <th>CPF</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Nasc.</th>
                <th>Valor (R$)</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="benef-tbody"></tbody>
    </table>
    <button type="button" class="btn-add-benef" onclick="adicionarBeneficiario()">+ Adicionar beneficiário</button>

    <hr class="divider">
    <div style="text-align:right">
        <button type="button" class="btn-back" onclick="voltarUpload()">Voltar</button>
        <button type="submit" class="btn-salvar" id="btn-salvar">Confirmar e Cadastrar</button>
    </div>
</form>
</div>
</div>

<script>
var urlParse  = "{{ route('pdf.empresarial.parse') }}";
var urlStore  = "{{ route('pdf.empresarial.store') }}";
var csrfToken = "{{ csrf_token() }}";

// ─── Drag & drop ─────────────────────────────────────────────
const dropArea = document.getElementById('file-drop-area');
dropArea.addEventListener('dragover',  e => { e.preventDefault(); dropArea.classList.add('dragover'); });
dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));
dropArea.addEventListener('drop', e => {
    e.preventDefault(); dropArea.classList.remove('dragover');
    const f = e.dataTransfer.files[0];
    if (f) { document.getElementById('pdf-input').files = e.dataTransfer.files; document.getElementById('drop-label').textContent = f.name; }
});
document.getElementById('pdf-input').addEventListener('change', function() {
    if (this.files[0]) document.getElementById('drop-label').textContent = this.files[0].name;
});

// ─── Alertas ─────────────────────────────────────────────────
function showMsg(msg, type) { document.getElementById('msg-area').innerHTML = `<div class="alert-box alert-${type}">${msg}</div>`; }
function clearMsg() { document.getElementById('msg-area').innerHTML = ''; }

// ─── Processar PDF ───────────────────────────────────────────
function processarPdf() {
    const file = document.getElementById('pdf-input').files[0];
    if (!file) { showMsg('Selecione o arquivo PDF.', 'danger'); return; }

    clearMsg();
    const btn = document.getElementById('btn-processar');
    btn.innerHTML = '<span class="spinner"></span> Processando...'; btn.disabled = true;

    const fd = new FormData();
    fd.append('pdf', file);
    fd.append('_token', csrfToken);

    fetch(urlParse, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btn.innerHTML = 'Processar PDF'; btn.disabled = false;
            if (data.error) { showMsg(data.error, 'danger'); return; }
            preencherFormulario(data);
        })
        .catch(e => { btn.innerHTML = 'Processar PDF'; btn.disabled = false; showMsg('Erro: ' + e.message, 'danger'); });
}

// ─── Preencher preview ───────────────────────────────────────
function preencherFormulario(data) {
    const p = data.parsed;

    document.getElementById('h-pdf-token').value = data.pdf_token || '';

    // Vendedor
    document.getElementById('disp-codigo-vendedor').value = p.codigo_vendedor || '';
    if (data.vendedor) {
        document.getElementById('sel-corretor').value = data.vendedor.id;
        document.getElementById('vendedor-status').innerHTML = `<span class="vendedor-found">✓ ${data.vendedor.name}</span>`;
    } else if (p.codigo_vendedor || p.nome_vendedor) {
        document.getElementById('vendedor-status').innerHTML =
            `<span class="vendedor-notfound">Vendedor (${esc(p.nome_vendedor || p.codigo_vendedor)}) não encontrado — selecione manualmente.</span>`;
    }

    // Contrato
    document.getElementById('disp-proposta-nr').value  = p.proposta_nr  || '';
    document.getElementById('f-codigo-corretora').value = p.codigo_corretora || '';
    document.getElementById('f-codigo-vendedor').value  = p.codigo_vendedor  || '';
    document.getElementById('f-data-vigencia').value    = p.data_vigencia    || '';
    document.getElementById('f-data-boleto').value      = p.data_boleto      || '';

    // Tabela origem
    if (data.tabela_origem_id) {
        document.getElementById('f-tabela-origem').value    = data.tabela_origem_id;
        document.getElementById('disp-tabela-origem').value = data.tabela_origem_nome || data.tabela_origem_id;
    }

    // Empresa
    document.getElementById('f-cnpj').value        = p.cnpj         || '';
    document.getElementById('f-razao-social').value = p.razao_social || '';
    document.getElementById('f-cidade').value       = p.cidade       || '';
    document.getElementById('f-uf').value           = p.uf           || '';
    document.getElementById('f-responsavel').value  = p.responsavel  || '';
    document.getElementById('f-celular').value      = p.celular      || '';
    document.getElementById('f-email').value        = p.email        || '';
    document.getElementById('f-vidas').value        = p.vidas        || '';

    // Plano
    document.getElementById('f-plano-contrado').value  = p.plano_nome_comercial || '';
    document.getElementById('f-codigo-saude').value    = p.codigo_saude         || '';
    document.getElementById('f-codigo-odonto').value   = p.codigo_odonto        || '';
    document.getElementById('disp-ans-saude').value    = p.codigo_ans_saude     || '';
    document.getElementById('disp-ans-odonto').value   = p.codigo_ans_odonto    || '';

    // Valores (pre-fill from PDF, editable — always set even when 0.00)
    if (p.valor_plano_saude  != null) document.getElementById('f-vpl-saude').value   = p.valor_plano_saude;
    if (p.valor_plano_odonto != null) document.getElementById('f-vpl-odonto').value  = p.valor_plano_odonto;
    if (p.taxa_adesao        != null) document.getElementById('f-taxa-adesao').value = p.taxa_adesao;
    if (p.valor_total && parseFloat(p.valor_total) > 0) document.getElementById('f-valor-boleto').value = p.valor_total;
    calcTotais();

    // Beneficiários
    document.getElementById('benef-tbody').innerHTML = '';
    (p.beneficiarios || []).forEach(b => adicionarBeneficiario(b));

    // Show preview
    document.getElementById('upload-section').style.display  = 'none';
    document.getElementById('preview-section').style.display = 'block';
    clearMsg();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── Totais ──────────────────────────────────────────────────
function calcTotais() {
    const s = parseFloat(document.getElementById('f-vpl-saude').value)   || 0;
    const o = parseFloat(document.getElementById('f-vpl-odonto').value)  || 0;
    const a = parseFloat(document.getElementById('f-taxa-adesao').value) || 0;
    const total = s + o + a;
    document.getElementById('disp-valor-total').value = total.toFixed(2);
    // Auto-fill boleto if not touched
    const boleto = document.getElementById('f-valor-boleto');
    if (!boleto.dataset.touched) boleto.value = total.toFixed(2);
}
document.getElementById('f-valor-boleto').addEventListener('input', function() {
    this.dataset.touched = '1';
});

// ─── Beneficiários ───────────────────────────────────────────
function adicionarBeneficiario(b) {
    b = b || {};
    const tbody = document.getElementById('benef-tbody');
    const tr = document.createElement('tr');
    const tipo = b.tipo || 'T';
    tr.innerHTML = `
        <td><input type="text" name="benef_cpfs[]"   value="${esc(b.cpf  ||'')}" placeholder="000.000.000-00"></td>
        <td><input type="text" name="benef_nomes[]"  value="${esc(b.nome ||'')}" placeholder="Nome completo"></td>
        <td>
            <select name="benef_tipos[]">
                <option value="T" ${tipo==='T'?'selected':''}>T - Titular</option>
                <option value="D" ${tipo==='D'?'selected':''}>D - Dependente</option>
            </select>
        </td>
        <td><input type="date" name="benef_nascs[]"  value="${esc(b.data_nascimento||'')}"></td>
        <td><input type="text" name="benef_valors[]" value="${esc(b.valor||'')}" placeholder="0.00" style="width:80px"></td>
        <td><button type="button" class="btn-rem-benef" onclick="this.closest('tr').remove()">✕</button></td>`;
    tbody.appendChild(tr);
}

// ─── Voltar ───────────────────────────────────────────────────
function voltarUpload() {
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('upload-section').style.display  = 'block';
    clearMsg();
}

// ─── Salvar ───────────────────────────────────────────────────
function salvarContrato(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-salvar');
    btn.innerHTML = '<span class="spinner"></span> Salvando...'; btn.disabled = true;
    clearMsg();

    const fd = new FormData(document.getElementById('preview-section'));

    fetch(urlStore, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrfToken } })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                showMsg('Erro: ' + data.error, 'danger');
                btn.innerHTML = 'Confirmar e Cadastrar'; btn.disabled = false;
                return;
            }
            showMsg('Contrato cadastrado com sucesso! Redirecionando...', 'success');
            setTimeout(() => window.location.href = data.redirect, 1200);
        })
        .catch(err => {
            showMsg('Erro: ' + err.message, 'danger');
            btn.innerHTML = 'Confirmar e Cadastrar'; btn.disabled = false;
        });
}

function esc(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])); }
</script>
</x-app-layout>
