<div id="modal-clientes" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center" style="z-index: 9999999;">
    <div class="bg-white w-[90%] lg:w-[70%] h-[80%] rounded-lg shadow-md p-6 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 id="modal-titulo" class="text-xl font-semibold text-gray-700">Clientes</h2>
            <button onclick="fecharModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>

        <!-- Campo de pesquisa -->
        <input type="text" id="pesquisa-modal"
               class="w-full p-2 border rounded-md mb-4"
               placeholder="Pesquisar cliente...">

        <!-- Tabela de clientes -->
        <div class="overflow-x-auto max-h-[70%]">
            <table class="table-auto w-full text-sm text-left border-collapse">
                <thead class="bg-gray-200 text-gray-600">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Administradora</th>
                    <th class="px-4 py-2">Data</th>
                    <th class="px-4 py-2">Cod.</th>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Corretor</th>
                    <th class="px-4 py-2">Parcela</th>
                    <th class="px-4 py-2">Vencimento</th>
                    <th class="px-4 py-2">Valor Plano</th>
                </tr>
                </thead>
                <tbody id="modal-tabela-clientes" class="text-gray-700"></tbody>
            </table>
        </div>
    </div>
</div>
