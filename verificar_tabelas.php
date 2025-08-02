<?php
require_once 'painel/db.php';

echo "🔍 Verificando tabelas necessárias...\n\n";

$tabelas = [
    'bloqueios_ana',
    'agentes_notificacao',
    'transferencias_rafael',
    'transferencias_humano',
    'logs_integracao_ana',
    'sistema_config'
];

foreach ($tabelas as $tabela) {
    $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
    if ($result->num_rows > 0) {
        echo "✅ $tabela existe\n";
    } else {
        echo "❌ $tabela NÃO existe\n";
    }
}

echo "\n🚀 Agora vou tentar criar as tabelas que faltam:\n\n";

// Criar bloqueios_ana se não existir
$sql_bloqueios = "CREATE TABLE IF NOT EXISTS `bloqueios_ana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_cliente` varchar(20) NOT NULL,
  `motivo` enum('transferencia_humano','solicitacao_manual','problema_tecnico','outros') DEFAULT 'transferencia_humano',
  `data_bloqueio` datetime NOT NULL,
  `data_desbloqueio` datetime NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `observacoes` text NULL,
  `criado_por` varchar(50) DEFAULT 'sistema',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_numero_cliente` (`numero_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql_bloqueios)) {
    echo "✅ Tabela bloqueios_ana criada/verificada\n";
} else {
    echo "❌ Erro ao criar bloqueios_ana: " . $mysqli->error . "\n";
}

// Criar agentes_notificacao se não existir
$sql_agentes = "CREATE TABLE IF NOT EXISTS `agentes_notificacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `numero_whatsapp` varchar(20) NOT NULL,
  `departamentos` text NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `prioridade` int(11) DEFAULT 1,
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_numero` (`numero_whatsapp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql_agentes)) {
    echo "✅ Tabela agentes_notificacao criada/verificada\n";
} else {
    echo "❌ Erro ao criar agentes_notificacao: " . $mysqli->error . "\n";
}

// Inserir Rafael se não existir
$rafael_insert = "INSERT IGNORE INTO `agentes_notificacao` (`nome`, `numero_whatsapp`, `departamentos`, `ativo`, `prioridade`) VALUES ('Rafael - Sites/Ecommerce', '5547973095525', '[\"SITES\",\"COM\"]', 1, 1)";

if ($mysqli->query($rafael_insert)) {
    echo "✅ Rafael configurado como agente\n";
} else {
    echo "❌ Erro ao inserir Rafael: " . $mysqli->error . "\n";
}

echo "\n🎉 Verificação concluída!\n";
?> 