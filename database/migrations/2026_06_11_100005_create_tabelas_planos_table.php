<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acomodacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('administradoras', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->tinyInteger('empresarial')->default(0);
            $table->timestamps();
        });

        Schema::create('faixa_etarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('tabela_origens', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('uf', 2);
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        Schema::create('layouts', function (Blueprint $table) {
            $table->id();
            $table->string('imagem');
            $table->string('nome')->nullable();
            $table->timestamps();
        });

        Schema::create('concessionarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('imagem')->nullable();
            $table->integer('meta_individual')->default(0);
            $table->integer('individual')->default(0);
            $table->integer('meta_super_simples')->default(0);
            $table->integer('super_simples')->default(0);
            $table->integer('meta_pme')->default(0);
            $table->integer('pme')->default(0);
            $table->integer('meta_adesao')->default(0);
            $table->integer('adesao')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concessionarias');
        Schema::dropIfExists('layouts');
        Schema::dropIfExists('tabela_origens');
        Schema::dropIfExists('faixa_etarias');
        Schema::dropIfExists('planos');
        Schema::dropIfExists('administradoras');
        Schema::dropIfExists('acomodacoes');
    }
};
