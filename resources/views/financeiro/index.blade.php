<x-app-layout>
    @section('css')
       <link rel="stylesheet" href="{{asset('css/estilo-financeiro.css')}}"/>
    @endsection
    <input type="hidden" id="janela_atual" value="aba_individual">

        <!-- Modal -->
        <script>

            var urlGeralIndividualPendentes = "{{ route('financeiro.individual.geralIndividualPendentes') }}";
            var urlGeralColetivoPendentes = "{{ route('financeiro.coletivo.em_geral') }}";
            var urlPdfDownloadBase = "{{ url('/contratos') }}";
            var editarAdministradoraChange = "{{route('financeiro.administradora.change')}}"
            var urlGeralEmpresarialPendentes = "{{ route('contratos.listarEmpresarial.listarContratoEmpresaPendentes') }}";
            var listarOdonto = "{{ route('odonto.listar') }}";
            var financeiroSincroniza = "{{route('financeiro.sincronizar')}}";
            var atualizarIndividual = "{{route('financeiro.sincronizar.baixas.jaexiste')}}";

            var selectIndividual = "{{route('financeiro.select.individual')}}";

            var empresarialPlanilha = "{{route('financeiro.empresarial.confirmacao')}}";

            var estornoIndividual = "{{route('financeiro.estorno.individual')}}";
            var listarEstorno = "{{route('estorno.listar')}}";


            var adiantamentoIndividual = "{{route('financeiro.adiantamento.confirmar')}}";

            var confirmarEstorno = "{{route('estorno.confirmar')}}";
            var confirmarParcela = "{{route('parcelas.confirmar')}}";

            var cancelarIndividual = "{{route('financeiro.sincronizar.cancelados')}}";
            var individualFinanceiroInicializar = "{{route('financeiro.modal.contrato.individual')}}";
            var coletivoFinanceiroInicializar = "{{route('financeiro.modal.contrato.coletivo')}}";
            var empresarialFinanceiroInicializar = "{{route('financeiro.modal.contrato.empresarial')}}";
            var urlBaixaColetivo        = "{{route('financeiro.baixa.data')}}";

            var urlBaixaIndividual = "{{route('financeiro.baixa.individual')}}";

            var desfazerColetivo        = "{{route('desfazer.tarefa.coletivo')}}";
            var desfazerIndividual        = "{{route('desfazer.tarefa.individual')}}";

            var cadastrarOdonto         = "{{ route('odonto.create') }}";
            var emAnaliseAjax           = "{{route('financeiro.analise.coletivo')}}";
            var emissaoBoleto           = "{{route('financeiro.analise.boleto')}}";
            var empresarialEmAnalise    = "{{route('financeiro.analise.empresarial')}}";
            var empresarialDataBaixa    = "{{route('financeiro.baixa.data.empresarial')}}";
            var changecorretor          = "{{route('financeiro.changeFinanceiro')}}";
            var changecorretorColetivo  = "{{route('financeiro.changeFinanceiroColetivo')}}";
            var changecorretorEmpresarial  = "{{route('financeiro.changeFinanceiroEmpresarial')}}";
            var changeValoresCorretorEmpresarial  = "{{route('financeiro.changeValoresFinanceiroEmpresarial')}}";
            var financeiroCanceladoColetivo = "{{route('financeiro.contrato.cancelados')}}";
            var excluirOdonto           = "{{route('odonto.excluir')}}";
            var excluirColetivo         = "{{route('financeiro.excluir.cliente')}}";
            var excluirEmpresarial      = "{{route('financeiro.excluir.empresarial')}}";
            var cancelarEmpresarial      = "{{route('financeiro.cancelar.empresarial')}}";
            var mudarCampoIndividual = "{{route('financeiro.editar.campoIndividualmente')}}";
            var editarCampoColetivo = "{{route('financeiro.editar.campoColetvivo')}}";
            var editarCampoEmpresarial = "{{route('financeiro.editar.campoEmpresarial')}}";
            var updateComissaoLancadaEmpresarial = "{{route('financeiro.empresarial.update.comissao.lancada')}}";
            var table;
            var table_individual;
            var parcelaSelecionada;
            var tableodonto;
            var tableempresarial;
        </script>
            <div style="width:95%;margin:0 auto;">
            <ul class="list_abas">
                <li data-id="aba_individual" class="ativo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    Individual
                </li>
                <li data-id="aba_coletivo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    Coletivo
                </li>
                <li data-id="aba_empresarial">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    Empresarial
                </li>
                @if(auth()->user()->corretora_id == 1)
                    <li data-id="aba_odonto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                        Odonto
                    </li>
                @endif
                <li class="ml-3" data-id="aba_estorno">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                    Estorno
                </li>
            </ul>
    </div>
                <x-upload-modal></x-upload-modal>
                <x-upload-modal-empresarial></x-upload-modal-empresarial>
                <x-upload-atualizar></x-upload-atualizar>
                <x-upload-estorno></x-upload-estorno>

                <x-upload-adiantamento></x-upload-adiantamento>
                <x-upload-parcelas></x-upload-parcelas>




    <div id="modalEstorno" class="fixed inset-0 flex items-center justify-center z-[9999] hidden bg-black/60">
        <div class="w-full max-w-sm mx-4 rounded-xl overflow-hidden shadow-2xl" style="background:#1e1e1e;border:1px solid #2a3d55;">
            <div class="flex items-center justify-between px-5 py-3" style="background:#0e1a28;border-bottom:1px solid #2a3d55;">
                <h5 class="text-xs font-bold uppercase tracking-widest" style="color:#e0e0e0;">Inserir Valor do Estorno</h5>
            </div>
            <div class="px-5 py-5 space-y-4">
                <div>
                    <label for="valor_estorno" class="block text-[10px] font-bold uppercase tracking-widest mb-2" style="color:#3d7ab5;">Valor do Estorno</label>
                    <input type="text" name="valor_estorno" id="valor_estorno" class="block w-full text-sm rounded-lg px-3 py-2" style="background:#141414;border:1px solid #2a3d55;color:#e0e0e0;" placeholder="Digite o valor">
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button id="cancelarModal" class="px-4 py-2 text-xs font-semibold uppercase rounded-lg" style="background:#252525;color:#ccc;border:1px solid #2a3d55;">Cancelar</button>
                    <button id="confirmarModal" class="px-4 py-2 text-xs font-semibold uppercase rounded-lg" style="background:#1a3a5c;color:#e0e0e0;border:1px solid #2a3d55;">Confirmar</button>
                </div>
            </div>
        </div>
    </div>






        <div id="mensagem_erro" class="hidden bg-red-500 text-white px-4 py-3 rounded-lg mt-3"></div>

        <div id="uploadCancelados" class="fixed inset-0 flex items-center justify-center z-[9999] hidden bg-black/60">
            <div class="w-full max-w-md mx-4 rounded-xl overflow-hidden shadow-2xl" style="background:#1e1e1e;border:1px solid #2a3d55;">
                <div class="flex items-center justify-between px-5 py-3" style="background:#0e1a28;border-bottom:1px solid #2a3d55;">
                    <h5 class="text-xs font-bold uppercase tracking-widest" style="color:#e0e0e0;">Upload Cancelados</h5>
                    <button type="button" class="close-modal-cancelados text-2xl font-bold leading-none transition" style="color:#888;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#888'">&times;</button>
                </div>
                <div class="px-5 py-5">
                    <form action="" method="POST" name="formulario_cancelados" id="formulario_cancelados" enctype="multipart/form-data">
                        @csrf
                        <label for="arquivo_cancelados" class="block text-[10px] font-bold uppercase tracking-widest mb-2" style="color:#3d7ab5;">Arquivo</label>
                        <input type="file" name="arquivo_cancelados" id="arquivo_cancelados" class="block w-full text-sm rounded-lg px-3 py-2 cursor-pointer" style="background:#141414;border:1px solid #2a3d55;color:#e0e0e0;">
                    </form>
                </div>
            </div>
        </div>




        <!-- O container de loading com 3 pontinhos -->
        <div id="loading-overlay" class="ocultar" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; justify-content: center; align-items: center;">
            <div class="dots-loading">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div id="myModalIndividual" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] z-40"></div>
            <!-- Conteúdo da Modal -->
            <div class="relative w-11/12 rounded-lg shadow-3xl p-2 z-50">
                <!-- Botão Fechar no Topo -->
                <div id="modalLoaderIndividual" class="flex justify-center items-center h-64">
                    <div class="dot-flashing">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <!-- Borda Animada -->
                <div class="relative p-1 rounded-lg animate-border overflow-hidden content-modal-individual hidden">
                </div>
            </div>
        </div>

        <div id="myModalColetivo" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] z-40"></div>
            <!-- Conteúdo da Modal -->
            <div class="relative w-11/12 rounded-lg shadow-3xl p-2 z-50">
                <!-- Botão Fechar no Topo -->
                <div id="modalLoader" class="flex justify-center items-center h-64">
                    <div class="dot-flashing">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <!-- Borda Animada -->
                <div class="relative p-1 rounded-lg animate-border overflow-hidden content-modal-coletivo hidden">
                </div>
            </div>
        </div>

        <div id="myModalCreateOdonto" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]" onclick="closeModalOnClickOutside(event)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl relative">
                <!-- Botão para fechar a modal -->
                <button onclick="closeModalOdonto()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl font-bold">
                    &times;
                </button>

                <!-- Conteúdo da Aba Cadastrar -->
                <div id="contentCadastrar" class="p-4">
                    <div class="flex flex-col space-y-4">
                        <div>
                            <label for="user_id" class="block mb-2">Corretor</label>
                            <select name="user_id" id="user_id" class="border rounded px-4 py-2 w-full">
                                <option value="">--Escolher Corretor--</option>
                                @foreach($users as $u)
                                    <option value="{{$u->id}}">{{$u->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="nome" class="block mb-2">Cliente</label>
                            <input type="text" name="nome" id="nome" class="border rounded px-4 py-2 w-full">
                        </div>

                        <div>
                            <label for="valor" class="block mb-2">Valor</label>
                            <input type="text" name="valor" id="valor" class="border rounded px-4 py-2 w-full">
                        </div>

                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full cadastrar_odonto">Cadastrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="myModalEmpresarial" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] z-40"></div>
            <!-- Conteúdo da Modal -->
            <div class="relative w-11/12 rounded-lg shadow-3xl p-2 z-50">
                <!-- Botão Fechar no Topo -->
                <div id="modalLoaderEmpresa" class="flex justify-center items-center h-64">
                    <div class="dot-flashing">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <!-- Borda Animada -->
                <div class="relative p-1 rounded-lg animate-border overflow-hidden content-modal-empresarial hidden">
                </div>
            </div>
        </div>



        <section class="conteudo_abas mt-2" style="width:95%;margin:0 auto;">
            <x-aba-individual :usuariosindividuais="$usuarios_individuais"></x-aba-individual>
            <x-aba-coletivo></x-aba-coletivo>
            <x-aba-empresarial></x-aba-empresarial>
            @if(auth()->user()->corretora_id == 1)
                <x-aba-odonto></x-aba-odonto>
            @endif
            <x-aba-estorno></x-aba-estorno>

        </section>

    <script>


        function closeModalOdonto() {
            document.getElementById('myModalCreateOdonto').classList.add('hidden');
        }

        function closeModalOnClickOutside(event) {
            const modal = document.getElementById('myModalCreateOdonto');
            if (event.target === modal) {
                closeModalOdonto();
            }
        }




        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownOptions = document.getElementById('dropdownOptions');
        //const dropdownButtonText = dropdownButton.querySelector('span'); // Apenas o span de texto

        function showTab(tab) {
            var contentListar = document.getElementById('contentListar');
            var contentCadastrar = document.getElementById('contentCadastrar');
            var tabListar = document.getElementById('tabListar');
            var tabCadastrar = document.getElementById('tabCadastrar');

            if (tab === 'listar') {
                contentListar.classList.remove('hidden');
                contentCadastrar.classList.add('hidden');
                tabListar.classList.add('border-blue-500', 'text-blue-500');
                tabCadastrar.classList.remove('border-blue-500', 'text-blue-500');
            } else {
                contentListar.classList.add('hidden');
                contentCadastrar.classList.remove('hidden');
                tabListar.classList.remove('border-blue-500', 'text-blue-500');
                tabCadastrar.classList.add('border-blue-500', 'text-blue-500');
            }
        }
        // dropdownButton.addEventListener('click', () => {
        //     dropdownOptions.classList.toggle('hidden');
        // });

        // dropdownOptions.addEventListener('click', (event) => {
        //     if (event.target.tagName === 'LI') {
        //         const selectedOption = event.target.textContent;
        //         const value = event.target.getAttribute('data-value');
        //         //dropdownButtonText.textContent = selectedOption; // Atualiza apenas o texto do span
        //         dropdownOptions.classList.add('hidden');
        //         inicializarIndividual(value);
        //     }
        // });


        $(document).ready(function(){

            $('#valor').mask('#.##0,00', {reverse: true});
            $('#valor_estorno').mask('#.##0,00', {reverse: true});

            // $('#valor_contrato').mask('#.##0,00', {reverse: true});
            // $('#valor_adesao').mask('#.##0,00', {reverse: true});
            // $('#desconto_corretor').mask('#.##0,00', {reverse: true});
            // $('#desconto_corretora').mask('#.##0,00', {reverse: true});

            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            }

            function totalMes() {
                return $("#select_usuario_individual").val();
            }

            String.prototype.ucWords = function () {
                let str = this.toLowerCase()
                let re = /(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g
                return str.replace(re, s => s.toUpperCase())
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $("body").on('keydown', '.next', function(e) {
                e.preventDefault(); // Impede qualquer entrada de texto no campo
            });

            var currentStep = 1; // Etapa inicial

            $('.step-btn').on('click', function() {
                let step = $(this).data('step');
                if(step === currentStep) {
                    currentStep++;
                    $('#step-' + currentStep).show(); // Exibe a próxima etapa
                    if (currentStep >= 3) {
                        $('#step-' + currentStep + '-date').prop('disabled', false); // Habilita o campo de data
                    }
                    $(this).prop('disabled', true);
                } else {
                    alert('Por favor, complete a etapa anterior antes de prosseguir.');
                }
            });

            $('input[type="date"]').on('change', function() {
                let step = parseInt($(this).attr('id').split('-')[1]); // Pega o número da etapa
                if (step === currentStep) {
                    currentStep++;
                    $('#step-' + currentStep).show();
                    $('#step-' + currentStep + '-date').prop('disabled', false);
                } else {
                    alert('Por favor, complete a etapa anterior antes de prosseguir.');
                }
            });
        });
    </script>

        @section('scripts')

            <script src="{{asset('js/financeiro-arquivo.js')}}"></script>
            <script src="{{asset('js/financeiro-inicializar-individual.js')}}"></script>
            <script src="{{asset('js/financeiro-inicializar-coletivo.js')}}"></script>
            <script src="{{asset('js/financeiro-inicializar-empresarial.js')}}"></script>

            <script src="{{asset('js/financeiro-inicializar-odonto.js')}}"></script>
            <script src="{{asset('js/financeiro-inicializar-estorno.js')}}"></script>

            <script src="{{asset('js/financeiro-click-menus.js')}}"></script>
            <script src="{{asset('js/financeiro-change-menus.js')}}"></script>

            <script src="{{asset('js/financeiro-parametro-url.js')}}"></script>
        @endsection







</x-app-layout>
