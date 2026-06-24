<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ConfirmacaoPagamentoController; // ⚡ ADICIONAR IMPORT

class ProcessAdiantamento extends Command
{
    protected $signature = 'adiantamento:process {file} {--job-id=}';
    protected $description = 'Processar adiantamento em background';

    public function handle()
    {
        $filePath = $this->argument('file');
        $jobId = $this->option('job-id');

        \Log::info("=== COMANDO ADIANTAMENTO INICIADO: $jobId ===");

        try {

            config(['database.connections.tenant.database' => 'bmsysc98_america_bmsys']);

            $controller = new ConfirmacaoPagamentoController();
            $controller->processarEmBackground($filePath, $jobId);

            $this->info("=== ADIANTAMENTO CONCLUﾃ好O ===");

        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            \Log::error("Erro no comando de adiantamento: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
