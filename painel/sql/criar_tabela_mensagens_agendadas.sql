-- Tabela para mensagens agendadas
CREATE TABLE IF NOT EXISTS `mensagens_agendadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'cobranca_vencida',
  `prioridade` enum('alta','normal','baixa') NOT NULL DEFAULT 'normal',
  `data_agendada` datetime NOT NULL,
  `status` enum('agendada','enviada','cancelada','erro') NOT NULL DEFAULT 'agendada',
  `observacao` text,
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_agendada` (`data_agendada`),
  KEY `idx_prioridade` (`prioridade`),
  KEY `idx_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar se a tabela foi criada
SELECT 'Tabela mensagens_agendadas criada com sucesso!' as resultado; 