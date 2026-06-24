<?php

namespace App\Http\Controllers;

use App\Models\ComissoesCorretoresLancadas;
use App\Models\Contrato;
use App\Models\Administradora;
use App\Models\ComissoesCorretoresConfiguracoes;
use App\Models\ComissoesCorretoraConfiguracoes;
use App\Models\ComissoesCorretoraLancadas;
use App\Models\FaixaComissaoClt;
use App\Models\RegraComissaoPj;
use App\Models\Plano;
use App\Models\ParceirosConfigPagamento;
use App\Models\ParceirosRegraComissao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use phpDocumentor\Reflection\DocBlock\Tags\Author;

class FolhaAmerica extends Controller
{
    private $corretora_id = 1;
    private $plano_id = 1;
    public function __construct()
    {
        $this->corretora_id = auth()->user()->corretora_id;
    }

    public function index(Request $request)
    {
        // Plano especÃ­fico trabalhado na requisiÃ§Ã£o
        $plano_id = $request->get('plano_id', null); // Default Ã© plano_id = 1
        $menorData = $this->getMenorData($plano_id);
        $dataInicio = $menorData ?: Carbon::now()->subDays(30)->format('Y-m-d');
        $dataFim = $request->get('data_fim', Carbon::now()->format('Y-m-d'));
        $corretor_id = $request->get('corretor_id');
        $resumoGeral = $this->obterResumoGeral($dataInicio, $dataFim, $corretor_id, $plano_id);
        $resumoPorPlano = $this->obterResumoPorPlano();
        $corretores = $this->obterCorretoresComValores($dataInicio, $dataFim, $corretor_id, $plano_id);

        //dd($corretores);


        //dd($corretores);
        $listaCorretores = $this->obterListaCorretores($plano_id);
        $folhaMes = DB::table('folha_mes')
            ->where('corretora_id', auth()->user()->corretora_id)
            ->where('status', 0)
            ->first();
        if (!$folhaMes) {
            // Meses que jÃ¡ tiveram folha (qualquer status) para esta corretora
            $mesesUsados = DB::table('folha_mes')
                ->where('corretora_id', auth()->user()->corretora_id)
                ->pluck('mes')
                ->map(fn($m) => substr($m, 0, 7)) // "2025-10-01" â†’ "2025-10"
                ->flip()
                ->all();

            return view('folha.america.index', [
                'mesEmAberto'   => false,
                'proximosMeses' => $this->getProximos12Meses(),
                'mesesUsados'   => $mesesUsados,
                'folhaEmAberto' => false,
            ]);
        }
        $mesEmAberto = true;
        $mesAtual = Carbon::parse($folhaMes->mes)->locale('pt_BR')->translatedFormat('F/Y');
        $dadosMes = $folhaMes->mes; // Passamos o mÃªs para o frontend
        $folhaEmAberto = true;

        $faixasClt = FaixaComissaoClt::where('corretora_id', $this->corretora_id)
            ->orderBy('vidas_min')
            ->get(['id', 'nome', 'vidas_min', 'vidas_max']);

        return view('folha.america.index', compact(
            'resumoGeral',
            'resumoPorPlano',
            'corretores',
            'listaCorretores',
            'dataInicio',
            'dataFim',
            'corretor_id',
            'plano_id',
            'mesEmAberto',
            'mesAtual',
            'dadosMes',
            'folhaEmAberto',
            'faixasClt'
        ));
    }

    public function indexHistoricoParceiros()
    {
        $corretoraId = auth()->user()->corretora_id;

        $historico = DB::table('parceiros_folha_historico as h')
            ->join('users as u', 'h.user_id', '=', 'u.id')
            ->where('h.corretora_id', $corretoraId)
            ->select(
                'h.id',
                'h.user_id',
                'u.name as parceiro_nome',
                'h.frequencia',
                'h.periodo_inicio',
                'h.periodo_fim',
                'h.data_pagamento',
                'h.total_parcelas',
                'h.total_valor',
                'h.created_at'
            )
            ->orderBy('h.created_at', 'desc')
            ->get();

        return view('folha.america.historico-parceiros', compact('historico'));
    }

    public function periodosFinalizados(int $parceiroId): \Illuminate\Http\JsonResponse
    {
        $corretoraId = auth()->user()->corretora_id;

        $periodos = DB::table('parceiros_folha_historico')
            ->where('user_id', $parceiroId)
            ->where('corretora_id', $corretoraId)
            ->orderBy('periodo_inicio')
            ->pluck('periodo_fim', 'periodo_inicio')
            ->map(fn($fim, $inicio) => ['inicio' => $inicio, 'fim' => $fim])
            ->values();

        return response()->json(['periodos' => $periodos]);
    }

    public function gerarPdfHistorico(int $id, Request $request)
    {
        $corretoraId = auth()->user()->corretora_id;
        $tipo        = $request->input('tipo', 'parceiro');

        $historico = DB::table('parceiros_folha_historico')
            ->where('id', $id)
            ->where('corretora_id', $corretoraId)
            ->first();

        if (!$historico) {
            abort(404);
        }

        $corretor = DB::table('users')->find($historico->user_id);

        $ids = DB::table('comissoes_corretores_lancadas')
            ->where('parceiro_historico_id', $id)
            ->pluck('id');

        $comissoesNormais = collect();
        if ($ids->isNotEmpty()) {
            $comissoesNormais = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->leftJoin('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                ->leftJoin('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->whereIn('ccl.id', $ids)
                ->select(
                    'ccl.*',
                    DB::raw('CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social ELSE cl.nome END as cliente_nome'),
                    DB::raw('CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.cnpj ELSE cl.cpf END as cpf'),
                    DB::raw('CASE WHEN c.contrato_id IS NOT NULL THEN ct.codigo_externo WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.codigo_externo ELSE "---" END as contrato_codigo'),
                    DB::raw("CASE WHEN c.contrato_id IS NOT NULL AND c.plano_id = 1 THEN 'individual' WHEN c.contrato_id IS NOT NULL AND c.plano_id = 3 THEN 'coletivo' WHEN c.contrato_empresarial_id IS NOT NULL THEN 'empresarial' ELSE 'outro' END AS tipo_contrato"),
                    DB::raw('CASE WHEN c.contrato_id IS NOT NULL THEN cl.quantidade_vidas WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.quantidade_vidas ELSE cl.quantidade_vidas END as quantidade_vidas'),
                    'ccl.valor as valor_comissao',
                    DB::raw('CASE WHEN c.contrato_id IS NOT NULL THEN ct.valor_plano WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.valor_plano ELSE ccl.valor END as valor_plano'),
                    DB::raw('COALESCE(ct.desconto_corretor, 0) + COALESCE(ce.desconto_corretor, 0) as desconto_corretor'),
                    'ccl.parcela as parcela',
                    'ccl.data_baixa_gerente as data_vencimento'
                )
                ->get()
                ->groupBy('cliente_nome');
        }

        // Odonto: usa snapshot salvo no momento da finalização (pagou já foi marcado 1)
        $odontoSnapshot = json_decode($historico->odonto_snapshot ?? '[]', true);
        $comissoesOdonto = collect($odontoSnapshot)->map(fn($item) => (object)[
            'cliente_nome'     => $item['nome'],
            'cpf'              => '',
            'contrato_codigo'  => 'ODON-' . $item['id'],
            'tipo_contrato'    => 'odonto',
            'quantidade_vidas' => 1,
            'parcela'          => null,
            'valor_comissao'   => $item['comissao'],
            'valor_plano'      => $item['valor'],
            'desconto_corretor'=> 0,
            'data_vencimento'  => $item['created_at'],
        ]);

        $comissoes = $comissoesNormais->flatten()->merge($comissoesOdonto);

        $totalIndividual  = $comissoes->where('tipo_contrato', 'individual')->sum('valor_comissao');
        $totalColetivo    = $comissoes->where('tipo_contrato', 'coletivo')->sum('valor_comissao');
        $totalEmpresarial = $comissoes->where('tipo_contrato', 'empresarial')->sum('valor_comissao');
        $totalOdonto      = $comissoes->where('tipo_contrato', 'odonto')->sum('valor_comissao');
        $dadosDesconto    = $comissoes->sum('desconto_corretor');
        // Vale: usa valor salvo no snapshot (pago já foi marcado 1)
        $totalVale        = (float) ($historico->total_vale ?? 0);
        $totalCorretor    = $totalIndividual + $totalColetivo + $totalEmpresarial + $totalOdonto - $dadosDesconto - $totalVale;

        $totalVidas = $comissoes->filter(fn($c) => $c->tipo_contrato !== 'estorno')->sum('quantidade_vidas');

        $dados = [[
            'corretor'    => $corretor,
            'is_parceiro' => true,
            'total'       => $totalCorretor,
            'comissoes'   => $comissoes->groupBy('tipo_contrato'),
            'vidas'       => $totalVidas,
            'contratos'   => $comissoes->count(),
            'totais_tipos' => [
                'individual'  => $totalIndividual,
                'coletivo'    => $totalColetivo,
                'empresarial' => $totalEmpresarial,
                'odonto'      => $totalOdonto,
                'premiacao'   => 0,
                'vale'        => $totalVale,
                'fixo'        => 0,
                'estorno'     => 0,
                'desconto'    => $dadosDesconto,
            ],
        ]];

        $pdfView  = $tipo === 'corretora' ? 'folha.america.pdf-corretora' : 'folha.america.pdf';
        $pdf      = PDF::loadView($pdfView, [
            'dados'        => $dados,
            'totalGeral'   => $totalCorretor,
            'dataGeracao'  => now()->format('d/m/Y H:i:s'),
        ]);

        $filename = 'folha_historico_' . $id . '_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';
        $pdf->save(storage_path('app/folhas/' . $filename));

        return response()->json([
            'success'      => true,
            'download_url' => route('folha.america.download', ['file' => $filename]),
        ]);
    }

    public function indexFolhaParceiros(Request $request)
    {
        $plano_id    = $request->get('plano_id', null);
        $menorData   = $this->getMenorData($plano_id);
        $dataInicio  = $menorData ?: Carbon::now()->subDays(30)->format('Y-m-d');
        $dataFim     = $request->get('data_fim', Carbon::now()->format('Y-m-d'));
        $corretor_id = $request->get('corretor_id');

        // Parceiros tÃªm periodicidade prÃ³pria â€” a folha deles Ã© independente da folha CLT/PJ.
        // NÃ£o bloqueia se nÃ£o houver folha_mes aberta; usa o mÃªs atual como referÃªncia.
        $folhaMes = DB::table('folha_mes')
            ->where('corretora_id', auth()->user()->corretora_id)
            ->where('status', 0)
            ->first();

        $mesAtual = $folhaMes
            ? Carbon::parse($folhaMes->mes)->locale('pt_BR')->translatedFormat('F/Y')
            : Carbon::now()->locale('pt_BR')->translatedFormat('F/Y');

        $dadosMes = $folhaMes
            ? $folhaMes->mes
            : Carbon::now()->format('Y-m-01');

        $resumoGeral    = $this->obterResumoGeral($dataInicio, $dataFim, $corretor_id, $plano_id);
        $resumoPorPlano = $this->obterResumoPorPlano();
        $corretores     = $this->obterCorretoresComValores($dataInicio, $dataFim, $corretor_id, $plano_id, 'parceiro');

        $parceirosConfig = DB::table('parceiros_config_pagamento')
            ->whereIn('user_id', collect($corretores)->pluck('id'))
            ->get()
            ->keyBy('user_id');

        // Total líquido por parceiro (confirmados − vale) para exibir no listing
        $parceirosIds = collect($corretores)->pluck('id');

        $confirmadosBruto = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->where('ccl.status_apto_pagar', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->whereIn('c.user_id', $parceirosIds)
            ->groupBy('c.user_id')
            ->select('c.user_id', DB::raw('SUM(ccl.valor) as total_confirmado'))
            ->pluck('total_confirmado', 'c.user_id');

        $valePorParceiro = DB::table('vale')
            ->where('pago', 0)
            ->whereIn('user_id', $parceirosIds)
            ->pluck('valor', 'user_id');

        $confirmadosPorParceiro = $confirmadosBruto->mapWithKeys(function ($total, $userId) use ($valePorParceiro) {
            $vale = (float) ($valePorParceiro[$userId] ?? 0);
            return [$userId => max(0, (float) $total - $vale)];
        });

        return view('folha.america.folha-parceiros', compact(
            'resumoGeral',
            'resumoPorPlano',
            'corretores',
            'dataInicio',
            'dataFim',
            'corretor_id',
            'plano_id',
            'mesAtual',
            'dadosMes',
            'folhaMes',
            'parceirosConfig',
            'confirmadosPorParceiro'
        ));
    }

    public function finalizarParceiro(Request $request, $parceiroId)
    {
        $corretoraId = auth()->user()->corretora_id;

        $parceiro = DB::table('users')
            ->where('id', $parceiroId)
            ->where('corretora_id', $corretoraId)
            ->where('tipo_contrato', 'parceiro')
            ->first();

        if (!$parceiro) {
            return response()->json(['success' => false, 'message' => 'Parceiro nÃ£o encontrado.'], 404);
        }

        try {
            DB::beginTransaction();

            $folhaMes = DB::table('folha_mes')
                ->where('corretora_id', $corretoraId)
                ->where('status', 0)
                ->first();

            $competencia = $folhaMes
                ? Carbon::parse($folhaMes->mes)->format('Y-m')
                : now()->format('Y-m');

            $this->aplicarRegraParceiro($parceiroId, $competencia);

            // Finaliza apenas as parcelas confirmadas (status_apto_pagar=1)
            $pendentes = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->where('c.user_id', $parceiroId)
                ->where('ccl.status_financeiro', 1)
                ->where('ccl.status_gerente', 1)
                ->where('ccl.valor', '!=', 0)
                ->where('ccl.finalizado', '!=', 1)
                ->where('ccl.status_apto_pagar', 1)
                ->select('ccl.id', 'ccl.valor')
                ->get();

            if ($pendentes->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma parcela confirmada para finalizar. Confirme parcelas primeiro.',
                ], 422);
            }

            $config     = DB::table('parceiros_config_pagamento')->where('user_id', $parceiroId)->first();
            $hoje       = now()->toDateString();
            $frequencia = $config?->frequencia ?? 'mensal';

            // Usa perÃ­odo enviado pelo front (selecionado pelo usuÃ¡rio) ou calcula automaticamente
            $periodoInicio = $request->input('periodo_inicio');
            $periodoFim    = $request->input('periodo_fim');

            if (!$periodoInicio || !$periodoFim) {
                [$periodoInicio, $periodoFim] = $this->calcularPeriodoParceiro($frequencia);
            }

            // Captura snapshot de odonto e vale ANTES de marcá-los como pagos
            $odontoItems = DB::table('odonto')
                ->where('user_id', $parceiroId)
                ->where('pagou', 0)
                ->get();

            $odontoSnapshot = $odontoItems->map(fn($o) => [
                'id'         => $o->id,
                'nome'       => $o->nome,
                'comissao'   => $o->comissao,
                'valor'      => $o->valor,
                'created_at' => $o->created_at,
            ])->values()->toArray();

            $totalOdonto = collect($odontoSnapshot)->sum('comissao');

            $totalValeFinalizacao = DB::table('vale')
                ->where('user_id', $parceiroId)
                ->where('pago', 0)
                ->sum('valor');

            $totalFinal = $pendentes->sum('valor') + $totalOdonto - $totalValeFinalizacao;

            // Cria registro histÃ³rico com snapshot
            $historicoId = DB::table('parceiros_folha_historico')->insertGetId([
                'user_id'         => $parceiroId,
                'corretora_id'    => $corretoraId,
                'frequencia'      => $frequencia,
                'periodo_inicio'  => $periodoInicio,
                'periodo_fim'     => $periodoFim,
                'data_pagamento'  => $hoje,
                'total_parcelas'  => $pendentes->count(),
                'total_valor'     => $totalFinal,
                'total_odonto'    => $totalOdonto,
                'total_vale'      => $totalValeFinalizacao,
                'odonto_snapshot' => json_encode($odontoSnapshot),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $ids = $pendentes->pluck('id');
            DB::table('comissoes_corretores_lancadas')
                ->whereIn('id', $ids)
                ->update([
                    'data_baixa_gerente_folha' => $hoje,
                    'finalizado'               => 1,
                    'status_apto_pagar'        => 1,
                    'parceiro_historico_id'    => $historicoId,
                    'updated_at'               => now(),
                ]);

            // Marca vale como pago
            DB::table('vale')
                ->where('user_id', $parceiroId)
                ->where('pago', 0)
                ->update(['pago' => 1, 'updated_at' => now()]);

            // Marca odonto como pago
            if ($odontoItems->isNotEmpty()) {
                DB::table('odonto')
                    ->whereIn('id', $odontoItems->pluck('id'))
                    ->update(['pagou' => 1, 'updated_at' => now()]);
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => "Folha de {$parceiro->name} finalizada. PerÃ­odo: {$periodoInicio} a {$periodoFim}.",
                'total_parcelas' => $pendentes->count(),
                'total_valor'    => number_format($pendentes->sum('valor'), 2, ',', '.'),
                'historico_id'   => $historicoId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function calcularPeriodoParceiro(string $frequencia): array
    {
        $hoje = Carbon::now();

        switch ($frequencia) {
            case 'semanal':
                // Semana de segunda a domingo
                return [
                    $hoje->copy()->startOfWeek()->toDateString(),
                    $hoje->copy()->endOfWeek()->toDateString(),
                ];
            case 'quinzenal':
                if ($hoje->day <= 15) {
                    return [
                        $hoje->copy()->startOfMonth()->toDateString(),
                        $hoje->copy()->startOfMonth()->addDays(14)->toDateString(),
                    ];
                }
                return [
                    $hoje->copy()->startOfMonth()->addDays(15)->toDateString(),
                    $hoje->copy()->endOfMonth()->toDateString(),
                ];
            default: // mensal e personalizado
                return [
                    $hoje->copy()->startOfMonth()->toDateString(),
                    $hoje->copy()->endOfMonth()->toDateString(),
                ];
        }
    }

    // Confirmar parcela para a folha do parceiro (persiste no DB via status_apto_pagar)
    public function confirmarParcelaParaParceiro(Request $request)
    {
        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID nÃ£o informado.'], 422);
        }

        DB::table('comissoes_corretores_lancadas')
            ->where('id', $id)
            ->update(['status_apto_pagar' => 1, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Remover parcela dos confirmados (volta para Individual/Coletivo/Empresarial)
    public function removerParcelaDeConfirmados(Request $request)
    {
        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID nÃ£o informado.'], 422);
        }

        DB::table('comissoes_corretores_lancadas')
            ->where('id', $id)
            ->update(['status_apto_pagar' => 0, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Gera uma lista com os prÃ³ximos 12 meses.
     */
    private function getProximos12Meses()
    {
        $hoje = Carbon::now()->subMonth();

        $proximosMeses = [];
        for ($i = 0; $i < 12; $i++) {
            $data = $hoje->copy()->addMonths($i)->startOfMonth();
            $proximosMeses[] = [
                'valor' => $data->toDateString(), // Exemplo: "2025-09-01"
                'nome' => $data->locale('pt_BR')->translatedFormat('F/Y'), // Exemplo: "Setembro/2025"
            ];
        }

        return $proximosMeses;
    }

    public function selecionarMes(Request $request)
    {
        $request->validate([
            'mes' => 'required|date_format:Y-m-d',
        ]);
        $corretoraId = auth()->user()->corretora_id;
        $folhaMes = DB::table('folha_mes')
            ->where('corretora_id', $corretoraId)
            ->where('status', 0) // Somente status aberto
            ->first();

        if ($folhaMes) {
            return response()->json([
                'success' => false,
                'message' => 'JÃ¡ existe um mÃªs em aberto para esta corretora.',
            ], 400);
        }

        // Criar um registro para o mÃªs selecionado
        DB::table('folha_mes')->insert([
            'mes' => $request->input('mes'),
            'status' => 0, // Folha aberta
            'corretora_id' => $corretoraId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'MÃªs selecionado com sucesso!',
        ]);
    }



    public function obterClientesPorPlano(Request $request)
    {
        $planoId = $request->get('plano_id'); // ObtÃ©m o ID do plano selecionado
        $query = DB::table('comissoes_corretores_lancadas as ccl')->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id');
        // Planos Individual e Coletivo
        if ($planoId == 1 || $planoId == 3) {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->join("users as u","u.id","=","cl.user_id")
                ->join(DB::raw('administradoras'), 'administradoras.id', '=', 'ct.administradora_id')
                ->where('ct.plano_id', $planoId) // Filtra pelo plano
                ->select(
                    'cl.nome as cliente_nome',
                    'u.name as corretor',
                    'administradoras.nome as administradora',
                    'ct.codigo_externo as contrato_codigo',
                    'ct.valor_plano as valor_original_plano',
                    'ccl.valor as valor_comissao',
                    'ccl.parcela',
                    'ccl.data as vencimento',
                    'ct.created_at as data_cadastro',
                    DB::raw("
                    CASE
                        WHEN cl.desconto_operadora IS NOT NULL THEN
                            ct.valor_plano - ((ct.valor_plano * cl.desconto_operadora) / 100)
                        ELSE
                            ct.valor_plano
                    END as valor_plano_ajustado
                "),

                    DB::raw("IFNULL(ccl.porcentagem_paga, '-') as porcentagem")
                );
        }

        // Planos Odonto
        elseif ($planoId == 'odonto') {
            $resultadoOdonto = DB::table('odonto')
                ->join('users','users.id',"=","odonto.user_id")
                ->select(
                    'odonto.nome as cliente_nome',
                    'users.name as corretor',
                    'odonto.comissao as valor_plano_ajustado',
                    'odonto.comissao as valor_original_plano',
                    'odonto.created_at as data_cadastro',
                    'odonto.created_at as vencimento',
                    DB::raw("'hapvida' as administradora")
                )
                ->where('pagou', 0) // Apenas contratos nÃ£o pagos
                ->get();
            $totalOdonto = $resultadoOdonto->sum('valor_comissao');
            return response()->json([
                'success' => true,
                'clientes' => $resultadoOdonto,
                'frase' => "Contratos Odonto",
                'total' => $totalOdonto,
            ]);
        }
        // Planos Empresarial
        elseif ($planoId == 'empresarial') {
            $query
                ->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                ->join("users as u","u.id","=","ce.user_id")
                ->select(
                    'ce.razao_social as cliente_nome',
                    'ce.cnpj as cpf',
                    'u.name as corretor',
                    'ce.codigo_externo as contrato_codigo',
                    'ce.valor_plano as valor_original_plano',
                    DB::raw("'Hapvida' as administradora"),
                    'ccl.valor as valor_comissao',
                    'ccl.parcela',
                    'ccl.data as vencimento',
                    'ce.created_at as data_cadastro'
                );
        }

        // Planos Estorno
        elseif ($planoId == 'estorno') {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->join('users','users.id',"=","cl.user_id")
                ->join(DB::raw('administradoras'), 'administradoras.id', '=', 'ct.administradora_id')
                ->where('ct.estorno', 1)
                ->whereNotNull('ct.valor_estorno')
                ->select(
                    'cl.nome as cliente_nome',
                    'users.name as corretor',
                    'administradoras.nome as administradora',
                    'ct.codigo_externo as contrato_codigo',
                    'ct.valor_estorno as valor_comissao',
                    'ct.valor_plano as valor_original_plano',
                    'ccl.parcela',
                    'ccl.data as vencimento',
                    'ct.created_at as data_cadastro'
                );
        }

        // Executa a consulta
        $clientes = $query
            ->where('c.corretora_id',auth()->user()->corretora_id)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            //->where('ccl.valor', '!=', 0)
            ->where('ccl.finalizado', '!=', 1)
            ->where('valor',"!=",0)
            ->orderByRaw('c.user_id asc')
            ->get();



        // Retorna os dados para o frontend
        return response()->json([
            'success' => true,
            'clientes' => $clientes,
            'frase' => "Clientes do Plano",
            'total' => $clientes->sum('valor_comissao'),
        ]);
    }


    public function obterClientesPorPlanoECorretor(Request $request)
    {
        $planoId    = $request->get('plano_id');
        $corretorId = $request->get('corretor_id');
        $modo       = $request->get('modo', ''); // 'parceiro' activa filtros especÃ­ficos
        if (!$corretorId) {
            return response()->json([
                'success' => false,
                'message' => 'Corretor nÃ£o informado.'
            ], 400);
        }

        // Card de Confirmados: busca parcelas com status_apto_pagar=1 do parceiro
        if ($planoId === 'confirmados') {
            $dados = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->leftJoin('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->leftJoin('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                // administradora sÃ³ existe em contratos PF; empresarial nÃ£o tem essa coluna
                ->leftJoin('administradoras as adm', 'adm.id', '=', 'ct.administradora_id')
                ->select(
                    'ccl.id',
                    'ccl.valor as valor_comissao',
                    'ccl.parcela',
                    'ccl.data as vencimento',
                    DB::raw("COALESCE(cl.nome, ce.razao_social) as cliente_nome"),
                    DB::raw("COALESCE(cl.cpf, ce.cnpj) as cpf"),
                    DB::raw("COALESCE(ct.codigo_externo, ce.codigo_externo) as contrato_codigo"),
                    DB::raw("COALESCE(adm.nome, 'Hapvida') as administradora"),
                    DB::raw("COALESCE(cl.quantidade_vidas, ce.quantidade_vidas) as vidas"),
                )
                ->where('c.user_id', $corretorId)
                ->where('ccl.status_apto_pagar', 1)
                ->where('ccl.finalizado', '!=', 1)
                ->orderBy('cliente_nome')
                ->get();

            $configParceiro = DB::table('parceiros_config_pagamento')
                ->where('user_id', $corretorId)
                ->first();

            return response()->json([
                'success'    => true,
                'clientes'   => $dados,
                'corretor'   => DB::table('users')->select('id', 'name')->where('id', $corretorId)->first(),
                'total'      => $dados->sum('valor_comissao'),
                'tipo'       => 'confirmados',
                'frase'      => 'Confirmados',
                'resumo'     => $this->obterResumoPorPlanoCorretor($corretorId),
                'frequencia' => $configParceiro?->frequencia ?? 'mensal',
                'odonto'     => false,
                'desconto'   => false,
                'premiacao'  => 0,
            ]);
        }
        $query = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id');
        if ($planoId == 1 || $planoId == 3) {
            // Para planos Individual (1) ou Coletivo (3)
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->join(DB::raw('administradoras'), 'administradoras.id', '=', 'ct.administradora_id')
                ->where('ct.plano_id', $planoId)
                ->select(
                    'cl.nome as cliente_nome',
                    'cl.id as cliente_id',
                    'cl.cpf',
                    'ccl.manualmente',
                    'ct.plano_id as plano',
                    'ct.desconto_corretor',
                    'ct.valor_plano as valor_plano',
                    'ccl.valor as valor_comissao',
                    'ccl.incluir',
                    'ccl.folha',
                    'ccl.id as id',
                    'ct.codigo_externo as contrato_codigo',
                    'ct.valor_plano as valor_original_plano',
                    DB::raw("
                        CASE
                            WHEN cl.desconto_operadora IS NOT NULL THEN
                                ct.valor_plano - ((ct.valor_plano * cl.desconto_operadora) / 100)
                            ELSE
                                ct.valor_plano
                        END as valor_plano_ajustado
                    "),
                    //'ct.valor_plano as valor_plano',
                    'ct.created_at as data_cadastro',
                    'ccl.parcela',
                    'administradoras.nome as administradora',
                    'ccl.data AS vencimento',
                    DB::raw("
                      CASE
                          WHEN ccl.porcentagem_paga IS NOT NULL
                              THEN ccl.porcentagem_paga
                          WHEN (SELECT clt FROM users WHERE users.id = c.user_id LIMIT 1) = 1 THEN
                              (SELECT valor FROM comissoes_corretores_default
                               WHERE
                                   comissoes_corretores_default.plano_id = c.plano_id AND
                                   comissoes_corretores_default.administradora_id = c.administradora_id AND
                                   comissoes_corretores_default.corretora_id = c.corretora_id AND
                                   comissoes_corretores_default.parcela = ccl.parcela LIMIT 1)
                          WHEN EXISTS (
                              SELECT 1 FROM comissoes_corretores_configuracoes
                              WHERE
                                  comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                  comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                  comissoes_corretores_configuracoes.user_id = c.user_id AND
                                  comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                  comissoes_corretores_configuracoes.corretora_id = c.corretora_id
                                LIMIT 1
                          ) THEN
                              (SELECT valor FROM comissoes_corretores_configuracoes
                               WHERE
                                   comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                   comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                   comissoes_corretores_configuracoes.user_id = c.user_id AND
                                   comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                   comissoes_corretores_configuracoes.corretora_id = c.corretora_id LIMIT 1)

                          ELSE
                              (SELECT valor FROM comissoes_corretores_configuracoes
                               WHERE
                                   comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                   comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                   comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                   comissoes_corretores_configuracoes.corretora_id = c.corretora_id AND
                                   comissoes_corretores_configuracoes.user_id IS NULL)
                      END as porcentagem
                    ")
                );
        } elseif($planoId == 'estorno') {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->join(DB::raw('administradoras'), 'administradoras.id', '=', 'ct.administradora_id')
                ->where('ct.estorno', 1)
                ->whereNotNull('ct.valor_estorno')
                ->select(
                    'cl.nome as cliente_nome',
                    'cl.cpf',
                    'ct.valor_estorno as valor_comissao',
                    'ct.codigo_externo as contrato_codigo',
                    'ct.valor_plano as valor_original_plano',

                    DB::raw("
                        CASE
                            WHEN cl.desconto_operadora IS NOT NULL THEN
                                ct.valor_plano - ((ct.valor_plano * cl.desconto_operadora) / 100)
                            ELSE
                                ct.valor_plano
                        END as valor_plano_ajustado
                    "),
                    'ct.created_at as data_cadastro',
                    'ccl.parcela',
                    'administradoras.nome as administradora',
                    'ccl.data AS vencimento',
                    DB::raw("'-' as porcentagem")
                );
        } elseif ($planoId == 'odonto') {
            // Para Odonto
            $resultadoOdonto = DB::table('odonto')

                ->where('pagou', 0)
                ->where('user_id',$corretorId)
                ->get();

            $corretor = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('id', $corretorId)
                ->first();

            // CÃ¡lculo do total
            $total = $resultadoOdonto->sum('comissao');
            $frase = "Plano odonto - ".$corretor->name;
            return response()->json([
                'success' => true,
                'clientes' => $resultadoOdonto,
                'corretor' => $corretor,
                'total' => $total,
                'resumo' => $this->obterResumoPorPlanoCorretor($corretorId),
                'odonto' => true,
                'desconto' => false,
                'frase' => $frase
            ]);
        } else if($planoId == "premiacao") {
            $premiacao = DB::table('premiacoes')
                ->where('user_id', $corretorId)
                ->where('pago', 0)
                ->first();
            return response()->json([
                'success' => true,
                'odonto' => false,
                'premiacao' => true,
                'vale' => false,
                'fixo' => false,
                'valor' => $premiacao->valor ?? 0
            ]);
        } else if($planoId == "fixo") {
            $fixo = DB::table('fixo')
                ->where('user_id', $corretorId)
                ->where('pago', 0) // Apenas premiaÃ§Ãµes nÃ£o pagas
                ->first();
            return response()->json([
                'success' => true,
                'odonto' => false,
                'premiacao' => false,
                'vale' => false,
                'fixo' => true,
                'valor' => $fixo->valor ?? 0
            ]);
        } else if($planoId == "vale") {
            $fixo = DB::table('vale')
                ->where('user_id', $corretorId)
                ->where('pago', 0) // Apenas premiaÃ§Ãµes nÃ£o pagas
                ->first();
            return response()->json([
                'success' => true,
                'odonto' => false,
                'premiacao' => false,
                'fixo' => false,
                'vale' => true,
                'valor' => $fixo->valor ?? 0
            ]);
        } else if($planoId == "adiantamento") {

            $dados = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->join(DB::raw('administradoras'), 'administradoras.id', '=', 'ct.administradora_id')
                ->select(
                    'cl.nome as cliente_nome',
                    'cl.user_id as cliente_id',
                    'cl.cpf',
                    'ct.plano_id as plano',
                    'ccl.valor as valor_comissao',
                    'ccl.id as id',
                    'ct.codigo_externo as contrato_codigo',
                    'ct.valor_plano as valor_original_plano',
                    DB::raw("
                        CASE
                            WHEN cl.desconto_operadora IS NOT NULL THEN
                                ct.valor_plano - ((ct.valor_plano * cl.desconto_operadora) / 100)
                            ELSE
                                ct.valor_plano
                        END as valor_plano_ajustado
                    "),
                    //'ct.valor_plano as valor_plano',
                    'ct.created_at as data_cadastro',
                    'ccl.parcela',
                    'administradoras.nome as administradora',
                    'ccl.data AS vencimento',
                    DB::raw("
                      CASE
                          WHEN ccl.porcentagem_paga IS NOT NULL
                              THEN ccl.porcentagem_paga
                          WHEN (SELECT clt FROM users WHERE users.id = c.user_id LIMIT 1) = 1 THEN
                              (SELECT valor FROM comissoes_corretores_default
                               WHERE
                                   comissoes_corretores_default.plano_id = c.plano_id AND
                                   comissoes_corretores_default.administradora_id = c.administradora_id AND
                                   comissoes_corretores_default.corretora_id = c.corretora_id AND
                                   comissoes_corretores_default.parcela = ccl.parcela LIMIT 1)
                          WHEN EXISTS (
                              SELECT 1 FROM comissoes_corretores_configuracoes
                              WHERE
                                  comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                  comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                  comissoes_corretores_configuracoes.user_id = c.user_id AND
                                  comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                  comissoes_corretores_configuracoes.corretora_id = c.corretora_id
                                LIMIT 1
                          ) THEN
                              (SELECT valor FROM comissoes_corretores_configuracoes
                               WHERE
                                   comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                   comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                   comissoes_corretores_configuracoes.user_id = c.user_id AND
                                   comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                   comissoes_corretores_configuracoes.corretora_id = c.corretora_id LIMIT 1)

                          ELSE
                              (SELECT valor FROM comissoes_corretores_configuracoes
                               WHERE
                                   comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                   comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                   comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                   comissoes_corretores_configuracoes.corretora_id = c.corretora_id AND
                                   comissoes_corretores_configuracoes.user_id IS NULL)
                      END as porcentagem
                    "),
                    DB::raw("
            CASE
                WHEN ccl.status_financeiro = 1 AND ccl.status_gerente = 0 THEN 'cliente_pago'
                WHEN ccl.status_financeiro = 0 AND ccl.status_gerente = 1 THEN 'operadora_pagou'
                ELSE NULL
            END as resposta
        ")
                )
                ->where('c.user_id', $corretorId)
                ->where('ct.plano_id',1)
                ->where('ccl.valor', '!=', 0) // Filtra onde valor > 0
                ->where('ccl.finalizado', '!=', 1) // Exclui registros finalizados
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('ccl.status_financeiro', 1)
                            ->where('ccl.status_gerente', 0);
                    })
                        ->orWhere(function ($query) {
                            $query->where('ccl.status_financeiro', 0)
                                ->where('ccl.status_gerente', 1);
                        });
                })

                ->get();



            return response()->json([
                'success' => true,
                'clientes' => $dados,
                'frase' => "Clientes nÃ£o recebidos",
                'odonto' => false,
                'desconto' => false,
                'tipo' => 'adiantamento'
            ]);
        } else {
            // Para Plano Empresarial (apenas contrato_empresarial)
            $query->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                ->whereNotIn('ce.plano_id', [1, 3])
                ->select(
                    'ce.razao_social as cliente_nome',  // Usando razao_social em vez de nome do cliente
                    'ce.cnpj as cpf',                   // Usando CNPJ em vez de CPF
                    'ce.desconto_corretor',
                    'ccl.valor as valor_comissao',
                    'ccl.id',
                    'ccl.folha',
                    'ccl.incluir',
                    'ccl.manualmente',
                    'ce.codigo_externo as contrato_codigo',
                    'ccl.parcela',
                    DB::raw("'Hapvida' as administradora"),

                    'ce.created_at as data_cadastro',
                    'ccl.data AS vencimento',
                    'ce.valor_plano as valor_original_plano',
                    DB::raw("
                        CASE
                            WHEN ce.desconto_operadora IS NOT NULL THEN
                                ce.valor_plano - ((ce.valor_plano * ce.desconto_operadora) / 100)
                            ELSE
                                ce.valor_plano
                        END as valor_plano_ajustado
                    "),
                    DB::raw("
                      CASE
                          WHEN ccl.porcentagem_paga IS NOT NULL
                              THEN ccl.porcentagem_paga
                          WHEN (SELECT clt FROM users WHERE users.id = c.user_id LIMIT 1) = 1 THEN
                              (SELECT valor FROM comissoes_corretores_default
                               WHERE
                                   comissoes_corretores_default.plano_id = c.plano_id AND
                                   comissoes_corretores_default.administradora_id = c.administradora_id AND
                                   comissoes_corretores_default.corretora_id = c.corretora_id AND
                                   comissoes_corretores_default.parcela = ccl.parcela LIMIT 1)
                          WHEN EXISTS (
                              SELECT 1 FROM comissoes_corretores_configuracoes
                              WHERE
                                  comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                  comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                  comissoes_corretores_configuracoes.user_id = c.user_id AND
                                  comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                  comissoes_corretores_configuracoes.corretora_id = c.corretora_id
                                  LIMIT 1
                          ) THEN
                              (SELECT valor FROM comissoes_corretores_configuracoes
                               WHERE
                                   comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                   comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                   comissoes_corretores_configuracoes.user_id = c.user_id AND
                                   comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                   comissoes_corretores_configuracoes.corretora_id = c.corretora_id LIMIT 1)
                          ELSE
                              (SELECT valor FROM comissoes_corretores_configuracoes
                               WHERE
                                   comissoes_corretores_configuracoes.plano_id = c.plano_id AND
                                   comissoes_corretores_configuracoes.administradora_id = c.administradora_id AND
                                   comissoes_corretores_configuracoes.parcela = ccl.parcela AND
                                   comissoes_corretores_configuracoes.corretora_id = c.corretora_id AND
                                   comissoes_corretores_configuracoes.user_id IS NULL)
                      END as porcentagem
                    ")
                );
        }
        $clientes = $query->where('c.user_id', $corretorId)
            ->when($planoId != "estorno" && $planoId != "desconto", function ($q) {
                $q->where('ccl.status_gerente', 1);
            })
            ->when($planoId != "estorno" && $planoId != "desconto", function ($q) {
                $q->where(function ($query) {
                    $query->where('ccl.valor', '!=', 0)
                        ->orWhere('ccl.incluir', 1);
                });
            })
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            // Em modo parceiro, mostra apenas parcelas ainda nÃ£o confirmadas (status_apto_pagar=0)
            ->when($modo === 'parceiro', fn($q) => $q->where('ccl.status_apto_pagar', 0))
            ->orderBy('cliente_nome')
            ->get();

        $corretor = DB::table('users')
            ->select('id', 'name', 'email')
            ->where('id', $corretorId)
            ->first();
        $total = $clientes->sum('valor_comissao');
        $premiacao = DB::table('premiacoes')
            ->where('user_id', $corretorId)
            ->where('pago', 0) // Apenas premiaÃ§Ãµes nÃ£o pagas
            ->first();
        if($premiacao) {
            $premiacao = $premiacao->valor;
        } else {
            $premiacao = 0;
        }
        $frase = "";
        $tipo = "";
        switch($planoId) {
            case 1:
                $frase = "Plano Individual - ".$corretor->name;
                $tipo = "individual";
                break;
            case 3:
                $frase = "Plano Coletivo  - ".$corretor->name;
                $tipo = "coletivo";
                break;
            case "empresarial":
                $frase = "Plano Empresarial - ".$corretor->name;
                $tipo = "empresarial";
                break;
            case "odonto":
                $frase = "Plano odonto - ".$corretor->name;
                $tipo = "odonto";
                break;
            case "estorno":
                $frase = "Estorno - ".$corretor->name;
                $tipo = "estorno";
                break;
        }
        return response()->json([
            'success' => true,
            'clientes' => $clientes,
            'corretor' => $corretor,
            'total' => $total,
            'tipo' => $tipo,
            'resumo' => $this->obterResumoPorPlanoCorretor($corretorId),
            'odonto' => false,
            'desconto' => false,
            'frase' => $frase,

            'premiacao' => $premiacao ?? 0
        ]);
    }

    public function adicionarVale(Request $request)
    {
        if(empty($request->user_id)) {
            return response()->json(['success' => false, 'message' => 'Parceiro nÃ£o identificado.']);
        }

        if (!isset($request->valor) || $request->valor === '') {
            return response()->json(['success' => false, 'message' => 'Valor invÃ¡lido.']);
        }

        $user_id = $request->user_id;
        $valor = str_replace([".",","],["","."], $request->valor);

        DB::table('vale')->updateOrInsert(
            ['user_id' => $user_id, 'pago' => 0],
            ['valor' => $valor, 'updated_at' => now(), 'created_at' => now()]
        );

        $totalComissoes = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->where('c.user_id', $user_id)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.valor', '!=', 0)
            ->sum('ccl.valor');

        $totalVale = DB::table('vale')
            ->where('user_id', $user_id)
            ->where('pago', 0)
            ->sum('valor');

        $resumo = $this->obterResumoPorPlanoCorretor($user_id);

        return response()->json([
            'success'           => true,
            'message'           => 'Vale adicionado com sucesso',
            'valor'             => $valor,
            'total_vale'        => (float) $totalVale,
            'novo_total_liquido'=> (float) max(0, $totalComissoes - $totalVale),
            'resumo'            => $resumo,
        ]);
    }





    public function adicionarFixo(Request $request)
    {
        if(empty($request->user_id)) {
            return response()->json(['success' => false, 'message' => 'Parceiro nÃ£o identificado.']);
        }

        if (!isset($request->valor) || $request->valor === '') {
            return response()->json(['success' => false, 'message' => 'Valor invÃ¡lido.']);
        }

        $user_id = $request->user_id;
        $valor = str_replace([".",","],["","."], $request->valor);

        DB::table('fixo')->updateOrInsert(
            ['user_id' => $user_id, 'pago' => 0],
            ['valor' => $valor, 'updated_at' => now(), 'created_at' => now()]
        );

        $totalFixo = DB::table('fixo')
            ->where('user_id', $user_id)
            ->where('pago', 0)
            ->sum('valor');

        return response()->json([
            'success'    => true,
            'message'    => 'Fixo adicionado com sucesso',
            'valor'      => $valor,
            'total_fixo' => (float) $totalFixo,
        ]);
    }





    public function atualizarComissao(Request $request)
    {
        try {
            // Recuperar o registro da comissÃ£o
            $comissao = DB::table('comissoes_corretores_lancadas')
                ->where('id', $request->id)
                ->first();

            if (!$comissao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro de comissÃ£o nÃ£o encontrado.',
                ], 404);
            }

            // Remover o formato monetÃ¡rio de "R$" da string de valor e convertÃª-lo para nÃºmero
            $valorPlano = str_replace(['.', ','],['', '.'], $request->valor);
            $valorPlano = (float) $valorPlano;


            // Calcular o novo valor da comissÃ£o
            $novoValorComissao = ($valorPlano * $request->porcentagem) / 100;

            // Atualizar o banco de dados
            DB::table('comissoes_corretores_lancadas')
                ->where('id', $request->id)
                ->update([
                    'porcentagem_paga' => $request->porcentagem,
                    'valor' => $novoValorComissao,
                    'updated_at' => now(),
                ]);

            // Retornar sucesso
            return response()->json([
                'success' => true,
                'message' => 'ComissÃ£o atualizada com sucesso!',
                'valor_comissao' => number_format($novoValorComissao, 2, ',', '.'),
            ]);
        } catch (\Exception $e) {
            // Capturar erros e retornar resposta com status 500
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar a comissÃ£o: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function confirmarComissao(Request $request)
    {
        $id = $request->id;
        try {
            // Atualize a linha na tabela comissoes_corretores_lancadas
            $comissao = DB::table('comissoes_corretores_lancadas')->where('id', $id)->first();

            if (!$comissao) {
                return response()->json([
                    'success' => false,
                    'message' => 'ComissÃ£o nÃ£o encontrada.',
                ], 404);
            }

            // Atualizar os campos necessÃ¡rios
            DB::table('comissoes_corretores_lancadas')
                ->where('id', $id)
                ->update([
                    'status_gerente' => 1,
                    'data_baixa_gerente' => now(),
                    'manualmente' => 1,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'ComissÃ£o confirmada com sucesso.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar comissÃ£o: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function obterResumoAtualizado(Request $request)
    {
        // Captura o ID do corretor
        $corretorId = $request->get('corretor_id');

        if (!$corretorId) {
            return response()->json([
                'success' => false,
                'message' => 'Corretor nÃ£o informado.',
            ], 400);
        }

        try {
            // Obter o resumo por planos (esta Ã© uma funÃ§Ã£o que vocÃª jÃ¡ pode ter pronta)
            $resumo = $this->obterResumoPorPlanoCorretorAtualizado($corretorId);

            // Calcular o resumo geral
            $resumoGeral = [
                'total_geral' => collect($resumo)->sum(fn($plano) => $plano->valor_total ?? 0), // Total de todos os planos
                'total_contratos' => collect($resumo)->sum(fn($plano) => $plano->total_contratos ?? 0), // Total de contratos
                'total_vidas' => collect($resumo)->sum(fn($plano) => $plano->total_vidas ?? 0), // Total de vidas
            ];

            return response()->json([
                'success' => true,
                'resumoPorPlano' => $resumo, // Resumo de cada plano
                'resumoGeral' => $resumoGeral, // Total agregado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar resumo: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function obterResumoPorPlanoCorretorAtualizado($corretorId)
    {
        $individual = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
            ->where('ct.plano_id', 1)
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.folha', 1)
            ->where('c.corretora_id', $this->corretora_id)
            ->where('ccl.valor', '>', 0)
            ->selectRaw('
            COUNT(ct.id) as total_contratos,
            SUM(cl.quantidade_vidas) as total_vidas,
            SUM(ccl.valor) as valor_total
        ')
            ->first();



        // Exemplo bÃ¡sico de cÃ¡lculo para Adiantamentos
        $adiantamento = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cl', 'cl.id', '=', 'ct.cliente_id') // Relacionando o cliente
            ->selectRaw('
        COUNT(DISTINCT ct.id) as total_contratos,
        SUM(cl.quantidade_vidas) as total_vidas,
        SUM(ccl.valor) as valor_total
    ')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('ccl.status_financeiro', 1)
                        ->where('ccl.status_gerente', 0);
                })
                    ->orWhere(function ($query) {
                        $query->where('ccl.status_financeiro', 0)
                            ->where('ccl.status_gerente', 1);
                    });
            })
            ->where('ct.plano_id', 1) // Apenas contratos do plano Individual
            ->where('c.user_id', $corretorId) // Associado ao corretor atual
            ->where('ccl.valor', '!=', 0) // Exclui registros com valor 0
            ->where('ccl.finalizado', '!=', 1) // Exclui finalizados
            ->first(); // Retorna apenas um registro agregado

        return [
            'individual' => [
                'nome_plano' => 'Individual',
                'total_contratos' => $individual->total_contratos ?? 0,
                'total_vidas' => $individual->total_vidas ?? 0,
                'valor_total' => $individual->valor_total ?? 0,
            ],
            'adiantamento' => [
                'nome_plano' => 'Adiantamento',
                'total_contratos' => $adiantamento->total_contratos ?? 0,
                'total_vidas' => $adiantamento->total_vidas ?? 0,
                'valor_total' => $adiantamento->valor_total ?? 0,
            ],
            // Adicione outros planos aqui, como Coletivo, Empresarial, etc.
        ];







    }





    private function obterResumoPorPlano()
    {
        // Resumo para Plano Individual
        $individual = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
            ->selectRaw('COUNT(DISTINCT ct.id) as total_contratos, SUM(cl.quantidade_vidas) as total_vidas, SUM(ccl.valor) as valor_total')
            ->where('ct.plano_id', 1)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.valor',"!=",0)
            ->where('c.corretora_id',"=", $this->corretora_id)
            ->first();

        // Resumo para Plano Coletivo
        $coletivo = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
            ->selectRaw('COUNT(DISTINCT ct.id) as total_contratos, SUM(cl.quantidade_vidas) as total_vidas, SUM(ccl.valor) as valor_total')
            ->where('ct.plano_id', 3)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.valor',"!=",0)
            ->where('ccl.finalizado', '!=', 1)
            ->where('c.corretora_id',"=", $this->corretora_id)
            ->first();

        // Resumo para Plano Empresarial
        $empresarial = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
            ->selectRaw('COUNT(DISTINCT ce.id) as total_contratos, SUM(ce.quantidade_vidas) as total_vidas, SUM(ccl.valor) as valor_total')
            ->whereNotIn('ce.plano_id', [1, 3])
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.valor',"!=",0)
            ->where('ccl.finalizado', '!=', 1)
            ->where('c.corretora_id',"=", $this->corretora_id)
            ->first();

        $resultadoOdonto = DB::table('odonto')
            ->join("users","users.id","=","odonto.user_id")
            ->selectRaw('COUNT(*) as total_registros, SUM(comissao) as total_comissao')
            ->where('pagou', 0)
            ->where('corretora_id',$this->corretora_id)
            ->first();

        $estorno = DB::table('contratos')
            ->join('clientes',"clientes.id","=","contratos.cliente_id")
            ->selectRaw('COUNT(*) as total_registros_estorno, SUM(valor_estorno) as total_comissao_estorno')
            ->where('estorno',1)
            ->where('corretora_id',$this->corretora_id)
            // ->where(function($q) {
            //     $q->whereNull('valor_estorno')
            //     ->orWhere('valor_estorno', 0);
            // })
            ->where('valor_estorno',">",0)
            ->whereNull('data_baixa_estorno')
            ->first();

        return [
            'individual' => $individual,
            'coletivo' => $coletivo,
            'empresarial' => $empresarial,
            'odonto' => $resultadoOdonto,
            'estorno' => $estorno
        ];
    }

    private function getMenorData($plano_id)
    {
        if ($plano_id == 3) {
            // Plano coletivo (id = 3)
            return DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->where('c.corretora_id', $this->corretora_id)
                ->where('ct.plano_id', $plano_id)
                ->where('ccl.status_gerente', 1)
                ->whereNull('ccl.data_baixa_gerente_folha')
                ->min('ccl.data_baixa_gerente');
        }

        if ($plano_id == 'empresarial') {
            // Para plano empresarial (id â‰  1 e â‰  3)
            return DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                ->where('c.corretora_id', $this->corretora_id)
                ->whereNotIn('ce.plano_id', [1, 3])
                ->where('ccl.status_gerente', 1)
                ->whereNull('ccl.data_baixa_gerente_folha')
                ->min('ccl.data_baixa_gerente');
        }

        // Default para plano individual
        return DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->where('c.corretora_id', $this->corretora_id)
            ->where('ct.plano_id', 1)
            ->where('ccl.status_gerente', 1)
            ->whereNull('ccl.data_baixa_gerente_folha')
            ->min('ccl.data_baixa_gerente');
    }

    public function obterDetalhesClienteModal(Request $request)
    {



        $plano = $request->get('plano');
        $codigoContrato = $request->get('codigo');

        if ($plano == "1" || $plano == "3") {

            // Buscar cliente e contrato
            $cliente  = DB::table('clientes')->where('id', $codigoContrato)->first();
            $contrato = DB::table('contratos')->where('cliente_id', $cliente->id)->first();
            $comissoes = DB::table('comissoes')->where('contrato_id',$contrato->id)->first();
            // Buscar parcelas das comissÃµes
            $parcelas = DB::table('comissoes_corretores_lancadas')
                ->where('comissoes_id', $comissoes->id)
                ->select('parcela as numero', 'valor as valor_comissao', DB::raw("
                CASE WHEN status_financeiro = 1 AND status_gerente = 1 AND finalizado = 1 THEN 1 ELSE 0 END as paga
            "))
                ->get();

            return response()->json([
                'success' => true,
                'plano' => $plano,
                'cliente' => $cliente,
                'contrato' => $contrato,
                'parcelas' => $parcelas,
            ]);
        }

        if ($plano === 'Empresarial') {
            // Buscar empresa e contrato
            $empresa = DB::table('contrato_empresarial')->where('codigo_externo', $codigoContrato)->first();

            // Buscar parcelas das comissÃµes
            $parcelas = DB::table('comissoes_corretores_lancadas')
                ->where('contrato_empresarial_id', $empresa->id)
                ->select('parcela as numero', 'valor as valor_comissao', DB::raw("
                CASE WHEN status_financeiro = 1 AND status_gerente = 1 AND finalizado = 1 THEN 1 ELSE 0 END as paga
            "))
                ->get();

            return response()->json([
                'success' => true,
                'plano' => $plano,
                'empresa' => $empresa,
                'parcelas' => $parcelas,
            ]);
        }
    }







    public function obterDetalhesCliente($clienteId, Request $request)
    {
        try {
            // Buscar dados do cliente
            $cliente = DB
                ::table('clientes')
                ->join('contratos',"contratos.cliente_id","clientes.id")
                ->where('clientes.id', $clienteId)
                ->select('id', 'nome', 'cpf', 'celular')
                ->first();

            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'Cliente nÃ£o encontrado'], 404);
            }

            // Buscar parcelas relacionadas ao cliente
            $parcelas = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->where('ct.cliente_id', $clienteId)
                ->select(
                    'ccl.parcela',
                    'ccl.valor as valor',
                    'ccl.status_financeiro',
                    'ccl.status_gerente',
                    'ccl.data as data',
                    'ccl.data_baixa_gerente as data_aprovacao_gerente',
                    'ccl.data_baixa_gerente_folha as data_pagamento'
                )
                ->orderBy('ccl.parcela')
                ->get();

            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'parcelas' => $parcelas
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao buscar detalhes do cliente: ' . $e->getMessage()], 500);
        }
    }

    public function obterDetalhesCorretor($corretorId, Request $request)
    {
        try {
            $dataInicio = $request->get('data_inicio', Carbon::now()->subDays(30)->format('Y-m-d'));
            $dataFim = $request->get('data_fim', Carbon::now()->format('Y-m-d'));
            $planoId = $request->get('plano_id', 1);

            // Buscar dados do corretor
            $corretor = DB::table('users')
                ->select('id', 'name', 'email')
                ->where('id', $corretorId)
                ->first();

            if (!$corretor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Corretor nÃ£o encontrado'
                ], 404);
            }

            // Buscar comissÃµes detalhadas do corretor
            $comissoes = $this->obterComissoesDetalhadas($corretorId, $dataInicio, $dataFim, $planoId);

            // Adicionar Resumo por Plano para o corretor
            $resumoPorPlano = $this->obterResumoPorPlanoCorretor($corretorId); // MÃ©todo criado anteriormente

            // Calcular resumo
            $resumo = $this->calcularResumoCorretor($comissoes);

            return response()->json([
                'success' => true,
                'corretor' => $corretor,
                'resumo' => $resumo,
                'resumo_por_plano' => $resumoPorPlano, // Adiciona o resumo por plano no retorno
                'comissoes' => $comissoes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obterResumoPorPlanoCorretor($corretorId)
    {

        $resultadoOdonto = DB::table('odonto')
            ->selectRaw('COUNT(*) as total_registros, SUM(comissao) as total_comissao')
            ->where("user_id",$corretorId)
            ->where('pagou', 0)
            ->first();

        $resultadoFixo = DB::table('fixo')
            ->selectRaw('COUNT(*) as total_registros, valor as total_comissao')
            ->where("user_id",$corretorId)
            ->where('pago', 0)
            ->first();

        $resultadoVale = DB::table('vale')
            ->selectRaw('COUNT(*) as total_registros, valor as total_comissao')
            ->where("user_id",$corretorId)
            ->where('pago', 0)
            ->first();

        $estorno = DB::table('contratos')
            ->join('clientes','clientes.id','=','contratos.cliente_id')
            ->selectRaw('COUNT(*) as total_registros, SUM(valor_estorno) as total_estorno')
            ->where('clientes.user_id',$corretorId)
            ->where('estorno',1)
            ->whereNotNull('contratos.valor_estorno')
            ->whereNull('data_baixa_estorno')
            ->first();

        // Resumo para Plano Individual (plano_id = 1)
        // folha=1: parcela marcada para entrar na folha corrente
        $individual = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cc','cc.id','=','ct.cliente_id')
            ->selectRaw('COUNT(DISTINCT ct.id) as total_contratos, SUM(cc.quantidade_vidas) as total_vidas, SUM(ccl.valor) as valor_total')
            ->where('ct.plano_id', 1)
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.folha', 1)
            ->where(function ($query) {
                $query->where('ccl.valor', '!=', 0)
                    ->orWhere('ccl.incluir', 1);
            })
            ->first();

        // Resumo para Plano Coletivo (plano_id = 3)
        $coletivo = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cc','cc.id','=','ct.cliente_id')
            ->selectRaw('COUNT(DISTINCT ct.id) as total_contratos, SUM(cc.quantidade_vidas) as total_vidas, SUM(ccl.valor) as valor_total')
            ->where('ct.plano_id', 3)
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.folha', 1)
            ->where(function ($query) {
                $query->where('ccl.valor', '!=', 0)
                    ->orWhere('ccl.incluir', 1);
            })
            ->first();

        // Resumo para Plano Empresarial (plano_id != 1 e plano_id != 3)
        $empresarial = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
            ->selectRaw('COUNT(DISTINCT ce.id) as total_contratos, SUM(ce.quantidade_vidas) as total_vidas, SUM(ccl.valor) as valor_total')
            ->whereNotIn('ce.plano_id', [1, 3])
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.folha', 1)
            ->where(function ($query) {
                $query->where('ccl.valor', '!=', 0)
                    ->orWhere('ccl.incluir', 1);
            })
            ->first();

        $adiantamento = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cl', 'cl.id', '=', 'ct.cliente_id') // Relacionando o cliente
            ->selectRaw('
        COUNT(DISTINCT ct.id) as total_contratos,
        SUM(cl.quantidade_vidas) as total_vidas,
        SUM(ccl.valor) as valor_total
    ')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('ccl.status_financeiro', 1)
                        ->where('ccl.status_gerente', 0);
                })
                    ->orWhere(function ($query) {
                        $query->where('ccl.status_financeiro', 0)
                            ->where('ccl.status_gerente', 1);
                    });
            })
            ->where('ct.plano_id', 1) // Apenas contratos do plano Individual
            ->where('c.user_id', $corretorId) // Associado ao corretor atual
            ->where('ccl.valor', '!=', 0) // Exclui registros com valor 0
            ->where('ccl.finalizado', '!=', 1) // Exclui finalizados
            ->first(); // Retorna apenas um registro agregado





        // Parcelas confirmadas para a folha do parceiro (status_apto_pagar=1, nÃ£o finalizadas)
        $confirmados = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->selectRaw('COUNT(*) as total_contratos, SUM(ccl.valor) as valor_total')
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_apto_pagar', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->first();

        return [
            'individual'  => $individual,
            'coletivo'    => $coletivo,
            'empresarial' => $empresarial,
            'odonto'      => $resultadoOdonto,
            'fixo'        => $resultadoFixo,
            'vale'        => $resultadoVale,
            'estorno'     => $estorno,
            'adiantamento'=> $adiantamento,
            'confirmados' => $confirmados,
        ];
    }

    public function obterClientesPlano(Request $request, $corretorId)
    {
        $planoId = $request->get('plano_id', 1); // Default: Individual

        // Query base
        $query = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
            ->select('cl.nome as cliente_nome', 'cl.cpf', 'ccl.valor as valor_comissao', 'ct.codigo_externo as contrato_codigo', 'ccl.parcela')
            ->where('c.user_id', $corretorId)
            ->where('c.corretora_id', $this->corretora_id)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.valor',"!=",0)
            ->where('ccl.finalizado', '!=', 1);

        // Filtro por tipo de plano
        if ($planoId == 3) {
            $query->where('ct.plano_id', 3); // Coletivo
        } elseif ($planoId == 'empresarial') {
            $query->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                ->whereNotIn('ce.plano_id', [1, 3]);
        } else {
            $query->where('ct.plano_id', 1); // Individual
        }

        $clientes = $query->orderBy('cl.nome')->get();

        return response()->json([
            'success' => true,
            'clientes' => $clientes,
        ]);
    }

    public function gerarFolhaPagamentoCorretora(Request $request)
    {
        try {
            $request->validate([
                'corretores' => 'required|array|min:1',
                //'data_inicio' => 'required|date',
                //'data_fim' => 'required|date|after_or_equal:data_inicio'
            ]);

            $corretoresSelecionados = $request->input('corretores');
            $dataInicio = now();
            $dataFim = now();

            DB::beginTransaction();

            // ConfiguraÃ§Ã£o inicial
            $totalProcessado = 0;
            $corretoresProcessados = 0;
            $idsProcessados = [];
            $idsOdontoProcessados = [];
            $idsPremiacoesProcessadas = [];
            $dataProcessamento = now();
            $dados = [];
            $totalVidasGeral = 0;

            foreach ($corretoresSelecionados as $corretorId) {
                $corretor   = DB::table('users')->find($corretorId);
                $isParceiro = ($corretor?->tipo_contrato === 'parceiro');

                // Obter estornos
                $estornos = DB::table('contratos')
                    ->join('clientes', 'contratos.cliente_id', '=', 'clientes.id')
                    ->where('clientes.user_id', $corretorId)
                    ->where('contratos.estorno', 1)
                    ->whereNull('data_baixa_estorno')
                    ->whereNotNull('contratos.valor_estorno')
                    ->select(
                        'clientes.nome as cliente_nome',
                        'clientes.cpf',
                        'contratos.codigo_externo as contrato_codigo',
                        DB::raw("'estorno' as tipo_contrato"),
                        DB::raw("1 as quantidade_vidas"),
                        DB::raw("0 as desconto_corretor"),
                        'contratos.valor_estorno as valor_comissao',
                        'contratos.valor_plano as valor_plano',
                        'contratos.created_at as data_vencimento'
                    )
                    ->get();

                // Buscar comissoes normais
                // Parceiros: apenas parcelas confirmadas (status_apto_pagar=1)
                // CLT/PJ: logica original com folha=1
                $comissoesPendentes = DB::table('comissoes_corretores_lancadas as ccl')
                    ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                    ->where('c.user_id', $corretorId)
                    ->where('ccl.status_gerente', 1)
                    ->where('ccl.status_financeiro', 1)
                    ->where('ccl.valor', '!=', 0)
                    ->where('ccl.finalizado', '!=', 1)
                    ->when($isParceiro, fn($q) => $q->where('ccl.status_apto_pagar', 1))
                    ->when(!$isParceiro, fn($q) => $q->where('ccl.folha', 1)->whereNull('ccl.data_baixa_gerente_folha'))
                    ->where(function ($query) {
                        $query->whereNotNull('c.contrato_id')
                            ->orWhereNotNull('c.contrato_empresarial_id');
                    })
                    ->pluck('ccl.id');

                $comissoesNormais = collect();
                if ($comissoesPendentes->isNotEmpty()) {

                    $idsProcessados = array_merge($idsProcessados, $comissoesPendentes->toArray());
                    $comissoesNormais = DB::table('comissoes_corretores_lancadas as ccl')
                        ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                        ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                        ->leftJoin('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                        ->leftJoin('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                        ->whereIn('ccl.id', $comissoesPendentes)
                        ->select(
                            'ccl.*',
                            DB::raw('CASE
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social
                                ELSE cl.nome END as cliente_nome'),
                            DB::raw('CASE
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.cnpj
                                ELSE cl.cpf END as cpf'),
                            DB::raw('CASE
                                WHEN c.contrato_id IS NOT NULL THEN ct.codigo_externo
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.codigo_externo
                                ELSE "---" END as contrato_codigo'),
                            DB::raw("CASE
                                WHEN c.contrato_id IS NOT NULL AND c.plano_id = 1 THEN 'individual'
                                WHEN c.contrato_id IS NOT NULL AND c.plano_id = 3 THEN 'coletivo'
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN 'empresarial'
                                ELSE 'outro'
                                END AS tipo_contrato"),
                            DB::raw('CASE
                                WHEN c.contrato_id IS NOT NULL THEN cl.quantidade_vidas
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.quantidade_vidas
                                ELSE cl.quantidade_vidas END as quantidade_vidas'),

                            'ccl.valor as valor_comissao',
                            DB::raw('CASE
                                WHEN c.contrato_id IS NOT NULL THEN ct.valor_plano
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.valor_plano
                                ELSE ccl.valor END as valor_plano'),
                            DB::raw('COALESCE(ct.desconto_corretor, 0) + COALESCE(ce.desconto_corretor, 0) as desconto_corretor'),
                            'ccl.parcela as parcela',
                            'ccl.data_baixa_gerente as data_vencimento'
                        )
                        ->orderByRaw("CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social ELSE cl.nome END ASC")
                        ->orderBy('ccl.parcela', 'asc')
                        ->get()
                        ->map(fn($item) => $item)
                        ->groupBy('cliente_nome');
                }

                // Processar odontolÃ³gicos
                $odontoPendente = DB::table('odonto')
                    ->where('user_id', $corretorId)
                    ->where('pagou', 0)
                    ->get();

                $comissoesOdonto = $odontoPendente->map(function ($item) {
                    return (object)[
                        'cliente_nome' => $item->nome,
                        'cpf' => '',
                        'contrato_codigo' => 'ODON-' . $item->id,
                        'tipo_contrato' => 'odonto',
                        'quantidade_vidas' => 1,
                        'parcela' => null,
                        'valor_comissao' => $item->comissao,
                        'valor_plano' => $item->valor,
                        'desconto_corretor' => 0,
                        'data_vencimento' => $item->created_at
                    ];
                })->groupBy('cliente_nome');

                $comissoesCombinadas = $comissoesNormais->flatten()
                    ->merge($estornos)
                    ->merge($comissoesOdonto->flatten());

                // Calcular totais
                $totalIndividual = $comissoesCombinadas->where('tipo_contrato', 'individual')->sum('valor_comissao');
                $totalColetivo = $comissoesCombinadas->where('tipo_contrato', 'coletivo')->sum('valor_comissao');
                $totalEmpresarial = $comissoesCombinadas->where('tipo_contrato', 'empresarial')->sum('valor_comissao');
                $totalOdonto = $comissoesCombinadas->where('tipo_contrato', 'odonto')->sum('valor_comissao');
                $totalEstorno = $comissoesCombinadas->where('tipo_contrato', 'estorno')->sum('valor_comissao');
                $dadosDesconto = $comissoesCombinadas->sum('desconto_corretor');

                // Premiação (parceiros não têm premiação nem fixo)
                $premiacao = $isParceiro ? 0 : (DB::table('premiacoes')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->value('valor') ?? 0);

                $totalVale = DB::table('vale')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->sum('valor');

                $totalFixo = $isParceiro ? 0 : DB::table('fixo')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->sum('valor');


                // Cálculo final
                $totalCorretor = $totalIndividual + $totalColetivo + $totalEmpresarial + $totalOdonto + $premiacao - abs($totalEstorno) - $dadosDesconto - $totalVale - $totalFixo;

                $totalVidas = $comissoesCombinadas->filter(function ($comissao) {
                    return $comissao->tipo_contrato !== 'estorno'; // Exclui as vidas de estornos
                })->sum('quantidade_vidas');
                $totalContrato = $comissoesCombinadas->count('cliente_nome');
                $dados[] = [
                    'corretor' => $corretor,
                    'is_parceiro' => $isParceiro,
                    'total' => $totalCorretor,
                    'comissoes' => $comissoesCombinadas->groupBy('tipo_contrato'),
                    'vidas' => $totalVidas,
                    'contratos' => $totalContrato,
                    'totais_tipos' => [
                        'individual' => $totalIndividual,
                        'coletivo' => $totalColetivo,
                        'empresarial' => $totalEmpresarial,
                        'odonto' => $totalOdonto,
                        'premiacao' => $premiacao,
                        'vale' => $totalVale,
                        'fixo' => $totalFixo,
                        'estorno' => $totalEstorno,
                        'desconto' => $dadosDesconto
                    ]
                ];
                $totalProcessado += $totalCorretor;
            }

            // Gerar PDF
            $pdf = PDF::loadView('folha.america.pdf-corretora', [
                'dados' => $dados,
                'totalGeral' => $totalProcessado,

                'dataGeracao' => now()->format('d/m/Y H:i:s')
            ]);

            DB::commit();

            $filename = 'folha_pagamento_corretora_' . now()->format('Ymd_His') . '.pdf';
            $pdf->save(storage_path('app/folhas/' . $filename));

            return response()->json(['success' => true, 'download_url' => route('folha.america.download', ['file' => $filename])]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao gerar folha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao gerar folha'], 500);
        }
    }



    public function gerarFolhaPagamento(Request $request)
    {
        try {
            $request->validate([
                'corretores' => 'required|array|min:1',
                //'data_inicio' => 'required|date',
                //'data_fim' => 'required|date|after_or_equal:data_inicio'
            ]);

            $corretoresSelecionados = $request->input('corretores');
            $dataInicio = now();
            $dataFim = now();

            DB::beginTransaction();

            // ConfiguraÃ§Ã£o inicial
            $totalProcessado = 0;
            $corretoresProcessados = 0;
            $idsProcessados = [];
            $idsOdontoProcessados = [];
            $idsPremiacoesProcessadas = [];
            $dataProcessamento = now();
            $dados = [];
            $totalVidasGeral = 0;

            foreach ($corretoresSelecionados as $corretorId) {
                $corretor = DB::table('users')->find($corretorId);
                $isParceiro = ($corretor?->tipo_contrato === 'parceiro');
                // Obter estornos
                $estornos = DB::table('contratos')
                    ->join('clientes', 'contratos.cliente_id', '=', 'clientes.id')
                    ->where('clientes.user_id', $corretorId)
                    ->where('contratos.estorno', 1)
                    ->whereNull('data_baixa_estorno')
                    //->where('ccl.folha', 1) // Adicionando o filtro
                    ->whereNotNull('contratos.valor_estorno')
                    ->select(
                        'clientes.nome as cliente_nome',
                        'clientes.cpf',
                        'contratos.codigo_externo as contrato_codigo',
                        DB::raw("'estorno' as tipo_contrato"),
                        DB::raw("1 as quantidade_vidas"),
                        DB::raw("0 as desconto_corretor"),
                        'contratos.valor_estorno as valor_comissao',
                        'contratos.valor_plano as valor_plano',
                        'contratos.created_at as data_vencimento'
                    )
                    ->get();

                // Buscar comissoes normais
                // Parceiros: apenas parcelas confirmadas (status_apto_pagar=1)
                // CLT/PJ: logica original com folha=1
                $comissoesPendentes = DB::table('comissoes_corretores_lancadas as ccl')
                    ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                    ->where('c.user_id', $corretorId)
                    ->where('ccl.status_gerente', 1)
                    ->where('ccl.status_financeiro', 1)
                    ->where(function ($query) {
                        $query->where('ccl.valor', '!=', 0)
                            ->orWhere('ccl.incluir', 1);
                    })
                    ->where('ccl.finalizado', '!=', 1)
                    ->when($isParceiro, fn($q) => $q->where('ccl.status_apto_pagar', 1))
                    ->when(!$isParceiro, fn($q) => $q->where('ccl.folha', 1)->whereNull('ccl.data_baixa_gerente_folha'))
                    ->where(function ($query) {
                        $query->whereNotNull('c.contrato_id')
                            ->orWhereNotNull('c.contrato_empresarial_id');
                    })
                    ->pluck('ccl.id');

                $comissoesNormais = collect();
                if ($comissoesPendentes->isNotEmpty()) {

                    $idsProcessados = array_merge($idsProcessados, $comissoesPendentes->toArray());
                    $comissoesNormais = DB::table('comissoes_corretores_lancadas as ccl')
                        ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                        ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                        ->leftJoin('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                        ->leftJoin('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                        ->whereIn('ccl.id', $comissoesPendentes)
                        ->select(
                            'ccl.*',
                            DB::raw('CASE
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social
                                ELSE cl.nome END as cliente_nome'),
                            DB::raw('CASE
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.cnpj
                                ELSE cl.cpf END as cpf'),
                            DB::raw('CASE
                                WHEN c.contrato_id IS NOT NULL THEN ct.codigo_externo
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.codigo_externo
                                ELSE "---" END as contrato_codigo'),
                            DB::raw("CASE
                                WHEN c.contrato_id IS NOT NULL AND c.plano_id = 1 THEN 'individual'
                                WHEN c.contrato_id IS NOT NULL AND c.plano_id = 3 THEN 'coletivo'
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN 'empresarial'
                                ELSE 'outro'
                                END AS tipo_contrato"),
                            DB::raw('CASE
                                WHEN c.contrato_id IS NOT NULL THEN cl.quantidade_vidas
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.quantidade_vidas
                                ELSE cl.quantidade_vidas END as quantidade_vidas'),

                            'ccl.valor as valor_comissao',
                            DB::raw('CASE
                                WHEN c.contrato_id IS NOT NULL THEN ct.valor_plano
                                WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.valor_plano
                                ELSE ccl.valor END as valor_plano'),
                            DB::raw('COALESCE(ct.desconto_corretor, 0) + COALESCE(ce.desconto_corretor, 0) as desconto_corretor'),
                            'ccl.parcela as parcela',
                            'ccl.data_baixa_gerente as data_vencimento'
                        )
                        ->orderByRaw("CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social ELSE cl.nome END ASC")
                        ->orderBy('ccl.parcela', 'asc')
                        ->get()
                        ->map(fn($item) => $item)
                        ->groupBy('cliente_nome');
                }

                // Processar odontolÃ³gicos
                $odontoPendente = DB::table('odonto')
                    ->where('user_id', $corretorId)
                    ->where('pagou', 0)
                    ->get();

                $comissoesOdonto = $odontoPendente->map(function ($item) {
                    return (object)[
                        'cliente_nome' => $item->nome,
                        'cpf' => '',
                        'contrato_codigo' => 'ODON-' . $item->id,
                        'tipo_contrato' => 'odonto',
                        'quantidade_vidas' => 1,
                        'parcela' => null,
                        'valor_comissao' => $item->comissao,
                        'valor_plano' => $item->valor,
                        'desconto_corretor' => 0,
                        'data_vencimento' => $item->created_at
                    ];
                })->groupBy('cliente_nome');

                $comissoesCombinadas = $comissoesNormais->flatten()
                    ->merge($estornos)
                    ->merge($comissoesOdonto->flatten());

                // Calcular totais
                $totalIndividual = $comissoesCombinadas->where('tipo_contrato', 'individual')->sum('valor_comissao');
                $totalColetivo = $comissoesCombinadas->where('tipo_contrato', 'coletivo')->sum('valor_comissao');
                $totalEmpresarial = $comissoesCombinadas->where('tipo_contrato', 'empresarial')->sum('valor_comissao');
                $totalOdonto = $comissoesCombinadas->where('tipo_contrato', 'odonto')->sum('valor_comissao');
                $totalEstorno = $comissoesCombinadas->where('tipo_contrato', 'estorno')->sum('valor_comissao');
                $dadosDesconto = $comissoesCombinadas->sum('desconto_corretor');

                // Premiação (parceiros não têm premiação nem fixo)
                $premiacao = $isParceiro ? 0 : (DB::table('premiacoes')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->value('valor') ?? 0);

                $totalVale = DB::table('vale')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->sum('valor');

                $totalFixo = $isParceiro ? 0 : DB::table('fixo')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->sum('valor');


                // Cálculo final
                $totalCorretor = $totalIndividual + $totalColetivo + $totalEmpresarial + $totalOdonto + $premiacao - abs($totalEstorno) - $dadosDesconto;

                $totalVidas = $comissoesCombinadas->filter(function ($comissao) {
                    return $comissao->tipo_contrato !== 'estorno'; // Exclui as vidas de estornos
                })->sum('quantidade_vidas');
                $totalContrato = $comissoesCombinadas->count('cliente_nome');
                $dados[] = [
                    'corretor' => $corretor,
                    'is_parceiro' => $isParceiro,
                    'total' => $totalCorretor,
                    'comissoes' => $comissoesCombinadas->groupBy('tipo_contrato'),
                    'vidas' => $totalVidas,
                    'contratos' => $totalContrato,
                    'totais_tipos' => [
                        'individual' => $totalIndividual,
                        'coletivo' => $totalColetivo,
                        'empresarial' => $totalEmpresarial,
                        'odonto' => $totalOdonto,
                        'premiacao' => $premiacao,
                        'vale' => $totalVale,
                        'fixo' => $totalFixo,
                        'estorno' => $totalEstorno,
                        'desconto' => $dadosDesconto
                    ]
                ];
                $totalProcessado += $totalCorretor;
            }

            // Gerar PDF
            $pdf = PDF::loadView('folha.america.pdf', [
                'dados' => $dados,
                'totalGeral' => $totalProcessado,

                'dataGeracao' => now()->format('d/m/Y H:i:s')
            ]);

            DB::commit();

            $corretorNome = strtolower(str_replace([' ', '.', ','], '_', $corretor->name)); // Criar nome amigÃ¡vel
            //$filename = 'folha_pagamento_' . $corretorNome . '_' . now()->format('Ymd_His') . '.pdf';


            $filename = 'folha_pagamento_' . now()->format('Ymd_His') . '.pdf';
            $pdf->save(storage_path('app/folhas/' . $filename));

            return response()->json(['success' => true, 'download_url' => route('folha.america.download', ['file' => $filename])]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao gerar folha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao gerar folha'], 500);
        }
    }

    public function finalizarMes(Request $request)
    {
        $corretoraId = auth()->user()->corretora_id;

        try {
            // Buscar folha aberta para a corretora
            $folhaMes = DB::table('folha_mes')
                ->where('corretora_id', $corretoraId)
                ->where('status', 0) // Somente meses ainda em aberto
                ->first();

            if (!$folhaMes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum mÃªs em aberto encontrado para esta corretora.',
                ], 400);
            }

            // Identificar perÃ­odo de processamento
            $dataProcessamento = $folhaMes->mes; // Exemplo: "2025-09-01"
            $inicioMes = Carbon::parse($dataProcessamento)->startOfMonth()->toDateString();
            $fimMes = Carbon::parse($dataProcessamento)->endOfMonth()->toDateString();

            DB::beginTransaction();

            // 1. Obter todos os IDs dos corretores listados no frontend
            $corretoresSelecionados = DB::table('users')
                ->where('corretora_id', $corretoraId)
                ->pluck('id'); // IDs dos corretores pertencentes Ã  corretora atual

            if ($corretoresSelecionados->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum corretor encontrado para este mÃªs.',
                ]);
            }

            $competencia = Carbon::parse($dataProcessamento)->format('Y-m');

            // 2. Processar cada corretor
            foreach ($corretoresSelecionados as $corretorId) {
                // Aplicar faixa CLT antes de finalizar (recalcula valor com base no desempenho do mÃªs)
                $this->aplicarFaixaCltVendedor($corretorId, $competencia);
                $this->aplicarRegraParceiro($corretorId, $competencia);

                // 2.1 Atualizar parcelas (baixas)
                $comissoesPendentes = DB::table('comissoes_corretores_lancadas as ccl')
                    ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                    ->where('c.user_id', $corretorId)
                    ->where('c.corretora_id', $corretoraId)
                    ->where('ccl.status_gerente', 1)
                    ->where('ccl.status_financeiro', 1)
                    ->where('ccl.valor', '!=', 0)
                    ->where('ccl.folha', 1)
                    ->whereNull('ccl.data_baixa_gerente_folha')
                    ->where('ccl.finalizado', '!=', 1)
                    ->pluck('ccl.id');

                if ($comissoesPendentes->isNotEmpty()) {
                    DB::table('comissoes_corretores_lancadas')
                        ->whereIn('id', $comissoesPendentes)
                        ->update([
                            'data_baixa_gerente_folha' => $dataProcessamento,
                            'finalizado' => 1,
                            'updated_at' => now(),
                        ]);
                }

                // 2.2 Atualizar premiaÃ§Ãµes (baixa)
                $premiacao = DB::table('premiacoes')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->first();

                if ($premiacao) {
                    DB::table('premiacoes')
                        ->where('id', $premiacao->id)
                        ->update([
                            'pago' => 1,
                            'updated_at' => $dataProcessamento,
                        ]);
                }

                $fixo = DB::table('fixo')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->first();

                if($fixo) {
                    DB::table('fixo')
                        ->where('id', $fixo->id)
                        ->update([
                            'pago' => 1,
                            'updated_at' => $dataProcessamento,
                        ]);
                }

                $vale = DB::table('vale')
                    ->where('user_id', $corretorId)
                    ->where('pago', 0)
                    ->first();

                if($vale) {
                    DB::table('vale')
                        ->where('id', $vale->id)
                        ->update([
                            'pago' => 1,
                            'updated_at' => $dataProcessamento,
                        ]);
                }

                // 2.3 Atualizar contratos odontolÃ³gicos (baixa)
                $odontoPendentes = DB::table('odonto')
                    ->where('user_id', $corretorId)
                    ->where('pagou', 0)
                    ->pluck('id'); // IDs dos contratos odontolÃ³gicos pendentes

                if ($odontoPendentes->isNotEmpty()) {
                    DB::table('odonto')
                        ->whereIn('id', $odontoPendentes)
                        ->update([
                            'pagou' => 1,
                            'updated_at' => $dataProcessamento,
                        ]);
                }
            }

            // 3. Reativar parcelas desmarcadas (folha=0) para aparecerem na próxima folha
            // Parcelas que o usuário excluiu manualmente desta folha mas ainda não foram finalizadas
            $parcelasExcluidas = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->whereIn('c.user_id', $corretoresSelecionados)
                ->where('c.corretora_id', $corretoraId)
                ->where('ccl.status_gerente', 1)
                ->where('ccl.status_financeiro', 1)
                ->where('ccl.valor', '!=', 0)
                ->where('ccl.folha', 0)
                ->where('ccl.finalizado', '!=', 1)
                ->pluck('ccl.id');

            if ($parcelasExcluidas->isNotEmpty()) {
                DB::table('comissoes_corretores_lancadas')
                    ->whereIn('id', $parcelasExcluidas)
                    ->update(['folha' => 1, 'updated_at' => now()]);
            }

            // 4. Finalizar o mÃªs na tabela "folha_mes"
            DB::table('folha_mes')
                ->where('id', $folhaMes->id)
                ->update([
                    'status' => 1, // Finalizado
                    'updated_at' => $dataProcessamento,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'MÃªs finalizado com sucesso!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar o mÃªs.',
            ], 500);
        }
    }






    public function historicoFolha()
    {
        $corretoraId = auth()->user()->corretora_id;

        $meses = DB::table('folha_mes')
            ->where('corretora_id', $corretoraId)
            ->where('status', 1)
            ->orderBy('mes', 'desc')
            ->get();

        $historico = $meses->map(function ($folha) use ($corretoraId) {
            $mes = $folha->mes;

            $corretores = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->join('users as u', 'c.user_id', '=', 'u.id')
                ->where('c.corretora_id', $corretoraId)
                ->where('ccl.finalizado', 1)
                ->where('ccl.data_baixa_gerente_folha', $mes)
                ->selectRaw('
                    u.id,
                    u.name,
                    u.tipo_contrato,
                    COUNT(ccl.id)   AS total_parcelas,
                    SUM(ccl.valor)  AS total_comissao
                ')
                ->groupBy('u.id', 'u.name', 'u.tipo_contrato')
                ->orderBy('u.name')
                ->get();

            // Premiacao/fixo/vale pagos neste fechamento (updated_at = mes)
            $extras = DB::table('users as u')
                ->where('u.corretora_id', $corretoraId)
                ->whereIn('u.id', $corretores->pluck('id'))
                ->leftJoin(DB::raw("(SELECT user_id, SUM(valor) AS v FROM premiacoes WHERE pago=1 AND DATE_FORMAT(updated_at,'%Y-%m')=DATE_FORMAT('{$mes}','%Y-%m') GROUP BY user_id) AS pr"), 'pr.user_id', '=', 'u.id')
                ->leftJoin(DB::raw("(SELECT user_id, SUM(valor) AS v FROM fixo       WHERE pago=1 AND DATE_FORMAT(updated_at,'%Y-%m')=DATE_FORMAT('{$mes}','%Y-%m') GROUP BY user_id) AS fx"), 'fx.user_id', '=', 'u.id')
                ->leftJoin(DB::raw("(SELECT user_id, SUM(valor) AS v FROM vale       WHERE pago=1 AND DATE_FORMAT(updated_at,'%Y-%m')=DATE_FORMAT('{$mes}','%Y-%m') GROUP BY user_id) AS vl"), 'vl.user_id', '=', 'u.id')
                ->select('u.id', DB::raw('COALESCE(pr.v,0) AS premiacao'), DB::raw('COALESCE(fx.v,0) AS fixo'), DB::raw('COALESCE(vl.v,0) AS vale'))
                ->get()
                ->keyBy('id');

            $corretores = $corretores->map(function ($c) use ($extras) {
                $e = $extras[$c->id] ?? null;
                $c->premiacao     = (float) ($e->premiacao ?? 0);
                $c->fixo          = (float) ($e->fixo ?? 0);
                $c->vale          = (float) ($e->vale ?? 0);
                $c->total_liquido = (float) $c->total_comissao + $c->premiacao - $c->fixo - $c->vale;
                return $c;
            });

            return (object) [
                'folha'            => $folha,
                'mes_fmt'          => \Carbon\Carbon::parse($mes)->locale('pt_BR')->translatedFormat('F/Y'),
                'mes_raw'          => $mes,
                'corretores'       => $corretores,
                'total_corretores' => $corretores->count(),
                'total_parcelas'   => $corretores->sum('total_parcelas'),
                'total_bruto'      => $corretores->sum('total_comissao'),
                'total_liquido'    => $corretores->sum('total_liquido'),
            ];
        });

        $historico = $historico->filter(fn($h) => $h->total_parcelas > 0)->values();

        return view('folha.america.historico', compact('historico'));
    }

    public function gerarPdfHistoricoFolhaClt(Request $request)
    {
        $corretoraId = auth()->user()->corretora_id;
        $mes         = $request->input('mes');
        $userId      = $request->input('user_id');

        $folhaMes = DB::table('folha_mes')
            ->where('corretora_id', $corretoraId)
            ->where('mes', $mes)
            ->where('status', 1)
            ->first();

        abort_unless($folhaMes, 404, 'Mês não encontrado.');

        $corretoresSelecionados = $userId
            ? [(int) $userId]
            : DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->join('users as u', 'c.user_id', '=', 'u.id')
                ->where('c.corretora_id', $corretoraId)
                ->where('ccl.finalizado', 1)
                ->where('ccl.data_baixa_gerente_folha', $mes)
                ->whereIn('u.tipo_contrato', ['clt', 'pj'])
                ->distinct()
                ->pluck('u.id')
                ->toArray();

        $dados      = [];
        $totalGeral = 0;

        foreach ($corretoresSelecionados as $corretorId) {
            $corretor = DB::table('users')->find($corretorId);
            if (!$corretor) continue;

            $ids = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->where('c.user_id', $corretorId)
                ->where('c.corretora_id', $corretoraId)
                ->where('ccl.finalizado', 1)
                ->where('ccl.data_baixa_gerente_folha', $mes)
                ->where(function ($q) {
                    $q->whereNotNull('c.contrato_id')
                      ->orWhereNotNull('c.contrato_empresarial_id');
                })
                ->pluck('ccl.id');

            $comissoesNormais = collect();
            if ($ids->isNotEmpty()) {
                $comissoesNormais = DB::table('comissoes_corretores_lancadas as ccl')
                    ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                    ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                    ->leftJoin('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                    ->leftJoin('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                    ->whereIn('ccl.id', $ids)
                    ->select(
                        'ccl.*',
                        DB::raw('CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social ELSE cl.nome END as cliente_nome'),
                        DB::raw('CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.cnpj ELSE cl.cpf END as cpf'),
                        DB::raw('CASE WHEN c.contrato_id IS NOT NULL THEN ct.codigo_externo WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.codigo_externo ELSE "---" END as contrato_codigo'),
                        DB::raw("CASE WHEN c.contrato_id IS NOT NULL AND c.plano_id = 1 THEN 'individual' WHEN c.contrato_id IS NOT NULL AND c.plano_id = 3 THEN 'coletivo' WHEN c.contrato_empresarial_id IS NOT NULL THEN 'empresarial' ELSE 'outro' END AS tipo_contrato"),
                        DB::raw('CASE WHEN c.contrato_id IS NOT NULL THEN cl.quantidade_vidas WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.quantidade_vidas ELSE cl.quantidade_vidas END as quantidade_vidas'),
                        'ccl.valor as valor_comissao',
                        DB::raw('CASE WHEN c.contrato_id IS NOT NULL THEN ct.valor_plano WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.valor_plano ELSE ccl.valor END as valor_plano'),
                        DB::raw('COALESCE(ct.desconto_corretor, 0) + COALESCE(ce.desconto_corretor, 0) as desconto_corretor'),
                        'ccl.parcela as parcela',
                        'ccl.data_baixa_gerente as data_vencimento'
                    )
                    ->orderByRaw("CASE WHEN c.contrato_empresarial_id IS NOT NULL THEN ce.razao_social ELSE cl.nome END ASC")
                    ->orderBy('ccl.parcela', 'asc')
                    ->get()
                    ->map(fn($i) => $i)
                    ->groupBy('cliente_nome');
            }

            // Odonto: marcado pagou=1 com updated_at=$dataProcessamento ao fechar o mês
            $odontoItems = DB::table('odonto')
                ->where('user_id', $corretorId)
                ->where('pagou', 1)
                ->whereRaw("DATE_FORMAT(updated_at,'%Y-%m') = DATE_FORMAT(?,'%Y-%m')", [$mes])
                ->get();

            $comissoesOdonto = $odontoItems->map(fn($o) => (object)[
                'cliente_nome'     => $o->nome,
                'cpf'              => '',
                'contrato_codigo'  => 'ODON-' . $o->id,
                'tipo_contrato'    => 'odonto',
                'quantidade_vidas' => 1,
                'parcela'          => null,
                'valor_comissao'   => $o->comissao,
                'valor_plano'      => $o->valor,
                'desconto_corretor'=> 0,
                'data_vencimento'  => $o->created_at,
            ]);

            $flat = $comissoesNormais->flatten()->merge($comissoesOdonto);

            $totalIndividual  = $flat->where('tipo_contrato', 'individual')->sum('valor_comissao');
            $totalColetivo    = $flat->where('tipo_contrato', 'coletivo')->sum('valor_comissao');
            $totalEmpresarial = $flat->where('tipo_contrato', 'empresarial')->sum('valor_comissao');
            $totalOdonto      = $flat->where('tipo_contrato', 'odonto')->sum('valor_comissao');
            $dadosDesconto    = $flat->where('tipo_contrato', '!=', 'odonto')->sum('desconto_corretor');

            $premiacao = (float) DB::table('premiacoes')
                ->where('user_id', $corretorId)->where('pago', 1)
                ->whereRaw("DATE_FORMAT(updated_at,'%Y-%m') = DATE_FORMAT(?,'%Y-%m')", [$mes])
                ->sum('valor');

            $totalVale = (float) DB::table('vale')
                ->where('user_id', $corretorId)->where('pago', 1)
                ->whereRaw("DATE_FORMAT(updated_at,'%Y-%m') = DATE_FORMAT(?,'%Y-%m')", [$mes])
                ->sum('valor');

            $totalFixo = (float) DB::table('fixo')
                ->where('user_id', $corretorId)->where('pago', 1)
                ->whereRaw("DATE_FORMAT(updated_at,'%Y-%m') = DATE_FORMAT(?,'%Y-%m')", [$mes])
                ->sum('valor');

            $totalCorretor = $totalIndividual + $totalColetivo + $totalEmpresarial + $totalOdonto
                           + $premiacao - $dadosDesconto - $totalVale - $totalFixo;

            $dados[] = [
                'corretor'    => $corretor,
                'is_parceiro' => false,
                'total'       => $totalCorretor,
                'comissoes'   => $flat->groupBy('tipo_contrato'),
                'vidas'       => $flat->filter(fn($c) => $c->tipo_contrato !== 'estorno')->sum('quantidade_vidas'),
                'contratos'   => $flat->unique('contrato_codigo')->count(),
                'totais_tipos' => [
                    'individual'  => $totalIndividual,
                    'coletivo'    => $totalColetivo,
                    'empresarial' => $totalEmpresarial,
                    'odonto'      => $totalOdonto,
                    'premiacao'   => $premiacao,
                    'vale'        => $totalVale,
                    'fixo'        => $totalFixo,
                    'estorno'     => 0,
                    'desconto'    => $dadosDesconto,
                ],
            ];

            $totalGeral += $totalCorretor;
        }

        $mesFmt   = \Carbon\Carbon::parse($mes)->locale('pt_BR')->translatedFormat('F/Y');
        $slug     = str_replace('-', '', substr($mes, 0, 7));
        $filename = 'folha_hist_' . $slug . ($userId ? "_u{$userId}" : '') . '.pdf';

        $pdf = Pdf::loadView('folha.america.pdf', [
            'dados'       => $dados,
            'totalGeral'  => $totalGeral,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function adicionar(Request $request)
    {
        if(empty($request->user_id)) {
            return response()->json(['success' => false, 'message' => 'Corretor nÃ£o identificado.']);
        }

        if (!isset($request->valor) || $request->valor === '') {
            return response()->json(['success' => false, 'message' => 'Valor invÃ¡lido.']);
        }

        $user_id = $request->user_id;
        $valor = str_replace([".",","],["","."], $request->valor);

        DB::table('premiacoes')->updateOrInsert(
            ['user_id' => $user_id, 'pago' => 0], // Procura uma pendente
            ['valor' => $valor, 'updated_at' => now()] // Atualiza ou insere
        );

        return response()->json(['success' => true, 'message' => 'PremiaÃ§Ã£o adicionada com sucesso','valor' => $valor]);
    }

    public function downloadPDF($file)
    {
        $filePath = storage_path('app/folhas/' . $file);
        if (!file_exists($filePath)) {
            abort(404, 'Arquivo nÃ£o encontrado');
        }
        return response()
            ->download($filePath);
        //->deleteFileAfterSend(true);
    }

    private function obterResumoGeral($dataInicio, $dataFim, $corretorId = null, $plano_id = null)
    {
        $query = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id');

        if ($plano_id && $plano_id == 3) {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->where('ct.plano_id', 3);
        } elseif ($plano_id &&  $plano_id == 'empresarial') {
            $query->join('contrato_empresarial as ce', 'c.contrato_empresarial_id', '=', 'ce.id')
                ->whereNotIn('ce.plano_id', [1, 3]);
        } elseif($plano_id && $plano_id == 1) {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->where('ct.plano_id', $plano_id);
        }

        $query->where('c.corretora_id', $this->corretora_id)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado',"!=", 1)
            ->where('ccl.valor',"!=",0)
            ->whereBetween('ccl.data_baixa_gerente', [$dataInicio, $dataFim]);

        if ($corretorId) {
            $query->where('c.user_id', $corretorId);
        }

        $totalGeral = $query->sum('ccl.valor');
        $totalCorretores = $query->distinct('c.user_id')->count('c.user_id');
        $totalContratos = $query->distinct('c.contrato_id')->count('c.contrato_id');
        $resumoPorParcela = $query->select('ccl.parcela', DB::raw('SUM(ccl.valor) as total'), DB::raw('COUNT(*) as quantidade'))
            ->groupBy('ccl.parcela')
            ->orderBy('ccl.parcela')
            ->get()
            ->keyBy('parcela');

        // Retorno
        return [
            'total_geral' => $totalGeral,
            'total_corretores' => $totalCorretores,
            'total_contratos' => $totalContratos,
            'resumo_por_parcela' => $resumoPorParcela,
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim
            ]
        ];
    }

    public function exportarPlanosParaExcel(Request $request)
    {
        $corretora_id = auth()->user()->corretora_id;

        try {

            // Buscar os lanÃ§amentos de comissÃµes
            $dados = ComissoesCorretoresLancadas::with(['comissao' => function ($query) {
                $query->with(['contrato.cliente', 'contratoEmpresarial', 'administradora', 'corretor', 'plano']);
            }])
                ->where('status_financeiro', 1)
                ->where('status_gerente', 1)
                ->where('finalizado', '!=', 1)
                ->where('folha', 1)
                ->where('valor', '!=', 0)
                ->whereHas('comissao', function ($query) use ($corretora_id) {
                    $query->where('corretora_id', $corretora_id);
                })
                ->get()
                ->map(function ($lancamento) {
                    // Extrair a comissÃ£o
                    $comissao = $lancamento->comissao;

                    // Verificar se Ã© plano normal ou empresarial
                    $contrato = $comissao->contrato;
                    $contratoEmpresarial = $comissao->contratoEmpresarial;
                    $isEmpresarial = $contratoEmpresarial !== null;

                    return [
                        'administradora' => $comissao->administradora->nome ?? '',
                        'created_at' => $isEmpresarial
                            ? $contratoEmpresarial->created_at->format('d/m/Y')
                            : $contrato->created_at->format('d/m/Y'),
                        'codigo_externo' => $isEmpresarial
                            ? $contratoEmpresarial->codigo_externo
                            : $contrato->codigo_externo,
                        'cliente' => $isEmpresarial
                            ? $contratoEmpresarial->razao_social
                            : ($contrato->cliente->nome ?? ''),
                        'parcela' => $lancamento->parcela,
                        'valor_plano' => $isEmpresarial
                            ? $contratoEmpresarial->valor_plano
                            : $contrato->valor_plano,
                        'valor_comissao' => $lancamento->valor,
                        'corretor' => $comissao->corretor->name ?? '',
                        'plano_nome' => $comissao->plano->nome ?? '',
                        'quantidade_vidas' => $isEmpresarial
                            ? $contratoEmpresarial->quantidade_vidas
                            : ($contrato->cliente->quantidade_vidas ?? 0),
                        'tipo_contrato' => $isEmpresarial ? 'Empresarial' : 'Individual ou Coletivo',
                    ];
                })
                ->sortBy([
                    ['corretor', 'asc'],
                    ['administradora', 'asc'],
                ]);

            // Retorne os dados no formato JSON
            return response()->json($dados->values());
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar os dados do Excel: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao exportar os dados'], 500);
        }
    }



    public function exportarPlanosParaExcelFull(Request $request)
    {
        $corretora_id = auth()->user()->corretora_id;

        try {
            // Obter os dados via query SQL
            $dados = DB::select("
            SELECT
                -- Administradora Nome
                (SELECT nome FROM administradoras WHERE administradoras.id = comissoes.administradora_id) AS administradora,

                -- Data de CriaÃ§Ã£o
                DATE_FORMAT(
                    CASE
                        WHEN contratos.id IS NOT NULL THEN contratos.created_at
                        WHEN contrato_empresarial.id IS NOT NULL THEN contrato_empresarial.created_at
                        ELSE NULL
                    END, '%d/%m/%Y'
                ) AS created_at,

                -- CÃ³digo Externo
                CASE
                    WHEN contratos.id IS NOT NULL THEN contratos.codigo_externo
                    WHEN contrato_empresarial.id IS NOT NULL THEN contrato_empresarial.codigo_externo
                    ELSE NULL
                END AS codigo_externo,

                -- Nome do Cliente ou RazÃ£o Social
                CASE
                    WHEN contratos.id IS NOT NULL THEN (SELECT nome FROM clientes WHERE clientes.id = contratos.cliente_id)
                    WHEN contrato_empresarial.id IS NOT NULL THEN contrato_empresarial.razao_social
                    ELSE NULL
                END AS cliente,

                -- Parcela
                comissoes_corretores_lancadas.parcela,

                -- Valor do Plano
                CASE
                    WHEN contratos.id IS NOT NULL THEN contratos.valor_plano
                    WHEN contrato_empresarial.id IS NOT NULL THEN contrato_empresarial.valor_plano
                    ELSE NULL
                END AS valor_plano,

                -- Valor de ComissÃ£o
                comissoes_corretores_lancadas.valor AS valor_comissao,

                -- Nome do Corretor
                (SELECT name FROM users WHERE users.id = comissoes.user_id) AS corretor,

                -- Nome do Plano
                (SELECT nome FROM planos WHERE planos.id = comissoes.plano_id) AS plano_nome,

                -- Quantidade de Vidas
                CASE
                    WHEN contratos.id IS NOT NULL THEN (SELECT quantidade_vidas FROM clientes WHERE clientes.id = contratos.cliente_id)
                    WHEN contrato_empresarial.id IS NOT NULL THEN contrato_empresarial.quantidade_vidas
                    ELSE 0
                END AS quantidade_vidas,

                -- Tipo de Contrato
                CASE
                    WHEN contratos.id IS NOT NULL THEN 'Individual ou Coletivo'
                    WHEN contrato_empresarial.id IS NOT NULL THEN 'Empresarial'
                    ELSE 'Desconhecido'
                END AS tipo_contrato

            FROM comissoes_corretores_lancadas
            INNER JOIN comissoes ON comissoes_corretores_lancadas.comissoes_id = comissoes.id
            LEFT JOIN contratos ON comissoes.contrato_id = contratos.id
            LEFT JOIN contrato_empresarial ON comissoes.contrato_empresarial_id = contrato_empresarial.id

            WHERE
                comissoes_corretores_lancadas.status_financeiro = 1
                AND comissoes_corretores_lancadas.status_gerente = 1
                AND comissoes_corretores_lancadas.finalizado != 1
                AND comissoes_corretores_lancadas.folha = 1
                AND comissoes_corretores_lancadas.valor != 0
                AND comissoes.corretora_id = {$corretora_id}

            ORDER BY corretor,administradora, cliente
        ");

            // Retorna os dados no formato JSON
            return response()->json($dados);
        } catch (\Exception $e) {
            \Log::error('Erro ao exportar os dados do Excel: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao exportar os dados'], 500);
        }
    }


    private function obterCorretoresComValoreslast($dataInicio, $dataFim, $corretorId = null, $plano_id = null)
    {
        $query = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.image',
                DB::raw('
                (
                    COALESCE(SUM(DISTINCT ccl.valor), 0)
                    + COALESCE(SUM(DISTINCT o.valor_odonto), 0)
                    + COALESCE(SUM(DISTINCT p.valor_premiacao), 0)
                    - COALESCE(SUM(DISTINCT est.valor_estorno_total), 0)
                    - COALESCE(SUM(DISTINCT ct.desconto_corretor), 0)
                    - COALESCE(SUM(DISTINCT cte.desconto_corretor), 0)
                ) AS total_receber
            '),
                DB::raw('COUNT(DISTINCT ct.id) as total_contratos'),
                DB::raw('COUNT(DISTINCT ccl.id) as total_parcelas'),
                DB::raw('SUM(cc.quantidade_vidas) as quantidade_vidas'),
                DB::raw('COUNT(DISTINCT o.id) as total_odonto'),
                DB::raw('COUNT(DISTINCT est.id_estorno) as total_estorno')
            ])
            // ðŸ”¹ JOIN em comissÃµes e condiÃ§Ãµes de comissÃ£o dentro do join
            ->leftJoin('comissoes as c', function ($join) use ($plano_id) {
                $join->on('u.id', '=', 'c.user_id');
                if ($plano_id !== null) {
                    $join->where('c.plano_id', '=', $plano_id);
                }
            })
            ->leftJoin('comissoes_corretores_lancadas as ccl', function ($join) {
                $join->on('c.id', '=', 'ccl.comissoes_id')
                    ->where('ccl.status_gerente', 1)
                    ->where('ccl.status_financeiro', 1)
                    ->where('ccl.finalizado', '!=', 1)
                    ->where('ccl.folha', 1)
                    ->where('ccl.valor', '!=', 0);
            })
            ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->leftJoin('contrato_empresarial as cte', 'c.contrato_empresarial_id', '=', 'cte.id')
            ->leftJoin('clientes as cc', 'cc.id', '=', 'ct.cliente_id')

            // ðŸ”¹ Odonto (pagou = 0)
            ->leftJoin(DB::raw('(SELECT id, user_id, valor as valor_odonto FROM odonto WHERE pagou = 0) as o'), 'u.id', '=', 'o.user_id')

            // ðŸ”¹ PremiaÃ§Ãµes (pago = 0)
            ->leftJoin(DB::raw('(SELECT id, user_id, valor as valor_premiacao FROM premiacoes WHERE pago = 0) as p'), 'u.id', '=', 'p.user_id')

            // ðŸ”¹ Estornos
            ->leftJoin(DB::raw('(
            SELECT contratos.id as id_estorno, clientes.user_id, SUM(contratos.valor_estorno) as valor_estorno_total
            FROM contratos
            INNER JOIN clientes ON clientes.id = contratos.cliente_id
            WHERE contratos.estorno = 1 AND contratos.valor_estorno IS NOT NULL
            GROUP BY clientes.user_id, contratos.id
        ) as est'), 'u.id', '=', 'est.user_id')

            ->where('u.corretora_id', $this->corretora_id);

        // ðŸ”¹ Filtro por corretor especÃ­fico (opcional)
        if ($corretorId) {
            $query->where('u.id', $corretorId);
        }

        // ðŸ”¹ Agrupar e ordenar
        $dados = $query
            ->groupBy('u.id', 'u.name', 'u.email', 'u.image')
            ->having('total_receber', '>', 0)
            ->orderByDesc('total_receber')
            ->get();

        return $dados;
    }






    private function obterCorretoresComValores($dataInicio, $dataFim, $corretorId = null, $plano_id = null, $tipoContrato = null)
    {
        $query = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.image',
                'u.tipo_contrato',
                DB::raw('
                (
                    COALESCE(SUM(ccl.valor), 0)
                    + IFNULL((SELECT SUM(valor) FROM odonto WHERE user_id = u.id AND pagou = 0), 0)
                    + IFNULL((SELECT SUM(valor) FROM premiacoes WHERE user_id = u.id AND pago = 0), 0)
                    - IFNULL((
                        SELECT SUM(valor_estorno)
                        FROM contratos
                        INNER JOIN clientes ON clientes.id = contratos.cliente_id
                        WHERE contratos.estorno = 1
                          AND contratos.valor_estorno IS NOT NULL
                          AND contratos.data_baixa_estorno IS NULL
                          AND clientes.user_id = u.id
                    ), 0)
                    - COALESCE(SUM(
                        CASE
                            WHEN ccl.status_financeiro = 1
                            AND ccl.status_gerente = 1
                            AND ccl.finalizado != 1
                            AND ccl.folha = 1
                            AND ccl.valor != 0
                            THEN ct.desconto_corretor
                            ELSE 0
                        END
                    ), 0)
                    - COALESCE(SUM(
                        CASE
                            WHEN ccl.status_financeiro = 1
                            AND ccl.status_gerente = 1
                            AND ccl.finalizado != 1
                            AND ccl.folha = 1
                            AND ccl.valor != 0
                            THEN cte.desconto_corretor
                            ELSE 0
                        END
                    ), 0)
                    - IFNULL((SELECT SUM(valor) FROM vale WHERE user_id = u.id AND pago = 0), 0)
                    - IFNULL((SELECT SUM(valor) FROM fixo WHERE user_id = u.id AND pago = 0), 0)
                ) AS total_receber
            '),
                DB::raw('COUNT(DISTINCT ct.id) as total_contratos'),
                DB::raw('COUNT(ccl.id) as total_parcelas'),
                DB::raw('SUM(cc.quantidade_vidas) as quantidade_vidas'),
                DB::raw('(SELECT COUNT(*) FROM odonto WHERE user_id = u.id AND pagou = 0) as total_odonto'),
                DB::raw('(SELECT COUNT(*)
                      FROM clientes
                      INNER JOIN contratos ON contratos.cliente_id = clientes.id
                      WHERE contratos.estorno = 1
                        AND contratos.valor_estorno IS NOT NULL
                        AND clientes.user_id = u.id
                     ) as total_estorno')
            ])
            ->leftJoin('comissoes as c', function ($join) use ($plano_id) {
                $join->on('u.id', '=', 'c.user_id');
                if ($plano_id !== null) {
                    $join->where('c.plano_id', '=', $plano_id);
                }
            })
            ->leftJoin('comissoes_corretores_lancadas as ccl', function ($join) {
                $join->on('c.id', '=', 'ccl.comissoes_id')
                    ->where('ccl.status_financeiro', 1)
                    ->where('ccl.status_gerente', 1)
                    ->where('ccl.finalizado', '!=', 1)
                    ->where('ccl.folha', 1)
                    ->where('ccl.valor', '!=', 0);
            })
            ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->leftJoin('contrato_empresarial as cte', 'c.contrato_empresarial_id', '=', 'cte.id')
            ->leftJoin('clientes as cc', 'cc.id', '=', 'ct.cliente_id')
            ->where('u.corretora_id', $this->corretora_id);

        // Filtro por tipo de contrato
        if ($tipoContrato === 'parceiro') {
            $query->where('u.tipo_contrato', 'parceiro');
        } else {
            $query->where('u.tipo_contrato', '!=', 'parceiro');
        }

        // Filtro por corretor (se informado)
        if ($corretorId) {
            $query->where('u.id', $corretorId);
        }

        // Agrupar e ordenar
        $dados = $query
            ->groupBy('u.id', 'u.name', 'u.email', 'u.image', 'u.tipo_contrato')
            ->having('total_receber', '!=', 0)
            ->orderByDesc('total_receber')
            ->get();

        return $dados;

    }





    private function obterCorretoresComValores888888888888888888888($dataInicio, $dataFim, $corretorId = null, $plano_id = null)
    {
        $query = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.image',
                DB::raw('
                (
                    COALESCE(SUM(ccl.valor), 0)
                    + IFNULL((SELECT SUM(valor) FROM odonto WHERE user_id = u.id AND pagou = 0), 0)
                    + IFNULL((SELECT SUM(valor) FROM premiacoes WHERE user_id = u.id AND pago = 0), 0)
                    - IFNULL((
                        SELECT SUM(valor_estorno)
                        FROM contratos
                        INNER JOIN clientes ON clientes.id = contratos.cliente_id
                        WHERE contratos.estorno = 1
                          AND contratos.valor_estorno IS NOT NULL
                          AND clientes.user_id = u.id
                    ), 0)
                    - COALESCE(SUM(ct.desconto_corretor), 0)
                    - COALESCE(SUM(cte.desconto_corretor), 0)
                ) AS total_receber
            '),
                DB::raw('COUNT(DISTINCT ct.id) as total_contratos'),
                DB::raw('COUNT(ccl.id) as total_parcelas'),
                DB::raw('SUM(cc.quantidade_vidas) as quantidade_vidas'),
                DB::raw('(SELECT COUNT(*) FROM odonto WHERE user_id = u.id AND pagou = 0) as total_odonto'),
                DB::raw('(SELECT COUNT(*)
                      FROM clientes
                      INNER JOIN contratos ON contratos.cliente_id = clientes.id
                      WHERE contratos.estorno = 1
                        AND contratos.valor_estorno IS NOT NULL
                        AND clientes.user_id = u.id
                     ) as total_estorno')
            ])
            ->leftJoin('comissoes as c', 'u.id', '=', 'c.user_id')
            ->leftJoin('comissoes_corretores_lancadas as ccl', 'c.id', '=', 'ccl.comissoes_id')
            ->leftJoin('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->leftJoin('contrato_empresarial as cte', 'c.contrato_empresarial_id', '=', 'cte.id')
            ->leftJoin('clientes as cc', 'cc.id', '=', 'ct.cliente_id');

        // ðŸ”¹ Filtra sempre pela corretora atual
        $query->where('u.corretora_id', $this->corretora_id);

        // ðŸ”¹ Filtrar por plano, se necessÃ¡rio (permite tambÃ©m quem nÃ£o tem comissÃ£o)
        if ($plano_id !== null) {
            $query->where(function($q) use ($plano_id) {
                $q->where('c.plano_id', $plano_id)
                    ->orWhereNull('c.id'); // permite quem sÃ³ tem odonto
            });
        }

        // ðŸ”¹ Filtros principais de comissÃµes (mas permite quem nÃ£o tem)
        $query->where(function ($q) {
            $q->where(function($q2){
                $q2->where('ccl.status_gerente', 1)
                    ->where('ccl.status_financeiro', 1)
                    ->where('ccl.finalizado', '!=', 1)
                    ->where('ccl.folha', 1)
                    ->where('ccl.valor', '!=', 0);
            })
                ->orWhereNull('ccl.id'); // permite odonto-only
        });

        // ðŸ”¹ Filtro por corretor especÃ­fico (se informado)
        if ($corretorId) {
            $query->where('u.id', $corretorId);
        }

        // ðŸ”¹ Agrupar e ordenar
        $dados = $query->groupBy('u.id', 'u.name', 'u.email', 'u.image')
            ->having('total_receber', '>', 0)
            ->orderByDesc('total_receber')
            ->get();

        return $dados;
    }

    private function obterCorretoresComValorescomview($dataInicio, $dataFim, $corretorId = null, $plano_id = null)
    {
        // Query simplificada para consultar a view diretamente.
        $query = DB::table('corretores_resumo')
            ->select([
                'user_id as id',
                'name',
                'email',
                'image',
                'total_receber',
                'total_contratos',
                'total_parcelas',
                'quantidade_vidas',
                'total_odonto',
                'total_estorno'
            ])
            ->where('corretora_id', $this->corretora_id);

        // Filtro opcional por corretor
        if ($corretorId) {
            $query->where('user_id', $corretorId);
        }

        // Filtragem por total_receber > 0 e ordenaÃ§Ã£o
        $dados = $query
            ->having('total_receber', '>', 0)
            ->orderByDesc('total_receber')
            ->get();

        return $dados;
    }




    private function obterCorretoresComValoreasadasdasdass($dataInicio, $dataFim, $corretorId = null, $plano_id = 1)
    {
        $query = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.image',
                DB::raw('
                (
                    SUM(ccl.valor)
                    + IFNULL((SELECT SUM(valor) FROM odonto WHERE user_id = u.id AND pagou = 0), 0)
                    - IFNULL((
                        SELECT SUM(valor_estorno)
                        FROM contratos
                        INNER JOIN clientes ON clientes.id = contratos.cliente_id
                        WHERE contratos.estorno = 1
                          AND contratos.valor_estorno IS NOT NULL
                          AND clientes.user_id = u.id
                    ), 0)
                ) as total_receber
            '),
                DB::raw('COUNT(DISTINCT ct.id) as total_contratos'),
                DB::raw('COUNT(ccl.id) as total_parcelas'),
                DB::raw('SUM(cc.quantidade_vidas) as quantidade_vidas'),
                DB::raw('(SELECT COUNT(*) FROM odonto WHERE user_id = u.id AND pagou = 0) as total_odonto'),
                DB::raw('(SELECT COUNT(*)
                      FROM clientes
                      INNER JOIN contratos ON contratos.cliente_id = clientes.id
                      WHERE contratos.estorno = 1
                        AND contratos.valor_estorno IS NOT NULL
                        AND clientes.user_id = u.id
                     ) as total_estorno')
            ])
            ->join('comissoes as c', 'u.id', '=', 'c.user_id')
            ->join('comissoes_corretores_lancadas as ccl', 'c.id', '=', 'ccl.comissoes_id');

        // LÃ³gica para diferentes tipos de planos
        if ($plano_id == 3) {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cc', 'cc.id', '=', 'ct.cliente_id');
        } elseif ($plano_id == 'empresarial') {
            $query->join('contrato_empresarial as ct', 'c.contrato_empresarial_id', '=', 'ct.id');
        } else {
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cc', 'cc.id', '=', 'ct.cliente_id');
        }

        // Filtros
        $query->where('c.corretora_id', $this->corretora_id)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->where('ccl.valor', '!=', 0);

        if ($corretorId) {
            $query->where('u.id', $corretorId);
        }

        $dados = $query->groupBy('u.id', 'u.name', 'u.email', 'u.image')
            ->having('total_receber', '>', 0)
            ->orderByDesc('total_receber')
            ->get();

        return $dados;
    }





    private function obterListaCorretores()
    {
        return DB::table('users as u')
            ->select('u.id', 'u.name')
            ->join('comissoes as c', 'u.id', '=', 'c.user_id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->where('c.corretora_id', $this->corretora_id)
            ->where('ct.plano_id', $this->plano_id)
            ->distinct()
            ->orderBy('u.name')
            ->get();
    }

    private function obterComissoesDetalhadas($corretorId, $dataInicio, $dataFim, $planoId)
    {
        $query = DB::table('comissoes_corretores_lancadas as ccl')
            ->select([
                'cl.id as cliente_id',
                'cl.nome as cliente_nome',
                'cl.cpf as cliente_cpf',
                'cl.quantidade_vidas as vidas',
                'ct.id as contrato_id',
                'ct.codigo_externo as contrato_codigo',
                'ccl.parcela',
                'ccl.valor as valor_comissao',
                'ccl.data as data_vencimento',
                'ccl.data_baixa_gerente as data_aprovacao',

            ])
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id');

        if ($planoId == 3) {
            // Para plano coletivo
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->where('ct.plano_id', 3);
        } elseif ($planoId == 'empresarial') {
            // Para plano empresarial
            $query->join('contrato_empresarial as ct', 'c.contrato_empresarial_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.codigo_cliente', '=', 'cl.id')
                ->whereNotIn('ct.plano_id', [1, 3]);
        } else {
            // Para plano individual
            $query->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
                ->join('clientes as cl', 'ct.cliente_id', '=', 'cl.id')
                ->where('ct.plano_id', 1);
        }

        $query->where('c.user_id', $corretorId)
            ->where('ccl.valor',"!=",0)
            ->whereBetween('ccl.data_baixa_gerente', [$dataInicio, $dataFim])
            ->orderBy('cl.nome')
            ->orderBy('ccl.parcela');

        return $query->get()->groupBy('cliente_id');
    }

    private function calcularResumoCorretor($comissoes)
    {
        $totalGeral = 0;
        $vidas = 0;
        $totalContratos = $comissoes->count();
        $totalParcelas = 0;
        $resumoPorParcela = [];

        foreach ($comissoes as $clienteComissoes) {
            foreach ($clienteComissoes as $comissao) {
                $totalGeral += $comissao->valor_comissao;
                $vidas += $comissao->vidas;
                $totalParcelas++;
                $parcela = $comissao->parcela;
                if (!isset($resumoPorParcela[$parcela])) {
                    $resumoPorParcela[$parcela] = [
                        'quantidade' => 0,
                        'valor' => 0,
                        'vidas' => 0,
                    ];
                }
                $resumoPorParcela[$parcela]['quantidade']++;
                $resumoPorParcela[$parcela]['valor'] += $comissao->valor_comissao;
                $resumoPorParcela[$parcela]['vidas'] += $comissao->vidas;
            }
        }

        return [
            'total_geral' => $totalGeral,
            'total_contratos' => $totalContratos,
            'total_parcelas' => $totalParcelas,
            'resumo_por_parcela' => $resumoPorParcela,
            'vidas' => $vidas
        ];
    }

    public function consultarFolhasGeradas(Request $request)
    {
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        $query = DB::table('comissoes_corretores_lancadas as ccl')
            ->select([
                'ccl.data_baixa_gerente_folha',
                DB::raw('COUNT(DISTINCT c.user_id) as total_corretores'),
                DB::raw('COUNT(ccl.id) as total_parcelas'),
                DB::raw('SUM(ccl.valor) as total_valor')
            ])
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->where('c.corretora_id', $this->corretora_id)
            ->where('ct.plano_id', $this->plano_id)
            ->whereNotNull('ccl.data_baixa_gerente_folha');
        if ($dataInicio && $dataFim) {
            $query->whereBetween('ccl.data_baixa_gerente_folha', [$dataInicio,$dataFim]);
        }
        $folhasGeradas = $query->groupBy('ccl.data_baixa_gerente_folha')
            ->orderByDesc('ccl.data_baixa_gerente_folha')
            ->get();
        return response()->json([
            'success' => true,
            'folhas' => $folhasGeradas
        ]);
    }

    public function reverterFolha(Request $request)
    {
        try {
            $request->validate([
                'data_processamento' => 'required|date'
            ]);

            $dataProcessamento = $request->input('data_processamento');

            DB::beginTransaction();

            // Reverter apenas as comissÃµes processadas nesta data especÃ­fica
            $comissoesRevertidas = DB::table('comissoes_corretores_lancadas')
                ->where('data_baixa_gerente_folha', $dataProcessamento)
                ->update([
                    'data_baixa_gerente_folha' => null,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Folha revertida! {$comissoesRevertidas} comissÃµes voltaram para pendente.",
                'comissoes_revertidas' => $comissoesRevertidas
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao reverter folha: ' . $e->getMessage()
            ], 500);
        }
    }

    public function atualizarFolha(Request $request)
    {
        try {
            $clienteId = $request->input('cliente_id');

            DB::table('comissoes_corretores_lancadas')
                ->where('id', $clienteId)
                ->update(['folha' => $request->input('folha')]);

            // Retorna resumo atualizado para o JS atualizar os cards
            $row = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->where('ccl.id', $clienteId)
                ->select('c.user_id')
                ->first();

            $resumo = $row ? $this->obterResumoPorPlanoCorretor($row->user_id) : null;

            return response()->json(['success' => true, 'resumo' => $resumo]);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar folha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar folha'], 500);
        }
    }

    // ==================== CÃLCULO FAIXAS CLT ====================

    private function aplicarFaixaCltVendedor(int $corretorId, string $competencia): void
    {
        $user = DB::table('users')->where('id', $corretorId)->select('tipo_contrato')->first();

        if (!$user || $user->tipo_contrato !== 'clt') {
            return;
        }

        // ComissÃµes pendentes do vendedor nesta competÃªncia
        $comissoes = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.competencia', $competencia)
            ->where('ccl.finalizado', '!=', 1)
            ->whereNull('ccl.data_baixa_gerente_folha')
            ->select('ccl.id', 'ccl.comissoes_id', 'ccl.valor_pago')
            ->get();

        if ($comissoes->isEmpty()) {
            return;
        }

        // Vidas = contratos Ãºnicos com baixa nesta competÃªncia
        $vidas = $comissoes->unique('comissoes_id')->count();

        // ProduÃ§Ã£o = soma do valor pago pelos clientes no mÃªs
        $producao = $comissoes->whereNotNull('valor_pago')->sum('valor_pago');

        // Encontrar a faixa pela quantidade de vidas
        $faixa = FaixaComissaoClt::where('corretora_id', $this->corretora_id)
            ->where('vidas_min', '<=', $vidas)
            ->where(function ($q) use ($vidas) {
                $q->whereNull('vidas_max')->orWhere('vidas_max', '>=', $vidas);
            })
            ->orderByDesc('vidas_min')
            ->first();

        if (!$faixa) {
            return;
        }

        // Determinar o percentual: base ou bÃ´nus (se produÃ§Ã£o atingiu o limiar)
        $percentual = (float) $faixa->percentual;
        if ($faixa->producao_bonus !== null && $producao >= (float) $faixa->producao_bonus && $faixa->percentual_bonus !== null) {
            $percentual = (float) $faixa->percentual_bonus;
        }

        // Recalcular valor de cada comissÃ£o com o percentual apurado
        foreach ($comissoes as $ccl) {
            if (!$ccl->valor_pago) {
                continue;
            }

            $base      = (float) $ccl->valor_pago - 35;
            $novoValor = max(0, $base * $percentual / 100);

            DB::table('comissoes_corretores_lancadas')
                ->where('id', $ccl->id)
                ->update(['valor' => $novoValor]);
        }
    }

    // ==================== FAIXAS CLT (CRUD) ====================

    public function indexFaixasClt()
    {
        $faixas = FaixaComissaoClt::where('corretora_id', $this->corretora_id)
            ->orderBy('vidas_min')
            ->get();

        return view('folha.america.faixas-clt', compact('faixas'));
    }

    public function salvarFaixaClt(Request $request)
    {
        $request->validate([
            'vidas_min'        => 'required|integer|min:0',
            'vidas_max'        => 'nullable|integer|gt:vidas_min',
            'percentual'       => 'required|numeric|min:0|max:100',
            'producao_bonus'   => 'nullable|numeric|min:0',
            'percentual_bonus' => 'nullable|numeric|min:0|max:100',
        ]);

        FaixaComissaoClt::create([
            'corretora_id'     => $this->corretora_id,
            'nome'             => '',
            'vidas_min'        => $request->vidas_min,
            'vidas_max'        => $request->vidas_max ?: null,
            'producao_min'     => 0,
            'producao_max'     => null,
            'percentual'       => $request->percentual,
            'producao_bonus'   => $request->producao_bonus ?: null,
            'percentual_bonus' => $request->percentual_bonus ?: null,
        ]);

        $this->renumerarFaixasClt();

        return redirect()->route('folha.america.faixas-clt')->with('success', 'Faixa cadastrada com sucesso.');
    }

    public function atualizarFaixaClt(Request $request, $id)
    {
        $request->validate([
            'vidas_min'        => 'required|integer|min:0',
            'vidas_max'        => 'nullable|integer|gt:vidas_min',
            'percentual'       => 'required|numeric|min:0|max:100',
            'producao_bonus'   => 'nullable|numeric|min:0',
            'percentual_bonus' => 'nullable|numeric|min:0|max:100',
        ]);

        FaixaComissaoClt::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->update([
                'vidas_min'        => $request->vidas_min,
                'vidas_max'        => $request->vidas_max ?: null,
                'percentual'       => $request->percentual,
                'producao_bonus'   => $request->producao_bonus ?: null,
                'percentual_bonus' => $request->percentual_bonus ?: null,
            ]);

        $this->renumerarFaixasClt();

        return response()->json(['ok' => true]);
    }

    public function deletarFaixaClt($id)
    {
        FaixaComissaoClt::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->delete();

        $this->renumerarFaixasClt();

        return response()->json(['ok' => true]);
    }

    private function renumerarFaixasClt(): void
    {
        $faixas = FaixaComissaoClt::where('corretora_id', $this->corretora_id)
            ->orderBy('vidas_min')
            ->pluck('id');

        foreach ($faixas as $index => $id) {
            FaixaComissaoClt::where('id', $id)
                ->update(['nome' => 'Regra ' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)]);
        }
    }

    // ==================== FAIXAS PJ (comissÃ£o por desempenho) ====================

    public function indexFaixasPj()
    {
        $regras = RegraComissaoPj::where('corretora_id', $this->corretora_id)
            ->orderBy('vidas_min')
            ->get();

        return view('folha.america.regras-pj', compact('regras'));
    }

    public function salvarFaixaPj(Request $request)
    {
        $request->validate([
            'vidas_min'     => 'required|integer|min:0',
            'vidas_max'     => 'nullable|integer|gt:vidas_min',
            'parcela_2_pct' => 'required|numeric|min:0|max:999',
            'parcela_3_pct' => 'required|numeric|min:0|max:999',
            'parcela_4_pct' => 'required|numeric|min:0|max:999',
        ]);

        RegraComissaoPj::create([
            'corretora_id'  => $this->corretora_id,
            'nome'          => '',
            'vidas_min'     => $request->vidas_min,
            'vidas_max'     => $request->vidas_max ?: null,
            'parcela_2_pct' => $request->parcela_2_pct,
            'parcela_3_pct' => $request->parcela_3_pct,
            'parcela_4_pct' => $request->parcela_4_pct,
        ]);

        $this->renumerarRegrasPj();

        return redirect()->route('folha.america.regras-pj')->with('success', 'Faixa cadastrada com sucesso.');
    }

    public function atualizarFaixaPj(Request $request, $id)
    {
        $request->validate([
            'vidas_min'     => 'required|integer|min:0',
            'vidas_max'     => 'nullable|integer|gt:vidas_min',
            'parcela_2_pct' => 'required|numeric|min:0|max:999',
            'parcela_3_pct' => 'required|numeric|min:0|max:999',
            'parcela_4_pct' => 'required|numeric|min:0|max:999',
        ]);

        RegraComissaoPj::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->update([
                'vidas_min'     => $request->vidas_min,
                'vidas_max'     => $request->vidas_max ?: null,
                'parcela_2_pct' => $request->parcela_2_pct,
                'parcela_3_pct' => $request->parcela_3_pct,
                'parcela_4_pct' => $request->parcela_4_pct,
            ]);

        $this->renumerarRegrasPj();

        return response()->json(['ok' => true]);
    }

    public function deletarFaixaPj($id)
    {
        RegraComissaoPj::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->delete();

        $this->renumerarRegrasPj();

        return response()->json(['ok' => true]);
    }

    private function renumerarRegrasPj(): void
    {
        $regras = RegraComissaoPj::where('corretora_id', $this->corretora_id)
            ->orderBy('vidas_min')
            ->pluck('id');

        foreach ($regras as $index => $id) {
            RegraComissaoPj::where('id', $id)
                ->update(['nome' => 'Faixa ' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)]);
        }
    }

    // Planos fixos para PJ
    const PJ_PLANO_SUPER_SIMPLES = 5;
    const PJ_PLANO_INDIVIDUAL    = 1;
    const PJ_PLANO_COLETIVO      = 3;

    private function aplicarRegraPjVendedor(int $corretorId, string $competencia): void
    {
        $user = DB::table('users')->where('id', $corretorId)->select('tipo_contrato')->first();
        if (!$user || $user->tipo_contrato !== 'pj') return;

        // ComissÃµes do mÃªs (todas as pendentes de finalizaÃ§Ã£o)
        $comissoes = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->where('c.user_id', $corretorId)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.competencia', $competencia)
            ->where('ccl.finalizado', '!=', 1)
            ->whereNull('ccl.data_baixa_gerente_folha')
            ->select('ccl.id', 'ccl.parcela', 'ccl.valor_pago', 'ccl.comissoes_id', 'c.plano_id')
            ->get();

        if ($comissoes->isEmpty()) return;

        // Contar vidas apenas de Individual (1) + Super Simples (5) para determinar a faixa
        $vidasFaixa = $comissoes
            ->whereIn('plano_id', [self::PJ_PLANO_INDIVIDUAL, self::PJ_PLANO_SUPER_SIMPLES])
            ->unique('comissoes_id')
            ->count();

        // Encontrar a faixa pelo nÃºmero de vidas
        $regra = RegraComissaoPj::where('corretora_id', $this->corretora_id)
            ->where('vidas_min', '<=', $vidasFaixa)
            ->where(function ($q) use ($vidasFaixa) {
                $q->whereNull('vidas_max')->orWhere('vidas_max', '>=', $vidasFaixa);
            })
            ->orderByDesc('vidas_min')
            ->first();

        if (!$regra) return;

        // Agrupar parcelas por comissao_id para processar contrato a contrato
        $porComissao = $comissoes->groupBy('comissoes_id');

        foreach ($porComissao as $comissaoId => $parcelas) {
            $planoId  = $parcelas->first()->plano_id;
            $baseCalc = $parcelas->whereNotNull('valor_pago')->max('valor_pago') ?? 0;

            // Zerar todas as parcelas do contrato na competÃªncia
            $ids = $parcelas->pluck('id');
            DB::table('comissoes_corretores_lancadas')
                ->whereIn('id', $ids)
                ->update(['valor' => 0]);

            if ($baseCalc <= 0) continue;

            if ($planoId == self::PJ_PLANO_SUPER_SIMPLES) {
                // Super Simples: 100% na 1Âª parcela
                $p1 = $parcelas->firstWhere('parcela', 1);
                if ($p1) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p1->id)
                        ->update(['valor' => round($baseCalc * 1.00, 2)]);
                }

            } elseif ($planoId == self::PJ_PLANO_COLETIVO) {
                // Coletivo: 50% na 2Âª parcela (fixo, independente da faixa)
                $p2 = $parcelas->firstWhere('parcela', 2);
                if ($p2) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p2->id)
                        ->update(['valor' => round($baseCalc * 0.50, 2)]);
                }

            } elseif ($planoId == self::PJ_PLANO_INDIVIDUAL) {
                // Individual: segue a faixa (parcelas 2, 3, 4)
                $p2 = $parcelas->firstWhere('parcela', 2);
                $p3 = $parcelas->firstWhere('parcela', 3);
                $p4 = $parcelas->firstWhere('parcela', 4);

                if ($p2 && (float)$regra->parcela_2_pct > 0) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p2->id)
                        ->update(['valor' => round($baseCalc * (float)$regra->parcela_2_pct / 100, 2)]);
                }
                if ($p3 && (float)$regra->parcela_3_pct > 0) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p3->id)
                        ->update(['valor' => round($baseCalc * (float)$regra->parcela_3_pct / 100, 2)]);
                }
                if ($p4 && (float)$regra->parcela_4_pct > 0) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p4->id)
                        ->update(['valor' => round($baseCalc * (float)$regra->parcela_4_pct / 100, 2)]);
                }
            }
        }
    }

    // ==================== PARCEIROS CONFIG ====================

    public function indexParceiros()
    {
        $parceiros = DB::table('users')
            ->where('corretora_id', $this->corretora_id)
            ->where('tipo_contrato', 'parceiro')
            ->where('ativo', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $configs = ParceirosConfigPagamento::with('user')
            ->whereIn('user_id', $parceiros->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('folha.america.parceiros-config', compact('parceiros', 'configs'));
    }

    public function salvarParceiro(Request $request)
    {
        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'frequencia'     => 'required|in:semanal,quinzenal,mensal,personalizado',
            'dias_pagamento' => 'required|string',
        ]);

        $dias = array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $request->dias_pagamento)))
        );

        ParceirosConfigPagamento::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'frequencia'     => $request->frequencia,
                'dias_pagamento' => $dias,
                'ativo'          => 1,
            ]
        );

        return redirect()->route('folha.america.parceiros-config')->with('success', 'ConfiguraÃ§Ã£o salva.');
    }

    public function deletarParceiro($id)
    {
        ParceirosConfigPagamento::whereHas('user', function ($q) {
            $q->where('corretora_id', $this->corretora_id);
        })->where('id', $id)->delete();

        return redirect()->route('folha.america.parceiros-config')->with('success', 'ConfiguraÃ§Ã£o removida.');
    }

    // ==================== PAGAMENTOS PARCEIROS ====================

    public function pagamentosParceiros()
    {
        return view('folha.america.parceiros-pagamentos');
    }

    public function previewPagamentosParceiros(Request $request)
    {
        $data = $request->filled('data')
            ? Carbon::parse($request->data)
            : Carbon::today();

        $configs = $this->parceirosDueNaData($data);

        $preview = $configs->map(function ($config) {
            $pendentes = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->where('c.user_id', $config->user_id)
                ->where('ccl.status_gerente', 1)
                ->where('ccl.status_financeiro', 1)
                ->where('ccl.valor', '!=', 0)
                ->where('ccl.finalizado', '!=', 1)
                ->whereNull('ccl.data_baixa_gerente_folha')
                ->selectRaw('COUNT(*) as total_parcelas, COALESCE(SUM(ccl.valor), 0) as total_valor')
                ->first();

            return [
                'user_id'        => $config->user_id,
                'nome'           => $config->user->name,
                'frequencia'     => $config->frequencia,
                'total_parcelas' => (int) ($pendentes->total_parcelas ?? 0),
                'total_valor'    => (float) ($pendentes->total_valor ?? 0),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $preview,
            'data_referencia' => $data->format('d/m/Y'),
        ]);
    }

    public function gerarPagamentosParceiros(Request $request)
    {
        $request->validate([
            'data' => 'nullable|date',
        ]);

        $data = $request->filled('data')
            ? Carbon::parse($request->data)
            : Carbon::today();

        $configs = $this->parceirosDueNaData($data);

        if ($configs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum parceiro com pagamento previsto para ' . $data->format('d/m/Y') . '.',
            ]);
        }

        DB::beginTransaction();
        try {
            $processados = [];

            foreach ($configs as $config) {
                $userId = $config->user_id;

                $comissoes = DB::table('comissoes_corretores_lancadas as ccl')
                    ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                    ->where('c.user_id', $userId)
                    ->where('ccl.status_gerente', 1)
                    ->where('ccl.status_financeiro', 1)
                    ->where('ccl.valor', '!=', 0)
                    ->where('ccl.finalizado', '!=', 1)
                    ->whereNull('ccl.data_baixa_gerente_folha')
                    ->select('ccl.id', 'ccl.valor')
                    ->get();

                if ($comissoes->isEmpty()) {
                    continue;
                }

                $totalValor = $comissoes->sum('valor');

                DB::table('comissoes_corretores_lancadas')
                    ->whereIn('id', $comissoes->pluck('id'))
                    ->update([
                        'data_baixa_gerente_folha' => $data->toDateString(),
                        'finalizado'               => 1,
                        'updated_at'               => now(),
                    ]);

                DB::table('valores_corretores_lancadas')->insert([
                    'corretora_id'   => $this->corretora_id,
                    'user_id'        => $userId,
                    'data'           => $data->toDateString(),
                    'valor_comissao' => $totalValor,
                    'valor_total'    => $totalValor,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $processados[] = [
                    'nome'           => $config->user->name,
                    'total_parcelas' => $comissoes->count(),
                    'total_valor'    => $totalValor,
                ];
            }

            DB::commit();

            return response()->json([
                'success'     => true,
                'processados' => $processados,
                'message'     => count($processados) . ' parceiro(s) pagos em ' . $data->format('d/m/Y') . '.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar pagamentos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function historicoPagamentosParceiros()
    {
        $historico = DB::table('valores_corretores_lancadas as vcl')
            ->join('users as u', 'vcl.user_id', '=', 'u.id')
            ->where('vcl.corretora_id', $this->corretora_id)
            ->where('u.tipo_contrato', 'parceiro')
            ->orderByDesc('vcl.data')
            ->orderBy('u.name')
            ->select('vcl.id', 'u.name', 'vcl.data', 'vcl.valor_comissao', 'vcl.valor_total')
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'data' => $historico]);
    }

    // ==================== COMISSÃƒO CORRETORA ====================

    public function indexComissaoCorretora()
    {
        $configuracoes = ComissoesCorretoraConfiguracoes::with(['plano', 'administradora', 'user'])
            ->where('corretora_id', $this->corretora_id)
            ->orderBy('plano_id')
            ->orderBy('administradora_id')
            ->orderBy('parcela')
            ->get();

        $planos          = Plano::orderBy('nome')->get();
        $administradoras = Administradora::orderBy('nome')->get();

        $vendedores = DB::table('users')
            ->where('corretora_id', $this->corretora_id)
            ->where('ativo', 1)
            ->select('id', 'name', 'tipo_contrato')
            ->orderBy('name')
            ->get();

        // Resumo do mÃªs aberto
        $folhaMes = DB::table('folha_mes')
            ->where('corretora_id', $this->corretora_id)
            ->where('status', 0)
            ->first();

        $resumoMes = null;
        if ($folhaMes) {
            $competencia = Carbon::parse($folhaMes->mes)->format('Y-m');
            $resumoMes = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->where('c.corretora_id', $this->corretora_id)
                ->where('ccl.competencia', $competencia)
                ->where('ccl.status_financeiro', 1)
                ->selectRaw('
                    COUNT(DISTINCT ccl.comissoes_id) as total_contratos,
                    COALESCE(SUM(ccl.valor_pago), 0)      as total_recebido,
                    COALESCE(SUM(ccl.valor), 0)           as total_pago_vendedores,
                    COALESCE(SUM(ccl.valor_corretora), 0) as total_corretora
                ')
                ->first();
        }

        return view('folha.america.comissao-corretora', compact(
            'configuracoes', 'planos', 'administradoras', 'vendedores', 'resumoMes', 'folhaMes'
        ));
    }

    public function salvarComissaoCorretora(Request $request)
    {
        $request->validate([
            'plano_id'          => 'required|exists:planos,id',
            'administradora_id' => 'required|exists:administradoras,id',
            'user_id'           => 'nullable|exists:users,id',
            'parcela'           => 'required|integer|min:1',
            'valor'             => 'required|numeric|min:0|max:100',
        ]);

        ComissoesCorretoraConfiguracoes::create([
            'corretora_id'      => $this->corretora_id,
            'plano_id'          => $request->plano_id,
            'administradora_id' => $request->administradora_id,
            'user_id'           => $request->user_id ?: null,
            'tabela_origens_id' => $request->tabela_origens_id ?: null,
            'parcela'           => $request->parcela,
            'valor'             => $request->valor,
        ]);

        return redirect()->route('folha.america.comissao-corretora')->with('success', 'ConfiguraÃ§Ã£o salva.');
    }

    public function deletarComissaoCorretora($id)
    {
        ComissoesCorretoraConfiguracoes::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->delete();

        return redirect()->route('folha.america.comissao-corretora')->with('success', 'ConfiguraÃ§Ã£o removida.');
    }

    public function recalcularValorCorretora(Request $request)
    {
        $competencia = $request->competencia
            ?? Carbon::parse(
                DB::table('folha_mes')
                    ->where('corretora_id', $this->corretora_id)
                    ->where('status', 0)
                    ->value('mes') ?? now()
            )->format('Y-m');

        DB::beginTransaction();
        try {
            $comissoes = DB::table('comissoes_corretores_lancadas as ccl')
                ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
                ->where('c.corretora_id', $this->corretora_id)
                ->where('ccl.competencia', $competencia)
                ->where('ccl.status_financeiro', 1)
                ->select('ccl.id', 'ccl.comissoes_id', 'ccl.parcela', 'ccl.valor_pago', 'c.plano_id', 'c.administradora_id', 'c.user_id')
                ->get();

            $atualizados = 0;
            foreach ($comissoes as $ccl) {
                $config = ComissoesCorretoraConfiguracoes::where('corretora_id', $this->corretora_id)
                    ->where('plano_id', $ccl->plano_id)
                    ->where('administradora_id', $ccl->administradora_id)
                    ->where('parcela', $ccl->parcela)
                    ->where(fn($q) => $q->where('user_id', $ccl->user_id)->orWhereNull('user_id'))
                    ->orderByRaw('user_id IS NULL ASC') // prefere config especÃ­fica do vendedor
                    ->first();

                if (!$config || !$ccl->valor_pago) {
                    continue;
                }

                $valorCorretora = max(0, ((float) $ccl->valor_pago - 35) * (float) $config->valor / 100);

                DB::table('comissoes_corretores_lancadas')
                    ->where('id', $ccl->id)
                    ->update(['valor_corretora' => $valorCorretora]);

                $atualizados++;
            }

            DB::commit();

            return response()->json([
                'success'     => true,
                'atualizados' => $atualizados,
                'competencia' => $competencia,
                'message'     => "$atualizados registro(s) recalculados para $competencia.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==================== REGRAS PJ ====================

    public function indexRegrasPJ()
    {
        $regras = ComissoesCorretoresConfiguracoes::with(['plano', 'administradora', 'user'])
            ->where('corretora_id', $this->corretora_id)
            ->orderBy('user_id')
            ->orderBy('plano_id')
            ->orderBy('parcela')
            ->get();

        $vendedoresPJ = DB::table('users')
            ->where('corretora_id', $this->corretora_id)
            ->where('tipo_contrato', 'pj')
            ->where('ativo', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $planos          = Plano::orderBy('nome')->get();
        $administradoras = Administradora::orderBy('nome')->get();

        return view('folha.america.regras-pj', compact('regras', 'vendedoresPJ', 'planos', 'administradoras'));
    }

    public function salvarRegraPJ(Request $request)
    {
        $request->validate([
            'plano_id'        => 'required|exists:planos,id',
            'administradora_id' => 'required|exists:administradoras,id',
            'user_id'         => 'nullable|exists:users,id',
            'parcela'         => 'required|integer|min:1',
            'valor'           => 'required|numeric|min:0|max:100',
        ]);

        ComissoesCorretoresConfiguracoes::create([
            'corretora_id'      => $this->corretora_id,
            'plano_id'          => $request->plano_id,
            'administradora_id' => $request->administradora_id,
            'user_id'           => $request->user_id ?: null,
            'tabela_origens_id' => $request->tabela_origens_id ?: null,
            'parcela'           => $request->parcela,
            'valor'             => $request->valor,
        ]);

        return redirect()->route('folha.america.regras-pj')->with('success', 'Regra cadastrada com sucesso.');
    }

    public function deletarRegraPJ($id)
    {
        ComissoesCorretoresConfiguracoes::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->delete();

        return redirect()->route('folha.america.regras-pj')->with('success', 'Regra removida.');
    }

    // ==================== REGRAS COMISSAO PARCEIROS ====================

    public function indexRegrasParceiro()
    {
        $parceiros = DB::table('users')
            ->where('corretora_id', $this->corretora_id)
            ->where('tipo_contrato', 'parceiro')
            ->where('ativo', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $planos = DB::table('planos')->select('id', 'nome')->orderBy('nome')->get();

        $regras = ParceirosRegraComissao::with(['parceiro', 'plano'])
            ->where('corretora_id', $this->corretora_id)
            ->orderBy('parceiro_id')
            ->orderBy('plano_id')
            ->get();

        $parceirosMap = $parceiros->keyBy('id');
        $planosMap    = $planos->keyBy('id');

        return view('folha.america.parceiros-regras', compact(
            'parceiros', 'planos', 'regras', 'parceirosMap', 'planosMap'
        ));
    }

    public function salvarRegraParceiro(Request $request)
    {
        $request->validate([
            'parceiro_id'   => 'required|exists:users,id',
            'plano_id'      => 'required|exists:planos,id',
            'parcela_1_pct' => 'required|numeric|min:0|max:100',
            'parcela_2_pct' => 'required|numeric|min:0|max:100',
            'parcela_3_pct' => 'required|numeric|min:0|max:100',
            'parcela_4_pct' => 'required|numeric|min:0|max:100',
            'parcela_5_pct' => 'nullable|numeric|min:0|max:100',
            'parcela_6_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        ParceirosRegraComissao::updateOrCreate(
            [
                'corretora_id' => $this->corretora_id,
                'parceiro_id'  => $request->parceiro_id,
                'plano_id'     => $request->plano_id,
            ],
            [
                'parcela_1_pct' => $request->parcela_1_pct,
                'parcela_2_pct' => $request->parcela_2_pct,
                'parcela_3_pct' => $request->parcela_3_pct,
                'parcela_4_pct' => $request->parcela_4_pct,
                'parcela_5_pct' => $request->parcela_5_pct ?? 0,
                'parcela_6_pct' => $request->parcela_6_pct ?? 0,
            ]
        );

        return redirect()->route('folha.america.parceiros.regras')->with('success', 'Regra salva com sucesso.');
    }

    public function atualizarRegraParceiro(Request $request, $id)
    {
        $request->validate([
            'parcela_1_pct' => 'required|numeric|min:0|max:100',
            'parcela_2_pct' => 'required|numeric|min:0|max:100',
            'parcela_3_pct' => 'required|numeric|min:0|max:100',
            'parcela_4_pct' => 'required|numeric|min:0|max:100',
            'parcela_5_pct' => 'nullable|numeric|min:0|max:100',
            'parcela_6_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        ParceirosRegraComissao::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->update([
                'parcela_1_pct' => $request->parcela_1_pct,
                'parcela_2_pct' => $request->parcela_2_pct,
                'parcela_3_pct' => $request->parcela_3_pct,
                'parcela_4_pct' => $request->parcela_4_pct,
                'parcela_5_pct' => $request->parcela_5_pct ?? 0,
                'parcela_6_pct' => $request->parcela_6_pct ?? 0,
            ]);

        return response()->json(['ok' => true]);
    }

    public function deletarRegraParceiro($id)
    {
        ParceirosRegraComissao::where('id', $id)
            ->where('corretora_id', $this->corretora_id)
            ->delete();

        return response()->json(['ok' => true]);
    }

    private function aplicarRegraParceiro(int $parceiroId, string $competencia): void
    {
        $user = DB::table('users')->where('id', $parceiroId)->select('tipo_contrato')->first();
        if (!$user || $user->tipo_contrato !== 'parceiro') return;

        $comissoes = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->where('c.user_id', $parceiroId)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.status_gerente', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->whereNull('ccl.data_baixa_gerente_folha')
            ->select('ccl.id', 'ccl.parcela', 'ccl.valor_pago', 'ccl.comissoes_id', 'c.plano_id')
            ->get();

        if ($comissoes->isEmpty()) return;

        foreach ($comissoes->groupBy('comissoes_id') as $comissaoId => $parcelas) {
            $planoId  = $parcelas->first()->plano_id;
            $baseCalc = $parcelas->whereNotNull('valor_pago')->max('valor_pago') ?? 0;

            $regra = ParceirosRegraComissao::where('corretora_id', $this->corretora_id)
                ->where('parceiro_id', $parceiroId)
                ->where('plano_id', $planoId)
                ->first();

            if (!$regra) continue;

            DB::table('comissoes_corretores_lancadas')
                ->whereIn('id', $parcelas->pluck('id'))
                ->update(['valor' => 0]);

            if ($baseCalc <= 0) continue;

            foreach ([1 => $regra->parcela_1_pct, 2 => $regra->parcela_2_pct, 3 => $regra->parcela_3_pct, 4 => $regra->parcela_4_pct] as $num => $pct) {
                if ((float) $pct <= 0) continue;
                $p = $parcelas->firstWhere('parcela', $num);
                if ($p) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p->id)
                        ->update(['valor' => round($baseCalc * (float) $pct / 100, 2)]);
                }
            }
        }
    }

    private function parceirosDueNaData(Carbon $data): \Illuminate\Support\Collection
    {
        $configs = ParceirosConfigPagamento::with('user')
            ->whereHas('user', fn($q) => $q->where('corretora_id', $this->corretora_id))
            ->where('ativo', 1)
            ->get();

        return $configs->filter(function ($config) use ($data) {
            $dias = $config->dias_pagamento;

            return match ($config->frequencia) {
                'semanal'                              => in_array($data->dayOfWeekIso, $dias),
                'quinzenal', 'mensal', 'personalizado' => in_array($data->day, $dias),
                default                                => false,
            };
        });
    }
}
