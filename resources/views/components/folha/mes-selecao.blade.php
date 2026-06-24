@props(['proximosMeses', 'mesesUsados' => []])

<div class="min-h-screen flex items-center justify-center p-6" style="background: radial-gradient(ellipse at top, #1a1040 0%, #0d0d1a 60%, #0a0a14 100%);">

    <div class="w-full max-w-2xl">

        {{-- Ícone + Título --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
                 style="background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%); box-shadow: 0 0 40px rgba(124,58,237,.4)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="white" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Abrir Folha de Pagamento
            </h1>
            <p class="text-gray-400 text-sm mt-1">Selecione o mês de competência para iniciar o processamento</p>
        </div>

        {{-- Grid de meses --}}
        <div class="grid grid-cols-3 gap-3 mb-6" id="grid-meses">
            @foreach($proximosMeses as $mes)
                @php
                    [$nomeMes, $ano] = explode('/', $mes['nome']);
                    $chave   = substr($mes['valor'], 0, 7); // "2025-10-01" → "2025-10"
                    $jaUsado = array_key_exists($chave, $mesesUsados);
                @endphp

                @if($jaUsado)
                    {{-- Mês já processado: cinza + X vermelho --}}
                    <button
                        type="button"
                        class="mes-card-bloqueado group relative overflow-hidden rounded-xl border text-center p-4 transition-all duration-200 hover:border-red-500/50 focus:outline-none cursor-not-allowed"
                        style="background: rgba(255,255,255,0.03); border-color: rgba(239,68,68,0.2);"
                        data-nome="{{ $mes['nome'] }}"
                    >
                        {{-- Badge X --}}
                        <span class="absolute top-2 right-2 flex items-center justify-center w-5 h-5 rounded-full bg-red-600/90">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-3 h-3">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                            </svg>
                        </span>

                        <span class="block text-lg font-bold text-gray-500 capitalize">{{ $nomeMes }}</span>
                        <span class="block text-xs text-gray-600 mt-0.5">{{ $ano }}</span>
                    </button>
                @else
                    {{-- Mês disponível --}}
                    <button
                        type="button"
                        class="mes-card group relative overflow-hidden rounded-xl border border-white/10 text-center p-4 transition-all duration-200 hover:border-purple-500/60 hover:scale-[1.03] focus:outline-none"
                        style="background: rgba(255,255,255,0.05); backdrop-filter: blur(8px);"
                        data-valor="{{ $mes['valor'] }}"
                        data-nome="{{ $mes['nome'] }}"
                    >
                        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-xl"
                              style="background: radial-gradient(circle at 50% 50%, rgba(124,58,237,.15) 0%, transparent 70%)"></span>

                        <span class="block text-lg font-bold text-white group-hover:text-purple-300 transition-colors capitalize">
                            {{ $nomeMes }}
                        </span>
                        <span class="block text-xs text-gray-400 mt-0.5">{{ $ano }}</span>
                    </button>
                @endif
            @endforeach
        </div>

        {{-- Spinner de carregamento --}}
        <div id="loading-mes" class="hidden text-center py-4">
            <div class="inline-flex items-center gap-3 text-gray-300">
                <svg class="animate-spin w-5 h-5 text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="text-sm">Abrindo folha...</span>
            </div>
        </div>

        {{-- Legenda --}}
        <div class="flex items-center justify-center gap-6 mt-2">
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="inline-block w-3 h-3 rounded-full" style="background: rgba(124,58,237,.6)"></span>
                Disponível
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <span class="inline-block w-3 h-3 rounded-full bg-red-600/70"></span>
                Já processado
            </div>
        </div>

        <p class="text-center text-xs text-gray-600 mt-3">
            Após confirmar, a folha ficará aberta até ser fechada manualmente
        </p>

    </div>
</div>

{{-- Modal de confirmação (mês disponível) --}}
<div id="modal-confirmar-mes"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background: rgba(0,0,0,.7); backdrop-filter: blur(4px)">
    <div class="w-full max-w-sm rounded-2xl border border-white/10 p-6 shadow-2xl"
         style="background: #1a1040;">
        <div class="flex items-center gap-3 mb-1">
            <div class="flex items-center justify-center w-8 h-8 rounded-full"
                 style="background: linear-gradient(135deg, #7c3aed, #db2777)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="white" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-white">Confirmar abertura</h3>
        </div>
        <p class="text-gray-400 text-sm mb-5 ml-11">
            Deseja abrir a folha de
            <strong id="modal-mes-nome" class="text-purple-300"></strong>?
        </p>
        <div class="flex gap-3">
            <button id="btn-cancelar-mes"
                    class="flex-1 rounded-lg border border-white/10 py-2 text-sm text-gray-300 hover:bg-white/5 transition">
                Cancelar
            </button>
            <button id="btn-confirmar-mes"
                    class="flex-1 rounded-lg py-2 text-sm font-semibold text-white transition hover:opacity-90"
                    style="background: linear-gradient(135deg, #7c3aed, #db2777)">
                Confirmar
            </button>
        </div>
    </div>
</div>

{{-- Modal de aviso (mês já processado) --}}
<div id="modal-mes-bloqueado"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background: rgba(0,0,0,.7); backdrop-filter: blur(4px)">
    <div class="w-full max-w-sm rounded-2xl border border-red-500/30 p-6 shadow-2xl"
         style="background: #1a0d0d;">
        <div class="flex items-center gap-3 mb-1">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-red-600/80">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-red-300">Mês já processado</h3>
        </div>
        <p class="text-gray-400 text-sm mb-5 ml-11">
            O mês <strong id="modal-bloqueado-nome" class="text-red-400"></strong>
            já possui uma folha registrada e não pode ser aberto novamente.
        </p>
        <button id="btn-fechar-bloqueado"
                class="w-full rounded-lg border border-red-500/30 py-2 text-sm text-gray-300 hover:bg-red-500/10 transition">
            Entendido
        </button>
    </div>
</div>

<script>
(function () {
    let mesSelecionadoValor = null;

    const modalConfirm   = document.getElementById('modal-confirmar-mes');
    const modalBloqueado = document.getElementById('modal-mes-bloqueado');
    const modalNome      = document.getElementById('modal-mes-nome');
    const modalBloqNome  = document.getElementById('modal-bloqueado-nome');
    const btnConfirm     = document.getElementById('btn-confirmar-mes');
    const btnCancel      = document.getElementById('btn-cancelar-mes');
    const btnFecharBloq  = document.getElementById('btn-fechar-bloqueado');
    const loading        = document.getElementById('loading-mes');
    const grid           = document.getElementById('grid-meses');

    // Clique em mês disponível
    grid.querySelectorAll('.mes-card').forEach(function (btn) {
        btn.addEventListener('click', function () {
            mesSelecionadoValor = this.dataset.valor;
            modalNome.textContent = this.dataset.nome;
            modalConfirm.classList.remove('hidden');
            modalConfirm.classList.add('flex');
        });
    });

    // Clique em mês bloqueado
    grid.querySelectorAll('.mes-card-bloqueado').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modalBloqNome.textContent = this.dataset.nome;
            modalBloqueado.classList.remove('hidden');
            modalBloqueado.classList.add('flex');
        });
    });

    function fecharConfirm() {
        modalConfirm.classList.add('hidden');
        modalConfirm.classList.remove('flex');
    }
    btnCancel.addEventListener('click', fecharConfirm);
    modalConfirm.addEventListener('click', function (e) { if (e.target === modalConfirm) fecharConfirm(); });

    function fecharBloqueado() {
        modalBloqueado.classList.add('hidden');
        modalBloqueado.classList.remove('flex');
    }
    btnFecharBloq.addEventListener('click', fecharBloqueado);
    modalBloqueado.addEventListener('click', function (e) { if (e.target === modalBloqueado) fecharBloqueado(); });

    // Confirmar abertura
    btnConfirm.addEventListener('click', async function () {
        if (!mesSelecionadoValor) return;

        fecharConfirm();
        grid.classList.add('opacity-40', 'pointer-events-none');
        loading.classList.remove('hidden');

        try {
            const resp = await fetch('/folha/selecionar/mes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ mes: mesSelecionadoValor })
            });
            const data = await resp.json();

            if (data.success) {
                location.reload();
            } else {
                grid.classList.remove('opacity-40', 'pointer-events-none');
                loading.classList.add('hidden');
                alert(data.message || 'Erro ao abrir a folha.');
            }
        } catch (e) {
            grid.classList.remove('opacity-40', 'pointer-events-none');
            loading.classList.add('hidden');
            alert('Erro de comunicação. Tente novamente.');
        }
    });
})();
</script>
