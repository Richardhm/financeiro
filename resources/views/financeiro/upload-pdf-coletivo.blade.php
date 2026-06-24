<x-app-layout>
@section('css')
<link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
<style>
    .pdf-upload-wrap { max-width: 900px; margin: 30px auto; }
    .card-escuro { background: #1e2230; border-radius: 10px; padding: 28px; color: #e0e6f0; }
    .card-escuro h2 { font-size: 1.15rem; margin-bottom: 20px; color: #7eb8f7; border-bottom: 1px solid #2e3550; padding-bottom: 10px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 0.82rem; color: #8a9bbb; margin-bottom: 5px; }
    .form-group input, .form-group select {
        width: 100%; padding: 9px 12px; border: 1px solid #2e3550;
        border-radius: 6px; background: #252a3a; color: #e0e6f0; font-size: 0.9rem; box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #4e7ab5; }
    .form-group input[readonly] { opacity: 0.55; cursor: not-allowed; }
    .form-row { display: grid; gap: 12px; }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-row.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
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
    .alert-warn    { background: #3a2d10; border: 1px solid #e67e22; color: #f39c12; }
    #preview-section { display: none; }
    .file-drop {
        border: 2px dashed #2e3550; border-radius: 10px; padding: 36px;
        text-align: center; cursor: pointer; color: #8a9bbb; transition: border-color 0.2s;
        margin-top: 18px;
    }
    .file-drop:hover, .file-drop.dragover { border-color: #3b6fd4; color: #7eb8f7; }
    .tag-adm { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600; }
    .tag-alter   { background: #2d1f3a; color: #b07fe0; }
    .tag-allcare { background: #1a3040; color: #4fc3f7; }
    .dep-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end; margin-bottom: 8px; }
    .btn-rem-dep { background: #c0392b; color: #fff; border: none; border-radius: 5px; padding: 8px 12px; cursor: pointer; }
    .btn-add-dep { background: #1e2a38; color: #7eb8f7; border: 1px solid #3b6fd4; border-radius: 6px; padding: 7px 16px; cursor: pointer; font-size: 0.85rem; margin-top: 6px; }
    .spinner { display: inline-block; width: 16px; height: 16px; border: 3px solid #3b6fd4; border-top-color: transparent; border-radius: 50%; animation: spin 0.7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .vendedor-found    { color: #2ecc71; font-size: 0.82rem; margin-top: 4px; }
    .vendedor-notfound { color: #e74c3c; font-size: 0.82rem; margin-top: 4px; }
    .divider { border: none; border-top: 1px solid #2e3550; margin: 22px 0; }

    /* Modal desconto */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.65); z-index:1000; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal-box { background:#1e2230; border-radius:12px; padding:28px; width:100%; max-width:480px; border:1px solid #2e3550; }
    .modal-box h3 { color:#f39c12; font-size:1.05rem; margin-bottom:6px; }
    .modal-box p  { color:#8a9bbb; font-size:0.85rem; margin-bottom:18px; }
    .modal-radio-group { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
    .modal-radio-group label { display:flex; align-items:center; gap:6px; cursor:pointer; padding:8px 14px; border:1px solid #2e3550; border-radius:7px; font-size:0.88rem; color:#e0e6f0; transition:border-color .15s; }
    .modal-radio-group label:hover { border-color:#e67e22; }
    .modal-radio-group input[type=radio]:checked + span { color:#f39c12; }
    .modal-radio-group label:has(input:checked) { border-color:#e67e22; background:#2a1e10; }
    .modal-desc-fields { display:none; }
    .modal-desc-fields.show { display:block; }
    .modal-value-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .btn-modal-ok { background:#e67e22; color:#fff; border:none; padding:10px 28px; border-radius:7px; cursor:pointer; font-size:0.95rem; width:100%; margin-top:12px; }
    .btn-modal-ok:hover { background:#ca6f1e; }
    .diff-badge { display:inline-block; background:#3a2d10; border:1px solid #e67e22; color:#f39c12; padding:3px 10px; border-radius:8px; font-size:0.82rem; font-weight:600; }

    /* Pre-upload extras */
    .pre-upload-card { background:#252a3a; border-radius:8px; padding:18px; margin-bottom:20px; border:1px solid #2e3550; }
    .pre-upload-card .card-label { font-size:0.78rem; color:#8a9bbb; margin-bottom:12px; text-transform:uppercase; letter-spacing:.04em; }
    .toggle-desc { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
    .toggle-desc input[type=checkbox] { width:16px; height:16px; cursor:pointer; }
    .toggle-desc label { font-size:0.9rem; color:#e0e6f0; cursor:pointer; }
</style>
@endsection

<div class="pdf-upload-wrap">
<div class="card-escuro">
<h2>
    <svg style="width:18px;vertical-align:middle;margin-right:6px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
    Cadastrar Contrato Coletivo via PDF
</h2>

<div id="msg-area"></div>

{{-- ===================== ETAPA 1: PRÉ-UPLOAD ===================== --}}
<div id="upload-section">

    <div class="pre-upload-card">
        <div class="card-label">1. Informações obrigatórias antes do upload</div>

        <div class="form-row cols-2">
            <div class="form-group">
                <label>Administradora <span style="color:#e74c3c">*</span></label>
                <select id="sel-adm" required>
                    <option value="">Selecione...</option>
                    <option value="alter">Alter Administradora</option>
                    <option value="allcare">Allcare Administradora</option>
                </select>
            </div>
            <div class="form-group">
                <label>Valor da Adesão — o que o cliente paga (R$) <span style="color:#e74c3c">*</span></label>
                <input type="number" step="0.01" min="0" id="pre-valor-adesao" placeholder="Ex: 300,00" required>
            </div>
        </div>

        <div class="toggle-desc">
            <input type="checkbox" id="chk-desconto-op" onchange="toggleDescontoOp()">
            <label for="chk-desconto-op">A operadora concedeu desconto nas comissões?</label>
        </div>
        <div id="desconto-op-fields" style="display:none">
            <div class="form-row cols-2">
                <div class="form-group">
                    <label>Desconto da Operadora (%)</label>
                    <input type="number" step="0.01" min="0" max="100" id="pre-desconto-op" placeholder="Ex: 20">
                </div>
                <div class="form-group">
                    <label>Qtd. de Parcelas com Desconto</label>
                    <input type="number" min="1" max="12" id="pre-qtd-parcelas" placeholder="Ex: 3">
                </div>
            </div>
        </div>
    </div>

    <div class="pre-upload-card">
        <div class="card-label">2. Upload do arquivo PDF</div>
        <div class="file-drop" id="file-drop-area" onclick="document.getElementById('pdf-input').click()">
            <svg style="width:36px;margin-bottom:8px;opacity:0.4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            <p id="drop-label">Clique ou arraste o PDF aqui</p>
            <p style="font-size:0.78rem;margin-top:4px;opacity:0.5">Apenas .pdf — máx. 20MB</p>
        </div>
        <input type="file" id="pdf-input" accept=".pdf" style="display:none">
    </div>

    <div style="text-align:right;margin-top:8px">
        <a href="{{ route('financeiro.index') }}?ac=coletivo" class="btn-back">Voltar</a>
        <button class="btn-upload" id="btn-processar" onclick="processarPdf()">
            Processar PDF
        </button>
    </div>
</div>

{{-- ===================== ETAPA 2: PREVIEW ===================== --}}
<form id="preview-section" onsubmit="salvarContrato(event)">
    @csrf

    <div class="alert-box alert-info" id="info-fonte"></div>

    {{-- Campos ocultos vindos do pré-upload --}}
    <input type="hidden" name="desconto_operadora"  id="h-desconto-op">
    <input type="hidden" name="quantidade_parcelas" id="h-qtd-parcelas">
    {{-- Desconto corretor/corretora (preenchido pela modal) --}}
    <input type="hidden" name="desconto_corretor"   id="h-desc-corretor"  value="0">
    <input type="hidden" name="desconto_corretora"  id="h-desc-corretora" value="0">
    {{-- valor_plano extraído do PDF --}}
    <input type="hidden" id="h-valor-plano-pdf">
    {{-- token do PDF temporário --}}
    <input type="hidden" name="pdf_token" id="h-pdf-token">

    <p class="section-title">Vendedor / Corretor</p>
    <div class="form-row cols-2">
        <div class="form-group">
            <label>CPF extraído do PDF</label>
            <input type="text" id="vendedor-cpf-display" readonly>
            <span id="vendedor-status"></span>
        </div>
        <div class="form-group">
            <label>Corretor <span style="color:#e74c3c">*</span></label>
            <select name="usuario_id" id="sel-corretor" required>
                <option value="">Selecione o corretor...</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <p class="section-title">Contrato</p>
    <div class="form-row cols-3">
        <div class="form-group">
            <label>Nº Proposta / Código Externo <span style="color:#e74c3c">*</span></label>
            <input type="text" name="codigo_externo" id="f-codigo-externo" required>
        </div>
        <div class="form-group">
            <label>Início de Vigência <span style="color:#e74c3c">*</span></label>
            <input type="date" name="data_vigencia" id="f-data-vigencia" required>
        </div>
        <div class="form-group">
            <label>Data do Boleto <span style="color:#e74c3c">*</span></label>
            <input type="date" name="data_boleto" id="f-data-boleto" required>
        </div>
    </div>
    <div class="form-row cols-3">
        <div class="form-group">
            <label>Administradora <span style="color:#e74c3c">*</span></label>
            <select name="administradora_id" id="f-administradora" required>
                <option value="">Selecione...</option>
                @foreach($administradoras as $adm)
                    <option value="{{ $adm->id }}">{{ $adm->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Tabela / Cidade Origem <span style="color:#e74c3c">*</span></label>
            <input type="text" id="f-tabela-origem-display" readonly placeholder="Auto-detectado pelo PDF...">
            <input type="hidden" name="tabela_origem_id" id="f-tabela-origem" required>
        </div>
        <div class="form-group">
            <label>Acomodação <span style="color:#e74c3c">*</span></label>
            <select name="acomodacao_id" id="f-acomodacao" required>
                <option value="">Selecione...</option>
                @foreach(\App\Models\Acomodacao::all() as $ac)
                    <option value="{{ $ac->id }}">{{ $ac->nome }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>Valor do Plano — tabela PDF (R$) <span style="color:#e74c3c">*</span></label>
            <input type="number" step="0.01" name="valor_plano" id="f-valor-plano" required>
        </div>
        <div class="form-group">
            <label>Valor da Adesão — cliente paga (R$) <span style="color:#e74c3c">*</span></label>
            <input type="number" step="0.01" name="valor_adesao" id="f-valor-adesao" required>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;margin-top:8px">
                <input type="checkbox" name="coparticipacao" id="f-copart" style="width:auto"> Coparticipação
            </label>
            <label style="display:flex;align-items:center;gap:8px;margin-top:8px">
                <input type="checkbox" name="odonto" id="f-odonto" style="width:auto"> Odonto
            </label>
        </div>
    </div>

    {{-- Bloco desconto (aparece quando há diferença detectada) --}}
    <div id="bloco-desconto" style="display:none" class="alert-box alert-warn">
        <strong>Diferença de valor detectada</strong><br>
        Valor do PDF: <span id="bd-pdf"></span> — Valor da adesão: <span id="bd-adesao"></span> —
        Desconto total: <span id="bd-diff" class="diff-badge"></span><br><br>
        <div id="bd-resumo" style="font-size:0.85rem"></div>
        <button type="button" onclick="abrirModalDesconto()" style="margin-top:8px;background:#e67e22;color:#fff;border:none;padding:6px 18px;border-radius:6px;cursor:pointer;font-size:0.85rem">
            Informar quem deu o desconto
        </button>
    </div>

    <p class="section-title">Titular</p>
    <div class="form-row cols-2">
        <div class="form-group">
            <label>Nome Completo <span style="color:#e74c3c">*</span></label>
            <input type="text" name="nome" id="f-nome" required>
        </div>
        <div class="form-group">
            <label>CPF <span style="color:#e74c3c">*</span></label>
            <input type="text" name="cpf" id="f-cpf" required>
        </div>
    </div>
    <div class="form-row cols-3">
        <div class="form-group">
            <label>Data de Nascimento <span style="color:#e74c3c">*</span></label>
            <input type="date" name="data_nascimento" id="f-data-nasc" required>
        </div>
        <div class="form-group">
            <label>Celular</label>
            <input type="text" name="celular" id="f-celular">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="f-email">
        </div>
    </div>
    <div class="form-row cols-4">
        <div class="form-group">
            <label>CEP</label>
            <input type="text" name="cep" id="f-cep">
        </div>
        <div class="form-group" style="grid-column:span 2">
            <label>Rua / Logradouro</label>
            <input type="text" name="rua" id="f-rua">
        </div>
        <div class="form-group">
            <label>Bairro</label>
            <input type="text" name="bairro" id="f-bairro">
        </div>
    </div>
    <div class="form-row cols-2">
        <div class="form-group">
            <label>Cidade</label>
            <input type="text" name="cidade" id="f-cidade">
        </div>
        <div class="form-group">
            <label>UF</label>
            <input type="text" name="uf" id="f-uf" maxlength="2">
        </div>
    </div>

    <p class="section-title">Dependentes</p>
    <div id="dependentes-wrap"></div>
    <button type="button" class="btn-add-dep" onclick="adicionarDependente()">+ Adicionar dependente</button>

    <hr class="divider">

    <div style="text-align:right">
        <button type="button" class="btn-back" onclick="voltarUpload()">Voltar</button>
        <button type="submit" class="btn-salvar" id="btn-salvar">Confirmar e Cadastrar</button>
    </div>
</form>
</div>
</div>

{{-- ===================== MODAL DESCONTO ===================== --}}
<div class="modal-overlay" id="modal-desconto">
<div class="modal-box">
    <h3>⚠️ Quem deu o desconto?</h3>
    <p>
        O plano na tabela custa <strong id="md-plano"></strong> mas o cliente pagará <strong id="md-adesao"></strong>.
        O desconto de <strong id="md-diff" class="diff-badge"></strong> precisa ser imputado a quem o concedeu,
        pois será descontado na folha de pagamento.
    </p>

    <div class="modal-radio-group">
        <label><input type="radio" name="quem_desc" value="corretor"  onchange="onQuemDesc()"> <span>Corretor</span></label>
        <label><input type="radio" name="quem_desc" value="corretora" onchange="onQuemDesc()"> <span>Corretora</span></label>
        <label><input type="radio" name="quem_desc" value="ambos"     onchange="onQuemDesc()"> <span>Ambos</span></label>
    </div>

    <div class="modal-desc-fields" id="md-fields">
        <div class="modal-value-row">
            <div class="form-group" id="md-field-corretor">
                <label>Desconto do Corretor (R$)</label>
                <input type="number" step="0.01" min="0" id="md-desc-corretor" value="0" oninput="onDescontoInput('corretor')">
            </div>
            <div class="form-group" id="md-field-corretora">
                <label>Desconto da Corretora (R$)</label>
                <input type="number" step="0.01" min="0" id="md-desc-corretora" value="0" oninput="onDescontoInput('corretora')">
            </div>
        </div>
        <div id="md-aviso" style="font-size:0.82rem;color:#e74c3c;margin-top:4px"></div>
    </div>

    <button class="btn-modal-ok" onclick="confirmarDesconto()">Confirmar</button>
</div>
</div>

<script>
var urlParse  = "{{ route('pdf.coletivo.parse') }}";
var urlStore  = "{{ route('pdf.coletivo.store') }}";
var csrfToken = "{{ csrf_token() }}";
var _diffTotal = 0;

// ─── Drag & drop ────────────────────────────────────────────
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

// ─── Toggle desconto operadora ───────────────────────────────
function toggleDescontoOp() {
    const show = document.getElementById('chk-desconto-op').checked;
    document.getElementById('desconto-op-fields').style.display = show ? 'block' : 'none';
    if (!show) { document.getElementById('pre-desconto-op').value = ''; document.getElementById('pre-qtd-parcelas').value = ''; }
}

// ─── Alertas ─────────────────────────────────────────────────
function showMsg(msg, type) { document.getElementById('msg-area').innerHTML = `<div class="alert-box alert-${type}">${msg}</div>`; }
function clearMsg() { document.getElementById('msg-area').innerHTML = ''; }

// ─── Processar PDF ───────────────────────────────────────────
function processarPdf() {
    const adm  = document.getElementById('sel-adm').value;
    const file = document.getElementById('pdf-input').files[0];
    const adesao = parseFloat(document.getElementById('pre-valor-adesao').value);

    if (!adm)         { showMsg('Selecione a administradora.', 'danger'); return; }
    if (isNaN(adesao) || adesao <= 0) { showMsg('Informe o valor da adesão (o que o cliente paga).', 'danger'); return; }
    if (!file)        { showMsg('Selecione o arquivo PDF.', 'danger'); return; }

    clearMsg();
    const btn = document.getElementById('btn-processar');
    btn.innerHTML = '<span class="spinner"></span> Processando...';
    btn.disabled  = true;

    const fd = new FormData();
    fd.append('pdf', file);
    fd.append('administradora', adm);
    fd.append('_token', csrfToken);

    fetch(urlParse, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btn.innerHTML = 'Processar PDF'; btn.disabled = false;
            if (data.error) { showMsg(data.error, 'danger'); return; }
            preencherFormulario(data, adm, adesao);
        })
        .catch(e => { btn.innerHTML = 'Processar PDF'; btn.disabled = false; showMsg('Erro: ' + e.message, 'danger'); });
}

// ─── Preencher preview ───────────────────────────────────────
function preencherFormulario(data, adm, adesaoPre) {
    const p   = data.parsed;
    const tag = adm === 'alter'
        ? '<span class="tag-adm tag-alter">Alter</span>'
        : '<span class="tag-adm tag-allcare">Allcare</span>';

    document.getElementById('info-fonte').innerHTML =
        `${tag} PDF processado com sucesso. Revise os dados antes de confirmar.`;

    // Campos ocultos pré-upload
    const descOp  = document.getElementById('pre-desconto-op').value;
    const qtdParc = document.getElementById('pre-qtd-parcelas').value;
    document.getElementById('h-desconto-op').value  = descOp  || '';
    document.getElementById('h-qtd-parcelas').value = qtdParc || '';

    // Vendedor
    document.getElementById('vendedor-cpf-display').value = p.vendedor_cpf || '';
    if (data.vendedor) {
        document.getElementById('sel-corretor').value = data.vendedor.id;
        document.getElementById('vendedor-status').innerHTML = `<span class="vendedor-found">✓ ${data.vendedor.name}</span>`;
    } else if (p.vendedor_cpf) {
        document.getElementById('vendedor-status').innerHTML = `<span class="vendedor-notfound">CPF não encontrado — selecione manualmente.</span>`;
    }

    // Contrato
    document.getElementById('f-codigo-externo').value = p.codigo_externo || '';
    document.getElementById('f-data-vigencia').value  = p.data_vigencia  || '';
    document.getElementById('f-data-boleto').value    = p.data_boleto    || '';
    if (data.administradora_id) document.getElementById('f-administradora').value = data.administradora_id;
    if (data.acomodacao_id)     document.getElementById('f-acomodacao').value     = data.acomodacao_id;

    // Token do PDF para ser enviado no store()
    document.getElementById('h-pdf-token').value = data.pdf_token || '';

    // Tabela origem: hidden recebe o ID, display mostra o nome
    if (data.tabela_origem_id) {
        document.getElementById('f-tabela-origem').value         = data.tabela_origem_id;
        document.getElementById('f-tabela-origem-display').value = data.tabela_origem_nome || data.tabela_origem_id;
    }
    document.getElementById('f-copart').checked = !!p.plano?.coparticipacao;
    document.getElementById('f-odonto').checked = !!p.plano?.odonto;

    // Valores
    const valorPdf = parseFloat(p.valor_plano || 0);
    document.getElementById('h-valor-plano-pdf').value = valorPdf.toFixed(2);
    document.getElementById('f-valor-plano').value   = valorPdf.toFixed(2);
    document.getElementById('f-valor-adesao').value  = adesaoPre.toFixed(2);

    // Verificar diferença de valor
    verificarDiferenca(valorPdf, adesaoPre);

    // Titular
    const t = p.titular || {};
    document.getElementById('f-nome').value      = t.nome || '';
    document.getElementById('f-cpf').value       = t.cpf  || '';
    document.getElementById('f-data-nasc').value = t.data_nascimento || '';
    document.getElementById('f-celular').value   = t.celular || '';
    document.getElementById('f-email').value     = t.email  || '';
    document.getElementById('f-cep').value       = t.cep    || '';
    document.getElementById('f-rua').value       = t.rua    || '';
    document.getElementById('f-bairro').value    = t.bairro || '';
    document.getElementById('f-cidade').value    = t.cidade || '';
    document.getElementById('f-uf').value        = t.uf     || '';

    // Dependentes
    document.getElementById('dependentes-wrap').innerHTML = '';
    (p.dependentes || []).forEach(d => adicionarDependente(d.nome, d.cpf));

    // Exibir preview
    document.getElementById('upload-section').style.display  = 'none';
    document.getElementById('preview-section').style.display = 'block';
    clearMsg();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── Verificar diferença de valor ───────────────────────────
function verificarDiferenca(valorPdf, valorAdesao) {
    const diff = Math.abs(valorPdf - valorAdesao);
    _diffTotal = diff;

    // Zera descontos anteriores
    document.getElementById('h-desc-corretor').value  = '0';
    document.getElementById('h-desc-corretora').value = '0';

    const bloco = document.getElementById('bloco-desconto');
    if (diff > 0.009) {
        document.getElementById('bd-pdf').textContent    = 'R$ ' + fmt(valorPdf);
        document.getElementById('bd-adesao').textContent = 'R$ ' + fmt(valorAdesao);
        document.getElementById('bd-diff').textContent   = 'R$ ' + fmt(diff);
        document.getElementById('bd-resumo').innerHTML   = '';
        bloco.style.display = 'block';
    } else {
        bloco.style.display = 'none';
    }
}

// ─── Modal desconto ──────────────────────────────────────────
function abrirModalDesconto() {
    document.getElementById('md-plano').textContent  = 'R$ ' + fmt(parseFloat(document.getElementById('h-valor-plano-pdf').value));
    document.getElementById('md-adesao').textContent = 'R$ ' + fmt(parseFloat(document.getElementById('f-valor-adesao').value));
    document.getElementById('md-diff').textContent   = 'R$ ' + fmt(_diffTotal);
    document.getElementById('md-desc-corretor').value  = '0';
    document.getElementById('md-desc-corretora').value = '0';
    document.getElementById('md-fields').classList.remove('show');
    document.querySelectorAll('input[name=quem_desc]').forEach(r => r.checked = false);
    document.getElementById('md-aviso').textContent = '';
    document.getElementById('modal-desconto').classList.add('open');
}

function fecharModal() { document.getElementById('modal-desconto').classList.remove('open'); }

function onQuemDesc() {
    const val = document.querySelector('input[name=quem_desc]:checked')?.value;
    const fields = document.getElementById('md-fields');
    const fCorr  = document.getElementById('md-field-corretor');
    const fCorra = document.getElementById('md-field-corretora');
    fields.classList.add('show');
    if (val === 'corretor') {
        fCorr.style.display  = 'block';
        fCorra.style.display = 'none';
        document.getElementById('md-desc-corretor').value  = fmt(_diffTotal);
        document.getElementById('md-desc-corretora').value = '0';
    } else if (val === 'corretora') {
        fCorr.style.display  = 'none';
        fCorra.style.display = 'block';
        document.getElementById('md-desc-corretor').value  = '0';
        document.getElementById('md-desc-corretora').value = fmt(_diffTotal);
    } else { // ambos
        fCorr.style.display  = 'block';
        fCorra.style.display = 'block';
        document.getElementById('md-desc-corretor').value  = '0';
        document.getElementById('md-desc-corretora').value = '0';
    }
    document.getElementById('md-aviso').textContent = '';
}

function onDescontoInput(quem) {
    const val = document.querySelector('input[name=quem_desc]:checked')?.value;
    if (val !== 'ambos') return;
    const a = parseFloat(document.getElementById('md-desc-corretor').value)  || 0;
    const b = parseFloat(document.getElementById('md-desc-corretora').value) || 0;
    const restante = _diffTotal - (quem === 'corretor' ? a : b);
    if (quem === 'corretor')
        document.getElementById('md-desc-corretora').value = fmt(Math.max(0, restante));
    else
        document.getElementById('md-desc-corretor').value  = fmt(Math.max(0, restante));
    document.getElementById('md-aviso').textContent = '';
}

function confirmarDesconto() {
    const quem = document.querySelector('input[name=quem_desc]:checked')?.value;
    if (!quem) { document.getElementById('md-aviso').textContent = 'Selecione quem concedeu o desconto.'; return; }

    const dc  = parseFloat(document.getElementById('md-desc-corretor').value)  || 0;
    const dca = parseFloat(document.getElementById('md-desc-corretora').value) || 0;
    const soma = dc + dca;

    if (Math.abs(soma - _diffTotal) > 0.01) {
        document.getElementById('md-aviso').textContent =
            `A soma dos descontos (R$ ${fmt(soma)}) deve ser igual ao total (R$ ${fmt(_diffTotal)}).`;
        return;
    }

    document.getElementById('h-desc-corretor').value  = dc.toFixed(2);
    document.getElementById('h-desc-corretora').value = dca.toFixed(2);

    // Atualiza resumo no bloco de aviso
    let resumo = '';
    if (dc  > 0) resumo += `Corretor: R$ ${fmt(dc)} | `;
    if (dca > 0) resumo += `Corretora: R$ ${fmt(dca)}`;
    document.getElementById('bd-resumo').innerHTML = `<strong>Desconto registrado →</strong> ${resumo}`;

    fecharModal();
}

// Fecha modal clicando fora
document.getElementById('modal-desconto').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});

// ─── Dependentes ─────────────────────────────────────────────
function adicionarDependente(nome = '', cpf = '') {
    const wrap = document.getElementById('dependentes-wrap');
    const row  = document.createElement('div');
    row.className = 'dep-row';
    row.innerHTML = `
        <div class="form-group" style="margin:0">
            <label>Nome do Dependente</label>
            <input type="text" name="dependentes_nomes[]" value="${esc(nome)}">
        </div>
        <div class="form-group" style="margin:0">
            <label>CPF do Dependente</label>
            <input type="text" name="dependentes_cpfs[]" value="${esc(cpf)}">
        </div>
        <div><button type="button" class="btn-rem-dep" onclick="this.closest('.dep-row').remove()">✕</button></div>`;
    wrap.appendChild(row);
}

function esc(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])); }
function fmt(n) { return parseFloat(n).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }

// ─── Voltar ───────────────────────────────────────────────────
function voltarUpload() {
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('upload-section').style.display  = 'block';
    clearMsg();
}

// ─── Salvar ───────────────────────────────────────────────────
function salvarContrato(e) {
    e.preventDefault();

    // Verificar se há diferença de valor sem desconto informado
    const diff = _diffTotal;
    const dc  = parseFloat(document.getElementById('h-desc-corretor').value)  || 0;
    const dca = parseFloat(document.getElementById('h-desc-corretora').value) || 0;
    if (diff > 0.009 && Math.abs(dc + dca - diff) > 0.01) {
        showMsg('Há uma diferença de valor não explicada. Clique em "Informar quem deu o desconto" antes de salvar.', 'warn');
        document.getElementById('bloco-desconto').scrollIntoView({ behavior: 'smooth' });
        return;
    }

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
</script>
</x-app-layout>
