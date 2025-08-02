-- 🚀 TABELAS PARA SISTEMA DE TRANSFERÊNCIAS COMPLETO
-- Executar este script para completar a implementação

-- 1. Tabela de bloqueios da Ana (para transferências para humanos)
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

-- 2. Adicionar campos que podem estar faltando na tabela transferencias_rafael
ALTER TABLE `transferencias_rafael` 
ADD COLUMN IF NOT EXISTS `data_processamento` datetime NULL AFTER `data_transferencia`,
ADD COLUMN IF NOT EXISTS `observacoes` text NULL AFTER `status`,
ADD COLUMN IF NOT EXISTS `tentativas_notificacao` int(11) DEFAULT 0 AFTER `observacoes`;

-- 3. Adicionar campos que podem estar faltando na tabela transferencias_humano
ALTER TABLE `transferencias_humano` 
ADD COLUMN IF NOT EXISTS `data_processamento` datetime NULL AFTER `data_transferencia`,
ADD COLUMN IF NOT EXISTS `observacoes` text NULL AFTER `status`,
ADD COLUMN IF NOT EXISTS `agente_responsavel` varchar(100) NULL AFTER `observacoes`;

-- 4. Tabela de configuração de agentes para notificações
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

-- 5. Inserir Rafael como agente padrão
INSERT INTO `agentes_notificacao` (`nome`, `numero_whatsapp`, `departamentos`, `ativo`, `prioridade`) 
VALUES 
('Rafael - Sites/Ecommerce', '5547973095525', '["SITES","COM"]', 1, 1),
('Financeiro - Pixel12Digital', '5547973095525', '["FIN"]', 1, 2),
('Suporte - Pixel12Digital', '5547973095525', '["SUP"]', 1, 2),
('Administrativo - Pixel12Digital', '5547973095525', '["ADM"]', 1, 2)
ON DUPLICATE KEY UPDATE 
nome = VALUES(nome),
departamentos = VALUES(departamentos);

-- 6. Tabela de log de transferências executadas
CREATE TABLE IF NOT EXISTS `log_transferencias_executadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_transferencia` enum('rafael','humano') NOT NULL,
  `numero_cliente` varchar(20) NOT NULL,
  `id_transferencia_origem` int(11) NOT NULL,
  `data_execucao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_execucao` enum('sucesso','erro','parcial') DEFAULT 'sucesso',
  `detalhes` text NULL COMMENT 'JSON com detalhes da execução',
  `tempo_processamento_ms` int(11) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo_transferencia`),
  KEY `idx_cliente` (`numero_cliente`),
  KEY `idx_data` (`data_execucao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Atualizar estrutura da tabela logs_integracao_ana se necessário
ALTER TABLE `logs_integracao_ana` 
ADD COLUMN IF NOT EXISTS `tempo_resposta_ms` int(11) NULL AFTER `status_api`,
ADD COLUMN IF NOT EXISTS `transferencia_executada` tinyint(1) DEFAULT 0 AFTER `tempo_resposta_ms`;

-- 8. Criar índices para performance
CREATE INDEX IF NOT EXISTS `idx_status_data` ON `transferencias_rafael` (`status`, `data_transferencia`);
CREATE INDEX IF NOT EXISTS `idx_status_data_humano` ON `transferencias_humano` (`status`, `data_transferencia`);
CREATE INDEX IF NOT EXISTS `idx_numero_data` ON `logs_integracao_ana` (`numero_cliente`, `data_log`);

-- 9. Tabela de estatísticas de transferências (para relatórios)
CREATE TABLE IF NOT EXISTS `estatisticas_transferencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_referencia` date NOT NULL,
  `total_transferencias_rafael` int(11) DEFAULT 0,
  `total_transferencias_humanos` int(11) DEFAULT 0,
  `tempo_medio_execucao_ms` int(11) DEFAULT 0,
  `taxa_sucesso_rafael` decimal(5,2) DEFAULT 0.00,
  `taxa_sucesso_humanos` decimal(5,2) DEFAULT 0.00,
  `ultima_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_data_ref` (`data_referencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Criar tabela de sistema_config se não existir
CREATE TABLE IF NOT EXISTS `sistema_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text NULL,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== SCRIPT CONCLUÍDO =====
-- Execute este script para ter o sistema de transferências 100% funcional 