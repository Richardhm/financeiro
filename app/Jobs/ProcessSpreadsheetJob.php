<?php
// app/Jobs/ProcessSpreadsheetJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Comissao;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\ComissoesCorretoresConfiguracoes;
use App\Models\ComissoesCorretoresDefault;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;


class ProcessSpreadsheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected $filePath;
    protected $jobId;

    public function __construct($filePath, $jobId)
    {
        $this->filePath = $filePath;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        try {
            Log::info("=== JOB INICIADO: {$this->jobId} ===");

            // ⚡ CONFIGURAR TENANT
            config(['database.connections.tenant.database' => 'bmsysc98_america_bmsys']);

            // ⚡ CONTAR TOTAL DE LINHAS
            $totalLines = $this->countTotalLines();
            Log::info("Total de linhas: $totalLines");

            // ⚡ CRIAR CACHE INICIAL
            $cacheKey = "spreadsheet_processing_{$this->jobId}";
            $cacheData = [
                'status' => 'processing',
                'total_lines' => $totalLines,
                'processed_lines' => 0,
                'started_at' => now(),
                'filename' => $this->jobId
            ];

            Cache::put($cacheKey, $cacheData, 3600);
            Log::info("Cache criado: $cacheKey");

            // ⚡ PROCESSAR DIRETAMENTE NO JOB (SEM COMMAND)
            $this->processSpreadsheet();

        } catch (\Exception $e) {
            Log::error("Erro no job {$this->jobId}: " . $e->getMessage());

            Cache::put("spreadsheet_processing_{$this->jobId}", [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now(),
                'filename' => $this->jobId
            ], 3600);

            throw $e;
        }
    }

    private function processSpreadsheet()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $cacheKey = "spreadsheet_processing_{$this->jobId}";

        if (!file_exists($this->filePath)) {
            throw new \Exception("Arquivo não encontrado: {$this->filePath}");
        }

        try {
            $reader = ReaderEntityFactory::createReaderFromFile($this->filePath);
            $reader->open($this->filePath);

            $processedRows = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber <= 1) continue;

                    $processedRows++;

                    // ⚡ PROCESSAR LINHA
                    $this->processRow($row);

                    // ⚡ ATUALIZAR PROGRESSO A CADA 5 LINHAS
                    if ($processedRows % 5 == 0) {
                        Log::info("Processadas $processedRows linhas...");
                        $this->updateProgress($cacheKey, $processedRows);
                    }
                }
            }

            $reader->close();
            $this->atualizarContrato();

            // ⚡ MARCAR COMO CONCLUÍDO
            $this->updateProgress($cacheKey, $processedRows, true);

            Log::info("=== PROCESSAMENTO CONCLUÍDO: $processedRows linhas ===");

        } catch (\Exception $e) {
            Log::error("Erro no processamento: " . $e->getMessage());

            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now()
            ], 3600);

            throw $e;
        } finally {
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }

    private function processRow($row)
    {
        $cells = $row->getCells();
        $nome = $cells[7]->getValue();
        $valor = $cells[10]->getValue();

        if ($id_alt = $this->verificarCarteirinha($nome, $valor)) {
            $cliente_alt = Cliente::on('tenant')->find($id_alt);
            $user = User::on('tenant')->find($cliente_alt->user_id);
            $cliente_alt->cateirinha = $cells[5]->getValue();
            $cliente_alt->save();

            $contrato_id = Contrato::on('tenant')->where("cliente_id", $id_alt)->first()->id;
            $cc = Contrato::on('tenant')->find($contrato_id);
            $cc->created_at = $cells[6]->getValue()->format('Y-m-d');
            $cc->save();

            $comissao = Comissao::on('tenant')->where("contrato_id", $contrato_id)->first();

            if ($cells[11]->getValue() == "LIQUIDADO" || $cells[11]->getValue() == "LIQUIDADO N/COB") {
                if ($cliente_alt) {
                    $comissoes = $cliente_alt->contrato->comissao->comissoesLancadas;
                    $vencimento = $cells[13]->getValue()->format('Y-m-d');
                    $dt_pagamento = $cells[12]->getValue()->format('Y-m-d');

                    foreach ($comissoes as $c) {
                        $datasCadastradas = ComissoesCorretoresLancadas::on('tenant')
                            ->where('comissoes_id', $c->comissoes_id)
                            ->whereNotNull('data')
                            ->pluck('data');

                        $dataMenor = $datasCadastradas->every(fn($data) => $vencimento < $data);

                        if ($dataMenor) {
                            ComissoesCorretoresLancadas::on('tenant')
                                ->where('comissoes_id', $c->comissoes_id)
                                ->where('parcela', 1)
                                ->update(['data_baixa' => $dt_pagamento]);
                        }

                        if (date('m', strtotime($c->data)) == date('m', strtotime($vencimento))) {
                            ComissoesCorretoresLancadas::on('tenant')
                                ->find($c->id)
                                ->update([
                                    'status_financeiro' => 1,
                                    'data_baixa' => $dt_pagamento,
                                    'valor_pago' => $cells[10]->getValue()
                                ]);
                        }
                    }
                }
            }
        } else {
            $spreadsheetCode = $cells[5]->getValue();
            $carteirinha_existe = Cliente::on('tenant')
                ->select('clientes.*')
                ->join('users', 'users.id', '=', 'clientes.user_id')
                ->join('contratos', 'contratos.cliente_id', '=', 'clientes.id')
                ->whereRaw("LEFT(cateirinha, 11) = ?", [$spreadsheetCode])
                ->with(['user', 'contrato', 'contrato.comissao', 'contrato.comissao.comissoesLancadas'])
                ->first();

            if ($cells[11]->getValue() == "LIQUIDADO" || $cells[11]->getValue() == "LIQUIDADO N/COB") {
                if ($carteirinha_existe) {
                    $comissoes = $carteirinha_existe->contrato->comissao->comissoesLancadas;
                    $vencimento = $cells[13]->getValue()->format('Y-m-d');
                    $dt_pagamento = $cells[12]->getValue()->format('Y-m-d');

                    foreach ($comissoes as $c) {
                        $datasCadastradas = ComissoesCorretoresLancadas::on('tenant')
                            ->where('comissoes_id', $c->comissoes_id)
                            ->whereNotNull('data')
                            ->pluck('data');

                        $dataMenor = $datasCadastradas->every(fn($data) => $vencimento < $data);

                        if ($dataMenor) {
                            ComissoesCorretoresLancadas::on('tenant')
                                ->where('comissoes_id', $c->comissoes_id)
                                ->where('parcela', 1)
                                ->update(['data_baixa' => $dt_pagamento]);
                        }

                        if (date('m', strtotime($c->data)) == date('m', strtotime($vencimento))) {
                            ComissoesCorretoresLancadas::on('tenant')
                                ->find($c->id)
                                ->update([
                                    'status_financeiro' => 1,
                                    'data_baixa' => $dt_pagamento,
                                    'valor_pago' => $cells[10]->getValue()
                                ]);
                        }
                    }
                }
            }
        }
    }

    private function updateProgress($cacheKey, $processedLines, $completed = false)
    {
        try {
            $currentData = Cache::get($cacheKey, []);

            if ($completed) {
                $currentData['status'] = 'completed';
                $currentData['completed_at'] = now();
            }

            $currentData['processed_lines'] = $processedLines;
            $currentData['last_update'] = now();

            Cache::put($cacheKey, $currentData, 3600);

            Log::info("Progresso atualizado - Linhas: $processedLines");

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar progresso: " . $e->getMessage());
        }
    }

    private function countTotalLines()
    {
        try {
            if (!file_exists($this->filePath)) {
                Log::error("Arquivo não encontrado: {$this->filePath}");
                return 0;
            }

            $reader = ReaderEntityFactory::createReaderFromFile($this->filePath);
            $reader->open($this->filePath);

            $totalLines = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber <= 1) continue;
                    $totalLines++;
                }
            }

            $reader->close();
            Log::info("Linhas contadas: $totalLines");
            return $totalLines;

        } catch (\Exception $e) {
            Log::error("Erro ao contar linhas: " . $e->getMessage());
            return 0;
        }
    }

    private function verificarCarteirinha($nome, $valor)
    {
        $cliente = Cliente::on('tenant')
            ->select('clientes.*')
            ->join('users', 'users.id', '=', 'clientes.user_id')
            ->join('contratos', 'contratos.cliente_id', '=', 'clientes.id')
            ->where('clientes.nome', $nome)
            ->where('contratos.valor_adesao', $valor)
            ->first();

        return $cliente ? $cliente->id : null;
    }

    private function atualizarContrato()
    {
        $comissoes = DB::connection('tenant')->select("
               select * from comissoes_corretores_lancadas where
               comissoes_id IN(select id from comissoes where contrato_id IN(select id from contratos where plano_id = 1))
               and status_financeiro = 1
         ");

        foreach($comissoes as $cc) {
            switch ($cc->parcela) {
                case 2:
                    $contrato_id = Comissao::on('tenant')->where("id", $cc->comissoes_id)->first()->contrato_id;
                    Contrato::on('tenant')->where("id", $contrato_id)->update(["financeiro_id" => 6]);
                    break;
                case 3:
                    $contrato_id = Comissao::on('tenant')->where("id", $cc->comissoes_id)->first()->contrato_id;
                    Contrato::on('tenant')->where("id", $contrato_id)->update(["financeiro_id" => 7]);
                    break;
                case 4:
                    $contrato_id = Comissao::on('tenant')->where("id", $cc->comissoes_id)->first()->contrato_id;
                    Contrato::on('tenant')->where("id", $contrato_id)->update(["financeiro_id" => 8]);
                    break;
                case 5:
                    $contrato_id = Comissao::on('tenant')->where("id", $cc->comissoes_id)->first()->contrato_id;
                    Contrato::on('tenant')->where("id", $contrato_id)->update(["financeiro_id" => 9]);
                    break;
                case 6:
                    $contrato_id = Comissao::on('tenant')->where("id", $cc->comissoes_id)->first()->contrato_id;
                    Contrato::on('tenant')->where("id", $contrato_id)->update(["financeiro_id" => 11]);
                    break;
                default:
                    $contrato_id = Comissao::on('tenant')->where("id", $cc->comissoes_id)->first()->contrato_id;
                    Contrato::on('tenant')->where("id", $contrato_id)->update(["financeiro_id" => 5]);
                    break;
            }
        }
    }
}
