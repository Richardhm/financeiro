<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estornos', function (Blueprint $table) {
            $table->id();
            $table->string('lote', 50);
            $table->string('carteirinha', 30);
            $table->string('beneficiario')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('contrato_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('vendedor que recebeu/receberia a comissao');
            $table->decimal('valor', 10, 2)->default(0);
            $table->date('data_cancelamento')->nullable();
            $table->string('status', 20)->default('pendente')->comment('pendente | aplicado | sem_vinculo');
            $table->timestamp('data_aplicacao')->nullable();
            $table->string('folha_referencia')->nullable()->comment('folha em que o estorno foi descontado');
            $table->timestamps();

            // identificacao unica do evento de estorno: mesma remessa + mesmo beneficiario
            // nunca gera segundo desconto
            $table->unique(['lote', 'carteirinha']);
        });

        if (!Schema::hasColumn('parceiros_folha_historico', 'total_estorno')) {
            Schema::table('parceiros_folha_historico', function (Blueprint $table) {
                $table->decimal('total_estorno', 10, 2)->default(0)->after('total_vale');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estornos');
        if (Schema::hasColumn('parceiros_folha_historico', 'total_estorno')) {
            Schema::table('parceiros_folha_historico', function (Blueprint $table) {
                $table->dropColumn('total_estorno');
            });
        }
    }
};
