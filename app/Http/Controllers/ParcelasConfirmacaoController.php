<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ComissoesCorretoresLancadas;
use App\Models\Contrato;
use Illuminate\Http\Request;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\Log;

class ParcelasConfirmacaoController extends Controller
{
    public function processarConfirmacaoParcela(Request $request)
    {
        set_time_limit(1000);
        try {
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
            $confirmados = 0;
            $erros = [];
            $clientesParaProcessar = [];
            $parcela = null;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    if ($rowNumber > 1) {
                        $cells = $row->getCells();
                        try {
                            $carteirinha = trim($cells[15]->getValue());
                            $parcela = trim($cells[17]->getValue());
                            $carteirinha = substr($carteirinha, 0, -3);

                            $cliente = Cliente::where('cateirinha', $carteirinha)->first();
                            if ($cliente) {
                                $clientesParaProcessar[] = [
                                    'cliente_id' => $cliente->id,
                                    'parcela' => $parcela
                                ];
                                $processados++;
                            } else {
                                $erros[] = "Linha {$rowNumber}: Cliente não encontrado (Carteirinha: {$carteirinha})";
                            }
                        } catch (\Exception $e) {
                            $erros[] = "Linha {$rowNumber}: Erro - {$e->getMessage()}";
                        }
                    }
                }
            }
            $reader->close();
            unlink($filePath);

            if (!empty($clientesParaProcessar)) {
                $resultado = $this->confirmarParcela($clientesParaProcessar);
                $confirmados = $resultado['processados'];
                $erros = array_merge($erros, $resultado['erros']);
            }

            return response()->json([
                'success' => true,
                'message' => "Processamento da {$parcela}ª parcela concluído! {$confirmados} clientes confirmados de {$processados} processados.",
                'detalhes' => [
                    'parcela' => $parcela,
                    'processados' => $processados,
                    'confirmados' => $confirmados,
                    'erros' => count($erros)
                ],
                'erros' => $erros
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no processamento da confirmação de parcela: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    private function confirmarParcela($dados)
    {
        $processados = 0;
        $erros = [];

        foreach ($dados as $d) {
            $cliente_id = $d['cliente_id'];
            $parcela = $d['parcela'];
            try {
                $contrato = Contrato::where('cliente_id', $cliente_id)->with(['comissao'])->first();
                if ($contrato) {
                    $lancada = ComissoesCorretoresLancadas::where('comissoes_id', $contrato->comissao->id)
                        ->where('parcela', $parcela)
                        ->first();
                    if ($lancada) {
                        $lancada->status_gerente = 1;
                        $lancada->data_baixa_gerente = $lancada->data;
                        $lancada->save();
                        $processados++;
                        Log::info("Parcela confirmada", [
                            'cliente_id' => $cliente_id,
                            'parcela' => $parcela,
                            'comissao_id' => $contrato->comissao->id
                        ]);
                    }
                } else {
                    $erros[] = "Cliente ID {$cliente_id} não encontrado";
                }
            } catch (\Exception $e) {
                Log::error("Erro ao confirmar parcela", [
                    'cliente_id' => $cliente_id,
                    'parcela' => $parcela,
                    'erro' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return [
            'processados' => $processados,
            'erros' => $erros
        ];
    }
}
