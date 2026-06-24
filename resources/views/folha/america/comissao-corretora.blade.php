<x-app-layout>
    <div class="p-4 max-w-5xl mx-auto">

        {{-- Cabeçalho --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-teal-500 bg-clip-text text-transparent">
                        Comissão da Corretora
                    </h1>
                    <p class="text-sm text-gray-300">
                        Configure o percentual retido pela corretora por parcela e acompanhe o total do mês
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

        {{-- Resumo do mês aberto --}}
        @if($folhaMes && $resumoMes)
            @php $mes = \Carbon\Carbon::parse($folhaMes->mes)->format('m/Y'); @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-white/10 rounded-xl p-4 text-center">
                    <p class="text-gray-400 text-xs mb-1">Contratos ({{ $mes }})</p>
                    <p class="text-white text-xl font-bold">{{ $resumoMes->total_contratos }}</p>
                </div>
                <div class="bg-white/10 rounded-xl p-4 text-center">
                    <p class="text-gray-400 text-xs mb-1">Recebido dos clientes</p>
                    <p class="text-blue-300 text-xl font-bold">
                        R$ {{ number_format($resumoMes->total_recebido, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white/10 rounded-xl p-4 text-center">
                    <p class="text-gray-400 text-xs mb-1">Pago aos vendedores</p>
                    <p class="text-yellow-300 text-xl font-bold">
                        R$ {{ number_format($resumoMes->total_pago_vendedores, 2, ',', '.') }}
                    </p>
                </div>
                <div class="bg-emerald-500/20 border border-emerald-500/30 rounded-xl p-4 text-center">
                    <p class="text-emerald-300 text-xs mb-1">Comissão corretora</p>
                    <p class="text-emerald-300 text-xl font-bold">
                        R$ {{ number_format($resumoMes->total_corretora, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="mb-6 flex justify-end">
                <button onclick="recalcular()"
                        class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded shadow text-sm font-semibold">
                    Recalcular valor_corretora do mês {{ $mes }}
                </button>
            </div>
            <div id="resultado-recalculo" class="hidden mb-4"></div>
        @endif

        {{-- Formulário --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
            <h2 class="text-white font-semibold mb-4">Nova Configuração</h2>
            <form action="{{ route('folha.america.comissao-corretora.salvar') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                    <div>
                        <label class="text-gray-300 text-sm block mb-1">Plano</label>
                        <select name="plano_id"
                                class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-teal-500">
                            <option value="" class="bg-gray-800">Selecione...</option>
                            @foreach($planos as $p)
                                <option value="{{ $p->id }}" class="bg-gray-800" {{ old('plano_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('plano_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-gray-300 text-sm block mb-1">Administradora</label>
                        <select name="administradora_id"
                                class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-teal-500">
                            <option value="" class="bg-gray-800">Selecione...</option>
                            @foreach($administradoras as $a)
                                <option value="{{ $a->id }}" class="bg-gray-800" {{ old('administradora_id') == $a->id ? 'selected' : '' }}>
                                    {{ $a->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('administradora_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-gray-300 text-sm block mb-1">
                            Vendedor <span class="text-gray-500">(vazio = todos)</span>
                        </label>
                        <select name="user_id"
                                class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-teal-500">
                            <option value="" class="bg-gray-800">Todos</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" class="bg-gray-800" {{ old('user_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }} ({{ $v->tipo_contrato }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-gray-300 text-sm block mb-1">Parcela nº</label>
                        <input type="number" name="parcela" value="{{ old('parcela', 1) }}" min="1"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-teal-500">
                        @error('parcela')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="text-gray-300 text-sm block mb-1">Percentual retido pela corretora (%)</label>
                        <input type="number" name="valor" value="{{ old('valor') }}" min="0" max="100" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-teal-500">
                        @error('valor')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded shadow text-sm font-semibold">
                            Salvar Configuração
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabela de configurações --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-sm text-white">
                <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Plano</th>
                        <th class="px-4 py-3 text-left">Administradora</th>
                        <th class="px-4 py-3 text-left">Vendedor</th>
                        <th class="px-4 py-3 text-center">Parcela</th>
                        <th class="px-4 py-3 text-right">% Corretora</th>
                        <th class="px-4 py-3 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($configuracoes as $cfg)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-3">{{ $cfg->plano->nome ?? "#{$cfg->plano_id}" }}</td>
                            <td class="px-4 py-3">{{ $cfg->administradora->nome ?? "#{$cfg->administradora_id}" }}</td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $cfg->user->name ?? 'Todos' }}
                            </td>
                            <td class="px-4 py-3 text-center">{{ $cfg->parcela }}ª</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-400">
                                {{ number_format($cfg->valor, 2, ',', '.') }}%
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('folha.america.comissao-corretora.deletar', $cfg->id) }}"
                                      method="POST" onsubmit="return confirm('Remover esta configuração?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1 bg-red-600/80 hover:bg-red-600 text-white rounded text-xs">
                                        Remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                Nenhuma configuração cadastrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    @section('scripts')
    <script>
        function recalcular() {
            fetch('{{ route("folha.america.comissao-corretora.recalcular") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(res => {
                const el = document.getElementById('resultado-recalculo');
                el.classList.remove('hidden');
                el.innerHTML = res.success
                    ? `<div class="px-4 py-3 bg-green-500/20 border border-green-500/40 text-green-300 rounded-lg text-sm">${res.message} — <a href="" class="underline">recarregar para ver totais atualizados</a></div>`
                    : `<div class="px-4 py-3 bg-red-500/20 border border-red-500/40 text-red-300 rounded-lg text-sm">${res.message}</div>`;
            });
        }
    </script>
    @endsection
</x-app-layout>
