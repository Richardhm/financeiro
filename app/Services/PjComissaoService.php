<?php

namespace App\Services;

use App\Models\ComissoesCorretoresLancadas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PjComissaoService
{
    // Cria sempre 6 parcelas com valor=0 (valores calculados pelo recalcularMes)
    public static function criarParcelas($comissao, string $dataVigencia): void
    {
        $base = Carbon::parse($dataVigencia);
        $now  = now();
        for ($i = 0; $i < 6; $i++) {
            $ccl = new ComissoesCorretoresLancadas();
            $ccl->comissoes_id = $comissao->id;
            $ccl->parcela      = $i + 1;
            $ccl->valor        = 0;
            $ccl->data         = $base->copy()->addMonths($i)->format('Y-m-d');
            $ccl->created_at   = $now;
            $ccl->updated_at   = $now;
            $ccl->save();
        }
    }

    /**
     * Conta vidas de contratos individuais por contratos.created_at do mês
     * e recalcula todas as comissões PJ daquele mês.
     *
     * @param  int    $userId     ID do vendedor(a) PJ
     * @param  string $mes        Formato YYYY-MM
     * @return array  Resumo da operação
     */
    public static function recalcularMes(int $userId, string $mes): array
    {
        // 1. Contar individuais por contratos.created_at
        $qtdIndividual = DB::table('comissoes as c')
            ->join('contratos as ct', 'ct.id', '=', 'c.contrato_id')
            ->where('c.user_id', $userId)
            ->where('c.plano_id', 1)
            ->whereRaw("DATE_FORMAT(ct.created_at, '%Y-%m') = ?", [$mes])
            ->count();

        // 2. Contar Super Simples (contrato_empresarial.created_at)
        $qtdEmpresarial = (int) DB::table('contrato_empresarial')
            ->where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$mes])
            ->sum('quantidade_vidas');

        // 3. Contar coletivo
        $qtdColetivo = DB::table('comissoes as c')
            ->join('contratos as ct', 'ct.id', '=', 'c.contrato_id')
            ->where('c.user_id', $userId)
            ->where('c.plano_id', 3)
            ->whereRaw("DATE_FORMAT(ct.created_at, '%Y-%m') = ?", [$mes])
            ->count();

        $qtdComissao = $qtdIndividual + $qtdEmpresarial;
        $qtdTotal    = $qtdIndividual + $qtdColetivo + $qtdEmpresarial;

        // 4. Atualizar tabela de controle
        DB::table('vidas_mes_vendedor')->updateOrInsert(
            ['user_id' => $userId, 'mes' => $mes],
            [
                'quantidade_individual'  => $qtdIndividual,
                'quantidade_coletivo'    => $qtdColetivo,
                'quantidade_empresarial' => $qtdEmpresarial,
                'quantidade_comissao'    => $qtdComissao,
                'quantidade_total'       => $qtdTotal,
                'updated_at'             => now(),
                'created_at'             => now(),
            ]
        );

        Log::info("PjComissaoService::recalcularMes user={$userId} mes={$mes}: individual={$qtdIndividual} empresarial={$qtdEmpresarial} coletivo={$qtdColetivo} comissao={$qtdComissao}");

        // 5. Encontrar a faixa
        $corretora_id = DB::table('users')->where('id', $userId)->value('corretora_id') ?? 1;
        $regra = DB::table('regras_comissao_pj')
            ->where('corretora_id', $corretora_id)
            ->where('vidas_min', '<=', $qtdComissao)
            ->where(function ($q) use ($qtdComissao) {
                $q->whereNull('vidas_max')->orWhere('vidas_max', '>=', $qtdComissao);
            })
            ->orderByDesc('vidas_min')
            ->first();

        if (!$regra) {
            Log::warning("PjComissaoService: nenhuma regra PJ para user={$userId} com {$qtdComissao} vidas.");
            return ['vidas' => $qtdComissao, 'regra' => null, 'contratos' => 0];
        }

        // 6. Buscar comissoes_id dos contratos individuais do mês
        $comissaoIds = DB::table('comissoes as c')
            ->join('contratos as ct', 'ct.id', '=', 'c.contrato_id')
            ->where('c.user_id', $userId)
            ->where('c.plano_id', 1)
            ->whereRaw("DATE_FORMAT(ct.created_at, '%Y-%m') = ?", [$mes])
            ->pluck('c.id');

        if ($comissaoIds->isEmpty()) {
            return ['vidas' => $qtdComissao, 'regra' => $regra->nome, 'contratos' => 0];
        }

        // 7. Buscar todas as parcelas desses contratos
        $todasParcelas = DB::table('comissoes_corretores_lancadas')
            ->whereIn('comissoes_id', $comissaoIds)
            ->get()
            ->groupBy('comissoes_id');

        foreach ($todasParcelas as $comissaoId => $parcelas) {
            // Base = maior valor_pago já confirmado, ou valor_plano do contrato
            $base = $parcelas->max('valor_pago');
            if (!$base || $base <= 0) {
                $base = (float) DB::table('comissoes as c')
                    ->join('contratos as ct', 'ct.id', '=', 'c.contrato_id')
                    ->where('c.id', $comissaoId)
                    ->value('ct.valor_plano');
            }
            if (!$base || $base <= 0) continue;

            // Zera parcelas 2-6
            $idsP26 = $parcelas->whereIn('parcela', [2, 3, 4, 5, 6])->pluck('id');
            if ($idsP26->isNotEmpty()) {
                DB::table('comissoes_corretores_lancadas')->whereIn('id', $idsP26)->update(['valor' => 0]);
            }

            // Aplica percentuais da faixa nas parcelas 2, 3, 4
            foreach ([2 => 'parcela_2_pct', 3 => 'parcela_3_pct', 4 => 'parcela_4_pct'] as $n => $campo) {
                $pct = (float) ($regra->$campo ?? 0);
                if ($pct <= 0) continue;
                $p = $parcelas->firstWhere('parcela', $n);
                if ($p) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p->id)
                        ->update(['valor' => round($base * $pct / 100, 2)]);
                }
            }
        }

        $qtdContratos = count($todasParcelas);
        Log::info("PjComissaoService: {$qtdContratos} contratos recalculados | faixa={$regra->nome} p2={$regra->parcela_2_pct}% p3={$regra->parcela_3_pct}% p4={$regra->parcela_4_pct}%");

        return ['vidas' => $qtdComissao, 'regra' => $regra->nome, 'contratos' => $qtdContratos];
    }
}
