<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comissoes_corretores_lancadas', function (Blueprint $table) {
            // '2026-06' format — month the commission belongs to (prevents mixing months in a payroll)
            $table->char('competencia', 7)->nullable()->after('comissoes_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('comissoes_corretores_lancadas', function (Blueprint $table) {
            $table->dropIndex(['competencia']);
            $table->dropColumn('competencia');
        });
    }
};
