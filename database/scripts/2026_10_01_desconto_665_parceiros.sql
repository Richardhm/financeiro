-- ============================================================================
-- Desconto de 6,65% (imposto) na comissao INDIVIDUAL dos parceiros
-- Pedido da backoffice em 01/10/2026. Rodar em producao JUNTO com o deploy
-- do codigo (a coluna e usada por aplicarRegraParceiro e pelo upload).
-- Alternativa a este script: rodar `php artisan migrate` no servidor
-- (migration 2026_10_01_000001) e depois apenas os UPDATEs abaixo.
-- ============================================================================

-- 1. Coluna nova na regra do parceiro (pular se ja rodou artisan migrate)
ALTER TABLE parceiros_regras_comissao
    ADD COLUMN desconto_665 TINYINT(1) NOT NULL DEFAULT 0 AFTER parcela_6_pct;

-- 2. Ativar para os 9 parceiros pedidos — somente plano Individual (plano_id=1)
--    Ana Paula Garcia (118), Carolina Olinda (18), Brenda Rosa (130),
--    Emilly Gomes (136), Evelly Gomes (49), Islene Correia (137),
--    Ivan (119), Morgana Dorneles (128), Thiago Almeida de Macedo (135)
UPDATE parceiros_regras_comissao
SET desconto_665 = 1
WHERE plano_id = 1
  AND parceiro_id IN (118, 18, 130, 136, 49, 137, 119, 128, 135);

-- Conferencia
SELECT r.id, u.name, r.plano_id, r.desconto_665
FROM parceiros_regras_comissao r JOIN users u ON u.id = r.parceiro_id
WHERE r.desconto_665 = 1;
