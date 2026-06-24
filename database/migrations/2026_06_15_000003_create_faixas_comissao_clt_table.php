<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faixas_comissao_clt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretora_id')->constrained('corretoras')->cascadeOnDelete();

            // Faixa por número de vidas no mês (null = sem limite superior)
            $table->integer('vidas_min')->default(0);
            $table->integer('vidas_max')->nullable();

            // Faixa por produção financeira no mês em R$ (null = sem limite superior)
            $table->decimal('producao_min', 10, 2)->default(0);
            $table->decimal('producao_max', 10, 2)->nullable();

            // Percentual aplicado sobre a comissão base quando a faixa é atingida
            $table->decimal('percentual', 5, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faixas_comissao_clt');
    }
};
