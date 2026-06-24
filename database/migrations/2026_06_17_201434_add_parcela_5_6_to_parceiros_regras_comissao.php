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
        Schema::table('parceiros_regras_comissao', function (Blueprint $table) {
            $table->decimal('parcela_5_pct', 5, 2)->default(0)->after('parcela_4_pct');
            $table->decimal('parcela_6_pct', 5, 2)->default(0)->after('parcela_5_pct');
        });
    }

    public function down(): void
    {
        Schema::table('parceiros_regras_comissao', function (Blueprint $table) {
            $table->dropColumn(['parcela_5_pct', 'parcela_6_pct']);
        });
    }
};
