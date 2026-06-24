<x-app-layout>

    <!-- Modal de Clientes -->
    <x-folha.modal-clientes />

    <!---Modal detalhes do cliente--->
    <x-folha.modal-detalhes-cliente />

    <!---Modal editar comissao--->
    <x-folha.modal-editar-comissao />

    <!--Conteudo-->
    <x-folha.conteudo-parceiros
        :resumoPorPlano="$resumoPorPlano"
        :resumoGeral="$resumoGeral"
        :corretores="$corretores"
        :mesAtual="$mesAtual"
        :confirmadosPorParceiro="$confirmadosPorParceiro"
    />

    @section('scripts')
        <script>
            var PARCEIROS_MODE = true;
            var URL_FINALIZAR_PARCEIRO_BASE = "{{ url('/folha/folha-parceiros') }}";
        </script>
        <script src="{{asset('js/folha-script.js')}}"></script>
        <script src="{{asset('js/folha-jquery.js')}}"></script>
        <script src="{{asset('js/renderizar-detalhe.js')}}"></script>
    @endsection

</x-app-layout>
