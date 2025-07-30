<?php
/**
 * Criar Tabela de Relatórios de Verificação
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🗄️ Criando Tabela de Relatórios de Verificação</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // SQL para criar a tabela
    $sql = "CREATE TABLE IF NOT EXISTS `relatorios_verificacao` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $result = $mysqli->query($sql);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Tabela 'relatorios_verificacao' criada com sucesso!</p>";
        
        // Adicionar comentário na tabela
        $sql_comment = "ALTER TABLE `relatorios_verificacao` COMMENT='Relatórios de verificação diária do sistema de monitoramento'";
        $mysqli->query($sql_comment);
        
        echo "<p style='color: green;'>✅ Comentário adicionado à tabela!</p>";
        
        // Verificar se a tabela foi criada
        $sql_check = "SHOW TABLES LIKE 'relatorios_verificacao'";
        $result_check = $mysqli->query($sql_check);
        
        if ($result_check && $result_check->num_rows > 0) {
            echo "<p style='color: green;'>✅ Tabela confirmada no banco de dados!</p>";
            
            // Mostrar estrutura da tabela
            $sql_structure = "DESCRIBE relatorios_verificacao";
            $result_structure = $mysqli->query($sql_structure);
            
            echo "<h2>📋 Estrutura da Tabela</h2>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
            echo "</tr>";
            
            while ($row = $result_structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>{$row['Default']}</td>";
                echo "<td>{$row['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<p style='color: red;'>❌ Erro: Tabela não foi criada corretamente</p>";
        }
        
    } else {
        throw new Exception("Erro ao criar tabela: " . $mysqli->error);
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Tabela criada em " . date('d/m/Y H:i:s') . "</em></p>";
?> 