<div id="loading-overlay">
    <div class="loading-spinner"></div>
</div>



{{-- MÃªs jÃ¡ selecionado --}}
<div class="text-center">

    <div id="detalhes-cliente-modal" class="hidden">
        <div id="detalhes-cliente-conteudo" class="relative">
            <!-- O conteÃºdo do modal serÃ¡ carregado dinamicamente -->
        </div>
    </div>

    <div class="p-2" id="folha-container">

        <div class="div bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 w-[99%] items-center mx-auto flex justify-between   animate-fade-in-up">
            <div class="text-left text-white">
               <div>📊 <span class="text-xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent">Folha de Pagamento - América</span></div>
               <small>Gerencie e processe pagamentos de comissões por período</small>
            </div>
            <div>
                <button id="finalizar-mes"
                        class="flex items-center gap-1 px-3 py-1.5 text-sm font-bold bg-gradient-to-r from-red-500 to-blue-500 text-white border border-white rounded hover:opacity-90">
                    <i class="fas fa-sync-alt"></i>
                    Finalizar <span class="uppercase text-yellow-300 font-extrabold">({{ $mesAtual }})</span>
                </button>
            </div>
        </div>





        <div class="flex flex-wrap gap-2 items-center py-2">
                <fieldset class="border-2 border-white/60 rounded px-3 py-2 flex items-center gap-2">
                    <legend class="text-sm font-semibold text-gray-200 px-1 leading-none">Documento</legend>

                    <button id="btnGerarFolhaCorretora"
                            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-500/90 text-white rounded shadow hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        PDF Corretora
                    </button>

                    <button id="btnGerarFolha"
                            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-500/90 text-white rounded shadow hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        PDF Corretor
                    </button>

                    <button class="flex items-center gap-1 px-3 py-1.5 text-sm bg-orange-500 text-white rounded shadow hover:bg-orange-600 criar-excel">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                        </svg>
                        Criar Excel
                    </button>
                </fieldset>

                <fieldset class="border-2 border-white/60 rounded px-3 py-2 flex items-center gap-2">
                    <legend class="text-sm font-semibold text-gray-200 px-1 leading-none">Geral</legend>

                    <button onclick="window.location.reload()"
                            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Atualizar
                    </button>

                    <button id="btn-recalcular-comissoes"
                            class="flex items-center gap-1 px-3 py-1.5 text-sm bg-teal-600 text-white rounded shadow hover:bg-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" />
                        </svg>
                        Recalcular Comissões
                    </button>
                </fieldset>



        </div>

        {{-- Resumo Por Plano Compacto (oculto) --}}
        <div class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 text-xs">
                <!-- Individual -->
                <div class="bg-blue-500/90 text-white rounded-lg shadow p-2 text-center card-resumo hover:cursor-pointer" data-plano-id="1">
                    <h4 class="text-sm font-semibold">Plano Individual</h4>
                    <p>
                        <strong>Contratos:</strong> {{ $resumoPorPlano['individual']->total_contratos ?? 0 }}<br>
                        <strong>Vidas:</strong> {{ $resumoPorPlano['individual']->total_vidas ?? 0 }}<br>
                        <strong>Total:</strong> R$ {{ number_format($resumoPorPlano['individual']->valor_total ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <!-- Coletivo -->
                <div class="bg-green-500/90 text-white rounded-lg shadow p-2 text-center card-resumo hover:cursor-pointer" data-plano-id="3">
                    <h4 class="text-sm font-semibold">Plano Coletivo</h4>
                    <p>
                        <strong>Contratos:</strong> {{ $resumoPorPlano['coletivo']->total_contratos ?? 0 }}<br>
                        <strong>Vidas:</strong> {{ $resumoPorPlano['coletivo']->total_vidas ?? 0 }}<br>
                        <strong>Total:</strong> R$ {{ number_format($resumoPorPlano['coletivo']->valor_total ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <!-- Empresarial -->
                <div class="bg-orange-500/90 text-white rounded-lg shadow p-2 text-center card-resumo hover:cursor-pointer" data-plano-id="empresarial">
                    <h4 class="text-sm font-semibold">Plano Empresarial</h4>
                    <p>
                        <strong>Contratos:</strong> {{ $resumoPorPlano['empresarial']->total_contratos ?? 0 }}<br>
                        <strong>Vidas:</strong> {{ $resumoPorPlano['empresarial']->total_vidas ?? 0 }}<br>
                        <strong>Total:</strong> R$ {{ number_format($resumoPorPlano['empresarial']->valor_total ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <!-- Odonto -->
                <div class="bg-yellow-500 text-white rounded-lg shadow p-2 text-center card-resumo hover:cursor-pointer" data-plano-id="odonto">
                    <h4 class="text-sm font-semibold">Odonto</h4>
                    <p>
                        <strong>Contratos:</strong> {{ $resumoPorPlano['odonto']->total_registros ?? 0 }}<br>
                        <strong>Vidas:</strong> {{ $resumoPorPlano['odonto']->total_registros ?? 0 }}<br>
                        <strong>Total:</strong> R$ {{ number_format($resumoPorPlano['odonto']->total_comissao ?? 0, 2, ',', '.') }}
                    </p>
                </div>
                <!-- Estorno -->
                <div class="bg-red-950 text-white rounded-lg shadow p-2 text-center card-resumo hover:cursor-pointer" data-plano-id="estorno">
                    <h4 class="text-sm font-semibold">Estorno</h4>
                    <p>
                        <strong>Contratos:</strong> {{ $resumoPorPlano['estorno']->total_registros_estorno ?? 0 }}<br>
                        <strong>Vidas:</strong> {{ $resumoPorPlano['estorno']->total_registros_estorno ?? 0 }}<br>
                        <strong>Total:</strong> R$ {{ number_format($resumoPorPlano['estorno']->total_comissao_estorno ?? 0, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>


        <input type="hidden" id="valor_plano_id_clicado" value="1">

        <!-- Resumo por Parcelas -->


        <!-- Layout Principal -->
        <style>
            .folha-grid { display: flex; flex-direction: column; gap: 8px; }
            @media (min-width: 1024px) {
                .folha-grid { display: grid; grid-template-columns: 300px minmax(0, 1fr); align-items: start; }
            }
            /* Fontes compactas: lista de vendedores */
            #lista-corretores { font-size: 12px; }
            #lista-corretores .text-sm { font-size: 11px !important; }
            #lista-corretores .text-xs { font-size: 10px !important; }
            /* Fontes compactas: tabelas dos detalhes */
            .folha-grid table { font-size: 10px !important; }
            .folha-grid table.text-sm { font-size: 10px !important; }
            .folha-grid table .text-xs { font-size: 10px !important; }
            .folha-grid table th, .folha-grid table td { padding-top: 4px !important; padding-bottom: 4px !important; }
        </style>
        <div class="folha-grid">

            <!-- Lista de Corretores -->
            <div>
                <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg p-4 animate-fade-in-up" style="animation-delay: 0.7s;">

                    <!-- CabeÃ§alho e aÃ§Ãµes -->
                    <div class="border-b border-gray-700">
                        <div class="flex items-center gap-1 pb-2">
                            <input type="text" id="pesquisa-corretores" placeholder="Pesquisar..."
                                   class="flex-1 min-w-0 border border-gray-600 rounded-lg bg-gray-800 text-white text-sm placeholder-gray-400 px-3 py-1.5 focus:ring focus:ring-blue-500 focus:outline-none">
                            <button onclick="selecionarTodos()"
                                    class="px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring focus:ring-blue-400 shrink-0">
                                Todos
                            </button>
                            <button onclick="deselecionarTodos()"
                                    class="px-2 py-1 text-xs font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 focus:ring focus:ring-gray-400 shrink-0">
                                Nenhum
                            </button>
                        </div>


                    </div>

                    {{-- Abas PJ / CLT --}}
                    @php
                        $vendedoresClt = collect($corretores)->where('tipo_contrato', 'clt');
                        $vendedoresPj  = collect($corretores)->where('tipo_contrato', 'pj');
                    @endphp

                    <div class="flex mt-2 border-b border-white/20">
                        <button id="tab-pj"
                                onclick="trocarAba('pj')"
                                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold border-b-2 border-blue-400 text-blue-300 transition-colors">
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-400"></span>
                            PJ
                            <span class="ml-1 text-xs bg-blue-500/30 text-blue-200 rounded-full px-1.5">{{ $vendedoresPj->count() }}</span>
                        </button>
                        <button id="tab-clt"
                                onclick="trocarAba('clt')"
                                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-gray-400 hover:text-gray-200 transition-colors">
                            <span class="inline-block w-2 h-2 rounded-full bg-green-400"></span>
                            CLT
                            <span class="ml-1 text-xs bg-green-500/30 text-green-200 rounded-full px-1.5">{{ $vendedoresClt->count() }}</span>
                        </button>
                    </div>

                    <div class="max-h-96 overflow-y-auto" id="lista-corretores">

                        {{-- Painel PJ (default visível) --}}
                        <div id="painel-pj">
                            @forelse($vendedoresPj as $corretor)
                                <div class="group cursor-pointer border-b border-white/10 p-1 transition-colors corretor-item"
                                     data-corretor-id="{{ $corretor->id }}"
                                     data-nome="{{ strtolower($corretor->name) }}">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <input type="checkbox"
                                                   class="rounded border-gray-600 mr-2 text-blue-500 bg-gray-800 focus:ring focus:ring-blue-500 corretor-checkbox"
                                                   data-corretor-id="{{ $corretor->id }}"
                                                   onchange="toggleCorretor({{ $corretor->id }})">
                                            <div class="w-6 h-6 rounded-full text-[10px] text-center justify-center flex items-center shrink-0 {{ $corretor->image && file_exists($corretor->image) ? '' : 'bg-gradient-to-r from-blue-500 to-indigo-600' }}">
                                                @if (!empty($corretor->image) && file_exists($corretor->image))
                                                    <img src="{{ asset($corretor->image) }}" alt="{{ $corretor->name }}" class="w-full h-full bg-white rounded-full object-cover">
                                                @else
                                                    <span class="text-white text-center flex justify-center font-bold">{{ substr($corretor->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-white flex mr-5">
                                                    <span class="ml-2">{{ implode(' ', array_slice(explode(' ', $corretor->name), 0, 3)) }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-blue-400 text-xs total_a_receber">
                                                R$ {{ number_format($corretor->total_receber, 2, ',', '.') }}
                                            </div>
                                            <div class="text-xs text-white text-right">a receber</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <i class="fas fa-user-tie text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-gray-400">Nenhum vendedor PJ com valores no período</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Painel CLT (oculto por default) --}}
                        <div id="painel-clt" class="hidden">
                            @forelse($vendedoresClt as $corretor)
                                @php
                                    // Vidas da competencia aberta (mesma contagem do recalculo),
                                    // NAO o total da carteira
                                    $vidas = (int) (($vidasClt[$corretor->id] ?? null) ?? 0);
                                    $regraClt = isset($faixasClt)
                                        ? $faixasClt->first(fn($f) =>
                                            $vidas >= $f->vidas_min &&
                                            ($f->vidas_max === null || $vidas <= $f->vidas_max)
                                          )
                                        : null;
                                @endphp
                                <div class="group cursor-pointer border-b border-white/10 p-1 transition-colors corretor-item"
                                     data-corretor-id="{{ $corretor->id }}"
                                     data-nome="{{ strtolower($corretor->name) }}">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <input type="checkbox"
                                                   class="rounded border-gray-600 mr-2 text-green-500 bg-gray-800 focus:ring focus:ring-green-500 corretor-checkbox"
                                                   data-corretor-id="{{ $corretor->id }}"
                                                   onchange="toggleCorretor({{ $corretor->id }})">
                                            <div class="w-6 h-6 rounded-full text-[10px] text-center justify-center flex items-center shrink-0 {{ $corretor->image && file_exists($corretor->image) ? '' : 'bg-gradient-to-r from-green-500 to-emerald-600' }}">
                                                @if (!empty($corretor->image) && file_exists($corretor->image))
                                                    <img src="{{ asset($corretor->image) }}" alt="{{ $corretor->name }}" class="w-full h-full bg-white rounded-full object-cover">
                                                @else
                                                    <span class="text-white text-center flex justify-center font-bold">{{ substr($corretor->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-white flex mr-5">
                                                    <span class="ml-2">{{ implode(' ', array_slice(explode(' ', $corretor->name), 0, 3)) }}</span>
                                                </p>
                                                @if($regraClt)
                                                    @php $mesAbrev = isset($mesAtual) ? ucfirst(mb_substr($mesAtual, 0, 3)) : ''; @endphp
                                                    <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-green-900/60 text-green-300 font-semibold tracking-wide"
                                                          title="Vendas do mês da folha ({{ $mesAtual ?? '' }}) — define a faixa das vendas deste mês. Cada parcela listada usa a faixa do mês da própria venda.">
                                                        Vendas {{ $mesAbrev }}: {{ $vidas }} · {{ $regraClt->nome }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-green-400 text-xs total_a_receber">
                                                R$ {{ number_format($corretor->total_receber, 2, ',', '.') }}
                                            </div>
                                            <div class="text-xs text-white text-right">a receber</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <i class="fas fa-user-tie text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-gray-400">Nenhum vendedor CLT com valores no período</p>
                                </div>
                            @endforelse
                        </div>

                    </div>

                    <script>
                    function trocarAba(aba) {
                        const isPj = aba === 'pj';
                        document.getElementById('painel-pj').classList.toggle('hidden', !isPj);
                        document.getElementById('painel-clt').classList.toggle('hidden', isPj);

                        const tabPj  = document.getElementById('tab-pj');
                        const tabClt = document.getElementById('tab-clt');

                        tabPj.classList.toggle('border-blue-400',  isPj);
                        tabPj.classList.toggle('text-blue-300',    isPj);
                        tabPj.classList.toggle('border-transparent', !isPj);
                        tabPj.classList.toggle('text-gray-400',    !isPj);

                        tabClt.classList.toggle('border-green-400', !isPj);
                        tabClt.classList.toggle('text-green-300',   !isPj);
                        tabClt.classList.toggle('border-transparent', isPj);
                        tabClt.classList.toggle('text-gray-400',    isPj);
                    }
                    </script>
                </div>
            </div>

            <!-- Detalhes do Corretor -->
            <div class="min-w-0">
                <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg animate-fade-in-up" style="animation-delay: 0.8s;">
                    <!-- Estado inicial -->
                    <div id="detalhes-inicial" class="p-8 text-center">
                        <i class="fas fa-hand-pointer text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-200 mb-2">
                            Selecione um corretor
                        </h3>
                        <p class="text-gray-400">
                            Clique em um corretor à esquerda para ver os detalhes das comissões
                        </p>
                    </div>

                    <!-- Loading -->
                    <div id="detalhes-loading" class="hidden p-8 text-center">
                        <div class="flex justify-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce delay-200"></div>
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce delay-400"></div>
                        </div>
                        <p class="mt-4 text-gray-400">Carregando detalhes...</p>
                    </div>

                    <!-- ConteÃºdo dos detalhes -->
                    <div id="detalhes-conteudo" class="hidden">
                        <div class="lg:col-span-2">
                            <div id="detalhes-cliente" class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg p-1 hidden">
                                <div id="clientes-header">
                                </div>
                                <div id="clientes-itens">
                                    <!-- DinÃ¢mico -->
                                    <p class="text-gray-400">Nenhum cliente ainda.</p>
                                </div>
                            </div>
                        </div>
                        <!-- JavaScript irÃ¡ preencher o conteÃºdo aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.getElementById('btn-recalcular-comissoes')?.addEventListener('click', function () {
    // Mes da folha aberta (pre-selecionado) + 6 meses anteriores a hoje
    var mesAberto = "{{ isset($dadosMes) ? \Carbon\Carbon::parse($dadosMes)->format('Y-m') : '' }}";
    var nomesMes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    var hoje = new Date();
    var meses = [];
    for (var i = 1; i <= 6; i++) {
        var d = new Date(hoje.getFullYear(), hoje.getMonth() - i, 1);
        meses.push(d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0'));
    }
    if (mesAberto && meses.indexOf(mesAberto) === -1) meses.unshift(mesAberto);

    var checks = meses.map(function (m) {
        var partes = m.split('-');
        var rotulo = nomesMes[parseInt(partes[1], 10) - 1] + '/' + partes[0];
        var marcado = m === mesAberto ? 'checked' : '';
        var badge = m === mesAberto ? ' <span style="font-size:10px;color:#34d399;">(folha aberta)</span>' : '';
        return '<label style="display:flex;align-items:center;gap:8px;padding:6px 10px;border:1px solid #374151;border-radius:8px;cursor:pointer;">' +
               '<input type="checkbox" class="chk-competencia" value="' + m + '" ' + marcado + ' style="accent-color:#0d9488;width:15px;height:15px;">' +
               '<span>' + rotulo + badge + '</span></label>';
    }).join('');

    Swal.fire({
        title: 'Recalcular comissões',
        html: '<p style="font-size:13px;color:#94a3b8;margin-bottom:10px;">Escolha os meses: aplica as <b>faixas CLT</b> e as <b>regras de parceiros</b> sobre as parcelas de cada competência (não finaliza nada).</p>' +
              '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;text-align:left;font-size:13px;">' + checks + '</div>',
        icon: 'question',
        background: '#1f2937', color: '#f3f4f6',
        showCancelButton: true,
        confirmButtonText: 'Recalcular selecionados',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d9488', cancelButtonColor: '#4b5563',
        width: 520,
        preConfirm: function () {
            var sel = Array.from(document.querySelectorAll('.chk-competencia:checked')).map(function (c) { return c.value; });
            if (!sel.length) { Swal.showValidationMessage('Selecione ao menos um mês'); return false; }
            return sel;
        }
    }).then(function (r) {
        if (!r.isConfirmed) return;
        var competenciasSelecionadas = r.value;

        Swal.fire({
            title: 'Recalculando...',
            background: '#1f2937', color: '#f3f4f6',
            allowOutsideClick: false, showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });

        fetch("{{ route('folha.america.recalcular-comissoes') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({ competencias: competenciasSelecionadas })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) throw new Error(data.message || 'Erro');

            var linhas = (data.recalculados || []).map(function (v) {
                return '<tr>' +
                    '<td style="text-align:left;padding:3px 8px;">' + v.competencia + '</td>' +
                    '<td style="text-align:left;padding:3px 8px;">' + v.vendedor + '</td>' +
                    '<td style="padding:3px 8px;">' + v.vidas + '</td>' +
                    '<td style="padding:3px 8px;">' + v.faixa + '</td>' +
                    '<td style="padding:3px 8px;color:#34d399;font-weight:700;">' + v.percentual + '%</td>' +
                    '<td style="padding:3px 8px;">' + v.parcelas + '</td>' +
                '</tr>';
            }).join('');

            var corpo = linhas
                ? '<table style="width:100%;font-size:13px;border-collapse:collapse;">' +
                  '<thead><tr style="color:#94a3b8;font-size:11px;text-transform:uppercase;">' +
                  '<th style="text-align:left;padding:3px 8px;">Mês</th><th style="text-align:left;padding:3px 8px;">Vendedor</th><th>Vidas</th><th>Faixa</th><th>%</th><th>Parcelas</th>' +
                  '</tr></thead><tbody>' + linhas + '</tbody></table>'
                : '<p style="color:#94a3b8;">Nenhum vendedor CLT com parcelas baixadas na competência ' + data.competencia + '.</p>';

            Swal.fire({
                title: 'Recálculo concluído',
                html: corpo,
                icon: linhas ? 'success' : 'info',
                background: '#1f2937', color: '#f3f4f6',
                confirmButtonText: 'OK', confirmButtonColor: '#0d9488',
                width: 560,
            }).then(function () { window.location.reload(); });
        })
        .catch(function (e) {
            Swal.fire({ icon: 'error', title: 'Erro ao recalcular', text: e.message, background: '#1f2937', color: '#f3f4f6' });
        });
    });
});
</script>
