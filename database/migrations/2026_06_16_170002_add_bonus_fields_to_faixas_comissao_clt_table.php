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
            // Produção mínima que aciona o percentual bônus
            $table->decimal('producao_bonus', 10, 2)->nullable()->after('producao_max');
            // Percentual aplicado quando a produção ultrapassa producao_bonus
            $table->decimal('percentual_bonus', 5, 2)->nullable()->after('producao_bonus');
        });

        // Limpar dados incorretos e inserir as 5 faixas corretas
        DB::table('faixas_comissao_clt')->where('corretora_id', 1)->delete();

        $now = now();
        DB::table('faixas_comissao_clt')->insert([
            [
                'corretora_id'    => 1,
                'vidas_min'       => 0,
                'vidas_max'       => 10,
                'producao_min'    => 0,
                'producao_max'    => null,
                'percentual'      => 10.00,
                'producao_bonus'  => 3000.00,
                'percentual_bonus'=> 15.00,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'corretora_id'    => 1,
                'vidas_min'       => 11,
                'vidas_max'       => 20,
                'producao_min'    => 0,
                'producao_max'    => null,
                'percentual'      => 15.00,
                'producao_bonus'  => 4000.00,
                'percentual_bonus'=> 20.00,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'corretora_id'    => 1,
                'vidas_min'       => 21,
                'vidas_max'       => 30,
                'producao_min'    => 0,
                'producao_max'    => null,
                'percentual'      => 20.00,
                'producao_bonus'  => 6000.00,
                'percentual_bonus'=> 25.00,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'corretora_id'    => 1,
                'vidas_min'       => 31,
                'vidas_max'       => 35,
                'producao_min'    => 0,
                'producao_max'    => null,
                'percentual'      => 25.00,
                'producao_bonus'  => 7000.00,
                'percentual_bonus'=> 30.00,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'corretora_id'    => 1,
                'vidas_min'       => 36,
                'vidas_max'       => null,
                'producao_min'    => 0,
                'producao_max'    => null,
                'percentual'      => 30.00,
                'producao_bonus'  => 8500.00,
                'percentual_bonus'=> 35.00,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('faixas_comissao_clt', function (Blueprint $table) {
            $table->dropColumn(['producao_bonus', 'percentual_bonus']);
        });
    }
};
