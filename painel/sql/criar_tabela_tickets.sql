-- Tabela para tickets de atendimento
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `status` enum('aberto','em_andamento','fechado','cancelado') NOT NULL DEFAULT 'aberto',
  `prioridade` enum('baixa','normal','alta','urgente') NOT NULL DEFAULT 'normal',
  `categoria` varchar(100) NOT NULL DEFAULT 'geral',
  `atendente_id` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_fechamento` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_status` (`status`),
  KEY `idx_prioridade` (`prioridade`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_atendente_id` (`atendente_id`),
  KEY `idx_data_criacao` (`data_criacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para comentários dos tickets
CREATE TABLE IF NOT EXISTS `tickets_comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `tipo` enum('interno','publico') NOT NULL DEFAULT 'publico',
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_data_criacao` (`data_criacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar se as tabelas foram criadas
SELECT 'Tabelas de tickets criadas com sucesso!' as resultado; 