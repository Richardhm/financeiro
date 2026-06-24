<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('layout_id')->nullable()->default(1)->after('id');
            $table->string('uf_preferencia')->nullable()->after('layout_id');
            $table->unsignedBigInteger('corretora_id')->nullable()->after('uf_preferencia');
            $table->unsignedBigInteger('cargo_id')->nullable()->after('corretora_id');
            $table->string('codigo_vendedor', 50)->nullable()->after('cargo_id');
            $table->boolean('ranking')->default(0)->after('name');
            $table->string('cpf')->nullable()->after('ranking');
            $table->string('endereco')->nullable()->after('cpf');
            $table->string('cidade')->nullable()->after('endereco');
            $table->string('estado')->nullable()->after('cidade');
            $table->string('celular')->nullable()->after('estado');
            $table->string('numero')->nullable()->after('celular');
            $table->string('image')->nullable()->after('numero');
            $table->boolean('admin')->nullable()->after('image');
            $table->boolean('ativo')->default(1)->after('admin');
            $table->boolean('estagiario')->default(0)->after('ativo');
            $table->boolean('clt')->default(0)->after('estagiario');

            $table->foreign('corretora_id')->references('id')->on('corretoras')->cascadeOnDelete();
            $table->foreign('cargo_id')->references('id')->on('cargos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['corretora_id']);
            $table->dropForeign(['cargo_id']);
            $table->dropColumn([
                'layout_id','uf_preferencia','corretora_id','cargo_id','codigo_vendedor',
                'ranking','cpf','endereco','cidade','estado','celular','numero','image',
                'admin','ativo','estagiario','clt',
            ]);
        });
    }
};
