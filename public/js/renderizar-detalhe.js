/* ===== UTILIDADES CSRF ===== */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function jsonHeaders() {
    return { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json', 'Accept': 'application/json' };
}

/* ===== CARD ATIVO ===== */
function setActiveCard(planoId) {
    document.querySelectorAll('#grids [data-plano]').forEach(el => {
        el.style.outline    = '';
        el.style.boxShadow  = '';
        el.style.transform  = '';
        el.style.transition = '';
        el.style.zIndex     = '';
    });
    const active = document.querySelector(`#grids [data-plano="${CSS.escape(String(planoId))}"]`);
    if (active) {
        active.style.transition = 'outline 0.15s, box-shadow 0.15s, transform 0.15s';
        active.style.outline    = '2px solid #f97316';
        active.style.boxShadow  = '0 0 0 3px rgba(249,115,22,0.35), 0 4px 16px rgba(249,115,22,0.45)';
        active.style.transform  = 'scale(1.05)';
        active.style.zIndex     = '10';
    }
}
window.setActiveCard = setActiveCard;

/* ===== ATUALIZA NÚMEROS DOS CARDS DE PLANO ===== */
function atualizarCardsComResumo(resumo) {
    if (!resumo) return;
    const modoP = !!window.PARCEIROS_MODE;
    const planos = [
        { key: 'individual',   seletor: '[data-plano="1"]' },
        { key: 'coletivo',     seletor: '[data-plano="3"]' },
        { key: 'empresarial',  seletor: '[data-plano="empresarial"]' },
        { key: 'adiantamento', seletor: '[data-plano="adiantamento"]' },
    ];
    planos.forEach(({ key, seletor }) => {
        const card = document.querySelector(`#grids ${seletor}`);
        if (!card) return;
        const d = resumo[key];
        const statsDiv = card.querySelector('div');
        if (!statsDiv) return;
        statsDiv.innerHTML = `<div><strong>Contr.:</strong> ${d?.total_contratos || 0}</div>${!modoP ? `<div><strong>Vidas:</strong> ${d?.total_vidas || 0}</div>` : ''}<div><strong>Total:</strong> <span class="font-bold">${formatMoney(d?.valor_total || 0)}</span></div>`;
    });
}
window.atualizarCardsComResumo = atualizarCardsComResumo;

/* ===== ATUALIZA CARD E LISTING COM DADOS DO DB ===== */
async function atualizarCardConfirmadosFromDB(corretorId) {
    try {
        const r    = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorId}&plano_id=confirmados`);
        const data = await r.json();
        if (!data.success) return null;

        const conf       = data.resumo?.confirmados;
        const contratos  = conf?.total_contratos || 0;
        const totalBruto = parseFloat(conf?.valor_total || 0);
        const totalVale  = parseFloat(data.resumo?.vale?.total_comissao || 0);
        const total      = Math.max(0, totalBruto - totalVale);

        const el = document.getElementById('card-confirmados-stats');
        if (el) {
            el.innerHTML = `
                <div><strong>Contr.:</strong> ${contratos}</div>
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(total)}</span></div>`;
        }

        const vendorSpan = document.querySelector(`.corretor-item[data-corretor-id="${corretorId}"] .total_a_receber`);
        if (vendorSpan) vendorSpan.textContent = formatMoney(total);

        atualizarCardsComResumo(data.resumo);

        return { contratos, total, clientes: data.clientes };
    } catch (err) {
        console.error('atualizarCardConfirmadosFromDB:', err);
        return null;
    }
}

/* ===== EVENTOS GLOBAIS: CONFIRMAR / REMOVER / FINALIZAR ===== */
document.addEventListener('click', async function (e) {

    // Confirmar parcela → DB (status_apto_pagar = 1)
    const btnConfirmar = e.target.closest('.btn-confirmar-parceiro');
    if (btnConfirmar) {
        e.stopPropagation();
        if (btnConfirmar.disabled) return;
        btnConfirmar.disabled = true;
        btnConfirmar.textContent = '…';

        const id = btnConfirmar.dataset.id;
        try {
            const resp = await fetch('/folha/api/parceiro/confirmar', {
                method: 'POST', headers: jsonHeaders(),
                body: JSON.stringify({ id }),
            });
            const data = await resp.json();
            if (data.success) {
                btnConfirmar.closest('tr')?.remove();
                const stats = await atualizarCardConfirmadosFromDB(corretorAtualDetalhes);
                mostrarToastParceiro(`✓ Adicionado — ${stats?.contratos ?? ''} parcela(s) | ${formatMoney(stats?.total ?? 0)}`);
            } else {
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = '＋';
                alert(data.message || 'Erro ao confirmar');
            }
        } catch (err) {
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = '＋';
        }
        return;
    }

    // Remover do Confirmados → DB (status_apto_pagar = 0)
    const btnRemover = e.target.closest('.btn-remover-confirmado');
    if (btnRemover) {
        e.stopPropagation();
        if (btnRemover.disabled) return;
        btnRemover.disabled = true;

        const id = btnRemover.dataset.id;
        try {
            const resp = await fetch('/folha/api/parceiro/remover', {
                method: 'POST', headers: jsonHeaders(),
                body: JSON.stringify({ id }),
            });
            const data = await resp.json();
            if (data.success) {
                btnRemover.closest('tr')?.remove();
                await atualizarCardConfirmadosFromDB(corretorAtualDetalhes);
                // Atualiza totais visíveis na tabela confirmados
                const totalEl = document.getElementById('conf-table-total');
                const countEl = document.getElementById('conf-table-count');
                const r2 = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorAtualDetalhes}&plano_id=confirmados`);
                const d2 = await r2.json();
                const conf = d2.resumo?.confirmados;
                if (totalEl) totalEl.textContent = formatMoney(conf?.valor_total || 0);
                if (countEl) countEl.textContent  = conf?.total_contratos || 0;
            } else {
                btnRemover.disabled = false;
            }
        } catch (err) {
            btnRemover.disabled = false;
        }
        return;
    }

    // Finalizar confirmados — abre modal de seleção de período
    const btnFinalizar = e.target.closest('.btn-finalizar-selecionados');
    if (btnFinalizar) {
        e.stopPropagation();
        const url        = btnFinalizar.dataset.url;
        const frequencia = btnFinalizar.dataset.frequencia || 'mensal';
        const parceiroId = btnFinalizar.dataset.parceiroId;

        let periodosFinalizados = [];
        try {
            const rPer = await fetch(`/folha/folha-parceiros/${parceiroId}/periodos-finalizados`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const dPer = await rPer.json();
            periodosFinalizados = dPer.periodos || [];
        } catch (_) {}

        Swal.fire({
            title: 'Período de pagamento',
            html: gerarHtmlSeletorPeriodo(frequencia, periodosFinalizados),
            background: '#1f2937',
            color: '#f3f4f6',
            width: 560,
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Confirmar e Finalizar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                // Seleção de período: clique nos cards
                document.querySelectorAll('.periodo-option').forEach(card => {
                    card.addEventListener('click', () => {
                        document.querySelectorAll('.periodo-option').forEach(c => {
                            c.classList.remove('border-emerald-400', 'bg-emerald-900/50', 'text-white');
                            c.classList.add('border-gray-600', 'bg-gray-800/60', 'text-gray-300');
                            // remove indicador atual
                            const ind = c.querySelector('.periodo-atual-badge');
                            if (ind) ind.textContent = '';
                        });
                        card.classList.remove('border-gray-600', 'bg-gray-800/60', 'text-gray-300');
                        card.classList.add('border-emerald-400', 'bg-emerald-900/50', 'text-white');
                    });
                });
            },
            preConfirm: () => {
                const sel = document.querySelector('.periodo-option.border-emerald-400');
                if (!sel) {
                    Swal.showValidationMessage('Selecione um período antes de finalizar.');
                    return false;
                }
                return { periodo_inicio: sel.dataset.inicio, periodo_fim: sel.dataset.fim };
            },
        }).then(async result => {
            if (!result.isConfirmed) return;
            const { periodo_inicio, periodo_fim } = result.value;

            try {
                const resp = await fetch(url, {
                    method: 'POST', headers: jsonHeaders(),
                    body: JSON.stringify({ periodo_inicio, periodo_fim }),
                });
                const data = await resp.json();
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Finalizado!' : 'Erro',
                    text: data.message,
                    background: '#1f2937',
                    color: '#f3f4f6',
                    timer: 4000,
                    showConfirmButton: false,
                }).then(() => { if (data.success) location.reload(); });
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha na comunicação com o servidor.', background: '#1f2937', color: '#f3f4f6' });
            }
        });
        return;
    }
});

function mostrarToastParceiro(msg) {
    const t = document.createElement('div');
    t.className = 'fixed bottom-4 right-4 z-50 px-4 py-2 rounded-lg text-sm text-white shadow-lg';
    t.style.background = '#059669';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2800);
}

/* ===== RENDERIZAR CABEÇALHO DOS CARDS ===== */
function renderizarHeader(data, corretorId) {
    const modoP   = !!window.PARCEIROS_MODE;
    const conf       = data.resumo?.confirmados;
    const confQtd    = conf?.total_contratos || 0;
    const totalValeH = parseFloat(data.resumo?.vale?.total_comissao || 0);
    const confVal    = Math.max(0, parseFloat(conf?.valor_total || 0) - totalValeH);

    // Em PARCEIROS_MODE: flex nowrap, sem scroll — cards comprimem com flex-1 min-w-0.
    const containerStyle = modoP
        ? 'display:flex;flex-wrap:nowrap;gap:4px;'
        : 'display:flex;flex-wrap:wrap;gap:4px;';

    let header = `<div id="grids" style="${containerStyle}margin-bottom:4px;">`;

    // ── Confirmados (apenas parceiros) ──
    if (modoP) {
        header += `
            <div class="bg-emerald-700 text-white rounded shadow-lg px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0 border-2 border-emerald-400/40" data-plano="confirmados">
                <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">✓ Confirmados</h4>
                <div class="text-[10px] mt-0.5 leading-snug" id="card-confirmados-stats">
                    <div><strong>Contr.:</strong> ${confQtd}</div>
                    <div><strong>Total:</strong> <span class="font-bold">${formatMoney(confVal)}</span></div>
                </div>
            </div>`;
    }

    // ── Individual ──
    header += `
        <div class="bg-green-500 text-white rounded shadow-lg px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="1">
            <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">Individual</h4>
            <div class="text-[10px] mt-0.5 leading-snug">
                <div><strong>Contr.:</strong> ${data.resumo.individual?.total_contratos || 0}</div>
                ${!modoP ? `<div><strong>Vidas:</strong> ${data.resumo.individual?.total_vidas || 0}</div>` : ''}
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.individual?.valor_total || 0)}</span></div>
            </div>
        </div>`;

    // ── Coletivo ──
    header += `
        <div class="bg-green-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="3">
            <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">Coletivo</h4>
            <div class="text-[10px] mt-0.5 leading-snug">
                <div><strong>Contr.:</strong> ${data.resumo.coletivo?.total_contratos || 0}</div>
                ${!modoP ? `<div><strong>Vidas:</strong> ${data.resumo.coletivo?.total_vidas || 0}</div>` : ''}
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.coletivo?.valor_total || 0)}</span></div>
            </div>
        </div>`;

    // ── Empresarial ──
    header += `
        <div class="bg-green-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="empresarial">
            <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">Empresarial</h4>
            <div class="text-[10px] mt-0.5 leading-snug">
                <div><strong>Contr.:</strong> ${data.resumo.empresarial?.total_contratos || 0}</div>
                ${!modoP ? `<div><strong>Vidas:</strong> ${data.resumo.empresarial?.total_vidas || 0}</div>` : ''}
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.empresarial?.valor_total || 0)}</span></div>
            </div>
        </div>`;

    // ── Odonto ──
    header += `
        <div class="bg-green-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="odonto">
            <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">Odonto</h4>
            <div class="text-[10px] mt-0.5 leading-snug">
                <div><strong>Contr.:</strong> ${data.resumo.odonto?.total_registros || 0}</div>
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.odonto?.total_comissao || 0)}</span></div>
            </div>
        </div>`;

    // ── Premiação (oculto em PARCEIROS_MODE) ──
    if (!modoP) {
        header += `
            <div class="bg-green-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="premiacao">
                <h4 class="text-[11px] font-semibold leading-tight">Premiação</h4>
                <div class="text-[10px] mt-0.5 leading-snug">
                    <div><strong>Total:</strong> <span class="font-bold total_premiacao">${formatMoney(data.premiacao)}</span></div>
                </div>
            </div>`;
    }

    // ── Fixo (oculto em PARCEIROS_MODE) ──
    if (!modoP) {
        header += `
            <div class="bg-blue-400 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="fixo">
                <h4 class="text-[11px] font-semibold leading-tight">Fixo</h4>
                <div class="text-[10px] mt-0.5 leading-snug">
                    <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.fixo?.total_comissao || 0)}</span></div>
                </div>
            </div>`;
    }

    // ── Vale ──
    header += `
        <div class="bg-red-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="vale">
            <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">Vale</h4>
            <div class="text-[10px] mt-0.5 leading-snug">
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.vale?.total_comissao || 0)}</span></div>
            </div>
        </div>`;

    // ── Estorno (oculto em PARCEIROS_MODE) ──
    if (!modoP) {
        header += `
            <div class="bg-red-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="estorno">
                <h4 class="text-[11px] font-semibold leading-tight">Estorno</h4>
                <div class="text-[10px] mt-0.5 leading-snug">
                    <div><strong>Contr.:</strong> ${data.resumo.estorno?.total_registros || 0}</div>
                    <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.estorno?.total_estorno || 0)}</span></div>
                </div>
            </div>`;
    }

    // ── Não Recebido ──
    header += `
        <div class="bg-yellow-500 text-white rounded shadow-md px-1.5 py-1 hover:cursor-pointer flex-1 min-w-0" data-plano="adiantamento">
            <h4 class="text-[11px] font-semibold leading-tight whitespace-nowrap">Não Recebido</h4>
            <div class="text-[10px] mt-0.5 leading-snug">
                <div><strong>Contr.:</strong> ${data.resumo.adiantamento?.total_contratos || 0}</div>
                ${!modoP ? `<div><strong>Vidas:</strong> ${data.resumo.adiantamento?.total_vidas || 0}</div>` : ''}
                <div><strong>Total:</strong> <span class="font-bold">${formatMoney(data.resumo.adiantamento?.valor_total || 0)}</span></div>
            </div>
        </div>`;

    header += `</div>`;
    return header;
}

/* ===== TABELA PRINCIPAL (Individual / Coletivo / Empresarial) ===== */
function renderizarTabelaPrincipal(data) {
    const modoP  = !!window.PARCEIROS_MODE;
    const tipo   = data.tipo;
    const cols   = data.tipo !== 'estorno';
    const naoRec = data.tipo === 'adiantamento';

    let html = `
        <div class="flex justify-between w-full items-center bg-white/10 backdrop-blur-md rounded-lg mb-1 p-2">
            <div class="p-1 flex w-[30%] text-center font-bold text-white text-sm frase-plano">${data.frase}</div>
            <div class="flex w-[50%]">
                <input type="text" id="pesquisa-clientes" placeholder="Digite para filtrar..."
                    class="w-full px-4 py-2 border border-gray-400/50 rounded-lg text-sm text-blue-800 bg-white/20 backdrop-blur-md
                    shadow-inner focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:border-transparent transition" />
            </div>
        </div>
        <div id="container_table" class="overflow-x-auto max-h-[400px] overflow-y-auto border border-gray-700 rounded-lg">
            <table id="table-clientes" class="table-auto border-collapse border border-gray-700 w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-700 text-gray-300 text-xs">
                    <tr>
                        <th class="px-4 py-2 border border-gray-600">Admin</th>
                        <th class="px-4 py-2 border border-gray-600">Data</th>
                        <th class="px-4 py-2 border border-gray-600">Cod.</th>
                        <th class="px-4 py-2 border border-gray-600">Cliente</th>
                        <th class="px-4 py-2 border border-gray-600">Parcela</th>
                        <th class="px-4 py-2 border border-gray-600">Vencimento</th>
                        <th class="px-4 py-2 border border-gray-600">Valor</th>
                        <th class="px-4 py-2 border border-gray-600">%</th>
                        <th class="px-4 py-2 border border-gray-600">Pagar</th>
                        <th class="px-4 py-2 border border-gray-600">Desconto</th>
                        ${cols && !naoRec ? `<th class="px-4 py-2 border border-gray-600">Editar</th>` : ''}
                        ${cols ? `<th class="px-4 py-2 border border-gray-600">Detalhes</th>` : ''}
                        ${naoRec ? `<th class="px-4 py-2 border border-gray-600">Incluir</th>` : ''}
                        ${cols && !modoP && !naoRec ? `<th class="px-4 py-2 border border-gray-600">Folha</th>` : ''}
                        ${modoP && !naoRec && cols ? `<th class="px-4 py-2 border border-gray-600 text-emerald-300">Confirmar</th>` : ''}
                    </tr>
                </thead>
                <tbody>`;

    data.clientes.forEach((cliente, index) => {
        const isManual = cliente.manualmente === 1;
        const rowClass = `${index % 2 === 0 ? 'bg-[rgba(254,254,254,0.05)]' : 'bg-[rgba(254,254,254,0.1)]'} ${isManual ? 'bg-red-400 text-white font-bold' : ''}`;
        const valorPlano = (cliente.valor_plano_ajustado ?? cliente.valor_original_plano ?? 0)
            .toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const pct = !isNaN(parseInt(cliente.porcentagem)) ? parseInt(cliente.porcentagem) + '%' : '-';

        html += `<tr class="${rowClass} hover:bg-gray-600 transition-fast cliente-row text-xs">
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${cliente.administradora}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${new Date(cliente.data_cadastro).toLocaleDateString('pt-BR')}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${cliente.contrato_codigo || '-'}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${cliente.cliente_nome}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${cliente.parcela || '-'}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${new Date(cliente.vencimento).toLocaleDateString('pt-BR')}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${valorPlano}</td>
            <td class="px-3 py-2 border border-gray-600 text-white text-xs">${pct}</td>
            <td class="px-3 py-2 border border-gray-600 text-green-400 font-bold">${formatMoney(cliente.valor_comissao)}</td>
            <td class="px-3 py-2 border border-gray-600 text-green-400 font-bold text-xs">${formatMoney(cliente.desconto_corretor ?? 0)}</td>
            ${cols && !naoRec ? `<td class="px-4 py-2 text-center">
                <button class="editar-comissao text-blue-500 hover:text-blue-700"
                    data-id="${cliente.id}" data-incluir="${cliente.incluir}"
                    data-contrato="${cliente.contrato_codigo}"
                    data-valor_plano="${valorPlano}"
                    data-nome="${cliente.cliente_nome}"
                    data-porcentagem="${cliente.porcentagem}"
                    data-comissao="${new Intl.NumberFormat('pt-BR', {minimumFractionDigits:2}).format(cliente.valor_comissao)}">
                    <span>&#x270E;</span>
                </button>
            </td>` : ''}
            ${cols ? `<td class="px-4 py-2 border border-gray-600 text-center">
                <button class="detalhe-cliente-btn bg-blue-500 hover:bg-blue-700 text-white px-2 py-1 rounded"
                    data-cliente="${cliente.plano}" data-id="${cliente.cliente_id}">👁️</button>
            </td>` : ''}
            ${naoRec ? `<td class="px-4 py-2 border border-gray-600 text-center hover:cursor-pointer btn-confirmar-comissao"
                data-id-comissao="${cliente.id}" data-id-cliente="${cliente.cliente_id}">
                <span style="color:yellow;font-size:24px;">+</span>
            </td>` : ''}
            ${cols && !modoP && !naoRec ? `<td class="px-3 py-2 border border-gray-600 text-center">
                <input type="checkbox" class="checkbox-selecionar-cliente"
                    data-id="${cliente.id}" data-desconto="${cliente.desconto_corretor}"
                    data-valor="${cliente.valor_comissao}" ${cliente.folha == 1 ? 'checked' : ''}>
            </td>` : ''}
            ${modoP && !naoRec && cols ? `<td class="px-3 py-2 border border-gray-600 text-center">
                <button class="btn-confirmar-parceiro text-emerald-400 hover:text-emerald-200 text-xl font-bold leading-none"
                    data-id="${cliente.id}"
                    data-nome="${cliente.cliente_nome}"
                    data-adm="${cliente.administradora}"
                    data-valor="${cliente.valor_comissao}"
                    data-parcela="${cliente.parcela || ''}"
                    data-vencimento="${cliente.vencimento}"
                    data-codigo="${cliente.contrato_codigo || ''}"
                    data-vidas="${cliente.quantidade_vidas || 1}"
                    title="Confirmar para folha">＋</button>
            </td>` : ''}
        </tr>`;
    });

    html += `</tbody></table></div>`;
    return html;
}

/* ===== CÁLCULO DE PERÍODOS ===== */
function calcularSemanasDoMes(ano, mes) {
    // mes: 0-based (0 = Janeiro)
    const primeiro = new Date(ano, mes, 1);
    const ultimo   = new Date(ano, mes + 1, 0);
    const semanas  = [];
    const meses    = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    let cursor = new Date(primeiro);

    while (cursor <= ultimo) {
        const inicio = new Date(cursor);
        // Avança até domingo (fim da semana)
        const diasAteDom = cursor.getDay() === 0 ? 0 : 7 - cursor.getDay();
        const fim = new Date(cursor);
        fim.setDate(fim.getDate() + diasAteDom);
        if (fim > ultimo) fim.setTime(ultimo.getTime());

        const pad = n => String(n).padStart(2, '0');
        semanas.push({
            inicio: `${ano}-${pad(mes + 1)}-${pad(inicio.getDate())}`,
            fim:    `${ano}-${pad(mes + 1)}-${pad(fim.getDate())}`,
            label:  `${pad(inicio.getDate())}/${pad(mes+1)} – ${pad(fim.getDate())}/${pad(mes+1)}/${ano}`,
        });

        cursor = new Date(fim);
        cursor.setDate(cursor.getDate() + 1);
    }
    return semanas;
}

function calcularQuinzenasDoMes(ano, mes) {
    const pad  = n => String(n).padStart(2, '0');
    const ultimo = new Date(ano, mes + 1, 0).getDate();
    return [
        {
            inicio: `${ano}-${pad(mes+1)}-01`,
            fim:    `${ano}-${pad(mes+1)}-15`,
            label:  `1ª quinzena — 01/${pad(mes+1)} a 15/${pad(mes+1)}/${ano}`,
        },
        {
            inicio: `${ano}-${pad(mes+1)}-16`,
            fim:    `${ano}-${pad(mes+1)}-${pad(ultimo)}`,
            label:  `2ª quinzena — 16/${pad(mes+1)} a ${pad(ultimo)}/${pad(mes+1)}/${ano}`,
        },
    ];
}

function calcularMesesDisponiveis() {
    const hoje  = new Date();
    const meses = [];
    const nomes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    const pad   = n => String(n).padStart(2, '0');
    for (let i = 1; i >= 0; i--) {
        const d = new Date(hoje.getFullYear(), hoje.getMonth() - i, 1);
        const ultimo = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
        meses.push({
            inicio: `${d.getFullYear()}-${pad(d.getMonth()+1)}-01`,
            fim:    `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(ultimo)}`,
            label:  `${nomes[d.getMonth()]} ${d.getFullYear()}`,
        });
    }
    return meses;
}

function gerarHtmlSeletorPeriodo(frequencia, finalizados = []) {
    const hoje = new Date();
    const ano  = hoje.getFullYear();
    const mes  = hoje.getMonth(); // 0-based
    const nomes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

    let periodos = [];
    let titulo   = '';

    if (frequencia === 'semanal') {
        titulo = 'Selecione a semana';
        const semAnterior = calcularSemanasDoMes(mes === 0 ? ano - 1 : ano, mes === 0 ? 11 : mes - 1);
        const semAtual    = calcularSemanasDoMes(ano, mes);
        periodos = [...semAnterior.slice(-2), ...semAtual];
    } else if (frequencia === 'quinzenal') {
        titulo = 'Selecione a quinzena';
        const qAnterior = calcularQuinzenasDoMes(mes === 0 ? ano - 1 : ano, mes === 0 ? 11 : mes - 1);
        const qAtual    = calcularQuinzenasDoMes(ano, mes);
        periodos = [...qAnterior, ...qAtual];
    } else {
        titulo = 'Selecione o mês';
        periodos = calcularMesesDisponiveis();
    }

    const hojeStr = hoje.toISOString().split('T')[0];
    const autoIdx = periodos.findIndex(p => hojeStr >= p.inicio && hojeStr <= p.fim);

    const freqBadge = {
        semanal:   { txt: 'Semanal',   cls: 'bg-blue-600' },
        quinzenal: { txt: 'Quinzenal', cls: 'bg-purple-600' },
        mensal:    { txt: 'Mensal',    cls: 'bg-teal-600' },
    }[frequencia] || { txt: frequencia, cls: 'bg-gray-600' };

    let html = `
        <div class="mb-3 flex items-center gap-2">
            <span class="text-xs px-2 py-0.5 rounded-full text-white font-semibold ${freqBadge.cls}">${freqBadge.txt}</span>
            <span class="text-gray-300 text-sm">${titulo}</span>
        </div>
        <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">`;

    let temDesabilitado = false;

    periodos.forEach((p, idx) => {
        const jaFinalizado = finalizados.some(f => f.inicio === p.inicio && f.fim === p.fim);

        if (jaFinalizado) {
            temDesabilitado = true;
            html += `<div class="cursor-not-allowed rounded-lg border-2 border-red-700/50 bg-red-950/20 p-3 text-left text-sm select-none"
                data-inicio="${p.inicio}" data-fim="${p.fim}">
                <div class="flex items-center justify-between mb-0.5">
                    <div class="font-semibold text-xs text-gray-500">${p.label}</div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-red-500 shrink-0">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                    </svg>
                </div>
                <div class="text-red-500/70 text-[10px]">já finalizado</div>
            </div>`;
        } else {
            const isAuto = idx === autoIdx;
            const base   = 'periodo-option cursor-pointer rounded-lg border-2 p-3 text-left transition-all duration-150 text-sm';
            const style  = isAuto
                ? `${base} border-emerald-400 bg-emerald-900/50 text-white`
                : `${base} border-gray-600 bg-gray-800/60 text-gray-300 hover:border-gray-400`;
            html += `<div class="${style}" data-inicio="${p.inicio}" data-fim="${p.fim}">
                <div class="font-semibold text-xs mb-0.5">${p.label}</div>
                ${isAuto ? '<div class="text-emerald-400 text-[10px] mt-1">● período atual</div>' : ''}
            </div>`;
        }
    });

    html += `</div>`;

    if (temDesabilitado) {
        html += `<div class="flex items-center gap-2 text-xs text-gray-400 mt-3 pt-2 border-t border-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-red-500 shrink-0">
                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
            </svg>
            Períodos com ✕ já possuem folha finalizada e não podem ser selecionados
        </div>`;
    } else {
        html += `<p class="text-gray-500 text-xs mt-3">Clique em um período para selecioná-lo</p>`;
    }

    return html;
}

/* ===== TABELA CONFIRMADOS (dados do DB) ===== */
function renderizarTabelaConfirmados(clientes, resumoConf, corretorId, frequencia) {
    const contratos  = resumoConf?.total_contratos || clientes.length;
    const total      = parseFloat(resumoConf?.valor_total || 0);
    const url        = `${window.URL_FINALIZAR_PARCEIRO_BASE || '/folha/folha-parceiros'}/${corretorId}/finalizar`;
    const freq       = frequencia || 'mensal';

    if (!clientes || clientes.length === 0) {
        return `<div class="text-center p-8 bg-gray-800/60 rounded-xl">
            <p class="text-2xl mb-3">☑️</p>
            <p class="text-gray-300 font-medium">Nenhuma parcela confirmada ainda</p>
            <p class="text-gray-500 text-sm mt-1">Clique em <strong class="text-emerald-400">＋</strong> nas listagens Individual, Coletivo ou Empresarial para confirmar parcelas.</p>
        </div>`;
    }

    const freqBadge = {
        semanal:   { txt: 'Semanal',   cls: 'bg-blue-600/70' },
        quinzenal: { txt: 'Quinzenal', cls: 'bg-purple-600/70' },
        mensal:    { txt: 'Mensal',    cls: 'bg-teal-600/70' },
    }[freq] || { txt: freq, cls: 'bg-gray-600/70' };

    let html = `
        <div class="flex justify-between w-full items-center bg-emerald-900/40 backdrop-blur-md rounded-lg mb-1 p-2">
            <div class="flex items-center gap-2">
                <span class="font-bold text-emerald-300 text-sm">✓ Parcelas Confirmadas</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full text-white font-semibold ${freqBadge.cls}">${freqBadge.txt}</span>
            </div>
            <div class="text-xs text-gray-300">
                <span id="conf-table-count">${contratos}</span> parcela(s) ·
                Total: <strong id="conf-table-total">${formatMoney(total)}</strong>
            </div>
        </div>
        <div id="container_table" class="overflow-x-auto max-h-[350px] overflow-y-auto border border-emerald-700/40 rounded-lg">
            <table id="table-clientes" class="table-auto border-collapse border border-gray-700 w-full text-sm text-left text-gray-400">
                <thead class="bg-emerald-900/60 text-emerald-200 text-xs">
                    <tr>
                        <th class="px-3 py-2">Admin</th>
                        <th class="px-3 py-2">Cliente</th>
                        <th class="px-3 py-2">Cod.</th>
                        <th class="px-3 py-2">Parcela</th>
                        <th class="px-3 py-2">Vencimento</th>
                        <th class="px-3 py-2">Pagar</th>
                        <th class="px-3 py-2 text-center">Remover</th>
                    </tr>
                </thead>
                <tbody>`;

    clientes.forEach((item, i) => {
        const rc = i % 2 === 0 ? 'hover:bg-emerald-900/30' : 'bg-emerald-950/20 hover:bg-emerald-900/30';
        const venc = item.vencimento ? new Date(item.vencimento).toLocaleDateString('pt-BR') : '-';
        html += `<tr class="${rc} text-xs border-b border-gray-700">
            <td class="px-3 py-2 text-white">${item.administradora || '-'}</td>
            <td class="px-3 py-2 text-white">${item.cliente_nome || '-'}</td>
            <td class="px-3 py-2 text-white">${item.contrato_codigo || '-'}</td>
            <td class="px-3 py-2 text-white">${item.parcela || '-'}</td>
            <td class="px-3 py-2 text-white">${venc}</td>
            <td class="px-3 py-2 text-emerald-400 font-bold">${formatMoney(item.valor_comissao)}</td>
            <td class="px-3 py-2 text-center">
                <button class="btn-remover-confirmado text-red-400 hover:text-red-200 font-bold text-base"
                    data-id="${item.id}" title="Remover">✕</button>
            </td>
        </tr>`;
    });

    html += `</tbody></table></div>
        <div class="flex justify-end mt-3 px-1">
            <button class="btn-finalizar-selecionados flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-white text-sm shadow-lg"
                style="background: linear-gradient(135deg,#7c3aed,#db2777)"
                data-url="${url}"
                data-frequencia="${freq}"
                data-parceiro-id="${corretorId}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Finalizar Folha (${contratos} parcela${contratos != 1 ? 's' : ''} · ${formatMoney(total)})
            </button>
        </div>`;

    return html;
}

/* ===== RENDERIZAR DETALHES DO CORRETOR (carga inicial) ===== */
async function renderizarDetalhesCorretor(data, planoSelecionado = '1') {
    if (!data.success) {
        DOM.clientesLista.innerHTML = `<div class="text-center p-8 bg-gray-800 rounded-lg">
            <p class="text-gray-300">Nenhum cliente encontrado para este corretor</p></div>`;
        return;
    }

    if (data.resumo) {
        DOM.clientesHeader.innerHTML = renderizarHeader(data, corretorAtualDetalhes);
    }

    // Em PARCEIROS_MODE, primeiro card = Confirmados (carregado do DB)
    if (window.PARCEIROS_MODE) {
        try {
            const r  = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorAtualDetalhes}&plano_id=confirmados`);
            const d  = await r.json();
            const resumoConf    = d.resumo?.confirmados;
            const totalValeInit = parseFloat(d.resumo?.vale?.total_comissao || 0);
            const resumoConfNet = resumoConf ? { ...resumoConf, valor_total: Math.max(0, parseFloat(resumoConf.valor_total || 0) - totalValeInit) } : null;
            DOM.clientesLista.innerHTML = renderizarTabelaConfirmados(d.clientes || [], resumoConfNet, corretorAtualDetalhes, d.frequencia);
        } catch (err) {
            DOM.clientesLista.innerHTML = renderizarTabelaConfirmados([], null, corretorAtualDetalhes, 'mensal');
        }
        setTimeout(() => setActiveCard('confirmados'), 30);
        adicionarFiltroTabelaClientes();
        return;
    }

    let html = '';
    if (data.clientes?.length > 0) {
        if (data.odonto) {
            html += `<div class="mb-1 p-3 bg-purple-700 text-white rounded-lg text-sm">
                <i class="fas fa-tooth mr-2"></i> Contratos Odontológicos - Pendentes de Pagamento
            </div>
            <div id="container_table" class="overflow-x-auto max-h-[400px] overflow-y-auto border border-gray-700 rounded-lg">
                <table id="table-clientes" class="table-auto border-collapse border border-gray-700 w-full text-sm text-left text-gray-400">
                    <thead class="bg-gray-700 text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2 border border-gray-600">Nome</th>
                            <th class="px-4 py-2 border border-gray-600">Valor</th>
                            <th class="px-4 py-2 border border-gray-600">Comissão</th>
                            <th class="px-4 py-2 border border-gray-600">Data</th>
                        </tr>
                    </thead>
                    <tbody>`;
            data.clientes.forEach((c, i) => {
                const rc = i % 2 === 0 ? 'bg-[rgba(254,254,254,0.05)]' : 'bg-[rgba(254,254,254,0.1)]';
                html += `<tr class="${rc} hover:bg-gray-600 transition-fast">
                    <td class="px-4 py-2 border border-gray-600 text-white">${c.nome}</td>
                    <td class="px-4 py-2 border border-gray-600 text-white">${formatMoney(c.valor)}</td>
                    <td class="px-4 py-2 border border-gray-600 text-white font-bold">${formatMoney(c.comissao)}</td>
                    <td class="px-4 py-2 border border-gray-600 text-white">${new Date(c.created_at).toLocaleDateString('pt-BR')}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        } else {
            html = renderizarTabelaPrincipal(data);
        }
    } else {
        html = `<div class="text-center p-8 bg-gray-800 rounded-lg">
            <i class="fas fa-info-circle text-2xl text-gray-400 mb-3"></i>
            <p class="text-gray-300">Nenhum cliente encontrado para este corretor</p>
        </div>`;
    }

    DOM.clientesLista.innerHTML = html;
    adicionarFiltroTabelaClientes();
}

/* ===== ATUALIZAR TABELA (clique nos cards) ===== */
async function atualizarTabelaClientes(data) {
    const tipo = data.tipo;
    let html = '';

    // ── Confirmados (carrega do DB) ──
    if (tipo === 'confirmados') {
        try {
            const r  = await fetch(`/folha/api/clientes-corretor?corretor_id=${corretorAtualDetalhes}&plano_id=confirmados`);
            const d  = await r.json();
            const resumoConf    = d.resumo?.confirmados;
            const totalValeTab  = parseFloat(d.resumo?.vale?.total_comissao || 0);
            const resumoConfNet = resumoConf ? { ...resumoConf, valor_total: Math.max(0, parseFloat(resumoConf.valor_total || 0) - totalValeTab) } : null;
            $('#clientes-itens').html(renderizarTabelaConfirmados(d.clientes || [], resumoConfNet, corretorAtualDetalhes, d.frequencia));
        } catch (err) {
            $('#clientes-itens').html(renderizarTabelaConfirmados([], null, corretorAtualDetalhes, 'mensal'));
        }
        adicionarFiltroTabelaClientes();
        return;
    }

    if (data.success && data.clientes?.length > 0) {
        if (data.odonto) {
            html += `<div class="mb-1 p-3 bg-yellow-500 text-center font-bold text-white rounded-lg text-lg frase-plano">${data.frase}</div>
                <div id="container_table" class="overflow-x-auto max-h-[400px] overflow-y-auto border border-gray-700 rounded-lg">
                    <table id="table-clientes" class="table-auto border-collapse border border-gray-700 w-full text-sm text-left text-gray-400">
                        <thead class="bg-gray-700 text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="px-3 py-2 border border-gray-600 text-xs">Nome</th>
                                <th class="px-3 py-2 border border-gray-600 text-xs">Valor</th>
                                <th class="px-3 py-2 border border-gray-600 text-xs">Comissão</th>
                                <th class="px-3 py-2 border border-gray-600 text-xs">Data</th>
                            </tr>
                        </thead>
                        <tbody>`;
            data.clientes.forEach((c, i) => {
                const rc = `${i % 2 === 0 ? 'bg-[rgba(254,254,254,0.05)]' : 'bg-[rgba(254,254,254,0.1)]'} ${c.manualmente ? 'bg-red-400 text-white font-bold' : ''}`;
                html += `<tr class="${rc} hover:bg-gray-600 text-xs">
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.nome}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${formatMoney(c.valor)}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white font-bold text-xs">${formatMoney(c.comissao)}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${new Date(c.created_at).toLocaleDateString('pt-BR')}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;

        } else if (data.desconto) {
            html += `<div class="mb-1 p-3 bg-yellow-500 text-center font-bold text-white rounded-lg text-lg frase-plano">${data.frase}</div>
                <div id="container_table" class="overflow-x-auto max-h-[400px] overflow-y-auto border border-gray-700 rounded-lg">
                    <table id="table-clientes" class="table-auto border-collapse border border-gray-700 w-full text-sm text-left text-gray-400">
                        <thead class="bg-gray-700 text-gray-300 uppercase text-xs"><tr>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Administradora</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Plano</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Data</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Cod.</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Cliente</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Parcela</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Vencimento</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Valor Plano</th>
                            <th class="px-4 py-2 border border-gray-600 text-xs">Desconto</th>
                        </tr></thead>
                        <tbody>`;
            data.clientes.forEach(c => {
                html += `<tr class="hover:bg-gray-600 text-xs">
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.administradora}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.plano}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${new Date(c.data_cadastro).toLocaleDateString('pt-BR')}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.contrato_codigo}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.cliente_nome}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.parcela}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${new Date(c.vencimento).toLocaleDateString('pt-BR')}</td>
                    <td class="px-3 py-2 border border-gray-600 text-white text-xs">${c.valor_plano.toLocaleString('pt-BR', {style:'currency',currency:'BRL'})}</td>
                    <td class="px-3 py-2 border border-gray-600 text-green-400 text-xs">${c.desconto.toLocaleString('pt-BR', {style:'currency',currency:'BRL'})}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;

        } else {
            html = renderizarTabelaPrincipal(data);
        }

    } else if (data.premiacao && !['coletivo','empresarial','estorno'].includes(tipo)) {
        html = `<div class="flex flex-wrap mt-3 border border-white rounded p-2 w-[80%] mx-auto">
            <label for="valor_premiacao" class="font-medium text-white text-lg">Valor Premiação:</label>
            <input type="text" name="valor_premiacao" value="${formatMoneySemCifrao(data.valor)}" id="valor_premiacao" class="w-[100%] rounded bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
            <button class="adicionar-premiacao focus:outline-none w-[100%] mt-4 text-white bg-green-700 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-lg px-5 py-2.5 me-2 mb-2">Adicionar Premiação</button>
        </div>`;
    } else if (data.fixo && !['coletivo','empresarial','estorno'].includes(tipo)) {
        html = `<div class="flex flex-wrap mt-3 border border-white rounded p-2 w-[80%] mx-auto">
            <label for="valor_fixo" class="font-medium text-white text-lg">Valor Fixo:</label>
            <input type="text" name="valor_fixo" value="${formatMoneySemCifrao(data.valor)}" id="valor_fixo" class="w-[100%] rounded bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
            <button class="adicionar-fixo focus:outline-none w-[100%] mt-4 text-white bg-green-700 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-lg px-5 py-2.5 me-2 mb-2">Adicionar Fixo</button>
        </div>`;
    } else if (data.vale && !['coletivo','empresarial','estorno'].includes(tipo)) {
        html = `<div class="flex flex-wrap mt-3 border border-white rounded p-2 w-[80%] mx-auto">
            <label for="valor_vale" class="font-medium text-white text-lg">Valor Vale:</label>
            <input type="text" name="valor_vale" value="${formatMoneySemCifrao(data.valor)}" id="valor_vale" class="w-[100%] rounded bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
            <button class="adicionar-vale focus:outline-none w-[100%] mt-4 text-white bg-green-700 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-lg px-5 py-2.5 me-2 mb-2">Adicionar Vale</button>
        </div>`;
    } else {
        html = `<div class="text-center p-8 bg-gray-800 rounded-lg">
            <i class="fas fa-info-circle text-2xl text-gray-400 mb-3"></i>
            <p class="text-gray-300">Nenhum cliente encontrado para este corretor</p>
        </div>`;
    }

    $('#clientes-itens').html(html);
    adicionarFiltroTabelaClientes();
}
