<x-app-layout>
<div class="p-4 max-w-5xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-indigo-500 bg-clip-text text-transparent">
                    Regras de Comissão PJ
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    A faixa é determinada pela soma de vidas dos planos
                    <span class="text-white font-medium">Individual (id 1)</span> +
                    <span class="text-white font-medium">Super Simples (id 5)</span>.
                </p>
            </div>
            <a href="{{ route('folha.america.index') }}"
               class="px-3 py-1 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">
                Voltar
            </a>
        </div>
    </div>

    {{-- Regras fixas por plano --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white/10 rounded-xl p-4 border border-white/10">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                <span class="text-xs font-bold text-yellow-300 uppercase tracking-wider">Super Simples (id 5)</span>
            </div>
            <p class="text-white text-sm font-semibold">100% na <span class="text-yellow-300">1ª parcela</span></p>
            <p class="text-gray-400 text-xs mt-1">Regra fixa — independente da faixa de vidas</p>
        </div>
        <div class="bg-white/10 rounded-xl p-4 border border-white/10">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-purple-400 inline-block"></span>
                <span class="text-xs font-bold text-purple-300 uppercase tracking-wider">Coletivo (id 3)</span>
            </div>
            <p class="text-white text-sm font-semibold">50% na <span class="text-purple-300">2ª parcela</span></p>
            <p class="text-gray-400 text-xs mt-1">Regra fixa — independente da faixa de vidas</p>
        </div>
        <div class="bg-white/10 rounded-xl p-4 border border-white/10">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                <span class="text-xs font-bold text-blue-300 uppercase tracking-wider">Individual (id 1)</span>
            </div>
            <p class="text-white text-sm font-semibold">Segue a <span class="text-blue-300">tabela de faixas</span></p>
            <p class="text-gray-400 text-xs mt-1">Vidas Individual + Super Simples determinam a faixa</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/40 text-green-300 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-500/20 border border-red-500/40 text-red-300 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
        <h2 class="text-white font-semibold mb-1">Nova Faixa</h2>
        <p class="text-xs text-gray-400 mb-4">Os percentuais são aplicados sobre a comissão base do contrato Individual.</p>

        <form action="{{ route('folha.america.regras-pj.salvar') }}" method="POST">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-gray-300 text-sm block mb-1">Vidas mínimas (Individual + Super Simples) <span class="text-red-400">*</span></label>
                    <input type="number" name="vidas_min" value="{{ old('vidas_min', 0) }}" min="0"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-gray-300 text-sm block mb-1">Vidas máximas <span class="text-gray-500">(vazio = sem limite)</span></label>
                    <input type="number" name="vidas_max" value="{{ old('vidas_max') }}" min="1"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 border-t border-white/10"></div>
                <span class="text-xs text-gray-400 uppercase tracking-wider">Distribuição da comissão — Plano Individual</span>
                <div class="flex-1 border-t border-white/10"></div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="text-gray-300 text-sm block mb-1">% na 2ª parcela <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input type="number" name="parcela_2_pct" value="{{ old('parcela_2_pct', 100) }}" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-blue-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
                <div>
                    <label class="text-gray-300 text-sm block mb-1">% na 3ª parcela</label>
                    <div class="relative">
                        <input type="number" name="parcela_3_pct" value="{{ old('parcela_3_pct', 0) }}" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-indigo-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
                <div>
                    <label class="text-gray-300 text-sm block mb-1">% na 4ª parcela</label>
                    <div class="relative">
                        <input type="number" name="parcela_4_pct" value="{{ old('parcela_4_pct', 0) }}" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-indigo-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow text-sm font-semibold">
                Cadastrar Faixa
            </button>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
        <table class="w-full text-sm text-white">
            <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Nome</th>
                    <th class="px-4 py-3 text-left">Vidas (Ind. + SS)</th>
                    <th class="px-4 py-3 text-center">2ª parcela</th>
                    <th class="px-4 py-3 text-center">3ª parcela</th>
                    <th class="px-4 py-3 text-center">4ª parcela</th>
                    <th class="px-4 py-3 text-center">Total</th>
                    <th class="px-4 py-3 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($regras as $regra)
                    @php $total = (float)$regra->parcela_2_pct + (float)$regra->parcela_3_pct + (float)$regra->parcela_4_pct; @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-white/10 text-gray-200 tracking-wide">
                                {{ $regra->nome }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            @if($regra->vidas_max)
                                {{ $regra->vidas_min }} – {{ $regra->vidas_max }} vidas
                            @else
                                {{ $regra->vidas_min }}+ vidas
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300">
                                {{ number_format($regra->parcela_2_pct, 0) }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if((float)$regra->parcela_3_pct > 0)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300">
                                    {{ number_format($regra->parcela_3_pct, 0) }}%
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if((float)$regra->parcela_4_pct > 0)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300">
                                    {{ number_format($regra->parcela_4_pct, 0) }}%
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-green-500/20 text-green-300">
                                {{ number_format($total, 0) }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                        onclick="abrirModalEditar({{ $regra->id }},'{{ $regra->nome }}',{{ $regra->vidas_min }},{{ $regra->vidas_max ?? 'null' }},{{ $regra->parcela_2_pct }},{{ $regra->parcela_3_pct }},{{ $regra->parcela_4_pct }})"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600/80 hover:bg-blue-600 text-white rounded text-xs font-medium transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editar
                                </button>
                                <button type="button"
                                        onclick="confirmarExclusao({{ $regra->id }},'{{ $regra->nome }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-red-600/80 hover:bg-red-600 text-white rounded text-xs font-medium transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">Nenhuma faixa cadastrada ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 bg-white/5 rounded-xl text-xs text-gray-400 space-y-1">
        <p>Os percentuais das 3ª e 4ª parcelas são calculados sobre a comissão base do contrato Individual.</p>
        <p>O <span class="text-green-300 font-semibold">Total</span> representa a soma 2ª + 3ª + 4ª parcelas.</p>
    </div>

</div>

{{-- MODAL EDITAR --}}
<div id="modal-editar" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="fecharModalEditar()"></div>
    <div class="relative w-full max-w-lg bg-gray-900 border border-white/10 rounded-2xl shadow-2xl p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-white">Editar Faixa PJ</h2>
                <p id="modal-nome-label" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button onclick="fecharModalEditar()" class="text-gray-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="form-editar">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-gray-300 text-xs block mb-1">Vidas mínimas <span class="text-red-400">*</span></label>
                    <input type="number" id="edit_vidas_min" min="0"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-gray-300 text-xs block mb-1">Vidas máximas <span class="text-gray-500">(vazio = sem limite)</span></label>
                    <input type="number" id="edit_vidas_max" min="1"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 border-t border-white/10"></div>
                <span class="text-xs text-gray-400 uppercase tracking-wider">Parcelas — Plano Individual</span>
                <div class="flex-1 border-t border-white/10"></div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="text-gray-300 text-xs block mb-1">% na 2ª parcela</label>
                    <div class="relative">
                        <input type="number" id="edit_parcela_2_pct" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-blue-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
                <div>
                    <label class="text-gray-300 text-xs block mb-1">% na 3ª parcela</label>
                    <div class="relative">
                        <input type="number" id="edit_parcela_3_pct" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-indigo-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
                <div>
                    <label class="text-gray-300 text-xs block mb-1">% na 4ª parcela</label>
                    <div class="relative">
                        <input type="number" id="edit_parcela_4_pct" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-indigo-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="fecharModalEditar()"
                        class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm transition">
                    Cancelar
                </button>
                <button type="button" onclick="salvarEdicao()"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    const urlBase   = '{{ route("folha.america.regras-pj") }}';

    function abrirModalEditar(id, nome, vidasMin, vidasMax, p2, p3, p4) {
        document.getElementById('modal-nome-label').textContent = 'Editando: ' + nome;
        document.getElementById('edit_vidas_min').value         = vidasMin;
        document.getElementById('edit_vidas_max').value         = vidasMax !== null ? vidasMax : '';
        document.getElementById('edit_parcela_2_pct').value     = p2;
        document.getElementById('edit_parcela_3_pct').value     = p3;
        document.getElementById('edit_parcela_4_pct').value     = p4;
        document.getElementById('form-editar').dataset.id       = id;
        document.getElementById('modal-editar').classList.remove('hidden');
        document.getElementById('modal-editar').classList.add('flex');
    }

    function fecharModalEditar() {
        document.getElementById('modal-editar').classList.add('hidden');
        document.getElementById('modal-editar').classList.remove('flex');
    }

    function salvarEdicao() {
        const id = document.getElementById('form-editar').dataset.id;
        fetch(urlBase + '/' + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                _method:       'PUT',
                _token:        csrfToken,
                vidas_min:     document.getElementById('edit_vidas_min').value,
                vidas_max:     document.getElementById('edit_vidas_max').value || null,
                parcela_2_pct: document.getElementById('edit_parcela_2_pct').value,
                parcela_3_pct: document.getElementById('edit_parcela_3_pct').value,
                parcela_4_pct: document.getElementById('edit_parcela_4_pct').value,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                fecharModalEditar();
                Swal.fire({
                    icon: 'success', title: 'Salvo!', text: 'Faixa atualizada com sucesso.',
                    background: '#1f2937', color: '#f3f4f6', confirmButtonColor: '#2563eb',
                    timer: 1800, showConfirmButton: false,
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Erro', text: data.message ?? 'Nao foi possivel salvar.', background: '#1f2937', color: '#f3f4f6', confirmButtonColor: '#dc2626' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Erro de conexao', background: '#1f2937', color: '#f3f4f6' }));
    }

    function confirmarExclusao(id, nome) {
        Swal.fire({
            title: 'Excluir ' + nome + '?',
            html: 'Essa acao <strong>nao pode ser desfeita</strong>.<br>A numeracao das demais faixas sera ajustada automaticamente.',
            icon: 'warning',
            background: '#1f2937', color: '#f3f4f6',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626', cancelButtonColor: '#4b5563',
            focusCancel: true,
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(urlBase + '/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ _method: 'DELETE', _token: csrfToken }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    Swal.fire({
                        icon: 'success', title: 'Excluido!', text: nome + ' foi removida.',
                        background: '#1f2937', color: '#f3f4f6', confirmButtonColor: '#2563eb',
                        timer: 1600, showConfirmButton: false,
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: 'Nao foi possivel excluir.', background: '#1f2937', color: '#f3f4f6' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Erro de conexao', background: '#1f2937', color: '#f3f4f6' }));
        });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModalEditar(); });
</script>
@endsection

</x-app-layout>
