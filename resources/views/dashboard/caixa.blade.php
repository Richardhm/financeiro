<x-app-layout>
    <div class="min-h-screen p-6">
        <!-- Header -->
        <header class="glass-card p-6 mb-6 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white rounded-2xl shadow-lg">
            <h1 class="text-lg font-semibold">📊 Caixa da Corretora</h1>
            <p class="text-sm opacity-80">Resumo financeiro das projeções e despesas da corretora.</p>
        </header>

        <!-- Resumo Geral -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="glass-card p-6 rounded-xl shadow-md bg-gradient-to-br from-red-500 to-red-700 text-white">
                <h4 class="text-sm font-medium">
                    Comissoes ja pagas pela corretora
                </h4>
                <p class="text-2xl font-bold mt-2">

                    R$ {{ number_format($despesasPagas, 2, ',', '.') }}
                </p>
            </div>
            <div class="glass-card p-6 rounded-xl shadow-md bg-gradient-to-br from-green-500 to-emerald-600 text-white">
                <h4 class="text-sm font-medium">
                    Corretora já recebeu
                </h4>
                <p class="text-2xl font-bold mt-2">
                    R$ {{ number_format($comissaoJaRecebida, 2, ',', '.') }}
                </p>
            </div>
            <div class="glass-card p-6 rounded-xl shadow-md bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                <h4 class="text-sm font-medium">Projeção de Adiantamento</h4>
                <p class="text-2xl font-bold mt-2">R$ {{ number_format($despesasNaoPagas, 2, ',', '.') }}</p>
            </div>
            <div class="glass-card p-6 rounded-xl shadow-md bg-gradient-to-br from-orange-500 to-yellow-600 text-white">
                <h4 class="text-sm font-medium">Parcelas Não Pagas</h4>
                <p class="text-2xl font-bold mt-2">{{ $quantidadeParcelas }}</p>
            </div>
        </div>

        <!-- Resumo por Vendedor -->
        <div class="glass-card p-6 mb-6 rounded-2xl shadow-lg bg-white/80">
            <h4 class="text-lg font-semibold mb-4 text-gray-800">Resumo por Vendedor</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($resumoVendedores as $vendedor)
                    <div class="glass-card p-4 rounded-xl shadow-md bg-gradient-to-br from-gray-50 to-gray-100 border">
                        <h5 class="text-lg font-bold text-gray-800">{{ $vendedor->vendedor_nome }}</h5>
                        <p class="text-sm text-gray-500">ID: {{ $vendedor->vendedor_id }}</p>
                        <p class="text-sm mt-2 text-green-600 font-semibold">💸 Total Gasto: R$ {{ number_format($vendedor->total_gasto, 2, ',', '.') }}</p>
                        <p class="text-sm mt-1 text-blue-600 font-semibold">📥 Total Recebido: R$ {{ number_format($vendedor->total_recebido, 2, ',', '.') }}</p>
                        <button onclick="verDetalhesVendedor({{ $vendedor->vendedor_id }})"
                                class="mt-3 w-full py-2 px-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">
                            Detalhes
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Gráfico -->
        <div class="glass-card p-6 rounded-2xl shadow-lg bg-white/90">
            <h4 class="text-lg font-semibold mb-4 text-gray-800">Gráfico de Controle Financeiro</h4>
            <canvas id="grafico-financeiro" height="120"></canvas>
        </div>
    </div>


    <script>
        const ctx = document.getElementById('grafico-financeiro');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Controle Financeiro'],
                datasets: [
                    {
                        label: 'Projeção de Despesas',
                        data: [{{ $comissaoJaRecebida }}],
                        backgroundColor: '#22c55e',
                        borderWidth: 1
                    },
                    {
                        label: 'Despesas Pagas',
                        data: [{{ $despesasPagas }}],
                        backgroundColor: '#ef4444',
                        borderWidth: 1
                    },

                    {
                        label: 'Projeção de Adiantamento',
                        data: [{{ $despesasNaoPagas }}],
                        backgroundColor: '#3b82f6',
                        borderWidth: 1
                    },
                    {
                        label: 'Parcelas Não Pagas',
                        data: [{{ $quantidadeParcelas }}],
                        backgroundColor: '#f97316',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Resumo Financeiro da Corretora' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</x-app-layout>
