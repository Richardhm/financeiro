<x-app-layout>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6 bg-white/10 backdrop-blur-md text-center text-green-50 rounded p-2">Configuração de Comissões por Corretor</h1>

        <!-- Barra de busca -->
        <div class="mb-6 w-[100%] flex justify-end">
            <input
                type="text"
                id="searchCorretor"
                placeholder="Buscar por corretor..."
                class="w-[30%] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
        </div>

        <!-- Grid de corretores -->
        <div id="corretores-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($dadosCorretores as $corretor)
                <!-- Card do Corretor -->
                <div class="bg-white/10 backdrop-blur-md rounded-lg shadow-md p-2 flex flex-col justify-between">
                    <!-- Nome do corretor no header -->
                    <div class="text-center mb-4">
                        <h2 class="text-lg font-semibold text-white underline">
                            {{ $corretor['nome'] }}
                        </h2>
                    </div>

                    <!-- Botão "Voltar" ao Plano Individual (Inicialmente Oculto) -->
                    <div
                        id="voltar-individual-{{ $loop->index }}"
                        class="hidden text-center mb-4"
                    >
                        <button
                            class="text-sm font-medium text-blue-500 hover:underline"
                            onclick="mostrarPlano('individual', {{ $loop->index }})">
                            Voltar para Individual
                        </button>
                    </div>

                    <!-- Tabelas com as Comissões -->
                    <div class="planos-container">
                        <!-- Tabela de Comissões Individual (Padrão) -->
                        <table
                            class="w-full table-auto border-collapse border border-gray-200 plano-individual"
                            id="plano-individual-{{ $loop->index }}"
                        >
                            <thead class="bg-orange-300">
                            <tr>
                                <th class="px-2 py-1 text-sm font-medium text-white border-b">Parcela</th>
                                <th class="px-2 py-1 text-sm font-medium text-white border-b">Comissão (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($corretor['comissoes']['Individual'] as $parcela => $valor)
                                <tr>
                                    <td class="px-2 py-1 text-xs text-white border-b">Parcela {{ $parcela }}</td>
                                    <td class="px-2 py-1 text-sm text-dark border-b flex items-center space-x-2">
                                        <input
                                            type="number"
                                            name="comissoes[1][{{ $parcela }}]"
                                            value="{{ $valor }}"
                                            step="0.01"
                                            disabled
                                            class="w-full px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        />
                                        <button
                                            class="text-dark hover:text-blue-700 edit-btn bg-green-200 p-1 rounded"
                                            data-corretor-id="{{ $corretor['id'] }}"
                                            data-plano-id="1"
                                            data-parcela="{{ $parcela }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6m2 2v14m1.324-12.598l-9.833 13.598c-.138.192-.329.353-.552.462l-4.02 1.865c-.299.138-.657-.028-.75-.352l-2-7c-.077-.269-.045-.561.087-.8L14.263 3.324c.279-.399.81-.502 1.21-.259L20.324 5.402z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <!-- Tabela de Comissões Coletivo (Inicialmente Oculta) -->
                        <table
                            class="w-full table-auto border-collapse border border-gray-200 hidden plano-coletivo"
                            id="plano-coletivo-{{ $loop->index }}"
                        >
                            <thead class="bg-orange-300">
                            <tr>
                                <th class="px-2 py-1 text-sm font-medium text-white border-b">Parcela</th>
                                <th class="px-2 py-1 text-sm font-medium text-white border-b">Comissão (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($corretor['comissoes']['Coletivo'] as $parcela => $valor)
                                <tr>
                                    <td class="px-2 py-1 text-xs text-white border-b">Parcela {{ $parcela }}</td>
                                    <td class="px-2 py-1 text-sm text-dark border-b flex items-center space-x-2">
                                        <input
                                            type="number"
                                            name="comissoes[3][{{ $parcela }}]"
                                            value="{{ $valor }}"
                                            step="0.01"
                                            disabled
                                            class="w-full px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        />
                                        <button
                                            class="text-dark hover:text-blue-700 edit-btn bg-green-200 p-1 rounded"
                                            data-corretor-id="{{ $corretor['id'] }}"
                                            data-plano-id="3"
                                            data-parcela="{{ $parcela }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6m2 2v14m1.324-12.598l-9.833 13.598c-.138.192-.329.353-.552.462l-4.02 1.865c-.299.138-.657-.028-.75-.352l-2-7c-.077-.269-.045-.561.087-.8L14.263 3.324c.279-.399.81-.502 1.21-.259L20.324 5.402z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <!-- Tabela de Comissões Empresarial (Inicialmente Oculta) -->
                        <table
                            class="w-full table-auto border-collapse border border-gray-200 hidden plano-empresarial"
                            id="plano-empresarial-{{ $loop->index }}"
                        >
                            <thead class="bg-orange-300">
                            <tr>
                                <th class="px-2 py-1 text-sm font-medium text-white border-b">Parcela</th>
                                <th class="px-2 py-1 text-sm font-medium text-white border-b">Comissão (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($corretor['comissoes']['Empresarial'] as $parcela => $valor)
                                <tr>
                                    <td class="px-2 py-1 text-xs text-white border-b">Parcela {{ $parcela }}</td>
                                    <td class="px-2 py-1 text-sm text-dark border-b flex items-center space-x-2">
                                        <input
                                            type="number"
                                            name="comissoes[5][{{ $parcela }}]"
                                            value="{{ $valor }}"
                                            step="0.01"
                                            disabled
                                            class="w-full px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        />
                                        <button
                                            class="text-dark hover:text-blue-700 edit-btn bg-green-200 p-1 rounded"
                                            data-corretor-id="{{ $corretor['id'] }}"
                                            data-plano-id="5"
                                            data-parcela="{{ $parcela }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6m2 2v14m1.324-12.598l-9.833 13.598c-.138.192-.329.353-.552.462l-4.02 1.865c-.299.138-.657-.028-.75-.352l-2-7c-.077-.269-.045-.561.087-.8L14.263 3.324c.279-.399.81-.502 1.21-.259L20.324 5.402z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Links de navegação no footer -->
                    <div class="flex justify-between mt-4">
                        <a
                            href="#"
                            class="text-sm font-medium text-white hover:underline"
                            onclick="mostrarPlano('empresarial', {{ $loop->index }})"><< Empresarial</a>
                        <a
                            href="#"
                            class="text-sm font-medium text-white hover:underline"
                            onclick="mostrarPlano('coletivo', {{ $loop->index }})">Coletivo >></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>





@section('scripts')
        <script>

            document.getElementById('searchCorretor').addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const items = document.querySelectorAll('#corretores-grid > div');

                items.forEach(item => {
                    const nomeCorretor = item.querySelector('h2').textContent.toLowerCase();
                    if (nomeCorretor.includes(query)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });




            // Função para alternar entre os planos
            function mostrarPlano(plano, index) {
                const individual = document.getElementById(`plano-individual-${index}`);
                const coletivo = document.getElementById(`plano-coletivo-${index}`);
                const empresarial = document.getElementById(`plano-empresarial-${index}`);
                const voltar = document.getElementById(`voltar-individual-${index}`);

                individual.classList.add('hidden');
                coletivo.classList.add('hidden');
                empresarial.classList.add('hidden');
                voltar.classList.add('hidden');

                if (plano === 'individual') {
                    individual.classList.remove('hidden');
                } else if (plano === 'coletivo') {
                    coletivo.classList.remove('hidden');
                    voltar.classList.remove('hidden');
                } else if (plano === 'empresarial') {
                    empresarial.classList.remove('hidden');
                    voltar.classList.remove('hidden');
                }
            }






            // Buscar inputs e alternar seus estados para edição
            document.addEventListener('click', function(e) {
                if (e.target.closest('.edit-btn')) {
                    const btn = e.target.closest('.edit-btn');
                    const corretorId = btn.dataset.corretorId;
                    const planoId = btn.dataset.planoId;
                    const parcela = btn.dataset.parcela;

                    // Encontre o input correspondente
                    const input = btn.previousElementSibling;

                    if (input.disabled) {
                        // Habilitar o input para edição
                        input.disabled = false;
                        input.focus();

                        // Alterar ícone para um botão de salvar
                        btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 012 0v4a1 1 0 01-2 0V7zm1 8a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" clip-rule="evenodd" />
                        </svg>
                    `;

                        // Adicionar evento de salvar
                        btn.onclick = function() {
                            salvarComissao(input.value, corretorId, planoId, parcela, btn, input);
                        };
                    }
                }
            });

            // Salvar a comissão via AJAX
            function salvarComissao(valor, corretorId, planoId, parcela, btn, input) {
                const payload = {
                    corretor_id: corretorId,
                    plano_id: planoId,
                    parcela: parcela,
                    valor: valor,
                    _token: '{{ csrf_token() }}',
                };

                fetch('{{ route("comissoes.salvar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Exibir mensagem de sucesso
                            alert('Comissão salva com sucesso!');

                            // Desabilitar o input novamente
                            input.disabled = true;

                            // Alterar botão de volta ao estado de editar
                            btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6m2 2v14m1.324-12.598l-9.833 13.598c-.138.192-.329.353-.552.462l-4.02 1.865c-.299.138-.657-.028-.75-.352l-2-7c-.077-.269-.045-.561.087-.8L14.263 3.324c.279-.399.81-.502 1.21-.259L20.324 5.402z" />
                        </svg>
                    `;

                            // Remover evento de salvar do botão
                            btn.onclick = null;
                        } else {
                            alert('Erro ao salvar a comissão.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao salvar:', error);
                        alert('Ocorreu um erro ao salvar a comissão.');
                    });
            }
        </script>
    @endsection
</x-app-layout>
