<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Indices de performance — o banco herdado do grupoamerica veio so com as
 * PRIMARY KEYs (nenhum indice secundario), fazendo /folha varrer 47k+ linhas
 * em cada join. Criacao com guard (so cria se nao existir).
 */
return new class extends Migration
{
    private array $indices = [
        ['comissoes_corretores_lancadas', 'idx_ccl_comissoes',   ['comissoes_id']],
        ['comissoes_corretores_lancadas', 'idx_ccl_competencia', ['competencia']],
        ['comissoes_corretores_lancadas', 'idx_ccl_status',      ['status_financeiro', 'status_gerente', 'finalizado']],
        ['comissoes_corretores_lancadas', 'idx_ccl_finalizado',  ['finalizado', 'folha']],
        ['comissoes',            'idx_com_user',       ['user_id']],
        ['comissoes',            'idx_com_contrato',   ['contrato_id']],
        ['comissoes',            'idx_com_ce',         ['contrato_empresarial_id']],
        ['comissoes',            'idx_com_corretora',  ['corretora_id']],
        ['contratos',            'idx_ct_cliente',     ['cliente_id']],
        ['contratos',            'idx_ct_financeiro',  ['financeiro_id']],
        ['contratos',            'idx_ct_estorno',     ['estorno', 'data_baixa_estorno']],
        ['clientes',             'idx_cl_user',        ['user_id']],
        ['clientes',             'idx_cl_carteirinha', ['cateirinha']],
        ['contrato_empresarial', 'idx_ce_user',        ['user_id']],
        ['contrato_empresarial', 'idx_ce_financeiro',  ['financeiro_id']],
    ];

    public function up(): void
    {
        foreach ($this->indices as [$tabela, $nome, $colunas]) {
            $existe = DB::selectOne(
                "SELECT COUNT(*) as n FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [$tabela, $nome]
            );
            if ((int) $existe->n === 0) {
                $cols = implode('`,`', $colunas);
                DB::statement("ALTER TABLE `$tabela` ADD INDEX `$nome` (`$cols`)");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indices as [$tabela, $nome]) {
            $existe = DB::selectOne(
                "SELECT COUNT(*) as n FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [$tabela, $nome]
            );
            if ((int) $existe->n > 0) {
                DB::statement("ALTER TABLE `$tabela` DROP INDEX `$nome`");
            }
        }
    }
};
