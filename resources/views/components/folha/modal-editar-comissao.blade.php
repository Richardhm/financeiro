<div id="modal-editar-comissao" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center transition-all duration-300 opacity-0 transform scale-90" style="z-index: 9999999;">
    <div class="bg-white/10 backdrop-blur-md h-screen rounded-lg shadow-lg w-[50%] p-6">
        <h3 class="text-xl font-bold mb-4 text-white">Editar Comissão</h3>
        <form id="form-editar-comissao">
            <input type="hidden" id="comissao-id">
            <div class="mb-4">
                <span class="block text-white mb-1 flex">Nome Cliente</span>
                <input type="text" id="modal-nome-cliente" class="w-full border rounded p-2 bg-gray-400" disabled>
            </div>
            <div class="mb-4">
                <span class="block text-white mb-1 flex">Codigo Contrato</span>
                <input type="text" id="modal-codigo-contrato" class="w-full border rounded p-2 bg-gray-400" disabled>
            </div>
            <div class="mb-4">
                <span class="block text-white mb-1 flex">Valor Plano</span>
                <input type="text" id="modal-valor-plano" class="w-full border rounded p-2 bg-gray-400" disabled>
            </div>
            <div class="mb-4">
                <span class="block text-white mb-1 flex">% Comissão <span class="text-green-500 uppercase font-bold text-lg">(Campo Editável)</span></span>
                <input type="number" id="modal-porcentagem" class="w-full border rounded p-2" step="0.01">
            </div>
            <div class="mb-4">
                <span class="block text-white mb-1 flex">Valor Comissão</span>
                <input type="text" id="modal-valor-comissao" class="w-full border rounded p-2 bg-gray-400" disabled>
            </div>
            <div class="mb-4 text-white">
                <input type="checkbox" id="zerar_comissao"> Marque essa opção quando for zerar a comissão.
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelar-edicao" class="text-white bg-gradient-to-r from-red-400 via-red-500 to-red-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-lg px-5 py-2.5 text-center me-2 mb-2 w-[300px]">Cancelar</button>
                <button type="submit" class="text-white bg-gradient-to-br from-purple-600 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-lg px-10 py-2.5 text-center me-2 mb-2 w-[300px]">Salvar</button>
            </div>
        </form>
    </div>
</div>


