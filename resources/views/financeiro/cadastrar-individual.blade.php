<x-app-layout>
@section('css')
<link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
<style>
    .cad-wrap { max-width: 980px; margin: 30px auto; }
    .card-escuro { background: #1e2230; border-radius: 10px; padding: 28px; color: #e0e6f0; }
    .card-escuro h2 { font-size: 1.15rem; margin-bottom: 20px; color: #7eb8f7; border-bottom: 1px solid #2e3550; padding-bottom: 10px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 0.82rem; color: #8a9bbb; margin-bottom: 5px; }
    .form-group label .req { color: #e74c3c; margin-left: 2px; }
    .form-group input, .form-group select {
        width: 100%; padding: 9px 12px; border: 1px solid #2e3550;
        border-radius: 6px; background: #252a3a; color: #e0e6f0; font-size: 0.9rem; box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #4e7ab5; }
    .form-row { display: grid; gap: 12px; }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-row.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
    .section-title { font-size: 0.92rem; color: #7eb8f7; margin: 22px 0 10px; font-weight: 600; border-left: 3px solid #3b6fd4; padding-left: 10px; }
    .btn-salvar { background: #27ae60; color: #fff; border: none; padding: 11px 32px; border-radius: 7px; cursor: pointer; font-size: 1rem; }
    .btn-salvar:hover { background: #1e9950; }
    .btn-back { background: transparent; color: #8a9bbb; border: 1px solid #2e3550; padding: 10px 22px; border-radius: 7px; cursor: pointer; font-size: 0.9rem; margin-right: 8px; text-decoration: none; display: inline-block; }
    .alert-box { padding: 12px 16px; border-radius: 7px; margin-bottom: 16px; font-size: 0.9rem; }
    .alert-danger  { background: #3a1a1a; border: 1px solid #c0392b; color: #e74c3c; }
    .alert-success { background: #1a3a22; border: 1px solid #27ae60; color: #2ecc71; }
    .divider { border: none; border-top: 1px solid #2e3550; margin: 22px 0; }
    .parcelas-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 0.85rem; }
    .parcelas-table th { background: #252a3a; color: #8a9bbb; padding: 8px 10px; text-align: left; border-bottom: 1px solid #2e3550; }
    .parcelas-table td { padding: 7px 10px; border-bottom: 1px solid #1a1f2e; color: #e0e6f0; }
    .badge-adesao { background: #1a3040; color: #4fc3f7; padding: 2px 8px; border-radius: 4px; font-size: 0.78rem; }
    .badge-boleto { background: #1a2a1a; color: #4ade80; padding: 2px 8px; border-radius: 4px; font-size: 0.78rem; }
    .badge-info { color: #8a9bbb; font-size: 0.78rem; }
    .hint { font-size: 0.78rem; color: #5a6b8a; margin-top: 4px; }
</style>
@endsection

<div class="cad-wrap">
<div class="card-escuro">
<h2>
    <svg style="width:18px;vertical-align:middle;margin-right:6px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
    Cadastrar Contrato Individual (Manual)
</h2>

@if(session('success'))
<div class="alert-box alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert-box alert-danger">
    <strong>Corrija os erros abaixo:</strong>
    <ul style="margin:8px 0 0 16px;padding:0">
        @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('individual.manual.store') }}">
@csrf

{{-- ══ Corretor ══ --}}
<div class="section-title">Corretor / Vendedor</div>
<div class="form-row cols-3">
    <div class="form-group">
        <label>Corretor <span class="req">*</span></label>
        <select name="usuario_id" required>
            <option value="">Selecione...</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" {{ old('usuario_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Código Externo</label>
        <input type="text" name="codigo_externo" value="{{ old('codigo_externo') }}" placeholder="opcional (único)">
    </div>
    <div class="form-group">
        <label>Quantidade de Vidas</label>
        <input type="number" name="quantidade_vidas" value="{{ old('quantidade_vidas', 1) }}" min="1" max="99">
    </div>
</div>

{{-- ══ Cliente ══ --}}
<hr class="divider">
<div class="section-title">Dados do Cliente</div>
<div class="form-row cols-3">
    <div class="form-group" style="grid-column:span 2">
        <label>Nome Completo <span class="req">*</span></label>
        <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Nome completo do titular">
    </div>
    <div class="form-group">
        <label>CPF <span class="req">*</span></label>
        <input type="text" name="cpf" value="{{ old('cpf') }}" required placeholder="000.000.000-00" maxlength="14">
    </div>
</div>
<div class="form-row cols-4">
    <div class="form-group">
        <label>Data de Nascimento <span class="req">*</span></label>
        <input type="date" name="data_nascimento" value="{{ old('data_nascimento') }}" required>
    </div>
    <div class="form-group">
        <label>Celular <span class="req">*</span></label>
        <input type="text" name="celular" value="{{ old('celular') }}" required placeholder="(00) 00000-0000">
    </div>
    <div class="form-group">
        <label>Telefone</label>
        <input type="text" name="telefone" value="{{ old('telefone') }}" placeholder="opcional">
    </div>
    <div class="form-group">
        <label>E-mail</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="opcional">
    </div>
</div>

{{-- ══ Endereço ══ --}}
<hr class="divider">
<div class="section-title">Endereço <span style="font-size:0.78rem;color:#5a6b8a;font-weight:400">(opcional)</span></div>
<div class="form-row cols-4">
    <div class="form-group">
        <label>CEP</label>
        <input type="text" name="cep" value="{{ old('cep') }}" placeholder="00000-000" maxlength="9">
    </div>
    <div class="form-group" style="grid-column:span 2">
        <label>Rua / Logradouro</label>
        <input type="text" name="rua" value="{{ old('rua') }}">
    </div>
    <div class="form-group">
        <label>Bairro</label>
        <input type="text" name="bairro" value="{{ old('bairro') }}">
    </div>
</div>
<div class="form-row cols-4">
    <div class="form-group" style="grid-column:span 2">
        <label>Complemento</label>
        <input type="text" name="complemento" value="{{ old('complemento') }}">
    </div>
    <div class="form-group">
        <label>Cidade</label>
        <input type="text" name="cidade" value="{{ old('cidade') }}">
    </div>
    <div class="form-group">
        <label>UF</label>
        <input type="text" name="uf" value="{{ old('uf') }}" maxlength="2" placeholder="GO" style="text-transform:uppercase">
    </div>
</div>

{{-- ══ Contrato ══ --}}
<hr class="divider">
<div class="section-title">Dados do Contrato</div>
<div class="form-row cols-4">
    <div class="form-group">
        <label>Tabela de Origem / Cidade <span class="req">*</span></label>
        <select name="tabela_origem_id" required>
            <option value="">Selecione...</option>
            @foreach($tabelas as $t)
            <option value="{{ $t->id }}" {{ old('tabela_origem_id') == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Acomodação <span class="req">*</span></label>
        <select name="acomodacao_id" required>
            <option value="">Selecione...</option>
            @foreach($acomodacoes as $a)
            <option value="{{ $a->id }}" {{ old('acomodacao_id') == $a->id ? 'selected' : '' }}>{{ $a->nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Coparticipação</label>
        <select name="coparticipacao">
            <option value="nao" {{ old('coparticipacao', 'nao') == 'nao' ? 'selected' : '' }}>Não</option>
            <option value="sim" {{ old('coparticipacao') == 'sim' ? 'selected' : '' }}>Sim</option>
        </select>
    </div>
    <div class="form-group">
        <label>Odonto</label>
        <select name="odonto">
            <option value="nao" {{ old('odonto', 'nao') == 'nao' ? 'selected' : '' }}>Não</option>
            <option value="sim" {{ old('odonto') == 'sim' ? 'selected' : '' }}>Sim</option>
        </select>
    </div>
</div>
<div class="form-row cols-2">
    <div class="form-group">
        <label>Valor do Plano (R$) <span class="req">*</span></label>
        <input type="text" name="valor_plano" id="valor_plano" value="{{ old('valor_plano') }}" required placeholder="0,00">
        <p class="hint">Base de cálculo das comissões</p>
    </div>
    <div class="form-group">
        <label>Valor de Adesão (R$)</label>
        <input type="text" name="valor_adesao" value="{{ old('valor_adesao', '0,00') }}" placeholder="0,00">
        <p class="hint">Valor pago na 1ª parcela (adesão)</p>
    </div>
</div>

{{-- ══ Datas & Parcelas ══ --}}
<hr class="divider">
<div class="section-title">Adesão &amp; Parcelas</div>
<div class="form-row cols-4">
    <div class="form-group">
        <label>Data Adesão <span class="req">*</span></label>
        <input type="date" name="data_adesao" id="data_adesao" value="{{ old('data_adesao') }}" required>
        <p class="hint">Data da assinatura / vigência</p>
    </div>
    <div class="form-group">
        <label>Data Baixa do Contrato</label>
        <input type="date" name="data_baixa" value="{{ old('data_baixa') }}">
        <p class="hint">Data de encerramento (opcional)</p>
    </div>
    <div class="form-group">
        <label>Data 1º Boleto <span class="req">*</span></label>
        <input type="date" name="data_boleto" id="data_boleto" value="{{ old('data_boleto') }}" required>
        <p class="hint">Gera as parcelas 2 em diante</p>
    </div>
    <div class="form-group">
        <label>Qtd. Parcelas <span class="req">*</span></label>
        <input type="number" name="qtd_parcelas" id="qtd_parcelas" value="{{ old('qtd_parcelas', 6) }}" required min="1" max="24">
        <p class="hint">Usada se não houver config cadastrada</p>
    </div>
</div>

{{-- ══ Preview dinâmico ══ --}}
<div id="parcelas-preview-wrap" style="display:none">
    <div class="section-title" style="margin-top:18px">
        Preview das Parcelas
        <span id="preview-note" class="badge-info" style="font-weight:400;font-size:0.78rem;margin-left:8px"></span>
    </div>
    <table class="parcelas-table">
        <thead>
            <tr>
                <th style="width:80px">Parcela</th>
                <th>Data Prevista</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody id="parcelas-preview-body"></tbody>
    </table>
</div>

<hr class="divider" style="margin-top:28px">
<div style="text-align:right;">
    <a href="{{ route('financeiro.index') }}?ac=individual" class="btn-back">&#8592; Voltar</a>
    <button type="submit" class="btn-salvar">
        <svg style="width:15px;vertical-align:middle;margin-right:5px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        Cadastrar Contrato
    </button>
</div>

</form>
</div>
</div>

@section('js')
<script>
(function () {
    var fldAdesao  = document.getElementById('data_adesao');
    var fldBoleto  = document.getElementById('data_boleto');
    var fldQtd     = document.getElementById('qtd_parcelas');

    function pad(n) { return ('0' + n).slice(-2); }
    function fmt(d) { return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear(); }
    function addMonths(dateStr, months) {
        var d = new Date(dateStr + 'T00:00:00');
        d.setMonth(d.getMonth() + months);
        return d;
    }

    function calcPreview() {
        var adesao = fldAdesao.value;
        var boleto = fldBoleto.value;
        var qtd    = parseInt(fldQtd.value) || 6;
        var wrap   = document.getElementById('parcelas-preview-wrap');
        var tbody  = document.getElementById('parcelas-preview-body');
        var note   = document.getElementById('preview-note');

        if (!adesao || !boleto) { wrap.style.display = 'none'; return; }

        var rows = '';
        var adesaoDate = new Date(adesao + 'T00:00:00');
        rows += '<tr><td><strong>1</strong></td><td>' + fmt(adesaoDate) +
                '</td><td><span class="badge-adesao">Adesão</span></td></tr>';

        for (var i = 1; i < qtd; i++) {
            var d = addMonths(boleto, i - 1);
            rows += '<tr><td>' + (i + 1) + '</td><td>' + fmt(d) +
                    '</td><td><span class="badge-boleto">Boleto</span></td></tr>';
        }

        tbody.innerHTML = rows;
        note.textContent = '(preview com ' + qtd + ' parcelas — o sistema usará config de comissão se disponível)';
        wrap.style.display = '';
    }

    fldAdesao.addEventListener('change', calcPreview);
    fldBoleto.addEventListener('change', calcPreview);
    fldQtd.addEventListener('input', calcPreview);

    // Restore preview on validation error
    if (fldAdesao.value && fldBoleto.value) calcPreview();
})();
</script>
@endsection
</x-app-layout>
