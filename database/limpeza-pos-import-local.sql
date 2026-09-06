-- Limpa dados de teste das tabelas novas que referenciam IDs substituidos pelo dump
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE estornos;
TRUNCATE TABLE vidas_mes_vendedor;
TRUNCATE TABLE parceiros_folha_historico;
TRUNCATE TABLE dependentes_empresariais;
TRUNCATE TABLE comissoes_corretora_lancadas;
SET FOREIGN_KEY_CHECKS=1;
