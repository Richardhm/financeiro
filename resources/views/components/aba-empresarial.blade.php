<main id="aba_empresarial" class="hidden">

    <div class="fin-toolbar">

        <!-- Linha 1: Indicadores (esq) + Filtros (dir) -->
        <div class="fin-row-1">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Indicadores</legend>
                <ul id="list_empresarial_begin" class="fin-stats-group list-none m-0 p-0">
                    <li class="fin-stat fin-stat-kpi empresarial">Contratos<b class="total_por_orcamento_empresarial">0</b></li>
                    <li class="fin-stat fin-stat-kpi empresarial">Vidas<b class="total_por_vida_empresarial">0</b></li>
                    <li class="fin-stat fin-stat-kpi empresarial">Valor<b class="total_por_page_empresarial">0</b></li>
                </ul>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Filtros</legend>
                <div class="fin-filters-group">
                    @if(auth()->user()->can('listar_todos'))
                        <select id="select_corretoras_empresarial" class="fin-select">
                            <option value="1">Vivaz</option>
                            <option value="2">America</option>
                            <option value="0">GrupoAmerica</option>
                        </select>
                    @endif

                    <div class="fin-corretor-wrapper">
                        <svg class="fin-corretor-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <select name="mudar_user_empresarial" id="mudar_user_empresarial" class="fin-select fin-select-corretor">
                            <option value="todos" data-id="0">-- Todos os Corretores --</option>
                        </select>
                    </div>

                    <select name="mudar_planos_empresarial" id="mudar_planos_empresarial" class="fin-select fin-select-periodo">
                        <option value="todos" data-id="0">-- Plano --</option>
                    </select>

                    <select id="mudar_ano_table_empresarial" class="fin-select fin-select-periodo">
                        <option value="">-- Ano --</option>
                    </select>

                    <select id="mudar_mes_table_empresarial" class="fin-select fin-select-periodo">
                        <option value="">-- Mês --</option>
                        <option value="01">Janeiro</option>
                        <option value="02">Fevereiro</option>
                        <option value="03">Março</option>
                        <option value="04">Abril</option>
                        <option value="05">Maio</option>
                        <option value="06">Junho</option>
                        <option value="07">Julho</option>
                        <option value="08">Agosto</option>
                        <option value="09">Setembro</option>
                        <option value="10">Outubro</option>
                        <option value="11">Novembro</option>
                        <option value="12">Dezembro</option>
                    </select>
                </div>
            </fieldset>

        </div>

        <!-- Linha 2: Ações (esq) + Status (dir) -->
        <div class="fin-row-2" id="content_list_empresarial_begin">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Ações</legend>
                <div class="fin-btns-group">
                    <span class="fin-btn fin-btn-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <a class="text-white" href="{{route('pdf.empresarial.upload')}}">Cadastrar via PDF</a>
                    </span>
                    <span class="fin-btn" style="background:#4a5568">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                        <a class="text-white" href="{{route('contratos.create.empresarial')}}">Cadastrar Manual</a>
                    </span>
                    <span class="fin-btn fin-btn-blue modal_upload_empresarial">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        Upload
                    </span>
                </div>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Status</legend>
                <div class="fin-status-group">

                    <ul id="atrasado_corretor_empresarial" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-warn empresarial">Atrasados <b class="empresarial_quantidade_atrasado">0</b></li>
                    </ul>

                    <ul id="finalizado_corretor_empresarial" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-success empresarial">Finalizado <b class="quantidade_empresarial_finalizado">0</b></li>
                    </ul>

                    <ul id="aguardando_cancelado_empresarial" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-danger empresarial">Cancelado <b class="empresarial_quantidade_cancelado">0</b></li>
                    </ul>

                    <button class="fin-btn fin-btn-reset" id="btn-zerar-empresarial" title="Limpar todos os filtros">
                        <svg class="fin-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                        Todos
                    </button>

                    <ul id="listar_empresarial" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-analise empresarial" id="aguardando_em_analise_empresarial">Em Análise <b class="empresarial_quantidade_em_analise">0</b></li>
                        <li class="fin-stat fin-p1 empresarial" id="aguardando_pagamento_1_parcela_empresarial">1ª Parcela <b class="empresarial_quantidade_1_parcela">0</b></li>
                        <li class="fin-stat fin-p2 empresarial" id="aguardando_pagamento_2_parcela_empresarial">2ª Parcela <b class="empresarial_quantidade_2_parcela">0</b></li>
                        <li class="fin-stat fin-p3 empresarial" id="aguardando_pagamento_3_parcela_empresarial">3ª Parcela <b class="empresarial_quantidade_3_parcela">0</b></li>
                        <li class="fin-stat fin-p4 empresarial" id="aguardando_pagamento_4_parcela_empresarial">4ª Parcela <b class="empresarial_quantidade_4_parcela">0</b></li>
                        <li class="fin-stat fin-p5 empresarial" id="aguardando_pagamento_5_parcela_empresarial">5ª Parcela <b class="empresarial_quantidade_5_parcela">0</b></li>
                        <li class="fin-stat fin-p6 empresarial" id="aguardando_pagamento_6_parcela_empresarial">6ª Parcela <b class="empresarial_quantidade_6_parcela">0</b></li>
                    </ul>

                </div>
            </fieldset>

        </div>
    </div>

    <!-- Tabela -->
    <div class="fin-table-container">
        <table id="tabela_empresarial" class="table table-sm w-100 text-left listarempresarial" style="table-layout: fixed;">
            <thead>
            <tr>
                <th>Data</th>
                <th>Cod.</th>
                <th>Corretor</th>
                <th>Cliente</th>
                <th>CNPJ</th>
                <th>Vidas</th>
                <th>Valor</th>
                <th>Plano</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th>Ver</th>
                <th>Resposta</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</main>
