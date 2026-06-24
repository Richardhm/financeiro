<x-app-layout>
    <div class="min-h-screen p-6">
        <div class="max-w-7xl mx-auto">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Caixa da Corretora</h1>
                <p class="text-gray-600 mt-2">Controle financeiro e comissões</p>
            </div>

            {{-- Filtros --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <form id="filtros-form" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-48">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Plano
                            </label>
                            <select name="plano_id" id="plano_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos os Planos</option>
                                <option value="coletivo">Coletivo</option>
                                <option value="individual">Individual</option>
                                <option value="empresarial">Empresarial</option>
                            </select>
                        </div>
                        <div>
                            <button type="button" onclick="aplicarFiltros()"
                                    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                <i class="fas fa-filter mr-2"></i>
                                Filtrar
                            </button>
                        </div>
                        <div>
                            <button type="button" onclick="limparFiltros()"
                                    class="px-6 py-2 bg-gray-500 text-white font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>
                                Limpar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Indicadores --}}
            <div class="mb-8" id="indicadores-container">
                @include('dashboard.partials.indicadores', ['indicadores' => $indicadores])
            </div>

            {{-- Gráficos --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Gráfico Movimento Mensal --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                            Movimento Mensal da Corretora
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Entradas vs Saídas</p>
                    </div>
                    <div class="p-6">
                        <div class="relative h-80">
                            <canvas id="grafico-corretora"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Gráfico por Planos --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                            Recebimentos por Plano
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Segmentação mensal</p>
                    </div>
                    <div class="p-6">
                        <div class="relative h-80">
                            <canvas id="grafico-planos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listagem de Vendedores --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- Lista de Vendedores --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-users text-purple-500 mr-2"></i>
                                Vendedores
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">{{ count($vendedores) }} vendedores</p>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2" id="lista-vendedores">
                                @foreach($vendedores as $vendedor)
                                    <button onclick="carregarResumoVendedor({{ $vendedor->id }})"
                                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-blue-50 transition-all duration-200 vendedor-item"
                                            data-vendedor-id="{{ $vendedor->id }}">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $vendedor->name }}</p>
                                                <p class="text-sm text-gray-500">Vendedor</p>
                                            </div>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Resumo do Vendedor --}}
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-user-circle text-indigo-500 mr-2"></i>
                                Resumo do Vendedor
                            </h3>
                        </div>
                        <div class="p-6" id="resumo-vendedor">
                            <div class="text-center py-12">
                                <i class="fas fa-user-plus text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500">Selecione um vendedor para ver o resumo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Carregando...</span>
        </div>
    </div>

    @section('scripts')
        <script src="{{ asset('js/dashboard-caixa.js') }}"></script>
        <script>
            // Inicializar gráficos
            const graficos = @json($graficos);
            inicializarGraficos(graficos);
        </script>
    @endsection
</x-app-layout>
