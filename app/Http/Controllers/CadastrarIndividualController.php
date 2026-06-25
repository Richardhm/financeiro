<?php

namespace App\Http\Controllers;

use App\Models\Acomodacao;
use App\Models\Cliente;
use App\Models\Comissoes;
use App\Models\ComissoesCorretoresConfiguracoes;
use App\Models\ComissoesCorretoresDefault;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\Contrato;
use App\Models\TabelaOrigens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CadastrarIndividualController extends Controller
{
    public function showForm()
    {
        $corretora_id = auth()->user()->corretora_id;
        $users       = User::where('ativo', 1)
            ->where('corretora_id', $corretora_id)
            ->where('id', '!=', 1)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->get();
        $acomodacoes = Acomodacao::orderBy('nome')->get();
        $tabelas     = TabelaOrigens::orderBy('nome')->get();

        return view('financeiro.cadastrar-individual', compact('users', 'acomodacoes', 'tabelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'       => 'required|exists:users,id',
            'nome'             => 'required|string|max:255',
            'cpf'              => 'required|string',
            'data_nascimento'  => 'required|date',
            'celular'          => 'required|string',
            'tabela_origem_id' => 'required|exists:tabela_origens,id',
            'acomodacao_id'    => 'required|exists:acomodacoes,id',
            'valor_plano'      => 'required|string',
            'data_adesao'      => 'required|date',
            'data_boleto'      => 'required|date',
            'qtd_parcelas'     => 'required|integer|min:1|max:24',
        ]);

        $corretora_id = auth()->user()->corretora_id;
        $user         = User::findOrFail($request->usuario_id);
        $valor_plano  = (float) str_replace(['.', ','], ['', '.'], $request->valor_plano);
        $valor_adesao = (float) str_replace(['.', ','], ['', '.'], $request->valor_adesao ?? '0');
        $qtd_parcelas = (int) $request->qtd_parcelas;

        DB::beginTransaction();
        try {
            $cliente = new Cliente();
            $cliente->corretora_id     = $corretora_id;
            $cliente->user_id          = $user->id;
            $cliente->nome             = $request->nome;
            $cliente->cpf              = preg_replace('/\D/', '', $request->cpf);
            $cliente->data_nascimento  = $request->data_nascimento;
            $cliente->celular          = preg_replace('/\D/', '', $request->celular ?? '');
            $cliente->telefone         = $request->telefone ?? null;
            $cliente->email            = $request->email ?? null;
            $cliente->cep              = preg_replace('/\D/', '', $request->cep ?? '');
            $cliente->rua              = $request->rua ?? null;
            $cliente->bairro           = $request->bairro ?? null;
            $cliente->complemento      = $request->complemento ?? null;
            $cliente->uf               = $request->uf ?? null;
            $cliente->cidade           = $request->cidade ?? null;
            $cliente->pessoa_fisica    = 1;
            $cliente->pessoa_juridica  = 0;
            $cliente->quantidade_vidas = (int) ($request->quantidade_vidas ?? 1);
            $cliente->dados            = 1;
            $cliente->save();

            $contrato = new Contrato();
            $contrato->cliente_id        = $cliente->id;
            $contrato->administradora_id = 4;
            $contrato->acomodacao_id     = $request->acomodacao_id;
            $contrato->tabela_origens_id = $request->tabela_origem_id;
            $contrato->plano_id          = 1;
            $contrato->financeiro_id     = 1;
            $contrato->coparticipacao    = $request->coparticipacao === 'sim' ? 1 : 0;
            $contrato->odonto            = $request->odonto === 'sim' ? 1 : 0;
            $contrato->codigo_externo    = $request->codigo_externo ?: null;
            $contrato->data_vigencia     = $request->data_adesao;
            $contrato->data_boleto       = $request->data_boleto;
            $contrato->data_baixa        = $request->data_baixa ?: null;
            $contrato->valor_adesao      = $valor_adesao;
            $contrato->valor_plano       = $valor_plano;
            $contrato->save();

            $comissao = new Comissoes();
            $comissao->contrato_id       = $contrato->id;
            $comissao->user_id           = $user->id;
            $comissao->corretora_id      = $corretora_id;
            $comissao->plano_id          = 1;
            $comissao->administradora_id = 4;
            $comissao->tabela_origens_id = $request->tabela_origem_id;
            $comissao->data              = now()->format('Y-m-d');
            $comissao->save();

            $this->lancarParcelas(
                $comissao, $user, $corretora_id,
                $valor_plano, $valor_adesao,
                $request->data_adesao,
                $request->data_boleto,
                $qtd_parcelas
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Erro ao cadastrar: ' . $e->getMessage()])
                ->withInput();
        }

        return redirect(route('financeiro.index'))
            ->with('success', 'Contrato individual cadastrado com sucesso!');
    }

    private function lancarParcelas(
        Comissoes $comissao,
        User $user,
        int $corretoraId,
        float $valorPlano,
        float $valorAdesao,
        string $dataAdesao,
        string $dataBoleto,
        int $qtdDefault
    ): void {
        if ($user->clt) {
            $config = ComissoesCorretoresDefault::where('plano_id', 1)
                ->where('administradora_id', 4)
                ->where('corretora_id', $corretoraId)
                ->orderBy('parcela')
                ->get();
        } else {
            $config = ComissoesCorretoresConfiguracoes::where('plano_id', 1)
                ->where('administradora_id', 4)
                ->where('user_id', $user->id)
                ->where('corretora_id', $corretoraId)
                ->orderBy('parcela')
                ->get();

            if ($config->isEmpty()) {
                $config = ComissoesCorretoresConfiguracoes::where('plano_id', 1)
                    ->where('administradora_id', 4)
                    ->whereNull('user_id')
                    ->where('corretora_id', $corretoraId)
                    ->orderBy('parcela')
                    ->get();
            }
        }

        $qtd = $config->isNotEmpty() ? $config->count() : $qtdDefault;

        for ($i = 0; $i < $qtd; $i++) {
            $lancada = new ComissoesCorretoresLancadas();
            $lancada->comissoes_id = $comissao->id;
            $lancada->parcela      = $i + 1;
            $lancada->manualmente  = 1;

            if ($i === 0) {
                $lancada->data              = $dataAdesao;
                $lancada->status_financeiro = 1;
                $lancada->data_baixa        = $dataAdesao;
                $lancada->valor_pago        = $valorAdesao;
            } else {
                $lancada->data = date('Y-m-d', strtotime($dataBoleto . ' +' . ($i - 1) . ' month'));
            }

            $lancada->valor = ($config->isNotEmpty() && isset($config[$i]))
                ? ($valorPlano * $config[$i]->valor) / 100
                : 0;

            $lancada->save();
        }
    }
}
