<?php

namespace App\Http\Controllers;

use App\Models\Comissoes;
use App\Models\ComissoesCorretoresConfiguracoes;
use App\Models\ComissoesCorretoresDefault;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\ContratoEmpresarial;
use App\Models\DependenteEmpresarial;
use App\Models\TabelaOrigens;
use App\Models\User;
use App\Services\PdfParser\HapvidaEmpresarialPdfParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfContratoEmpresarialController extends Controller
{
    public function showUpload()
    {
        $corretora_id = auth()->user()->corretora_id;
        $users = User::where('ativo', 1)
            ->where('corretora_id', $corretora_id)
            ->where('id', '!=', 1)
            ->orderBy('name')
            ->get();
        $cidades = TabelaOrigens::all();

        return view('financeiro.upload-pdf-empresarial', compact('users', 'cidades'));
    }

    public function parsePdf(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:30720',
        ]);

        $file     = $request->file('pdf');
        $fullPath = $file->getRealPath();

        try {
            $parsed = (new HapvidaEmpresarialPdfParser())->parse($fullPath);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao processar PDF: ' . $e->getMessage()], 422);
        }

        $token   = Str::uuid()->toString();
        $tmpPath = 'propostas_pdf_emp/tmp/' . $token . '.pdf';
        Storage::disk('local')->put($tmpPath, file_get_contents($fullPath));

        $corretora_id = auth()->user()->corretora_id;

        // Resolve vendedor: first by code (cidade_codigo_vendedores), then by name LIKE
        $vendedor = $this->resolveVendedor($parsed['codigo_vendedor'], $parsed['nome_vendedor'], $corretora_id);

        // Resolve tabela_origens by first city from area atuação
        $origem = $this->resolveTabelaOrigem($parsed['tabela_cidade']);

        return response()->json([
            'parsed'            => $parsed,
            'vendedor'          => $vendedor,
            'tabela_origem_id'  => $origem['id'],
            'tabela_origem_nome'=> $origem['nome'],
            'pdf_token'         => $token,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'        => 'required|exists:users,id',
            'tabela_origem_id'  => 'required|exists:tabela_origens,id',
            'cnpj'              => 'required|string',
            'razao_social'      => 'required|string',
            'responsavel'       => 'required|string',
            'data_vigencia'     => 'required|date',
            'data_boleto'       => 'required|date',
            'valor_plano_saude' => 'required|numeric|min:0',
            'valor_plano_odonto'=> 'nullable|numeric|min:0',
            'taxa_adesao'       => 'nullable|numeric|min:0',
            'valor_boleto'      => 'required|numeric|min:0',
        ]);

        $user         = User::findOrFail($request->usuario_id);
        $corretora_id = $user->corretora_id;

        $valor_saude  = (float) $request->valor_plano_saude;
        $valor_odonto = (float) $request->valor_plano_odonto;
        $valor_plano  = $valor_saude + $valor_odonto;
        $taxa_adesao  = (float) $request->taxa_adesao;
        $valor_total  = $valor_plano + $taxa_adesao;

        $desconto_op     = $request->desconto && (float)$request->desconto > 0 ? (float)$request->desconto : 0;
        $qtd_parcelas_dc = $desconto_op > 0 ? (int)($request->quantidade_parcelas ?? 0) : 0;
        $desconto_corretor = $request->desconto_corretor ? (float)$request->desconto_corretor : 0;

        DB::beginTransaction();
        try {
            $dados = [
                'plano_id'           => 5,
                'tabela_origens_id'  => $request->tabela_origem_id,
                'user_id'            => $user->id,
                'corretora_id'       => $corretora_id,
                'financeiro_id'      => 1,
                'cnpj'               => $request->cnpj,
                'razao_social'       => $request->razao_social,
                'responsavel'        => $request->responsavel,
                'celular'            => $request->celular ?? '',
                'email'              => $request->email ?? '',
                'cidade'             => $request->cidade ?? '',
                'uf'                 => $request->uf ?? '',
                'plano_contrado'     => $request->plano_contrado ?? 1,
                'quantidade_vidas'   => (int)($request->vidas ?? 0),
                'data_analise'       => now()->format('Y-m-d'),
                'data_baixa'         => $request->data_vigencia,
                'vencimento_boleto'  => $request->data_vigencia,
                'data_boleto'        => $request->data_boleto,
                'codigo_corretora'   => $request->codigo_corretora ?? '',
                'codigo_vendedor'    => $request->codigo_vendedor ?? '',
                'codigo_externo'     => $request->codigo_externo ?? '',
                'codigo_saude'       => $request->codigo_saude ?? '',
                'codigo_odonto'      => $request->codigo_odonto ?? '',
                'codigo_cliente'     => $request->codigo_cliente ?? null,
                'senha_cliente'      => $request->senha_cliente ?? '',
                'valor_plano_saude'  => $valor_saude,
                'valor_plano_odonto' => $valor_odonto,
                'valor_plano'        => $valor_plano,
                'taxa_adesao'        => $taxa_adesao,
                'valor_total'        => $valor_total,
                'valor_boleto'       => (float)$request->valor_boleto,
                'desconto_corretor'  => $desconto_corretor,
                'desconto_corretora' => 0,
                'desconto_operadora' => $desconto_op,
                'quantidade_parcelas'=> $qtd_parcelas_dc,
            ];

            // Move PDF to permanent storage
            $pdfToken = $request->pdf_token ?? null;
            if ($pdfToken && preg_match('/^[a-f0-9\-]{36}$/i', $pdfToken)) {
                $tmpPath  = 'propostas_pdf_emp/tmp/' . $pdfToken . '.pdf';
                $cnpjSlug = preg_replace('/\D/', '', $dados['cnpj']);
                $permPath = 'propostas_pdf_emp/' . $cnpjSlug . '_' . now()->format('Ymd') . '.pdf';
                if (Storage::disk('local')->exists($tmpPath)) {
                    Storage::disk('local')->move($tmpPath, $permPath);
                    $dados['pdf_path'] = $permPath;
                }
            }

            $contrato = ContratoEmpresarial::create($dados);

            // Beneficiários
            $bCpfs   = $request->input('benef_cpfs',   []);
            $bNomes  = $request->input('benef_nomes',  []);
            $bTipos  = $request->input('benef_tipos',  []);
            $bNascs  = $request->input('benef_nascs',  []);
            $bValors = $request->input('benef_valors', []);
            foreach ($bNomes as $i => $nome) {
                if (empty($nome)) continue;
                DependenteEmpresarial::create([
                    'contrato_empresarial_id' => $contrato->id,
                    'cpf'              => $bCpfs[$i]   ?? '',
                    'nome'             => $nome,
                    'tipo'             => $bTipos[$i]  ?? 'T',
                    'data_nascimento'  => $bNascs[$i]  ?? null,
                    'valor'            => isset($bValors[$i]) ? (float)str_replace(',','.', $bValors[$i]) : null,
                ]);
            }

            // Comissão
            $comissao = new Comissoes();
            $comissao->contrato_empresarial_id = $contrato->id;
            $comissao->user_id           = $user->id;
            $comissao->corretora_id      = $corretora_id;
            $comissao->plano_id          = 5;
            $comissao->administradora_id = 4;
            $comissao->tabela_origens_id = $request->tabela_origem_id;
            $comissao->data              = now()->format('Y-m-d');
            $comissao->empresarial       = 1;
            $comissao->save();

            // Parcelas
            $this->lancarComissoesCorretor(
                $comissao, $user, $corretora_id,
                $valor_plano,
                $request->data_boleto,
                $desconto_op,
                $qtd_parcelas_dc
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'redirect' => route('financeiro.index') . '?ac=empresarial']);
    }

    public function downloadPdf(int $id)
    {
        $contrato = ContratoEmpresarial::where('id', $id)
            ->where('corretora_id', auth()->user()->corretora_id)
            ->firstOrFail();

        if (empty($contrato->pdf_path) || !Storage::disk('local')->exists($contrato->pdf_path)) {
            abort(404, 'PDF não encontrado.');
        }

        $filename = 'proposta_' . preg_replace('/\D/', '', $contrato->cnpj) . '.pdf';
        return Storage::disk('local')->download($contrato->pdf_path, $filename);
    }

    // ─── Private helpers ──────────────────────────────────────

    private function resolveVendedor(string $code, string $nome, int $corretoraId): ?array
    {
        // Try by code in cidade_codigo_vendedores
        if (!empty($code)) {
            $row = DB::table('cidade_codigo_vendedores')
                ->where('codigo_vendedor', $code)
                ->first();
            if ($row) {
                $user = User::find($row->user_id);
                if ($user && $user->corretora_id === $corretoraId) {
                    return ['id' => $user->id, 'name' => $user->name];
                }
            }
        }

        // Try by name LIKE
        if (!empty($nome)) {
            $parts = array_filter(explode(' ', $nome));
            $query = User::where('corretora_id', $corretoraId);
            foreach (array_slice($parts, 0, 2) as $part) {
                if (strlen($part) > 2) {
                    $query->where('name', 'LIKE', '%' . $part . '%');
                }
            }
            $user = $query->first();
            if ($user) {
                return ['id' => $user->id, 'name' => $user->name];
            }
        }

        return null;
    }

    private function resolveTabelaOrigem(string $cidade): array
    {
        $default = ['id' => 2, 'nome' => 'Goiânia'];
        if (empty($cidade)) return $default;

        $row = DB::table('tabela_origens')
            ->whereRaw("LOWER(CONVERT(nome USING utf8mb4)) LIKE ?", ['%' . strtolower(trim($cidade)) . '%'])
            ->first();

        return $row ? ['id' => $row->id, 'nome' => $row->nome] : $default;
    }

    private function lancarComissoesCorretor(
        Comissoes $comissao, User $user, int $corretoraId,
        float $valor, string $dataBoleto,
        float $descontoOp = 0, int $qtdParcelas = 0
    ): void {
        $calcValor = function(float $pct, int $parcela) use ($valor, $descontoOp, $qtdParcelas): float {
            if ($descontoOp > 0 && $qtdParcelas >= 1 && $parcela <= $qtdParcelas) {
                return ($valor * (1 - $descontoOp / 100)) * $pct / 100;
            }
            return ($valor * $pct) / 100;
        };

        $contagem = 0;

        if ($user->clt == 1) {
            $dados = ComissoesCorretoresDefault::where('plano_id', 5)
                ->where('administradora_id', 4)
                ->where('corretora_id', $corretoraId)
                ->orderBy('parcela')
                ->get();

            foreach ($dados as $c) {
                $contagem++;
                $lancada = new ComissoesCorretoresLancadas();
                $lancada->comissoes_id = $comissao->id;
                $lancada->parcela      = $c->parcela;
                $lancada->data         = $contagem === 1
                    ? $dataBoleto
                    : date('Y-m-d', strtotime($dataBoleto . '+' . ($contagem - 1) . ' month'));
                $lancada->valor = $calcValor($c->valor, $contagem);
                $lancada->save();
            }
        } else {
            $configuradas = ComissoesCorretoresConfiguracoes::where('plano_id', 5)
                ->where('administradora_id', 4)
                ->where('user_id', $user->id)
                ->where('corretora_id', $corretoraId)
                ->orderBy('parcela')
                ->get();

            if ($configuradas->isEmpty()) {
                $configuradas = ComissoesCorretoresConfiguracoes::where('plano_id', 5)
                    ->where('administradora_id', 4)
                    ->where('corretora_id', $corretoraId)
                    ->whereNull('user_id')
                    ->orderBy('parcela')
                    ->get();
            }

            foreach ($configuradas as $c) {
                $contagem++;
                $lancada = new ComissoesCorretoresLancadas();
                $lancada->comissoes_id = $comissao->id;
                $lancada->parcela      = $c->parcela;
                $lancada->data         = $contagem === 1
                    ? $dataBoleto
                    : date('Y-m-d', strtotime($dataBoleto . '+' . ($contagem - 1) . ' month'));
                $lancada->valor = $calcValor($c->valor, $contagem);
                $lancada->save();
            }
        }
    }
}
