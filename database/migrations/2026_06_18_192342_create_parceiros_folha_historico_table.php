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
        Schema::create('parceiros_folha_historico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('corretora_id');
            $table->string('frequencia', 20)->nullable();
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fim')->nullable();
            $table->date('data_pagamento');
            $table->integer('total_parcelas')->default(0);
            $table->decimal('total_valor', 10, 2)->default(0);
            $table->timestamps();
            $table->index(['user_id', 'corretora_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parceiros_folha_historico');
    }
};
