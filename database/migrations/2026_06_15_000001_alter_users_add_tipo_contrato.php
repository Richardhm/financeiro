<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo_contrato', ['clt', 'pj', 'parceiro'])->default('pj')->after('clt');
        });

        // Populate from existing clt boolean
        DB::statement("UPDATE users SET tipo_contrato = 'clt' WHERE clt = 1");
        DB::statement("UPDATE users SET tipo_contrato = 'pj'  WHERE clt = 0");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tipo_contrato');
        });
    }
};
