@if(count($dados) >= 1)
@php
    $ii=0;
    $total = 0;
    $contagem = 1;
@endphp

<style>
    .plano-card {
        background: #141824;
        border: 1px solid rgba(249,115,22,0.35);
        border-radius: 10px;
        padding: 14px;
        width: 32%;
        min-width: 260px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 14px;
    }
    .plano-card-logo {
        background: #fff;
        border-radius: 6px;
        padding: 6px;
        width: 48%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .plano-card-logo img { max-width: 100%; max-height: 48px; object-fit: contain; }
    .plano-card-info { width: 48%; display: flex; flex-direction: column; justify-content: center; gap: 2px; }
    .plano-card-info p { margin: 0; font-size: 0.82rem; color: #c8d8f0; text-align: center; }
    .plano-card-info .plano-nome { font-weight: 600; color: #f97316; font-size: 0.85rem; }
    .plano-card-badges { display: flex; gap: 6px; }
    .plano-badge {
        flex: 1; text-align: center;
        background: #1a2030; border: 1px solid #252f44;
        border-radius: 5px; padding: 5px 4px;
        font-size: 0.72rem; color: #8a9bbb;
    }
    .plano-card-datas { display: flex; gap: 8px; align-items: flex-end; }
    .plano-data-field { display: flex; flex-direction: column; gap: 3px; flex: 1; }
    .plano-data-field label { font-size: 0.68rem; color: #f97316; font-weight: 500; }
    .plano-data-field input {
        background: #1a2030; border: 1px solid #252f44; border-radius: 5px;
        color: #dde6f5; font-size: 0.75rem; padding: 5px 7px; width: 100%;
        box-sizing: border-box; outline: none;
    }
    .plano-card-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
    .plano-card-table thead tr { background: #1a2035; }
    .plano-card-table thead th { color: #f97316; font-weight: 600; padding: 6px 8px; text-align: left; font-size: 0.72rem; letter-spacing: 0.04em; }
    .plano-card-table tbody tr:nth-child(even) { background: #171d2b; }
    .plano-card-table tbody td { color: #c8d8f0; padding: 5px 8px; border-bottom: 1px solid #1e2534; }
    .plano-card-table tfoot td { background: #1a2035; color: #f97316; font-weight: 700; padding: 7px 8px; text-align: right; font-size: 0.82rem; }
    .plano-card-table tfoot .edit-icon { color: #7eb8f7; cursor: pointer; margin-left: 6px; font-size: 0.78rem; }
    .nossos-planos-titulo { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #f97316; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid rgba(249,115,22,0.25); width: 100%; }
</style>

<div style="background:#141824;border:1px solid rgba(249,115,22,0.35);border-radius:10px;padding:16px 18px;margin-top:14px">
    <div class="nossos-planos-titulo">Nossos Planos</div>
    <div style="display:flex;flex-wrap:wrap;gap:14px">

        @for($i=0;$i < count($dados); $i++)
            @php $contagem = $i + 1; @endphp
            @if($dados[$i]->card == $card_inicial)
                @if($ii==0)
                    <input type="hidden" name="administradora" id="administradora" value="{{$dados[$i]->administradora}}">
                    <div class="plano-card valores-acomodacao" data-acomodacao="{{$dados[$i]->acomodacao}}">

                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="plano-card-logo">
                                <img src="{{asset($dados[$i]->logo)}}" alt="" width="100%">
                            </div>
                            <div class="plano-card-info">
                                <p class="plano-nome">{{$dados[$i]->planos}}</p>
                                <p class="tipo">{{$dados[$i]->acomodacao}}</p>
                            </div>
                        </div>

                        <div class="plano-card-badges">
                            <div class="plano-badge">{{$dados[$i]->coparticipacao}}</div>
                            <div class="plano-badge">{{$dados[$i]->odonto}}</div>
                        </div>

                        <div class="plano-card-datas">
                            <div class="plano-data-field">
                                <label>Data Vigência</label>
                                <input type="date" name="vigente" id="vigente_{{strtolower($dados[$i]->acomodacao)}}" value="" class="vigente">
                            </div>
                            <div class="plano-data-field">
                                <label>Data Boleto</label>
                                <input type="date" name="boleto" id="boleto_{{strtolower($dados[$i]->acomodacao)}}" value="" class="boleto">
                            </div>
                            <div class="plano-data-field">
                                <label>Valor Adesão</label>
                                <input type="text" name="adesao" id="adesao_{{strtolower($dados[$i]->acomodacao)}}" placeholder="R$" class="valor_adesao">
                            </div>
                        </div>

                        <table class="plano-card-table">
                            <thead>
                                <tr>
                                    <th>Faixas</th>
                                    <th>Vidas</th>
                                    <th>Valor</th>
                                    <th style="text-align:right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                @endif
                @php
                    $ii++;
                    $total += $dados[$i]->valor * $dados[$i]->quantidade;
                    $acomodacao = $dados[$i]->acomodacao;
                @endphp
                    <tr>
                        <td>{{$dados[$i]->faixa}}</td>
                        <td>{{$dados[$i]->quantidade}}</td>
                        <td>{{number_format($dados[$i]->valor,2,",",".")}}</td>
                        <td style="text-align:right">{{number_format($dados[$i]->valor * $dados[$i]->quantidade,2,",",".")}}</td>
                    </tr>

            @else
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        R$ <span class="aqui_total_change">{{number_format($total,2,",",".")}}</span>
                                        <i class="fas fa-pen fa-sm edit-icon editar_valor_coletivo" data-btn-acomodacao="{{$acomodacao}}"></i>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                @php $card_inicial = $dados[$i]->card; $i--; $ii=0; $total=0; @endphp
            @endif
        @endfor

        @if($contagem == $quantidade)
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        R$ <span class="aqui_total_change">{{number_format($total,2,",",".")}}</span>
                                        <i class="fas fa-pen fa-sm edit-icon editar_valor_coletivo" data-btn-acomodacao="{{$acomodacao}}"></i>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
        @endif

    </div>
</div>

<input type="hidden" name="valor" id="valor" value="">
<input type="hidden" name="data_vigencia" id="data_vigencia" value="">
<input type="hidden" name="data_boleto" id="data_boleto" value="">
<input type="hidden" name="valor_adesao" id="valor_adesao" value="">
<input type="hidden" name="acomodacao" id="acomodacao" value="">

<div id="btn_submit" style="clear:both;width:100%;"></div>

@else

<div style="background:#141824;border:1px solid rgba(249,115,22,0.35);border-radius:10px;padding:28px 18px;margin-top:14px;text-align:center">
    <svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;color:#f97316;margin:0 auto 12px;display:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <p style="color:#f97316;font-weight:700;font-size:0.9rem;margin:0 0 4px">Nenhuma tabela encontrada</p>
    <p style="color:#6a80a8;font-size:0.8rem;margin:0">Verifique os filtros selecionados e tente novamente.</p>
</div>

@endif
<script>
    $(function(){
        $('.valor_adesao').mask("#.##0,00", {reverse: true});
    });
</script>
