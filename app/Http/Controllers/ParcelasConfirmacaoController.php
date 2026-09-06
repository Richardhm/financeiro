<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\Contrato;
use Illuminate\Http\Request;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParcelasConfirmacaoController extends Controller
{
    /**
     * Upload "Parcelas": confirmacao de pagamento da Operadora (status_gerente +
     * data_baixa_gerente), como o Adiantamento faz para a 1a parcela, mas para
     * as parcelas informadas na planilha (que pode misturar 2a, 3a, 4a...).
     *
     * Layout da planilha (0-based):
     *  [10] VENCIMENTO | [14] VL COMISSAO (pago a corretora) | [15] CD USUARIO
     *  (carteirinha + 3 digitos) | [17] PARCELA
     *
     * A mesma carteirinha+parcela pode vir em varias linhas (componentes do
     * pagamento, algumas com valor 0): o valor da corretora e a SOMA delas.
     */
    public function processarConfirmacaoParcela(Request $request)
    {
        set_time_limit(1000);
        try {
            $filename = uniqid() . ".xlsx";
        $filePath = \App\Support\PlanilhaUpload::receber($request->file("file") ?: $request->file, $filename);
            if (!is_readable($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao fazer upload do arquivo.'
                ], 400);
            }
            $reader = ReaderEntityFactory::createReaderFromFile($filePath);
            $reader->open($filePath);

            // Agrupa por carteirinha+parcela somando o valor pago a corretora
            $pares = [];
            $linhas = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber <= 1) {
                        continue;
                    }
                    $cells = $row->getCells();
                    if (count($cells) < 18) {
                        continue;
                    }

                    $carteirinha = trim((string) $cells[15]->getValue());
                    $parcela     = trim((string) $cells[17]->getValue());
                    $valor       = (float) str_replace(',', '.', (string) $cells[14]->getValue());

                    if (strlen($carteirinha) < 12 || !is_numeric($parcela)) {
                        continue;
                    }
                    $carteirinha = substr($carteirinha, 0, -3);
                    $linhas++;

                    $chave = $carteirinha . '|' . (int) $parcela;
                    if (!isset($pares[$chave])) {
                        $pares[$chave] = [
                            'carteirinha' => $carteirinha,
                            'parcela'     => (int) $parcela,
                            'valor'       => 0.0,
                        ];
                    }
                    $pares[$chave]['valor'] += $valor;
                }
                break;
            }
            $reader->close();
            @unlink($filePath);

            $confirmados        = 0;
            $jaConfirmados      = 0;
            $naoEncontrados     = 0;
            $valoresPreenchidos = 0;
            $erros              = [];

            foreach ($pares as $par) {
                try {
                    $cliente = Cliente::where('cateirinha', $par['carteirinha'])->first();
                    if (!$cliente) {
                        $naoEncontrados++;
                        continue;
                    }

                    $contrato = Contrato::where('cliente_id', $cliente->id)->orderByDesc('id')->first();
                    $comissaoId = $contrato
                        ? DB::table('comissoes')->where('contrato_id', $contrato->id)->value('id')
                        : null;
                    if (!$comissaoId) {
                        $naoEncontrados++;
                        $erros[] = "Carteirinha {$par['carteirinha']}: contrato/comissao nao encontrado";
                        continue;
                    }

                    $lancada = ComissoesCorretoresLancadas::where('comissoes_id', $comissaoId)
                        ->where('parcela', $par['parcela'])
                        ->first();
                    if (!$lancada) {
                        $naoEncontrados++;
                        $erros[] = "Carteirinha {$par['carteirinha']}: parcela {$par['parcela']} nao encontrada";
                        continue;
                    }

                    // Idempotente: parcela ja confirmada pela operadora nao muda de status.
                    // Mas se ela veio do sistema antigo SEM o valor da corretora registrado,
                    // preenche valor_corretora com o valor REAL da planilha (sem tocar em status).
                    if ((int) $lancada->status_gerente === 1) {
                        if ((float) $lancada->valor_corretora <= 0 && $par['valor'] > 0) {
                            $lancada->valor_corretora = round($par['valor'], 2);
                            $lancada->save();
                            $valoresPreenchidos++;
                            Log::info("valor_corretora preenchido retroativamente (upload Parcelas)", [
                                'ccl_id' => $lancada->id,
                                'valor'  => round($par['valor'], 2),
                            ]);
                        } else {
                            $jaConfirmados++;
                        }
                        continue;
                    }

                    $lancada->status_gerente     = 1;
                    $lancada->data_baixa_gerente = $lancada->data;
                    $lancada->valor_corretora    = round($par['valor'], 2);
                    $lancada->save();
                    $confirmados++;

                    Log::info("Parcela confirmada pela operadora (upload Parcelas)", [
                        'cliente_id'      => $cliente->id,
                        'parcela'         => $par['parcela'],
                        'comissao_id'     => $comissaoId,
                        'valor_corretora' => round($par['valor'], 2),
                    ]);
                } catch (\Exception $e) {
                    $erros[] = "Carteirinha {$par['carteirinha']} parcela {$par['parcela']}: {$e->getMessage()}";
                    Log::error("Erro ao confirmar parcela (upload Parcelas)", [
                        'carteirinha' => $par['carteirinha'],
                        'parcela'     => $par['parcela'],
                        'erro'        => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Processamento concluido! {$confirmados} parcelas confirmadas.",
                'detalhes' => [
                    'linhas'          => $linhas,
                    'pares'           => count($pares),
                    'confirmados'        => $confirmados,
                    'ja_confirmados'     => $jaConfirmados,
                    'valores_preenchidos'=> $valoresPreenchidos,
                    'nao_encontrados'    => $naoEncontrados,
                    'erros'           => count($erros),
                ],
                'erros' => array_slice($erros, 0, 20),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no processamento da confirmacao de parcela: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
