<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parceiros_config_pagamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // semanal = toda semana no(s) dia(s) configurado(s)
            // quinzenal = dias 15 e 30 (ou configurado)
            // mensal = dia único por mês
            // personalizado = dias livres em dias_pagamento
            $table->enum('frequencia', ['semanal', 'quinzenal', 'mensal', 'personalizado'])->default('mensal');

            // Dias de pagamento: array de inteiros
            // semanal:      [5] = toda sexta (1=seg ... 7=dom, formato ISO)
            // quinzenal:    [15, 30]
            // mensal:       [10]
            // personalizado: qualquer combinação
            $table->json('dias_pagamento');

            $table->boolean('ativo')->default(1);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parceiros_config_pagamento');
    }
};
