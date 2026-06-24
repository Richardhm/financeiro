<?php

namespace App\Http\Controllers;

use App\Models\CidadeCodigoVendedor;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Comissao;
use App\Models\ComissaoCorretorLancada;
use App\Models\User;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConfirmacaoPagamentoController extends Controller
{
    public function processarConfirmacaoPagamento(Request $request)
    {
        set_time_limit(1000);

        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);

            $filename = uniqid() . ".xlsx";
            if (!move_uploaded_file($request->file, $filename)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao fazer upload do arquivo.'
                ], 400);
            }

            $filePath = base_path("public/{$filename}");
            $reader = ReaderEntityFactory::createReaderFromFile($filePath);
            $reader->open($filePath);

            $processados = 0;
            $erros = [];
            $confirmados = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber <= 1) continue;

                    $cells = $row->getCells();

                    if (count($cells) < 11) continue;

                    try {
                        $resultado = $this->processarLinhaConfirmacao($cells, $rowNumber);
                        if ($resultado['success']) {
                            $confirmados++;
                        } else {
                            $erros[] = "Linha {$rowNumber}: {$resultado['message']}";
                        }

                        $processados++;

                    } catch (\Exception $e) {
                        $erros[] = "Linha {$rowNumber}: Erro inesperado - {$e->getMessage()}";
                        Log::error("Erro na linha {$rowNumber}: " . $e->getMessage());
                    }
                }
            }

            $reader->close();
            unlink($filePath);

            return response()->json([
                'success' => true,
                'message' => "Processamento concluído! {$confirmados} clientes confirmados de {$processados} processados.",
                'detalhes' => [
                    'processados' => $processados,
                    'confirmados' => $confirmados,
                    'erros' => count($erros)
                ],
                'erros' => $erros
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no processamento da confirmação: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processarLinhaConfirmacao($cells, $rowNumber)
    {
        $numeroDocumento = $cells[0]->getValue();
        $codVendedor = trim($cells[1]->getValue());
        $nomeVendedor = trim($cells[2]->getValue());
        $status = $cells[3]->getValue();
        $valorRecebidoCliente = $this->parseNumber($cells[4]->getValue());
        $valorAvulso = $this->parseNumber($cells[5]->getValue());
        $valorAdiantado = $this->parseNumber($cells[6]->getValue());
        $dataSolicitacao = $this->parseDate($cells[7]->getValue());
        $contrato = $cells[8]->getValue();
        $titular = trim($cells[9]->getValue());
        $possuiRepique = $cells[10]->getValue();

        Log::info("=== PROCESSANDO LINHA {$rowNumber} ===", [
            'cod_vendedor' => $codVendedor,
            'titular' => $titular,
            'valor_recebido' => $valorRecebidoCliente,
            'contrato' => $contrato
        ]);

        $cidadeCodigoVendedor = CidadeCodigoVendedor::where('codigo_vendedor', $codVendedor)->first();

        if (!$cidadeCodigoVendedor) {
            return [
                'success' => false,
                'message' => "Código vendedor {$codVendedor} não encontrado"
            ];
        }

        $vendedor = User::find($cidadeCodigoVendedor->user_id);

        if (!$vendedor) {
            return [
                'success' => false,
                'message' => "Vendedor não encontrado no sistema"
            ];
        }

        $cliente = $this->buscarClientePorCriterios($titular, $cidadeCodigoVendedor->user_id, $contrato, $valorRecebidoCliente);

        if (!$cliente) {
            return [
                'success' => false,
                'message' => "Cliente '{$titular}' não encontrado para o vendedor {$nomeVendedor}"
            ];
        }

        if (is_null($cliente->cateirinha) || empty($cliente->cateirinha)) {
            $alt = Cliente::find($cliente->cliente_id);
            $alt->cateirinha = $contrato;
            $alt->save();
            Log::info("cateirinha preenchida", [
                'cliente_id' => $cliente->id,
                'cateirinha' => $contrato
            ]);
        }

        $contratoCliente = $this->buscarClientePorCriterios($titular, $cidadeCodigoVendedor->user_id, $contrato, $valorRecebidoCliente);

        if (!$contratoCliente) {
            return [
                'success' => false,
                'message' => "Contrato não encontrado para o cliente {$titular} com valor {$valorRecebidoCliente} e cateirinha {$contrato}"
            ];
        }

        $comissao = Comissao::where('contrato_id', $contratoCliente->id)
            ->where('user_id', $cidadeCodigoVendedor->user_id)
            ->first();

        if (!$comissao) {
            return [
                'success' => false,
                'message' => "Comissão não encontrada para o contrato do cliente {$titular}"
            ];
        }

        $primeiraParcelaConfirmada = $this->confirmarPrimeiraParcela($comissao->id, $valorAdiantado);

        if (!$primeiraParcelaConfirmada) {
            return [
                'success' => false,
                'message' => "Não foi possível confirmar a 1ª parcela para o cliente {$titular}"
            ];
        }

        Log::info("=== CLIENTE PROCESSADO COM SUCESSO ===", [
            'cliente_id' => $cliente->id,
            'contrato_id' => $contratoCliente->id,
            'comissao_id' => $comissao->id,
            'cateirinha' => $cliente->cateirinha,
            'linha' => $rowNumber
        ]);

        return [
            'success' => true,
            'message' => "Cliente {$titular} - 1ª parcela confirmada com sucesso"
        ];
    }

    private function confirmarPrimeiraParcela($comissaoId, $valorAdiantamento)
    {
        try {
            $primeiraParcela = ComissaoCorretorLancada::where('comissoes_id', $comissaoId)
                ->where('parcela', 1)
                ->where('status_gerente', 0)
                ->first();

            if (!$primeiraParcela) {
                Log::warning("1ª parcela não encontrada ou já confirmada", [
                    'comissao_id' => $comissaoId
                ]);
                return false;
            }

            $primeiraParcela->status_gerente = 1;
            $primeiraParcela->data_baixa_gerente = $primeiraParcela->data;
            $primeiraParcela->valor_corretora = $valorAdiantamento;
            $primeiraParcela->save();

            Log::info("1ª parcela confirmada com sucesso", [
                'comissao_corretor_lancada_id' => $primeiraParcela->id,
                'comissao_id' => $comissaoId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Erro ao confirmar 1ª parcela", [
                'comissao_id' => $comissaoId,
                'erro' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function buscarClientePorCriterios($titular, $codigoVendedor, $cateirinha, $valorRecebido)
    {
        $contrato = Contrato
            ::join('clientes', 'contratos.cliente_id', '=', 'clientes.id')
            ->where('clientes.nome', 'LIKE', "%{$titular}%")
            ->where('clientes.cateirinha', $cateirinha)
            ->whereBetween('contratos.valor_plano', [$valorRecebido * 0.95, $valorRecebido * 1.05])
            ->selectRaw('contratos.*, clientes.cateirinha')
            ->first();

        if ($contrato) {
            Log::info("Contrato encontrado por valor exato + cateirinha", [
                'contrato_id' => $contrato->id,
                'valor_plano' => $contrato->valor_plano,
                'cateirinha' => $cateirinha
            ]);
            return $contrato;
        }

        $contrato = Contrato::join('clientes', 'contratos.cliente_id', '=', 'clientes.id')
            ->where('clientes.nome', 'like', "%{$titular}%")
            ->where('clientes.cateirinha', $cateirinha)
            ->selectRaw('contratos.*, clientes.cateirinha')
            ->first();

        if ($contrato) {
            Log::info("Contrato encontrado apenas por cateirinha", [
                'contrato_id' => $contrato->id,
                'valor_plano' => $contrato->valor_plano,
                'valor_buscado' => $valorRecebido,
                'cateirinha' => $cateirinha
            ]);
            return $contrato;
        }

        $contrato = Contrato
            ::join('clientes', 'contratos.cliente_id', '=', 'clientes.id')
            ->where('clientes.nome', 'like', "%{$titular}%")
            ->whereBetween('contratos.valor_plano', [$valorRecebido - 5, $valorRecebido + 5])
            ->where('clientes.cateirinha', $cateirinha)
            ->selectRaw('contratos.*, clientes.cateirinha')
            ->first();

        if ($contrato) {
            Log::info("Contrato encontrado com tolerância de valor + cateirinha", [
                'contrato_id' => $contrato->id,
                'valor_plano' => $contrato->valor_plano,
                'valor_buscado' => $valorRecebido,
                'cateirinha' => $cateirinha
            ]);
            return $contrato;
        }

        $contrato = Contrato
            ::join('clientes', 'clientes.id', '=', 'contratos.cliente_id')
            ->where('clientes.nome', 'like', "%{$titular}%")
            ->where('valor_plano', $valorRecebido)
            ->selectRaw('contratos.*, clientes.cateirinha')
            ->first();

        if ($contrato) {
            Log::info("Contrato encontrado por valor exato (fallback)", [
                'contrato_id' => $contrato->id,
                'valor_plano' => $contrato->valor_plano
            ]);
            return $contrato;
        }

        $contratos = Contrato
            ::join('clientes', 'contratos.cliente_id', '=', 'clientes.id')
            ->where('clientes.nome', 'like', "%{$titular}%")
            ->selectRaw('contratos.*, clientes.cateirinha')
            ->get();

        if ($contratos->count() == 1) {
            Log::info("Cliente tem apenas um contrato, retornando ele", [
                'contrato_id' => $contratos->first()->id,
                'valor_plano' => $contratos->first()->valor_plano,
                'valor_buscado' => $valorRecebido
            ]);
            return $contratos->first();
        }

        Log::warning("Nenhum contrato encontrado", [
            'titular' => $titular,
            'valor_buscado' => $valorRecebido,
            'cateirinha' => $cateirinha,
            'total_contratos' => $contratos->count()
        ]);

        return null;
    }

    private function compararNomesFlexivel($nomeBanco, $nomePlanilha)
    {
        $nomeBancoNormalizado = $this->normalizarNome($nomeBanco);
        $nomePlanilhaNormalizado = $this->normalizarNome($nomePlanilha);

        $similaridade = 0;
        similar_text($nomeBancoNormalizado, $nomePlanilhaNormalizado, $similaridade);

        return $similaridade >= 70;
    }

    private function normalizarNome($nome)
    {
        $nome = mb_strtoupper($nome, 'UTF-8');
        $nome = $this->removerAcentos($nome);
        $nome = preg_replace('/\s+/', ' ', trim($nome));

        return $nome;
    }

    private function removerAcentos($string)
    {
        $acentos = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];

        return strtr($string, $acentos);
    }

    private function parseNumber($value)
    {
        if (empty($value)) return 0;

        $value = str_replace([' ', ','], ['', '.'], $value);

        return floatval($value);
    }

    private function parseDate($value)
    {
        try {
            if (strpos($value, '/') !== false) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } elseif (strpos($value, '-') !== false) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            }
            return null;
        } catch (\Exception $e) {
            Log::warning("Erro ao converter data: {$value}");
            return null;
        }
    }
}
