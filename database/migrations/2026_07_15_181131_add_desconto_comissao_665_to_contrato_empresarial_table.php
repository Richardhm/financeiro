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
        Schema::table('contrato_empresarial', function (Blueprint $table) {
            $table->boolean('desconto_comissao_665')->default(false)->after('desconto_operadora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato_empresarial', function (Blueprint $table) {
            $table->dropColumn('desconto_comissao_665');
        });
    }
};
