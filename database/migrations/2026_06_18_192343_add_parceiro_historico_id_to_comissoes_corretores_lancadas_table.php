<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comissoes_corretores_lancadas', function (Blueprint $table) {
            $table->unsignedBigInteger('parceiro_historico_id')->nullable()->after('updated_at');
            $table->index('parceiro_historico_id');
        });
    }

    public function down(): void
    {
        Schema::table('comissoes_corretores_lancadas', function (Blueprint $table) {
            $table->dropIndex(['parceiro_historico_id']);
            $table->dropColumn('parceiro_historico_id');
        });
    }
};
