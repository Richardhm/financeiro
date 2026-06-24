<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfirmacaoPagamentoController;
use App\Http\Controllers\EstrelaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\FolhaAmerica;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\ParcelasConfirmacaoController;
use App\Http\Controllers\PdfContratoController;
use App\Http\Controllers\PdfContratoEmpresarialController;
use App\Http\Controllers\CadastrarIndividualController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('home.index'))->middleware('auth');

Route::middleware('auth')->group(function () {

    /****Home****/
    Route::get('/', [HomeController::class, 'index'])->name('home.index');
    Route::get('/dashboard', [HomeController::class, 'dashboardFinanceiro'])->name('dashboard');
    Route::get('/dashboard/corretor/{id}', [HomeController::class, 'paginaCorretor'])->name('dashboard.corretor.perfil');
    Route::get('/dashboard/corretor/{id}/json', [HomeController::class, 'detalheCorretor'])->name('dashboard.corretor.detalhe');
    Route::get('/dashboard/balanco', [HomeController::class, 'balancaCorretora'])->name('dashboard.balanco');
    Route::get('/tabela_preco', [HomeController::class, 'search'])->name('orcamento.search.home');
    Route::post('/tabela_preco', [HomeController::class, 'tabelaPrecoResposta'])->name('tabela.preco.resposta');
    Route::post('/mudar/grafico/ano', [HomeController::class, 'mudarGraficoAno'])->name('mudar.grafico.ano');
    Route::post('/tabela_preco/cidade/resposta', [HomeController::class, 'tabelaPrecoRespostaCidade'])->name('tabela.preco.resposta.cidade');
    Route::get('/consultar', [HomeController::class, 'consultar'])->name('home.administrador.consultar');
    Route::post('/consultar', [HomeController::class, 'consultarCarteirnha'])->name('consultar.carteirinha');
    Route::post('/dashboard/filtrar/user', [HomeController::class, 'dashboardFiltrarUser'])->name('dashboard.filtrar.user');
    Route::post('/dashboard/semestre', [HomeController::class, 'dashboardSemestre'])->name('dashboard.semestre');
    Route::post('/dashboard/mes', [HomeController::class, 'dashboardMes'])->name('dashboard.mes');
    Route::post('/dashboard/ano', [HomeController::class, 'dashboardAno'])->name('dashboard.ano');
    Route::post('/dashboard/ranking/semestral', [HomeController::class, 'dashboardRankingSemestral'])->name('dashboard.ranking.semestral');
    Route::post('/dashboard/ranking/mes', [HomeController::class, 'dashboardRankingmes'])->name('dashboard.ranking.mes');
    Route::post('/dashboard/tabela/ranking/mes', [HomeController::class, 'dashboardTabelaRankingmes'])->name('dashboard.tabela.ranking.mes');
    Route::post('/dashboard/ranking/ano', [HomeController::class, 'dashboardRankingano'])->name('dashboard.ranking.ano');
    Route::post('/dashboard/grafico/ano', [HomeController::class, 'dashboardGraficoAno'])->name('grafico.mudar.ano');
    /****Fim Home****/

    /*****Meus Clientes************/
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/listar', [ClienteController::class, 'listar'])->name('clientes.listar');
    Route::get('/clientes/coletivo/listar', [ClienteController::class, 'listarColetivo'])->name('clientes.coletivo.listar');
    Route::get('/clientes/empresarial/listar', [ClienteController::class, 'listarEmpresarial'])->name('clientes.empresarial.listar');
    /*****Fim Meus Clientes************/

    /****Financeiro****/
    Route::get('/financeiro',[FinanceiroController::class,'index'])->name('financeiro.index');
    Route::get('/contratos/cadastrar/individual',[FinanceiroController::class,'formCreate'])->name('financeiro.formCreate');
    Route::get('/contratos/cadastrar/individual/manual',[CadastrarIndividualController::class,'showForm'])->name('individual.manual.create');
    Route::post('/contratos/cadastrar/individual/manual',[CadastrarIndividualController::class,'store'])->name('individual.manual.store');
    Route::get("/financeiro/individual/em_geral/{mes?}",[FinanceiroController::class,'geralIndividualPendentes'])->name('financeiro.individual.geralIndividualPendentes');
    Route::post('/financeiro/change/individual',[FinanceiroController::class,'changeIndividual'])->name('financeiro.changeFinanceiro');
    Route::post('/financeiro/change/coletivo',[FinanceiroController::class,'changeColetivo'])->name('financeiro.changeFinanceiroColetivo');
    Route::post('/financeiro/change/empresarial',[FinanceiroController::class,'changeEmpresarial'])->name('financeiro.changeFinanceiroEmpresarial');
    Route::post('/financeiro/valores/change/empresarial',[FinanceiroController::class,'changeValoresEmpresarial'])->name('financeiro.changeValoresFinanceiroEmpresarial');
    Route::post('/financeiro/administradora/change',[FinanceiroController::class,'changeAdministradora'])->name('financeiro.administradora.change');
    Route::post('/contratos/montarPlanosIndividual',[FinanceiroController::class,'montarPlanosIndividual'])->name('contratos.montarPlanosIndividual');
    Route::post('/contratos/individual',[FinanceiroController::class,'storeIndividual'])->name('individual.store');
    Route::get('/contratos/cadastrar/empresarial',[FinanceiroController::class,'formCreateEmpresarial'])->name('contratos.create.empresarial');
    Route::post('/financeiro/sincronizar_baixas/ja_existente',[FinanceiroController::class,'sincronizarBaixasJaExiste'])->name('financeiro.sincronizar.baixas.jaexiste');
    Route::post('/financeiro/estorno/individual',[FinanceiroController::class,'uploadEstorno'])->name('financeiro.estorno.individual');
    Route::post('/financeiro/estorno/confirmar',[FinanceiroController::class,'confirmarEstorno'])->name('estorno.confirmar');

    Route::post('/financeiro/select/individual',[FinanceiroController::class,'selectIndividual'])->name('financeiro.select.individual');
    Route::post('/financeiro/empresarial/confirmacao',[FinanceiroController::class,'empresarialConfirmacao'])->name('financeiro.empresarial.confirmacao');

    Route::get('/contratos/cadastrar/coletivo',[FinanceiroController::class,'formCreateColetivo'])->name('contratos.create.coletivo');
    Route::get('/contratos/cadastrar/coletivo/pdf',[PdfContratoController::class,'showUpload'])->name('pdf.coletivo.upload');
    Route::post('/contratos/cadastrar/coletivo/pdf/parse',[PdfContratoController::class,'parsePdf'])->name('pdf.coletivo.parse');
    Route::post('/contratos/cadastrar/coletivo/pdf/store',[PdfContratoController::class,'store'])->name('pdf.coletivo.store');
    Route::get('/contratos/{contrato}/pdf/download',[PdfContratoController::class,'downloadPdf'])->name('pdf.coletivo.download');

    Route::get('/contratos/cadastrar/empresarial/pdf',[PdfContratoEmpresarialController::class,'showUpload'])->name('pdf.empresarial.upload');
    Route::post('/contratos/cadastrar/empresarial/pdf/parse',[PdfContratoEmpresarialController::class,'parsePdf'])->name('pdf.empresarial.parse');
    Route::post('/contratos/cadastrar/empresarial/pdf/store',[PdfContratoEmpresarialController::class,'store'])->name('pdf.empresarial.store');
    Route::get('/contratos/empresarial/{id}/pdf/download',[PdfContratoEmpresarialController::class,'downloadPdf'])->name('pdf.empresarial.download');
    Route::post('/contratos/montarPlanos',[FinanceiroController::class,'montarPlanos'])->name('contratos.montarPlanos');
    Route::post('/contratos',[FinanceiroController::class,'store'])->name('contratos.store');
    Route::get('/financeiro/detalhes/coletivo/{id}',[FinanceiroController::class,'detalhesContratoColetivo'])->name('financeiro.detalhes.contrato.coletivo');
    Route::post('/financeiro/modal/individual',[FinanceiroController::class,'modalIndividual'])->name('financeiro.modal.contrato.individual');
    Route::post('/financeiro/modal/coletivo',[FinanceiroController::class,'modalColetivo'])->name('financeiro.modal.contrato.coletivo');
    Route::post('/financeiro/modal/empresarial',[FinanceiroController::class,'modalEmpresarial'])->name('financeiro.modal.contrato.empresarial');
    Route::post('/financeiro/gerente/modal/empresarial',[FinanceiroController::class,'modalEmpresarialGerente'])->name('financeiro.gerente.modal.contrato.empresarial');
    Route::post('/financeiro/excluir',[FinanceiroController::class,'excluirCliente'])->name('financeiro.excluir.cliente');
    Route::post('/financeiro/empresarial/excluir',[FinanceiroController::class,'excluirEmpresarial'])->name('financeiro.excluir.empresarial');
    Route::post('/financeiro/cancelar/empresarial',[FinanceiroController::class,'cancelarEmpresarial'])->name('financeiro.cancelar.empresarial');
    Route::post('/financeiro/mudarEstadosColetivo',[FinanceiroController::class,'mudarEstadosColetivo'])->name('financeiro.mudarStatusColetivo');
    Route::post('/financeiro/cancelados',[FinanceiroController::class,'cancelarContrato'])->name('financeiro.contrato.cancelados');

    Route::post('/financeiro/baixaDaData',[FinanceiroController::class,'baixaDaData'])->name('financeiro.baixa.data');
    Route::post('/financeiro/individual/baixaDaData',[FinanceiroController::class,'baixaDaDataIndividual'])->name('financeiro.baixa.individual');

    Route::post('/financeiro/empresarial/baixaDaDataEmpresarial',[FinanceiroController::class,'baixaDaDataEmpresarial'])->name('financeiro.baixa.data.empresarial');
    Route::post('/financeiro/empresarial/updateComissaoLancada',[FinanceiroController::class,'updateComissaoLancadaEmpresarial'])->name('financeiro.empresarial.update.comissao.lancada');
    Route::post('/financeiro/editarCampoIndividualmente',[FinanceiroController::class,'editarCampoIndividualmente'])->name('financeiro.editar.campoIndividualmente');
    Route::post('/financeiro/editarCampoColetivo',[FinanceiroController::class,'editarCampoColetivo'])->name('financeiro.editar.campoColetvivo');
    Route::post('/financeiro/editarCampoEmpresarial',[FinanceiroController::class,'editarCampoEmpresarial'])->name('financeiro.editar.campoEmpresarial');

    Route::post('/financeiro/desfazer/coletivo',[FinanceiroController::class,'desfazerColetivo'])->name('desfazer.tarefa.coletivo');
    Route::post('/financeiro/desfazer/individual',[FinanceiroController::class,'desfazerIndividual'])->name('desfazer.tarefa.individual');

    Route::post('/financeiro/sincronizar',[FinanceiroController::class,'sincronizarDados'])->name('financeiro.sincronizar');
    Route::get('/financeiro/detalhes/{id}',[FinanceiroController::class,'detalhesContrato'])->name('financeiro.detalhes.contrato');
    Route::post('/financeiro/analise/coletivo',[FinanceiroController::class,'emAnaliseColetivo'])->name('financeiro.analise.coletivo');
    Route::post('/financeiro/analise/empresarial',[FinanceiroController::class,'emAnaliseEmpresarial'])->name('financeiro.analise.empresarial');
    Route::post('/financeiro/boleto/coletivo',[FinanceiroController::class,'emissaoColetivo'])->name('financeiro.analise.boleto');
    Route::get('/financeiro/zerar/tabela',[FinanceiroController::class,'zerarTabelaFinanceiro'])->name('financeiro.zerar.financeiro');
    Route::get('/financeiro/coletivo/em_geral',[FinanceiroController::class,'coletivoEmGeral'])->name('financeiro.coletivo.em_geral');
    Route::get('/contratos/empendentes/empresarial',[FinanceiroController::class,'listarContratoEmpresaPendentes'])->name('contratos.listarEmpresarial.listarContratoEmpresaPendentes');
    Route::post('/financeiro/sincronizar/cancelados',[FinanceiroController::class,'sincronizarCancelados'])->name('financeiro.sincronizar.cancelados');
    Route::post('/financeiro/atualizar_dados',[FinanceiroController::class,'atualizarDados'])->name('financeiro.atualizar.dados');
    Route::post('/financeiro/sincronizar_baixas',[FinanceiroController::class,'sincronizarBaixas'])->name('financeiro.sincronizar.baixas');
    Route::post('/financeiro/sincronizar/coletivo',[FinanceiroController::class,'sincronizarDadosColetivo'])->name('financeiro.sincronizar.coletivo');
    Route::post('/contratos/empresarial/financeiro',[FinanceiroController::class,'storeEmpresarialFinanceiro'])->name('contratos.storeEmpresarial.financeiro');
    Route::post("/odonto/create",[FinanceiroController::class,'storeOdonto'])->name('odonto.create');
    Route::get("/odonto/listar",[FinanceiroController::class,'listarOdonto'])->name('odonto.listar');
    Route::post("/odonto/excluir",[FinanceiroController::class,'excluirOdonto'])->name('odonto.excluir');
    Route::get("/estorno/listar",[FinanceiroController::class,'listarEstorno'])->name('estorno.listar');
    /****Fim Financeiro****/


    /*******************Gerente America***************************************/
    Route::prefix('folha')->name('folha.america.')->group(function () {
        Route::get('/', [FolhaAmerica::class, 'index'])->name('index');
        Route::get('/corretor/{id}', [FolhaAmerica::class, 'obterDetalhesCorretor'])->name('corretor.detalhes');
        Route::post('/gerar', [FolhaAmerica::class, 'gerarFolhaPagamento'])->name('gerar');
        Route::post('/gerarcorretora', [FolhaAmerica::class, 'gerarFolhaPagamentoCorretora'])->name('gerar.corretora');
        Route::get('/download/{file}', [FolhaAmerica::class, 'downloadPDF'])->name('download');
        Route::get('/api/clientes-corretor', [FolhaAmerica::class, 'obterClientesPorPlanoECorretor'])->name('clientes.corretor');
        Route::get('/api/resumo-atualizado', [FolhaAmerica::class, 'obterResumoAtualizado'])->name('folha.api.resumo-atualizado');
        Route::get('/modal/cliente', [FolhaAmerica::class, 'obterDetalhesClienteModal'])->name('clientes.modal.detalhe');
        Route::get('/api/clientes-plano', [FolhaAmerica::class, 'obterClientesPorPlano'])->name('clientes.plano');
        Route::get('/consultar-geradas', [FolhaAmerica::class, 'consultarFolhasGeradas'])->name('consultar.geradas');
        Route::post('/reverter', [FolhaAmerica::class, 'reverterFolha'])->name('reverter');
        Route::get('/cliente/{id}/detalhes', [FolhaAmerica::class, 'obterDetalhesCliente'])->name('folha.america.cliente.detalhes');
        Route::get('/corretor/{id}/resumo', [FolhaAmerica::class, 'obterResumoPorPlanoCorretor'])->name('corretor.resumo');
        Route::get('/corretor/{id}/clientes', [FolhaAmerica::class, 'obterClientesPlano'])->name('corretor.clientes');
        Route::post('/premiacoes/adicionar', [FolhaAmerica::class, 'adicionar']);
        Route::post('/fixo/adicionar', [FolhaAmerica::class, 'adicionarFixo']);
        Route::post('/vale/adicionar', [FolhaAmerica::class, 'adicionarVale']);
        Route::post('/selecionar/mes', [FolhaAmerica::class, 'selecionarMes'])->name('selecionar-mes');
        Route::post('/selecionar/finalizar-mes', [FolhaAmerica::class, 'finalizarMes'])->name('finalizar-mes');
        Route::post('/atualizar-comissao', [FolhaAmerica::class, 'atualizarComissao'])->name('atualizar-comissao');
        Route::post('/comissoes/atualizar-folha', [FolhaAmerica::class, 'atualizarFolha'])->name('atualizar-folha');
        Route::post('/api/confirmar-comissao', [FolhaAmerica::class, 'confirmarComissao'])->name('folha.america.confirmar-comissao');
        Route::get('/gerente/planos/excel',[FolhaAmerica::class, 'exportarPlanosParaExcel']);

        Route::get('/historico',[FolhaAmerica::class, 'historicoFolha'])->name('historico');
        Route::get('/historico/pdf',[FolhaAmerica::class, 'gerarPdfHistoricoFolhaClt'])->name('historico.pdf');

        Route::post('/folha-america/sincronizar-parcelas', [FolhaAmerica::class, 'sincronizarParcelas'])->name('folha.america.sincronizar');

        Route::get('/folha-parceiros', [FolhaAmerica::class, 'indexFolhaParceiros'])->name('folha-parceiros');
        Route::post('/folha-parceiros/{id}/finalizar', [FolhaAmerica::class, 'finalizarParceiro'])->name('folha-parceiros.finalizar');
        Route::get('/folha-parceiros/historico', [FolhaAmerica::class, 'indexHistoricoParceiros'])->name('folha-parceiros.historico');
        Route::post('/folha-parceiros/historico/{id}/pdf', [FolhaAmerica::class, 'gerarPdfHistorico'])->name('folha-parceiros.historico.pdf');
        Route::get('/folha-parceiros/{id}/periodos-finalizados', [FolhaAmerica::class, 'periodosFinalizados'])->name('folha-parceiros.periodos-finalizados');
        Route::post('/api/parceiro/confirmar', [FolhaAmerica::class, 'confirmarParcelaParaParceiro'])->name('parceiro.confirmar');
        Route::post('/api/parceiro/remover', [FolhaAmerica::class, 'removerParcelaDeConfirmados'])->name('parceiro.remover');

        Route::get('/faixas-clt', [FolhaAmerica::class, 'indexFaixasClt'])->name('faixas-clt');
        Route::post('/faixas-clt', [FolhaAmerica::class, 'salvarFaixaClt'])->name('faixas-clt.salvar');
        Route::put('/faixas-clt/{id}', [FolhaAmerica::class, 'atualizarFaixaClt'])->name('faixas-clt.atualizar');
        Route::delete('/faixas-clt/{id}', [FolhaAmerica::class, 'deletarFaixaClt'])->name('faixas-clt.deletar');

        Route::get('/regras-pj', [FolhaAmerica::class, 'indexFaixasPj'])->name('regras-pj');
        Route::post('/regras-pj', [FolhaAmerica::class, 'salvarFaixaPj'])->name('regras-pj.salvar');
        Route::put('/regras-pj/{id}', [FolhaAmerica::class, 'atualizarFaixaPj'])->name('regras-pj.atualizar');
        Route::delete('/regras-pj/{id}', [FolhaAmerica::class, 'deletarFaixaPj'])->name('regras-pj.deletar');

        Route::get('/parceiros-config', [FolhaAmerica::class, 'indexParceiros'])->name('parceiros-config');
        Route::post('/parceiros-config', [FolhaAmerica::class, 'salvarParceiro'])->name('parceiros-config.salvar');
        Route::delete('/parceiros-config/{id}', [FolhaAmerica::class, 'deletarParceiro'])->name('parceiros-config.deletar');

        Route::get('/parceiros/regras', [FolhaAmerica::class, 'indexRegrasParceiro'])->name('parceiros.regras');
        Route::post('/parceiros/regras', [FolhaAmerica::class, 'salvarRegraParceiro'])->name('parceiros.regras.salvar');
        Route::put('/parceiros/regras/{id}', [FolhaAmerica::class, 'atualizarRegraParceiro'])->name('parceiros.regras.atualizar');
        Route::delete('/parceiros/regras/{id}', [FolhaAmerica::class, 'deletarRegraParceiro'])->name('parceiros.regras.deletar');

        Route::get('/comissao-corretora', [FolhaAmerica::class, 'indexComissaoCorretora'])->name('comissao-corretora');
        Route::post('/comissao-corretora', [FolhaAmerica::class, 'salvarComissaoCorretora'])->name('comissao-corretora.salvar');
        Route::delete('/comissao-corretora/{id}', [FolhaAmerica::class, 'deletarComissaoCorretora'])->name('comissao-corretora.deletar');
        Route::post('/comissao-corretora/recalcular', [FolhaAmerica::class, 'recalcularValorCorretora'])->name('comissao-corretora.recalcular');

        Route::get('/parceiros/pagamentos', [FolhaAmerica::class, 'pagamentosParceiros'])->name('parceiros.pagamentos');
        Route::get('/parceiros/pagamentos/preview', [FolhaAmerica::class, 'previewPagamentosParceiros'])->name('parceiros.pagamentos.preview');
        Route::post('/parceiros/pagamentos/gerar', [FolhaAmerica::class, 'gerarPagamentosParceiros'])->name('parceiros.pagamentos.gerar');
        Route::get('/parceiros/pagamentos/historico', [FolhaAmerica::class, 'historicoPagamentosParceiros'])->name('parceiros.pagamentos.historico');
    });
    /*******************Fim Gerente America***********************************/


    /******Estrela********/
    Route::get('/estrela', [EstrelaController::class, 'index'])->name('estrela.index');
    /******Fim Estrela********/



    /*******************Confirmar Adiantamento***********************************/
    Route::post("/confirmar/adiantamento", [ConfirmacaoPagamentoController::class, 'processarConfirmacaoPagamento'])->name('financeiro.adiantamento.confirmar');
    /*******************Fim Confirmar Adiantamento***********************************/

    /*******************Confirmar Parcela***********************************/
    Route::post('/parcela/confirmar', [ParcelasConfirmacaoController::class, 'processarConfirmacaoParcela'])->name('parcelas.confirmar');
    /*******************Fim Confirmar Parcela***********************************/

    /*******Corretores*********/
    Route::get('/corretores', [ProfileController::class, 'listar'])->name('corretores.listar');
    Route::get('/list/corretores', [ProfileController::class, 'listUser'])->name('corretores.list');
    Route::post('/store/corretores', [ProfileController::class, 'storeUser'])->name('corretores.store');
    Route::post('/destroy/corretore', [ProfileController::class, 'destroyUser'])->name('destroy.corretor');
    Route::post('/alterar/corretor', [ProfileController::class, 'alterarUser'])->name('corretores.alterar');
    Route::post('/alterar/user/corretor', [ProfileController::class, 'alterarUserCLT'])->name('corretores.user.alterar');
    Route::post('/corretor/show', [ProfileController::class, 'show'])->name('corretor.show');
    Route::post('/corretor/excluir', [ProfileController::class, 'excluir'])->name('corretor.excluir');
    Route::post('/corretor/codigo/atualizar', [ProfileController::class, 'atualizarCodigo'])->name('corretor.codigo.atualizar');
    /*******Fim Corretores*********/

    /***********PERFIL************/
    Route::get('/perfil', [ProfileController::class, 'perfil'])->name('perfil.index');
    Route::put('/perfil/alterar', [ProfileController::class, 'alterar'])->name('profile.alterar');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    /***********FIM PERFIL************/

});

require __DIR__.'/auth.php';
