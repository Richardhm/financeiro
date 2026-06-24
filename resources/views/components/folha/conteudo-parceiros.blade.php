<div id="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<div class="text-center">

    <div id="detalhes-cliente-modal" class="hidden">
        <div id="detalhes-cliente-conteudo" class="relative"></div>
    </div>

    <div class="p-2" id="folha-container">

        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 animate-fade-in-up">
            <h1 class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent mb-1">
                Folha de Parceiros Independentes
            </h1>
            <p class="text-sm text-gray-300 mb-3">
                Cada parceiro possui periodicidade própria — finalize individualmente
            </p>

            <div class="flex flex-wrap gap-1">
                <button id="btnGerarFolhaCorretora"
                        class="flex items-center gap-1 px-2 py-1 text-xs bg-green-500/90 text-white rounded shadow hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    PDF Corretora
                </button>

                <button id="btnGerarFolha"
                        class="flex items-center gap-1 px-2 py-1 text-xs bg-green-500/90 text-white rounded shadow hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    PDF Parceiro
                </button>

                <button onclick="window.location.reload()"
                        class="flex items-center gap-1 px-2 py-1 text-xs bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Atualizar
                </button>

                <button class="flex items-center gap-1 px-2 py-1 text-xs bg-orange-500 text-white rounded shadow hover:bg-orange-600 criar-excel">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                    </svg>
                    Criar Excel
                </button>

                <a href="{{ route('folha.america.parceiros.regras') }}"
                   class="flex items-center gap-1 px-2 py-1 text-xs bg-yellow-600 text-white rounded shadow hover:bg-yellow-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z" />
                    </svg>
                    Regras Comissão
                </a>

                <a href="{{ route('folha.america.parceiros-config') }}"
                   class="flex items-center gap-1 px-2 py-1 text-xs bg-purple-700 text-white rounded shadow hover:bg-purple-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    Config. Parceiros
                </a>

                <a href="{{ route('folha.america.parceiros.pagamentos') }}"
                   class="flex items-center gap-1 px-2 py-1 text-xs bg-pink-700 text-white rounded shadow hover:bg-pink-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                    Pagar Parceiros
                </a>

                <a href="{{ route('folha.america.folha-parceiros.historico') }}"
                   class="flex items-center gap-1 px-2 py-1 text-xs bg-indigo-700 text-white rounded shadow hover:bg-indigo-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Histórico
                </a>

                <a href="{{ route('folha.america.index') }}"
                   class="flex items-center gap-1 px-2 py-1 text-xs bg-gray-600 text-white rounded shadow hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Folha CLT/PJ
                </a>

            </div>
        </div>

        <input type="hidden" id="valor_plano_id_clicado" value="1">

        <!-- Layout Principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-1 mt-3">

            <!-- Lista de Parceiros -->
            <div class="lg:col-span-1">
                <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg p-4 animate-fade-in-up">
                    <div class="border-b border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex w-[65%]">
                                <input type="text" id="pesquisa-corretores" placeholder="Pesquisar..."
                                       class="w-full border border-gray-600 rounded-lg bg-gray-800 text-white text-sm placeholder-gray-400 px-3 py-2 focus:ring focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="flex items-center space-x-1 w-[33%]">
                                <button onclick="selecionarTodos()"
                                        class="px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring focus:ring-blue-400">
                                    Todos
                                </button>
                                <button onclick="deselecionarTodos()"
                                        class="px-3 py-1 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 focus:ring focus:ring-gray-400">
                                    Nenhum
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-96 overflow-y-auto" id="lista-corretores">
                        @forelse($corretores as $corretor)
                            @php
                                $config = $parceirosConfig[$corretor->id] ?? null;
                                $freqLabel = match($config?->frequencia ?? '') {
                                    'semanal'    => ['txt' => 'Semanal',    'cls' => 'bg-blue-500/30 text-blue-200'],
                                    'quinzenal'  => ['txt' => 'Quinzenal',  'cls' => 'bg-indigo-500/30 text-indigo-200'],
                                    'mensal'     => ['txt' => 'Mensal',     'cls' => 'bg-purple-500/30 text-purple-200'],
                                    default      => null,
                                };
                            @endphp
                            <div class="border-b border-white/10 p-2 transition-colors corretor-item"
                                 data-corretor-id="{{ $corretor->id }}"
                                 data-nome="{{ strtolower($corretor->name) }}">
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center flex-1 min-w-0 cursor-pointer">
                                        <input type="checkbox"
                                               class="rounded border-gray-600 mr-2 text-purple-500 bg-gray-800 focus:ring focus:ring-purple-500 corretor-checkbox"
                                               data-corretor-id="{{ $corretor->id }}"
                                               onchange="toggleCorretor({{ $corretor->id }})">

                                        <div class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center {{ $corretor->image && file_exists($corretor->image) ? '' : 'bg-gradient-to-r from-purple-500 to-pink-500' }}">
                                            @if (!empty($corretor->image) && file_exists($corretor->image))
                                                <img src="{{ asset($corretor->image) }}" alt="{{ $corretor->name }}" class="w-full h-full rounded-full object-cover">
                                            @else
                                                <span class="text-white font-bold text-sm">{{ substr($corretor->name, 0, 1) }}</span>
                                            @endif
                                        </div>

                                        <div class="ml-2 min-w-0">
                                            <p class="font-medium text-white text-sm truncate">
                                                {{ implode(' ', array_slice(explode(' ', $corretor->name), 0, 3)) }}
                                            </p>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                @if($freqLabel)
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $freqLabel['cls'] }} font-semibold">
                                                        {{ $freqLabel['txt'] }}
                                                    </span>
                                                @endif
                                                @php
                                                    $totalConf = $confirmadosPorParceiro[$corretor->id] ?? 0;
                                                @endphp
                                                <span class="font-bold text-purple-300 text-xs total_a_receber">
                                                    R$ {{ number_format($totalConf, 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        class="btn-ver-parceiro ml-2 shrink-0 flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-gray-600/60 text-gray-200 rounded hover:bg-gray-500/60"
                                        onclick="carregarDetalhesCorretor({{ $corretor->id }}, 1)"
                                        title="Ver contratos">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <p class="text-gray-400">Nenhum parceiro com valores no período</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

            <!-- Detalhes do Parceiro -->
            <div class="lg:col-span-2">
                <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg animate-fade-in-up">
                    <div id="detalhes-inicial" class="p-8 text-center">
                        <i class="fas fa-hand-pointer text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-200 mb-2">Selecione um parceiro</h3>
                        <p class="text-gray-400">Clique em um parceiro à esquerda para ver os detalhes das comissões</p>
                    </div>

                    <div id="detalhes-loading" class="hidden p-8 text-center">
                        <div class="flex justify-center space-x-2">
                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce delay-200"></div>
                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce delay-400"></div>
                        </div>
                        <p class="mt-4 text-gray-400">Carregando detalhes...</p>
                    </div>

                    <div id="detalhes-conteudo" class="hidden">
                        <div class="lg:col-span-2">
                            <div id="detalhes-cliente" class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg p-3 hidden">
                                <div id="clientes-header" class="mb-2"></div>
                                <div id="clientes-itens">
                                    <p class="text-gray-400">Nenhum cliente ainda.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
