<?php

namespace App\Services;

use App\Models\ParceirosRegraComissao;
use Illuminate\Support\Facades\DB;

class ParceirosComissaoService
{
    /**
     * Aplica as regras de comissão personalizadas para um parceiro independente.
     * Chamado imediatamente após status_financeiro = 1 ser setado (upload de planilha).
     */
    public static function aplicarRegra(int $parceiroId): void
    {
        $user = DB::table('users')
            ->where('id', $parceiroId)
            ->select('tipo_contrato', 'corretora_id')
            ->first();

        if (!$user || $user->tipo_contrato !== 'parceiro') return;

        $comissoes = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c', 'ccl.comissoes_id', '=', 'c.id')
            ->join('contratos as ct', 'c.contrato_id', '=', 'ct.id')
            ->where('c.user_id', $parceiroId)
            ->where('ccl.status_financeiro', 1)
            ->where('ccl.finalizado', '!=', 1)
            ->whereNull('ccl.data_baixa_gerente_folha')
            ->select('ccl.id', 'ccl.parcela', 'ccl.valor_pago', 'ccl.comissoes_id', 'c.plano_id', 'ct.valor_plano')
            ->get();

        if ($comissoes->isEmpty()) return;

        foreach ($comissoes->groupBy('comissoes_id') as $parcelas) {
            $planoId  = $parcelas->first()->plano_id;
            // Usa valor_plano do contrato (sem acrescimo) como base de cálculo
            $baseCalc = (float) ($parcelas->first()->valor_plano ?: ($parcelas->whereNotNull('valor_pago')->max('valor_pago') ?? 0));

            $regra = ParceirosRegraComissao::where('corretora_id', $user->corretora_id)
                ->where('parceiro_id', $parceiroId)
                ->where('plano_id', $planoId)
                ->first();

            if (!$regra) continue;

            DB::table('comissoes_corretores_lancadas')
                ->whereIn('id', $parcelas->pluck('id'))
                ->update(['valor' => 0]);

            if ($baseCalc <= 0) continue;

            foreach ([
                1 => $regra->parcela_1_pct,
                2 => $regra->parcela_2_pct,
                3 => $regra->parcela_3_pct,
                4 => $regra->parcela_4_pct,
                5 => $regra->parcela_5_pct,
                6 => $regra->parcela_6_pct,
            ] as $num => $pct) {
                $p = $parcelas->firstWhere('parcela', $num);
                if ($p) {
                    DB::table('comissoes_corretores_lancadas')
                        ->where('id', $p->id)
                        ->update([
                            'valor'            => round($baseCalc * (float) $pct / 100, 2),
                            'porcentagem_paga' => (float) $pct,
                        ]);
                }
            }
        }
    }
}
