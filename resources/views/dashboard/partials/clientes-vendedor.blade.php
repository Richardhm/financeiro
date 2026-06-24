{{-- resources/views/dashboard/partials/clientes-vendedor.blade.php --}}
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h4 class="text-xl font-semibold text-gray-900">
                Clientes de {{ $vendedor->name }}
            </h4>
            <p class="text-gray-600">
                @if($tipo === 'pagos')
                    Clientes com todas as comissões pagas
                @elseif($tipo === 'pendentes')
                    Clientes com comissões pendentes
                @else
                    Todos os clientes
                @endif
                ({{ $totalRegistros }} {{ $totalRegistros == 1 ? 'cliente' : 'clientes' }})
            </p>
        </div>
        <button onclick="voltarResumoVendedor({{ $vendedor->id }})"
                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </button>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3 mb-4">
            <button onclick="detalharClientes({{ $vendedor->id }}, 'todos')"
                    class="px-4 py-2 rounded-lg transition-colors duration-200 {{ $tipo === 'todos' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <i class="fas fa-users mr-1"></i>
                Todos
            </button>
            <button onclick="detalharClientes({{ $vendedor->id }}, 'pagos')"
                    class="px-4 py-2 rounded-lg transition-colors duration-200 {{ $tipo === 'pagos' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <i class="fas fa-check-circle mr-1"></i>
                Pagos
            </button>
            <button onclick="detalharClientes({{ $vendedor->id }}, 'pendentes')"
                    class="px-4 py-2 rounded-lg transition-colors duration-200 {{ $tipo === 'pendentes' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                <i class="fas fa-clock mr-1"></i>
                Pendentes
            </button>
        </div>

        {{-- Busca --}}
        <div class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Buscar Cliente
                </label>
                <div class="relative">
                    <input type="text"
                           id="busca-cliente"
                           value="{{ $busca ?? '' }}"
                           placeholder="Digite o nome do cliente..."
                           class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            <div>
                <button onclick="buscarClientes({{ $vendedor->id }}, '{{ $tipo }}')"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
            </div>
            @if($busca)
                <div>
                    <button onclick="limparBusca({{ $vendedor->id }}, '{{ $tipo }}')"
                            class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Limpar
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Lista de Clientes --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($clientes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Plano
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Valor do Plano
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Comissão Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pago
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pendente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($clientes as $cliente)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $cliente->cliente_nome }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Vigência: {{ \Carbon\Carbon::parse($cliente->data_vigencia)->format('d/m/Y') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $cliente->nm_plano }}</div>
                                <div class="text-sm text-gray-500">{{ $cliente->quantidade_parcelas }} parcelas</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                R$ {{ number_format($cliente->valor_plano, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                R$ {{ number_format($cliente->total_comissao, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-green-600">
                                    R$ {{ number_format($cliente->valor_pago, 2, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $cliente->parcelas_pagas }}/{{ $cliente->total_parcelas }} parcelas
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-600">
                                R$ {{ number_format($cliente->valor_pendente, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cliente->status_pagamento === 'pago')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Pago
                                        </span>
                                @elseif($cliente->status_pagamento === 'pendente')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pendente
                                        </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>
                                            Parcial
                                        </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="verDetalheCliente({{ $cliente->cliente_id }}, {{ $vendedor->id }})"
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver Detalhes
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            @if($totalPaginas > 1)
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            @if($page > 1)
                                <button onclick="carregarPaginaClientes({{ $vendedor->id }}, '{{ $tipo }}', {{ $page - 1 }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Anterior
                                </button>
                            @endif
                            @if($page < $totalPaginas)
                                <button onclick="carregarPaginaClientes({{ $vendedor->id }}, '{{ $tipo }}', {{ $page + 1 }})"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Próxima
                                </button>
                            @endif
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando <span class="font-medium">{{ ($page - 1) * $perPage + 1 }}</span> a
                                    <span class="font-medium">{{ min($page * $perPage, $totalRegistros) }}</span> de
                                    <span class="font-medium">{{ $totalRegistros }}</span> resultados
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    @if($page > 1)
                                        <button onclick="carregarPaginaClientes({{ $vendedor->id }}, '{{ $tipo }}', {{ $page - 1 }})"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                    @endif

                                    @for($i = max(1, $page - 2); $i <= min($totalPaginas, $page + 2); $i++)
                                        <button onclick="carregarPaginaClientes({{ $vendedor->id }}, '{{ $tipo }}', {{ $i }})"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i == $page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                                            {{ $i }}
                                        </button>
                                    @endfor

                                    @if($page < $totalPaginas)
                                        <button onclick="carregarPaginaClientes({{ $vendedor->id }}, '{{ $tipo }}', {{ $page + 1 }})"
                                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    @endif
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum cliente encontrado</h3>
                <p class="text-gray-500">
                    @if($busca)
                        Nenhum cliente encontrado para "{{ $busca }}"
                    @else
                        Não há clientes {{ $tipo === 'pagos' ? 'pagos' : ($tipo === 'pendentes' ? 'pendentes' : '') }} para este vendedor
                    @endif
                </p>
                @if($busca)
                    <button onclick="limparBusca({{ $vendedor->id }}, '{{ $tipo }}')"
                            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Limpar busca
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
