-- Tabela para configurações do sistema
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json') NOT NULL DEFAULT 'texto',
  `data_criacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`),
  KEY `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão
INSERT IGNORE INTO `configuracoes` (`chave`, `valor`, `descricao`, `tipo`) VALUES
('asaas_api_key', '', 'Chave da API do Asaas', 'texto'),
('asaas_ambiente', 'sandbox', 'Ambiente do Asaas (sandbox/production)', 'texto'),
('whatsapp_webhook_url', '', 'URL do webhook do WhatsApp', 'texto'),
('whatsapp_vps_url', 'http://212.85.11.238:3000', 'URL do servidor VPS do WhatsApp', 'texto'),
('sistema_nome', 'Pixel12 Digital', 'Nome do sistema', 'texto'),
('sistema_versao', '2.0', 'Versão do sistema', 'texto'),
('monitoramento_ativo', '1', 'Monitoramento automático ativo', 'booleano'),
('max_mensagens_dia', '50', 'Máximo de mensagens por dia', 'numero'),
('horario_inicio_envio', '09:00', 'Horário de início para envio de mensagens', 'texto'),
('horario_fim_envio', '18:00', 'Horário de fim para envio de mensagens', 'texto');

-- Verificar se a tabela foi criada
SELECT 'Tabela configuracoes criada com sucesso!' as resultado; 