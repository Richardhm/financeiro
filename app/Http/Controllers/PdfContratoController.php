<?php

namespace App\Http\Controllers;

use App\Models\Acomodacao;
use App\Models\Administradora;
use App\Models\Cliente;
use App\Models\Comissoes;
use App\Models\ComissoesCorretoresConfiguracoes;
use App\Models\ComissoesCorretoresDefault;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\Contrato;
use App\Models\Dependente;
use App\Models\TabelaOrigens;
use App\Models\User;
use App\Services\PdfParser\AlterPdfParser;
use App\Services\PdfParser\AllcarePdfParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfContratoController extends Controller
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
        $administradoras = Administradora::whereRaw("id != (SELECT id FROM administradoras WHERE nome LIKE '%hapvida%')")->get();

        return view('financeiro.upload-pdf-coletivo', compact('users', 'cidades', 'administradoras'));
    }

    public function parsePdf(Request $request)
    {
        $request->validate([
            'pdf'           => 'required|file|mimes:pdf|max:20480',
            'administradora'=> 'required|in:alter,allcare',
        ]);

        $file = $request->file('pdf');
        $fullPath = $file->getRealPath();

        try {
            $parsed = $this->runParser($request->administradora, $fullPath);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao processar PDF: ' . $e->getMessage()], 422);
        }

        // Salva o PDF em local temporário para ser movido no store()
        $token   = Str::uuid()->toString();
        $tmpPath = 'propostas_pdf/tmp/' . $token . '.pdf';
        Storage::disk('local')->put($tmpPath, file_get_contents($fullPath));

        // Resolve vendedor pelo CPF
        $vendedor = $this->resolveVendedor($parsed['vendedor_cpf'], auth()->user()->corretora_id);

        // Resolve administradora_id
        $administradoraModel = $this->resolveAdministradora($request->administradora);

        // Resolve acomodacao_id
        $acomodacaoId = $this->resolveAcomodacao($parsed['plano']['acomodacao']);

        // Resolve tabela_origem_id pela cidade/UF extraída do PDF
        $origemResolvida = $this->resolveTabelaOrigem(
            $parsed['titular']['cidade'] ?? '',
            $parsed['titular']['uf']     ?? ''
        );

        return response()->json([
            'parsed'              => $parsed,
            'vendedor'            => $vendedor,
            'administradora_id'   => $administradoraModel?->id,
            'administradora_nome' => $administradoraModel?->nome,
            'acomodacao_id'       => $acomodacaoId,
            'tabela_origem_id'    => $origemResolvida['id'],
            'tabela_origem_nome'  => $origemResolvida['nome'],
            'pdf_token'           => $token,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'        => 'required|exists:users,id',
            'administradora_id' => 'required|exists:administradoras,id',
            'tabela_origem_id'  => 'required|exists:tabela_origens,id',
            'codigo_externo'    => 'required|string|max:50',
            'data_vigencia'     => 'required|date',
            'data_boleto'       => 'required|date',
            'valor_plano'       => 'required|numeric|min:0',
            'valor_adesao'      => 'required|numeric|min:0',
            'nome'              => 'required|string|max:255',
            'cpf'               => 'required|string|max:20',
            'data_nascimento'   => 'required|date',
            'acomodacao_id'     => 'required|exists:acomodacoes,id',
        ]);

        if (Contrato::where('codigo_externo', $request->codigo_externo)->exists()) {
            return response()->json(['error' => 'Contrato com este código já cadastrado.'], 422);
        }

        $user = User::findOrFail($request->usuario_id);
        $corretora_id = $user->corretora_id;

        $descontoCorretor  = (float) ($request->desconto_corretor  ?? 0);
        $descontoCorretora = (float) ($request->desconto_corretora ?? 0);
        $descontoOp        = $request->desconto_operadora  ? (float) $request->desconto_operadora  : null;
        $qtdParcelas       = $request->quantidade_parcelas ? (int)   $request->quantidade_parcelas : null;

        $numDeps = count(array_filter($request->dependentes_nomes ?? [], fn($n) => !empty($n)));

        DB::beginTransaction();
        try {
            $cliente = new Cliente();
            $cliente->user_id          = $user->id;
            $cliente->corretora_id     = $corretora_id;
            $cliente->nome             = $request->nome;
            $cliente->cpf              = $request->cpf;
            $cliente->data_nascimento  = $request->data_nascimento;
            $cliente->celular          = $request->celular  ?? '';
            $cliente->email            = $request->email    ?? '';
            $cliente->cep              = $request->cep      ?? '';
            $cliente->rua              = $request->rua      ?? '';
            $cliente->bairro           = $request->bairro   ?? '';
            $cliente->cidade           = $request->cidade   ?? '';
            $cliente->uf               = $request->uf       ?? '';
            $cliente->pessoa_fisica    = 1;
            $cliente->pessoa_juridica  = 0;
            $cliente->quantidade_vidas = 1 + $numDeps;
            if ($descontoOp !== null && $qtdParcelas >= 1) {
                $cliente->desconto_operadora  = $descontoOp;
                $cliente->quantidade_parcelas = $qtdParcelas;
            }
            $cliente->save();

            // Dependentes
            $nomes = $request->dependentes_nomes ?? [];
            $cpfs  = $request->dependentes_cpfs  ?? [];
            foreach ($nomes as $i => $nome) {
                if (!empty($nome)) {
                    $dep = new Dependente();
                    $dep->cliente_id = $cliente->id;
                    $dep->nome = $nome;
                    $dep->cpf  = $cpfs[$i] ?? '';
                    $dep->save();
                }
            }

            $contrato = new Contrato();
            $contrato->cliente_id        = $cliente->id;
            $contrato->administradora_id = $request->administradora_id;
            $contrato->acomodacao_id     = $request->acomodacao_id;
            $contrato->tabela_origens_id = $request->tabela_origem_id;
            $contrato->plano_id          = 3;
            $contrato->financeiro_id     = 1;
            $contrato->codigo_externo    = $request->codigo_externo;
            $contrato->data_vigencia     = $request->data_vigencia;
            $contrato->data_boleto       = $request->data_boleto;
            $contrato->valor_adesao      = (float) $request->valor_adesao;
            $contrato->valor_plano       = (float) $request->valor_plano;
            $contrato->coparticipacao    = $request->coparticipacao ? 1 : 0;
            $contrato->odonto            = $request->odonto ? 1 : 0;
            $contrato->desconto_corretor  = $descontoCorretor;
            $contrato->desconto_corretora = $descontoCorretora;

            // Move o PDF temporário para local permanente
            $pdfToken = $request->pdf_token ?? null;
            if ($pdfToken && preg_match('/^[a-f0-9\-]{36}$/i', $pdfToken)) {
                $tmpPath  = 'propostas_pdf/tmp/' . $pdfToken . '.pdf';
                $permPath = 'propostas_pdf/' . $request->codigo_externo . '_' . now()->format('Ymd') . '.pdf';
                if (Storage::disk('local')->exists($tmpPath)) {
                    Storage::disk('local')->move($tmpPath, $permPath);
                    $contrato->pdf_path = $permPath;
                }
            }

            $contrato->save();

            $comissao = new Comissoes();
            $comissao->contrato_id       = $contrato->id;
            $comissao->user_id           = $user->id;
            $comissao->plano_id          = 3;
            $comissao->administradora_id = $request->administradora_id;
            $comissao->tabela_origens_id = $request->tabela_origem_id;
            $comissao->data              = now()->format('Y-m-d');
            $comissao->corretora_id      = $corretora_id;
            $comissao->save();

            $this->lancarComissoesCorretor(
                $comissao, $user, $corretora_id,
                (float) $request->valor_plano,
                $request->data_vigencia,
                $descontoOp,
                $qtdParcelas
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'redirect' => route('financeiro.index') . '?ac=coletivo']);
    }

    public function downloadPdf(int $contratoId)
    {
        $contrato = Contrato::findOrFail($contratoId);

        // Garante que o usuário só baixa PDFs da sua corretora
        $cliente = $contrato->cliente;
        abort_if(
            $cliente->corretora_id !== auth()->user()->corretora_id && !auth()->user()->can('listar_todos'),
            403
        );

        abort_if(empty($contrato->pdf_path), 404, 'PDF não disponível para este contrato.');

        $fullPath = Storage::disk('local')->path($contrato->pdf_path);
        abort_if(!file_exists($fullPath), 404, 'Arquivo não encontrado.');

        $filename = 'proposta_' . $contrato->codigo_externo . '.pdf';
        return response()->download($fullPath, $filename, ['Content-Type' => 'application/pdf']);
    }

    // -----------------------------------------------------------------------

    private function runParser(string $type, string $path): array
    {
        return match($type) {
            'alter'   => (new AlterPdfParser())->parse($path),
            'allcare' => (new AllcarePdfParser())->parse($path),
        };
    }

    private function resolveVendedor(string $cpf, int $corretoraId): ?array
    {
        if (empty($cpf)) return null;
        $cpfDigits = preg_replace('/\D/', '', $cpf);
        $user = User::where('corretora_id', $corretoraId)
            ->whereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') = ?", [$cpfDigits])
            ->first();
        if ($user) {
            return ['id' => $user->id, 'name' => $user->name];
        }
        return null;
    }

    private function resolveAdministradora(string $tipo): ?Administradora
    {
        $keyword = $tipo === 'alter' ? 'alter' : 'allcare';
        return Administradora::whereRaw("LOWER(nome) LIKE ?", ["%{$keyword}%"])->first();
    }

    private function resolveAcomodacao(string $nome): ?int
    {
        $acomodacao = Acomodacao::whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower($nome) . '%'])->first();
        return $acomodacao?->id;
    }

    private function resolveTabelaOrigem(string $cidade, string $uf): array
    {
        // Mapeamento UF → id de fallback (caso a cidade não seja encontrada)
        $ufMap = [
            'GO' => 2,   // Goiânia
            'DF' => 8,   // Brasília
            'MT' => 4,   // Cuiabá
            'MS' => 7,   // Campo Grande
            'BA' => 10,  // Bahia
        ];
        $default = ['id' => 2, 'nome' => 'Goiânia'];

        // 1. Tenta busca exata ou parcial pelo nome da cidade
        if (!empty($cidade)) {
            // Normaliza: remove acentos para comparação insensível
            $cidadeLimpa = strtolower(trim($cidade));
            $row = DB::table('tabela_origens')
                ->whereRaw("LOWER(CONVERT(nome USING utf8mb4)) LIKE ?", ["%{$cidadeLimpa}%"])
                ->first();
            if ($row) {
                return ['id' => $row->id, 'nome' => $row->nome];
            }
        }

        // 2. Fallback por UF
        if (!empty($uf) && isset($ufMap[$uf])) {
            $row = DB::table('tabela_origens')->find($ufMap[$uf]);
            if ($row) {
                return ['id' => $row->id, 'nome' => $row->nome];
            }
        }

        // 3. Default Goiânia
        return $default;
    }

    private function lancarComissoesCorretor(
        Comissoes $comissao, User $user, int $corretoraId,
        float $valor, string $dataVigencia,
        ?float $descontoOp = null, ?int $qtdParcelas = null
    ): void {
        $contagem = 0;
        $ii = 0;
        $cd = 0;

        $calcValor = function(float $percentual, int $parcela) use ($valor, $descontoOp, $qtdParcelas): float {
            if ($descontoOp !== null && $qtdParcelas >= 1 && $parcela <= $qtdParcelas) {
                return ($valor * (1 - $descontoOp / 100)) * $percentual / 100;
            }
            return ($valor * $percentual) / 100;
        };

        if ($user->clt == 1) {
            $dados = ComissoesCorretoresDefault::where('plano_id', 3)
                ->where('corretora_id', $corretoraId)
                ->get();

            foreach ($dados as $c) {
                $cd++;
                $lancada = new ComissoesCorretoresLancadas();
                $lancada->comissoes_id = $comissao->id;
                $lancada->parcela      = $c->parcela;
                $lancada->data         = $contagem === 0
                    ? $dataVigencia
                    : date('Y-m-d', strtotime($dataVigencia . "+{$contagem}month"));
                $lancada->valor = $calcValor($c->valor, $cd);
                $lancada->save();
                $contagem++;
            }
        } else {
            $configuradas = ComissoesCorretoresConfiguracoes::where('plano_id', 3)
                ->where('user_id', $user->id)
                ->where('corretora_id', $corretoraId)
                ->get();

            if ($configuradas->isEmpty()) {
                $configuradas = ComissoesCorretoresConfiguracoes::where('plano_id', 3)
                    ->where('corretora_id', $corretoraId)
                    ->whereNull('user_id')
                    ->get();
            }

            foreach ($configuradas as $c) {
                $cd++;
                $lancada = new ComissoesCorretoresLancadas();
                $lancada->comissoes_id = $comissao->id;
                $lancada->parcela      = $c->parcela;
                $lancada->data         = $contagem === 0
                    ? $dataVigencia
                    : date('Y-m-d', strtotime($dataVigencia . "+{$ii}month"));
                if ($contagem > 0) $ii++;
                $lancada->valor = $calcValor($c->valor, $cd);
                $lancada->save();
                $contagem++;
            }
        }
    }
}
