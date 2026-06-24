<main id="aba_odonto" class="hidden">

    <div class="fin-toolbar">

        <!-- Linha 1: Filtros + Ações -->
        <div class="fin-row-1">

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Filtros</legend>
                <div class="fin-filters-group">
                    <div class="fin-corretor-wrapper">
                        <svg class="fin-corretor-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <select id="select_usuario_odonto" class="fin-select fin-select-corretor">
                            <option value="todos">-- Todos os Corretores --</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="fin-fieldset">
                <legend class="fin-legend">Ações</legend>
                <div class="fin-btns-group">
                    <span class="fin-btn fin-btn-teal create_odonto hover:cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fin-btn-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <a class="text-white">Cadastrar</a>
                    </span>
                </div>
            </fieldset>

        </div>

    </div>

    <!-- Tabela -->
    <div class="fin-table-container">
        <table id="tabela_odonto" class="table table-sm listardadosodonto w-100 text-left" style="table-layout:fixed;">
            <thead>
            <tr>
                <th>Data</th>
                <th>Cliente</th>
                <th>Corretor</th>
                <th>Valor</th>
                <th>Excluir</th>
            </tr>
            </thead>
        </table>
    </div>

</main>
