$("#arquivo_atualizar").on('change',function(){
    let files = $('#arquivo_atualizar')[0].files;
    let load = $(".ajax_load");
    // let file = $(this).val();
    let fd = new FormData();
    fd.append('file',files[0]);
    // fd.append('file',e.target.files[0]);
    $.ajax({
        url:atualizarIndividual,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            load.fadeIn(200).css("display", "flex");
        },
        success:function(res) {
            if(res == "successo") {
                load.fadeOut(200);
                window.location.reload();
            }
        }
    });
});

$("#arquivo_atualizar_empresarial").on('change',function(){

    let files = $('#arquivo_atualizar_empresarial')[0].files;
    let load = $(".ajax_load"); // Loader visual para notificações
    let fd = new FormData();
    fd.append('file', files[0]);

    $.ajax({
        url: empresarialPlanilha,
        method: "POST",
        data: fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            load.fadeIn(200).css("display", "flex"); // Mostrar loader
        },
        success: function (res) {
            console.log(res);
            // load.fadeOut(200); // Esconde loader
            // if (res === "successo") {
            //     alert("Importação realizada com sucesso! 🎉");
            //     window.location.reload();
            // } else {
            //     alert("Erro durante a importação: " + res.message);
            // }
        },
        error: function (err) {
            load.fadeOut(200);
            alert("Erro no envio do arquivo. Verifique a planilha e tente novamente.");
        }
    });



});


$("#arquivo_parcela").on('change',function(){
    let files = $('#arquivo_parcela')[0].files;
    let load = $(".ajax_load");
    let file = $(this).val();
    let fd = new FormData();
    fd.append('file',files[0]);
    $.ajax({
        url:confirmarParcela,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            //$('#atualizarModal').modal('hide');
            //load.fadeIn(200).css("display", "flex");
        },
        success:function(res) {
            console.log(res);
            //if(res == "successo") {
            //load.fadeOut(200);
            //window.location.reload();
            //}
        }
    });
});





$("#arquivo_adiantamento").on('change',function(){
    let files = $('#arquivo_adiantamento')[0].files;
    let load = $(".ajax_load");
    let file = $(this).val();
    let fd = new FormData();
    fd.append('file',files[0]);
    $.ajax({
        url:adiantamentoIndividual,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            //$('#atualizarModal').modal('hide');
            //load.fadeIn(200).css("display", "flex");
        },
        success:function(res) {
            console.log(res);
            //if(res == "successo") {
                //load.fadeOut(200);
                //window.location.reload();
            //}
        }
    });
});



$("#arquivo_estorno").on('change',function(){
    let files = $('#arquivo_estorno')[0].files;
    let load = $(".ajax_load");
    let file = $(this).val();
    let fd = new FormData();
    fd.append('file',files[0]);
    // // fd.append('file',e.target.files[0]);
    $.ajax({
        url:estornoIndividual,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            //$('#atualizarModal').modal('hide');
            load.fadeIn(200).css("display", "flex");
        },
        success:function(res) {
            if(res == "successo") {
                load.fadeOut(200);
                window.location.reload();
            }
        }
    });
});


let selectAtual = null;
let selectId = null;
$(document).on('change', '.select-opcoes', function() {
    const valorSelecionado = $(this).val();
    selectAtual = $(this);
    if (valorSelecionado === 'estornar') {
        $('#modalEstorno').removeClass('hidden');
        selectId = $(this).attr("data-id");
    }
});

$(document).on('click', '#cancelarModal', function() {
    $('#modalEstorno').addClass('hidden');
    $('#valor_estorno').val('');
    if (selectAtual) {
        selectAtual.val(''); // Reseta o valor do <select> para vazio
        selectAtual = null; // Limpa a variável global
    }
});

$(document).on('click','#confirmarModal',function(){
   let valor_estorno = $("#valor_estorno").val();
   if(!valor_estorno) {
       alert("O valor é Obrigatorio");
   }

   $.ajax({
      url:confirmarEstorno,
      method:"POST",
      data:{
         id:selectId,
         valor:valor_estorno
      },
      success:function(res) {
        if(res != "error") {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: 'O cliente foi transferido para o gerente.',
                confirmButtonText: 'OK'
            });
            inicializarEstorno();
        } else {

        }
      }
   });





});



$("#arquivo_cancelados").on('change',function(){
    let files = $('#arquivo_cancelados')[0].files;
    let load = $(".ajax_load");
    // let file = $(this).val();
    let fd = new FormData();
    fd.append('file',files[0]);
    // fd.append('file',e.target.files[0]);
    $.ajax({
        url:cancelarIndividual,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            //load.fadeIn(200).css("display", "flex");
            //$('#uploadModal').modal('hide');
        },
        success:function(res) {
            if(res == "sucesso") {
                window.location.reload();
            }

        }
    });



});




/*************************************************REALIZAR UPLOAD DO EXCEL*********************************************************************/
$("#arquivo_upload").on('change',function(e){
    var files = $('#arquivo_upload')[0].files;
    var load = $(".ajax_load");
    // let file = $(this).val();
    var fd = new FormData();
    fd.append('file',files[0]);
    // fd.append('file',e.target.files[0]);
    $.ajax({
        url:financeiroSincroniza,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            load.fadeIn(200).css("display", "flex");
            $("#mensagem_erro").fadeOut(200).addClass("hidden"); // Oculta mensagens de erro antigas
            //$('#uploadModal').modal('hide');
        },
        success:function(res) {
            console.log(res);
            if(res == "sucesso") {
                window.location.reload();
                // load.fadeOut(200);
                // $('#uploadModal').modal('show');
                // $(".div_icone_arquivo_upload").removeClass('btn-danger').addClass('btn-success').html('<i class="far fa-smile-beam fa-lg"></i>');
                // $("#arquivo_upload").val('').prop('disabled',true);

            } else {
                $("#mensagem_erro")
                    .html(res.message || "Algo deu errado durante a importação.")
                    .removeClass("hidden")
                    .fadeIn(200);
            }
        },
        error: function (xhr) {
            // Captura erros do backend
            const erro = xhr.responseJSON?.message || "Erro desconhecido no upload."; // Captura a mensagem do erro
            $("#mensagem_erro")
                .html(erro) // Mostra o texto do erro
                .removeClass("hidden")
                .fadeIn(200); // Exibe o erro no frontend

            $("#arquivo_upload").val('');


        },
        complete: function () {
            // Após completar a requisição, oculta o loader
            load.fadeOut(200);
        },




    });
});

/*************************************************Atualizar Dados*********************************************************************/
$(".atualizar_dados").on('click',function(){
    var load = $(".ajax_load");

    $.ajax({
        url:"{{route('financeiro.atualizar.dados')}}",
        method:"POST",
        beforeSend: function (res) {
            load.fadeIn(200).css("display", "flex");
            $('#uploadModal').addClass('hidden').removeClass('flex');

        },
        success:function(res) {
            if(res == "sucesso") {
                load.fadeOut(200);
                $('#uploadModal').removeClass('hidden').addClass('flex');
                $(".div_icone_arquivo_upload").removeClass('btn-danger').addClass('btn-success').html('<i class="far fa-smile-beam fa-lg"></i>');
                $(".div_icone_atualizar_dados").removeClass('btn-danger').addClass('btn-success').html('<i class="far fa-smile-beam fa-lg"></i>');
                $(".atualizar_dados").removeClass('btn-warning').addClass('btn-secondary').prop('disabled',true);
                $("#arquivo_upload").val('').prop('disabled',true);
                window.location.href = response.redirect;
            }
        }
    });

    return false;
});
/*************************************************Sincronizar Dados*********************************************************************/
$(".sincronizar_baixas").on('click',function(){
    var load = $(".ajax_load");
    $.ajax({
        url:"{{route('financeiro.sincronizar.baixas')}}",
        method:"POST",
        beforeSend: function (res) {
            load.fadeIn(200).css("display", "flex");
            $('#uploadModal').addClass('hidden').removeClass('flex');

        },
        success:function(res) {

            if(res == "sucesso") {
                window.location.reload();
            } else {

            }
        }
    });
    return false;
});

/*****************************************************UPLOAD COLETIVO****************************************************************************** */
$("#arquivo_upload_coletivo").on('change',function(e){
    var files = $('#arquivo_upload_coletivo')[0].files;
    var load = $(".ajax_load");
    var fd = new FormData();
    fd.append('file',files[0]);
    $.ajax({
        url:"{{route('financeiro.sincronizar.coletivo')}}",
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            load.fadeIn(200).css("display", "flex");
            $('#uploadModalColetivo').modal('hide');
        },
        success:function(res) {

            if(res == "sucesso") {
                load.fadeOut(200);
            } else {

            }

        }
    });
})
