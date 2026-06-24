<main id="aba_coletivo" class="hidden">

    <div class="fin-toolbar">

        <!-- Linha 1: Indicadores (esq) + Filtros (dir) -->
        <div class="fin-row-1">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Indicadores</legend>
                <ul id="list_coletivo_begin" class="fin-stats-group list-none m-0 p-0">
                    <li class="fin-stat fin-stat-kpi coletivo">Contratos<b class="total_por_orcamento_coletivo">0</b></li>
                    <li class="fin-stat fin-stat-kpi coletivo">Vidas<b class="total_por_vida_coletivo">0</b></li>
                    <li class="fin-stat fin-stat-kpi coletivo">Valor<b class="total_por_page_coletivo">0</b></li>
                </ul>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Filtros</legend>
                <div class="fin-filters-group">
                    @if(auth()->user()->can('listar_todos'))
                        <select id="select_corretoras_coletivo" class="fin-select">
                            <option value="1">Vivaz</option>
                            <option value="2">America</option>
                            <option value="0">Grupo America</option>
                        </select>
                    @endif

                    <div class="fin-corretor-wrapper">
                        <svg class="fin-corretor-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <select id="select_usuario" class="fin-select fin-select-corretor">
                            <option value="todos">-- Todos os Corretores --</option>
                        </select>
                    </div>

                    <select id="select_coletivo_administradoras" class="fin-select fin-select-periodo">
                        <option value="todos">-- Administradora --</option>
                    </select>

                    <select id="mudar_ano_table_coletivo" class="fin-select fin-select-periodo">
                        <option value="">-- Ano --</option>
                    </select>

                    <select id="mudar_mes_table_coletivo" class="fin-select fin-select-periodo">
                        <option value="00">-- Mês --</option>
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
        <div class="fin-row-2" id="content_list_coletivo_begin">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Ações</legend>
                <div class="fin-btns-group">
                    <span class="fin-btn fin-btn-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <a class="text-white" href="{{route('contratos.create.coletivo')}}">Cadastrar</a>
                    </span>
                    <span class="fin-btn" style="background:#6d28d9">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <a class="text-white" href="{{route('pdf.coletivo.upload')}}">Via PDF</a>
                    </span>
                    <span class="fin-btn fin-btn-blue btn_upload_coletivo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        Upload
                    </span>
                </div>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Status</legend>
                <div class="fin-status-group">

                    <ul id="atrasado_corretor_coletivo" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-warn individual">Atrasados <b class="coletivo_quantidade_atrasado">0</b></li>
                    </ul>

                    <ul id="finalizado_corretor_coletivo" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-success individual">Finalizado <b class="quantidade_coletivo_finalizado">0</b></li>
                    </ul>

                    <ul id="grupo_finalizados" class="fin-stats-group list-none m-0 p-0">
                        <li class="fin-stat fin-stat-danger coletivo hover:cursor-pointer" id="cancelado_coletivo">Cancelados <b class="quantidade_coletivo_cancelados">0</b></li>
                    </ul>

                    <button class="fin-btn fin-btn-reset" id="btn-zerar-coletivo" title="Limpar todos os filtros">
                        <svg class="fin-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                        Todos
                    </button>

                    <ul class="fin-stats-group list-none m-0 p-0" id="listar">
                        <li class="fin-stat fin-analise coletivo" id="em_analise_coletivo">Em Análise <b class="coletivo_quantidade_em_analise">0</b></li>
                        <li class="fin-stat fin-emissao coletivo" id="emissao_boleto_coletivo">Emissão Boleto <b class="coletivo_quantidade_emissao_boleto">0</b></li>
                        <li class="fin-stat fin-adesao coletivo" id="pagamento_adesao_coletivo">Pag. Adesão <b class="coletivo_quantidade_pagamento_adesao">0</b></li>
                        <li class="fin-stat fin-vigencia coletivo" id="pagamento_vigencia_coletivo">Pag. Vigência <b class="coletivo_quantidade_pagamento_vigencia">0</b></li>
                        <li class="fin-stat fin-p2 coletivo" id="pagamento_segunda_parcela">2ª Parcela <b class="coletivo_quantidade_segunda_parcela">0</b></li>
                        <li class="fin-stat fin-p3 coletivo" id="pagamento_terceira_parcela">3ª Parcela <b class="coletivo_quantidade_terceira_parcela">0</b></li>
                        <li class="fin-stat fin-p4 coletivo" id="pagamento_quarta_parcela">4ª Parcela <b class="coletivo_quantidade_quarta_parcela">0</b></li>
                        <li class="fin-stat fin-p5 coletivo" id="pagamento_quinta_parcela">5ª Parcela <b class="coletivo_quantidade_quinta_parcela">0</b></li>
                        <li class="fin-stat fin-p6 coletivo" id="pagamento_sexta_parcela">6ª Parcela <b class="coletivo_quantidade_sexta_parcela">0</b></li>
                    </ul>

                </div>
            </fieldset>

        </div>
    </div>

    <!-- Tabela -->
    <div class="fin-table-container">
        <table id="tabela_coletivo" class="table table-sm listardados w-100 text-left" style="table-layout:fixed;">
            <thead>
            <tr>
                <th>Data</th>
                <th>Cod.</th>
                <th>Corretor</th>
                <th>Cliente</th>
                <th>Admin</th>
                <th>CPF</th>
                <th>Vidas</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th>Ver</th>
                <th>Teste</th>
                <th>Data Nasc.</th>
                <th>Celular</th>
                <th>Email</th>
                <th>Cidade</th>
                <th>UF</th>
            </tr>
            </thead>
        </table>
    </div>

</main>
