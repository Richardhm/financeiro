var tableodonto;
function inicializarOdonto() {
    if($.fn.DataTable.isDataTable('.listardadosodonto')) {
        $('.listardadosodonto').DataTable().destroy();
    }
    tableodonto = $(".listardadosodonto").DataTable({
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
            "url":listarOdonto,
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
            {data:"valor",name:"valor_plano",render: $.fn.dataTable.render.number('.', ',', 2, 'R$ ')},
            {data:"id", name:"id", orderable:false, searchable:false,
             render: function(data){ return '<button class="button_excluir_odonto" data-id="'+data+'" style="background:#7f1d1d;color:#fecaca;border:none;border-radius:5px;padding:3px 8px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;"><svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'currentColor\' viewBox=\'0 0 24 24\' style=\'width:12px;height:12px\'><path fill-rule=\'evenodd\' d=\'M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z\' clip-rule=\'evenodd\'/></svg>Excluir</button>'; }
            },
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
        "drawCallback":function(settings) {},
        footerCallback: function (row, data, start, end, display) {}
    });
}

$(document).ready(function() {
    $('#select_usuario_odonto').select2({
        placeholder: 'Todos os Corretores',
        allowClear: true,
        minimumResultsForSearch: 0,
        width: '100%',
        language: {
            noResults: function() { return 'Nenhum corretor encontrado'; }
        }
    });

    $('#select_usuario_odonto').on('select2:open', function() {
        setTimeout(function() {
            $('.select2-search__field').attr('placeholder', 'Pesquisar corretor...');
        }, 0);
    });
});

$('#select_usuario_odonto').on('change', function() {
    let selectedUser = $(this).val();
    if (!selectedUser || selectedUser === "todos") {
        tableodonto.column(2).search("").draw();
    } else {
        tableodonto.column(2).search('^' + selectedUser + '$', true, false).draw();
    }
});


$('#tabela_odonto').on('click', 'tbody tr', function () {
    tableodonto.$('tr').removeClass('textoforte');
    $(this).closest('tr').addClass('textoforte');
});
