<main id="aba_individual" class="block active-tab">

    <div class="fin-toolbar">

        <!-- Linha 1: Indicadores (esq) + Filtros (dir) -->
        <div class="fin-row-1">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Indicadores</legend>
                <ul class="fin-stats-group list-none m-0 p-0" id="list_individual_begin">
                    <li class="fin-stat fin-stat-kpi individual">Contratos<b class="total_por_orcamento">0</b></li>
                    <li class="fin-stat fin-stat-kpi individual">Vidas<b class="total_por_vida">0</b></li>
                    <li class="fin-stat fin-stat-kpi individual">Valor<b class="total_por_page">0</b></li>
                </ul>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Filtros</legend>
                <div class="fin-filters-group">
                    @if(auth()->user()->can('listar_todos'))
                        <select id="select_corretoras" class="fin-select">
                            <option value="1" {{auth()->user()->corretora_id && auth()->user()->corretora_id == 1 ? "selected" : ""}}>Vivaz</option>
                            <option value="2" {{auth()->user()->corretora_id && auth()->user()->corretora_id == 2 ? "selected" : ""}}>America</option>
                            <option value="0">Grupo America</option>
                        </select>
                    @endif
                    <div class="fin-corretor-wrapper">
                        <svg class="fin-corretor-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <select id="select_usuario_individual" class="fin-select fin-select-corretor">
                            <option value="" data-id="">Todos os Corretores</option>
                            @foreach($usuariosindividuais as $ui)
                                <option value="{{$ui->name}}" data-id="{{$ui->id}}" data-corretora="{{$ui->corretora_id}}">{{$ui->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <select id="mudar_ano_table" class="fin-select fin-select-periodo">
                        <option value="">-- Ano --</option>
                    </select>
                    <select id="mudar_mes_table" class="fin-select fin-select-periodo">
                        <option value="">-- Mês --</option>
                    </select>
                </div>
            </fieldset>

        </div>

        <!-- Linha 2: Ações (esq) + Status (dir) -->
        <div class="fin-row-2" id="content_list_individual_begin">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Ações</legend>
                <div class="fin-btns-group">
                    <a href="{{ route('individual.manual.create') }}" class="fin-btn fin-btn-teal" style="text-decoration:none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                        Cadastrar
                    </a>
                    <span class="fin-btn fin-btn-blue modal_upload">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        Upload
                    </span>
                    <span class="fin-btn fin-btn-green btn-atualizar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        Atualizar
                    </span>
                    <span class="fin-btn fin-btn-red btn-cancelados">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        Cancelados
                    </span>
                    <span class="fin-btn fin-btn-orange btn-adiantamento">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        Adiantamento
                    </span>
                    <span class="fin-btn fin-btn-purple btn-parcelas">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                        Parcelas
                    </span>
                    <span class="fin-btn fin-btn-amber btn-estorno">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                        Estorno
                    </span>
                </div>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Status</legend>
                <div class="fin-status-group">

                    <ul id="atrasado_corretor" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-warn individual">Atrasados <b class="individual_quantidade_atrasado">0</b></li>
                    </ul>

                    <div id="finalizado_corretor" class="hover:cursor-pointer">
                        <ul id="aguardando_pagamento_6_parcela_individual" class="fin-stats-group list-none m-0 p-0">
                            <li class="fin-stat fin-stat-success individual">Finalizado <b class="individual_quantidade_6_parcela">0</b></li>
                        </ul>
                    </div>

                    <ul id="cancelado_corretor" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-danger individual" id="cancelado_individual">Cancelados <b class="individual_quantidade_cancelado">0</b></li>
                    </ul>

                    <button class="fin-btn fin-btn-reset" id="btn-zerar-individual" title="Limpar todos os filtros">
                        <svg class="fin-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                        Todos
                    </button>

                    <ul id="listar_individual" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-p1 individual" id="aguardando_pagamento_1_parcela_individual">1ª Parcela <b class="individual_quantidade_1_parcela">0</b></li>
                        <li class="fin-stat fin-p2 individual" id="aguardando_pagamento_2_parcela_individual">2ª Parcela <b class="individual_quantidade_2_parcela">0</b></li>
                        <li class="fin-stat fin-p3 individual" id="aguardando_pagamento_3_parcela_individual">3ª Parcela <b class="individual_quantidade_3_parcela">0</b></li>
                        <li class="fin-stat fin-p4 individual" id="aguardando_pagamento_4_parcela_individual">4ª Parcela <b class="individual_quantidade_4_parcela">0</b></li>
                        <li class="fin-stat fin-p5 individual" id="aguardando_pagamento_5_parcela_individual">5ª Parcela <b class="individual_quantidade_5_parcela">0</b></li>
                    </ul>

                </div>
            </fieldset>

        </div>
    </div>

    <!-- Tabela -->
    <div class="fin-table-container">
        <table id="tabela_individual" class="table table-sm listarindividual w-100 text-left" style="table-layout:fixed;">
            <thead>
            <tr>
                <th>Data</th>
                <th>Cod.</th>
                <th>Corretor</th>
                <th>Cliente</th>
                <th>CPF</th>
                <th>Vidas</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Atrasado</th>
                <th>Status</th>
                <th>Nasci.</th>
                <th>Fone</th>
                <th>Ver</th>
                <th>Atrasado</th>
                <th>Estagiario</th>
                <th>User_id</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</main>
