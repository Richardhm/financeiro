<x-app-layout>
    <div class="p-4 max-w-4xl mx-auto">

        {{-- Cabeçalho --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
                        Configuração de Parceiros
                    </h1>
                    <p class="text-sm text-gray-300">Defina a frequência e os dias de pagamento de cada parceiro independente</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="document.getElementById('modal-ajuda').classList.remove('hidden')"
                            class="px-3 py-1 bg-purple-700 hover:bg-purple-600 text-white rounded shadow text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Como cadastrar
                    </button>
                    <a href="{{ route('folha.america.index') }}"
                       class="px-3 py-1 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        {{-- Mensagem de sucesso --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/40 text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Formulário --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
            <h2 class="text-white font-semibold mb-4">Adicionar / Atualizar Configuração</h2>

            @if($parceiros->isEmpty())
                <p class="text-gray-400 text-sm">
                    Nenhum parceiro encontrado. Cadastre usuários com
                    <span class="text-indigo-300 font-mono">tipo_contrato = parceiro</span> para configurá-los aqui.
                </p>
            @else
                <form action="{{ route('folha.america.parceiros-config.salvar') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label class="text-gray-300 text-sm block mb-1">Parceiro</label>
                            <select name="user_id"
                                    class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-purple-500">
                                <option value="" class="bg-gray-800">Selecione...</option>
                                @foreach($parceiros as $p)
                                    <option value="{{ $p->id }}" class="bg-gray-800" {{ old('user_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                        @if(isset($configs[$p->id]))
                                            (já configurado)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="text-gray-300 text-sm block mb-1">Frequência</label>
                            <select name="frequencia" id="sel-frequencia"
                                    class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-purple-500">
                                <option value="semanal"       class="bg-gray-800" {{ old('frequencia') == 'semanal'       ? 'selected' : '' }}>Semanal</option>
                                <option value="quinzenal"     class="bg-gray-800" {{ old('frequencia') == 'quinzenal'     ? 'selected' : '' }}>Quinzenal</option>
                                <option value="mensal"        class="bg-gray-800" {{ old('frequencia') == 'mensal'        ? 'selected' : '' }}>Mensal</option>
                                <option value="personalizado" class="bg-gray-800" {{ old('frequencia') == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                            </select>
                            @error('frequencia')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-gray-300 text-sm block mb-1">
                                Dias de pagamento
                                <span class="text-gray-500 ml-1" id="hint-dias">
                                    (semanal: 1=seg ... 7=dom | quinzenal: ex: 15,30 | mensal: ex: 10)
                                </span>
                            </label>
                            <input type="text" name="dias_pagamento" id="inp-dias"
                                   value="{{ old('dias_pagamento') }}"
                                   placeholder="ex: 5  ou  15,30  ou  10"
                                   class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-purple-500">
                            @error('dias_pagamento')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit"
                                    class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded shadow text-sm font-semibold">
                                Salvar Configuração
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        {{-- Tabela de configs existentes --}}
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-sm text-white">
                <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Parceiro</th>
                        <th class="px-4 py-3 text-left">Frequência</th>
                        <th class="px-4 py-3 text-left">Dias</th>
                        <th class="px-4 py-3 text-center">Ativo</th>
                        <th class="px-4 py-3 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($configs as $config)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-3">{{ $config->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize">{{ $config->frequencia }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $dias = $config->dias_pagamento;
                                    if ($config->frequencia === 'semanal') {
                                        $nomes = [1=>'Seg',2=>'Ter',3=>'Qua',4=>'Qui',5=>'Sex',6=>'Sáb',7=>'Dom'];
                                        $labels = array_map(fn($d) => $nomes[$d] ?? $d, $dias);
                                    } else {
                                        $labels = array_map(fn($d) => "Dia $d", $dias);
                                    }
                                @endphp
                                {{ implode(', ', $labels) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($config->ativo)
                                    <span class="px-2 py-0.5 bg-green-500/30 text-green-300 rounded text-xs">Ativo</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-500/30 text-red-300 rounded text-xs">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('folha.america.parceiros-config.deletar', $config->id) }}"
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
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                Nenhuma configuração cadastrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal de ajuda --}}
    <div id="modal-ajuda" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('modal-ajuda').classList.add('hidden')"></div>
        <div class="relative bg-gray-900 border border-white/20 rounded-2xl shadow-2xl w-full max-w-lg text-white overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/10 bg-purple-900/40">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-bold text-lg text-purple-200">Como preencher os dias</h3>
                </div>
                <button onclick="document.getElementById('modal-ajuda').classList.add('hidden')"
                        class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Corpo --}}
            <div class="px-5 py-5 space-y-5 text-sm">

                {{-- Semanal --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-green-700/50 text-green-300 rounded text-xs font-bold">SEMANAL</span>
                        <span class="text-gray-300">Use o numero do dia da semana</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        @foreach(['1'=>'Seg','2'=>'Ter','3'=>'Qua','4'=>'Qui','5'=>'Sex','6'=>'Sab','7'=>'Dom'] as $num => $nome)
                        <div class="bg-gray-800 rounded text-center py-1.5">
                            <p class="text-white font-bold text-base">{{ $num }}</p>
                            <p class="text-gray-400 text-[10px]">{{ $nome }}</p>
                        </div>
                        @endforeach
                    </div>
                    <div class="bg-gray-800/60 rounded-lg p-3 space-y-1 text-gray-300">
                        <p><span class="text-white font-semibold">Toda sexta:</span> digite <code class="bg-gray-700 px-1 rounded text-green-300">5</code></p>
                        <p><span class="text-white font-semibold">Segunda e quinta:</span> digite <code class="bg-gray-700 px-1 rounded text-green-300">1,4</code></p>
                        <p><span class="text-white font-semibold">Toda semana, segunda a sexta:</span> <code class="bg-gray-700 px-1 rounded text-green-300">1,2,3,4,5</code></p>
                    </div>
                </div>

                <hr class="border-white/10">

                {{-- Quinzenal --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-blue-700/50 text-blue-300 rounded text-xs font-bold">QUINZENAL</span>
                        <span class="text-gray-300">Use o dia do mes (1 a 31)</span>
                    </div>
                    <div class="bg-gray-800/60 rounded-lg p-3 space-y-1 text-gray-300">
                        <p><span class="text-white font-semibold">Dia 15 e dia 30:</span> digite <code class="bg-gray-700 px-1 rounded text-blue-300">15,30</code></p>
                        <p><span class="text-white font-semibold">Dia 1 e dia 16:</span> digite <code class="bg-gray-700 px-1 rounded text-blue-300">1,16</code></p>
                    </div>
                </div>

                <hr class="border-white/10">

                {{-- Mensal --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-yellow-700/50 text-yellow-300 rounded text-xs font-bold">MENSAL</span>
                        <span class="text-gray-300">Use o dia do mes (1 a 31)</span>
                    </div>
                    <div class="bg-gray-800/60 rounded-lg p-3 text-gray-300">
                        <p><span class="text-white font-semibold">Todo dia 10:</span> digite <code class="bg-gray-700 px-1 rounded text-yellow-300">10</code></p>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-5 py-3 border-t border-white/10 flex justify-end">
                <button onclick="document.getElementById('modal-ajuda').classList.add('hidden')"
                        class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded shadow text-sm font-semibold">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        const hints = {
            semanal:       '1=Seg, 2=Ter, 3=Qua, 4=Qui, 5=Sex, 6=Sab, 7=Dom — ex: 5 (toda sexta)',
            quinzenal:     'Dois dias do mes — ex: 15,30',
            mensal:        'Um dia do mes — ex: 10',
            personalizado: 'Qualquer combinacao de dias — ex: 5,12,20',
        };
        document.getElementById('sel-frequencia').addEventListener('change', function () {
            document.getElementById('hint-dias').textContent = '(' + hints[this.value] + ')';
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.getElementById('modal-ajuda').classList.add('hidden');
            }
        });
    </script>
    @endsection
</x-app-layout>
