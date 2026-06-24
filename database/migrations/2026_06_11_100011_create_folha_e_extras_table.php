<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odonto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('nome');
            $table->decimal('valor', 10, 2);
            $table->boolean('pagou')->default(0);
            $table->decimal('comissao', 10, 2);
            $table->timestamps();
        });

        Schema::create('premiacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('valor', 10, 2)->default(0);
            $table->boolean('pago')->default(0);
            $table->timestamps();
        });

        Schema::create('fixo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('valor', 10, 2)->default(0);
            $table->boolean('pago')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('vale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('valor', 10, 2)->default(0);
            $table->boolean('pago')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('ranking_diario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('nome');
            $table->foreignId('corretora_id')->constrained('corretoras')->restrictOnDelete();
            $table->integer('vidas_individual')->default(0);
            $table->integer('vidas_coletivo')->default(0);
            $table->integer('vidas_empresarial')->default(0);
            $table->date('data');
            $table->timestamps();
        });

        Schema::create('valores_corretores_lancadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretora_id')->constrained('corretoras')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('data');
            $table->decimal('valor_comissao', 10, 2)->nullable();
            $table->decimal('valor_salario', 10, 2)->nullable();
            $table->decimal('valor_premiacao', 10, 2)->nullable();
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->decimal('valor_desconto', 10, 2)->nullable();
            $table->decimal('valor_estorno', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('folha_mes', function (Blueprint $table) {
            $table->id();
            $table->string('mes');
            $table->string('status');
            $table->foreignId('corretora_id')->constrained('corretoras')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('folha_pagamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folha_mes_id')->constrained('folha_mes')->cascadeOnDelete();
            $table->foreignId('valores_corretores_lancados_id')
                ->constrained('valores_corretores_lancadas')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folha_pagamento');
        Schema::dropIfExists('folha_mes');
        Schema::dropIfExists('valores_corretores_lancadas');
        Schema::dropIfExists('ranking_diario');
        Schema::dropIfExists('vale');
        Schema::dropIfExists('fixo');
        Schema::dropIfExists('premiacoes');
        Schema::dropIfExists('odonto');
    }
};
