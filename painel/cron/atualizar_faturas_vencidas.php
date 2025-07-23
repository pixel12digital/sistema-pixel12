<?php
/**
 * Script para atualizar automaticamente faturas vencidas
 * Executa diariamente via cron: 0 8 * * * php /caminho/para/painel/cron/atualizar_faturas_vencidas.php
 */

require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../db.php';

// Log do início da execução
$log_data = date('Y-m-d H:i:s') . " - Iniciando atualização de faturas vencidas\n";
file_put_contents(dirname(__FILE__) . '/../logs/atualizar_faturas_vencidas.log', $log_data, FILE_APPEND);

try {
    // Atualizar faturas PENDING que já venceram para OVERDUE
    $sql = "UPDATE cobrancas 
            SET status = 'OVERDUE' 
            WHERE status = 'PENDING' 
            AND vencimento < CURDATE()";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao atualizar faturas: " . $mysqli->error);
    }
    
    $faturas_atualizadas = $mysqli->affected_rows;
    
    // Log do resultado
    $log_data = date('Y-m-d H:i:s') . " - Faturas atualizadas: {$faturas_atualizadas}\n";
    file_put_contents(dirname(__FILE__) . '/../logs/atualizar_faturas_vencidas.log', $log_data, FILE_APPEND);
    
    // Se for execução via web, retornar JSON
    if (isset($_SERVER['REQUEST_METHOD'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'faturas_atualizadas' => $faturas_atualizadas,
            'message' => "Atualização concluída. {$faturas_atualizadas} faturas atualizadas.",
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Execução via CLI
        echo "✅ Atualização concluída. {$faturas_atualizadas} faturas atualizadas.\n";
    }
    
    // Buscar estatísticas para log
    $sql_stats = "SELECT 
                    COUNT(CASE WHEN status = 'PENDING' THEN 1 END) as pendentes,
                    COUNT(CASE WHEN status = 'OVERDUE' THEN 1 END) as vencidas,
                    COUNT(CASE WHEN status = 'RECEIVED' THEN 1 END) as pagas
                  FROM cobrancas";
    
    $result_stats = $mysqli->query($sql_stats);
    if ($result_stats) {
        $stats = $result_stats->fetch_assoc();
        $log_data = date('Y-m-d H:i:s') . " - Estatísticas: {$stats['pendentes']} pendentes, {$stats['vencidas']} vencidas, {$stats['pagas']} pagas\n";
        file_put_contents(dirname(__FILE__) . '/../logs/atualizar_faturas_vencidas.log', $log_data, FILE_APPEND);
    }
    
} catch (Exception $e) {
    $error_msg = "Erro na atualização: " . $e->getMessage();
    $log_data = date('Y-m-d H:i:s') . " - ERRO: {$error_msg}\n";
    file_put_contents(dirname(__FILE__) . '/../logs/atualizar_faturas_vencidas.log', $log_data, FILE_APPEND);
    
    if (isset($_SERVER['REQUEST_METHOD'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $error_msg,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo "❌ {$error_msg}\n";
    }
}

$log_data = date('Y-m-d H:i:s') . " - Execução finalizada\n\n";
file_put_contents(dirname(__FILE__) . '/../logs/atualizar_faturas_vencidas.log', $log_data, FILE_APPEND);
?> 