<x-app-layout>
    @section('scripts')
    <script>
        async function gerarPdf(historicoId, tipo) {
            const btn = document.getElementById(`btn-${tipo}-${historicoId}`);
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-pulse">...</span>';

            try {
                const resp = await fetch(`/folha/folha-parceiros/historico/${historicoId}/pdf`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ tipo }),
                });
                const data = await resp.json();
                if (data.success) {
                    window.open(data.download_url, '_blank');
                } else {
                    alert('Erro ao gerar PDF.');
                }
            } catch (e) {
                alert('Erro ao conectar com o servidor.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        }

        // Filtro por parceiro
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('filtro-parceiro');
            if (!input) return;
            input.addEventListener('input', function () {
                const termo = this.value.toLowerCase();
                document.querySelectorAll('.historico-row').forEach(row => {
                    const nome = row.dataset.nome || '';
                    row.style.display = nome.includes(termo) ? '' : 'none';
                });
            });
        });
    </script>
    @endsection

    <div class="p-4">

        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
                        Histórico de Folhas — Parceiros Independentes
                    </h1>
                    <p class="text-sm text-gray-400 mt-0.5">Regenere PDFs de qualquer período finalizado</p>
                </div>
                <a href="{{ route('folha.america.folha-parceiros') }}"
                   class="flex items-center gap-1 px-3 py-1.5 text-xs bg-gray-600 text-white rounded shadow hover:bg-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Voltar
                </a>
            </div>

            <!-- Filtro -->
            <div class="mt-3">
                <input id="filtro-parceiro" type="text" placeholder="Filtrar por parceiro..."
                       class="w-full max-w-xs px-3 py-1.5 text-sm rounded-lg bg-gray-800 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring focus:ring-purple-500">
            </div>
        </div>

        <!-- Tabela -->
        @if($historico->isEmpty())
            <div class="bg-white/10 rounded-xl p-10 text-center text-gray-400">
                <p class="text-lg mb-1">Nenhuma folha finalizada ainda</p>
                <p class="text-sm">Ao finalizar a folha de um parceiro, o registro aparecerá aqui.</p>
            </div>
        @else
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-800/80 text-gray-300 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3">Parceiro</th>
                            <th class="px-4 py-3">Frequência</th>
                            <th class="px-4 py-3">Período</th>
                            <th class="px-4 py-3">Pago em</th>
                            <th class="px-4 py-3 text-center">Parcelas</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">PDF Parceiro</th>
                            <th class="px-4 py-3 text-center">PDF Corretora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @foreach($historico as $i => $h)
                        @php
                            $freqMap = [
                                'semanal'   => ['txt' => 'Semanal',   'cls' => 'bg-blue-600/70 text-blue-100'],
                                'quinzenal' => ['txt' => 'Quinzenal', 'cls' => 'bg-indigo-600/70 text-indigo-100'],
                                'mensal'    => ['txt' => 'Mensal',    'cls' => 'bg-purple-600/70 text-purple-100'],
                            ];
                            $freq = $freqMap[$h->frequencia] ?? ['txt' => ucfirst($h->frequencia), 'cls' => 'bg-gray-600 text-gray-100'];
                            $inicio = \Carbon\Carbon::parse($h->periodo_inicio)->format('d/m/Y');
                            $fim    = \Carbon\Carbon::parse($h->periodo_fim)->format('d/m/Y');
                            $pago   = \Carbon\Carbon::parse($h->data_pagamento)->format('d/m/Y');
                            $rowBg  = $i % 2 === 0 ? 'bg-white/5' : 'bg-white/[0.02]';
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-white/10 transition historico-row"
                            data-nome="{{ strtolower($h->parceiro_nome) }}">
                            <td class="px-4 py-3 text-white font-medium">
                                {{ implode(' ', array_slice(explode(' ', $h->parceiro_nome), 0, 3)) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold {{ $freq['cls'] }}">
                                    {{ $freq['txt'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-300 text-xs">
                                {{ $inicio }} — {{ $fim }}
                            </td>
                            <td class="px-4 py-3 text-gray-300 text-xs">{{ $pago }}</td>
                            <td class="px-4 py-3 text-center text-gray-200">{{ $h->total_parcelas }}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-400">
                                R$ {{ number_format($h->total_valor, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button id="btn-parceiro-{{ $h->id }}"
                                        onclick="gerarPdf({{ $h->id }}, 'parceiro')"
                                        class="flex items-center gap-1 mx-auto px-2 py-1 text-xs bg-green-600/80 text-white rounded hover:bg-green-600 disabled:opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    PDF
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button id="btn-corretora-{{ $h->id }}"
                                        onclick="gerarPdf({{ $h->id }}, 'corretora')"
                                        class="flex items-center gap-1 mx-auto px-2 py-1 text-xs bg-blue-600/80 text-white rounded hover:bg-blue-600 disabled:opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    PDF
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
