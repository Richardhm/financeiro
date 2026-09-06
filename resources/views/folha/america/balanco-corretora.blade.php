<x-app-layout>
@section('css')
    <link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
@endsection
<div class="p-4 max-w-6xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">
                    Balanço da Corretora
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Quanto <span class="text-emerald-300 font-semibold">entrou</span> (comissão da corretora recebida da operadora)
                    e quanto <span class="text-red-300 font-semibold">saiu</span> (comissões pagas aos vendedores) no período.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="month" name="mes" value="{{ $mes }}"
                           class="bg-gray-800 border border-white/20 text-white rounded px-3 py-1.5 text-sm focus:outline-none focus:ring focus:ring-amber-500">
                    <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded text-sm font-semibold">Ver</button>
                </form>
                <a href="{{ route('folha.america.comissao-corretora') }}"
                   class="px-3 py-1.5 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">Config. %</a>
            </div>
        </div>
    </div>

    {{-- Cards principais --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl p-5 border border-emerald-500/30 bg-emerald-500/10">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-300 mb-1">⬇ Entrou — {{ $mesFmt }}</p>
            <p class="text-3xl font-extrabold text-emerald-300">R$ {{ number_format($entrou->total, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $entrou->qt }} parcela(s) adiantadas pela operadora</p>
        </div>
        <div class="rounded-xl p-5 border border-red-500/30 bg-red-500/10">
            <p class="text-xs font-bold uppercase tracking-wider text-red-300 mb-1">⬆ Saiu — {{ $mesFmt }}</p>
            <p class="text-3xl font-extrabold text-red-300">R$ {{ number_format($saiu->total, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $saiu->qt }} parcela(s) pagas em folhas de vendedores</p>
        </div>
        <div class="rounded-xl p-5 border {{ $saldo >= 0 ? 'border-blue-500/30 bg-blue-500/10' : 'border-orange-500/40 bg-orange-500/10' }}">
            <p class="text-xs font-bold uppercase tracking-wider {{ $saldo >= 0 ? 'text-blue-300' : 'text-orange-300' }} mb-1">= Saldo do período</p>
            <p class="text-3xl font-extrabold {{ $saldo >= 0 ? 'text-blue-300' : 'text-orange-300' }}">R$ {{ number_format($saldo, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">entrou − saiu</p>
        </div>
    </div>

    {{-- Cards secundários: operadora, adiantamento manual e estornos --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl p-5 border border-teal-500/30 bg-teal-500/10">
            <p class="text-xs font-bold uppercase tracking-wider text-teal-300 mb-1">✔ Confirmadas pela Operadora — {{ $mesFmt }}</p>
            <p class="text-2xl font-extrabold text-teal-300">R$ {{ number_format($confirmadasOperadora->total, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $confirmadasOperadora->qt }} parcela(s) com pagamento confirmado pela operadora
                <span class="text-gray-500">(uploads Parcelas / Adiantamento)</span>
            </p>
        </div>
        <div class="rounded-xl p-5 border border-yellow-500/40 bg-yellow-500/10">
            <p class="text-xs font-bold uppercase tracking-wider text-yellow-300 mb-1">⚠ Paguei sem receber — {{ $mesFmt }}</p>
            <p class="text-2xl font-extrabold text-yellow-300">R$ {{ number_format($adiantadoManual->total, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $adiantadoManual->qt }} parcela(s): cliente pagou e a backoffice adiantou manualmente,
                sem confirmação da operadora
                @if(($adiantadoManual->pago_vendedor ?? 0) > 0)
                    <br><span class="text-yellow-200 font-semibold">R$ {{ number_format($adiantadoManual->pago_vendedor, 2, ',', '.') }}</span> já pago(s) ao vendedor
                @endif
            </p>
        </div>
        <div class="rounded-xl p-5 border {{ ($estornoAplicado->qt + $estornoPendente->qt) > 0 ? 'border-rose-500/40 bg-rose-500/10' : 'border-white/10 bg-white/5' }}">
            <p class="text-xs font-bold uppercase tracking-wider {{ ($estornoAplicado->qt + $estornoPendente->qt) > 0 ? 'text-rose-300' : 'text-gray-400' }} mb-1">↩ Estornos</p>
            @if(($estornoAplicado->qt + $estornoPendente->qt) > 0)
                <p class="text-2xl font-extrabold text-rose-300">R$ {{ number_format($estornoAplicado->total + $estornoPendente->total, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    Descontados em folha no mês: <span class="text-rose-200 font-semibold">R$ {{ number_format($estornoAplicado->total, 2, ',', '.') }}</span> ({{ $estornoAplicado->qt }})<br>
                    Pendentes (a descontar): <span class="text-rose-200 font-semibold">R$ {{ number_format($estornoPendente->total, 2, ',', '.') }}</span> ({{ $estornoPendente->qt }})
                </p>
            @else
                <p class="text-2xl font-extrabold text-gray-400">R$ 0,00</p>
                <p class="text-xs text-gray-500 mt-1">Nenhum estorno no período</p>
            @endif
        </div>
    </div>

    {{-- Situação atual (não depende do mês selecionado) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl p-5 border border-cyan-500/30 bg-cyan-500/10">
            <p class="text-xs font-bold uppercase tracking-wider text-cyan-300 mb-1">⏳ A receber da Operadora — hoje</p>
            <p class="text-2xl font-extrabold text-cyan-300">R$ {{ number_format($aReceber->total, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $aReceber->qt }} parcela(s) que o cliente já pagou e a operadora ainda não repassou
                <span class="text-gray-500">(previsão pela comissão da corretora)</span>
            </p>
        </div>
        <div class="rounded-xl p-5 border border-purple-500/30 bg-purple-500/10">
            <p class="text-xs font-bold uppercase tracking-wider text-purple-300 mb-1">💼 A pagar aos Vendedores — hoje</p>
            <p class="text-2xl font-extrabold text-purple-300">R$ {{ number_format($aPagar->total, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $aPagar->qt }} parcela(s) com dupla confirmação aguardando fechamento de folha
                @if($estornoPendente->total > 0)
                    <br>− <span class="text-rose-300 font-semibold">R$ {{ number_format($estornoPendente->total, 2, ',', '.') }}</span> de estornos a descontar
                    = <span class="text-purple-200 font-semibold">R$ {{ number_format($aPagar->total - $estornoPendente->total, 2, ',', '.') }}</span> líquido
                @endif
            </p>
        </div>
    </div>

    {{-- Evolução 6 meses --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-white/10">
            <h2 class="text-white font-semibold text-sm">Evolução — últimos 6 meses</h2>
        </div>
        @php $maxEvo = max(1, $evolucao->max('entrou'), $evolucao->max('saiu')); @endphp
        <div class="p-4">
            <div class="flex items-end justify-between gap-2" style="height:150px;">
                @foreach($evolucao as $ev)
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-0.5">
                        <div class="flex items-end gap-1 w-full justify-center h-full">
                            <div class="w-4 rounded-t bg-emerald-400/80" title="Entrou: R$ {{ number_format($ev->entrou, 2, ',', '.') }}"
                                 style="height: {{ max(2, round($ev->entrou / $maxEvo * 100)) }}%"></div>
                            <div class="w-4 rounded-t bg-red-400/80" title="Saiu: R$ {{ number_format($ev->saiu, 2, ',', '.') }}"
                                 style="height: {{ max(2, round($ev->saiu / $maxEvo * 100)) }}%"></div>
                        </div>
                        <span class="text-[10px] {{ $ev->mes == $mes ? 'text-amber-300 font-bold' : 'text-gray-400' }}">{{ $ev->label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <table class="w-full text-sm text-white border-t border-white/10">
            <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2 text-left">Mês</th>
                    <th class="px-4 py-2 text-right"><span class="inline-block w-2 h-2 rounded-sm bg-emerald-400/80 mr-1"></span>Entrou</th>
                    <th class="px-4 py-2 text-right"><span class="inline-block w-2 h-2 rounded-sm bg-red-400/80 mr-1"></span>Saiu</th>
                    <th class="px-4 py-2 text-right">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($evolucao as $ev)
                    <tr class="hover:bg-white/5 {{ $ev->mes == $mes ? 'bg-amber-500/5' : '' }} {{ $ev->entrou == 0 && $ev->saiu == 0 ? 'opacity-40' : '' }}">
                        <td class="px-4 py-2 {{ $ev->mes == $mes ? 'text-amber-300 font-bold' : '' }}">{{ $ev->label }}</td>
                        <td class="px-4 py-2 text-right text-emerald-300 font-semibold">{{ $ev->entrou > 0 ? 'R$ '.number_format($ev->entrou, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right text-red-300 font-semibold">{{ $ev->saiu > 0 ? 'R$ '.number_format($ev->saiu, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-bold {{ ($ev->entrou - $ev->saiu) >= 0 ? 'text-blue-300' : 'text-orange-300' }}">R$ {{ number_format($ev->entrou - $ev->saiu, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Por semana --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-white/10">
            <h2 class="text-white font-semibold text-sm">Semana a semana — {{ $mesFmt }}</h2>
        </div>
        <table class="w-full text-sm text-white">
            <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2 text-left">Semana</th>
                    <th class="px-4 py-2 text-right">Entrou</th>
                    <th class="px-4 py-2 text-right">Saiu</th>
                    <th class="px-4 py-2 text-right">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @for($s = 1; $s <= 5; $s++)
                    @php
                        $e = (float) ($entradaSemana[$s] ?? 0);
                        $sa = (float) ($saidaSemana[$s] ?? 0);
                        $ini = ($s - 1) * 7 + 1;
                        $fim = min($s * 7, \Carbon\Carbon::parse($mes.'-01')->daysInMonth);
                    @endphp
                    @if($ini <= \Carbon\Carbon::parse($mes.'-01')->daysInMonth)
                    <tr class="hover:bg-white/5 {{ $e == 0 && $sa == 0 ? 'opacity-40' : '' }}">
                        <td class="px-4 py-2">Semana {{ $s }} <span class="text-gray-500 text-xs">(dia {{ $ini }}–{{ $fim }})</span></td>
                        <td class="px-4 py-2 text-right text-emerald-300 font-semibold">{{ $e > 0 ? 'R$ '.number_format($e, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right text-red-300 font-semibold">{{ $sa > 0 ? 'R$ '.number_format($sa, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-bold {{ ($e - $sa) >= 0 ? 'text-blue-300' : 'text-orange-300' }}">R$ {{ number_format($e - $sa, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                @endfor
            </tbody>
        </table>
    </div>

    {{-- Por vendedor --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10">
            <h2 class="text-white font-semibold text-sm">Por vendedor — {{ $mesFmt }}</h2>
            <p class="text-xs text-gray-400">Entrou = comissão da corretora gerada pelos contratos do vendedor · Saiu = comissão paga ao vendedor</p>
        </div>
        <table class="w-full text-sm text-white">
            <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2 text-left">Vendedor</th>
                    <th class="px-4 py-2 text-center">Tipo</th>
                    <th class="px-4 py-2 text-right">Entrou</th>
                    <th class="px-4 py-2 text-right">Saiu</th>
                    <th class="px-4 py-2 text-right">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($porVendedor as $v)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-2 font-medium">{{ $v->name }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                                {{ $v->tipo_contrato === 'clt' ? 'bg-green-500/30 text-green-200' : ($v->tipo_contrato === 'parceiro' ? 'bg-purple-500/30 text-purple-200' : 'bg-blue-500/30 text-blue-200') }}">
                                {{ strtoupper($v->tipo_contrato ?? 'PJ') }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right text-emerald-300">{{ $v->entrou > 0 ? 'R$ '.number_format($v->entrou, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right text-red-300">{{ $v->saiu > 0 ? 'R$ '.number_format($v->saiu, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-bold {{ $v->saldo >= 0 ? 'text-blue-300' : 'text-orange-300' }}">R$ {{ number_format($v->saldo, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Nenhuma movimentação neste mês.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 bg-white/5 rounded-xl text-xs text-gray-400 space-y-1">
        <p><span class="text-emerald-300 font-semibold">Entrou</span>: parcelas com adiantamento confirmado no mês, valoradas pelo % configurado em <a href="{{ route('folha.america.comissao-corretora') }}" class="underline">Comissão Corretora</a>.</p>
        <p><span class="text-red-300 font-semibold">Saiu</span>: parcelas finalizadas em folhas (CLT/PJ e Parceiros) com data de pagamento no mês.</p>
    </div>

</div>
</x-app-layout>
