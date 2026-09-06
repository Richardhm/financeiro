{{-- resources/views/folha/america/index.blade.php --}}
<x-app-layout>
@section('css')
    <link rel="stylesheet" href="{{ asset('css/estilo-financeiro.css') }}"/>
@endsection

    {{-- Seleção inicial do mês (caso não tenha um mês selecionado ainda) --}}
    @if(!$folhaEmAberto)
        <x-folha.mes-selecao :proximosMeses="$proximosMeses" :mesesUsados="$mesesUsados ?? []" />

    @else
        <!-- Modal de Clientes -->
        <x-folha.modal-clientes />

        <!---Modal detalhes do cliente--->
        <x-folha.modal-detalhes-cliente />

        <!---Modal editar comissao--->
        <x-folha.modal-editar-comissao />

        <!--Conteudo-->
        <x-folha.conteudo :resumoPorPlano="$resumoPorPlano" :resumoGeral="$resumoGeral" :corretores="$corretores" :mesAtual="$mesAtual" :faixasClt="$faixasClt" :vidasClt="$vidasClt ?? collect()" :dadosMes="$dadosMes ?? null" />
    @endif
    @section('scripts')
        <script src="{{asset('js/folha-script.js')}}"></script>
        <script src="{{asset('js/folha-jquery.js')}}"></script>
        <script src="{{asset('js/renderizar-detalhe.js')}}"></script>
    @endsection
</x-app-layout>
