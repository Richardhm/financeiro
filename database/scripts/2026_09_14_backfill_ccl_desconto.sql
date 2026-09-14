-- =====================================================================
-- Migracao: desconto do vendedor passa a viver na PARCELA (ccl.desconto)
-- Rodar UMA vez em producao, JUNTO com o deploy do codigo de 2026-09-14.
--
-- Modelo novo:
--   * A folha subtrai somente ccl.desconto (nunca mais o desconto do
--     contrato por conta propria) — fim do desconto cobrado 2x.
--   * Lancamento manual da backoffice = valor LIQUIDO final (desconto 0).
--   * Lancamento automatico grava o desconto do contrato UMA vez, na
--     primeira parcela com valor.
-- =====================================================================

START TRANSACTION;

-- 1. Historico: parcelas ja pagas em folha replicam o desconto que foi
--    subtraido na epoca (por linha) — relatorios antigos continuam batendo
UPDATE comissoes_corretores_lancadas ccl
JOIN comissoes c ON ccl.comissoes_id = c.id
LEFT JOIN contratos ct ON c.contrato_id = ct.id
LEFT JOIN contrato_empresarial ce ON c.contrato_empresarial_id = ce.id
SET ccl.desconto = COALESCE(ct.desconto_corretor, 0) + COALESCE(ce.desconto_corretor, 0)
WHERE ccl.finalizado = 1 AND ccl.valor != 0;

-- 2. Pendentes: desconto do contrato na MENOR parcela pendente com valor
--    (uma vez por contrato), somente se nenhuma parcela finalizada ja cobrou
UPDATE comissoes_corretores_lancadas ccl
JOIN comissoes c ON ccl.comissoes_id = c.id
LEFT JOIN contratos ct ON c.contrato_id = ct.id
LEFT JOIN contrato_empresarial ce ON c.contrato_empresarial_id = ce.id
JOIN (
    SELECT comissoes_id, MIN(parcela) mp
    FROM comissoes_corretores_lancadas
    WHERE finalizado != 1 AND valor != 0
    GROUP BY comissoes_id
) m ON m.comissoes_id = ccl.comissoes_id AND m.mp = ccl.parcela
LEFT JOIN (
    SELECT DISTINCT comissoes_id
    FROM comissoes_corretores_lancadas
    WHERE finalizado = 1 AND valor != 0
) f ON f.comissoes_id = ccl.comissoes_id
SET ccl.desconto = COALESCE(ct.desconto_corretor, 0) + COALESCE(ce.desconto_corretor, 0)
WHERE ccl.finalizado != 1 AND ccl.valor != 0 AND f.comissoes_id IS NULL;

-- 3. Coletivos lancados manualmente pela backoffice com valor JA LIQUIDO
--    (Giselly: Angela Cunha, Leia Tereza, Leysa Alves): converter para
--    BRUTO (liquido + desconto) com o desconto visivel na parcela.
--    Liquido a pagar continua 178,26 / 178,26 / 73,50.
UPDATE comissoes_corretores_lancadas SET valor = 254.66, desconto = 76.40 WHERE id IN (90150, 90143) AND valor = 178.26;
UPDATE comissoes_corretores_lancadas SET valor = 105.01, desconto = 31.51 WHERE id = 90129 AND valor = 73.50;

-- 4. Contrato da Leysa Alves Soares com ano digitado errado (2006 -> 2026)
UPDATE contratos SET created_at = '2026-08-19 21:00:00' WHERE id = 13715 AND created_at LIKE '2006-08-19%';

COMMIT;
