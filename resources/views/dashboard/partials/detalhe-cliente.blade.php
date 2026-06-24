{{-- resources/views/dashboard/partials/detalhe-cliente.blade.php --}}
<div class="space-y-6">
    {{-- Header do Cliente --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-user-circle text-blue-600 text-xl"></i>
            </div>
            <div>
                <h4 class="text-xl font-semibold text-gray-900">{{ $cliente->nome }}</h4>
                <p class="text-gray-600">{{ $cliente->nm_plano }}</p>
            </div>
        </div>
        <button onclick="fecharDetalheCliente()"
                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
            <i class="fas fa-times mr-2"></i>
            Fechar
        </button>
    </div>

    {{-- Informações do Cliente --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <h5 class="text-lg font-medium text-gray-900 mb-3">Informações do Contrato</h5>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Valor do Plano</p>
                <p class="text-lg font-semibold text-gray-900">
                    R$ {{ number_format($cliente->valor_plano, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Data de Vigência</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($cliente->data_vigencia)->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Quantidade de Parcelas</p>
                <p class="text-lg font-semibold text-gray-900">{{ $cliente->quantidade_parcelas }}</p>
            </div>
        </div>
    </div>

    {{-- Parcelas de Comissão --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-900">Parcelas de Comissão</h5>
        </div>

        @if($parcelas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Parcela
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Valor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Comissão
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Pagamento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($parcelas as $index => $parcela)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $index + 1 }}ª Parcela
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                R$ {{ number_format($parcela->valor, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $parcela->data_comissao ? \Carbon\Carbon::parse($parcela->data_comissao)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $parcela->data_baixa_finalizado ? \Carbon\Carbon::parse($parcela->data_baixa_finalizado)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($parcela->status_descricao === 'Pago')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Pago
                                        </span>
                                @elseif($parcela->status_descricao === 'Pendente')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pendente
                                        </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>
                                            Não Processado
                                        </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-file-invoice text-gray-300 text-4xl mb-4"></i>
                <p class="text-gray-500">Nenhuma parcela encontrada</p>
            </div>
        @endif
    </div>
</div>
