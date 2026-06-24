
function finStatusBadge(status) {
    const s = (status || '').toLowerCase().trim();
    const m = {
        'pag. 1º parcela': 'fin-p1',
        'pag. 2º parcela': 'fin-p2',
        'pag. 3º parcela': 'fin-p3',
        'pag. 4º parcela': 'fin-p4',
        'pag. 5º parcela': 'fin-p5',
        'pag. 6º parcela': 'fin-p6',
        'finalizado':      'fin-stat-success',
        'em análise':      'fin-analise',
        'emissão boleto':  'fin-emissao',
        'pag. adesão':     'fin-adesao',
        'pag. vigência':   'fin-vigencia',
        'atrasado':        'fin-stat-warn',
        'cancelado':       'fin-stat-danger',
    };
    const cls = m[s] || '';
    return `<span class="fin-tbl-badge ${cls}">${status || ''}</span>`;
}

function inicializarColetivo(corretora_id = null) {
    console.log("Olaaaaa");
    if ($.fn.DataTable.isDataTable('.listardados')) {
        $('.listardados').DataTable().destroy();
    }
    table = $(".listardados").DataTable({
        dom: '<"flex justify-between"<"#title_individual">Bftr><t><"flex justify-between"lp>',
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
            "url":urlGeralColetivoPendentes,
            data: function (d) {
                d.corretora_id = corretora_id;
            }
        },
        "lengthMenu": [1000,2000],
        "ordering": false,
        "paging": true,
        "searching": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        columns: [
            {data:"data",name:"data",width:"7%"},
            {data:"orcamento",name:"codigo_externo",width:"5%"},
            {data:"corretor",name:"corretor",width:"14%"},
            {data:"cliente",name:"cliente",width:"14%"},
            {data:"administradora",name:"administradora",width:"11%"},
            {data:"cpf",name:"cpf",width:"9%"},
            {data:"quantidade_vidas",name:"vidas",width:"3%"},
            {data:"valor_plano",name:"valor_plano",width:"8%",render: $.fn.dataTable.render.number('.', ',', 2, 'R$ ')},
            {data:"vencimento",name:"Vencimento",width:"7%"},
            {data:"status",name:"status",width:"12%",
                "createdCell": function(td, cellData) { $(td).html(finStatusBadge(cellData)); }
            },
            {data:"id",name:"detalhes",width:"3%"},
            {data:"resposta",name:"resposta",visible:false},
            {data:"nascimento",name:"nascimento",visible:false},
            {data:"fone",name:"fone",visible:false},
            {data:"email",name:"email",visible:false},
            {data:"cidade",name:"cidade",visible:false},
            {data:"bairro",name:"bairro",visible:false},
            {data:"rua",name:"rua",visible:false},
            {data:"cep",name:"cep",visible:false},
            {data:"pdf_path",name:"pdf_path",visible:false},
        ],
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'vivaz-coletivo',
                text: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> Exportar',
                className: 'btn-exportar', // Classe personalizada para estilo
                exportOptions: {
                    columns: [0,1,2,3,4,5,6] // Define as colunas a serem exportadas (ajuste conforme necessário)
                },
                filename: 'vivaz-coletivo'
            }
        ],
        "columnDefs": [
            {
                "targets": 10,
                "width":"2%",
                "createdCell": function (td, cellData, rowData, row, col) {
                    let id = cellData;
                    let cliente = rowData['cliente'];
                    let cpf = rowData['cpf'];
                    let email = rowData['email'];
                    let nascimento = rowData['nascimento'];
                    let bairro = rowData['bairro'];
                    let rua = rowData['rua'];
                    let cidade = rowData['cidade'];
                    let cep = rowData['cep'];
                    let codigo_externo = rowData['orcamento'];
                    let corretor = rowData['corretor'];
                    let data_nascimento = rowData['nascimento'];
                    let uf = rowData['uf'];
                    let valor_plano = rowData['valor_plano'];
                    let valor_adesao = rowData['valor_adesao'];
                    let desconto_corretora = rowData['desconto_corretora'];
                    let desconto_corretor = rowData['desconto_corretor'];
                    let status = rowData['status'];
                    let financeiro_id = rowData['financeiro_id'];
                    let administradora = rowData['administradora'];
                    let fone = rowData['fone'];
                    let data_contrato = rowData['data_contrato'];
                    let quantidade_parcelas = rowData['quantidade_parcelas'];
                    let desconto_operadora = rowData['desconto_operadora'];
                    let pdf_path = rowData['pdf_path'];
                    let pdfBtn = pdf_path
                        ? `<a href="${urlPdfDownloadBase}/${id}/pdf/download" target="_blank" title="Baixar proposta PDF"
                              style="color:#a78bfa;margin-left:6px">
                               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;vertical-align:middle">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                               </svg>
                             </a>`
                        : '';
                    $(td).html(
                        `<div class='text-center text-white' style="display:flex;align-items:center;justify-content:center;gap:4px">
                            <a href="#" class="text-white open-modal" data-parcelas="${quantidade_parcelas}" data-operadora_valor="${desconto_operadora}"  data-contrato="${data_contrato}" data-fone="${fone}" data-administradora="${administradora}" data-financeiro="${financeiro_id}" data-status="${status}" data-valorplano="${valor_plano}" data-adesao="${valor_adesao}" data-descontocorretora="${desconto_corretora}" data-descontocorretor="${desconto_corretor}" data-uf="${uf}" data-nascimento="${data_nascimento}" data-corretor="${corretor}" data-rua="${rua}" data-cidade="${cidade}" data-cep="${cep}" data-codigo="${codigo_externo}" data-id="${id}" data-cliente="${cliente}" data-cpf="${cpf}" data-email="${email}" data-nascimento="${nascimento}" data-bairro="${bairro}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 div_info">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </a>${pdfBtn}
                        </div>`
                    );
                },
            },
        ],
        "initComplete": function( settings, json ) {

            $('.btn-exportar').css({
                'background-color': '#4CAF50',
                'color': '#FFF',
                'border': 'none',
                'padding': '8px 16px',
                'border-radius': '4px'
            });

            $('#title_coletivo_por_adesao_table').html("<h4 style='font-size:1em;margin-top:10px;margin-left:5px;'>Listagem(Completa)</h4>");
            let api = this.api();
            let dadosColuna9 = api.column(9,{search: 'applied'}).data();
            let dadosColuna11 = api.column(11,{search: 'applied'}).data();
            let contagemEmAnalise = 0;
            let emissao = 0;
            let adesao = 0;
            let vigencia = 0;
            let segundaParcela = 0;
            let terceiraParcela = 0;
            let quartaParcela = 0;
            let quintaParcela = 0;
            let sextaParcela = 0;
            let finalizado = 0;
            let cancelados = 0;
            let atrasados = 0;
            dadosColuna9.each(function (valor) {
                if (valor.toLowerCase() == 'em análise') {contagemEmAnalise++;}
                if (valor.toLowerCase() == 'emissão boleto') {emissao++;}
                if (valor.toLowerCase() == 'pag. vigência') {vigencia++;}
                if (valor.toLowerCase() == 'pag. adesão') {adesao++;}
                if (valor.toLowerCase() == 'pag. 2º parcela') {segundaParcela++;}
                if (valor.toLowerCase() == 'pag. 3º parcela') {terceiraParcela++;}
                if (valor.toLowerCase() == 'pag. 4º parcela') {quartaParcela++;}
                if (valor.toLowerCase() == 'pag. 5º parcela') {quintaParcela++;}
                if (valor.toLowerCase() == 'pag. 6º parcela') {sextaParcela++;}
                if (valor.toLowerCase() == 'finalizado') {finalizado++;}
                if (valor.toLowerCase() == 'cancelado') {cancelados++;}
            });
            dadosColuna11.each(function(valor){
                if (valor.toLowerCase() == 'atrasado') {atrasados++;}
            });



            $(".coletivo_quantidade_em_analise").html(`<span class="my-auto flex items-center align-middle self-center h-100">${contagemEmAnalise}</span>`);
            $(".coletivo_quantidade_emissao_boleto").html(`<span class="my-auto flex items-center align-middle self-center h-100">${emissao}</span>`);
            $(".coletivo_quantidade_pagamento_adesao").html(`<span class="my-auto flex items-center align-middle self-center h-100">${adesao}</span>`);
            $(".coletivo_quantidade_pagamento_vigencia").html(`<span class="my-auto flex items-center align-middle self-center h-100">${vigencia}</span>`);
            $(".coletivo_quantidade_segunda_parcela").html(`<span class="my-auto flex items-center align-middle self-center h-100">${segundaParcela}</span>`);
            $(".coletivo_quantidade_terceira_parcela").html(`<span class="my-auto flex items-center align-middle self-center h-100">${terceiraParcela}</span>`);
            $(".coletivo_quantidade_quarta_parcela").html(`<span class="my-auto flex items-center align-middle self-center h-100">${quartaParcela}</span>`);
            $(".coletivo_quantidade_quinta_parcela").html(`<span class="my-auto flex items-center align-middle self-center h-100">${quintaParcela}</span>`);
            $(".coletivo_quantidade_sexta_parcela").html(`<span class="my-auto flex items-center align-middle self-center h-100">${sextaParcela}</span>`);
            $(".quantidade_coletivo_finalizado").html(`<span class="my-auto flex items-center align-middle self-center h-100">${finalizado}</span>`);
            $(".quantidade_coletivo_cancelados").html(`<span class="my-auto flex items-center align-middle self-center h-100">${cancelados}</span>`);
            $(".coletivo_quantidade_atrasado").html(`<span class="my-auto flex items-center align-middle self-center h-100">${atrasados}</span>`);
            let corretoresUnicos = new Set();
            this.api().column(2).data().each(function(v) {
                corretoresUnicos.add(v);
            });
            let corretoresOrdenados = Array.from(corretoresUnicos).sort();
            $('#select_usuario').empty();
            $('#select_usuario').append('<option value="todos">-- Escolher Corretor --</option>');
            corretoresOrdenados.forEach(function(corretor) {
                $('#select_usuario').append('<option value="' + corretor + '">' + corretor + '</option>');
            });

            let anos = this.api().column(0).data().toArray().map(function(value) {
                let year = new Date(value).getFullYear();
                return !isNaN(year) ? year : null;
            });
            let anosUnicos = new Set(anos.filter(function(ano) {return ano !== null;}));
            let selectAno = $('#mudar_ano_table_coletivo');
            selectAno.empty(); // Limpar opções existentes
            selectAno.empty(); // Limpar opções existentes
            selectAno.append('<option value="" selected class="text-center">-Ano-</option>'); // Opção padrão
            anosUnicos.forEach(function(ano) {
                selectAno.append('<option value="' + ano + '">' + ano + '</option>');
            });
            let administradoras = new Set();
            this.api().column(4).data().each(function(v) {
                administradoras.add(v);
            });
            let adminOrdenados = Array.from(administradoras).sort();
            $('#select_coletivo_administradoras').empty();
            $('#select_coletivo_administradoras').append('<option value="todos" class="text-center">-- Administradoras --</option>');
            adminOrdenados.forEach(function(corretor) {
                $('#select_coletivo_administradoras').append('<option value="' + corretor + '" class="text-center">' + corretor + '</option>');
            });

            // Select2 no filtro de corretores (reinicializa a cada load)
            if ($('#select_usuario').hasClass('select2-hidden-accessible')) {
                $('#select_usuario').select2('destroy');
            }
            $('#select_usuario').select2({
                placeholder: '-- Todos os Corretores --',
                allowClear: true,
                minimumResultsForSearch: 0,
                width: '100%',
                language: { noResults: function() { return 'Nenhum corretor encontrado'; } }
            });
            $('#select_usuario').off('select2:open.coletivo').on('select2:open.coletivo', function() {
                setTimeout(function() { $('.select2-search__field').attr('placeholder', 'Pesquisar...'); }, 0);
            });
        },
        "rowCallback": function(row, data, index) {

            $(row).find('.open-modal').data('cliente', data['cliente'])
        },
        "footerCallback": function (row, data, start, end, display) {
            let intVal = (i) =>  typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            total = this.api().column(7,{search: 'applied'}).data().reduce(function (a, b) {return intVal(a) + intVal(b);},0);
            total_vidas = this.api().column(6,{search: 'applied'}).data().reduce(function (a, b) {return intVal(a) + intVal(b);},0);
            total_linhas = this.api().column(5,{search: 'applied'}).data().count();
            let total_br = total.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});
            $(".total_por_vida_coletivo").html(`<span class="my-auto flex items-center align-middle self-center h-100">${total_vidas}</span>`);
            $(".total_por_orcamento_coletivo").html(`<span class="my-auto flex items-center align-middle self-center h-100">${total_linhas}</span>`);
            $(".total_por_page_coletivo").html(`<span class="my-auto flex items-center align-middle self-center h-100">${total_br}</span>`);

        }
    });
}

$('#tabela_coletivo').on('click', 'tbody tr', function () {
    table.$('tr').removeClass('textoforte');
    $(this).closest('tr').addClass('textoforte');
});
