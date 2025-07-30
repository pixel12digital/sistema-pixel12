-- Criar tabela para armazenar relatórios de verificação diária
CREATE TABLE IF NOT EXISTS `relatorios_verificacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_verificacao` datetime NOT NULL,
  `status_geral` enum('OK','PROBLEMAS') NOT NULL,
  `total_clientes_monitorados` int(11) NOT NULL DEFAULT 0,
  `clientes_sem_mensagens` int(11) NOT NULL DEFAULT 0,
  `mensagens_problematicas` int(11) NOT NULL DEFAULT 0,
  `mensagens_vencidas` int(11) NOT NULL DEFAULT 0,
  `cron_ok` tinyint(1) NOT NULL DEFAULT 0,
  `problemas_encontrados` text,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_data_verificacao` (`data_verificacao`),
  KEY `idx_status_geral` (`status_geral`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir comentário na tabela
ALTER TABLE `relatorios_verificacao` COMMENT='Relatórios de verificação diária do sistema de monitoramento'; 