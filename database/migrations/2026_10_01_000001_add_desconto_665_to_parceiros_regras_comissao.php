<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parceiros_regras_comissao', function (Blueprint $table) {
            // Opcao da backoffice: desconto de 6,65% (imposto) embutido na comissao
            // do parceiro para este plano (pedido para planos Individuais)
            $table->boolean('desconto_665')->default(0)->after('parcela_6_pct');
        });
    }

    public function down(): void
    {
        Schema::table('parceiros_regras_comissao', function (Blueprint $table) {
            $table->dropColumn('desconto_665');
        });
    }
};
