var tablestorno;
function inicializarEstorno() {
    if($.fn.DataTable.isDataTable('.listardadosestorno')) {
        $('.listardadosestorno').DataTable().destroy();
    }
    tablestorno = $(".listardadosestorno").DataTable({
        dom: '<"flex justify-between"<"#title_odonto">Bftr><t><"flex justify-between"lp>',
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
            "lengthMenu": "Exibir _MENU_ por página",
            "zeroRecords": "Nenhum registro encontrado"
        },
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'vivaz',
                text: 'Exportar',
                className: 'btn-exportar', // Classe personalizada para estilo
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5,6] // Define as colunas a serem exportadas (ajuste conforme necessário)
                },
                filename: 'vivaz'
            }
        ],
        ajax: {
            "url":listarEstorno,
            "dataSrc": ""
        },
        "lengthMenu": [1000,2000,3000],
        "ordering": false,
        "paging": true,
        "searching": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "processing": true,
        columns: [
            {data:"created_at",name:"created_at"},
            {data:"nome",name:"nome"},
            {data:"usuario",name:"usuario"},
            {data:"cateirinha",name:"cateirinha"},
            {
                data: null, // Para a última coluna (dinâmica)
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <select data-id="${row.id}"
                            class="select-opcoes text-[5px] px-1 py-1 rounded text-black border border-gray-300 bg-gray-50 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="" class="text-[5px]">(vazio)</option>
                            <option value="voltar" class="text-[5px]">Voltar</option>
                            <option value="estornar" class="text-[5px]">Estornar</option>
                        </select>
                    `;
                }
            }
        ],
        "columnDefs": [],
        "initComplete": function( settings, json ) {

            $('.btn-exportar').css({
                'background-color': '#4CAF50',
                'color': '#FFF',
                'border': 'none',
                'padding': '8px 16px',
                'border-radius': '4px'
            });

            let uniqueUsers = [];
            json.forEach(row => {
                if (!uniqueUsers.includes(row.usuario)) {
                    uniqueUsers.push(row.usuario);
                }
            });

            // Construir opções como uma string e adicionar ao select
            let optionsHtml = '<option value="todos" class="text-center">---Escolher Corretor---</option>';
            uniqueUsers.forEach(user => {
                optionsHtml += `<option value="${user}" class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] text-black text-lg">${user}</option>`;
            });
            $('#select_usuario_odonto').html(optionsHtml);

        },
        "drawCallback":function(settings) {

        },
        footerCallback: function (row, data, start, end, display) {}
    });
}


