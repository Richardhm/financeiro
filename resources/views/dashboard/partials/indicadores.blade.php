{{-- resources/views/dashboard/partials/indicadores.blade.php --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    {{-- Comissões Pagas --}}
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Comissões Pagas</p>
                <p class="text-2xl font-bold mt-1">
                    R$ {{ number_format($indicadores['comissoes_pagas'], 2, ',', '.') }}
                </p>
            </div>
            <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-green-100 text-sm">
            <i class="fas fa-arrow-up mr-1"></i>
            <span>Pagamentos finalizados</span>
        </div>
    </div>

    {{-- Comissões Não Pagas --}}
    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-yellow-100 text-sm font-medium">Não Pagas</p>
                <p class="text-2xl font-bold mt-1">
                    R$ {{ number_format($indicadores['comissoes_nao_pagas'], 2, ',', '.') }}
                </p>
            </div>
            <div class="bg-yellow-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-yellow-100 text-sm">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <span>Pendente de pagamento</span>
        </div>
    </div>

    {{-- Recebidas Não Repassadas --}}
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Recebidas Não Repassadas</p>
                <p class="text-2xl font-bold mt-1">
                    R$ {{ number_format($indicadores['comissoes_recebidas_nao_repassadas'], 2, ',', '.') }}
                </p>
            </div>
            <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-exchange-alt text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-blue-100 text-sm">
            <i class="fas fa-hourglass-half mr-1"></i>
            <span>Aguardando repasse</span>
        </div>
    </div>

    {{-- Corretora Recebeu --}}
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-medium">Corretora Recebeu</p>
                <p class="text-2xl font-bold mt-1">
                    R$ {{ number_format($indicadores['corretora_recebeu'], 2, ',', '.') }}
                </p>
            </div>
            <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-building text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-purple-100 text-sm">
            <i class="fas fa-arrow-down mr-1"></i>
            <span>Total recebido</span>
        </div>
    </div>

    {{-- Corretora Não Recebeu --}}
    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm font-medium">Não Recebeu</p>
                <p class="text-2xl font-bold mt-1">
                    R$ {{ number_format($indicadores['corretora_nao_recebeu'], 2, ',', '.') }}
                </p>
            </div>
            <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-red-100 text-sm">
            <i class="fas fa-arrow-down mr-1"></i>
            <span>Pendente de recebimento</span>
        </div>
    </div>
</div>
