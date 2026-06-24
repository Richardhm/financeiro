@php
    $statusCls = match(true) {
        str_contains(strtolower($status ?? ''), '1º') => 'fin-p1',
        str_contains(strtolower($status ?? ''), '2º') => 'fin-p2',
        str_contains(strtolower($status ?? ''), '3º') => 'fin-p3',
        str_contains(strtolower($status ?? ''), '4º') => 'fin-p4',
        str_contains(strtolower($status ?? ''), '5º') => 'fin-p5',
        str_contains(strtolower($status ?? ''), '6º') || strtolower($status ?? '') === 'finalizado' => 'fin-p6',
        strtolower($status ?? '') === 'cancelado'  => 'fin-stat-danger',
        strtolower($status ?? '') === 'em análise' => 'fin-analise',
        strtolower($status ?? '') === 'atrasado'   => 'fin-stat-warn',
        default => ''
    };
    $pColors = ['fin-p1','fin-p2','fin-p3','fin-p4','fin-p5','fin-p6'];
@endphp

<div class="fim-modal-header">
    <button id="closeModalIndividual" class="fim-modal-close">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<div class="fim-panels">

    {{-- LEFT: formulário --}}
    <div class="fim-panel-left">

        <div class="fim-client-header">
            <div>
                <div class="fim-client-name">{{ $cliente }}</div>
                <div class="fim-client-subinfo">ID #{{ $id }} &bull; Contrato {{ $dados->codigo_externo }}</div>
            </div>
            <span class="fin-tbl-badge {{ $statusCls }}">{{ $status }}</span>
        </div>

        <form>
            <input type="hidden" id="id_cliente" value="{{ $id }}">

            {{-- Contrato --}}
            <div class="fim-section-label">Contrato</div>
            <div class="fim-grid-4">
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Administradora</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="administradora" value="Hapvida" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Corretor</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual_select"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <select disabled id="mudar_corretor_individual" class="fim-select">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $u->id == $user_id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Status</span></label>
                    <input type="text" value="{{ $status }}" id="status_individual" class="fim-input" readonly>
                </div>
            </div>

            {{-- Dados Pessoais --}}
            <div class="fim-section-label">Dados Pessoais</div>
            <div class="fim-grid-4">
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Cliente</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cliente" value="{{ $cliente }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>CPF</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cpf" value="{{ $cpf }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Nascimento</span></label>
                    <input type="text" id="data_nascimento" value="{{ $data_nascimento }}" class="fim-input editar_campo_individual" readonly>
                </div>
            </div>

            {{-- Contato --}}
            <div class="fim-section-label">Contato</div>
            <div class="fim-grid-4">
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Cód. Externo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="codigo_externo" value="{{ $codigo_externo }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Celular</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="fone" value="{{ $celular }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Email</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="email" value="{{ $email }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Carteirinha</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="carteirinha" value="{{ $carteirinha }}" class="fim-input editar_campo_individual" readonly>
                </div>
            </div>

            {{-- Endereço --}}
            <div class="fim-section-label">Endereço</div>
            <div class="fim-grid-4">
                <div class="fim-field">
                    <label class="fim-label">
                        <span>CEP</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cep" value="{{ $cep }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Cidade</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cidade" value="{{ $cidade }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>UF</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="uf" value="{{ $uf }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Bairro</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="bairro" value="{{ $bairro }}" class="fim-input editar_campo_individual" readonly>
                </div>
            </div>
            <div class="fim-grid-5">
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Rua</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="rua" value="{{ $rua }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Complemento</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="complemento" value="{{ $complemento }}" class="fim-input editar_campo_individual" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Data Contrato</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="date" id="data_contrato" class="fim-input editar_campo_individual" readonly>
                </div>
            </div>

            {{-- Financeiro --}}
            <div class="fim-section-label">Financeiro</div>
            <div class="fim-grid-5">
                <div class="fim-field">
                    <label class="fim-label"><span>Valor Contrato</span></label>
                    <input type="text" id="valor_contrato" readonly value="{{ number_format($valor_plano,2,',','.') }}" class="fim-input editar_campo_individual">
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Valor Adesão</span></label>
                    <input type="text" id="valor_adesao" readonly value="{{ number_format($valor_adesao,2,',','.') }}" class="fim-input editar_campo_individual">
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Nome Responsável</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="nome_responsavel" readonly value="" class="fim-input editar_campo_individual">
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>CPF Responsável</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_individual"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cpf_responsavel" readonly value="" class="fim-input editar_campo_individual">
                </div>
            </div>

        </form>
    </div>

    {{-- RIGHT: Parcelas --}}
    <div class="fim-panel-right">
        <div class="fim-parcelas-title">Parcelas do Contrato</div>
        <table class="fim-parcelas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Contrato</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Baixa</th>
                    <th>Dias</th>
                    <th>Ação</th>
                    <th>Desfazer</th>
                </tr>
            </thead>
            <tbody>
            @php
                $total_cliente   = 0;
                $total_comissao  = 0;
            @endphp
            @foreach($dados->comissao->comissoesLancadas as $kk => $cr)
                @php
                    $isPaid  = !empty($cr->data_baixa);
                    $isLate  = !$isPaid && ($cr->quantidade_dias ?? 0) > 0;
                    $trCls   = $isPaid ? 'fim-tr-paid' : ($isLate ? 'fim-tr-late' : '');
                    $pIdx    = min((int)$cr->parcela, 6);
                    $pCls    = $pColors[$pIdx - 1] ?? '';
                    if ($isPaid) { $total_comissao += $cr->valor; }
                    else         { $total_cliente  += $cr->valor; }
                @endphp
                <tr class="{{ $trCls }}" id="{{ $cr->parcela }}">
                    <td>
                        @if($cr->parcela == 1)
                            <span class="fim-parcelas-badge fin-p1">Adesão</span>
                        @else
                            <span class="fim-parcelas-badge {{ $pCls }}">{{ $cr->parcela }}ª P.</span>
                        @endif
                    </td>
                    <td>{{ $dados->codigo_externo }}</td>
                    <td>{{ date('d/m/Y', strtotime($cr->data)) }}</td>
                    <td>
                        @if($cr->valor_pago > 0)
                            {{ number_format($cr->valor_pago, 2, ',', '.') }}
                        @else
                            <span style="color:#253a52">---</span>
                        @endif
                    </td>
                    <td class="data_baixa_individual">
                        @if(empty($cr->data_baixa))
                            <span style="color:#253a52">---</span>
                        @else
                            {{ date('d/m/Y', strtotime($cr->data_baixa)) }}
                        @endif
                    </td>
                    <td style="text-align:center">{{ $cr->quantidade_dias }}</td>
                    <td class="acao_aqui_individual" style="text-align:center">
                        @if($cr->status_financeiro == 0)
                            <input type="date" data-id="{{ $cr->id }}"
                                   min="{{ date('Y-m-d', strtotime('1900-01-01')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="fim-date-action next_individual date-picker">
                        @else
                            <button type="button" class="cursor-not-allowed" style="background:#05401e;border:1px solid #0a5c2a;border-radius:5px;padding:3px 7px;display:inline-flex;align-items:center;justify-content:center;">
                                <svg style="width:13px;height:13px;color:#5dd8a0" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M15.03 9.684h3.965c.322 0 .64.08.925.232.286.153.532.374.717.645a2.109 2.109 0 0 1 .242 1.883l-2.36 7.201c-.288.814-.48 1.355-1.884 1.355-2.072 0-4.276-.677-6.157-1.256-.472-.145-.924-.284-1.348-.404h-.115V9.478a25.485 25.485 0 0 0 4.238-5.514 1.8 1.8 0 0 1 .901-.83 1.74 1.74 0 0 1 1.21-.048c.396.13.736.397.96.757.225.36.32.788.269 1.211l-1.562 4.63ZM4.177 10H7v8a2 2 0 1 1-4 0v-6.823C3 10.527 3.527 10 4.176 10Z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <svg xmlns="http://www.w3.org/2000/svg" data-id="{{ $cr->id }}" viewBox="0 0 24 24" fill="currentColor" class="desfazer_individual">
                            <path fill-rule="evenodd" d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
