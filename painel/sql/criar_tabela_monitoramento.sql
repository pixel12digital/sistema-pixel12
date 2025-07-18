-- Tabela para monitoramento automático de clientes
CREATE TABLE IF NOT EXISTS `clientes_monitoramento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `monitorado` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_id` (`cliente_id`),
  KEY `idx_monitorado` (`monitorado`),
  KEY `idx_data_atualizacao` (`data_atualizacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para otimização (apenas se as tabelas existirem)
-- Verificar se a tabela cobrancas existe antes de criar índices
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cobrancas') > 0,
    'CREATE INDEX IF NOT EXISTS `idx_cobrancas_vencimento` ON `cobrancas` (`vencimento`, `status`);',
    'SELECT "Tabela cobrancas não encontrada, pulando criação de índices" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cobrancas') > 0,
    'CREATE INDEX IF NOT EXISTS `idx_cobrancas_cliente_status` ON `cobrancas` (`cliente_id`, `status`);',
    'SELECT "Tabela cobrancas não encontrada, pulando criação de índices" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 