<x-app-layout>
@section('css')
    <link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
@endsection
    <div class="p-4 max-w-5xl mx-auto">

        {{-- Cabeçalho --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        Templates de Comissão PJ / CLT
                    </h1>
                    <p class="text-sm text-gray-300">
                        Configure quantas parcelas e qual % cada plano recebe — geral ou por vendedor(a)
                    </p>
                </div>
                <a href="{{ route('folha.america.index') }}"
                   class="px-3 py-1 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">
                    Voltar
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/40 text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-500/20 border border-red-500/40 text-red-300 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulário --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
            <h2 class="text-white font-semibold mb-4">Adicionar / Atualizar Template</h2>

            <form action="{{ route('folha.america.template-comissoes.salvar') }}" method="POST" id="form-template">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                    {{-- Plano --}}
                    <div>
                        <label class="text-gray-300 text-sm block mb-1">Plano <span class="text-red-400">*</span></label>
                        <select name="plano_id" id="sel-plano"
                                class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500">
                            <option value="" class="bg-gray-800">Selecione o plano...</option>
                            @foreach($planos as $plano)
                                <option value="{{ $plano->id }}" class="bg-gray-800"
                                    {{ old('plano_id') == $plano->id ? 'selected' : '' }}>
                                    {{ $plano->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('plano_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Vendedor --}}
                    <div>
                        <label class="text-gray-300 text-sm block mb-1">Vendedor(a)</label>
                        <select name="user_id" id="sel-vendedor"
                                class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500">
                            <option value="" class="bg-gray-800">— Geral (todos os vendedores) —</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" class="bg-gray-800"
                                    {{ old('user_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                    <span class="text-gray-400">({{ strtoupper($v->tipo_contrato) }})</span>
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Parcelas (dinâmico por JS) --}}
                <div id="area-parcelas" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-gray-300 text-sm">% por Parcela</label>
                        <span id="total-pct" class="text-sm font-semibold text-gray-300">
                            Total: <span id="total-valor" class="text-white">0.00</span>%
                        </span>
                    </div>

                    <div id="inputs-parcelas" class="grid gap-2"></div>

                    <p class="text-gray-500 text-xs mt-2">
                        A soma não precisa ser 100% — parcelas com 0% ficam cadastradas mas sem valor até o fechamento do mês.
                    </p>
                </div>

                <div id="hint-selecione" class="text-gray-500 text-sm italic py-2">
                    Selecione um plano para configurar as parcelas.
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" id="btn-salvar" disabled
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded shadow text-sm font-semibold">
                        Salvar Template
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabela de templates existentes --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
            <div class="px-4 py-3 border-b border-white/10">
                <h2 class="text-white font-semibold">Templates Cadastrados</h2>
            </div>

            @if($templates->isEmpty())
                <p class="px-4 py-6 text-center text-gray-400 text-sm">Nenhum template cadastrado ainda.</p>
            @else
                <div class="divide-y divide-white/10">
                    @foreach($templates as $chave => $parcelas)
                        @php
                            $primeiro   = $parcelas->first();
                            $planoNome  = $primeiro->plano_nome ?? 'Plano #' . $primeiro->plano_id;
                            $userNome   = $primeiro->user_nome  ?? null;
                            $planoId    = $primeiro->plano_id;
                            $userId     = $primeiro->user_id;
                        @endphp
                        <div class="flex items-center gap-4 px-4 py-3 hover:bg-white/5 flex-wrap">

                            {{-- Identificação --}}
                            <div class="min-w-[180px]">
                                <p class="text-white text-sm font-semibold">{{ $planoNome }}</p>
                                @if($userNome)
                                    <p class="text-cyan-300 text-xs">{{ $userNome }}</p>
                                @else
                                    <p class="text-gray-400 text-xs italic">Geral</p>
                                @endif
                            </div>

                            {{-- Parcelas --}}
                            <div class="flex flex-wrap gap-1 flex-1">
                                @foreach($parcelas as $p)
                                    <span class="px-2 py-0.5 rounded text-xs font-mono
                                        {{ $p->valor > 0 ? 'bg-blue-500/30 text-blue-200' : 'bg-white/10 text-gray-400' }}">
                                        P{{ $p->parcela }}: {{ number_format($p->valor, 2) }}%
                                    </span>
                                @endforeach
                            </div>

                            {{-- Botão remover --}}
                            <button
                                onclick="confirmarRemover({{ $planoId }}, {{ $userId ?? 'null' }}, '{{ $planoNome }}', '{{ $userNome ?? 'Geral' }}')"
                                class="px-3 py-1 bg-red-600/80 hover:bg-red-600 text-white rounded text-xs whitespace-nowrap">
                                Remover
                            </button>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    @section('scripts')
    <script>
        // Parcelas por plano_id
        const parcelasPorPlano = {
            1: 6,   // Individual
            3: 7,   // Coletivo
            5: 6,   // Super Simples
        };
        const DEFAULT_PARCELAS = 6;

        const selPlano     = document.getElementById('sel-plano');
        const areaParcelas = document.getElementById('area-parcelas');
        const hintSel      = document.getElementById('hint-selecione');
        const inputsCont   = document.getElementById('inputs-parcelas');
        const totalValor   = document.getElementById('total-valor');
        const btnSalvar    = document.getElementById('btn-salvar');

        // Valores anteriores (em caso de erro de validação)
        const oldValores = @json(old('valores', []));
        const oldPlanoId = "{{ old('plano_id', '') }}";

        function renderParcelas(planoId) {
            const n = parcelasPorPlano[planoId] ?? DEFAULT_PARCELAS;
            inputsCont.innerHTML = '';

            // Grid responsivo: até 7 colunas
            const cols = Math.min(n, 7);
            inputsCont.style.gridTemplateColumns = `repeat(${cols}, minmax(0, 1fr))`;

            for (let i = 1; i <= n; i++) {
                const oldVal = oldValores[i] ?? '0';
                const div = document.createElement('div');
                div.innerHTML = `
                    <label class="text-gray-400 text-xs block mb-1 text-center">Parcela ${i}</label>
                    <div class="relative">
                        <input type="number" name="valores[${i}]" value="${oldVal}"
                               min="0" max="100" step="0.01"
                               oninput="calcTotal()"
                               class="w-full bg-gray-800 border border-white/20 text-white rounded px-2 py-2 text-sm text-center focus:outline-none focus:ring focus:ring-blue-500 pr-6">
                        <span class="absolute right-2 top-2 text-gray-500 text-xs">%</span>
                    </div>
                `;
                inputsCont.appendChild(div);
            }

            calcTotal();
            areaParcelas.classList.remove('hidden');
            hintSel.classList.add('hidden');
            btnSalvar.disabled = false;
        }

        function calcTotal() {
            const inputs = inputsCont.querySelectorAll('input[type=number]');
            let soma = 0;
            inputs.forEach(inp => soma += parseFloat(inp.value || 0));
            totalValor.textContent = soma.toFixed(2);

            if (soma > 100) {
                totalValor.classList.add('text-red-400');
                totalValor.classList.remove('text-green-400', 'text-white');
            } else if (soma === 100) {
                totalValor.classList.add('text-green-400');
                totalValor.classList.remove('text-red-400', 'text-white');
            } else {
                totalValor.classList.add('text-white');
                totalValor.classList.remove('text-red-400', 'text-green-400');
            }
        }

        selPlano.addEventListener('change', function () {
            if (!this.value) {
                areaParcelas.classList.add('hidden');
                hintSel.classList.remove('hidden');
                btnSalvar.disabled = true;
                return;
            }
            renderParcelas(parseInt(this.value));
        });

        // Restaurar estado anterior (erro de validação)
        if (oldPlanoId) {
            selPlano.value = oldPlanoId;
            renderParcelas(parseInt(oldPlanoId));
        }

        // Remover template via AJAX
        function confirmarRemover(planoId, userId, planoNome, userNome) {
            Swal.fire({
                title: 'Remover template?',
                html: `<span class="text-gray-300">Plano: <b class="text-white">${planoNome}</b><br>Vendedor: <b class="text-white">${userNome}</b></span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar',
                background: '#1f2937',
                color: '#f3f4f6',
            }).then(result => {
                if (!result.isConfirmed) return;

                const form = new FormData();
                form.append('_token', '{{ csrf_token() }}');
                form.append('_method', 'DELETE');
                form.append('plano_id', planoId);
                if (userId !== null) form.append('user_id', userId);

                fetch('{{ route('folha.america.template-comissoes.deletar') }}', {
                    method: 'POST',
                    body: form,
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Removido!',
                            timer: 1200,
                            showConfirmButton: false,
                            background: '#1f2937',
                            color: '#f3f4f6',
                        }).then(() => location.reload());
                    }
                });
            });
        }
    </script>
    @endsection
</x-app-layout>
