<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corretora_id')->nullable();
            $table->date('data');
            $table->unsignedBigInteger('plano_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('administradora_id');
            $table->unsignedBigInteger('tabela_origens_id');
            $table->unsignedBigInteger('contrato_id')->nullable();
            $table->unsignedBigInteger('contrato_empresarial_id')->nullable();
            $table->boolean('empresarial')->default(0);
            $table->timestamps();

            $table->foreign('corretora_id')->references('id')->on('corretoras')->cascadeOnDelete();
        });

        Schema::create('comissoes_corretores_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corretora_id')->nullable();
            $table->unsignedBigInteger('plano_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('administradora_id');
            $table->unsignedBigInteger('tabela_origens_id');
            $table->decimal('valor', 10, 2);
            $table->integer('parcela');
            $table->timestamps();

            $table->foreign('corretora_id')->references('id')->on('corretoras')->cascadeOnDelete();
        });

        Schema::create('comissoes_corretores_default', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plano_id');
            $table->unsignedBigInteger('administradora_id');
            $table->unsignedBigInteger('tabela_origens_id')->nullable();
            $table->decimal('valor', 8, 2);
            $table->integer('parcela');
            $table->unsignedBigInteger('corretora_id')->nullable();
            $table->timestamps();

            $table->foreign('corretora_id')->references('id')->on('corretoras')->cascadeOnDelete();
        });

        Schema::create('comissoes_corretores_lancadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comissoes_id');
            $table->integer('parcela');
            $table->date('data')->nullable();
            $table->decimal('valor', 10, 2);
            $table->boolean('incluir')->default(0);
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->decimal('valor_corretora', 10, 2)->nullable();
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
            $table->date('data_baixa_gerente_folha')->nullable();
            $table->date('data_baixa_finalizado')->nullable();
            $table->string('documento_gerador', 50)->nullable();
            $table->tinyInteger('estorno')->default(0);
            $table->date('data_baixa_estorno')->nullable();
            $table->tinyInteger('cancelados')->default(0);
            $table->tinyInteger('atual')->default(0);
            $table->tinyInteger('folha')->default(1);
            $table->boolean('manualmente')->default(0);
            $table->timestamps();

            $table->foreign('comissoes_id')->references('id')->on('comissoes')->cascadeOnDelete();
            $table->index(['status_gerente', 'data_baixa_gerente_folha'], 'idx_status_folha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes_corretores_lancadas');
        Schema::dropIfExists('comissoes_corretores_default');
        Schema::dropIfExists('comissoes_corretores_configuracoes');
        Schema::dropIfExists('comissoes');
    }
};
