<x-app-layout>
    @section('scripts')
    <script>
    function toggleMes(id) {
        const el = document.getElementById('detalhe-' + id);
        const icon = document.getElementById('icon-' + id);
        el.classList.toggle('hidden');
        icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('filtro-mes');
        if (!input) return;
        input.addEventListener('input', function () {
            const t = this.value.toLowerCase();
            document.querySelectorAll('.mes-row').forEach(row => {
                row.style.display = (row.dataset.mes || '').includes(t) ? '' : 'none';
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
                    <h1 class="text-xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent">
                        Histórico de Folhas — CLT / PJ
                    </h1>
                    <p class="text-sm text-gray-400 mt-0.5">Meses finalizados com totais por vendedor</p>
                </div>
                <a href="{{ route('folha.america.index') }}"
                   class="flex items-center gap-1 px-3 py-1.5 text-xs bg-gray-600 text-white rounded shadow hover:bg-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Voltar
                </a>
            </div>
            <div class="mt-3">
                <input id="filtro-mes" type="text" placeholder="Filtrar por mês (ex: junho, 2025)..."
                       class="w-full max-w-xs px-3 py-1.5 text-sm rounded-lg bg-gray-800 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-500">
            </div>
        </div>

        @if($historico->isEmpty())
            <div class="bg-white/10 rounded-xl p-10 text-center text-gray-400">
                <p class="text-lg mb-1">Nenhum mês finalizado ainda</p>
                <p class="text-sm">Ao finalizar um mês na folha CLT/PJ, ele aparecerá aqui.</p>
            </div>
        @else
        <div class="space-y-3">
            @foreach($historico as $i => $h)
            @php
                $tipoMap = ['pj' => ['txt'=>'PJ','cls'=>'bg-blue-600/70 text-blue-100'], 'clt' => ['txt'=>'CLT','cls'=>'bg-green-600/70 text-green-100']];
                $pdfMesUrl = route('folha.america.historico.pdf', ['mes' => $h->mes_raw]);
            @endphp
            <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg overflow-hidden mes-row"
                 data-mes="{{ strtolower($h->mes_fmt) }}">

                <!-- Cabeçalho do mês (clicável para expandir) -->
                <div class="flex items-center justify-between px-5 py-3 hover:bg-white/5 transition">
                    <!-- Clique na esquerda abre/fecha -->
                    <button onclick="toggleMes({{ $i }})" class="flex items-center gap-4 flex-1 text-left min-w-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-blue-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                            {{ \Carbon\Carbon::parse($h->mes_raw)->format('m') }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-white font-bold text-sm">{{ $h->mes_fmt }}</div>
                            <div class="text-gray-400 text-xs">
                                {{ $h->total_corretores }} vendedor(es) · {{ $h->total_parcelas }} parcela(s)
                            </div>
                        </div>
                    </button>

                    <!-- Totais + botão baixar mês -->
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="text-right hidden sm:block">
                            <div class="text-[11px] text-gray-400">Bruto</div>
                            <div class="text-sm font-semibold text-blue-300">R$ {{ number_format($h->total_bruto, 2, ',', '.') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] text-gray-400">Líquido</div>
                            <div class="text-sm font-bold text-emerald-400">R$ {{ number_format($h->total_liquido, 2, ',', '.') }}</div>
                        </div>

                        <!-- Baixar folha do mês completo -->
                        <a href="{{ $pdfMesUrl }}" target="_blank" title="Baixar folha completa de {{ $h->mes_fmt }}"
                           class="flex items-center gap-1 px-2.5 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg shadow transition whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            PDF Mês
                        </a>

                        <button onclick="toggleMes({{ $i }})" class="p-1 rounded hover:bg-white/10">
                            <svg id="icon-{{ $i }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor"
                                 class="size-5 text-gray-400 transition-transform duration-200">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Detalhe por vendedor — fechado por default -->
                <div id="detalhe-{{ $i }}" class="hidden">
                    @if($h->corretores->isEmpty())
                        <div class="px-5 py-4 text-gray-400 text-sm">Nenhum vendedor com parcelas finalizadas neste mês.</div>
                    @else
                    <div class="overflow-x-auto border-t border-white/10">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-800/60 text-gray-300 text-xs">
                                <tr>
                                    <th class="px-4 py-2">Vendedor</th>
                                    <th class="px-4 py-2 text-center">Tipo</th>
                                    <th class="px-4 py-2 text-center">Parcelas</th>
                                    <th class="px-4 py-2 text-right">Comissão</th>
                                    <th class="px-4 py-2 text-right">Premiação</th>
                                    <th class="px-4 py-2 text-right">Fixo (−)</th>
                                    <th class="px-4 py-2 text-right">Vale (−)</th>
                                    <th class="px-4 py-2 text-right font-bold text-emerald-300">Líquido</th>
                                    <th class="px-4 py-2 text-center">PDF</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/40">
                                @foreach($h->corretores as $j => $c)
                                @php
                                    $bg = $j % 2 === 0 ? 'bg-white/[0.03]' : '';
                                    $pdfVendUrl = route('folha.america.historico.pdf', ['mes' => $h->mes_raw, 'user_id' => $c->id]);
                                @endphp
                                <tr class="{{ $bg }} hover:bg-white/10 transition">
                                    <td class="px-4 py-2 text-white font-medium">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs">
                                                {{ substr($c->name, 0, 1) }}
                                            </div>
                                            {{ implode(' ', array_slice(explode(' ', $c->name), 0, 3)) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @php $tipo = $tipoMap[$c->tipo_contrato] ?? ['txt' => strtoupper($c->tipo_contrato ?? '-'), 'cls' => 'bg-gray-600 text-gray-100']; @endphp
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $tipo['cls'] }}">
                                            {{ $tipo['txt'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center text-gray-300">{{ $c->total_parcelas }}</td>
                                    <td class="px-4 py-2 text-right text-blue-300">
                                        R$ {{ number_format($c->total_comissao, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-green-400">
                                        @if($c->premiacao > 0) R$ {{ number_format($c->premiacao, 2, ',', '.') }} @else <span class="text-gray-600">—</span> @endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-red-400">
                                        @if($c->fixo > 0) R$ {{ number_format($c->fixo, 2, ',', '.') }} @else <span class="text-gray-600">—</span> @endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-red-400">
                                        @if($c->vale > 0) R$ {{ number_format($c->vale, 2, ',', '.') }} @else <span class="text-gray-600">—</span> @endif
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold text-emerald-400">
                                        R$ {{ number_format($c->total_liquido, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <a href="{{ $pdfVendUrl }}" target="_blank"
                                           title="Baixar folha de {{ $c->name }}"
                                           class="inline-flex items-center gap-1 px-2 py-1 text-[11px] bg-blue-600/70 hover:bg-blue-500 text-white rounded transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-800/40 text-xs text-gray-300">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 font-semibold">Total do mês</td>
                                    <td class="px-4 py-2 text-right font-semibold text-blue-300">
                                        R$ {{ number_format($h->total_bruto, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-green-400">
                                        R$ {{ number_format($h->corretores->sum('premiacao'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-red-400">
                                        R$ {{ number_format($h->corretores->sum('fixo'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-red-400">
                                        R$ {{ number_format($h->corretores->sum('vale'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold text-emerald-400">
                                        R$ {{ number_format($h->total_liquido, 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</x-app-layout>
