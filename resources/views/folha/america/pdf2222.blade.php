{{-- resources/views/folha/america/pdf.blade.php --}}
    <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folha de Pagamento - América</title>
    <style>
        * {margin: 0;padding: 0;box-sizing: border-box;}
        body {font-family: 'DejaVu Sans', Arial, sans-serif;font-size: 12px;line-height: 1.4;color: #333;}
        .header {background: linear-gradient(135deg, #3b82f6, #8b5cf6);color: white;padding: 20px;text-align: center;margin-bottom: 20px;}
        .header h1 {font-size: 24px;margin-bottom: 5px;color:black;}
        .header p {font-size: 14px;opacity: 0.9;color:black;}
        .info-section {background: #f8f9fa;padding: 15px;margin-bottom: 20px;border-left: 4px solid #3b82f6;}
        .info-grid {display: grid;grid-template-columns: 1fr 1fr 1fr;gap: 20px;margin-bottom: 20px;}
        .info-item {text-align: center;}
        .info-value {font-size: 18px;font-weight: bold;color: #3b82f6;}
        .info-label {font-size: 11px;color: #666;text-transform: uppercase;}
        .corretor-section {margin-bottom: 30px;page-break-inside: avoid;}
        .corretor-header {background: #e3f2fd;padding: 15px;border-left: 4px solid #2196f3;margin-bottom: 15px;}
        .corretor-name {font-size: 16px;font-weight: bold;color: #1976d2;}
        .corretor-total {font-size: 14px;color: #388e3c;font-weight: bold;}
        table {width: 100%;border-collapse: collapse;margin-bottom: 15px;}
        th {background: #f5f5f5;padding: 8px 6px;text-align: left;border: 1px solid #ddd;font-weight: bold;font-size: 10px;text-transform: uppercase;}
        td {padding: 6px;border: 1px solid #ddd;font-size: 10px;}
        tr:nth-child(even) {background: #f9f9f9;}
        .valor {text-align: right;font-weight: bold;color: #2e7d32;}
        .total-geral {background: #e8f5e8;padding: 15px;text-align: center;border: 2px solid #4caf50;margin-top: 20px;}
        .total-geral-valor {font-size: 24px;font-weight: bold;color: #2e7d32;}
        .footer {position: fixed;bottom: 20px;left: 0;right: 0;text-align: center;font-size: 10px;color: #666;border-top: 1px solid #ddd;padding-top: 10px;}
        .page-break {page-break-before: always;}
        @media print {
            .header {-webkit-print-color-adjust: exact;color-adjust: exact;}
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="header">
    <h1>📊 FOLHA DE PAGAMENTO - AMÉRICA</h1>
    <p>Período: {{ \Carbon\Carbon::parse($periodo['inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($periodo['fim'])->format('d/m/Y') }}</p>
    <p>Gerado em: {{ $dataGeracao->format('d/m/Y H:i:s') }}</p>
</div>

<!-- Resumo Geral -->
<div class="info-section">
    <h3 style="margin-bottom: 15px;">📈 Resumo Geral</h3>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-value">{{ count($dados) }}</div>
            <div class="info-label">Corretores</div>
        </div>
        <div class="info-item">
            <div class="info-value">R$ {{ number_format($totalGeral, 2, ',', '.') }}</div>
            <div class="info-label">Total Geral</div>
        </div>
        <div class="info-item">
            <div class="info-value">{{ \Carbon\Carbon::parse($periodo['inicio'])->diffInDays(\Carbon\Carbon::parse($periodo['fim'])) + 1 }}</div>
            <div class="info-label">Dias do Período</div>
        </div>
    </div>
</div>

<!-- Lista de Corretores -->
<div class="info-section">
    <h3 style="margin-bottom: 15px;">📋 Lista de Corretores</h3>
    <table>
        <thead>
        <tr>
            <th style="width: 30%;">Nome</th>
            <th style="width: 20%; text-align: right;">Valor Total (R$)</th>
            <th style="width: 25%; text-align: center;">Quantidade de Vidas</th>
            <th style="width: 25%; text-align: center;">Quantidade de Clientes</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dados as $corretor)
            <tr>
                <td>{{ $corretor['corretor']->name }}</td>
                <td style="text-align: right;">R$ {{ number_format($corretor['total'], 2, ',', '.') }}</td>
                <td style="text-align: center;">{{ $corretor['vidas'] }}</td>
                <td style="text-align: center;">{{ $corretor['comissoes']->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="page-break"></div>

<!-- Detalhes por Corretor -->
@foreach($dados as $index => $dadosCorretor)
    @if($index > 0)
        <div class="page-break"></div>
    @endif

    <div class="corretor-section">
        <div class="corretor-header">
            <div class="corretor-name">{{ $dadosCorretor['corretor']->name }}</div>
            <div class="corretor-total">Total: R$ {{ number_format($dadosCorretor['total'], 2, ',', '.') }}</div>
            <div class="corretor-total">Total Vidas: {{ $dadosCorretor['vidas'] }}</div>
            @if($dadosCorretor['corretor']->email)
                <div style="font-size: 12px; color: #666;">{{ $dadosCorretor['corretor']->email }}</div>
            @endif
        </div>

        @if(count($dadosCorretor['comissoes']) > 0)
            <table>
                <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 25%;">Cliente</th>
                    <th style="width: 12%;">CPF</th>
                    <th style="width: 15%;">Contrato</th>
                    <th style="width: 10%">Vidas</th>
                    <th style="width: 8%;">Parcela</th>
                    <th style="width: 10%;">% Comissão</th>
                    <th style="width: 12%;">Valor Base</th>
                    <th style="width: 12%;">Valor Comissão</th>
                    <th style="width: 6%;">Vencimento</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $totalCorretor = 0;
                    $totalVidas = 0;
                    $ii=0;
                @endphp
                @foreach($dadosCorretor['comissoes'] as $clienteId => $clienteComissoes)
                    @foreach($clienteComissoes as $comissao)
                        @php
                            $totalCorretor += $comissao->valor_comissao;
                            $totalVidas += $comissao->quantidade_vidas;
                            $ii++;
                        @endphp
                        <tr>
                            <td>{{$ii}}</td>
                            <td>{{ $comissao->cliente_nome }}</td>
                            <td>{{ substr($comissao->cliente_cpf, 0, 3) }}.***.***-{{ substr($comissao->cliente_cpf, -2) }}</td>
                            <td>{{ $comissao->contrato_codigo }}</td>
                            <td>{{ $comissao->quantidade_vidas }}</td>
                            <td style="text-align: center;">{{ $comissao->parcela }}ª</td>
                            <td style="text-align: center;">100%</td>
                            <td class="valor">R$ {{$comissao->valor_plano}}</td>
                            <td class="valor">R$ {{$comissao->valor}}</td>
                            <td style="text-align: center; font-size: 9px;">
                                {{ $comissao->data_vencimento ? \Carbon\Carbon::parse($comissao->data_vencimento)->format('d/m/Y') : '--' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                <!-- Linha de Total do Corretor -->
                <tr style="background: #e8f5e8; font-weight: bold;">
                    <td colspan="8" style="text-align: right; padding-right: 10px;">TOTAL DO CORRETOR:</td>
                    <td class="valor" style="font-size: 12px; color: #2e7d32;">R$ {{ number_format($totalCorretor, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                Nenhuma comissão encontrada para este corretor no período.
            </div>
        @endif
    </div>
@endforeach


<!-- Footer -->
<div class="footer">
    <p>📄 Documento gerado automaticamente pelo Sistema BmSyS</p>
    <p>🔒 Documento confidencial - Uso interno • Gerado em: {{ $dataGeracao->format('d/m/Y H:i:s') }}</p>
</div>
</body>
</html>
