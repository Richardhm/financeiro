<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administradora_id')->constrained('administradoras')->cascadeOnDelete();
            $table->foreignId('tabela_origens_id')->constrained('tabela_origens')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->foreignId('acomodacao_id')->constrained('acomodacoes')->cascadeOnDelete();
            $table->foreignId('faixa_etaria_id')->constrained('faixa_etarias')->cascadeOnDelete();
            $table->boolean('coparticipacao')->default(0);
            $table->boolean('odonto')->default(0);
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });

        Schema::create('administradora_planos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->foreignId('administradora_id')->constrained('administradoras')->cascadeOnDelete();
            $table->foreignId('tabela_origens_id')->nullable()->constrained('tabela_origens')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('carencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->foreignId('tabela_origens_id')->constrained('tabela_origens')->cascadeOnDelete();
            $table->integer('tempo')->comment('Tempo da carência em dias');
            $table->text('detalhe')->nullable()->comment('Detalhamento da carência');
            $table->string('frase')->nullable()->comment('Frase informativa sobre a carência');
            $table->timestamps();
        });

        Schema::create('codigos', function (Blueprint $table) {
            $table->id();
            $table->boolean('odonto')->default(0);
            $table->foreignId('tabela_origens_id')->constrained('tabela_origens')->cascadeOnDelete();
            $table->foreignId('administradora_id')->constrained('administradoras')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->string('coparticipacao_enfermaria')->nullable();
            $table->string('coparticipacao_apartamento')->nullable();
            $table->string('parcial_enfermaria')->nullable();
            $table->string('parcial_apartamento')->nullable();
            $table->timestamps();
        });

        Schema::create('codigo_ambulatorial', function (Blueprint $table) {
            $table->id();
            $table->boolean('odonto')->default(0);
            $table->foreignId('tabela_origens_id')->constrained('tabela_origens')->cascadeOnDelete();
            $table->foreignId('administradora_id')->constrained('administradoras')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->string('coparticipacao')->nullable();
            $table->string('parcial')->nullable();
            $table->timestamps();
        });

        Schema::create('pdf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->foreignId('tabela_origens_id')->nullable()->constrained('tabela_origens')->nullOnDelete();
            $table->string('linha01')->nullable();
            $table->string('linha02')->nullable();
            $table->string('linha03')->nullable();
            $table->string('consultas_eletivas_total')->nullable();
            $table->string('consultas_de_urgencia_total')->nullable();
            $table->string('exames_simples_total')->nullable();
            $table->string('exames_complexos_total')->nullable();
            $table->string('terapias_especiais_total')->nullable();
            $table->string('demais_terapias_total')->nullable();
            $table->string('internacoes_total')->nullable();
            $table->string('cirurgia_total')->nullable();
            $table->string('consultas_eletivas_parcial')->nullable();
            $table->string('consultas_de_urgencia_parcial')->nullable();
            $table->string('exames_simples_parcial')->nullable();
            $table->string('exames_complexos_parcial')->nullable();
            $table->string('terapias_especiais_parcial')->nullable();
            $table->string('demais_terapias_parcial')->nullable();
            $table->string('internacoes_parcial')->nullable();
            $table->string('cirurgia_parcial')->nullable();
            $table->timestamps();
        });

        Schema::create('pdf_excecao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->foreignId('tabela_origens_id')->constrained('tabela_origens')->cascadeOnDelete();
            $table->string('linha01')->nullable();
            $table->string('linha02')->nullable();
            $table->string('linha03')->nullable();
            $table->string('consultas_eletivas_total')->nullable();
            $table->string('pronto_atendimento')->nullable();
            $table->string('faixa_1')->nullable();
            $table->string('faixa_2')->nullable();
            $table->string('faixa_3')->nullable();
            $table->string('faixa_4')->nullable();
            $table->timestamps();
        });

        Schema::create('desconto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabela_origens_id')->constrained('tabela_origens')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos')->cascadeOnDelete();
            $table->foreignId('administradora_id')->constrained('administradoras')->cascadeOnDelete();
            $table->decimal('valor', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desconto');
        Schema::dropIfExists('pdf_excecao');
        Schema::dropIfExists('pdf');
        Schema::dropIfExists('codigo_ambulatorial');
        Schema::dropIfExists('codigos');
        Schema::dropIfExists('carencias');
        Schema::dropIfExists('administradora_planos');
        Schema::dropIfExists('tabelas');
    }
};
