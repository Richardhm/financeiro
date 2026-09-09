<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/**
 * Area do vendedor: acompanhamento dos PROPRIOS clientes para cobranca.
 * O vendedor nunca ve clientes de outros vendedores — todas as consultas
 * filtram pelo usuario logado.
 *
 * Status e derivado em tempo real das parcelas (comissoes_corretores_lancadas):
 * a ultima parcela com status_financeiro=1 e a ultima paga pelo cliente.
 */
class VendedorClientesController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $individual = $this->contratosPorPlano($userId, 1);
        $coletivo   = $this->contratosPorPlano($userId, 3);
        $empresarial = $this->contratosEmpresariais($userId);

        $todos = $individual->concat($coletivo)->concat($empresarial);

        $resumo = [
            'total'       => $todos->count(),
            'atrasados'   => $todos->where('atrasado', true)->count(),
            'em_dia'      => $todos->where('atrasado', false)->whereNotIn('status_tipo', ['finalizado', 'cancelado'])->count(),
            'finalizados' => $todos->where('status_tipo', 'finalizado')->count(),
        ];

        return view('vendedor.meus-clientes', [
            'individual'  => $individual,
            'coletivo'    => $coletivo,
            'empresarial' => $empresarial,
            'resumo'      => $resumo,
        ]);
    }

    private function contratosPorPlano(int $userId, int $planoId)
    {
        $rows = DB::table('contratos as ct')
            ->join('clientes as cl', 'cl.id', '=', 'ct.cliente_id')
            ->where('cl.user_id', $userId)
            ->where('ct.plano_id', $planoId)
            ->select(
                'ct.id',
                'ct.created_at',
                'ct.codigo_externo',
                'ct.financeiro_id',
                'ct.valor_plano',
                'cl.nome',
                'cl.cpf',
                'cl.celular',
                'cl.quantidade_vidas',
                DB::raw("(SELECT MAX(ccl.parcela) FROM comissoes_corretores_lancadas ccl
                          JOIN comissoes c2 ON c2.id = ccl.comissoes_id
                          WHERE c2.contrato_id = ct.id AND ccl.status_financeiro = 1) as ultima_paga"),
                DB::raw("(SELECT COUNT(*) FROM comissoes_corretores_lancadas ccl
                          JOIN comissoes c2 ON c2.id = ccl.comissoes_id
                          WHERE c2.contrato_id = ct.id) as total_parcelas"),
                DB::raw("(SELECT MIN(ccl.data) FROM comissoes_corretores_lancadas ccl
                          JOIN comissoes c2 ON c2.id = ccl.comissoes_id
                          WHERE c2.contrato_id = ct.id AND ccl.status_financeiro = 0) as proximo_vencimento")
            )
            // Cadastro mais recente primeiro
            ->orderByDesc('ct.created_at')
            ->get();

        return $rows->map(fn($r) => $this->montarLinha($r, 'i'));
    }

    private function contratosEmpresariais(int $userId)
    {
        $rows = DB::table('contrato_empresarial as ce')
            ->where('ce.user_id', $userId)
            ->select(
                'ce.id',
                'ce.created_at',
                'ce.codigo_externo',
                'ce.financeiro_id',
                'ce.valor_plano',
                DB::raw('ce.razao_social as nome'),
                DB::raw('ce.cnpj as cpf'),
                'ce.celular',
                'ce.quantidade_vidas',
                DB::raw("(SELECT MAX(ccl.parcela) FROM comissoes_corretores_lancadas ccl
                          JOIN comissoes c2 ON c2.id = ccl.comissoes_id
                          WHERE c2.contrato_empresarial_id = ce.id AND ccl.status_financeiro = 1) as ultima_paga"),
                DB::raw("(SELECT COUNT(*) FROM comissoes_corretores_lancadas ccl
                          JOIN comissoes c2 ON c2.id = ccl.comissoes_id
                          WHERE c2.contrato_empresarial_id = ce.id) as total_parcelas"),
                DB::raw("(SELECT MIN(ccl.data) FROM comissoes_corretores_lancadas ccl
                          JOIN comissoes c2 ON c2.id = ccl.comissoes_id
                          WHERE c2.contrato_empresarial_id = ce.id AND ccl.status_financeiro = 0) as proximo_vencimento")
            )
            ->orderByDesc('ce.created_at')
            ->get();

        return $rows->map(fn($r) => $this->montarLinha($r, 'e'));
    }

    private function montarLinha($r, string $tipo): object
    {
        $ultimaPaga = (int) ($r->ultima_paga ?? 0);
        $totalParcelas = (int) ($r->total_parcelas ?? 0);
        $hoje = now()->toDateString();

        if ((int) $r->financeiro_id === 12) {
            $statusTipo = 'cancelado';
            $status = 'Cancelado';
        } elseif ($totalParcelas > 0 && $ultimaPaga >= $totalParcelas) {
            $statusTipo = 'finalizado';
            $status = 'Finalizado';
        } elseif ($ultimaPaga === 0) {
            $statusTipo = 'aguardando';
            $status = 'Aguardando 1ª Parcela';
        } else {
            $statusTipo = 'pagando';
            $status = "Pag. {$ultimaPaga}ª Parcela";
        }

        $atrasado = false;
        $diasAtraso = 0;
        if (!in_array($statusTipo, ['cancelado', 'finalizado']) && $r->proximo_vencimento && $r->proximo_vencimento < $hoje) {
            $atrasado = true;
            $diasAtraso = \Carbon\Carbon::parse($r->proximo_vencimento)->diffInDays($hoje);
        }

        return (object) [
            'id'             => $r->id,
            'tipo'           => $tipo,
            'data'           => \Carbon\Carbon::parse($r->created_at)->format('d/m/Y'),
            'data_ord'       => substr((string) $r->created_at, 0, 10),
            'codigo'         => $r->codigo_externo,
            'nome'           => $r->nome,
            'cpf'            => $r->cpf,
            'celular'        => $r->celular,
            'vidas'          => (int) ($r->quantidade_vidas ?: 1),
            'valor'          => (float) $r->valor_plano,
            'vencimento'     => $r->proximo_vencimento ? \Carbon\Carbon::parse($r->proximo_vencimento)->format('d/m/Y') : null,
            'status'         => $status,
            'status_tipo'    => $statusTipo,
            'atrasado'       => $atrasado,
            'dias_atraso'    => $diasAtraso,
        ];
    }

    /**
     * Parcelas do contrato para o modal — valida que o contrato pertence
     * ao vendedor logado (ou que o usuario e da gestao).
     */
    public function parcelas(string $tipo, int $id)
    {
        $userId = auth()->id();
        $isVendedor = (int) auth()->user()->cargo_id === 2;

        if ($tipo === 'e') {
            $dono = DB::table('contrato_empresarial')->where('id', $id)->value('user_id');
        } else {
            $dono = DB::table('contratos as ct')
                ->join('clientes as cl', 'cl.id', '=', 'ct.cliente_id')
                ->where('ct.id', $id)
                ->value('cl.user_id');
        }

        if ($dono === null || ($isVendedor && (int) $dono !== $userId)) {
            return response()->json(['success' => false, 'message' => 'Contrato não encontrado.'], 404);
        }

        $parcelas = DB::table('comissoes_corretores_lancadas as ccl')
            ->join('comissoes as c2', 'c2.id', '=', 'ccl.comissoes_id')
            ->when($tipo === 'e',
                fn($q) => $q->where('c2.contrato_empresarial_id', $id),
                fn($q) => $q->where('c2.contrato_id', $id))
            ->orderBy('ccl.parcela')
            ->select('ccl.parcela', 'ccl.data', 'ccl.valor_pago', 'ccl.status_financeiro', 'ccl.data_baixa')
            ->get()
            ->map(fn($p) => [
                'parcela'    => (int) $p->parcela,
                'rotulo'     => (int) $p->parcela === 1 ? 'Adesão' : $p->parcela . 'ª Parcela',
                'vencimento' => $p->data ? \Carbon\Carbon::parse($p->data)->format('d/m/Y') : '—',
                'baixa'      => $p->data_baixa ? \Carbon\Carbon::parse($p->data_baixa)->format('d/m/Y') : null,
                'pago'       => (int) $p->status_financeiro === 1,
                'atrasada'   => (int) $p->status_financeiro === 0 && $p->data && $p->data < now()->toDateString(),
            ]);

        return response()->json(['success' => true, 'parcelas' => $parcelas]);
    }
}
