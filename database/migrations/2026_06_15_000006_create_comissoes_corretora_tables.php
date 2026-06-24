<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissoes_corretora_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretora_id')->constrained('corretoras')->cascadeOnDelete();
            $table->unsignedBigInteger('plano_id');
            $table->unsignedBigInteger('administradora_id');
            $table->unsignedBigInteger('tabela_origens_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // null = aplica a todos os vendedores
            $table->decimal('valor', 8, 2);                   // percentual
            $table->integer('parcela');
            $table->timestamps();
        });

        Schema::create('comissoes_corretora_lancadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comissoes_id')->constrained('comissoes')->cascadeOnDelete();
            $table->integer('parcela');
            $table->date('data')->nullable();
            $table->decimal('valor', 10, 2);
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('porcentagem_paga', 10, 2)->nullable();
            $table->tinyInteger('status_financeiro')->default(0);
            $table->tinyInteger('status_gerente')->default(0);
            $table->tinyInteger('status_apto_pagar')->default(0);
            $table->tinyInteger('status_comissao')->default(0);
            $table->tinyInteger('finalizado')->default(0);
            $table->date('data_antecipacao')->nullable();
            $table->date('data_baixa')->nullable();
            $table->date('data_baixa_gerente')->nullable();
            $table->date('data_baixa_finalizado')->nullable();
            $table->string('documento_gerador', 50)->nullable();
            $table->tinyInteger('estorno')->default(0);
            $table->date('data_baixa_estorno')->nullable();
            $table->tinyInteger('cancelados')->default(0);
            $table->tinyInteger('atual')->default(0);
            $table->tinyInteger('manualmente')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes_corretora_lancadas');
        Schema::dropIfExists('comissoes_corretora_configuracoes');
    }
};
