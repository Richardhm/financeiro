<?php

namespace App\Http\Controllers;

use App\Models\Administradora;
use App\Models\Desconto;
use App\Models\Layout;
use App\Models\Pdf;
use App\Models\Plano;
use App\Models\Tabela;
use App\Models\TabelaOrigens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrcamentoController extends Controller
{

    public function empresarialIndex()
    {
        $estados = TabelaOrigens::groupBy('uf')->get(); // Estados disponíveis
        $administradoras = Administradora::all();       // Todas as Administradoras
        $planos = Plano::all();                         // Todos os Planos
        $ufpreferencia = auth()->user()->uf_preferencia ?? ''; // Preferência de UF do usuário

        return view('empresarial.index', compact('estados', 'administradoras', 'planos', 'ufpreferencia'));
    }



    /**
     * Processa a requisição e retorna os dados para os cards empresariais.
     */
    public function mostrarCenariosEmpresarial(Request $request)
    {
        // Inputs básicos recebidos pelo form
        $cidade = $request->input('tabela_origem');
        $plano = $request->input('plano');
        $operadora = $request->input('operadora');
        $faixasInput = $request->input('faixas')[0]; // Exemplo: [1 => 10, 2 => 5, ...]

        // Verifica se existem faixas preenchidas
        $sqlCase = '';
        $faixasIds = [];
        foreach ($faixasInput as $faixaId => $quantidade) {
            if ($quantidade > 0) {
                $sqlCase .= " WHEN tabelas.faixa_etaria_id = {$faixaId} THEN {$quantidade}";
                $faixasIds[] = $faixaId;
            }
        }

        if (empty($faixasIds)) {
            return response()->json(['message' => 'Nenhuma faixa etária válida fornecida.'], 422);
        }

        // Cenários para os cards
        $cenarios = [
            'com_copart_com_odonto' => ['label' => 'Com Copart - Com Odonto', 'copart' => 1, 'odonto' => 1],
            'sem_copart_com_odonto' => ['label' => 'Sem Copart - Com Odonto', 'copart' => 0, 'odonto' => 1],
            'com_copart_sem_odonto' => ['label' => 'Com Copart - Sem Odonto', 'copart' => 1, 'odonto' => 0],
            'sem_copart_sem_odonto' => ['label' => 'Sem Copart - Sem Odonto', 'copart' => 0, 'odonto' => 0],
        ];

        $resultados = [];

        foreach ($cenarios as $key => $cenario) {

            $dadosTabela = Tabela::select('tabelas.*')
                ->selectRaw("CASE {$sqlCase} END AS quantidade")
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where('tabelas.coparticipacao', $cenario['copart'])
                ->where('tabelas.odonto', $cenario['odonto'])
                ->where('tabelas.acomodacao_id', "!=", 3) // Ignora ambulatorial
                ->whereIn('tabelas.faixa_etaria_id', $faixasIds)
                ->get();

            // Agrupar os dados por faixa etária
            $dadosAgrupados = $this->agruparPorFaixaEtaria($dadosTabela);

            // Calcula os totais baseados no agrupamento
            $totais = $this->calcularTotais($dadosAgrupados);

            // Adiciona os resultados formatados para cada cenário
            $resultados[] = [
                'label' => $cenario['label'],
                'rows' => $totais['rows'],
                'copart' => $cenario['copart'],
                'odonto' => $cenario['odonto'],
                'total_apartamento' => $totais['total_apartamento'],
                'total_enfermaria' => $totais['total_enfermaria'],
            ];
        }

        // Retorna a view renderizada
        return view('empresarial.cards', ['resultados' => $resultados])->render();
    }

    /**
     * Agrupa os dados por faixa etária consolidando apartamento e enfermaria na mesma linha.
     */
    private function agruparPorFaixaEtaria($dadosTabela)
    {
        $dadosAgrupados = [];

        foreach ($dadosTabela as $dado) {
            $faixaId = $dado->faixa_etaria_id;

            if (!isset($dadosAgrupados[$faixaId])) {
                // Inicia o agrupamento para esta faixa
                $dadosAgrupados[$faixaId] = [
                    'faixa_etaria' => "Faixa {$faixaId}", // Ou outro formato de exibição para a faixa
                    'quantidade' => $dado->quantidade, // Quantidade de vidas
                    'valor_apartamento' => 0,
                    'valor_enfermaria' => 0,
                    'total_apartamento' => 0,
                    'total_enfermaria' => 0,
                ];
            }

            // Atualiza os valores de apartamento ou enfermaria
            if ($dado->acomodacao_id == 1) { // Apartamento
                $dadosAgrupados[$faixaId]['valor_apartamento'] = $dado->valor;
                $dadosAgrupados[$faixaId]['total_apartamento'] = $dado->valor * $dadosAgrupados[$faixaId]['quantidade'];
            } elseif ($dado->acomodacao_id == 2) { // Enfermaria
                $dadosAgrupados[$faixaId]['valor_enfermaria'] = $dado->valor;
                $dadosAgrupados[$faixaId]['total_enfermaria'] = $dado->valor * $dadosAgrupados[$faixaId]['quantidade'];
            }
        }

        return array_values($dadosAgrupados); // Retorna os dados como array simples
    }

    /**
     * Calcula os totais e organiza os dados para exibição nos cards.
     */
    private function calcularTotais($dadosAgrupados)
    {
        $resultado = [
            'rows' => [],
            'total_apartamento' => 0,
            'total_enfermaria' => 0,
        ];

        foreach ($dadosAgrupados as $dado) {
            $resultado['rows'][] = $dado; // Cada dado já está calculado com valores por faixa

            // Acumula os totais gerais
            $resultado['total_apartamento'] += $dado['total_apartamento'];
            $resultado['total_enfermaria'] += $dado['total_enfermaria'];
        }

        return $resultado;
    }

    /**
     * Calcula os totais e organiza os dados necessários para exibição nos cards.
     */
    private function calcularTotaissdfsdfsdf($dados)
    {
        $resultado = [
            'rows' => [],
            'total_apartamento' => 0,
            'total_enfermaria' => 0,
        ];

        foreach ($dados as $dado) {
            $quantidade = $dado->quantidade ?? 0; // Quantidade de vidas na faixa
            $valorApartamento = $dado->acomodacao_id == 1 ? $dado->valor : 0; // Apartamento
            $valorEnfermaria = $dado->acomodacao_id == 2 ? $dado->valor : 0; // Enfermaria

            // Adiciona a linha na tabela
            $resultado['rows'][] = [
                'faixa_etaria' => "Faixa {$dado->faixa_etaria_id}", // Exibe a faixa etária (ajuste o nome se necessário)
                'quantidade' => $quantidade,
                'valor_apartamento' => $valorApartamento,
                'valor_enfermaria' => $valorEnfermaria,
                'total_apartamento' => $valorApartamento * $quantidade,
                'total_enfermaria' => $valorEnfermaria * $quantidade,
            ];

            // Calcula os totais
            $resultado['total_apartamento'] += ($valorApartamento * $quantidade);
            $resultado['total_enfermaria'] += ($valorEnfermaria * $quantidade);
        }

        return $resultado;
    }








    /**
     * Calcula os totais e organiza os dados necessários para exibição nos cards.
     */
    private function calcularTotaisold($dados)
    {
        $resultado = [
            'rows' => [],
            'total_apartamento' => 0,
            'total_enfermaria' => 0,
        ];

        foreach ($dados as $dado) {
            $quantidade = $dado->quantidade ?? 0;
            $valorApartamento = $dado->acomodacao_id == 1 ? $dado->valor : 0; // Apartamento
            $valorEnfermaria = $dado->acomodacao_id == 2 ? $dado->valor : 0; // Enfermaria

            $resultado['rows'][] = [
                'faixa_etaria' => "{$dado->faixa_min} a {$dado->faixa_max}",
                'quantidade' => $quantidade,
                'valor_apartamento' => $valorApartamento,
                'valor_enfermaria' => $valorEnfermaria,
                'total_apartamento' => $valorApartamento * $quantidade,
                'total_enfermaria' => $valorEnfermaria * $quantidade,
            ];

            $resultado['total_apartamento'] += ($valorApartamento * $quantidade);
            $resultado['total_enfermaria'] += ($valorEnfermaria * $quantidade);
        }

        return $resultado;
    }



    public function index()
    {

        $estados = TabelaOrigens::groupBy("uf")->get();
        if(auth()->user()->corretora_id == 1) {
            $administradoras = Administradora::where("id","!=",5)->get();
        } else {
            $administradoras = Administradora::all();
        }
        $planos = Plano::all();
        $ufpreferencia = auth()->user()->uf_preferencia ?? '';

        return view('orcamento.index', compact('estados', 'administradoras','planos','ufpreferencia'));
    }

    public function getCidadesDeOrigem(Request $request)
    {
        $uf = $request->input('uf');
        $cidades = \DB::table('tabela_origens')
            ->where('uf', $uf)
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        return response()->json($cidades);
    }

    public function filtrarAdministradora(Request $request)
    {
        $cidade = $request->cidade;
        $administradoraIds = DB::table('tabelas')
            ->select('administradora_id')
            ->where('tabela_origens_id', $cidade)
            ->where("administradora_id","!=",5)
            ->where("administradora_id","!=",3)
            ->groupBy('administradora_id')
            ->pluck('administradora_id');
        $operadoras = Administradora::whereIn('id', $administradoraIds)
            //->where('cidade', $cidade)
            ->get();
        //$operadoras = Administradora::where('cidade', $cidade)->get();
        return response()->json($operadoras);
    }

    public function select(Request $request)
    {
        $user = auth()->user();
        $user->layout_id = $request->input('valor');

        if($user->save()) {
            return "sucesso";
        } else {
            return "error";
        }

    }




    public function buscar_planos(Request $request)
    {
        $administradora_id = $request->input('administradora_id');
        $tabela_origens_id = $request->input('tabela_origens_id');
        $planos = DB::table('administradora_planos')
            ->where('administradora_id', $administradora_id)
            ->where('tabela_origens_id', $tabela_origens_id)
            ->pluck('plano_id');
        return response()->json(['planos' => $planos]);
    }

    public function orcamento(Request $request)
    {
        $ambulatorial = $request->ambulatorial;
        $sql = "";
        $chaves = [];
        foreach(request()->faixas[0] as $k => $v) {
            if($v != null AND $v != 0) {
                $sql .= " WHEN tabelas.faixa_etaria_id = {$k} THEN ${v} ";
                $chaves[] = $k;
            }
        }
        $keys = implode(",",$chaves);
        $cidade = request()->tabela_origem;
        $plano = request()->plano;
        $operadora = request()->operadora;
        $imagem_operadora = Administradora::find($operadora)->logo;
        $plano_nome = Plano::find($plano)->nome;
        $imagem_plano = Administradora::find($operadora)->logo;
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        if($ambulatorial == 0) {
            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                //->where('acomodacao_id',"!=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->orderBy('tabelas.faixa_etaria_id')
                ->get();

            $desconto = Desconto::where("tabela_origens_id",$cidade)->where("plano_id",$plano)->where("administradora_id",$operadora)->count();
            $status_desconto = 0;
            if($desconto == 1) {
                $status_desconto = 1;
            }


                $status = $dados->contains('odonto', 0);
                return view("cotacao.cotacao2",[
                    "dados" => $dados,
                    "operadora" => $imagem_operadora,
                    "plano_nome" => $plano_nome,
                    "cidade_nome" => $cidade_nome,
                    "imagem_plano" => $imagem_plano,
                    "status" => $status,
                    "status_desconto" => $status_desconto
                ]);
        } else {
            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where('acomodacao_id',"=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->get();
            //return $dados;
            $status = $dados->contains('odonto', 0);

            $desconto = Desconto::where("tabela_origens_id",$cidade)->where("plano_id",$plano)->where("administradora_id",$operadora)->count();
            $status_desconto = 0;
            if($desconto == 1) {
                $status_desconto = 1;
            }

            return view("cotacao.cotacao-ambulatorial",[
                "dados" => $dados,
                "operadora" => $imagem_operadora,
                "plano_nome" => $plano_nome,
                "cidade_nome" => $cidade_nome,
                "imagem_plano" => $imagem_plano,
                "status" => $status,
                "status_desconto" => $status_desconto
            ]);
        }


    }

    public function getLayout(Request $request)
    {
        $user = Auth::user();
        $layouts = Layout::all();
        $estados = TabelaOrigens::groupBy('uf')->select('uf')->get();
        return view('orcamento.layouts',compact('layouts','user','estados'));
    }


    public function regiao(Request $request)
    {
        $user = Auth::user(); // ou auth()->user()
        $uf = $request->input('regiao');
        $user->uf_preferencia = $uf ?: null;
        if($user->save()) {
            return true;
        } else {
            return false;
        }
    }




}
