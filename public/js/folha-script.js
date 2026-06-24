/*****Mes Selecionar*****/

/*****Fim Mes Selecionar*****/

/**Inicializar JavaScript Depois do DOM**/
document.addEventListener('DOMContentLoaded', () => {

    // Seleção de mês migrada para o componente mes-selecao.blade.php

    const cardsResumo = document.querySelectorAll('.card-resumo');
    const modalClientes = document.getElementById('modal-clientes');
    const tabelaClientes = document.getElementById('modal-tabela-clientes');
    const pesquisaModal = document.getElementById('pesquisa-modal');
    const modalTitulo = document.getElementById('modal-titulo');

    // Função para abrir a modal
    const abrirModal = (titulo) => {
        modalClientes.classList.remove('hidden');
        modalClientes.classList.add('flex');
        modalTitulo.textContent = titulo;
    };

    // Função para fechar a modal
    window.fecharModal = () => {
        modalClientes.classList.add('hidden');
        modalClientes.classList.remove('flex');
        tabelaClientes.innerHTML = ''; // Limpar tabela ao fechar
    };

    // Adicionar evento de clique nos cards
    cardsResumo.forEach(card => {
        card.addEventListener('click', async function () {
            const planoId = card.dataset.planoId; // Captura o ID do plano selecionado
            try {
                // Chamar a API para obter os clientes
                const response = await fetch(`/folha/api/clientes-plano?plano_id=${planoId}`);
                const data = await response.json();

                if (data.success) {
                    // Renderizar os clientes na tabela
                    tabelaClientes.innerHTML = data.clientes.map((cliente, index) => `
                        <tr>
                            <td class="px-4 py-2">${index + 1}</td>
                            <td class="px-4 py-2">${cliente.administradora}</td>
                            <td class="px-4 py-2">${new Date(cliente.data_cadastro).toLocaleDateString('pt-BR')}</td>
                            <td class="px-4 py-2">${cliente.contrato_codigo || '-'}</td>
                            <td class="px-4 py-2">${cliente.cliente_nome}</td>
                            <td class="px-4 py-2">${cliente.corretor}</td>
                            <td class="px-4 py-2">${cliente.parcela || '-'}</td>
                            <td class="px-4 py-2">${new Date(cliente.vencimento).toLocaleDateString('pt-BR')}</td>
                            <td class="px-4 py-2">R$ ${parseFloat(cliente.valor_original_plano).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `).join('');

                    abrirModal(data.frase);
                }
            } catch (error) {
                console.error('Erro ao carregar clientes:', error);
            }
        });
    });



    pesquisaModal.addEventListener('input', function () {
        const termo = pesquisaModal.value.toLowerCase();
        const rows = tabelaClientes.querySelectorAll('tr');

        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(termo) ? '' : 'none';
        });
    });


    const searchInput = document.getElementById('pesquisa-corretores');
    const corretoresLista = document.getElementById('lista-corretores');
    const corretoresItens = corretoresLista.querySelectorAll('.corretor-item');

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();

        corretoresItens.forEach(item => {
            const nome = item.dataset.nome;
            if (nome.includes(query)) {
                item.style.display = 'flex'; // Mostra o corretor que bate com o filtro
            } else {
                item.style.display = 'none'; // Oculta os não correspondentes
            }
        });
    });


    // Delegation para o select de planos
    document.addEventListener('change', async (e) => {
        if (e.target?.id === 'select-plano' && corretorAtualDetalhes) {
            const planoId = e.target.value;
            await carregarDetalhesCorretor(corretorAtualDetalhes, planoId);
            e.target.focus();
        }
    });

    // Botão gerar folha
    DOM.btnGerarFolha.addEventListener('click', gerarFolhaPagamento);
    DOM.btnGerarFolhaCorretora.addEventListener('click', gerarFolhaPagamentoCorretora);

    // Delegation para cliques nos itens de corretor
    document.querySelector('.max-h-96').addEventListener('click', (e) => {
        const corretorItem = e.target.closest('[data-corretor-id]');
        if(corretorItem) {
            const corretorId = corretorItem.dataset.corretorId;
            document.querySelectorAll('[data-corretor-id]').forEach(item => {
                item.classList.remove('corretor-destaque');
            });
            corretorItem.classList.add('corretor-destaque');
            carregarDetalhesCorretor(corretorId,1);
        }
    });

    // Filtros do formulário
    // DOM.formFiltros.addEventListener('submit', (e) => {
    //     toggleLoading(true);
    // });
});
/**Fim Inicializar JavaScript Depois do DOM**/

document.getElementById('finalizar-mes')?.addEventListener('click', async function () {
    const confirmacao = confirm('Deseja realmente finalizar o mês atual?');
    if (!confirmacao) return;

    try {
        const response = await fetch('/folha/selecionar/finalizar-mes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Erro ao finalizar mês.');
    }
});

// Variáveis globais
let corretoresSelecionados = new Set();
let corretorAtualDetalhes = null;
const filtrosAtuais = {
    data_inicio: '{{ $dataInicio }}',
    data_fim: '{{ $dataFim }}',
    corretor_id: '{{ $corretor_id }}',
    plano_id: '{{ $plano_id }}'
};
// Elementos DOM cacheados
const DOM = {
    loadingOverlay: document.getElementById('loading-overlay'),
    btnGerarFolha: document.getElementById('btnGerarFolha'),
    btnGerarFolhaCorretora: document.getElementById('btnGerarFolhaCorretora'),
    clientesLista: document.getElementById('clientes-itens'),
    clientesHeader: document.getElementById('clientes-header'),
    detalhesInicial: document.getElementById('detalhes-inicial'),
    detalhesLoading: document.getElementById('detalhes-loading'),
    detalhesConteudo: document.getElementById('detalhes-conteudo'),
    detalhesCliente: document.getElementById('detalhes-cliente'),
    formFiltros: document.getElementById('form-filtros')
};
// Mostra/oculta loading global
function toggleLoading(show) {
    DOM.loadingOverlay.classList.toggle('active', show);
}

// Formata valores monetários
function formatMoney(value) {
    return parseFloat(value).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatMoneySemCifrao(value) {
    return parseFloat(value).toLocaleString('pt-BR', {
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Atualiza botão de gerar folha
function atualizarBotaoGerar() {
    const count = corretoresSelecionados.size; // Tamanho real do Set

    // Atualiza o estado e texto do botão
    DOM.btnGerarFolha.disabled = count === 0; // Desabilita se não houver seleções
    DOM.btnGerarFolha.innerHTML = `
        <i class="fas fa-file-invoice-dollar mr-2"></i>
            ${count > 0 ? `Gerar Folha (${count} corretor${count > 1 ? 'es' : ''})` : 'Gerar Folha'}
    `;

    DOM.btnGerarFolhaCorretora.disabled = count === 0; // Desabilita se não houver seleções
    DOM.btnGerarFolhaCorretora.innerHTML = `
        <i class="fas fa-file-invoice-dollar mr-2"></i>
            ${count > 0 ? `Gerar Folha (${count} corretora${count > 1 ? 'es' : ''})` : 'Gerar Folha'}
    `;



}

// Toggle corretor selecionado
function toggleCorretor(corretorId, checkbox = null) {
    if (!checkbox) {
        checkbox = document.querySelector(`input[data-corretor-id="${corretorId}"]`);
    }

    if (checkbox.checked) {
        // Adiciona ao Set caso esteja marcado
        corretoresSelecionados.add(corretorId);
    } else {
        // Remove do Set caso esteja desmarcado
        corretoresSelecionados.delete(corretorId);
    }

    atualizarBotaoGerar();
}

// Selecionar/deselecionar todos
function selecionarTodos() {
    document.querySelectorAll('.corretor-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        toggleCorretor(checkbox.dataset.corretorId, checkbox);
    });
}

function deselecionarTodos() {
    document.querySelectorAll('.corretor-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        toggleCorretor(checkbox.dataset.corretorId, checkbox);
    });
}

// Carrega detalhes do corretor
async function carregarDetalhesCorretor(corretorId, planoId = '1') {
    corretorAtualDetalhes = corretorId;

    // Estado de loading
    DOM.detalhesInicial.classList.add('hidden');
    DOM.detalhesLoading.classList.remove('hidden');
    DOM.detalhesCliente.classList.remove('hidden');
    DOM.detalhesConteudo.classList.add('hidden');

    try {
        const response = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorId}&plano_id=${planoId}`);
        const data = await response.json();
        await renderizarDetalhesCorretor(data,planoId);
    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        DOM.clientesLista.innerHTML = `
                <div class="text-red-400 p-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Ocorreu um erro ao carregar os detalhes.
                    <button onclick="carregarDetalhesCorretor(${corretorId})" class="text-blue-300 ml-2">
                        Tentar novamente
                    </button>
                </div>
            `;
    } finally {
        DOM.detalhesLoading.classList.add('hidden');
        DOM.detalhesConteudo.classList.remove('hidden');
    }
}

// Renderiza os detalhes do corretor


async function atualizarCardsResumo(corretorId) {
    try {
        const response = await fetch(`/folha/api/resumo-atualizado?corretor_id=${corretorId}`);
        const data = await response.json();
        if (data.success) {
            const resumo = data.resumoPorPlano;

            // Atualizar os valores dos cards específicos
            const individualCard = document.querySelector('[data-plano="1"]');
            const adiantamentoCard = document.querySelector('[data-plano="adiantamento"]');

            // Atualizar o card Individual
            if (individualCard) {
                individualCard.innerHTML = `
                    <h4 class="text-sm font-semibold mb-1">Individual</h4>
                    <div class="text-xs">
                        <div><strong>Contratos:</strong> ${resumo.individual.total_contratos ?? 0}</div>
                        <div><strong>Vidas:</strong> ${resumo.individual.total_vidas ?? 0}</div>
                        <div>
                            <strong>Total:</strong>
                            <span class="font-bold">${parseFloat(resumo.individual.valor_total ?? 0).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}</span>
                        </div>
                    </div>
                `;
            }

            // Atualizar o card Adiantamento
            if (adiantamentoCard) {
                adiantamentoCard.innerHTML = `
                    <h4 class="text-sm font-semibold mb-2">Adiantamento</h4>
                    <div class="text-xs">
                        <div><strong>Contratos:</strong> ${resumo.adiantamento.total_contratos ?? 0}</div>
                        <div><strong>Vidas:</strong> ${resumo.adiantamento.total_vidas ?? 0}</div>
                        <div>
                            <strong>Total:</strong>
                            <span class="font-bold">${parseFloat(resumo.adiantamento.valor_total ?? 0).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}</span>
                        </div>
                    </div>
                `;
            }
        } else {
            console.error('Erro ao atualizar resumo:', data.message);
        }
    } catch (error) {
        console.error('Erro ao atualizar os cards:', error);
    }
}






async function carregarDetalhesCliente(plano,codigoContrato) {

    try {
        const response = await fetch(`/folha/modal/cliente?plano=${plano}&codigo=${codigoContrato}`);
        const data = await response.json();

        if (data.success) {
            let conteudo = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Lado esquerdo: Informações Gerais e Informações do Plano -->
                    <div class="space-y-6">
                        <!-- Informações Gerais -->
                        <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                            <h3 class="text-lg font-bold border-b border-gray-500 pb-2 mb-4">Informações Gerais</h3>
                            <div class="space-y-2">
            `;

            if (plano == 1 || plano == 3) {
                conteudo += `
                                <p><strong>Nome:</strong> ${data.cliente.nome || 'Não informado'}</p>
                                <p><strong>CPF:</strong> ${data.cliente.cpf || 'Não informado'}</p>
                                <p><strong>Email:</strong> ${data.cliente.email || 'Não informado'}</p>
                                <p><strong>Celular:</strong> ${data.cliente.celular || 'Não informado'}</p>
                                <p><strong>Data de Nascimento:</strong> ${data.cliente.data_nascimento || 'Não informado'}</p>
                                <p><strong>Endereço:</strong>
                                    ${data.cliente.rua || ''}, ${data.cliente.bairro || ''},
                                    ${data.cliente.complemento || ''}, ${data.cliente.uf || ''}
                                </p>
                                <p><strong>Quantidade de Vidas:</strong> ${data.cliente.quantidade_vidas || 'Não informado'}</p>
                `;
            } else if (plano === 'Empresarial') {
                conteudo += `
                                <p><strong>Razão Social:</strong> ${data.empresa.razao_social || 'Não informado'}</p>
                                <p><strong>CNPJ:</strong> ${data.empresa.cnpj || 'Não informado'}</p>
                                <p><strong>Responsável:</strong> ${data.empresa.responsavel || 'Não informado'}</p>
                                <p><strong>Email:</strong> ${data.empresa.email || 'Não informado'}</p>
                                <p><strong>Celular:</strong> ${data.empresa.celular || 'Não informado'}</p>
                `;
            }

            conteudo += `
                            </div>
                        </div>

                        <!-- Informações do Plano -->
                        <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                            <h3 class="text-lg font-bold border-b border-gray-500 pb-2 mb-4">Informações do Plano</h3>
                            <div class="space-y-2">
                                <p><strong>Valor do Plano:</strong>
                                    ${data.contrato.valor_plano ? parseFloat(data.contrato.valor_plano).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : 'Não informado'}
                                </p>
            `;

            if (plano === 'Empresarial') {
                conteudo += `
                                <p><strong>Valor Total:</strong>
                                    ${data.contrato.valor_total ? parseFloat(data.contrato.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : 'Não informado'}
                                </p>
                                <p><strong>Valor Saúde:</strong>
                                    ${data.contrato.valor_saude ? parseFloat(data.contrato.valor_saude).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : 'Não informado'}
                                </p>
                                <p><strong>Valor Odonto:</strong>
                                    ${data.contrato.valor_odonto ? parseFloat(data.contrato.valor_odonto).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : 'Não informado'}
                                </p>
                `;
            }

            conteudo += `
                            </div>
                        </div>
                    </div>

                    <!-- Lado direito: Parcelas -->
                    <div class="bg-gray-700 p-4 rounded-lg shadow-md">
                        <h3 class="text-lg font-bold border-b border-gray-500 pb-2 mb-4 text-center">Parcelas</h3>
                        <table class="w-full text-sm border-collapse border border-gray-600">
                            <thead>
                                <tr class="bg-gray-800">
                                    <th class="px-4 py-2 border border-gray-500">Parcela</th>
                                    <th class="px-4 py-2 border border-gray-500">Comissão</th>
                                    <th class="px-4 py-2 border border-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${
                                data.parcelas.map(parcela => `
                                    <tr class="hover:bg-gray-600">
                                        <td class="px-4 py-2 border border-gray-500">${parcela.numero}</td>
                                        <td class="px-4 py-2 border border-gray-500">
                                            ${parseFloat(parcela.valor_comissao).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                                        </td>
                                        <td class="px-4 py-2 border border-gray-500">
                                            ${parcela.paga ? '<span class="text-green-400 font-bold">Paga</span>' : '<span class="text-red-400 font-bold">Pendente</span>'}
                                        </td>
                                    </tr>
                                `).join('')
                                }
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            document.getElementById('conteudoModal').innerHTML = conteudo;
            document.getElementById('modalDetalhesCliente').classList.remove('hidden');
        }
    } catch (error) {
        alert('Erro ao carregar os detalhes do cliente.');
    }




}

// Fechar modal
document.getElementById('fecharModal').addEventListener('click', () => {
    document.getElementById('modalDetalhesCliente').classList.add('hidden');
});


async function gerarFolhaPagamentoCorretora() {
    if (corretoresSelecionados.size === 0) {
        Swal.fire('Atenção', 'Selecione pelo menos um corretor', 'warning');
        return;
    }

    const { isConfirmed } = await Swal.fire({
        title: 'Confirmar Geração',
        html: `Gerar folha para <strong>${corretoresSelecionados.size} corretor(es)</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Gerar',
        cancelButtonText: 'Cancelar'
    });

    if (!isConfirmed) return;

    toggleLoading(true);
    DOM.btnGerarFolhaCorretora.disabled = true;


    try {
        const response = await fetch('/folha/gerarcorretora', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                corretores: Array.from(corretoresSelecionados),
                data_inicio: filtrosAtuais.data_inicio,
                data_fim: filtrosAtuais.data_fim
            })
        });

        const data = await response.json();

        if (data.success) {
            window.open(data.download_url, '_blank');
            Swal.fire('Sucesso', 'Folha gerada com sucesso', 'success');
        } else {
            Swal.fire('Erro', data.message || 'Erro ao gerar folha', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', 'Não foi possível gerar a folha', 'error');
    } finally {
        toggleLoading(false);
        DOM.btnGerarFolhaCorretora.disabled = false;
    }
}

// Gerar folha de pagamento
async function gerarFolhaPagamento() {
    if (corretoresSelecionados.size === 0) {
        Swal.fire('Atenção', 'Selecione pelo menos um corretor', 'warning');
        return;
    }

    const { isConfirmed } = await Swal.fire({
        title: 'Confirmar Geração',
        html: `Gerar folha para <strong>${corretoresSelecionados.size} corretor(es)</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Gerar',
        cancelButtonText: 'Cancelar'
    });

    if (!isConfirmed) return;

    toggleLoading(true);
    DOM.btnGerarFolha.disabled = true;


    try {
        const response = await fetch('/folha/gerar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                corretores: Array.from(corretoresSelecionados),
                data_inicio: filtrosAtuais.data_inicio,
                data_fim: filtrosAtuais.data_fim
            })
        });

        const data = await response.json();

        if (data.success) {
            window.open(data.download_url, '_blank');
            Swal.fire('Sucesso', 'Folha gerada com sucesso', 'success');
        } else {
            Swal.fire('Erro', data.message || 'Erro ao gerar folha', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', 'Não foi possível gerar a folha', 'error');
    } finally {
        toggleLoading(false);
        DOM.btnGerarFolha.disabled = false;
    }
}

function adicionarFiltroTabelaClientes() {
    const inputPesquisa = document.getElementById('pesquisa-clientes');

    // Verificar se o elemento existe na página
    if (!inputPesquisa) return;

    inputPesquisa.addEventListener('input', function () {
        const filtro = inputPesquisa.value.toLowerCase().trim();
        const linhasClientes = document.querySelectorAll('#table-clientes .cliente-row');

        linhasClientes.forEach((linha) => {
            const textoLinha = linha.textContent.toLowerCase();
            if (textoLinha.includes(filtro)) {
                linha.style.display = ''; // Mostra a linha
            } else {
                linha.style.display = 'none'; // Oculta a linha
            }
        });
    });
}


// Funções globais
window.selecionarCorretor = carregarDetalhesCorretor;
window.selecionarTodos = selecionarTodos;
window.deselecionarTodos = deselecionarTodos;
window.toggleCorretor = toggleCorretor;
