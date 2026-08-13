<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vidas_mes_vendedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('mes', 7);               // YYYY-MM
            $table->integer('quantidade_individual')->default(0);
            $table->integer('quantidade_coletivo')->default(0);
            $table->integer('quantidade_empresarial')->default(0); // Super Simples
            $table->integer('quantidade_comissao')->default(0);    // individual + empresarial
            $table->integer('quantidade_total')->default(0);       // individual + coletivo + empresarial
            $table->timestamps();
            $table->unique(['user_id', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vidas_mes_vendedor');
    }
};
