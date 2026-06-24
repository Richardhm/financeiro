<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Comissao;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\ComissoesCorretoresConfiguracoes;
use App\Models\ComissoesCorretoresDefault;
use App\Models\ComissoesCorretoraConfiguracoes;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\ParceirosRegraComissao;
use App\Services\ParceirosComissaoService;
use Carbon\Carbon;

class ProcessSpreadsheet extends Command
{
    protected $signature = 'spreadsheet:process {file} {--job-id=}';
    protected $description = 'Processar planilha de baixas';

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $filePath = $this->argument('file');
        $jobId    = $this->option('job-id');

        Log::info("=== COMANDO INICIADO: $filePath ===");

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: $filePath");
            return 1;
        }

        try {
            $reader = ReaderEntityFactory::createReaderFromFile($filePath);
            $reader->open($filePath);

            $processedRows  = 0;
            $parceirosTouched = [];

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber <= 1) continue;

                    $processedRows++;

                    if ($processedRows % 10 == 0) {
                        Log::info("Processadas $processedRows linhas...");
                        if ($jobId) {
                            $this->updateProgress($jobId, $processedRows);
                        }
                    }

                    $cells = $row->getCells();
                    $nome  = $cells[7]->getValue();
                    $valor = $cells[10]->getValue();



                    if ($id_alt = $this->verificarCarteirinha($nome, $valor)) {

                        $cliente_alt = Cliente::find($id_alt);
                        $user        = User::find($cliente_alt->user_id);

                        $cliente_alt->cateirinha = $cells[5]->getValue();
                        $cliente_alt->save();

                        $contrato = Contrato::where("cliente_id", $id_alt)->first();
                        if ($contrato) {
                            $contrato->created_at = $cells[6]->getValue()->format('Y-m-d');
                            $contrato->save();
                        }

                        $comissao = Comissao::where("contrato_id", $contrato->id)->first();

                        if ($comissao && !ComissoesCorretoresLancadas::where('comissoes_id', $comissao->id)->exists()) {
                            $this->cadastrarComissao(
                                $user,
                                $this->parseNumber($valor),
                                $comissao,
                                $cliente_alt->dia,
                                $cells[13]->getValue()->format('Y-m-d')
                            );
                        }

                        if ($cells[11]->getValue() == "LIQUIDADO" || $cells[11]->getValue() == "LIQUIDADO N/COB") {
                            $comissoes    = ComissoesCorretoresLancadas::where('comissoes_id', $comissao->id)->get();
                            $vencimento   = $cells[13]->getValue()->format('Y-m-d');
                            $dt_pagamento = $cells[12]->getValue()->format('Y-m-d');
                            $competencia  = $cells[13]->getValue()->format('Y-m');

                            foreach ($comissoes as $c) {
                                $datasCadastradas = ComissoesCorretoresLancadas::where('comissoes_id', $c->comissoes_id)
                                    ->whereNotNull('data')
                                    ->pluck('data');

                                $dataMenor = $datasCadastradas->every(fn($data) => $vencimento < $data);

                                if ($dataMenor) {
                                    ComissoesCorretoresLancadas::where('comissoes_id', $c->comissoes_id)
                                        ->where('parcela', 1)
                                        ->update(['data_baixa' => $dt_pagamento]);
                                }

                                if (date('m', strtotime($c->data)) == date('m', strtotime($vencimento))) {
                                    $valorPago      = (float) $cells[10]->getValue();
                                    $valorCorretora = $this->calcularValorCorretora($c->comissoes_id, $c->parcela, $valorPago);

                                    ComissoesCorretoresLancadas::where('id', $c->id)->update([
                                        'status_financeiro' => 1,
                                        'data_baixa'        => $dt_pagamento,
                                        'valor_pago'        => $valorPago,
                                        'competencia'       => $competencia,
                                        'valor_corretora'   => $valorCorretora ?: null,
                                    ]);

                                    if ($user->tipo_contrato === 'parceiro') {
                                        $parceirosTouched[$user->id] = true;
                                    }
                                }
                            }
                        }
                    } else {
                        $spreadsheetCode    = $cells[5]->getValue();
                        //dd($spreadsheetCode);
                        $carteirinha_existe = Cliente::select('clientes.*')
                            ->join('users', 'users.id', '=', 'clientes.user_id')
                            ->join('contratos', 'contratos.cliente_id', '=', 'clientes.id')
                            ->whereRaw("LEFT(cateirinha, 11) = ?", [$spreadsheetCode])
                            ->with(['contrato', 'contrato.comissao', 'contrato.comissao.comissoesLancadas'])
                            ->first();

                        if (($cells[11]->getValue() == "LIQUIDADO" || $cells[11]->getValue() == "LIQUIDADO N/COB") && $carteirinha_existe) {
                            $comissoes    = $carteirinha_existe->contrato->comissao->comissoesLancadas;
                            $vencimento   = $cells[13]->getValue()->format('Y-m-d');
                            $dt_pagamento = $cells[12]->getValue()->format('Y-m-d');
                            $competencia  = $cells[13]->getValue()->format('Y-m');

                            foreach ($comissoes as $c) {
                                $datasCadastradas = ComissoesCorretoresLancadas::where('comissoes_id', $c->comissoes_id)
                                    ->whereNotNull('data')
                                    ->pluck('data');

                                $dataMenor = $datasCadastradas->every(fn($data) => $vencimento < $data);

                                if ($dataMenor) {
                                    ComissoesCorretoresLancadas::where('comissoes_id', $c->comissoes_id)
                                        ->where('parcela', 1)
                                        ->update(['data_baixa' => $dt_pagamento]);
                                }

                                if (date('m', strtotime($c->data)) == date('m', strtotime($vencimento))) {
                                    $valorPago      = (float) $cells[10]->getValue();
                                    $valorCorretora = $this->calcularValorCorretora($c->comissoes_id, $c->parcela, $valorPago);

                                    ComissoesCorretoresLancadas::where('id', $c->id)->update([
                                        'status_financeiro' => 1,
                                        'data_baixa'        => $dt_pagamento,
                                        'valor_pago'        => $valorPago,
                                        'competencia'       => $competencia,
                                        'valor_corretora'   => $valorCorretora ?: null,
                                    ]);

                                    $contratoCli = $carteirinha_existe->contrato ?? null;
                                    if ($contratoCli) {
                                        $userCli = User::find($carteirinha_existe->user_id);
                                        if ($userCli && $userCli->tipo_contrato === 'parceiro') {
                                            $parceirosTouched[$userCli->id] = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Aplicar regras de comissão para parceiros independentes afetados
            foreach (array_keys($parceirosTouched) as $parceiroId) {
                ParceirosComissaoService::aplicarRegra($parceiroId);
                Log::info("Regras de comissão aplicadas para parceiro ID: {$parceiroId}");
            }

            $this->atualizarContrato();

            if ($jobId) {
                $this->updateProgress($jobId, $processedRows, true);
            }

            Log::info("=== PROCESSAMENTO CONCLUÍDO: $processedRows linhas ===");
            $this->info("=== CONCLUÍDO: $processedRows linhas ===");

        } catch (\Exception $e) {
            Log::error("Erro: " . $e->getMessage());
            $this->error("Erro: " . $e->getMessage());

            if ($jobId) {
                Cache::put("spreadsheet_processing_{$jobId}", [
                    'status'    => 'failed',
                    'error'     => $e->getMessage(),
                    'failed_at' => now()
                ], 3600);
            }

            return 1;
        } finally {
            if (isset($reader)) {
                try { $reader->close(); } catch (\Throwable $e) {}
            }
            if (file_exists($filePath)) {
                try { unlink($filePath); } catch (\Throwable $e) {}
            }
        }

        return 0;
    }

    private function verificarCarteirinha($nome, $valor)
    {
        $cliente = Cliente::select('clientes.*')
            ->join('contratos', 'contratos.cliente_id', '=', 'clientes.id')
            ->where('clientes.nome', $nome)
            ->where('contratos.valor_adesao', $valor)
            ->first();

        return $cliente ? $cliente->id : null;
    }

    private function parseNumber($number)
    {
        $number = trim($number);

        if (strpos($number, ',') === false && strpos($number, '.') === false) {
            return floatval($number);
        }

        if (strpos($number, ',') !== false && strpos($number, '.') !== false) {
            if (strrpos($number, ',') > strrpos($number, '.')) {
                $number = str_replace('.', '', $number);
                $number = str_replace(',', '.', $number);
            } else {
                $number = str_replace(',', '', $number);
            }
            return floatval($number);
        }

        if (strpos($number, ',') !== false) {
            $number = str_replace('.', '', $number);
            $number = str_replace(',', '.', $number);
            return floatval($number);
        }

        return floatval($number);
    }

    private function cadastrarComissao($user, $valor, $comissao, $dia, $data_vigencia)
    {
        if ($user->tipo_contrato === 'parceiro') {
            $this->cadastrarComissaoParceiro($user, $valor, $comissao, $data_vigencia);
            return;
        }

        $corretora_id = $user->corretora_id;
        $user_id      = $user->id;

        $comissao_corretor_default  = 0;
        $comissao_corretor_contagem = 0;

        if ($user->clt == 1) {
            $dados = ComissoesCorretoresDefault::where("plano_id", 1)
                ->where("administradora_id", 4)
                ->where("corretora_id", $corretora_id)
                ->get();

            foreach ($dados as $c) {
                $valor_comissao_default = (float) $valor - 35;
                $comissaoVendedor = new ComissoesCorretoresLancadas();
                $comissaoVendedor->comissoes_id = $comissao->id;
                $comissaoVendedor->parcela      = $c->parcela;
                $comissaoVendedor->valor        = ($valor_comissao_default * $this->parseNumber($c->valor)) / 100;

                if ($comissao_corretor_default == 0) {
                    $comissaoVendedor->data             = $data_vigencia;
                    $comissaoVendedor->status_financeiro = 1;
                    $comissaoVendedor->data_baixa       = $data_vigencia;
                    $comissaoVendedor->valor_pago       = $valor;
                } else {
                    $comissaoVendedor->data = $this->calcularData($data_vigencia, $dia, $comissao_corretor_default);
                }

                $comissaoVendedor->save();
                $comissao_corretor_default++;
            }
        } else {
            $configuradas = ComissoesCorretoresConfiguracoes::where("plano_id", 1)
                ->where("administradora_id", 4)
                ->where("corretora_id", $corretora_id)
                ->where("user_id", $user_id)
                ->get();

            if ($configuradas->isEmpty()) {
                $configuradas = ComissoesCorretoresConfiguracoes::where("plano_id", 1)
                    ->where("administradora_id", 4)
                    ->where("corretora_id", $corretora_id)
                    ->whereNull("user_id")
                    ->get();
            }

            foreach ($configuradas as $c) {
                $valor_comissao = (float) $valor - 35;
                $comissaoVendedor = new ComissoesCorretoresLancadas();
                $comissaoVendedor->comissoes_id = $comissao->id;
                $comissaoVendedor->parcela      = $c->parcela;
                $comissaoVendedor->valor        = ($valor_comissao * $this->parseNumber($c->valor)) / 100;

                if ($comissao_corretor_contagem == 0) {
                    $comissaoVendedor->data             = $data_vigencia;
                    $comissaoVendedor->status_financeiro = 1;
                    $comissaoVendedor->data_baixa       = $data_vigencia;
                    $comissaoVendedor->valor_pago       = $valor;
                } else {
                    $comissaoVendedor->data = $this->calcularData($data_vigencia, $dia, $comissao_corretor_contagem);
                }

                $comissaoVendedor->save();
                $comissao_corretor_contagem++;
            }
        }
    }

    private function cadastrarComissaoParceiro($user, float $valorPago, $comissao, string $dataVigencia): void
    {
        $totalParcelasPorPlano = [1 => 6, 3 => 7, 5 => 6];
        $totalParcelas = $totalParcelasPorPlano[(int) $comissao->plano_id] ?? 4;

        $regra = ParceirosRegraComissao::where('corretora_id', $user->corretora_id)
            ->where('parceiro_id', $user->id)
            ->where('plano_id', $comissao->plano_id)
            ->first();

        $percentuais = [
            1 => $regra ? (float) $regra->parcela_1_pct : 0,
            2 => $regra ? (float) $regra->parcela_2_pct : 0,
            3 => $regra ? (float) $regra->parcela_3_pct : 0,
            4 => $regra ? (float) $regra->parcela_4_pct : 0,
            5 => $regra ? (float) $regra->parcela_5_pct : 0,
            6 => $regra ? (float) $regra->parcela_6_pct : 0,
        ];

        // Use o valor_plano do contrato como base, excluindo o acrescimo da 1ª parcela
        $valorBase = (float) Contrato::where('id', $comissao->contrato_id)->value('valor_plano') ?: ($valorPago - 35);

        for ($num = 1; $num <= $totalParcelas; $num++) {
            $pct  = $percentuais[$num] ?? 0;
            $data = Carbon::parse($dataVigencia)->addMonths($num - 1)->format('Y-m-d');

            $ccl                   = new ComissoesCorretoresLancadas();
            $ccl->comissoes_id     = $comissao->id;
            $ccl->parcela          = $num;
            $ccl->valor            = $pct > 0 ? round($valorBase * $pct / 100, 2) : 0;
            $ccl->porcentagem_paga = $pct;
            $ccl->data             = $data;

            if ($num === 1) {
                $ccl->status_financeiro = 1;
                $ccl->data_baixa        = $dataVigencia;
                $ccl->valor_pago        = $valorPago;
                $ccl->competencia       = Carbon::parse($dataVigencia)->format('Y-m');
            }

            $ccl->save();
        }
    }

    private function calcularValorCorretora(int $comissaoId, int $parcela, float $valorPago): float
    {
        $comissao = DB::table('comissoes')->where('id', $comissaoId)->first();
        if (!$comissao) {
            return 0;
        }

        $config = ComissoesCorretoraConfiguracoes::where('corretora_id', $comissao->corretora_id)
            ->where('plano_id', $comissao->plano_id)
            ->where('administradora_id', $comissao->administradora_id)
            ->where('parcela', $parcela)
            ->where(fn($q) => $q->where('user_id', $comissao->user_id)->orWhereNull('user_id'))
            ->orderByRaw('user_id IS NULL ASC')
            ->first();

        if (!$config) {
            return 0;
        }

        return max(0, ($valorPago - 35) * (float) $config->valor / 100);
    }

    private function calcularData($data_vigencia, $dia, $meses)
    {
        $base  = date("Y-m", strtotime($data_vigencia . " +{$meses} month"));
        $mes   = (int) explode("-", $base)[1];
        $ano   = explode("-", $base)[0];

        if ($dia == 30 && $mes == 2) {
            $bissexto = date('L', mktime(0, 0, 0, 1, 1, $ano));
            return $bissexto ? "{$ano}-02-29" : "{$ano}-02-28";
        }

        return date("Y-m-{$dia}", strtotime($base));
    }

    private function atualizarContrato()
    {
        $comissoes = DB::select("
            SELECT * FROM comissoes_corretores_lancadas
            WHERE comissoes_id IN (
                SELECT id FROM comissoes
                WHERE contrato_id IN (SELECT id FROM contratos WHERE plano_id = 1)
            )
            AND status_financeiro = 1
        ");

        $mapa = [2 => 6, 3 => 7, 4 => 8, 5 => 9, 6 => 11];

        foreach ($comissoes as $cc) {
            $financeiro_id = $mapa[$cc->parcela] ?? 5;
            $contrato_id   = Comissao::where("id", $cc->comissoes_id)->value('contrato_id');
            if ($contrato_id) {
                Contrato::where("id", $contrato_id)->update(["financeiro_id" => $financeiro_id]);
            }
        }
    }

    private function updateProgress($jobId, $processedLines, $completed = false)
    {
        try {
            $cacheKey    = "spreadsheet_processing_{$jobId}";
            $currentData = Cache::get($cacheKey, []);

            if ($completed) {
                $currentData['status']       = 'completed';
                $currentData['completed_at'] = now();
            }

            $currentData['processed_lines'] = $processedLines;
            $currentData['last_update']     = now();

            Cache::put($cacheKey, $currentData, 3600);
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar progresso: " . $e->getMessage());
        }
    }
}
