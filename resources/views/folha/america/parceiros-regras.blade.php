<x-app-layout>
<div class="p-4 max-w-5xl mx-auto">

    {{-- Cabecalho --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">
                    Regras de Comissao — Parceiros Independentes
                </h1>
                <p class="text-sm text-gray-300">Configure o % de cada parcela por parceiro e por plano</p>
            </div>
            <a href="{{ route('folha.america.index') }}"
               class="px-3 py-1 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/40 text-green-300 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($parceiros->isEmpty())
        <div class="bg-yellow-900/30 border border-yellow-600/40 text-yellow-300 rounded-xl px-5 py-4 text-sm mb-6">
            Nenhum parceiro independente encontrado. Cadastre usuarios com
            <span class="font-mono bg-yellow-900/50 px-1 rounded">tipo_contrato = parceiro</span> para configurar as regras aqui.
        </div>
    @else

    {{-- Formulario --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
        <h2 class="text-white font-semibold mb-4">Adicionar / Atualizar Regra</h2>

        <form action="{{ route('folha.america.parceiros.regras.salvar') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-gray-300 text-sm block mb-1">Parceiro</label>
                    <select name="parceiro_id"
                            class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500">
                        <option value="" class="bg-gray-800">Selecione...</option>
                        @foreach($parceiros as $p)
                            <option value="{{ $p->id }}" class="bg-gray-800" {{ old('parceiro_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parceiro_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="text-gray-300 text-sm block mb-1">Plano</label>
                    <select name="plano_id"
                            class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500">
                        <option value="" class="bg-gray-800">Selecione...</option>
                        @foreach($planos as $pl)
                            <option value="{{ $pl->id }}" class="bg-gray-800" {{ old('plano_id') == $pl->id ? 'selected' : '' }}>
                                {{ $pl->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('plano_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Parcelas --}}
            <div class="bg-white/5 rounded-xl p-4 mb-4">
                <p class="text-gray-400 text-xs mb-3 uppercase tracking-wider">Percentual por parcela</p>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    @foreach([1,2,3,4,5,6] as $n)
                    <div>
                        <label class="text-gray-300 text-sm block mb-1">
                            {{ $n }}ª Parcela <span class="text-gray-500">(%)</span>
                        </label>
                        <input type="number" name="parcela_{{ $n }}_pct"
                               value="{{ old('parcela_'.$n.'_pct', 0) }}"
                               min="0" max="100" step="0.01"
                               oninput="atualizarTotal()"
                               id="inp-p{{ $n }}"
                               class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500">
                        @error('parcela_'.$n.'_pct')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="text-gray-400 text-sm">Total:</span>
                    <span id="total-pct" class="text-white font-bold text-sm">0%</span>
                    <span id="aviso-total" class="hidden text-xs text-red-400 ml-2">A soma ultrapassa 100%</span>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow text-sm font-semibold">
                    Salvar Regra
                </button>
            </div>
        </form>
    </div>

    @endif

    {{-- Tabela de regras --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10">
            <h2 class="text-white font-semibold text-sm">Regras Cadastradas</h2>
        </div>

        @if($regras->isEmpty())
            <div class="px-4 py-8 text-center text-gray-400 text-sm">
                Nenhuma regra cadastrada ainda.
            </div>
        @else

        @php
            $regrasPorParceiro = $regras->groupBy('parceiro_id');
        @endphp

        @foreach($regrasPorParceiro as $parceiroId => $regrasGrupo)
            @php $nomeParceiro = $parceirosMap[$parceiroId]->name ?? "Parceiro #$parceiroId"; @endphp

            {{-- Header do parceiro --}}
            <div class="px-4 py-2 bg-indigo-900/40 border-b border-white/10 flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr($nomeParceiro, 0, 1)) }}
                </div>
                <span class="text-indigo-200 font-semibold text-sm">{{ $nomeParceiro }}</span>
                <span class="text-gray-500 text-xs">({{ $regrasGrupo->count() }} plano{{ $regrasGrupo->count() > 1 ? 's' : '' }})</span>
            </div>

            <table class="w-full text-sm text-white">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Plano</th>
                        <th class="px-4 py-2 text-center">1ª</th>
                        <th class="px-4 py-2 text-center">2ª</th>
                        <th class="px-4 py-2 text-center">3ª</th>
                        <th class="px-4 py-2 text-center">4ª</th>
                        <th class="px-4 py-2 text-center">5ª</th>
                        <th class="px-4 py-2 text-center">6ª</th>
                        <th class="px-4 py-2 text-center">Total</th>
                        <th class="px-4 py-2 text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($regrasGrupo as $r)
                    @php
                        $nomePlano = $planosMap[$r->plano_id]->nome ?? "Plano #{$r->plano_id}";
                        $totalPct  = (float)$r->parcela_1_pct + (float)$r->parcela_2_pct + (float)$r->parcela_3_pct + (float)$r->parcela_4_pct + (float)$r->parcela_5_pct + (float)$r->parcela_6_pct;
                        $totalCor  = $totalPct > 100 ? 'text-red-400' : ($totalPct == 100 ? 'text-green-400' : 'text-yellow-400');
                    @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-2.5 font-medium">{{ $nomePlano }}</td>
                        <td class="px-4 py-2.5 text-center text-indigo-300">{{ number_format($r->parcela_1_pct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center text-indigo-300">{{ number_format($r->parcela_2_pct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center text-indigo-300">{{ number_format($r->parcela_3_pct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center text-indigo-300">{{ number_format($r->parcela_4_pct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center text-indigo-300">{{ number_format($r->parcela_5_pct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center text-indigo-300">{{ number_format($r->parcela_6_pct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center font-bold {{ $totalCor }}">{{ number_format($totalPct, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="abrirModalEditar(
                                            {{ $r->id }},
                                            '{{ addslashes($nomePlano) }}',
                                            {{ (float)$r->parcela_1_pct }},
                                            {{ (float)$r->parcela_2_pct }},
                                            {{ (float)$r->parcela_3_pct }},
                                            {{ (float)$r->parcela_4_pct }},
                                            {{ (float)$r->parcela_5_pct }},
                                            {{ (float)$r->parcela_6_pct }}
                                        )"
                                        class="px-3 py-1 bg-indigo-600/80 hover:bg-indigo-600 text-white rounded text-xs">
                                    Editar
                                </button>
                                <button onclick="confirmarExclusao({{ $r->id }}, '{{ addslashes($nomeParceiro) }} — {{ addslashes($nomePlano) }}')"
                                        class="px-3 py-1 bg-red-600/80 hover:bg-red-600 text-white rounded text-xs">
                                    Remover
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @endif
    </div>

</div>

{{-- Modal Editar --}}
<div id="modal-editar" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="fecharModalEditar()"></div>
    <div class="relative bg-gray-900 border border-white/20 rounded-2xl shadow-2xl w-full max-w-md text-white">

        <div class="flex items-center justify-between px-5 py-4 border-b border-white/10 bg-indigo-900/40">
            <h3 class="font-bold text-lg text-indigo-200">Editar Regra</h3>
            <button onclick="fecharModalEditar()" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-5 space-y-4">
            <p class="text-gray-400 text-sm">Plano: <span id="edit-plano-nome" class="text-white font-semibold"></span></p>
            <input type="hidden" id="edit-id">

            <div class="grid grid-cols-3 gap-3">
                @foreach([1,2,3,4,5,6] as $n)
                <div>
                    <label class="text-gray-300 text-xs block mb-1">{{ $n }}ª Parcela (%)</label>
                    <input type="number" id="edit-p{{ $n }}"
                           min="0" max="100" step="0.01"
                           oninput="atualizarTotalModal()"
                           class="w-full bg-gray-800 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500">
                </div>
                @endforeach
            </div>

            <div class="flex items-center gap-2 mt-1">
                <span class="text-gray-400 text-sm">Total:</span>
                <span id="edit-total-pct" class="text-white font-bold text-sm">0%</span>
                <span id="edit-aviso-total" class="hidden text-xs text-red-400 ml-2">Ultrapassa 100%</span>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-white/10 flex justify-end gap-2">
            <button onclick="fecharModalEditar()"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm">
                Cancelar
            </button>
            <button onclick="salvarEdicao()"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow text-sm font-semibold">
                Salvar
            </button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    const urlBase = '{{ route("folha.america.parceiros.regras") }}';
    const csrfToken = '{{ csrf_token() }}';

    // ---- Total do formulario principal ----
    function atualizarTotal() {
        const vals = [1,2,3,4,5,6].map(n => parseFloat(document.getElementById('inp-p'+n)?.value) || 0);
        const total = vals.reduce((a,b) => a+b, 0);
        document.getElementById('total-pct').textContent = total.toFixed(2) + '%';
        document.getElementById('aviso-total').classList.toggle('hidden', total <= 100);
    }

    // ---- Modal editar ----
    function abrirModalEditar(id, planoNome, p1, p2, p3, p4, p5, p6) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-plano-nome').textContent = planoNome;
        document.getElementById('edit-p1').value = p1;
        document.getElementById('edit-p2').value = p2;
        document.getElementById('edit-p3').value = p3;
        document.getElementById('edit-p4').value = p4;
        document.getElementById('edit-p5').value = p5 || 0;
        document.getElementById('edit-p6').value = p6 || 0;
        atualizarTotalModal();
        document.getElementById('modal-editar').classList.remove('hidden');
    }

    function fecharModalEditar() {
        document.getElementById('modal-editar').classList.add('hidden');
    }

    function atualizarTotalModal() {
        const vals = [1,2,3,4,5,6].map(n => parseFloat(document.getElementById('edit-p'+n)?.value) || 0);
        const total = vals.reduce((a,b) => a+b, 0);
        document.getElementById('edit-total-pct').textContent = total.toFixed(2) + '%';
        document.getElementById('edit-aviso-total').classList.toggle('hidden', total <= 100);
    }

    function salvarEdicao() {
        const id = document.getElementById('edit-id').value;
        const body = {
            _method:        'PUT',
            parcela_1_pct:  document.getElementById('edit-p1').value,
            parcela_2_pct:  document.getElementById('edit-p2').value,
            parcela_3_pct:  document.getElementById('edit-p3').value,
            parcela_4_pct:  document.getElementById('edit-p4').value,
            parcela_5_pct:  document.getElementById('edit-p5').value,
            parcela_6_pct:  document.getElementById('edit-p6').value,
        };

        fetch(urlBase + '/' + id, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
            body:    JSON.stringify(body),
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire({
                    icon: 'success', title: 'Salvo!', text: 'Regra atualizada.',
                    background: '#1f2937', color: '#f3f4f6', timer: 1500, showConfirmButton: false,
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Nao foi possivel salvar.', background: '#1f2937', color: '#f3f4f6' });
            }
        });
    }

    // ---- Excluir ----
    function confirmarExclusao(id, nome) {
        Swal.fire({
            title: 'Remover regra?',
            html: '<span style="font-size:14px;color:#d1d5db">' + nome + '</span>',
            icon: 'warning',
            background: '#1f2937', color: '#f3f4f6',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#4b5563',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(urlBase + '/' + id, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                body:    JSON.stringify({ _method: 'DELETE' }),
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success', title: 'Removida!',
                        background: '#1f2937', color: '#f3f4f6', timer: 1200, showConfirmButton: false,
                    }).then(() => location.reload());
                }
            });
        });
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') fecharModalEditar();
    });
</script>
@endsection
</x-app-layout>
