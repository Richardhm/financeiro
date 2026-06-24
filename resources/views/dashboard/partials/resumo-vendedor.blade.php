{{-- resources/views/dashboard/partials/resumo-vendedor.blade.php --}}
<div class="space-y-6">
    {{-- Header do Vendedor --}}
    <div class="flex items-center space-x-4">
        <div class="bg-indigo-100 rounded-full p-3">
            <i class="fas fa-user text-indigo-600 text-xl"></i>
        </div>
        <div>
            <h4 class="text-xl font-semibold text-gray-900">{{ $vendedor->name }}</h4>
            <p class="text-gray-600">Vendedor</p>
        </div>
    </div>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-lg p-4 border border-emerald-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-700 text-sm font-medium">Total Pago</p>
                    <p class="text-xl font-bold text-emerald-800 mt-1">
                        R$ {{ number_format($resumo->total_pago, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-emerald-200 rounded-full p-2">
                    <i class="fas fa-dollar-sign text-emerald-700"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-700 text-sm font-medium">Corretora Recebeu</p>
                    <p class="text-xl font-bold text-blue-800 mt-1">
                        R$ {{ number_format($resumo->total_recebido_corretora, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-blue-200 rounded-full p-2">
                    <i class="fas fa-building text-blue-700"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-700 text-sm font-medium">Pendente</p>
                    <p class="text-xl font-bold text-yellow-800 mt-1">
                        R$ {{ number_format($resumo->total_pendente, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-yellow-200 rounded-full p-2">
                    <i class="fas fa-clock text-yellow-700"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-700 text-sm font-medium">Contratos</p>
                    <p class="text-xl font-bold text-purple-800 mt-1">
                        {{ $resumo->total_contratos }}
                    </p>
                </div>
                <div class="bg-purple-200 rounded-full p-2">
                    <i class="fas fa-file-contract text-purple-700"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Contadores de Clientes --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <h5 class="text-lg font-medium text-gray-900 mb-3">Resumo de Clientes</h5>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $contadores->total_clientes }}</p>
                <p class="text-sm text-gray-600">Total</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">{{ $contadores->clientes_pagos }}</p>
                <p class="text-sm text-gray-600">Pagos</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-yellow-600">{{ $contadores->clientes_pendentes }}</p>
                <p class="text-sm text-gray-600">Pendentes</p>
            </div>
        </div>
    </div>

    {{-- Gráfico do Vendedor --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <h5 class="text-lg font-medium text-gray-900 mb-4">Movimento Mensal</h5>
        <div class="relative h-64">
            <canvas id="grafico-vendedor-{{ $vendedor->id }}" width="400" height="200"></canvas>
        </div>
    </div>

    {{-- Botões de Ação --}}
    <div class="flex flex-wrap gap-3">
        <button onclick="detalharClientes({{ $vendedor->id }}, 'todos')"
                class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
            <i class="fas fa-users mr-2"></i>
            Todos os Clientes ({{ $contadores->total_clientes }})
        </button>
        <button onclick="detalharClientes({{ $vendedor->id }}, 'pagos')"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
            <i class="fas fa-check-circle mr-2"></i>
            Clientes Pagos ({{ $contadores->clientes_pagos }})
        </button>
        <button onclick="detalharClientes({{ $vendedor->id }}, 'pendentes')"
                class="flex-1 bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-colors duration-200">
            <i class="fas fa-clock mr-2"></i>
            Clientes Pendentes ({{ $contadores->clientes_pendentes }})
        </button>
    </div>
</div>

{{-- resources/views/dashboard/partials/resumo-vendedor.blade.php - Seção do gráfico --}}

{{-- Gráfico do Vendedor --}}


{{-- Script atualizado --}}
<script>
    // Criar gráfico específico do vendedor
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado, criando gráfico para vendedor {{ $vendedor->id }}');
        console.log('Dados do gráfico:', @json($graficoMensal));

        // Aguardar um pouco para garantir que o canvas esteja disponível
        setTimeout(() => {
            const canvas = document.getElementById('grafico-vendedor-{{ $vendedor->id }}');
            if (canvas) {
                console.log('Canvas encontrado, criando gráfico...');
                criarGraficoVendedor({{ $vendedor->id }}, @json($graficoMensal));
            } else {
                console.error('Canvas não encontrado: grafico-vendedor-{{ $vendedor->id }}');
            }
        }, 300);
    });
</script>





