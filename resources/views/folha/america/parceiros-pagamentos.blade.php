<x-app-layout>
    <div class="p-4 max-w-5xl mx-auto">

        {{-- Cabeçalho --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
                        Pagamentos de Parceiros
                    </h1>
                    <p class="text-sm text-gray-300">Gere pagamentos para parceiros com base na frequência configurada</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('folha.america.parceiros-config') }}"
                       class="px-3 py-1 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">
                        Configurações
                    </a>
                    <a href="{{ route('folha.america.index') }}"
                       class="px-3 py-1 bg-gray-700 text-white rounded shadow hover:bg-gray-600 text-sm">
                        Folha
                    </a>
                </div>
            </div>
        </div>

        {{-- Seletor de data + Preview --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
            <div class="flex items-end gap-4">
                <div>
                    <label class="text-gray-300 text-sm block mb-1">Data de referência</label>
                    <input type="date" id="inp-data" value="{{ date('Y-m-d') }}"
                           class="bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-purple-500">
                </div>
                <button onclick="carregarPreview()"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow text-sm font-semibold">
                    Verificar parceiros do dia
                </button>
            </div>
        </div>

        {{-- Preview --}}
        <div id="area-preview" class="hidden mb-6">
            <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-white/5 flex items-center justify-between">
                    <h2 class="text-white font-semibold text-sm" id="titulo-preview"></h2>
                    <button id="btn-gerar"
                            onclick="gerarPagamentos()"
                            class="px-4 py-1 bg-green-600 hover:bg-green-700 text-white rounded shadow text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed">
                        Gerar Pagamentos
                    </button>
                </div>
                <table class="w-full text-sm text-white">
                    <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Parceiro</th>
                            <th class="px-4 py-3 text-left">Frequência</th>
                            <th class="px-4 py-3 text-right">Parcelas pendentes</th>
                            <th class="px-4 py-3 text-right">Total a pagar</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-preview" class="divide-y divide-white/10"></tbody>
                </table>
            </div>
        </div>

        {{-- Mensagem sem parceiros --}}
        <div id="area-vazio" class="hidden mb-6">
            <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-6 text-center text-gray-400 text-sm">
                Nenhum parceiro com pagamento previsto para a data selecionada.
            </div>
        </div>

        {{-- Resultado da geração --}}
        <div id="area-resultado" class="hidden mb-6"></div>

        {{-- Histórico --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
            <div class="px-4 py-3 bg-white/5 flex items-center justify-between">
                <h2 class="text-white font-semibold text-sm">Histórico de Pagamentos</h2>
                <button onclick="carregarHistorico()"
                        class="text-gray-400 hover:text-white text-xs underline">
                    Atualizar
                </button>
            </div>
            <table class="w-full text-sm text-white">
                <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Parceiro</th>
                        <th class="px-4 py-3 text-left">Data</th>
                        <th class="px-4 py-3 text-right">Valor pago</th>
                    </tr>
                </thead>
                <tbody id="tbody-historico" class="divide-y divide-white/10">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-center text-gray-500 text-xs">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @section('scripts')
    <script>
        const urlPreview  = '{{ route("folha.america.parceiros.pagamentos.preview") }}';
        const urlGerar    = '{{ route("folha.america.parceiros.pagamentos.gerar") }}';
        const urlHistorico= '{{ route("folha.america.parceiros.pagamentos.historico") }}';
        const csrfToken   = '{{ csrf_token() }}';

        function carregarPreview() {
            const data = document.getElementById('inp-data').value;
            fetch(urlPreview + '?data=' + data)
                .then(r => r.json())
                .then(res => {
                    document.getElementById('area-preview').classList.add('hidden');
                    document.getElementById('area-vazio').classList.add('hidden');
                    document.getElementById('area-resultado').classList.add('hidden');

                    if (!res.success || res.data.length === 0) {
                        document.getElementById('area-vazio').classList.remove('hidden');
                        return;
                    }

                    document.getElementById('titulo-preview').textContent =
                        'Parceiros com pagamento em ' + res.data_referencia;

                    const tbody = document.getElementById('tbody-preview');
                    tbody.innerHTML = '';

                    let totalGeral = 0;
                    res.data.forEach(p => {
                        totalGeral += p.total_valor;
                        tbody.innerHTML += `
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-3 font-medium">${p.nome}</td>
                                <td class="px-4 py-3 capitalize text-gray-300">${p.frequencia}</td>
                                <td class="px-4 py-3 text-right">${p.total_parcelas}</td>
                                <td class="px-4 py-3 text-right font-semibold text-green-400">
                                    R$ ${p.total_valor.toLocaleString('pt-BR', {minimumFractionDigits:2})}
                                </td>
                            </tr>`;
                    });

                    tbody.innerHTML += `
                        <tr class="bg-white/5 font-bold">
                            <td colspan="3" class="px-4 py-2 text-right text-gray-300">Total geral</td>
                            <td class="px-4 py-2 text-right text-green-300">
                                R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits:2})}
                            </td>
                        </tr>`;

                    const btnGerar = document.getElementById('btn-gerar');
                    const algumComSaldo = res.data.some(p => p.total_parcelas > 0);
                    btnGerar.disabled = !algumComSaldo;

                    document.getElementById('area-preview').classList.remove('hidden');
                });
        }

        function gerarPagamentos() {
            const data = document.getElementById('inp-data').value;
            const btn  = document.getElementById('btn-gerar');
            btn.disabled = true;
            btn.textContent = 'Gerando...';

            fetch(urlGerar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ data }),
            })
            .then(r => r.json())
            .then(res => {
                const area = document.getElementById('area-resultado');
                area.classList.remove('hidden');

                if (res.success) {
                    let rows = res.processados.map(p =>
                        `<tr class="hover:bg-white/5">
                            <td class="px-4 py-2">${p.nome}</td>
                            <td class="px-4 py-2 text-right">${p.total_parcelas}</td>
                            <td class="px-4 py-2 text-right text-green-400 font-semibold">
                                R$ ${p.total_valor.toLocaleString('pt-BR', {minimumFractionDigits:2})}
                            </td>
                        </tr>`
                    ).join('');

                    area.innerHTML = `
                        <div class="bg-green-500/20 border border-green-500/40 rounded-xl p-4">
                            <p class="text-green-300 font-semibold mb-3">${res.message}</p>
                            <table class="w-full text-sm text-white">
                                <thead class="text-gray-300 text-xs uppercase">
                                    <tr>
                                        <th class="text-left py-1">Parceiro</th>
                                        <th class="text-right py-1">Parcelas</th>
                                        <th class="text-right py-1">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">${rows}</tbody>
                            </table>
                        </div>`;

                    document.getElementById('area-preview').classList.add('hidden');
                    carregarHistorico();
                } else {
                    area.innerHTML = `
                        <div class="bg-red-500/20 border border-red-500/40 rounded-xl p-4 text-red-300">
                            ${res.message}
                        </div>`;
                    btn.disabled = false;
                    btn.textContent = 'Gerar Pagamentos';
                }
            });
        }

        function carregarHistorico() {
            fetch(urlHistorico)
                .then(r => r.json())
                .then(res => {
                    const tbody = document.getElementById('tbody-historico');
                    if (!res.success || res.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 text-xs">Nenhum pagamento registrado.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = res.data.map(h => `
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2">${h.name}</td>
                            <td class="px-4 py-2 text-gray-300">${formatarData(h.data)}</td>
                            <td class="px-4 py-2 text-right text-green-400 font-semibold">
                                R$ ${parseFloat(h.valor_total).toLocaleString('pt-BR', {minimumFractionDigits:2})}
                            </td>
                        </tr>`).join('');
                });
        }

        function formatarData(iso) {
            const [y, m, d] = iso.split('-');
            return `${d}/${m}/${y}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            carregarHistorico();
            carregarPreview();
        });
    </script>
    @endsection
</x-app-layout>
