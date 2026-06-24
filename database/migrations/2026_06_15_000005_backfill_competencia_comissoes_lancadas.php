<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Priority 1: derive competencia from 'data' (parcela due date = vencimento)
        DB::statement("
            UPDATE comissoes_corretores_lancadas
            SET competencia = DATE_FORMAT(data, '%Y-%m')
            WHERE competencia IS NULL
              AND data IS NOT NULL
        ");

        // Priority 2: fallback to 'data_baixa' (actual payment date) when 'data' is null
        DB::statement("
            UPDATE comissoes_corretores_lancadas
            SET competencia = DATE_FORMAT(data_baixa, '%Y-%m')
            WHERE competencia IS NULL
              AND data IS NULL
              AND data_baixa IS NOT NULL
        ");
    }

    public function down(): void
    {
        // Intentionally left blank — we cannot safely distinguish
        // which competencia values were backfilled vs. written by the app.
    }
};
