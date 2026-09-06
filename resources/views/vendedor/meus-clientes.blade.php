<x-app-layout>
@section('css')
    <link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
@endsection

<div class="p-4 max-w-7xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-4">
        <h1 class="text-xl font-bold bg-gradient-to-r from-sky-400 to-blue-500 bg-clip-text text-transparent">Meus Clientes</h1>
        <p class="text-sm text-gray-400 mt-1">
            Acompanhe os pagamentos dos seus clientes.
            <span class="text-red-300 font-semibold">Clientes atrasados</span> precisam ser cobrados para a parcela entrar na folha.
        </p>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="rounded-xl p-4 border border-white/10 bg-white/5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Clientes</p>
            <p class="text-2xl font-extrabold text-white">{{ $resumo['total'] }}</p>
        </div>
        <div class="rounded-xl p-4 border border-emerald-500/30 bg-emerald-500/10">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-300">Em dia</p>
            <p class="text-2xl font-extrabold text-emerald-300">{{ $resumo['em_dia'] }}</p>
        </div>
        <button type="button" id="filtro-atrasados" class="text-left rounded-xl p-4 border border-red-500/40 bg-red-500/10 hover:bg-red-500/20 transition cursor-pointer">
            <p class="text-[11px] font-bold uppercase tracking-wider text-red-300">⚠ Atrasados</p>
            <p class="text-2xl font-extrabold text-red-300">{{ $resumo['atrasados'] }}</p>
            <p class="text-[10px] text-gray-400">clique para filtrar</p>
        </button>
        <div class="rounded-xl p-4 border border-blue-500/30 bg-blue-500/10">
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-300">Finalizados</p>
            <p class="text-2xl font-extrabold text-blue-300">{{ $resumo['finalizados'] }}</p>
        </div>
    </div>

    {{-- Abas + busca --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 pt-3 pb-0 border-b border-white/10">
            <div class="flex gap-1">
                <button class="vtab px-4 py-2 rounded-t-lg text-sm font-semibold" data-aba="individual">Individual <span class="text-xs opacity-70">({{ $individual->count() }})</span></button>
                <button class="vtab px-4 py-2 rounded-t-lg text-sm font-semibold" data-aba="coletivo">Coletivo <span class="text-xs opacity-70">({{ $coletivo->count() }})</span></button>
                <button class="vtab px-4 py-2 rounded-t-lg text-sm font-semibold" data-aba="empresarial">Empresarial <span class="text-xs opacity-70">({{ $empresarial->count() }})</span></button>
            </div>
            <input type="text" id="busca-cliente" placeholder="Buscar por nome, CPF ou código..."
                   class="mb-2 w-64 max-w-full bg-gray-900 border border-white/20 text-white rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring focus:ring-sky-500 placeholder-gray-500">
        </div>

        @foreach(['individual' => $individual, 'coletivo' => $coletivo, 'empresarial' => $empresarial] as $aba => $lista)
        <div class="vconteudo overflow-x-auto" data-aba="{{ $aba }}">
            <table class="w-full text-sm text-white">
                <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2 text-left">Data</th>
                        <th class="px-3 py-2 text-left">Código</th>
                        <th class="px-3 py-2 text-left">{{ $aba === 'empresarial' ? 'Empresa' : 'Cliente' }}</th>
                        <th class="px-3 py-2 text-left">{{ $aba === 'empresarial' ? 'CNPJ' : 'CPF' }}</th>
                        <th class="px-3 py-2 text-center">Vidas</th>
                        <th class="px-3 py-2 text-right">Valor</th>
                        <th class="px-3 py-2 text-left">Próx. Venc.</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-center">Ver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($lista as $c)
                    <tr class="vlinha hover:bg-white/5 {{ $c->atrasado ? 'bg-red-500/10' : '' }}"
                        data-busca="{{ mb_strtolower($c->nome . ' ' . $c->cpf . ' ' . $c->codigo) }}"
                        data-atrasado="{{ $c->atrasado ? 1 : 0 }}">
                        <td class="px-3 py-2 whitespace-nowrap">{{ $c->data }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ $c->codigo }}</td>
                        <td class="px-3 py-2 font-semibold">{{ $c->nome }}
                            @if($c->atrasado)
                                <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white align-middle">{{ $c->dias_atraso }}d atraso</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-gray-300">{{ $c->cpf }}</td>
                        <td class="px-3 py-2 text-center">{{ $c->vidas }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">R$ {{ number_format($c->valor, 2, ',', '.') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap {{ $c->atrasado ? 'text-red-300 font-bold' : 'text-gray-300' }}">{{ $c->vencimento ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if($c->status_tipo === 'finalizado')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-900 text-blue-200">Finalizado</span>
                            @elseif($c->status_tipo === 'cancelado')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-700 text-gray-300">Cancelado</span>
                            @elseif($c->atrasado)
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-900 text-red-200">{{ $c->status }} — Atrasado</span>
                            @elseif($c->status_tipo === 'aguardando')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-yellow-900 text-yellow-200">{{ $c->status }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-900 text-emerald-200">{{ $c->status }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button class="ver-parcelas text-sky-400 hover:text-sky-200 transition" title="Ver parcelas"
                                    data-tipo="{{ $c->tipo }}" data-id="{{ $c->id }}" data-nome="{{ $c->nome }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Nenhum cliente nesta categoria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal parcelas --}}
<div id="modal-parcelas" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-lg rounded-xl overflow-hidden shadow-2xl" style="background:#1e1e1e;border:1px solid #2a3d55;">
        <div class="flex items-center justify-between px-5 py-3" style="background:#0e1a28;border-bottom:1px solid #2a3d55;">
            <h5 id="modal-titulo" class="text-sm font-bold text-white truncate pr-4">Parcelas</h5>
            <button type="button" id="fechar-modal" class="text-2xl font-bold leading-none text-gray-500 hover:text-white transition">&times;</button>
        </div>
        <div class="p-4 max-h-[70vh] overflow-y-auto">
            <table class="w-full text-sm text-white">
                <thead class="text-gray-400 uppercase text-xs border-b border-white/10">
                    <tr>
                        <th class="px-2 py-2 text-left">Parcela</th>
                        <th class="px-2 py-2 text-left">Vencimento</th>
                        <th class="px-2 py-2 text-left">Pagamento</th>
                        <th class="px-2 py-2 text-left">Situação</th>
                    </tr>
                </thead>
                <tbody id="modal-corpo" class="divide-y divide-white/10"></tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .vtab { color:#9ca3af; background:transparent; border:1px solid transparent; border-bottom:none; }
    .vtab.ativa { color:#fff; background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.15); }
    .vconteudo { display:none; }
    .vconteudo.ativa { display:block; }
    #filtro-atrasados.ativo { outline:2px solid #ef4444; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var soAtrasados = false;

    function ativarAba(nome) {
        document.querySelectorAll('.vtab').forEach(function (t) { t.classList.toggle('ativa', t.dataset.aba === nome); });
        document.querySelectorAll('.vconteudo').forEach(function (c) { c.classList.toggle('ativa', c.dataset.aba === nome); });
        aplicarFiltros();
    }

    function aplicarFiltros() {
        var termo = document.getElementById('busca-cliente').value.trim().toLowerCase();
        document.querySelectorAll('.vconteudo.ativa .vlinha').forEach(function (tr) {
            var okBusca = !termo || tr.dataset.busca.indexOf(termo) !== -1;
            var okAtraso = !soAtrasados || tr.dataset.atrasado === '1';
            tr.style.display = (okBusca && okAtraso) ? '' : 'none';
        });
    }

    document.querySelectorAll('.vtab').forEach(function (t) {
        t.addEventListener('click', function () { ativarAba(t.dataset.aba); });
    });
    document.getElementById('busca-cliente').addEventListener('input', aplicarFiltros);
    document.getElementById('filtro-atrasados').addEventListener('click', function () {
        soAtrasados = !soAtrasados;
        this.classList.toggle('ativo', soAtrasados);
        aplicarFiltros();
    });

    // Modal de parcelas
    var modal = document.getElementById('modal-parcelas');
    document.querySelectorAll('.ver-parcelas').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modal-titulo').textContent = 'Parcelas — ' + btn.dataset.nome;
            var corpo = document.getElementById('modal-corpo');
            corpo.innerHTML = '<tr><td colspan="4" class="px-2 py-6 text-center text-gray-500">Carregando...</td></tr>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            fetch('{{ url('/meus-clientes/parcelas') }}/' + btn.dataset.tipo + '/' + btn.dataset.id, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (!j.success) { corpo.innerHTML = '<tr><td colspan="4" class="px-2 py-6 text-center text-red-400">Erro ao carregar.</td></tr>'; return; }
                    corpo.innerHTML = j.parcelas.map(function (p) {
                        var situacao;
                        if (p.pago) situacao = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-900 text-emerald-200">Pago</span>';
                        else if (p.atrasada) situacao = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-900 text-red-200">Atrasada</span>';
                        else situacao = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-700 text-gray-300">Em aberto</span>';
                        return '<tr class="' + (p.atrasada ? 'bg-red-500/10' : '') + '">' +
                            '<td class="px-2 py-2 font-semibold">' + p.rotulo + '</td>' +
                            '<td class="px-2 py-2">' + p.vencimento + '</td>' +
                            '<td class="px-2 py-2">' + (p.baixa || '—') + '</td>' +
                            '<td class="px-2 py-2">' + situacao + '</td></tr>';
                    }).join('');
                })
                .catch(function () { corpo.innerHTML = '<tr><td colspan="4" class="px-2 py-6 text-center text-red-400">Erro ao carregar.</td></tr>'; });
        });
    });
    document.getElementById('fechar-modal').addEventListener('click', function () {
        modal.classList.add('hidden'); modal.classList.remove('flex');
    });
    modal.addEventListener('click', function (e) { if (e.target === modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } });

    ativarAba('individual');
});
</script>
</x-app-layout>
