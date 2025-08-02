-- Criar tabelas que faltaram no instalador

USE loja_virtual_revenda;

-- Tabela bloqueios_ana
CREATE TABLE IF NOT EXISTS `bloqueios_ana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_cliente` varchar(20) NOT NULL,
  `motivo` enum('transferencia_humano','solicitacao_manual','problema_tecnico','outros') DEFAULT 'transferencia_humano',
  `data_bloqueio` datetime NOT NULL,
  `data_desbloqueio` datetime NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `observacoes` text NULL,
  `criado_por` varchar(50) DEFAULT 'sistema',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_numero_cliente` (`numero_cliente`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_data_bloqueio` (`data_bloqueio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela agentes_notificacao
CREATE TABLE IF NOT EXISTS `agentes_notificacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `numero_whatsapp` varchar(20) NOT NULL,
  `departamentos` text NOT NULL COMMENT 'JSON com departamentos que atende',
  `ativo` tinyint(1) DEFAULT 1,
  `horario_inicio` time DEFAULT '08:00:00',
  `horario_fim` time DEFAULT '18:00:00',
  `dias_semana` varchar(20) DEFAULT '1,2,3,4,5' COMMENT 'Dias da semana que trabalha',
  `prioridade` int(11) DEFAULT 1 COMMENT 'Prioridade para receber notificações',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_numero` (`numero_whatsapp`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir Rafael como agente padrão
INSERT INTO `agentes_notificacao` (`nome`, `numero_whatsapp`, `departamentos`, `ativo`, `prioridade`) 
VALUES 
('Rafael - Sites/Ecommerce', '5547973095525', '["SITES","COM"]', 1, 1),
('Financeiro - Pixel12Digital', '5547973095525', '["FIN"]', 1, 2),
('Suporte - Pixel12Digital', '5547973095525', '["SUP"]', 1, 2),
('Administrativo - Pixel12Digital', '5547973095525', '["ADM"]', 1, 2)
ON DUPLICATE KEY UPDATE 
nome = VALUES(nome),
departamentos = VALUES(departamentos);

-- Tabela sistema_config se não existir
CREATE TABLE IF NOT EXISTS `sistema_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NULL,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 