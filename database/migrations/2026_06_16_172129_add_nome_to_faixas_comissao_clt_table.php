<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faixas_comissao_clt', function (Blueprint $table) {
            $table->string('nome', 20)->nullable()->after('id');
        });

        // Preencher nome das faixas existentes em ordem de vidas_min
        $faixas = DB::table('faixas_comissao_clt')->orderBy('vidas_min')->pluck('id');
        foreach ($faixas as $index => $id) {
            DB::table('faixas_comissao_clt')
                ->where('id', $id)
                ->update(['nome' => 'Regra ' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::table('faixas_comissao_clt', function (Blueprint $table) {
            $table->dropColumn('nome');
        });
    }
};
