// public/js/dashboard-caixa.js
let graficoCorretora, graficoPlanos;
let graficosVendedores = {};

function inicializarGraficos(dados) {
    console.log(dados);
    criarGraficoCorretora(dados.mensal_corretora);
    criarGraficoPlanos(dados.mensal_planos);
}

function criarGraficoCorretora(dados) {
    const ctx = document.getElementById('grafico-corretora').getContext('2d');

    const labels = gerarLabelsMessais(dados);
    const datasetRecebido = processarDadosMensais(dados.recebido, labels);
    const datasetPago = processarDadosMensais(dados.pago, labels);

    graficoCorretora = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Corretora Recebeu',
                data: datasetRecebido,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }, {
                label: 'Corretora Pagou',
                data: datasetPago,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' +
                                context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
}

function criarGraficoPlanos(dados) {
    console.log(dados);


    const ctx = document.getElementById('grafico-planos').getContext('2d');

    const labels = gerarLabelsMessais(dados);

    graficoPlanos = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Coletivo',
                data: processarDadosMensais(dados.coletivo, labels),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }, {
                label: 'Individual',
                data: processarDadosMensais(dados.individual, labels),
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                borderColor: 'rgb(245, 158, 11)',
                borderWidth: 1
            }, {
                label: 'Empresarial',
                data: processarDadosMensais(dados.empresarial, labels),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' +
                                context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
}

function criarGraficoVendedor(vendedorId, dados) {
    console.log('Criando gráfico para vendedor:', vendedorId);
    console.log('Dados recebidos:', dados);

    const canvasId = `grafico-vendedor-${vendedorId}`;
    const canvas = document.getElementById(canvasId);

    if (!canvas) {
        console.error(`Canvas não encontrado: ${canvasId}`);
        return;
    }

    const ctx = canvas.getContext('2d');

    // Destruir gráfico anterior se existir
    if (graficosVendedores[vendedorId]) {
        graficosVendedores[vendedorId].destroy();
        delete graficosVendedores[vendedorId];
    }

    // Processar dados para o gráfico
    const labels = gerarLabelsMessaisVendedor(dados);
    const datasetRecebido = processarDadosMensaisVendedor(dados.recebido, labels);
    const datasetPago = processarDadosMensaisVendedor(dados.pago, labels);

    console.log('Labels gerados:', labels);
    console.log('Dataset Recebido:', datasetRecebido);
    console.log('Dataset Pago:', datasetPago);

    // Verificar se há dados para exibir
    const temDados = datasetRecebido.some(val => val > 0) || datasetPago.some(val => val > 0);

    if (!temDados && labels.length === 0) {
        // Exibir mensagem de "sem dados"
        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center h-64 text-gray-500">
                <i class="fas fa-chart-line text-4xl mb-3 text-gray-300"></i>
                <p class="text-lg font-medium">Nenhum dado disponível</p>
                <p class="text-sm">Não há movimentação financeira para exibir</p>
            </div>
        `;
        return;
    }

    // Criar o gráfico
    graficosVendedores[vendedorId] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Corretora Recebeu',
                data: datasetRecebido,
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(168, 85, 247)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }, {
                label: 'Vendedor Recebeu',
                data: datasetPago,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(34, 197, 94)',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' +
                                parseFloat(context.parsed.y).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return 'R$ ' + parseFloat(value).toLocaleString('pt-BR');
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            elements: {
                point: {
                    hoverBorderWidth: 3
                }
            }
        }
    });

    console.log('Gráfico criado com sucesso para vendedor:', vendedorId);
}

function processarDadosMensaisVendedor(dados, labels) {
    const resultado = new Array(labels.length).fill(0);

    if (Array.isArray(dados)) {
        dados.forEach(item => {
            const mesFormatado = item.mes.toString().padStart(2, '0');
            const label = `${mesFormatado}/${item.ano}`;
            const index = labels.indexOf(label);

            if (index !== -1) {
                resultado[index] = parseFloat(item.total) || 0;
            }
        });
    }

    console.log('Dados processados:', resultado);
    return resultado;
}





// Funções auxiliares específicas para vendedor
function gerarLabelsMessaisVendedor(dados) {
    const meses = new Set();

    // Processar dados recebidos
    if (dados.recebido && Array.isArray(dados.recebido)) {
        dados.recebido.forEach(item => {
            const mesFormatado = item.mes.toString().padStart(2, '0');
            meses.add(`${mesFormatado}/${item.ano}`);
        });
    }

    // Processar dados pagos
    if (dados.pago && Array.isArray(dados.pago)) {
        dados.pago.forEach(item => {
            const mesFormatado = item.mes.toString().padStart(2, '0');
            meses.add(`${mesFormatado}/${item.ano}`);
        });
    }

    // Converter para array e ordenar
    const labelsArray = Array.from(meses).sort((a, b) => {
        const [mesA, anoA] = a.split('/').map(Number);
        const [mesB, anoB] = b.split('/').map(Number);

        if (anoA !== anoB) {
            return anoA - anoB;
        }
        return mesA - mesB;
    });

    console.log('Labels gerados:', labelsArray);
    return labelsArray;
}



function aplicarFiltros() {
    mostrarLoading();
    const planoId = document.getElementById('plano_id').value;

    fetch(`/dashboard/caixa-corretora/filtrar?plano_id=${planoId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            // Atualizar indicadores
            document.getElementById('indicadores-container').innerHTML = data.indicadores_html;

            // Atualizar gráficos
            atualizarGraficos(data.graficos);

            // Animação de sucesso
            mostrarNotificacao('Filtros aplicados com sucesso!', 'success');
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao aplicar filtros', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

function limparFiltros() {
    document.getElementById('plano_id').value = '';
    aplicarFiltros();
}

function carregarResumoVendedor(userId) {
    mostrarLoading();

    // Remover seleção anterior
    document.querySelectorAll('.vendedor-item').forEach(item => {
        item.classList.remove('bg-blue-50', 'border-blue-300');
        item.classList.add('border-gray-200');
    });

    // Adicionar seleção atual
    const vendedorItem = document.querySelector(`[data-vendedor-id="${userId}"]`);
    if (vendedorItem) {
        vendedorItem.classList.add('bg-blue-50', 'border-blue-300');
        vendedorItem.classList.remove('border-gray-200');
    }

    fetch(`/dashboard/vendedor/${userId}/resumo?corretora_id=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('Resposta completa:', data);

            if (data.success) {
                document.getElementById('resumo-vendedor').innerHTML = data.html;

                // Aguardar um pouco para o DOM ser atualizado
                setTimeout(() => {
                    if (data.grafico) {
                        console.log('Criando gráfico com dados:', data.grafico);
                        criarGraficoVendedor(userId, data.grafico);
                    } else {
                        console.warn('Nenhum dado de gráfico retornado');
                    }
                }, 200);
            } else {
                mostrarNotificacao('Erro ao carregar resumo: ' + (data.error || 'Erro desconhecido'), 'error');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            mostrarNotificacao('Erro ao carregar resumo do vendedor', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

function debugGraficoVendedor(vendedorId) {
    console.log('=== DEBUG GRÁFICO VENDEDOR ===');
    console.log('Vendedor ID:', vendedorId);

    const canvasId = `grafico-vendedor-${vendedorId}`;
    const canvas = document.getElementById(canvasId);

    console.log('Canvas ID procurado:', canvasId);
    console.log('Canvas encontrado:', canvas);

    if (canvas) {
        console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);
        console.log('Canvas parent:', canvas.parentElement);
    }

    console.log('Gráficos existentes:', Object.keys(graficosVendedores));
    console.log('Chart.js disponível:', typeof Chart !== 'undefined');

    // Listar todos os canvas na página
    const todosCanvas = document.querySelectorAll('canvas');
    console.log('Todos os canvas na página:', Array.from(todosCanvas).map(c => c.id));
}
function detalharClientes(userId, tipo) {
    console.log('Detalhando clientes:', { userId, tipo });
    mostrarLoading();

    fetch(`/dashboard/vendedor/${userId}/clientes?tipo=${tipo}&corretora_id=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                document.getElementById('resumo-vendedor').innerHTML = data.html;
            } else {
                mostrarNotificacao('Erro ao carregar clientes: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            mostrarNotificacao('Erro ao carregar clientes', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

function voltarResumoVendedor(userId) {
    console.log('Voltando para resumo do vendedor:', userId);
    carregarResumoVendedor(userId);
}

function carregarPaginaClientes(userId, tipo, page) {
    console.log('Carregando página:', { userId, tipo, page });
    mostrarLoading();

    const busca = document.getElementById('busca-cliente')?.value || '';

    fetch(`/dashboard/vendedor/${userId}/clientes?tipo=${tipo}&page=${page}&busca=${encodeURIComponent(busca)}&corretora_id=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('resumo-vendedor').innerHTML = data.html;
            } else {
                mostrarNotificacao('Erro ao carregar página: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao carregar página', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}
function verDetalheCliente(clienteId, userId) {
    mostrarLoading();

    fetch(`/dashboard/cliente/${clienteId}/detalhe?user_id=${userId}&corretora_id=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Criar modal ou substituir conteúdo
                document.getElementById('resumo-vendedor').innerHTML = data.html;
            } else {
                mostrarNotificacao('Erro ao carregar detalhes: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao carregar detalhes', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

// Adicionar ao dashboard-caixa.js

function verDetalheClienteModal(clienteId, userId) {
    mostrarLoading();

    fetch(`/dashboard/cliente/${clienteId}/detalhe?user_id=${userId}&corretora_id=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('conteudo-modal-cliente').innerHTML = data.html;
                document.getElementById('modal-cliente').classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevenir scroll do body
            } else {
                mostrarNotificacao('Erro ao carregar detalhes: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao carregar detalhes', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

function fecharModalCliente() {
    document.getElementById('modal-cliente').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restaurar scroll do body
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(event) {
    const modal = document.getElementById('modal-cliente');
    if (event.target === modal) {
        fecharModalCliente();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        fecharModalCliente();
    }
});

// Adicionar ao dashboard-caixa.js

function buscarClientes(userId, tipo) {
    const termoBusca = document.getElementById('busca-cliente').value;
    console.log('Buscando clientes:', { userId, tipo, termoBusca });
    mostrarLoading();

    fetch(`/dashboard/vendedor/${userId}/clientes?tipo=${tipo}&busca=${encodeURIComponent(termoBusca)}&corretora_id=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('resumo-vendedor').innerHTML = data.html;
            } else {
                mostrarNotificacao('Erro ao buscar clientes: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao buscar clientes', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

function limparBusca(userId, tipo) {
    console.log('Limpando busca:', { userId, tipo });
    // Limpar o campo se existir
    const campoBusca = document.getElementById('busca-cliente');
    if (campoBusca) {
        campoBusca.value = '';
    }
    // Recarregar sem busca
    detalharClientes(userId, tipo);
}

// Busca ao pressionar Enter
// Busca ao pressionar Enter
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' && event.target.id === 'busca-cliente') {
        event.preventDefault();

        // Encontrar o vendedor selecionado
        const vendedorSelecionado = document.querySelector('.vendedor-item.bg-blue-50');
        if (vendedorSelecionado) {
            const vendedorId = vendedorSelecionado.getAttribute('data-vendedor-id');

            // Detectar o tipo atual baseado na URL ou contexto
            const tipoAtual = 'todos'; // Pode ser melhorado para detectar o tipo atual

            buscarClientes(vendedorId, tipoAtual);
        }
    }
});

function fecharDetalheCliente() {
    // Voltar para a lista de clientes ou resumo do vendedor
    // Implementar lógica de navegação baseada no histórico
    history.back();
}

function mostrarLoading() {
    document.getElementById('loading-overlay').classList.remove('hidden');
}

function ocultarLoading() {
    document.getElementById('loading-overlay').classList.add('hidden');
}

function mostrarNotificacao(mensagem, tipo = 'info') {
    // Criar elemento de notificação
    const notificacao = document.createElement('div');
    notificacao.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

    // Definir cores baseadas no tipo
    const cores = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-white'
    };

    notificacao.className += ` ${cores[tipo] || cores.info}`;
    notificacao.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas fa-${tipo === 'success' ? 'check' : tipo === 'error' ? 'times' : 'info'}-circle"></i>
            <span>${mensagem}</span>
        </div>
    `;

    document.body.appendChild(notificacao);

    // Animar entrada
    setTimeout(() => {
        notificacao.classList.remove('translate-x-full');
    }, 100);

    // Remover após 3 segundos
    setTimeout(() => {
        notificacao.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notificacao);
        }, 300);
    }, 3000);
}

// Funções auxiliares (mantidas iguais)
function gerarLabelsMessais(dados) {
    const meses = new Set();

    Object.values(dados).forEach(dataset => {
        if (Array.isArray(dataset)) {
            dataset.forEach(item => {
                meses.add(`${item.mes.toString().padStart(2, '0')}/${item.ano}`);
            });
        }
    });

    return Array.from(meses).sort();
}

function processarDadosMensais(dados, labels) {
    const resultado = new Array(labels.length).fill(0);

    if (Array.isArray(dados)) {
        dados.forEach(item => {
            const label = `${item.mes.toString().padStart(2, '0')}/${item.ano}`;
            const index = labels.indexOf(label);
            if (index !== -1) {
                resultado[index] = parseFloat(item.total);
            }
        });
    }

    return resultado;
}

function atualizarGraficos(dados) {
    // Destruir gráficos existentes
    if (graficoCorretora) {
        graficoCorretora.destroy();
    }
    if (graficoPlanos) {
        graficoPlanos.destroy();
    }

    // Recriar com novos dados
    inicializarGraficos(dados);
}
