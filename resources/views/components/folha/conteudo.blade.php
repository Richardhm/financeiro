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

                    <a href="{{route('folha.america.historico')}}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-orange-500 text-white rounded shadow hover:bg-orange-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                        Histórico
                    </a>

                    <a href="{{ route('folha.america.parceiros.pagamentos') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-pink-700 text-white rounded shadow hover:bg-pink-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Pagar Parceiros
                    </a>

                    <a href="{{ route('folha.america.folha-parceiros') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm font-bold bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded shadow hover:opacity-90">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Folha Parceiros
                    </a>
                </fieldset>

                <fieldset class="border-2 border-white/60 rounded px-3 py-2 flex items-center gap-2">
                    <legend class="text-sm font-semibold text-gray-200 px-1 leading-none">Configurações</legend>

                    <a href="{{ route('folha.america.comissao-corretora') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-teal-600 text-white rounded shadow hover:bg-teal-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                        </svg>
                        Corretora
                    </a>

                    <a href="{{ route('folha.america.regras-pj') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-yellow-600 text-white rounded shadow hover:bg-yellow-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        Vendedores PJ
                    </a>

                    <a href="{{ route('folha.america.faixas-clt') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Vendedores CLT
                    </a>

                    <a href="{{ route('folha.america.parceiros-config') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-purple-700 text-white rounded shadow hover:bg-purple-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Parceiros
                    </a>

                    <a href="{{ route('folha.america.parceiros-config') }}"
                       class="flex items-center gap-1 px-3 py-1.5 text-sm bg-amber-500 text-white rounded shadow hover:bg-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        Lembrete
                    </a>
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-1">

            <!-- Lista de Corretores -->
            <div class="lg:col-span-1">
                <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-lg p-4 animate-fade-in-up" style="animation-delay: 0.7s;">

                    <!-- CabeÃ§alho e aÃ§Ãµes -->
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
                                            <div class="w-10 h-10 rounded-full text-center justify-center flex items-center {{ $corretor->image && file_exists($corretor->image) ? '' : 'bg-gradient-to-r from-blue-500 to-indigo-600' }}">
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
                                    $vidas = (int) $corretor->total_contratos;
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
                                            <div class="w-10 h-10 rounded-full text-center justify-center flex items-center {{ $corretor->image && file_exists($corretor->image) ? '' : 'bg-gradient-to-r from-green-500 to-emerald-600' }}">
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
                                                    <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-green-900/60 text-green-300 font-semibold tracking-wide">
                                                        {{ $regraClt->nome }} · {{ $vidas }} vida(s)
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
            <div class="lg:col-span-2">
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

