<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regras_comissao_pj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretora_id')->constrained('corretoras')->cascadeOnDelete();
            $table->string('nome', 20)->nullable();
            $table->integer('vidas_min')->default(0);
            $table->integer('vidas_max')->nullable();
            $table->decimal('parcela_2_pct', 5, 2)->default(100);
            $table->decimal('parcela_3_pct', 5, 2)->default(0);
            $table->decimal('parcela_4_pct', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regras_comissao_pj');
    }
};
