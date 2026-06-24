<x-app-layout>

    <div class="flex" style="align-items: flex-start;">

        <!-- FORM: Editar/Cadastrar (lado esquerdo) -->
        <div class="w-[35%] mt-2 ml-2 rounded-lg p-2 text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
            <div>
                <h2 class="text-2xl text-center">Editar/Cadastrar</h2>
            </div>

            <div class="flex justify-between">
                <div id="imagem_aqui"></div>
                <div class="interruptor-container">
                    <div id="interruptor" class="interruptor"></div>
                </div>
            </div>

            <div class="flex w-full justify-between mb-3">
                <label for="nome" class="flex w-[48%] flex-wrap">
                    <span class="flex w-full">Nome</span>
                    <input type="text" id="name" name="name" placeholder="Nome" class="w-full rounded-lg text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white">
                </label>

                <label for="email" class="flex w-[48%] flex-wrap">
                    <span class="flex w-full">Email</span>
                    <input type="text" id="email" name="email" placeholder="Email" class="w-full rounded-lg text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white">
                </label>
            </div>

            <div class="flex w-full justify-between mb-3">
                <label for="celular" class="flex w-[48%] flex-wrap">
                    <span class="flex w-full">Celular</span>
                    <input type="text" id="celular" name="celular" placeholder="Celular" class="w-full rounded-lg text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white">
                </label>
                <label for="tipo_contrato" class="flex w-[48%] flex-wrap">
                    <span class="flex w-full">Tipo de Contrato</span>
                    <select id="tipo_contrato" name="tipo_contrato" class="w-full rounded-lg bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white text-white">
                        <option value="" class="text-black">Escolher Tipo</option>
                        <option value="pj" class="text-black">PJ</option>
                        <option value="clt" class="text-black">CLT</option>
                        <option value="parceiro" class="text-black">Parceiro</option>
                    </select>
                </label>
            </div>

            <fieldset class="border-2 border-white p-3 rounded-lg mt-3">
                <legend class="mx-auto text-2xl">Codigo Vendedor</legend>
                <div class="flex w-[78%] justify-between flex-wrap">
                    <div class="w-[46%]">
                        <select name="corretora_id[]" class="w-full rounded-lg bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white" id="corretora_id">
                            <option value="">Escolher a cidade</option>
                            <option value="1" class="text-black">Anápolis - 8892</option>
                            <option value="2" class="text-black">Goiânia  - 5429</option>
                            <option value="8" class="text-black">Brasília - 9995</option>
                        </select>
                    </div>
                    <div class="w-[46%]">
                        <input type="text" name="codigo_vendedor[]" placeholder="Codigo Vendedor" id="codigo_vendedor" class="w-full rounded-lg text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-whit">
                    </div>
                </div>

                <div id="form-container" class="w-[90%]"></div>

                <div class="w-full flex mt-3">
                    <button type="button" id="add-row" class="bg-green-500 text-white text-3xl px-4 py-1 rounded-lg w-full">+</button>
                </div>

                <h2 class="my-3">Listagem dos Codigos</h2>
                <div class="list_codigo">
                    <div class="cidade_item"></div>
                </div>
            </fieldset>

            <div class="flex gap-2 w-full mt-3">
                <button type="button" class="text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white font-medium rounded-lg text-sm px-5 py-2.5 mb-2 flex-1 salvar_user">Salvar</button>
                <button type="button" class="text-white bg-red-500/40 border border-red-400 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 limpar_form">Limpar</button>
            </div>
        </div>

        <!-- Lado direito: botões + tabela empilhados -->
        <div class="w-[63%] mt-2 ml-2 flex flex-col gap-2">

            <!-- Card de filtros -->
            <div class="rounded-lg px-3 py-2 text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] flex gap-2 flex-wrap items-center">
                <button type="button" id="btn-todos" class="text-xs font-bold px-3 py-1.5 rounded text-white transition-all" style="background:#64748b">Todos</button>
                <div style="border-left:1px solid rgba(255,255,255,0.3);height:22px;margin:0 4px;"></div>
                <button type="button" class="btn-filtro-tipo text-xs font-bold px-3 py-1.5 rounded text-white transition-all" style="background:#3b82f6" data-tipo="pj">PJ <span id="count-pj"></span></button>
                <button type="button" class="btn-filtro-tipo text-xs font-bold px-3 py-1.5 rounded text-white transition-all" style="background:#22c55e" data-tipo="clt">CLT <span id="count-clt"></span></button>
                <button type="button" class="btn-filtro-tipo text-xs font-bold px-3 py-1.5 rounded text-white transition-all" style="background:#a855f7" data-tipo="parceiro">Parceiros <span id="count-parceiro"></span></button>
                <div style="border-left:1px solid rgba(255,255,255,0.3);height:22px;margin:0 4px;"></div>
                <button type="button" class="btn-filtro-ativo text-xs font-bold px-3 py-1.5 rounded text-white transition-all" style="background:#10b981" data-ativo="1">Ativo <span id="count-ativo"></span></button>
                <button type="button" class="btn-filtro-ativo text-xs font-bold px-3 py-1.5 rounded text-white transition-all" style="background:#ef4444" data-ativo="0">Desativado <span id="count-desativado"></span></button>
            </div>

            <!-- Card da tabela -->
            <div class="rounded-lg p-1 text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
                <table class="table table-sm listar_user" id="listar_usuarios">
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th style="text-align: center;">Ligar/Desligar</th>
                        <th style="text-align: center;">Tipo</th>
                        <th style="text-align: center;">Editar</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

@section('css')
    <style>
        table.dataTable tbody td {
            font-size: 0.85em !important;
            padding: 0px !important;
        }

        table.dataTable thead th {
            font-size: 0.85em !important;
            padding: 0px !important;
        }
    </style>
@endsection



@section('scripts')
    <script src="{{asset('js/jquery.mask.min.js')}}"></script>
    <script>
        $(function(){
            const interruptor = document.getElementById("interruptor");

            interruptor.addEventListener("click", () => {
                interruptor.classList.toggle("ligado");
            });
            $("#celular").mask("(00) 0 0000-0000");
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let cidadesUsadas = [];

            function atualizarDisponibilidadeCidades() {
                $("select[name='corretora_id[]']").each(function () {
                    let valorAtual = $(this).val();
                    $(this).find('option[value!=""]').each(function () {
                        let val = $(this).val();
                        $(this).prop('disabled', cidadesUsadas.includes(val) && val !== valorAtual);
                    });
                });
            }

            function limparForm() {
                $('#name').val('');
                $('#email').val('');
                $('#celular').val('');
                $('#codigo_vendedor').val('');
                $('#imagem_aqui').html('');
                $('#form-container').empty();
                $('#corretora_id').val('');
                $('#tipo_contrato').val('');
                $('.list_codigo').empty();
                $('#listar_usuarios tbody tr').css({'background': '', 'box-shadow': ''});
                cidadesUsadas = [];
                atualizarDisponibilidadeCidades();
            }

            $("body").on('click', '.limpar_form', function () {
                limparForm();
            });

            $("#add-row").on("click", function () {
                let d = (v) => cidadesUsadas.includes(v) ? 'disabled' : '';
                $("#form-container").append(`
            <div class="flex w-full justify-between flex-wrap items-center mt-2">
                <div class="w-[40%]">
                    <select name="corretora_id[]" class="w-full rounded-lg bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white corretora-select">
                        <option value="">Escolher a cidade</option>
                        <option value="1" class="text-black" ${d('1')}>Anápolis - 8892</option>
                        <option value="2" class="text-black" ${d('2')}>Goiânia - 5429</option>
                        <option value="8" class="text-black" ${d('8')}>Brasília - 9995</option>
                    </select>
                </div>
                <div class="w-[40%]">
                    <input type="text" name="codigo_vendedor[]" placeholder="Código Vendedor" class="w-full rounded-lg text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white">
                </div>
                <button type="button" class="remove-row bg-red-500 text-white px-3 py-1 rounded-lg ml-4 w-[5%]">x</button>
            </div>
        `);
            });

            $("body").on("click", ".remove-row", function () {
                $(this).parent().remove();
            });

            $("body").on('click','.remover_user',function(){
                let id = $(this).attr('id');
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Esta ação não pode ser desfeita!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, deletar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('destroy.corretor') }}",
                            method: "POST",
                            data: { id: id },
                            success: function(res) {
                                Swal.fire('Deletado!', 'O usuário foi deletado com sucesso.', 'success').then(()=>{
                                    $(".listar_user").DataTable().ajax.reload();
                                });
                            },
                            error: function(err) {
                                Swal.fire('Erro!', 'Não foi possível deletar o usuário.', 'error');
                                console.log("Erroorr ", err);
                            }
                        });
                    }
                });
            });

            $("body").on('click','.ver_info',function(e){
               e.preventDefault();

                $('#listar_usuarios tbody tr').css({'background': '', 'box-shadow': ''});
                $(this).closest('tr').css({'background': 'rgba(234,179,8,0.25)', 'box-shadow': 'inset 3px 0 0 #eab308'});

                let id            = $(this).attr('data-id');
                let celular       = $(this).attr('data-celular');
                let image         = $(this).attr('data-imagem');
                let nome          = $(this).attr('data-nome');
                let email         = $(this).attr('data-email');
                let tipoContrato  = $(this).attr('data-tipo-contrato') || 'pj';

                celular = celular != undefined ? celular : "";
                $("#name").val(nome);
                $("#celular").val(celular);
                $("#email").val(email);
                $("#tipo_contrato").val(tipoContrato);

                if(image && image !== 'null') {
                    let imgTest = new Image();
                    imgTest.src = image;
                    let imageLoaded = false;

                    imgTest.onload = function() {
                        imageLoaded = true;
                        $("#imagem_aqui").empty();
                        let imgElement = $("<img>").attr("src", image).attr("alt", "Imagem do usuário")
                            .addClass("max-w-[100px] w-[100px] max-h-[100px] h-[100px] rounded-full shadow-lg border border-gray-300 object-cover");
                        $("#imagem_aqui").append(imgElement);
                    };

                    imgTest.onerror = function() {
                        if (!imageLoaded) {
                            $("#imagem_aqui").text("Corretor sem imagem");
                        }
                    };
                } else {
                    $("#imagem_aqui").text("Foto");
                }

                $.ajax({
                    url:"{{route('corretor.show')}}",
                    method:"POST",
                    data: {id:id},
                    success:function(res) {
                        $(".list_codigo").empty();
                        cidadesUsadas = [];

                        if(res.codigo.length >= 1) {
                            res.codigo.forEach(function (codigo) {
                                if(codigo.cidade) {
                                    cidadesUsadas.push(String(codigo.tabela_origens_id));
                                    $(".list_codigo").append(`
                                        <div class="cidade_item flex items-center gap-2 mb-2" data-tabela-id="${codigo.tabela_origens_id}">
                                            <span class="text-sm font-bold w-20 shrink-0">${codigo.cidade.nome}</span>
                                            <input type="text"
                                                   class="codigo-edit-input flex-1 rounded text-white bg-white/10 border border-white/30 px-2 py-1 text-sm"
                                                   value="${codigo.codigo_vendedor}"
                                                   data-id="${codigo.id}">
                                            <button type="button"
                                                    class="salvar-codigo shrink-0 text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded"
                                                    data-id="${codigo.id}">Salvar</button>
                                            <svg data-id="${codigo.id}" data-tabela-id="${codigo.tabela_origens_id}"
                                                 xmlns="http://www.w3.org/2000/svg" style="color:red;cursor:pointer;"
                                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                 class="size-5 shrink-0 excluir_codigo">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    `);
                                }
                            });
                        } else {
                            $(".list_codigo").append(`
                                <div class="cidade_item">
                                    <strong>Nenhum código cadastrado</strong>
                                </div>
                            `);
                        }

                        atualizarDisponibilidadeCidades();
                    }
                });

               return false;
            });


            function inicializarTable() {
                $(".listar_user").DataTable({
                    dom: '<"flex justify-between"<"#title_individual">ftr><t><"flex justify-between"lp>',
                    language: {
                        "search": "Pesquisar",
                        "paginate": {
                            "next": "Próx.",
                            "previous": "Ant.",
                            "first": "Primeiro",
                            "last": "Último"
                        },
                        "emptyTable": "Nenhum registro encontrado",
                        "info": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                        "infoEmpty": "Mostrando 0 até 0 de 0 registros",
                        "infoFiltered": "(Filtrados de _MAX_ registros)",
                        "infoThousands": ".",
                        "loadingRecords": "Carregando...",
                        "processing": "Processando...",
                        "lengthMenu": "Exibir _MENU_ por página"
                    },
                    processing: true,
                    ajax: {
                        "url":"{{ route('corretores.list') }}",
                        "dataSrc": ""
                    },
                    "pageLength": 20,
                    "lengthMenu": [20, 50, 100],
                    "ordering": false,
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    columns: [
                        {data:"name",   name:"name"},
                        {data: null,    name: "toggle",     orderable: false, searchable: false},
                        {data: null,    name: "tipo_badge", orderable: false, searchable: false},
                        {data:"id",     name:"id"}
                    ],
                    "columnDefs": [
                        {
                            "targets": 3,
                            "createdCell": function (td, cellData, rowData, row, col) {
                                let id           = cellData;
                                let nome         = rowData.name;
                                let celular      = rowData.celular ?? "";
                                let imagem       = rowData.image;
                                let email        = rowData.email;
                                let tipoContrato = rowData.tipo_contrato || 'pj';
                                $(td).html(`<div class='text-right text-white flex justify-center'>
                                        <a href="#" class="text-white ver_info" data-email="${email}" data-id="${id}" data-nome="${nome}" data-celular="${celular}" data-imagem="${imagem}" data-tipo-contrato="${tipoContrato}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 div_info">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                    </div>
                                `);
                            }
                        },
                        {
                            "targets": 1,
                            "createdCell": function (td, cellData, rowData, row, col) {
                                let id     = rowData.id;
                                let status = rowData.ativo == 1 ? 'checked' : '';
                                $(td).html(`
                                    <label class="switch">
                                        <input type="checkbox" class="toggle-switch" data-id="${id}" ${status}>
                                        <span class="slider"></span>
                                    </label>
                                `);
                            }
                        },
                        {
                            "targets": 2,
                            "createdCell": function (td, cellData, rowData, row, col) {
                                let tipo        = rowData.tipo_contrato || 'pj';
                                let badgeColors = {clt:'#22c55e', parceiro:'#a855f7', pj:'#3b82f6'};
                                let badgeLabels = {clt:'CLT', parceiro:'Parceiro', pj:'PJ'};
                                let color       = badgeColors[tipo] || '#3b82f6';
                                let label       = badgeLabels[tipo] || 'PJ';
                                $(td).html(`
                                    <div class="flex items-center justify-center">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded" style="background:${color};color:#fff;">${label}</span>
                                    </div>
                                `);
                            }
                        }
                    ],
                    "initComplete": function( settings, json ) {
                        $('.dataTables_filter input').addClass('texto-branco');
                        $('#title_individual').html("<h4 style='font-size:1em;margin-top:10px;margin-left:5px;'>Listagem</h4>");
                        atualizarContadores(json);
                        atualizarVisualizacaoBotoes();
                    },
                    "drawCallback": function( settings ) {},
                    footerCallback: function (row, data, start, end, display) {}
                });
            }
            let filtroTipo  = '';
            let filtroAtivo = '';

            function atualizarContadores(dados) {
                let c = { pj:0, clt:0, parceiro:0, ativo:0, desativado:0 };
                dados.forEach(function(u) {
                    let tipo = u.tipo_contrato || 'pj';
                    if (c[tipo] !== undefined) c[tipo]++;
                    if (u.ativo == 1) c.ativo++; else c.desativado++;
                });
                $('#count-pj').text('(' + c.pj + ')');
                $('#count-clt').text('(' + c.clt + ')');
                $('#count-parceiro').text('(' + c.parceiro + ')');
                $('#count-ativo').text('(' + c.ativo + ')');
                $('#count-desativado').text('(' + c.desativado + ')');
            }

            function atualizarVisualizacaoBotoes() {
                // Todos: sempre colorido
                $('#btn-todos').css({'opacity':'1','box-shadow':'none'});

                // Tipo
                if (filtroTipo) {
                    $('.btn-filtro-tipo').css({'opacity':'0.4','box-shadow':'none'});
                    $('.btn-filtro-tipo[data-tipo="' + filtroTipo + '"]').css({'opacity':'1','box-shadow':'0 0 0 2px #fff'});
                } else {
                    $('.btn-filtro-tipo').css({'opacity':'1','box-shadow':'none'});
                }

                // Ativo
                if (filtroAtivo !== '') {
                    $('.btn-filtro-ativo').css({'opacity':'0.4','box-shadow':'none'});
                    $('.btn-filtro-ativo[data-ativo="' + filtroAtivo + '"]').css({'opacity':'1','box-shadow':'0 0 0 2px #fff'});
                } else {
                    $('.btn-filtro-ativo').css({'opacity':'1','box-shadow':'none'});
                }
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData) {
                if (settings.nTable.id !== 'listar_usuarios') return true;
                let tipoOk  = !filtroTipo || (rowData.tipo_contrato || 'pj') === filtroTipo;
                // null/undefined ativo é tratado como desativado (0)
                let userAtivo = (rowData.ativo == 1) ? '1' : '0';
                let ativoOk  = filtroAtivo === '' || userAtivo === filtroAtivo;
                return tipoOk && ativoOk;
            });

            inicializarTable();

            $(document).on('click', '#btn-todos', function () {
                filtroTipo  = '';
                filtroAtivo = '';
                atualizarVisualizacaoBotoes();
                $(".listar_user").DataTable().draw();
            });

            $(document).on('click', '.btn-filtro-tipo', function () {
                let tipo = $(this).data('tipo');
                filtroTipo = (filtroTipo === tipo) ? '' : tipo;
                atualizarVisualizacaoBotoes();
                $(".listar_user").DataTable().draw();
            });

            $(document).on('click', '.btn-filtro-ativo', function () {
                let ativo = String($(this).data('ativo'));
                filtroAtivo = (filtroAtivo === ativo) ? '' : ativo;
                atualizarVisualizacaoBotoes();
                $(".listar_user").DataTable().draw();
            });

            $(document).on('change', "select[name='corretora_id[]']", function () {
                if ($(this).val()) {
                    $(this).closest('div').parent().find("input[name='codigo_vendedor[]']").focus();
                }
                atualizarDisponibilidadeCidades();
            });

            $(document).on('click','.excluir_codigo',function(){
               let id       = $(this).attr('data-id');
               let tabelaId = String($(this).attr('data-tabela-id'));
               let self     = $(this);
               $.ajax({
                  url:"{{route('corretor.excluir')}}",
                  method:"POST",
                  data: { id:id },
                  success:function(res) {
                    if(res != "error") {
                        cidadesUsadas = cidadesUsadas.filter(v => v !== tabelaId);
                        atualizarDisponibilidadeCidades();
                        self.closest('.cidade_item').slideUp(300, function(){ $(this).remove(); });
                    }
                  }
               });
            });

            $(document).on('click', '.salvar-codigo', function () {
                let id         = $(this).data('id');
                let input      = $(this).siblings('.codigo-edit-input');
                let novoCodigo = input.val().trim();
                if (!novoCodigo) {
                    Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Código não pode ser vazio.' });
                    return;
                }
                $.ajax({
                    url: "{{ route('corretor.codigo.atualizar') }}",
                    method: 'POST',
                    data: { id: id, codigo_vendedor: novoCodigo },
                    success: function () {
                        Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1000, showConfirmButton: false });
                    }
                });
            });

            $(document).on('change', '.toggle-switch', function () {
                let userId = $(this).data('id');
                let status = $(this).is(':checked');

                $.ajax({
                    url: "{{route('corretores.alterar')}}",
                    method: 'POST',
                    data: {
                        id: userId,
                        ativo: status,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        console.log(res);
                    }
                });
            });

            $("body").on('click','.salvar_user',function(e){
                let nome  = $('#name').val().trim();
                let email = $('#email').val().trim();
                let tipo  = $('#tipo_contrato').val();
                let erros = [];

                if (!nome || nome.length < 3) {
                    erros.push('Nome é obrigatório e deve ter no mínimo 3 caracteres.');
                }
                if (!email) {
                    erros.push('Email é obrigatório.');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    erros.push('Email inválido.');
                }
                if (!tipo) {
                    erros.push('Tipo de Contrato é obrigatório.');
                }

                let inputSemCodigo = null;
                $("select[name='corretora_id[]']").each(function () {
                    if ($(this).val()) {
                        let inputCodigo = $(this).closest('div').parent().find("input[name='codigo_vendedor[]']");
                        if (!inputCodigo.val().trim()) {
                            inputSemCodigo = inputCodigo;
                            return false;
                        }
                    }
                });
                if (inputSemCodigo) {
                    erros.push('Informe o código do vendedor para a cidade selecionada.');
                }

                if (erros.length > 0) {
                    Swal.fire({ icon: 'warning', title: 'Atenção', html: erros.join('<br>') });
                    return;
                }

                var fd = new FormData();

                fd.append('nome',          $('#name').val());
                fd.append('email',         $('#email').val());
                fd.append('celular',       $('#celular').val());
                fd.append('tipo_contrato', $('#tipo_contrato').val());

                let corretoras             = [];
                let codigos                = [];
                let codigos_tabela_origens = [];

                $("select[name='corretora_id[]']").each(function () {
                    if ($(this).val()) corretoras.push($(this).val());
                });

                $("input[name='codigo_vendedor[]']").each(function () {
                    if ($(this).val()) codigos.push($(this).val());
                });

                $("select[name='corretora_id[]']").each(function () {
                    let opcaoTexto   = $(this).find("option:selected").text();
                    let codigoTabela = opcaoTexto.split(" - ")[1];
                    if (codigoTabela) codigos_tabela_origens.push(codigoTabela.trim());
                });

                fd.append("corretoras",            JSON.stringify(corretoras));
                fd.append("codigos",               JSON.stringify(codigos));
                fd.append("codigo_tabela_origens", JSON.stringify(codigos_tabela_origens));

                $.ajax({
                    url:"{{route('corretores.store')}}",
                    method:"POST",
                    data:fd,
                    contentType: false,
                    processData: false,
                    success:function(res){
                        $(".listar_user").DataTable().ajax.reload();
                        limparForm();
                        Swal.fire('Sucesso!', res.message, 'success');
                    }
                });
            });

        });
    </script>

@stop

@section('css')
    <style>
        .estilo_btn_plus {background-color:rgba(0,0,0,1);box-shadow:rgba(255,255,255,0.8) 0.1em 0.2em 5px;border-radius: 5px;display: flex;align-items: center;}
        .estilo_btn_plus i {color: #FFF !important;font-size: 0.7em;padding: 8px;}
        .estilo_btn_plus:hover {background-color:rgba(255,255,255,0.8);box-shadow:rgba(0,0,0,1) 0.1em 0.2em 5px;}
        .estilo_btn_plus:hover i {color: #000 !important;}
        .texto-branco {color: #fff;}
    </style>
@stop
</x-app-layout>
