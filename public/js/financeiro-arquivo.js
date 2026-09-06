$("#arquivo_atualizar").on('change',function(){
    let files = $('#arquivo_atualizar')[0].files;
    if (!files.length) return;
    let fd = new FormData();
    fd.append('file',files[0]);
    $.ajax({
        url:atualizarIndividual,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        success:function(res) {
            if (res && res.job_id) {
                acompanharProgressoSincronizacao(res.job_id);
            } else {
                Swal.fire({ icon:'error', title:'Erro', text:'Resposta inesperada do servidor.', background:'#1f2937', color:'#f3f4f6' });
            }
        },
        error:function() {
            Swal.fire({ icon:'error', title:'Erro no envio', text:'Verifique a planilha e tente novamente.', background:'#1f2937', color:'#f3f4f6' });
        }
    });
});

function acompanharProgressoSincronizacao(jobId) {
    $('#atualizarModal').addClass('hidden');
    Swal.fire({
        title: 'Atualizando parcelas...',
        html: '<div id="sync-prog-texto" style="font-size:14px;color:#cbd5e1;">Iniciando processamento…</div>' +
              '<div style="background:#374151;border-radius:8px;height:16px;margin-top:12px;overflow:hidden;">' +
              '<div id="sync-prog-barra" style="background:linear-gradient(90deg,#2563eb,#38bdf8);height:100%;width:0%;transition:width .4s;"></div></div>' +
              '<div id="sync-prog-pct" style="font-size:12px;color:#94a3b8;margin-top:6px;">0%</div>',
        background: '#1f2937',
        color: '#f3f4f6',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function () { consultarProgressoSincronizacao(jobId); }
    });
}

function consultarProgressoSincronizacao(jobId) {
    $.get(urlProgressoSincronizacao.replace('__JOB__', jobId))
        .done(function (d) {
            if (d.status === 'completed') {
                Swal.fire({
                    icon:'success', title:'Concluído!', text:'Parcelas atualizadas com sucesso.',
                    background:'#1f2937', color:'#f3f4f6', timer:1800, showConfirmButton:false
                }).then(function(){ window.location.reload(); });
                return;
            }
            if (d.status === 'failed') {
                Swal.fire({ icon:'error', title:'Falha no processamento', text:(d.error || 'Erro desconhecido.'), background:'#1f2937', color:'#f3f4f6' });
                return;
            }
            let total = d.total_lines || 0;
            let feito = d.processed_lines || 0;
            let pct   = total > 0 ? Math.min(100, Math.round(feito * 100 / total)) : 0;
            $('#sync-prog-texto').text(feito + ' de ' + total + ' linhas processadas');
            $('#sync-prog-barra').css('width', pct + '%');
            $('#sync-prog-pct').text(pct + '%');
            setTimeout(function(){ consultarProgressoSincronizacao(jobId); }, 2000);
        })
        .fail(function () {
            setTimeout(function(){ consultarProgressoSincronizacao(jobId); }, 3000);
        });
}

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
    if (!files.length) return;
    let fd = new FormData();
    fd.append('file',files[0]);
    $.ajax({
        url:confirmarParcela,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            Swal.fire({
                title: 'Processando parcelas...',
                html: 'Confirmando os pagamentos da operadora, aguarde.',
                background: '#1f2937',
                color: '#f3f4f6',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });
        },
        success:function(res) {
            if (res && res.success) {
                var d = res.detalhes || {};
                Swal.fire({
                    icon: 'success',
                    title: 'Parcelas processadas',
                    background: '#1f2937',
                    color: '#f3f4f6',
                    html: 'Parcelas confirmadas: <b>' + (d.confirmados || 0) + '</b><br>' +
                          'J&aacute; confirmadas antes: <b>' + (d.ja_confirmados || 0) + '</b><br>' +
                          'N&atilde;o encontradas no sistema: <b>' + (d.nao_encontrados || 0) + '</b>'
                }).then(function(){ window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Erro', text: (res && res.message) || 'Falha no processamento' });
            }
        },
        error:function() {
            Swal.fire({ icon: 'error', title: 'Erro ao processar a planilha de parcelas' });
        }
    });
});





$("#arquivo_adiantamento").on('change',function(){
    let files = $('#arquivo_adiantamento')[0].files;
    if (!files.length) return;
    let fd = new FormData();
    fd.append('file',files[0]);
    $.ajax({
        url:adiantamentoIndividual,
        method:"POST",
        data:fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            Swal.fire({
                title: 'Processando adiantamento...',
                html: 'Confirmando os pagamentos, aguarde.',
                background: '#1f2937',
                color: '#f3f4f6',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });
        },
        success:function(res) {
            if (res && res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Concluído!',
                    text: res.message || 'Adiantamento processado com sucesso.',
                    background: '#1f2937', color: '#f3f4f6',
                    confirmButtonColor: '#2563eb'
                }).then(function(){ window.location.reload(); });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: (res && res.message) || 'Falha ao processar o adiantamento.',
                    background: '#1f2937', color: '#f3f4f6'
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Erro no processamento',
                text: (xhr.responseJSON && xhr.responseJSON.message) || 'Verifique a planilha e tente novamente.',
                background: '#1f2937', color: '#f3f4f6'
            });
        },
        complete: function () {
            $("#arquivo_adiantamento").val('');
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
            Swal.fire({
                title: 'Processando estornos...',
                html: 'Identificando clientes e vendedores, aguarde.',
                background: '#1f2937',
                color: '#f3f4f6',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });
        },
        success:function(res) {
            if (res && res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Estornos processados',
                    background: '#1f2937',
                    color: '#f3f4f6',
                    html: 'Linhas lidas: <b>' + res.linhas + '</b><br>' +
                          'Novos estornos aplicados: <b>' + res.novos + '</b><br>' +
                          'J&aacute; processados (ignorados): <b>' + res.ja_processados + '</b><br>' +
                          'Sem v&iacute;nculo no sistema: <b>' + res.sem_vinculo + '</b>'
                }).then(function(){ window.location.reload(); });
            } else {
                window.location.reload();
            }
        },
        error:function() {
            Swal.fire({ icon: 'error', title: 'Erro ao processar a planilha de estorno' });
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
            Swal.fire({
                title: 'Processando cancelados...',
                html: 'Atualizando os contratos cancelados, aguarde.',
                background: '#1f2937',
                color: '#f3f4f6',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });
        },
        success:function(res) {
            if(res == "sucesso") {
                Swal.fire({
                    icon: 'success',
                    title: 'Cancelados processados',
                    background: '#1f2937',
                    color: '#f3f4f6',
                    timer: 1600,
                    showConfirmButton: false
                }).then(function(){ window.location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Erro ao processar a planilha de cancelados' });
            }
        },
        error:function() {
            Swal.fire({ icon: 'error', title: 'Erro ao processar a planilha de cancelados' });
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
            $("#mensagem_erro").fadeOut(200).addClass("hidden"); // Oculta mensagens de erro antigas
            Swal.fire({
                title: 'Processando planilha...',
                html: 'Cadastrando os contratos, aguarde.',
                background: '#1f2937',
                color: '#f3f4f6',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () { Swal.showLoading(); }
            });
        },
        success:function(res) {
            console.log(res);
            if(res == "sucesso") {
                Swal.fire({
                    icon:'success', title:'Concluído!', text:'Planilha processada com sucesso.',
                    background:'#1f2937', color:'#f3f4f6', timer:1500, showConfirmButton:false
                }).then(function(){ window.location.reload(); });
            } else {
                Swal.close();
                $("#mensagem_erro")
                    .html(res.message || "Algo deu errado durante a importação.")
                    .removeClass("hidden")
                    .fadeIn(200);
            }
        },
        error: function (xhr) {
            Swal.close();
            // Captura erros do backend
            const erro = xhr.responseJSON?.message || "Erro desconhecido no upload."; // Captura a mensagem do erro
            $("#mensagem_erro")
                .html(erro) // Mostra o texto do erro
                .removeClass("hidden")
                .fadeIn(200); // Exibe o erro no frontend

            $("#arquivo_upload").val('');


        },
        complete: function () {
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
