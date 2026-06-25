<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faixas_comissao_clt', function (Blueprint $table) {
            // Produção mínima que aciona o percentual bônus
            $table->decimal('producao_bonus', 10, 2)->nullable()->after('producao_max');
            // Percentual aplicado quando a produção ultrapassa producao_bonus
            $table->decimal('percentual_bonus', 5, 2)->nullable()->after('producao_bonus');
        });

    }

    public function down(): void
    {
        Schema::table('faixas_comissao_clt', function (Blueprint $table) {
            $table->dropColumn(['producao_bonus', 'percentual_bonus']);
        });
    }
};
