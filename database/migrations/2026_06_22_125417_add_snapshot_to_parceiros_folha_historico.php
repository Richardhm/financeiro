<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parceiros_folha_historico', function (Blueprint $table) {
            $table->decimal('total_odonto', 10, 2)->default(0)->after('total_valor');
            $table->decimal('total_vale', 10, 2)->default(0)->after('total_odonto');
            $table->json('odonto_snapshot')->nullable()->after('total_vale');
        });
    }

    public function down(): void
    {
        Schema::table('parceiros_folha_historico', function (Blueprint $table) {
            $table->dropColumn(['total_odonto', 'total_vale', 'odonto_snapshot']);
        });
    }
};
