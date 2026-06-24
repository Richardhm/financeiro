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
        Schema::create('dependentes_empresariais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_empresarial_id');
            $table->string('cpf', 20)->nullable();
            $table->string('nome', 255);
            $table->char('tipo', 1)->default('T')->comment('T=Titular D=Dependente');
            $table->date('data_nascimento')->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('contrato_empresarial_id')
                ->references('id')->on('contrato_empresarial')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependentes_empresariais');
    }
};
