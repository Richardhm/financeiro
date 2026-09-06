-- =====================================================================
-- POS-IMPORTACAO: adapta o dump do grupoamerica (sistema antigo)
-- para o schema do sistema financeiro (novo). Gerado em 2026-08-20.
-- Rodar DEPOIS de importar 127_0_0_1.sql, no banco importado.
-- Idempotente: pode rodar mais de uma vez sem erro.
-- =====================================================================

-- ---------- 1. TABELAS NOVAS ----------
CREATE TABLE IF NOT EXISTS `codigos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `odonto` tinyint(1) NOT NULL DEFAULT 0,
  `tabela_origens_id` bigint(20) unsigned NOT NULL,
  `administradora_id` bigint(20) unsigned NOT NULL,
  `plano_id` bigint(20) unsigned NOT NULL,
  `coparticipacao_enfermaria` varchar(255) DEFAULT NULL,
  `coparticipacao_apartamento` varchar(255) DEFAULT NULL,
  `parcial_enfermaria` varchar(255) DEFAULT NULL,
  `parcial_apartamento` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `codigos_tabela_origens_id_foreign` (`tabela_origens_id`),
  KEY `codigos_administradora_id_foreign` (`administradora_id`),
  KEY `codigos_plano_id_foreign` (`plano_id`),
  CONSTRAINT `codigos_administradora_id_foreign` FOREIGN KEY (`administradora_id`) REFERENCES `administradoras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `codigos_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `codigos_tabela_origens_id_foreign` FOREIGN KEY (`tabela_origens_id`) REFERENCES `tabela_origens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `codigo_ambulatorial` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `odonto` tinyint(1) NOT NULL DEFAULT 0,
  `tabela_origens_id` bigint(20) unsigned NOT NULL,
  `administradora_id` bigint(20) unsigned NOT NULL,
  `plano_id` bigint(20) unsigned NOT NULL,
  `coparticipacao` varchar(255) DEFAULT NULL,
  `parcial` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `codigo_ambulatorial_tabela_origens_id_foreign` (`tabela_origens_id`),
  KEY `codigo_ambulatorial_administradora_id_foreign` (`administradora_id`),
  KEY `codigo_ambulatorial_plano_id_foreign` (`plano_id`),
  CONSTRAINT `codigo_ambulatorial_administradora_id_foreign` FOREIGN KEY (`administradora_id`) REFERENCES `administradoras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `codigo_ambulatorial_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `codigo_ambulatorial_tabela_origens_id_foreign` FOREIGN KEY (`tabela_origens_id`) REFERENCES `tabela_origens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comissoes_corretora_configuracoes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corretora_id` bigint(20) unsigned NOT NULL,
  `plano_id` bigint(20) unsigned NOT NULL,
  `administradora_id` bigint(20) unsigned NOT NULL,
  `tabela_origens_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `valor` decimal(8,2) NOT NULL,
  `parcela` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comissoes_corretora_configuracoes_corretora_id_foreign` (`corretora_id`),
  CONSTRAINT `comissoes_corretora_configuracoes_corretora_id_foreign` FOREIGN KEY (`corretora_id`) REFERENCES `corretoras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comissoes_corretora_lancadas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comissoes_id` bigint(20) unsigned NOT NULL,
  `parcela` int(11) NOT NULL,
  `data` date DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `desconto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `porcentagem_paga` decimal(10,2) DEFAULT NULL,
  `status_financeiro` tinyint(4) NOT NULL DEFAULT 0,
  `status_gerente` tinyint(4) NOT NULL DEFAULT 0,
  `status_apto_pagar` tinyint(4) NOT NULL DEFAULT 0,
  `status_comissao` tinyint(4) NOT NULL DEFAULT 0,
  `finalizado` tinyint(4) NOT NULL DEFAULT 0,
  `data_antecipacao` date DEFAULT NULL,
  `data_baixa` date DEFAULT NULL,
  `data_baixa_gerente` date DEFAULT NULL,
  `data_baixa_finalizado` date DEFAULT NULL,
  `documento_gerador` varchar(50) DEFAULT NULL,
  `estorno` tinyint(4) NOT NULL DEFAULT 0,
  `data_baixa_estorno` date DEFAULT NULL,
  `cancelados` tinyint(4) NOT NULL DEFAULT 0,
  `atual` tinyint(4) NOT NULL DEFAULT 0,
  `manualmente` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comissoes_corretora_lancadas_comissoes_id_foreign` (`comissoes_id`),
  CONSTRAINT `comissoes_corretora_lancadas_comissoes_id_foreign` FOREIGN KEY (`comissoes_id`) REFERENCES `comissoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dependentes_empresariais` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contrato_empresarial_id` bigint(20) unsigned NOT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `tipo` char(1) NOT NULL DEFAULT 'T' COMMENT 'T=Titular D=Dependente',
  `data_nascimento` date DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dependentes_empresariais_contrato_empresarial_id_foreign` (`contrato_empresarial_id`),
  CONSTRAINT `dependentes_empresariais_contrato_empresarial_id_foreign` FOREIGN KEY (`contrato_empresarial_id`) REFERENCES `contrato_empresarial` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estornos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lote` varchar(50) NOT NULL,
  `carteirinha` varchar(30) NOT NULL,
  `beneficiario` varchar(255) DEFAULT NULL,
  `cliente_id` bigint(20) unsigned DEFAULT NULL,
  `contrato_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'vendedor que recebeu/receberia a comissao',
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_cancelamento` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pendente' COMMENT 'pendente | aplicado | sem_vinculo',
  `data_aplicacao` timestamp NULL DEFAULT NULL,
  `folha_referencia` varchar(255) DEFAULT NULL COMMENT 'folha em que o estorno foi descontado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estornos_lote_carteirinha_unique` (`lote`,`carteirinha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faixas_comissao_clt` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) DEFAULT NULL,
  `corretora_id` bigint(20) unsigned NOT NULL,
  `vidas_min` int(11) NOT NULL DEFAULT 0,
  `vidas_max` int(11) DEFAULT NULL,
  `producao_min` decimal(10,2) NOT NULL DEFAULT 0.00,
  `producao_max` decimal(10,2) DEFAULT NULL,
  `producao_bonus` decimal(10,2) DEFAULT NULL,
  `percentual_bonus` decimal(5,2) DEFAULT NULL,
  `percentual` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faixas_comissao_clt_corretora_id_foreign` (`corretora_id`),
  CONSTRAINT `faixas_comissao_clt_corretora_id_foreign` FOREIGN KEY (`corretora_id`) REFERENCES `corretoras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parceiros_config_pagamento` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `frequencia` enum('semanal','quinzenal','mensal','personalizado') NOT NULL DEFAULT 'mensal',
  `dias_pagamento` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`dias_pagamento`)),
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parceiros_config_pagamento_user_id_index` (`user_id`),
  CONSTRAINT `parceiros_config_pagamento_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parceiros_folha_historico` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `corretora_id` bigint(20) unsigned NOT NULL,
  `frequencia` varchar(20) DEFAULT NULL,
  `periodo_inicio` date DEFAULT NULL,
  `periodo_fim` date DEFAULT NULL,
  `data_pagamento` date NOT NULL,
  `total_parcelas` int(11) NOT NULL DEFAULT 0,
  `total_valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_odonto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_vale` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_estorno` decimal(10,2) NOT NULL DEFAULT 0.00,
  `odonto_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`odonto_snapshot`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parceiros_folha_historico_user_id_corretora_id_index` (`user_id`,`corretora_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parceiros_regras_comissao` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corretora_id` int(10) unsigned NOT NULL,
  `parceiro_id` bigint(20) unsigned NOT NULL,
  `plano_id` int(10) unsigned NOT NULL,
  `parcela_1_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_2_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_3_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_4_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_5_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_6_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_parceiro_plano` (`corretora_id`,`parceiro_id`,`plano_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `regras_comissao_pj` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corretora_id` bigint(20) unsigned NOT NULL,
  `nome` varchar(20) DEFAULT NULL,
  `vidas_min` int(11) NOT NULL DEFAULT 0,
  `vidas_max` int(11) DEFAULT NULL,
  `parcela_1_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_2_pct` decimal(5,2) NOT NULL DEFAULT 100.00,
  `parcela_3_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_4_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_5_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `parcela_6_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `regras_comissao_pj_corretora_id_foreign` (`corretora_id`),
  CONSTRAINT `regras_comissao_pj_corretora_id_foreign` FOREIGN KEY (`corretora_id`) REFERENCES `corretoras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vidas_mes_vendedor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `mes` varchar(7) NOT NULL,
  `quantidade_individual` int(11) NOT NULL DEFAULT 0,
  `quantidade_coletivo` int(11) NOT NULL DEFAULT 0,
  `quantidade_empresarial` int(11) NOT NULL DEFAULT 0,
  `quantidade_comissao` int(11) NOT NULL DEFAULT 0,
  `quantidade_total` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vidas_mes_vendedor_user_id_mes_unique` (`user_id`,`mes`),
  CONSTRAINT `vidas_mes_vendedor_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- 2. COLUNAS NOVAS (so adiciona se nao existir) ----------
DELIMITER //
CREATE PROCEDURE add_col_se_faltar(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl_alter TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col) THEN
    SET @s = CONCAT("ALTER TABLE ", tbl, " ", ddl_alter);
    PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END//
DELIMITER ;

CALL add_col_se_faltar("comissoes_corretores_lancadas", "competencia", "ADD COLUMN `competencia` char(7) DEFAULT NULL AFTER `comissoes_id`");
CALL add_col_se_faltar("comissoes_corretores_lancadas", "parceiro_historico_id", "ADD COLUMN `parceiro_historico_id` bigint(20) unsigned DEFAULT NULL");
CALL add_col_se_faltar("contratos", "pdf_path", "ADD COLUMN `pdf_path` varchar(255) DEFAULT NULL");
CALL add_col_se_faltar("contrato_empresarial", "valor_projecao_corretora", "ADD COLUMN `valor_projecao_corretora` decimal(10,2) DEFAULT NULL");
CALL add_col_se_faltar("contrato_empresarial", "desconto_comissao_665", "ADD COLUMN `desconto_comissao_665` tinyint(1) NOT NULL DEFAULT 0");
CALL add_col_se_faltar("contrato_empresarial", "pdf_path", "ADD COLUMN `pdf_path` varchar(300) DEFAULT NULL");
CALL add_col_se_faltar("concessionarias", "status", "ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0");
CALL add_col_se_faltar("users", "tipo_contrato", "ADD COLUMN `tipo_contrato` enum(\"clt\",\"pj\",\"parceiro\") NOT NULL DEFAULT \"pj\" AFTER `clt`");
DROP PROCEDURE add_col_se_faltar;

-- ---------- 3. BACKFILL DE DADOS ----------
-- tipo_contrato a partir do campo clt antigo (parceiros: ajustar depois na tela Corretores)
UPDATE users SET tipo_contrato = "clt" WHERE clt = 1;
UPDATE users SET tipo_contrato = "pj"  WHERE clt = 0 OR clt IS NULL;

-- competencia das parcelas (mes do vencimento; fallback data_baixa)
UPDATE comissoes_corretores_lancadas SET competencia = DATE_FORMAT(data, "%Y-%m") WHERE competencia IS NULL AND data IS NOT NULL;
UPDATE comissoes_corretores_lancadas SET competencia = DATE_FORMAT(data_baixa, "%Y-%m") WHERE competencia IS NULL AND data IS NULL AND data_baixa IS NOT NULL;

-- ---------- 4. REGISTRA MIGRATIONS DO SISTEMA NOVO ----------
-- (php artisan migrate passa a nao ter nada pendente)
SET @b = (SELECT COALESCE(MAX(batch),0)+1 FROM migrations);
INSERT INTO migrations (migration, batch) SELECT "0001_01_01_000000_create_users_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "0001_01_01_000000_create_users_table");
INSERT INTO migrations (migration, batch) SELECT "0001_01_01_000001_create_cache_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "0001_01_01_000001_create_cache_table");
INSERT INTO migrations (migration, batch) SELECT "0001_01_01_000002_create_jobs_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "0001_01_01_000002_create_jobs_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100001_create_corretoras_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100001_create_corretoras_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100002_create_cargos_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100002_create_cargos_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100003_create_permissions_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100003_create_permissions_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100004_create_estagio_financeiros_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100004_create_estagio_financeiros_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100005_create_tabelas_planos_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100005_create_tabelas_planos_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100006_alter_users_add_columns", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100006_alter_users_add_columns");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100007_create_plano_relacionamentos_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100007_create_plano_relacionamentos_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100008_create_clientes_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100008_create_clientes_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100009_create_contratos_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100009_create_contratos_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100010_create_comissoes_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100010_create_comissoes_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_11_100011_create_folha_e_extras_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_11_100011_create_folha_e_extras_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_15_000001_alter_users_add_tipo_contrato", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_15_000001_alter_users_add_tipo_contrato");
INSERT INTO migrations (migration, batch) SELECT "2026_06_15_000002_alter_comissoes_lancadas_add_competencia", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_15_000002_alter_comissoes_lancadas_add_competencia");
INSERT INTO migrations (migration, batch) SELECT "2026_06_15_000003_create_faixas_comissao_clt_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_15_000003_create_faixas_comissao_clt_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_15_000004_create_parceiros_config_pagamento_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_15_000004_create_parceiros_config_pagamento_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_15_000005_backfill_competencia_comissoes_lancadas", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_15_000005_backfill_competencia_comissoes_lancadas");
INSERT INTO migrations (migration, batch) SELECT "2026_06_15_000006_create_comissoes_corretora_tables", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_15_000006_create_comissoes_corretora_tables");
INSERT INTO migrations (migration, batch) SELECT "2026_06_16_170002_add_bonus_fields_to_faixas_comissao_clt_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_16_170002_add_bonus_fields_to_faixas_comissao_clt_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_16_172129_add_nome_to_faixas_comissao_clt_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_16_172129_add_nome_to_faixas_comissao_clt_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_16_185151_create_regras_comissao_pj_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_16_185151_create_regras_comissao_pj_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_16_210000_create_parceiros_regras_comissao_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_16_210000_create_parceiros_regras_comissao_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_17_201434_add_parcela_5_6_to_parceiros_regras_comissao", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_17_201434_add_parcela_5_6_to_parceiros_regras_comissao");
INSERT INTO migrations (migration, batch) SELECT "2026_06_18_171707_add_pdf_path_to_contratos_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_18_171707_add_pdf_path_to_contratos_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_18_192342_create_parceiros_folha_historico_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_18_192342_create_parceiros_folha_historico_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_18_192343_add_parceiro_historico_id_to_comissoes_corretores_lancadas_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_18_192343_add_parceiro_historico_id_to_comissoes_corretores_lancadas_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_22_125417_add_snapshot_to_parceiros_folha_historico", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_22_125417_add_snapshot_to_parceiros_folha_historico");
INSERT INTO migrations (migration, batch) SELECT "2026_06_24_144437_create_dependentes_empresariais_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_24_144437_create_dependentes_empresariais_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_24_174522_add_pdf_path_to_contrato_empresarial_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_24_174522_add_pdf_path_to_contrato_empresarial_table");
INSERT INTO migrations (migration, batch) SELECT "2026_06_26_161424_create_vidas_mes_vendedor_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_06_26_161424_create_vidas_mes_vendedor_table");
INSERT INTO migrations (migration, batch) SELECT "2026_07_15_181131_add_desconto_comissao_665_to_contrato_empresarial_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_07_15_181131_add_desconto_comissao_665_to_contrato_empresarial_table");
INSERT INTO migrations (migration, batch) SELECT "2026_08_14_100000_add_parcelas_1_5_6_to_regras_comissao_pj", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_08_14_100000_add_parcelas_1_5_6_to_regras_comissao_pj");
INSERT INTO migrations (migration, batch) SELECT "2026_08_20_100000_create_estornos_table", @b FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = "2026_08_20_100000_create_estornos_table");
