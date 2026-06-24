<x-app-layout>
<div class="p-4 max-w-5xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent">
                    Faixas de Comissão CLT
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Cada faixa define o <strong class="text-white">% base</strong> por quantidade de vidas e,
                    opcionalmente, um <strong class="text-white">% bônus</strong> quando a produção ultrapassa um valor.
                </p>
            </div>
            <a href="{{ route('folha.america.index') }}"
               class="px-3 py-1 bg-gray-600 text-white rounded shadow hover:bg-gray-500 text-sm">
                Voltar
            </a>
        </div>
    </div>

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/40 text-green-300 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Erros de validação --}}
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-500/20 border border-red-500/40 text-red-300 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário de cadastro --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md p-5 mb-6">
        <h2 class="text-white font-semibold mb-1">Nova Faixa</h2>
        <p class="text-xs text-gray-400 mb-4">
            Defina o intervalo de vidas, o percentual base e, se houver, o bônus por produção.
        </p>

        <form action="{{ route('folha.america.faixas-clt.salvar') }}" method="POST">
            @csrf

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="text-gray-300 text-sm block mb-1">Vidas mínimas <span class="text-red-400">*</span></label>
                    <input type="number" name="vidas_min" value="{{ old('vidas_min', 0) }}" min="0"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-green-500">
                </div>
                <div>
                    <label class="text-gray-300 text-sm block mb-1">Vidas máximas <span class="text-gray-500">(vazio = sem limite)</span></label>
                    <input type="number" name="vidas_max" value="{{ old('vidas_max') }}" min="1"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-green-500">
                </div>
                <div>
                    <label class="text-gray-300 text-sm block mb-1">% Base <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input type="number" name="percentual" value="{{ old('percentual') }}" min="0" max="100" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-green-500"
                               placeholder="Ex: 10">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 border-t border-white/10"></div>
                <span class="text-xs text-gray-400 uppercase tracking-wider">Bônus por produção (opcional)</span>
                <div class="flex-1 border-t border-white/10"></div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="text-gray-300 text-sm block mb-1">
                        Produção mínima para bônus (R$)
                        <span class="text-gray-500">— deixe vazio se não houver bônus</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400 text-sm">R$</span>
                        <input type="number" name="producao_bonus" value="{{ old('producao_bonus') }}" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pl-9 focus:outline-none focus:ring focus:ring-yellow-500"
                               placeholder="Ex: 3000">
                    </div>
                </div>
                <div>
                    <label class="text-gray-300 text-sm block mb-1">% Bônus</label>
                    <div class="relative">
                        <input type="number" name="percentual_bonus" value="{{ old('percentual_bonus') }}" min="0" max="100" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-yellow-500"
                               placeholder="Ex: 15">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
                <div class="col-span-3">
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow text-sm font-semibold">
                        Cadastrar Faixa
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabela de faixas --}}
    <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-md overflow-hidden">
        <table class="w-full text-sm text-white">
            <thead class="bg-white/10 text-gray-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Nome</th>
                    <th class="px-4 py-3 text-left">Vidas</th>
                    <th class="px-4 py-3 text-center">% Base</th>
                    <th class="px-4 py-3 text-center">Produção p/ Bônus</th>
                    <th class="px-4 py-3 text-center">% Bônus</th>
                    <th class="px-4 py-3 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($faixas as $faixa)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-white/10 text-gray-200 tracking-wide">
                                {{ $faixa->nome }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            @if($faixa->vidas_max)
                                {{ $faixa->vidas_min }} – {{ $faixa->vidas_max }} vidas
                            @else
                                Acima de {{ $faixa->vidas_min - 1 }} vidas
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-green-500/20 text-green-300">
                                {{ number_format($faixa->percentual, 0, ',', '.') }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($faixa->producao_bonus)
                                <span class="text-yellow-400 text-xs">
                                    acima de R$ {{ number_format($faixa->producao_bonus, 2, ',', '.') }}
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($faixa->percentual_bonus)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-300">
                                    {{ number_format($faixa->percentual_bonus, 0, ',', '.') }}%
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Botão Editar --}}
                                <button type="button"
                                        onclick="abrirModalEditar({{ $faixa->id }}, '{{ $faixa->nome }}', {{ $faixa->vidas_min }}, {{ $faixa->vidas_max ?? 'null' }}, {{ $faixa->percentual }}, {{ $faixa->producao_bonus ?? 'null' }}, {{ $faixa->percentual_bonus ?? 'null' }})"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600/80 hover:bg-blue-600 text-white rounded text-xs font-medium transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editar
                                </button>

                                {{-- Botão Excluir --}}
                                <button type="button"
                                        onclick="confirmarExclusao({{ $faixa->id }}, '{{ $faixa->nome }}')"
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
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                            Nenhuma faixa cadastrada ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Legenda --}}
    <div class="mt-4 p-4 bg-white/5 rounded-xl text-xs text-gray-400 space-y-1">
        <p><span class="text-green-300 font-semibold">% Base</span> — percentual aplicado quando o vendedor atinge o intervalo de vidas independente da produção.</p>
        <p><span class="text-yellow-300 font-semibold">% Bônus</span> — substitui o % base quando a produção total do mês ultrapassa o valor configurado.</p>
    </div>

</div>

{{-- ===================== MODAL EDITAR ===================== --}}
<div id="modal-editar" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="fecharModalEditar()"></div>

    {{-- Card --}}
    <div class="relative w-full max-w-lg bg-gray-900 border border-white/10 rounded-2xl shadow-2xl p-6 z-10">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-white">Editar Faixa</h2>
                <p id="modal-nome-label" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button onclick="fecharModalEditar()" class="text-gray-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="form-editar" method="POST">
            @csrf
            @method('PUT')

            {{-- Vidas + % Base --}}
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="text-gray-300 text-xs block mb-1">Vidas mínimas <span class="text-red-400">*</span></label>
                    <input type="number" id="edit_vidas_min" name="vidas_min" min="0"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-green-500">
                </div>
                <div>
                    <label class="text-gray-300 text-xs block mb-1">Vidas máximas <span class="text-gray-500">(vazio = sem limite)</span></label>
                    <input type="number" id="edit_vidas_max" name="vidas_max" min="1"
                           class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-green-500">
                </div>
                <div>
                    <label class="text-gray-300 text-xs block mb-1">% Base <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input type="number" id="edit_percentual" name="percentual" min="0" max="100" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-green-500">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
            </div>

            {{-- Separador bônus --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 border-t border-white/10"></div>
                <span class="text-xs text-gray-400 uppercase tracking-wider">Bônus por produção</span>
                <div class="flex-1 border-t border-white/10"></div>
            </div>

            {{-- Bônus --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-gray-300 text-xs block mb-1">Produção mínima para bônus (R$)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-400 text-sm">R$</span>
                        <input type="number" id="edit_producao_bonus" name="producao_bonus" min="0" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pl-9 focus:outline-none focus:ring focus:ring-yellow-500"
                               placeholder="Opcional">
                    </div>
                </div>
                <div>
                    <label class="text-gray-300 text-xs block mb-1">% Bônus</label>
                    <div class="relative">
                        <input type="number" id="edit_percentual_bonus" name="percentual_bonus" min="0" max="100" step="0.01"
                               class="w-full bg-white/10 border border-white/20 text-white rounded px-3 py-2 text-sm pr-8 focus:outline-none focus:ring focus:ring-yellow-500"
                               placeholder="Opcional">
                        <span class="absolute right-3 top-2 text-gray-400 text-sm">%</span>
                    </div>
                </div>
            </div>

            {{-- Botões --}}
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
    const urlBase   = '{{ route("folha.america.faixas-clt") }}';

    // ─── MODAL EDITAR ───────────────────────────────────────────
    function abrirModalEditar(id, nome, vidasMin, vidasMax, percentual, producaoBonus, percentualBonus) {
        document.getElementById('modal-nome-label').textContent = 'Editando: ' + nome;
        document.getElementById('edit_vidas_min').value         = vidasMin;
        document.getElementById('edit_vidas_max').value         = vidasMax !== null ? vidasMax : '';
        document.getElementById('edit_percentual').value        = percentual;
        document.getElementById('edit_producao_bonus').value    = producaoBonus !== null ? producaoBonus : '';
        document.getElementById('edit_percentual_bonus').value  = percentualBonus !== null ? percentualBonus : '';
        document.getElementById('form-editar').dataset.id       = id;
        document.getElementById('modal-editar').classList.remove('hidden');
        document.getElementById('modal-editar').classList.add('flex');
    }

    function fecharModalEditar() {
        document.getElementById('modal-editar').classList.add('hidden');
        document.getElementById('modal-editar').classList.remove('flex');
    }

    function salvarEdicao() {
        const id      = document.getElementById('form-editar').dataset.id;
        const payload = {
            _method:          'PUT',
            _token:           csrfToken,
            vidas_min:        document.getElementById('edit_vidas_min').value,
            vidas_max:        document.getElementById('edit_vidas_max').value || null,
            percentual:       document.getElementById('edit_percentual').value,
            producao_bonus:   document.getElementById('edit_producao_bonus').value || null,
            percentual_bonus: document.getElementById('edit_percentual_bonus').value || null,
        };

        fetch(urlBase + '/' + id, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body:    JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                fecharModalEditar();
                Swal.fire({
                    icon:              'success',
                    title:             'Salvo!',
                    text:              'A faixa foi atualizada com sucesso.',
                    background:        '#1f2937',
                    color:             '#f3f4f6',
                    confirmButtonColor: '#2563eb',
                    timer:             1800,
                    showConfirmButton: false,
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon:              'error',
                    title:             'Erro',
                    text:              data.message ?? 'Não foi possível salvar.',
                    background:        '#1f2937',
                    color:             '#f3f4f6',
                    confirmButtonColor: '#dc2626',
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon:              'error',
                title:             'Erro de conexão',
                text:              'Verifique sua conexão e tente novamente.',
                background:        '#1f2937',
                color:             '#f3f4f6',
                confirmButtonColor: '#dc2626',
            });
        });
    }

    // ─── EXCLUIR ────────────────────────────────────────────────
    function confirmarExclusao(id, nome) {
        Swal.fire({
            title:              'Excluir ' + nome + '?',
            html:               'Essa ação <strong>não pode ser desfeita</strong>.<br>A numeração das demais regras será ajustada automaticamente.',
            icon:               'warning',
            background:         '#1f2937',
            color:              '#f3f4f6',
            showCancelButton:   true,
            confirmButtonText:  'Sim, excluir',
            cancelButtonText:   'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor:  '#4b5563',
            focusCancel:        true,
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(urlBase + '/' + id, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body:    JSON.stringify({ _method: 'DELETE', _token: csrfToken }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    Swal.fire({
                        icon:              'success',
                        title:             'Excluído!',
                        text:              nome + ' foi removida.',
                        background:        '#1f2937',
                        color:             '#f3f4f6',
                        confirmButtonColor: '#2563eb',
                        timer:             1600,
                        showConfirmButton: false,
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: 'Não foi possível excluir.', background: '#1f2937', color: '#f3f4f6' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Erro de conexão', background: '#1f2937', color: '#f3f4f6' });
            });
        });
    }

    // Fechar modal ao pressionar ESC
    document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModalEditar(); });
</script>
@endsection

</x-app-layout>
