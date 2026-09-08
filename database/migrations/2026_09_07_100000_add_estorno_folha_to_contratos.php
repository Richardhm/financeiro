<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('contratos', 'estorno_folha')) {
            Schema::table('contratos', function (Blueprint $table) {
                // Backoffice confirma se o estorno pendente entra na folha
                // (0 = fora da folha ate ser marcado explicitamente)
                $table->tinyInteger('estorno_folha')->default(0)->after('estorno');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contratos', 'estorno_folha')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->dropColumn('estorno_folha');
            });
        }
    }
};
