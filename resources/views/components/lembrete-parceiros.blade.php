@php
use App\Models\ParceirosConfigPagamento;
use Carbon\Carbon;

$hoje   = Carbon::today();
$amanha = Carbon::tomorrow();

// ISO weekday: 1=Mon ... 7=Sun
$diaSemanaHoje   = (int) $hoje->isoFormat('E');
$diaSemanaAmanha = (int) $amanha->isoFormat('E');

// Day-of-month
$diaMesHoje   = (int) $hoje->day;
$diaMesAmanha = (int) $amanha->day;

$configs = ParceirosConfigPagamento::with('user')->where('ativo', true)->get();

$lembretes = [];   // ['nome' => ..., 'quando' => 'hoje'|'amanha']

foreach ($configs as $cfg) {
    $dias = (array) $cfg->dias_pagamento;

    if ($cfg->frequencia === 'semanal') {
        $venceHoje   = in_array($diaSemanaHoje,   $dias);
        $venceAmanha = !$venceHoje && in_array($diaSemanaAmanha, $dias);
    } else {
        // quinzenal, mensal, personalizado — compare day of month
        $venceHoje   = in_array($diaMesHoje,   $dias);
        $venceAmanha = !$venceHoje && in_array($diaMesAmanha, $dias);
    }

    $nome = $cfg->user->name ?? "Parceiro #{$cfg->user_id}";

    if ($venceHoje) {
        $lembretes[] = ['nome' => $nome, 'quando' => 'hoje'];
    } elseif ($venceAmanha) {
        $lembretes[] = ['nome' => $nome, 'quando' => 'amanha'];
    }
}
@endphp

@if(count($lembretes) > 0)
<div id="lembretes-parceiros" class="px-4 pt-3 space-y-2">

    @foreach($lembretes as $index => $lembrete)
        @php
            $isHoje = $lembrete['quando'] === 'hoje';
            $cor  = $isHoje ? 'orange' : 'blue';
            $bgClass     = $isHoje ? 'bg-orange-900/40 border-orange-500/50' : 'bg-blue-900/40 border-blue-500/50';
            $textClass   = $isHoje ? 'text-orange-200'  : 'text-blue-200';
            $iconBg      = $isHoje ? 'bg-orange-500/30' : 'bg-blue-500/30';
            $iconText    = $isHoje ? 'text-orange-300'  : 'text-blue-300';
            $badgeClass  = $isHoje ? 'bg-orange-500/40 text-orange-100' : 'bg-blue-500/40 text-blue-100';
            $label       = $isHoje ? 'HOJE' : 'AMANHA';
        @endphp
        <div id="lembrete-{{ $index }}"
             class="flex items-center justify-between rounded-lg border px-4 py-2.5 text-sm {{ $bgClass }}"
             data-key="lembrete-parceiro-{{ $index }}-{{ $hoje->format('Y-m-d') }}">

            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $iconBg }}">
                    <svg class="w-4 h-4 {{ $iconText }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <span class="font-semibold {{ $textClass }}">{{ $lembrete['nome'] }}</span>
                    <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold tracking-wider {{ $badgeClass }}">{{ $label }}</span>
                    <p class="{{ $textClass }} opacity-75 text-xs mt-0.5">
                        @if($isHoje)
                            Folha deste parceiro deve ser feita hoje.
                        @else
                            Folha deste parceiro vence amanha. Nao esqueca!
                        @endif
                    </p>
                </div>
            </div>

            <button onclick="dispensarLembrete('lembrete-{{ $index }}', this.closest('[data-key]').dataset.key)"
                    class="ml-4 flex-shrink-0 opacity-60 hover:opacity-100 transition {{ $textClass }}"
                    title="Dispensar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endforeach

</div>

<script>
(function () {
    // Hide alerts already dismissed today
    document.querySelectorAll('#lembretes-parceiros [data-key]').forEach(function (el) {
        if (sessionStorage.getItem(el.dataset.key) === '1') {
            el.remove();
        }
    });

    // Remove wrapper if all dismissed
    var wrapper = document.getElementById('lembretes-parceiros');
    if (wrapper && wrapper.querySelectorAll('[data-key]').length === 0) {
        wrapper.remove();
    }
})();

function dispensarLembrete(id, key) {
    sessionStorage.setItem(key, '1');
    var el = document.getElementById(id);
    if (el) {
        el.style.transition = 'opacity 0.3s';
        el.style.opacity = '0';
        setTimeout(function () {
            el.remove();
            var wrapper = document.getElementById('lembretes-parceiros');
            if (wrapper && wrapper.querySelectorAll('[data-key]').length === 0) {
                wrapper.remove();
            }
        }, 300);
    }
}
</script>
@endif
