@php
    $statusCls = match(true) {
        str_contains(strtolower($status ?? ''), 'análise')  => 'fin-analise',
        str_contains(strtolower($status ?? ''), 'emissão')  => 'fin-emissao',
        str_contains(strtolower($status ?? ''), 'adesão')   => 'fin-adesao',
        str_contains(strtolower($status ?? ''), 'vigência') => 'fin-vigencia',
        str_contains(strtolower($status ?? ''), '2º')       => 'fin-p2',
        str_contains(strtolower($status ?? ''), '3º')       => 'fin-p3',
        str_contains(strtolower($status ?? ''), '4º')       => 'fin-p4',
        str_contains(strtolower($status ?? ''), '5º')       => 'fin-p5',
        str_contains(strtolower($status ?? ''), '6º') || strtolower($status ?? '') === 'finalizado' => 'fin-p6',
        strtolower($status ?? '') === 'cancelado' => 'fin-stat-danger',
        default => ''
    };
@endphp

<div class="fim-modal-header">
    <button id="closeModalColetivo" class="fim-modal-close">
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
                <div class="fim-client-subinfo">ID #{{ $id }} &bull; Contrato {{ $codigo_externo }}</div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo_administradora"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <select disabled name="change_administradora_coletivo" id="change_administradora_coletivo" class="fim-select">
                        <option value="">-- Administradora --</option>
                        @foreach($administradoras as $ad)
                            <option value="{{ $ad->id }}" {{ $ad->id == $administradora_id ? 'selected' : '' }}>{{ $ad->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Corretor</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo_select"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <select disabled name="change_corretor_coletivo" id="change_corretor_coletivo" class="fim-select">
                        <option value="">-- Corretor --</option>
                        @foreach($users as $us)
                            <option value="{{ $us->id }}" {{ $us->id == $cliente_id ? 'selected' : '' }}>{{ $us->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Status</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="status" value="{{ $status }}" class="fim-input" readonly>
                </div>
            </div>

            {{-- Dados Pessoais --}}
            <div class="fim-section-label">Dados Pessoais</div>
            <div class="fim-grid-4">
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Cliente</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cliente" value="{{ $cliente }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>CPF</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cpf" value="{{ $cpf }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Nascimento</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="date" id="data_nascimento" value="{{ $nascimento }}" class="fim-input mudar_coletivo" readonly>
                </div>
            </div>

            {{-- Contato --}}
            <div class="fim-section-label">Contato</div>
            <div class="fim-grid-4">
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Cód. Externo</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="codigo_externo" value="{{ $codigo_externo }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Celular</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="fone" value="{{ $fone }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Email</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="email" id="email" value="{{ $email }}" class="fim-input mudar_coletivo" readonly>
                </div>
            </div>

            {{-- Endereço --}}
            <div class="fim-section-label">Endereço</div>
            <div class="fim-grid-4">
                <div class="fim-field">
                    <label class="fim-label">
                        <span>CEP</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cep" value="{{ $cep }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Cidade</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cidade" value="{{ $cidade }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>UF</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="uf" value="{{ $uf }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Bairro</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="bairro" value="{{ $bairro }}" class="fim-input mudar_coletivo" readonly>
                </div>
            </div>
            <div class="fim-grid-5">
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Rua</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="rua" value="{{ $rua }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Complemento</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="complemento" value="{{ $complemento }}" class="fim-input mudar_coletivo" readonly>
                </div>
                <div class="fim-field">
                    <label class="fim-label">
                        <span>Data Contrato</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="date" id="data_contrato" value="{{ $contrato }}" class="fim-input mudar_coletivo" readonly>
                </div>
            </div>

            {{-- Financeiro --}}
            <div class="fim-section-label">Financeiro</div>
            <div class="fim-grid-5">
                <div class="fim-field">
                    <label class="fim-label"><span>Valor Contrato</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="valor_contrato" readonly value="{{ number_format($valor_plano,2,',','.') }}" class="fim-input mudar_coletivo">
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Valor Adesão</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="valor_adesao" readonly value="{{ number_format($valor_adesao,2,',','.') }}" class="fim-input mudar_coletivo">
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Desc. Corretora</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="desconto_corretora" readonly value="{{ number_format($desconto_corretora,2,',','.') }}" class="fim-input mudar_coletivo">
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Desc. Corretor</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="desconto_corretor" readonly value="{{ number_format($desconto_corretor,2,',','.') }}" class="fim-input mudar_coletivo">
                </div>
                <div class="fim-field">
                    <label class="fim-label"><span>Parcelas</span></label>
                    <input type="text" readonly value="{{ $quantidade_parcelas ?? '-' }}" class="fim-input">
                </div>
            </div>

            <div class="fim-grid-4">
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>Nome Responsável</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="nome_responsavel" value="{{ $dependente_nome }}" readonly class="fim-input mudar_coletivo">
                </div>
                <div class="fim-field fim-col-2">
                    <label class="fim-label">
                        <span>CPF Responsável</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fim-edit-icon editar_coletivo"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                    </label>
                    <input type="text" id="cpf_responsavel" value="{{ $dependente_cpf }}" readonly class="fim-input mudar_coletivo">
                </div>
            </div>

        </form>
    </div>

    {{-- RIGHT: Etapas do contrato --}}
    <div class="fim-panel-right">
        <div class="fim-parcelas-title">Etapas do Contrato</div>
        @php $estagioAtual = $dados->financeiro_id; @endphp

        <table class="fim-parcelas-table">
            <thead>
                <tr>
                    <th>Etapa</th>
                    <th>Contrato</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Baixa</th>
                    <th>Dias</th>
                    <th>Ação</th>
                    <th>Comissão</th>
                    <th>Desfazer</th>
                </tr>
            </thead>
            <tbody>

                {{-- Linha 1: Em Análise --}}
                @php $analiseFeita = !empty($dados->data_analise); @endphp
                <tr class="{{ $analiseFeita ? 'fim-tr-paid' : '' }}">
                    <td><span class="fim-parcelas-badge fin-analise">Em Análise</span></td>
                    <td>{{ $codigo_externo }}</td>
                    <td><span style="color:#253a52">—</span></td>
                    <td><span style="color:#253a52">—</span></td>
                    <td class="data_analise"><span style="color:#253a52">—</span></td>
                    <td style="text-align:center"><span style="color:#253a52">—</span></td>
                    <td class="acao_aqui my-auto" style="text-align:center">
                        @if(!$analiseFeita)
                            <button type="button" data-id="{{ $id }}" class="em_analise fim-workflow-btn fim-workflow-btn-blue">Conferido</button>
                        @else
                            <button type="button" class="cursor-not-allowed" style="background:#05401e;border:1px solid #0a5c2a;border-radius:5px;padding:3px 7px;display:inline-flex;align-items:center;justify-content:center;">
                                <svg style="width:13px;height:13px;color:#5dd8a0" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M15.03 9.684h3.965c.322 0 .64.08.925.232.286.153.532.374.717.645a2.109 2.109 0 0 1 .242 1.883l-2.36 7.201c-.288.814-.48 1.355-1.884 1.355-2.072 0-4.276-.677-6.157-1.256-.472-.145-.924-.284-1.348-.404h-.115V9.478a25.485 25.485 0 0 0 4.238-5.514 1.8 1.8 0 0 1 .901-.83 1.74 1.74 0 0 1 1.21-.048c.396.13.736.397.96.757.225.36.32.788.269 1.211l-1.562 4.63ZM4.177 10H7v8a2 2 0 1 1-4 0v-6.823C3 10.527 3.527 10 4.176 10Z" clip-rule="evenodd"/></svg>
                            </button>
                        @endif
                    </td>
                    <td><span style="color:#253a52">—</span></td>
                    <td style="text-align:center;vertical-align:middle">
                        <svg xmlns="http://www.w3.org/2000/svg" data-id="{{ $id }}" data-fase="1" viewBox="0 0 24 24" fill="currentColor" id="desfazer_1" class="desfazer_individual" style="display:inline-block;vertical-align:middle">
                            <path fill-rule="evenodd" d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    </td>
                </tr>

                {{-- Linha 2: Emissão Boleto --}}
                @php $emissaoFeita = !empty($dados->data_emissao); @endphp
                <tr class="{{ $emissaoFeita ? 'fim-tr-paid' : '' }}">
                    <td><span class="fim-parcelas-badge fin-emissao">Emissão Boleto</span></td>
                    <td>{{ $codigo_externo }}</td>
                    <td><span style="color:#253a52">—</span></td>
                    <td><span style="color:#253a52">—</span></td>
                    <td class="data_emissao"><span style="color:#253a52">—</span></td>
                    <td style="text-align:center"><span style="color:#253a52">—</span></td>
                    <td class="acao_aqui" style="text-align:center">
                        @if(!$emissaoFeita)
                            <button type="button" data-id="{{ $id }}" class="emissao_boleto fim-workflow-btn fim-workflow-btn-purple">Emitido</button>
                        @else
                            <button type="button" class="cursor-not-allowed" style="background:#05401e;border:1px solid #0a5c2a;border-radius:5px;padding:3px 7px;display:inline-flex;align-items:center;justify-content:center;">
                                <svg style="width:13px;height:13px;color:#5dd8a0" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M15.03 9.684h3.965c.322 0 .64.08.925.232.286.153.532.374.717.645a2.109 2.109 0 0 1 .242 1.883l-2.36 7.201c-.288.814-.48 1.355-1.884 1.355-2.072 0-4.276-.677-6.157-1.256-.472-.145-.924-.284-1.348-.404h-.115V9.478a25.485 25.485 0 0 0 4.238-5.514 1.8 1.8 0 0 1 .901-.83 1.74 1.74 0 0 1 1.21-.048c.396.13.736.397.96.757.225.36.32.788.269 1.211l-1.562 4.63ZM4.177 10H7v8a2 2 0 1 1-4 0v-6.823C3 10.527 3.527 10 4.176 10Z" clip-rule="evenodd"/></svg>
                            </button>
                        @endif
                    </td>
                    <td><span style="color:#253a52">—</span></td>
                    <td style="text-align:center;vertical-align:middle">
                        <svg xmlns="http://www.w3.org/2000/svg" data-id="{{ $id }}" data-fase="2" viewBox="0 0 24 24" fill="currentColor" id="desfazer_2" class="desfazer_individual" style="display:inline-block;vertical-align:middle">
                            <path fill-rule="evenodd" d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    </td>
                </tr>

                {{-- Linhas das parcelas --}}
                @php $ii = 0; @endphp
                @foreach($dados->comissao->comissoesLancadas as $kk => $cr)
                    @php
                        [$title, $badgeCls, $fase] = match((int)$cr->parcela) {
                            1 => ['Pag. Adesão',     'fin-adesao',   2],
                            2 => ['Pag. Vigência',   'fin-vigencia', 3],
                            3 => ['Pag. 2ª Parcela', 'fin-p2',       4],
                            4 => ['Pag. 3ª Parcela', 'fin-p3',       5],
                            5 => ['Pag. 4ª Parcela', 'fin-p4',       8],
                            6 => ['Pag. 5ª Parcela', 'fin-p5',       9],
                            7 => ['Pag. 6ª Parcela', 'fin-p6',       11],
                            default => ['---', '', 0]
                        };
                        $isPaid      = !empty($cr->data_baixa);
                        $isCancelled = isset($cr->cancelados) && $cr->cancelados == 1;
                        $trCls       = $isPaid ? 'fim-tr-paid' : '';
                        if ($cr->parcela == 1) {
                            $valor = number_format($dados->valor_adesao, 2, ',', '.');
                        } elseif ($ii <= $quantidade_parcelas) {
                            $valorNum = $dados->valor_plano - ($dados->valor_plano * $operadora_valor / 100);
                            $valor = number_format($valorNum, 2, ',', '.');
                        } else {
                            $valor = number_format($dados->valor_plano, 2, ',', '.');
                        }
                    @endphp
                    <tr class="{{ $trCls }}">
                        <td><span class="fim-parcelas-badge {{ $badgeCls }}">{{ $title }}</span></td>
                        <td>{{ $dados->quantidade_parcelas }}</td>
                        <td>{{ date('d/m/Y', strtotime($cr->data)) }}</td>
                        <td>{{ $valor }}</td>
                        <td class="data_baixa">
                            @if($isCancelled && empty($cr->data_baixa))
                                <span style="color:#ef4444;font-size:10px;font-weight:600">Cancelado</span>
                            @elseif(empty($cr->data_baixa))
                                <span style="color:#253a52">—</span>
                            @else
                                {{ date('d/m/Y', strtotime($cr->data_baixa)) }}
                            @endif
                        </td>
                        <td style="text-align:center">{{ $cr->quantidade_dias }}</td>
                        <td class="acao_aqui" style="text-align:center">
                            @if($cr->status_financeiro == 0)
                                <input type="date" data-id="{{ $id }}"
                                       min="{{ date('Y-m-d', strtotime('1900-01-01')) }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="fim-date-action next date-picker">
                            @else
                                <button type="button" class="cursor-not-allowed" style="background:#05401e;border:1px solid #0a5c2a;border-radius:5px;padding:3px 7px;display:inline-flex;align-items:center;justify-content:center;">
                                    <svg style="width:13px;height:13px;color:#5dd8a0" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M15.03 9.684h3.965c.322 0 .64.08.925.232.286.153.532.374.717.645a2.109 2.109 0 0 1 .242 1.883l-2.36 7.201c-.288.814-.48 1.355-1.884 1.355-2.072 0-4.276-.677-6.157-1.256-.472-.145-.924-.284-1.348-.404h-.115V9.478a25.485 25.485 0 0 0 4.238-5.514 1.8 1.8 0 0 1 .901-.83 1.74 1.74 0 0 1 1.21-.048c.396.13.736.397.96.757.225.36.32.788.269 1.211l-1.562 4.63ZM4.177 10H7v8a2 2 0 1 1-4 0v-6.823C3 10.527 3.527 10 4.176 10Z" clip-rule="evenodd"/></svg>
                                </button>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:3px">
                                <input type="text"
                                       class="valor-comissao-input"
                                       data-id="{{ $cr->id }}"
                                       value="{{ number_format($cr->valor, 2, ',', '.') }}"
                                       style="width:72px;padding:3px 5px;background:#252a3a;border:1px solid #2e3550;border-radius:4px;color:#e0e6f0;text-align:right;font-size:0.78rem;box-sizing:border-box">
                                <button type="button"
                                        class="salvar-comissao-individual"
                                        data-id="{{ $cr->id }}"
                                        title="Salvar comissão"
                                        style="background:#1a3a5c;border:1px solid #2a5a8c;border-radius:4px;padding:2px 6px;color:#7eb8f7;font-size:0.72rem;cursor:pointer;white-space:nowrap">
                                    Salvar
                                </button>
                            </div>
                        </td>
                        <td style="text-align:center;vertical-align:middle">
                            <svg xmlns="http://www.w3.org/2000/svg" data-fase="{{ $fase }}" data-id="{{ $id }}" viewBox="0 0 24 24" fill="currentColor" id="desfazer_{{ $kk+3 }}" class="desfazer_individual" style="display:inline-block;vertical-align:middle">
                                <path fill-rule="evenodd" d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                            </svg>
                        </td>
                    </tr>
                    @php $ii++; @endphp
                @endforeach

            </tbody>
        </table>

        <div class="fim-modal-footer">
            <button data-id="{{ $id }}" class="button_excluir fim-btn-excluir">Excluir Contrato</button>
            <button data-id="{{ $id }}" class="button_cancelar fim-btn-cancelar">Cancelar Contrato</button>
        </div>
    </div>

</div>

<script>
    (function(){
        $("body").find("#valor_contrato").mask('#.##0,00', {reverse: true});
        $("body").find("#valor_fixo").mask('#.##0,00', {reverse: true});
        $("body").find("#valor_vale").mask('#.##0,00', {reverse: true});
    })();
</script>
