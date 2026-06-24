<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regras_comissao_pj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretora_id')->constrained('corretoras')->cascadeOnDelete();
            $table->string('nome', 20)->nullable();
            $table->integer('vidas_min')->default(0);
            $table->integer('vidas_max')->nullable();
            // Distribuição da comissão por parcela (% sobre a comissão base do contrato)
            $table->decimal('parcela_2_pct', 5, 2)->default(100); // sempre 100% Individual na 2ª
            $table->decimal('parcela_3_pct', 5, 2)->default(0);
            $table->decimal('parcela_4_pct', 5, 2)->default(0);
            $table->timestamps();
        });

        // Pre-popular as 5 faixas padrão
        $now = now();
        DB::table('regras_comissao_pj')->insert([
            ['corretora_id' => 1, 'nome' => 'Faixa 01', 'vidas_min' => 0,  'vidas_max' => 9,    'parcela_2_pct' => 100, 'parcela_3_pct' => 0,  'parcela_4_pct' => 0,  'created_at' => $now, 'updated_at' => $now],
            ['corretora_id' => 1, 'nome' => 'Faixa 02', 'vidas_min' => 10, 'vidas_max' => 15,   'parcela_2_pct' => 100, 'parcela_3_pct' => 10, 'parcela_4_pct' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['corretora_id' => 1, 'nome' => 'Faixa 03', 'vidas_min' => 16, 'vidas_max' => 24,   'parcela_2_pct' => 100, 'parcela_3_pct' => 15, 'parcela_4_pct' => 15, 'created_at' => $now, 'updated_at' => $now],
            ['corretora_id' => 1, 'nome' => 'Faixa 04', 'vidas_min' => 25, 'vidas_max' => 49,   'parcela_2_pct' => 100, 'parcela_3_pct' => 30, 'parcela_4_pct' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['corretora_id' => 1, 'nome' => 'Faixa 05', 'vidas_min' => 50, 'vidas_max' => null, 'parcela_2_pct' => 100, 'parcela_3_pct' => 50, 'parcela_4_pct' => 30, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regras_comissao_pj');
    }
};
