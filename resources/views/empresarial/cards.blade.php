@foreach($resultados as $cenario)

    <div class="max-w-5xl mx-auto my-1 border-2 border-white rounded-3xl bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] shadow-xl font-sans text-sm leading-tight">
        <!-- Título do Card -->
        <div class="mb-1 mt-1 w-[95%] mx-auto p-1 border-1 border-white rounded-xl text-white text-center font-bold text-sm bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] uppercase">
            {{ $cenario['label'] }}
        </div>
        <!-- Cabeçalhos das Colunas -->
        <div class="flex text-white font-semibold text-xs text-center mb-1">
            <div style="flex-basis: 20%;">Faixa Etária</div>
            <div style="flex-basis: 20%;">Quantidade</div>
            <div style="flex-basis: 30%;">Unitário</div>
            <div style="flex-basis: 30%;">Total</div>
        </div>
        <!-- Linhas de Dados -->
        <div>
            @php
                $faixasEtarias = [];
                $quantidades = [];
                $unitarios = [];
                $totais = [];
                $copart = "";
                $odonto = "";
                $totalApartamentoGeral = 0;
                $totalEnfermariaGeral = 0;
                /* Preenche os arrays com os dados de cada faixa etária */
                foreach ($cenario['rows'] as $row) {
                    /* Faixas Etárias "ajustadas" */
                    $faixa = match($row['faixa_etaria']) {
                        'Faixa 1' => '0 a 18',
                        'Faixa 2' => '19 a 23',
                        'Faixa 3' => '24 a 28',
                        'Faixa 4' => '29 a 33',
                        'Faixa 5' => '34 a 38',
                        'Faixa 6' => '39 a 43',
                        'Faixa 7' => '44 a 48',
                        'Faixa 8' => '49 a 53',
                        'Faixa 9' => '54 a 58',
                        'Faixa 10' => 'Acima 59+',
                        default => '',
                    };
                    $faixasEtarias[] = $faixa;

                    /* Quantidades (QTE) */
                    $quantidades[] = $row['quantidade'];

                    /* Valores Unitários */
                    $unitarios[] = [
                        'apartamento' => number_format($row['valor_apartamento'], 2, ',', '.'),
                        'enfermaria' => number_format($row['valor_enfermaria'], 2, ',', '.'),
                    ];

                    /* Totais (Apartamento e Enfermaria) */
                    $totais[] = [
                        'apartamento' => number_format($row['total_apartamento'], 2, ',', '.'),
                        'enfermaria' => number_format($row['total_enfermaria'], 2, ',', '.'),
                    ];




                    /* Somar Totais Gerais */
                    $totalApartamentoGeral += $row['total_apartamento'];
                    $totalEnfermariaGeral += $row['total_enfermaria'];
                }
            @endphp

            @foreach ($faixasEtarias as $index => $faixa)
                <div class="flex text-white text-center items-center mb-2">
                    <!-- Faixa Etária -->
                    <div style="flex-basis: 20%;" class="text-xs font-bold">{{ $faixa }}</div>

                    <!-- Quantidade -->
                    <div style="flex-basis: 20%;" class="text-xs font-bold">{{ $quantidades[$index] }}</div>

                    <!-- Unitário -->
                    <div style="flex-basis: 30%;" class="text-xs flex flex-wrap">
                        <div class="flex items-center justify-around w-full">
                            <span>Apart:</span>
                            <span>Enfer:</span>
                        </div>
                        <div class="flex items-center justify-around w-full">
                            <span class="font-bold">{{ $unitarios[$index]['apartamento'] }}</span>
                            <span class="font-bold">{{ $unitarios[$index]['enfermaria'] }}</span>
                        </div>
                    </div>

                    <!-- Totais -->
                    <div style="flex-basis: 30%;" class="text-xs flex flex-wrap">
                        <div class="flex items-center justify-around w-full">
                            <span>Apart:</span>
                            <span>Enfer:</span>
                        </div>
                        <div class="flex items-center justify-around w-full">
                            <span class="font-bold">{{ $totais[$index]['apartamento'] }}</span>
                            <span class="font-bold">{{ $totais[$index]['enfermaria'] }}</span>
                        </div>
                    </div>
                </div>
                @if (!$loop->last)
                    <div class="h-[2px] w-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] my-1 rounded-lg"></div>
                @endif
            @endforeach
        </div>

        <!-- Total Geral -->
        <div class="mt-2 pt-2 text-white border-t-2 border-white text-xs">
            <div class="flex justify-around items-center">
                <div style="flex-basis: 40%;" class="font-bold text-right">Total Apartarmento:</div>
                <div style="flex-basis: 30%;" class="font-bold text-center text-lg text-green-300"> R$ {{ number_format($totalApartamentoGeral, 2, ',', '.') }}</div>
                <span>|</span>
                <div style="flex-basis: 40%;" class="font-bold text-right">Total Enfermeria:</div>
                <div style="flex-basis: 30%;" class="font-bold text-center text-lg text-red-600"> R$ {{ number_format($totalEnfermariaGeral, 2, ',', '.') }}</div>
            </div>
        </div>

        <!-- Botão Gerar Imagem -->
        <div class="w-[95%] mx-auto">
            <button type="button" data-coparticipacao="{{ $cenario['copart'] }}" data-odonto="{{ $cenario['odonto'] }}" class="gerar_ss_empresarial mx-auto w-[100%] mt-2 text-white bg-red-400 font-medium rounded-lg text-sm px-2 py-1.5 mb-1">
                Gerar Imagem
            </button>
        </div>


    </div>

@endforeach
