<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parceiros_regras_comissao', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('corretora_id');
            $table->unsignedBigInteger('parceiro_id');
            $table->unsignedInteger('plano_id');
            $table->decimal('parcela_1_pct', 5, 2)->default(0);
            $table->decimal('parcela_2_pct', 5, 2)->default(0);
            $table->decimal('parcela_3_pct', 5, 2)->default(0);
            $table->decimal('parcela_4_pct', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['corretora_id', 'parceiro_id', 'plano_id'], 'unique_parceiro_plano');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parceiros_regras_comissao');
    }
};
