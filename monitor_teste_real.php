<?php
require_once 'config.php';

// Conectar ao banco de dados
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("❌ Erro na conexão com o banco: " . $mysqli->connect_error);
}

echo "🔍 MONITORAMENTO EM TEMPO REAL - TESTE REAL\n";
echo "===========================================\n\n";

echo "📱 INSTRUÇÕES:\n";
echo "1. Envie uma mensagem REAL do seu WhatsApp para +55 47 9714-6908\n";
echo "2. Aguarde a resposta da Ana\n";
echo "3. Este script vai monitorar tudo em tempo real\n\n";

echo "⏳ Monitorando... (Pressione Ctrl+C para parar)\n\n";

$ultima_verificacao = 0;

while (true) {
    $agora = time();
    
    // Verificar mensagens novas a cada 3 segundos
    if ($agora - $ultima_verificacao >= 3) {
        $ultima_verificacao = $agora;
        
        // Verificar mensagens recebidas hoje
        $sql = "SELECT 
            direcao,
            mensagem,
            data_hora,
            status,
            tipo
            FROM mensagens_comunicacao 
            WHERE DATE(data_hora) = CURDATE()
            AND data_hora > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            ORDER BY data_hora DESC";
        
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "🕐 " . date('H:i:s') . " - NOVAS MENSAGENS:\n";
            
            while ($row = $result->fetch_assoc()) {
                $hora = date('H:i:s', strtotime($row['data_hora']));
                $direcao = $row['direcao'] == 'recebido' ? '📥 RECEBIDA' : '📤 ENVIADA';
                $status = $row['status'];
                $tipo = $row['tipo'];
                $msg = substr($row['mensagem'], 0, 60) . (strlen($row['mensagem']) > 60 ? '...' : '');
                
                echo "   $hora | $direcao | $status | $tipo | $msg\n";
            }
            echo "\n";
        }
        
        // Verificar logs da Ana (corrigido)
        $sql_logs = "SELECT 
            log_mensagem,
            data_log,
            tipo_log
            FROM logs_integracao_ana 
            WHERE DATE(data_log) = CURDATE()
            AND data_log > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            ORDER BY data_log DESC";
        
        $logs_result = $mysqli->query($sql_logs);
        
        if ($logs_result && $logs_result->num_rows > 0) {
            echo "🤖 " . date('H:i:s') . " - LOGS DA ANA:\n";
            
            while ($log = $logs_result->fetch_assoc()) {
                $hora = date('H:i:s', strtotime($log['data_log']));
                $tipo = $log['tipo_log'];
                $msg = substr($log['log_mensagem'], 0, 60) . (strlen($log['log_mensagem']) > 60 ? '...' : '');
                
                echo "   $hora | $tipo | $msg\n";
            }
            echo "\n";
        }
        
        // Estatísticas em tempo real
        $sql_stats = "SELECT 
            COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
            COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas
            FROM mensagens_comunicacao 
            WHERE DATE(data_hora) = CURDATE()";
        
        $stats_result = $mysqli->query($sql_stats);
        if ($stats_result) {
            $stats = $stats_result->fetch_assoc();
            echo "📊 " . date('H:i:s') . " - ESTATÍSTICAS: 📥 " . $stats['recebidas'] . " | 📤 " . $stats['enviadas'] . "\n";
        }
        
        echo "─────────────────────────────────────────────────\n";
    }
    
    sleep(1);
}

$mysqli->close();
?> 