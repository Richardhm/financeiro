<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folha de Pagamento - América</title>

    <style>
        /* Estilos base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.5;
            font-size: 12px;
            color: #333;
        }

        /* Cabeçalho */
        .header {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            text-align: center;
            padding: 20px;
            border-bottom: 3px solid #1e3a8a;
        }
        .header h1 {
            font-size: 24px;
            color: black;
        }
        .header p {
            font-size: 14px;
            color: black;
        }

        /* Resumo geral */
        .info-section {
            padding: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }
        h3 {
            font-size: 14px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            font-size: 10px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .table-detalhes th,
        .table-detalhes td {
            font-size: 8px;
        }

        /* Tabelas com destaque */
        .valor {
            font-weight: bold;
            color: #2e7d32;
        }

        /* Seções do corretor */
        .corretor-header {
            background: #e3f2fd;
            padding: 10px;
            border-left: 4px solid #2196f3;
            margin-bottom: 10px;
        }
        .corretor-header h3 {
            font-size: 14px;
            color: #1976d2;
        }

        .corretor-section {
            margin-bottom: 30px;
        }

        /* Badges de cor */
        .badge {
            display: inline-block;
            font-size: 10px;
            color: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-individual { background: #1976d2; }
        .badge-coletivo { background: #b02a37; }
        .badge-empresarial { background: #2e7d32; }
        .badge-odonto { background: #8b5cf6; }
        .badge-estorno { background: #b71c1c; }

        /* Quebras de página */
        .page-break {
            page-break-before: always; /* Adiciona quebra antes da seção */
        }

        /* Rodapé fixo */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding: 10px;
            background: white;
        }
    </style>
</head>
<body>
<!-- Cabeçalho -->
<div class="header">
    <h1>Folha de Pagamento - América</h1>


</div>

<!-- Resumo Geral -->
<div class="info-section">
    <h3>📈 Resumo por Corretor</h3>
    <table>
        <thead>
        <tr>
            <th>Corretor</th>
            <th>Comissão</th>
            <th>Vidas</th>
            <th>Contratos</th>
            <th>Média por Vida</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dados as $corretor)
            <tr>
                <td>{{ $corretor['corretor']->name }}</td>
                <td class="valor">R$ {{ number_format($corretor['total'], 2, ',', '.') }}</td>
                <td>{{ $corretor['vidas'] }}</td>
                <td>{{ $corretor['comissoes']->unique('contrato_codigo')->count() }}</td>
                <td>
                    R$ {{ number_format($corretor['vidas'] > 0 ? $corretor['total'] / $corretor['vidas'] : 0, 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Quebra de página para isolar o resumo -->
<div class="page-break"></div>

<!-- Detalhes por Corretor -->
@foreach($dados as $index => $dadosCorretor)
    <div @if($index > 0) class="page-break" @endif>
        <div class="corretor-section">
            <div class="corretor-header">
                <h3>Corretor: {{ $dadosCorretor['corretor']->name }}</h3>
            </div>

            <!-- Estatísticas gerais -->
            <table>
                <thead>
                <tr>
                    <th>Total Comissão</th>
                    <th>Total Vidas</th>
                    <th>Contratos</th>
                    <th>Individual</th>
                    <th>Coletivo</th>
                    <th>Empresarial</th>
                    <th>Odonto</th>
                    @if(!$dadosCorretor['is_parceiro'])
                    <th>Premiação</th>
                    <th>Fixo</th>
                    @endif
                    <th>Vale</th>
                    <th>Estorno</th>
                    <th>Desconto</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="valor">R$ {{ number_format($dadosCorretor['total'], 2, ',', '.') }}</td>
                    <td>{{ $dadosCorretor['vidas'] }}</td>
                    <td>{{ $dadosCorretor['contratos'] }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['individual'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['coletivo'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['empresarial'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['odonto'], 2, ',', '.') }}</td>
                    @if(!$dadosCorretor['is_parceiro'])
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['premiacao'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['fixo'], 2, ',', '.') }}</td>
                    @endif
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['vale'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['estorno'], 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($dadosCorretor['totais_tipos']['desconto'], 2, ',', '.') }}</td>
                </tr>
                </tbody>
            </table>

            <!-- Comissões detalhadas -->
            <table class="table-detalhes">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Cliente/Razão Social</th>
                    <th>CPF/CNPJ</th>
                    <th>Contrato</th>
                    <th>Vidas</th>
                    <th>Parcela</th>
                    <th>Valor Base</th>
                    <th>Desconto</th> <!-- Nova coluna -->
                    <th>Comissão</th>
                    <th>Vencimento</th>
                </tr>
                </thead>
                <tbody>
                @php $indexComissao = 1; @endphp
                @foreach($dadosCorretor['comissoes'] as $tipo => $comissoes)
                    <tr style="
        font-weight: bold;
        color: white;
        @switch(strtoupper($tipo))
            @case('INDIVIDUAL')
                background: #1976d2;
                @break
            @case('COLETIVO')
                background: #b02a37;
                @break
            @case('EMPRESARIAL')
                background: #2e7d32;
                @break
            @case('ODONTO')
                background: #8b5cf6;
                @break
            @case('ESTORNO')
                background: #b71c1c;
                @break
            @default
                background: #333; /* Caso não seja nenhum dos tipos mencionados */
        @endswitch
    ">
                        <td colspan="11">{{ strtoupper($tipo) }}</td>
                    </tr>
                    @foreach($comissoes as $comissao)
                        <tr>
                            <td>{{ $indexComissao++ }}</td>
                            <td>
                                <span class="badge badge-{{ $tipo }}">{{ strtoupper($tipo) }}</span>
                            </td>
                            <td>{{ $comissao->cliente_nome }}</td>
                            <td>{{ $comissao->cpf ?? '--' }}</td>
                            <td>{{ $comissao->contrato_codigo ?? '--' }}</td>
                            <td>{{ $comissao->quantidade_vidas }}</td>
                            <td>{{ $comissao->parcela ?? '---' }}</td>
                            <td class="valor">R$ {{ number_format($comissao->valor_plano, 2, ',', '.') }}</td>
                            <td class="valor">R$ {{ number_format($comissao->desconto_corretor, 2, ',', '.') }}</td> <!-- Novo campo -->
                            <td class="valor">R$ {{ number_format($comissao->valor_comissao, 2, ',', '.') }}</td>
                            <td>{{ date('d/m/Y', strtotime($comissao->data_vencimento)) }}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<!-- Rodapé -->
<div class="footer">
    <p>Documento gerado automaticamente pelo Sistema BmSyS</p>
</div>
</body>
</html>
