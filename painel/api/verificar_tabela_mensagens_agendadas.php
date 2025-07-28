<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Verificar se a tabela mensagens_agendadas existe
    $sql_check = "SHOW TABLES LIKE 'mensagens_agendadas'";
    $result = $mysqli->query($sql_check);
    
    if ($result && $result->num_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tabela mensagens_agendadas já existe',
            'existe' => true
        ]);
    } else {
        // Criar a tabela se não existir
        $sql_create = "CREATE TABLE IF NOT EXISTS `mensagens_agendadas` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($mysqli->query($sql_create)) {
            echo json_encode([
                'success' => true,
                'message' => 'Tabela mensagens_agendadas criada com sucesso',
                'existe' => false,
                'criada' => true
            ]);
        } else {
            throw new Exception("Erro ao criar tabela: " . $mysqli->error);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 