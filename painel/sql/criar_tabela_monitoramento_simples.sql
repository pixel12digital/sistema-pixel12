-- Script simples para criar tabela de monitoramento de clientes
-- Execute este script no phpMyAdmin

-- Criar tabela de monitoramento
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

-- Verificar se a tabela foi criada
SELECT 'Tabela clientes_monitoramento criada com sucesso!' as resultado; 