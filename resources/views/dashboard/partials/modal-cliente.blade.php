{{-- resources/views/dashboard/partials/modal-cliente.blade.php --}}
<div id="modal-cliente" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-screen overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Detalhes do Cliente</h3>
            <button onclick="fecharModalCliente()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="conteudo-modal-cliente" class="p-6">
            <!-- Conteúdo será carregado dinamicamente -->
        </div>
    </div>
</div>
