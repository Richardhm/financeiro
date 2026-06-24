<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('corretora_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('nome')->nullable();
            $table->string('cidade')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('cpf')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('cep')->nullable();
            $table->string('rua')->nullable();
            $table->string('bairro')->nullable();
            $table->string('complemento')->nullable();
            $table->string('uf')->nullable();
            $table->string('cnpj')->nullable();
            $table->boolean('pessoa_fisica')->nullable();
            $table->boolean('pessoa_juridica')->nullable();
            $table->string('codigo_externo', 50)->nullable();
            $table->boolean('dependente')->nullable();
            $table->string('nm_plano')->nullable();
            $table->string('numero_registro_plano')->nullable();
            $table->string('rede_plano')->nullable();
            $table->string('tipo_acomodacao_plano')->nullable();
            $table->string('segmentacao_plano')->nullable();
            $table->string('cateirinha', 100)->nullable();
            $table->integer('quantidade_vidas')->nullable();
            $table->boolean('dados')->default(0);
            $table->date('baixa')->nullable();
            $table->string('desconto_operadora')->nullable();
            $table->integer('quantidade_parcelas')->nullable();
            $table->integer('dia')->nullable();
            $table->timestamps();

            $table->foreign('corretora_id')->references('id')->on('corretoras')->cascadeOnDelete();
        });

        Schema::create('dependentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nome');
            $table->string('cpf')->unique();
            $table->timestamps();
        });

        Schema::create('cliente_estagiario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('user_id');
            $table->date('data');
            $table->timestamps();
        });

        Schema::create('cidade_codigo_vendedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_tabela_origem');
            $table->string('codigo_vendedor');
            $table->unsignedBigInteger('tabela_origens_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cidade_codigo_vendedores');
        Schema::dropIfExists('cliente_estagiario');
        Schema::dropIfExists('dependentes');
        Schema::dropIfExists('clientes');
    }
};
