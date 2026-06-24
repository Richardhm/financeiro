$(document).ready(function(){
    function toggleLoading(show) {
        DOM.loadingOverlay.classList.toggle('active', show);
    }



    $('#sincronizar-parcelas').on('click', function () {
            toggleLoading(true);
            $.ajax({
                url: '/folha/folha-america/sincronizar-parcelas',
                type: 'POST',
                dataType: 'json',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },

                success: function (response) {

                    //console.log(response);
                    if (response.success) {
                        const dados = response.resumo; // Resumo enviado pelo backend
                        const registrosPorPagina = 5; // Quantidade de registros por página
                        let paginaAtual = 1; // Página inicial




                        // Função para construir a tabela com os dados
                        const renderizarTabela = (pagina) => {
                            const inicio = (pagina - 1) * registrosPorPagina; // Índice inicial
                            const fim = inicio + registrosPorPagina; // Índice final
                            const dadosPagina = dados.slice(inicio, fim); // Pegando os dados da página atual

                            // Verifica se há dados para renderizar
                            if (dadosPagina.length === 0) {
                                $('#tabelaResumo').html('<p class="text-gray-500">Nenhuma informação disponível.</p>');
                                return;
                            }



                            // Construção da tabela HTML
                            let tabelaHTML = '<table class="w-full text-left border-collapse">';
                            tabelaHTML += '<thead>';
                            tabelaHTML += '<tr><th>Corretor</th><th>Individual</th><th>Coletivo</th><th>Empresarial</th><th>Vidas</th><th>Estágio</th><th>Descrição</th></tr>';
                            tabelaHTML += '</thead>';
                            tabelaHTML += '<tbody>';
                            dadosPagina.forEach((item) => {
                                tabelaHTML += `<tr>
                                <td class="border-b p-2">${item.nome_corretor}</td>
                                <td class="border-b p-2">${item.vidas_individual}</td>
                                <td class="border-b p-2">${item.vidas_coletivo}</td>
                                <td class="border-b p-2">${item.vidas_empresarial}</td>
                                <td class="border-b p-2">${item.quantidade_vidas}</td>
                                <td class="border-b p-2">${item.estagio}</td>
                                <td class="border-b p-2">${item.distribuicao}</td>
                            </tr>`;
                            });
                            tabelaHTML += '</tbody>';
                            tabelaHTML += '</table>';

                            // Adicionar tabela ao modal
                            $('#tabelaResumo').html(tabelaHTML);

                            // Atualizar paginação
                            renderizarPaginacao(pagina);
                        };

                        // Função para construir os botões de paginação
                        const renderizarPaginacao = (paginaSelecionada) => {
                            console.log("paginaSelecionada ",paginaSelecionada);
                            console.log("dados ",dados.length);
                            const totalPaginas = Math.ceil(dados.length / registrosPorPagina); // Calcula o total de páginas
                            console.log("totalPaginas ",totalPaginas)

                            let paginacaoHTML = '<div class="flex justify-center space-x-2 mt-3">';

                            for (let i = 1; i <= totalPaginas; i++) {
                                paginacaoHTML += `<button class="px-3 py-1 ${
                                    i === paginaSelecionada
                                        ? 'bg-blue-500 text-white'
                                        : 'bg-gray-200 text-gray-600 hover:bg-blue-300 hover:text-white'
                                } rounded" data-pagina="${i}">${i}</button>`;
                            }



                            paginacaoHTML += '</div>';

                            console.log("paginacaoHTML ",paginacaoHTML);

                            $('#paginacao').html(paginacaoHTML); // Adiciona os botões de paginação ao container
                        };

                        // Inicializar tabela e paginação
                        renderizarTabela(paginaAtual);
                        //$('#paginacao').remove(); // Remove paginação anterior, se existir
                        //$('#tabelaResumo').after('<div id="paginacao"></div>'); // Adiciona o container ao final da tabela

                        // Evento para botões de paginação
                        $(document).on('click', '#paginacao button', function () {
                            const novaPagina = parseInt($(this).data('pagina')); // Pega a página clicada
                            paginaAtual = novaPagina;
                            renderizarTabela(novaPagina);
                        });

                        // Exibir o modal
                        $('#resumoModal').removeClass('hidden').addClass('flex');
                    } else {
                        alert('Erro: ' + response.message); // Caso o backend retorne falha
                    }
                },
                error: function () {
                    alert('Erro ao processar a sincronização.');
                },
                complete: function(){
                    toggleLoading(false);

                }
            });

    });

    $('#fecharModal').on('click', function () {
        $('#resumoModal').addClass('hidden');
        $('#modalDetalhesCliente').addClass('hidden');
    });


    $('body').on('click','.detalhe-cliente-btn',async function(e){
        e.preventDefault();
        let plano = $(this).data('cliente');
        let codigo = $(this).data('id');
        await carregarDetalhesCliente(plano, codigo);
        return false;
    });

    // Abrir a modal com os dados da linha
    $("body").on("click", ".editar-comissao", function () {
        const id = this.dataset.id;
        const nomeCliente = this.dataset.nome;
        const contrato = this.dataset.contrato;
        const valorPlano = this.dataset.valor_plano;
        const porcentagem = this.dataset.porcentagem;
        const comissao = this.dataset.comissao;
        const incluir = this.dataset.incluir;

        // Preencher os valores na modal
        $("#comissao-id").val(id);
        $("#modal-nome-cliente").val(nomeCliente);
        $("#modal-codigo-contrato").val(contrato);
        $("#modal-valor-plano").val(valorPlano);
        $("#modal-porcentagem").val(porcentagem);
        $("#modal-valor-comissao").val(comissao);

        // Abrir modal com animação
        $("#modal-editar-comissao").removeClass("hidden").addClass("visible");
    });

    // Fechar a modal
    $("#cancelar-edicao").on("click", function () {
        fecharModal();
    });

    // Fechar modal ao clicar fora dela
    $("#modal-editar-comissao").on("click", function (e) {
        if ($(e.target).is("#modal-editar-comissao")) {
            fecharModal();
        }
    });

    // Submeter formulário para atualizar a comissão
    $("#form-editar-comissao").on("submit", async function (e) {
        e.preventDefault();

        const id = $("#comissao-id").val();
        const porcentagem = parseFloat($("#modal-porcentagem").val());
        const valorPlano = $("#modal-valor-plano").val();

        const zerarComissao = $("#zerar_comissao").is(":checked"); // Retorna true ou false


        try {
            // Envio dos dados para o backend
            const response = await fetch("/folha/atualizar-comissao", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
                body: JSON.stringify({
                    id,
                    porcentagem,
                    valor:valorPlano,
                    incluir:zerarComissao
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Fechar modal
                fecharModal();
                // Atualizar a tabela chamando renderizarDetalhesCorretor
                const corretorId = corretorAtualDetalhes; // ID do corretor atual

                const planoSelecionado = $("#valor_plano_id_clicado").val(); // Plano selecionado na visualização
                console.log("PLano ",planoSelecionado);
                const detalhesResponse = await fetch(
                    `/folha/api/clientes-corretor?corretor_id=${corretorId}&plano_id=${planoSelecionado}`
                );
                const detalhesData = await detalhesResponse.json();

                // Renderizar novos dados com a função já existente
                renderizarDetalhesCorretor(detalhesData, planoSelecionado);
                // Exibir notificação de sucesso
                alert("Comissão atualizada com sucesso!");
            } else {
                alert("Erro ao atualizar comissão: " + data.message);
            }
        } catch (error) {
            console.error(error);
            alert("Erro ao conectar com o servidor.");
        }
    });

    // Fechar modal
    function fecharModal() {
        $("#modal-editar-comissao").removeClass("visible").addClass("hidden");
    }

    $("body").on('keyup','#valor_premiacao',function(){
        $(this).mask('#.##0,00',{reverse:true});
    });

    $("body").on('keyup','#valor_fixo',function(){
        $(this).mask('#.##0,00',{reverse:true});
    });

    $("body").on('keyup','#valor_vale',function(){
        $(this).mask('#.##0,00',{reverse:true});
    });

    $(document).on("click", ".adicionar-vale", async function () {
        const valorPremiacao = $("#valor_vale").val();
        const corretorId = corretorAtualDetalhes;

        try {
            const response = await fetch(`/folha/vale/adicionar`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                body: JSON.stringify({ user_id: corretorId, valor: valorPremiacao }),
            });
            const data = await response.json();

            if (data.success) {
                // Atualiza os cards de Individual/Coletivo/Empresarial com os dados reais do servidor
                if (data.resumo && window.atualizarCardsComResumo) {
                    window.atualizarCardsComResumo(data.resumo);
                }

                // Atualiza o card Vale no header
                const valeCardSpan = document.querySelector('[data-plano="vale"] span.font-bold');
                if (valeCardSpan) valeCardSpan.textContent = formatMoney(data.total_vale);

                // Atualiza total do vendedor na listagem esquerda (comissões - vale - fixo)
                const r = data.resumo;
                let totalLiquido = 0;
                if (r) {
                    totalLiquido = (parseFloat(r.individual?.valor_total  || 0)
                                 + parseFloat(r.coletivo?.valor_total     || 0)
                                 + parseFloat(r.empresarial?.valor_total  || 0)
                                 + parseFloat(r.odonto?.total_comissao    || 0)
                                 - parseFloat(r.vale?.total_comissao      || 0)
                                 - parseFloat(r.fixo?.total_comissao      || 0));
                    const vendorSpan = document.querySelector(`.corretor-item[data-corretor-id="${corretorId}"] .total_a_receber`);
                    if (vendorSpan) vendorSpan.textContent = formatMoney(Math.max(0, totalLiquido));
                }

                // Atualiza o total na tabela de confirmados se estiver visível
                const confTotal = document.getElementById('conf-table-total');
                if (confTotal) confTotal.textContent = formatMoney(data.novo_total_liquido);

                // Captura referências para re-afirmar após o SweetAlert fechar
                // (o clique no OK pode propagar ao card Vale e sobrescrever o total)
                const _corretorId = corretorId;
                const _totalLiquido = Math.max(0, totalLiquido);

                Swal.fire({
                    icon: 'success',
                    title: 'Vale adicionado!',
                    html: `<div style="font-size:14px;line-height:1.9;color:#f3f4f6">
                        Vale: <strong style="color:#f87171">${formatMoney(data.total_vale)}</strong><br>
                        Líquido a pagar: <strong style="color:#34d399">${formatMoney(data.novo_total_liquido)}</strong>
                    </div>`,
                    background: '#1f2937',
                    color: '#f3f4f6',
                    confirmButtonColor: '#7c3aed',
                    confirmButtonText: 'OK',
                    timer: 4000,
                    timerProgressBar: true,
                }).then(() => {
                    // Re-afirma o total correto após fechar (defensive: cobre qualquer
                    // click-through ou re-render que ocorra ao dispensar o dialog)
                    const span = document.querySelector(`.corretor-item[data-corretor-id="${_corretorId}"] .total_a_receber`);
                    if (span) span.textContent = formatMoney(_totalLiquido);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao adicionar vale.',
                    background: '#1f2937',
                    color: '#f3f4f6',
                    confirmButtonColor: '#7c3aed',
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro de conexão',
                text: 'Não foi possível conectar ao servidor.',
                background: '#1f2937',
                color: '#f3f4f6',
                confirmButtonColor: '#7c3aed',
            });
        }
    });



    $(document).on("click", ".adicionar-fixo", async function () {
        const valorPremiacao = $("#valor_fixo").val();

        const corretorId = corretorAtualDetalhes;
        try {
            const response = await fetch(`/folha/fixo/adicionar`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                body: JSON.stringify({ user_id: corretorId, valor: valorPremiacao }),
            });

            const data = await response.json();

            if (data.success) {
                const fixoSpan = document.querySelector('[data-plano="fixo"] span.font-bold');
                if (fixoSpan) fixoSpan.textContent = formatMoney(data.total_fixo);
                Swal.fire({
                    icon: 'success',
                    title: 'Fixo salvo!',
                    html: `<div style="font-size:14px;color:#f3f4f6">Valor fixo: <strong style="color:#60a5fa">${formatMoney(data.total_fixo)}</strong></div>`,
                    background: '#1f2937',
                    color: '#f3f4f6',
                    confirmButtonColor: '#7c3aed',
                    timer: 3000,
                    timerProgressBar: true,
                });
            } else {
                alert(data.message || 'Erro ao adicionar fixo.');
            }
        } catch (error) {
            alert("Erro ao conectar com o servidor.");
        }

    });

    $(document).on("click", ".adicionar-premiacao", async function () {
        const valorPremiacao = parseFloat($("#valor_premiacao").val());
        const corretorId = corretorAtualDetalhes;
        try {
            const response = await fetch(`/folha/premiacoes/adicionar`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                body: JSON.stringify({ user_id: corretorId, valor: valorPremiacao }),
            });

            const data = await response.json();

            if (data.success) {
                $(".total_premiacao").text("R$ "+data.valor);
            } else {
                alert("Erro ao adicionar premiação.");
            }
        } catch (error) {
            alert("Erro ao conectar com o servidor.");
        }

    });

    $(document).on('click', '[data-plano]', async function () {
        const planoId = $(this).data('plano');
        $("#valor_plano_id_clicado").val(planoId);

        if (window.setActiveCard) window.setActiveCard(planoId);

        // Confirmados em PARCEIROS_MODE: delega para atualizarTabelaClientes (que busca do DB)
        if (window.PARCEIROS_MODE && planoId === 'confirmados') {
            await atualizarTabelaClientes({ tipo: 'confirmados', success: true, clientes: [] });
            return;
        }

        try {
            $('#clientes-itens').html('<div class="text-center text-gray-300">Carregando...</div>');
            // Em modo parceiros, filtra apenas status_apto_pagar=0 (não confirmadas)
            const modo = window.PARCEIROS_MODE ? '&modo=parceiro' : '';
            const response = await fetch(
                `/folha/api/clientes-corretor?corretor_id=${corretorAtualDetalhes}&plano_id=${planoId}${modo}`
            );
            const data = await response.json();

            if (data.success) {
                atualizarTabelaClientes(data);
            } else {
                $('#clientes-itens').html('<div class="text-center text-red-400">Nenhum cliente encontrado para este plano.</div>');
            }
        } catch (error) {
            console.error('Erro ao carregar dados do plano:', error);
            $('#clientes-itens').html('<div class="text-center text-red-400">Erro ao carregar os clientes.</div>');
        }
    });


    $(document).on('click', '.btn-confirmar-comissao', function () {
        const comissaoId = $(this).data('id-comissao');
        const clienteId  = $(this).data('id-cliente');
        Swal.fire({
            title: 'Confirmação',
            text: 'Confirmar essa parcela como recebida?',
            icon: 'question',
            background: '#1f2937',
            color: '#f3f4f6',
            showCancelButton: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Cancelar',
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const response = await fetch(`/folha/api/confirmar-comissao`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: comissaoId }),
                });
                const data = await response.json();
                if (data.success) {
                    if (window.PARCEIROS_MODE) {
                        // Em modo parceiros: após confirmar o Não Recebido,
                        // navega para o card Individual para que o usuário confirme para a folha
                        Swal.fire({
                            icon: 'success', title: 'Incluído!',
                            text: 'Parcela movida para o Individual. Confirme-a para a folha.',
                            background: '#1f2937', color: '#f3f4f6',
                            timer: 2000, showConfirmButton: false,
                        }).then(async () => {
                            // Carrega a tabela Individual (filtrada por status_apto_pagar=0 em modo parceiro)
                            const r2 = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorAtualDetalhes}&plano_id=1&modo=parceiro`);
                            const d2 = await r2.json();
                            if (d2.success) {
                                atualizarTabelaClientes(d2);
                                if (window.setActiveCard) window.setActiveCard('1');
                                if (window.atualizarCardsComResumo) window.atualizarCardsComResumo(d2.resumo);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Sucesso!', text: 'Comissão confirmada com sucesso.', icon: 'success',
                            background: '#1f2937', color: '#f3f4f6',
                        });
                        const r2 = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorAtualDetalhes}&plano_id=adiantamento`);
                        const d2 = await r2.json();
                        atualizarTabelaClientes(d2);
                        await atualizarCardsResumo(corretorAtualDetalhes);
                    }
                } else {
                    Swal.fire('Erro', data.message || 'Ocorreu um erro durante a confirmação.', 'error');
                }
            } catch (error) {
                Swal.fire('Erro', 'Ocorreu um erro ao conectar com o servidor.', 'error');
            }
        });
    });

    function atualizarTotalReceber(valor,desconto,adicionar) {

        const corretorSelecionado = document.querySelector('.corretor-destaque');
        if (!corretorSelecionado) return; // Sai da função se nenhum corretor tiver a classe `corretor-destaque`

        // Localiza o total_a_receber específico do corretor destacado
        const totalElement = corretorSelecionado.querySelector('.total_a_receber');
        if (!totalElement) return;

        // Extrai o valor atual do total e remove os caracteres de moeda
        let totalAtual = parseFloat(
            totalElement.textContent.replace('R$', '').replace('.', '').replace(',', '.')
        ) || 0;

        // Adiciona ou subtrai no total
        if (adicionar) {
            totalAtual += valor;
            if (desconto > 0) totalAtual -= desconto; // Subtrai o desconto, se houver
        } else {
            totalAtual -= valor;
            if (desconto > 0) totalAtual += desconto;
        }

        // Atualiza o total na div correspondente
        totalElement.textContent = `${formatMoney(totalAtual)}`;

    }

// Listener para alterações nos checkboxes
    document.addEventListener('change', (event) => {

        if (event.target.classList.contains('checkbox-selecionar-cliente')) {
            const checkbox = event.target;
            const clienteId   = checkbox.dataset.id;
            const incluirNaFolha = checkbox.checked;

            // Atualiza o total a receber na listagem da esquerda imediatamente
            const valor   = parseFloat(checkbox.dataset.valor)   || 0;
            const desconto= parseFloat(checkbox.dataset.desconto) || 0;
            atualizarTotalReceber(valor, desconto, incluirNaFolha);

            // Persiste no banco e atualiza os cards com os dados reais do servidor
            fetch('/folha/comissoes/atualizar-folha', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ cliente_id: clienteId, folha: incluirNaFolha ? 1 : 0 })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert('Erro ao atualizar a folha de pagamento! Tente novamente.');
                } else if (data.resumo && window.atualizarCardsComResumo) {
                    window.atualizarCardsComResumo(data.resumo);
                }
            })
            .catch(err => console.error('Erro no Ajax:', err));
        }

    });


    $("body").on("click", ".criar-excel", function () {
        fetch('/folha/gerente/planos/excel')
            .then(response => response.json())
            .then(data => {
                // Cabeçalhos do Excel
                const headers = ["Administradora", "Data", "Cod.Externo", "Cliente", "Parcela", "Valor Plano", "Valor", "Corretor", "Plano", "Vidas"];
                // Preparar as linhas de dados
                const rows = data.map(item => [
                    item.administradora,
                    item.created_at,
                    item.codigo_externo,
                    item.cliente,
                    item.parcela,
                    item.valor_plano,
                    item.valor_comissao,
                    item.corretor,
                    item.plano_nome,
                    item.quantidade_vidas,
                ]);

                // Adicionar os headers e dados ao Excel
                const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Planos em Aberto");

                // Gerar e baixar o arquivo Excel
                XLSX.writeFile(wb, `planos_abertos_${new Date().toISOString().slice(0, 10)}.xlsx`);
            })
            .catch(error => console.error("Erro ao exportar os dados:", error));
    });








});
