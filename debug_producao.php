<?php
/**
 * 🐛 DEBUG COMPLETO - AMBIENTE DE PRODUÇÃO
 * Página de debug para identificar problemas em tempo real
 * Acesse: https://app.pixel12digital.com.br/debug_producao.php
 */

// Forçar ambiente de produção
$_SERVER['SERVER_NAME'] = 'app.pixel12digital.com.br';
$_SERVER['DOCUMENT_ROOT'] = '/home/u342734079/domains/app.pixel12digital.com.br/public_html';

// Incluir configurações
require_once 'config.php';
require_once 'painel/db.php';

// Ativar debug temporariamente
define('DEBUG_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐛 Debug Produção - Pixel12Digital</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #fff5f5; }
        .success { border-left-color: #28a745; background: #f8fff9; }
        .warning { border-left-color: #ffc107; background: #fffbf0; }
        .test-form { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .log-area { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-ok { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .refresh-btn { position: fixed; top: 20px; right: 20px; z-index: 1000; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐛 DEBUG COMPLETO - AMBIENTE DE PRODUÇÃO</h1>
            <p>Pixel12Digital - Sistema de WhatsApp com Ana</p>
            <p>Timestamp: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <button class="btn refresh-btn" onclick="location.reload()">🔄 Atualizar</button>

        <!-- 1. CONFIGURAÇÕES DO SISTEMA -->
        <div class="section">
            <h2>⚙️ 1. CONFIGURAÇÕES DO SISTEMA</h2>
            <table>
                <tr>
                    <td><strong>Ambiente:</strong></td>
                    <td><span class="status status-ok">PRODUÇÃO</span></td>
                </tr>
                <tr>
                    <td><strong>WHATSAPP_ROBOT_URL:</strong></td>
                    <td><?php echo defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : '<span class="status status-error">NÃO DEFINIDO</span>'; ?></td>
                </tr>
                <tr>
                    <td><strong>DEBUG_MODE:</strong></td>
                    <td><span class="status <?php echo defined('DEBUG_MODE') && DEBUG_MODE ? 'status-warning' : 'status-ok'; ?>"><?php echo defined('DEBUG_MODE') && DEBUG_MODE ? 'ATIVO' : 'INATIVO'; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Banco de Dados:</strong></td>
                    <td><?php echo defined('DB_HOST') ? DB_HOST : '<span class="status status-error">NÃO CONFIGURADO</span>'; ?></td>
                </tr>
            </table>
        </div>

        <!-- 2. STATUS DO VPS -->
        <div class="section">
            <h2>🖥️ 2. STATUS DO VPS (212.85.11.238:3000)</h2>
            <?php
            $vps_url = defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'http://212.85.11.238:3000';
            $status_url = "$vps_url/status";
            
            $ch = curl_init($status_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($curl_error) {
                echo '<div class="error"><strong>❌ Erro cURL:</strong> ' . $curl_error . '</div>';
            } else if ($http_code == 200) {
                $status_data = json_decode($response, true);
                echo '<div class="success">';
                echo '<strong>✅ VPS Online</strong><br>';
                echo 'Status: ' . ($status_data['status'] ?? 'N/A') . '<br>';
                echo 'Porta: ' . ($status_data['port'] ?? 'N/A') . '<br>';
                echo 'Ready: ' . ($status_data['ready'] ? 'SIM' : 'NÃO') . '<br>';
                
                if (isset($status_data['clients_status']['default'])) {
                    $client = $status_data['clients_status']['default'];
                    echo 'WhatsApp Conectado: ' . ($client['ready'] ? 'SIM' : 'NÃO') . '<br>';
                    echo 'QR Code Necessário: ' . ($client['hasQR'] ? 'SIM' : 'NÃO');
                }
                echo '</div>';
            } else {
                echo '<div class="error"><strong>❌ VPS Offline</strong> - HTTP: ' . $http_code . '</div>';
            }
            ?>
        </div>

        <!-- 3. TESTE DE ENVIO DIRETO -->
        <div class="section">
            <h2>📤 3. TESTE DE ENVIO DIRETO VIA VPS</h2>
            <div class="test-form">
                <form method="post" action="">
                    <input type="hidden" name="action" value="test_send">
                    <label>Número: <input type="text" name="number" value="554796164699" style="width: 200px;"></label><br><br>
                    <label>Mensagem: <input type="text" name="message" value="🧪 Teste debug produção - <?php echo date('H:i:s'); ?>" style="width: 400px;"></label><br><br>
                    <button type="submit" class="btn btn-success">📤 Enviar Teste</button>
                </form>
            </div>
            
            <?php
            if (isset($_POST['action']) && $_POST['action'] == 'test_send') {
                $send_url = "$vps_url/send/text";
                $data_envio = [
                    "number" => $_POST['number'],
                    "message" => $_POST['message']
                ];
                
                echo '<div class="log-area">';
                echo "📤 Enviando para: $send_url\n";
                echo "📤 Dados: " . json_encode($data_envio) . "\n";
                
                $ch_send = curl_init($send_url);
                curl_setopt($ch_send, CURLOPT_POST, true);
                curl_setopt($ch_send, CURLOPT_POSTFIELDS, json_encode($data_envio));
                curl_setopt($ch_send, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch_send, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_send, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch_send, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch_send, CURLOPT_SSL_VERIFYHOST, false);
                
                $response_send = curl_exec($ch_send);
                $curl_error_send = curl_error($ch_send);
                $http_code_send = curl_getinfo($ch_send, CURLINFO_HTTP_CODE);
                curl_close($ch_send);
                
                echo "📡 HTTP Code: $http_code_send\n";
                echo "📡 Response: $response_send\n";
                
                if ($curl_error_send) {
                    echo "❌ Erro cURL: $curl_error_send\n";
                } else if ($http_code_send == 200) {
                    $response_data = json_decode($response_send, true);
                    if (isset($response_data['success']) && $response_data['success']) {
                        echo "✅ ENVIO BEM-SUCEDIDO!\n";
                    } else {
                        echo "❌ Erro na resposta: " . ($response_data['message'] ?? 'Erro desconhecido') . "\n";
                    }
                } else {
                    echo "❌ Erro HTTP: $http_code_send\n";
                }
                echo '</div>';
            }
            ?>
        </div>

        <!-- 4. TESTE DO WEBHOOK -->
        <div class="section">
            <h2>🔗 4. TESTE DO WEBHOOK (Ana)</h2>
            <div class="test-form">
                <form method="post" action="">
                    <input type="hidden" name="action" value="test_webhook">
                    <label>Número: <input type="text" name="webhook_number" value="554796164699" style="width: 200px;"></label><br><br>
                    <label>Mensagem: <input type="text" name="webhook_message" value="🧪 Teste webhook Ana - <?php echo date('H:i:s'); ?>" style="width: 400px;"></label><br><br>
                    <button type="submit" class="btn btn-success">🤖 Testar Ana</button>
                </form>
            </div>
            
            <?php
            if (isset($_POST['action']) && $_POST['action'] == 'test_webhook') {
                $webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';
                $payload = [
                    'event' => 'onmessage',
                    'data' => [
                        'from' => $_POST['webhook_number'],
                        'text' => $_POST['webhook_message'],
                        'type' => 'text',
                        'timestamp' => time(),
                        'session' => 'default'
                    ]
                ];
                
                echo '<div class="log-area">';
                echo "📤 Enviando para webhook: $webhook_url\n";
                echo "📤 Payload: " . json_encode($payload) . "\n";
                
                $ch_webhook = curl_init($webhook_url);
                curl_setopt($ch_webhook, CURLOPT_POST, true);
                curl_setopt($ch_webhook, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch_webhook, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'User-Agent: WhatsApp-API/1.0'
                ]);
                curl_setopt($ch_webhook, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_webhook, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch_webhook, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch_webhook, CURLOPT_SSL_VERIFYHOST, false);
                
                $response_webhook = curl_exec($ch_webhook);
                $curl_error_webhook = curl_error($ch_webhook);
                $http_code_webhook = curl_getinfo($ch_webhook, CURLINFO_HTTP_CODE);
                curl_close($ch_webhook);
                
                echo "📡 HTTP Code: $http_code_webhook\n";
                echo "📡 Response: $response_webhook\n";
                
                if ($curl_error_webhook) {
                    echo "❌ Erro cURL: $curl_error_webhook\n";
                } else if ($http_code_webhook == 200) {
                    $response_data = json_decode($response_webhook, true);
                    if (isset($response_data['success']) && $response_data['success']) {
                        echo "✅ WEBHOOK PROCESSADO!\n";
                        echo "✅ Fonte: " . ($response_data['source'] ?? 'N/A') . "\n";
                        if (isset($response_data['ana_response'])) {
                            echo "✅ Ana respondeu: " . substr($response_data['ana_response'], 0, 100) . "...\n";
                        }
                    } else {
                        echo "❌ Erro no webhook: " . ($response_data['message'] ?? 'Erro desconhecido') . "\n";
                    }
                } else {
                    echo "❌ Erro HTTP: $http_code_webhook\n";
                }
                echo '</div>';
            }
            ?>
        </div>

        <!-- 5. MENSAGENS RECENTES -->
        <div class="section">
            <h2>📋 5. MENSAGENS RECENTES NO BANCO</h2>
            <?php
            $sql = "SELECT * FROM mensagens_comunicacao 
                    WHERE numero_whatsapp = '554796164699' 
                    ORDER BY data_hora DESC 
                    LIMIT 10";
            
            $result = $mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>Hora</th><th>Direção</th><th>Status</th><th>Mensagem</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    $direcao = $row['direcao'] == 'recebido' ? '📥' : '📤';
                    $status = $row['status'];
                    $hora = date('H:i:s', strtotime($row['data_hora']));
                    $msg = substr($row['mensagem'], 0, 50) . (strlen($row['mensagem']) > 50 ? '...' : '');
                    
                    echo "<tr>";
                    echo "<td>$hora</td>";
                    echo "<td>$direcao</td>";
                    echo "<td>$status</td>";
                    echo "<td>$msg</td>";
                    echo "</tr>";
                }
                echo '</table>';
            } else {
                echo '<div class="warning">Nenhuma mensagem encontrada</div>';
            }
            ?>
        </div>

        <!-- 6. ESTATÍSTICAS -->
        <div class="section">
            <h2>📊 6. ESTATÍSTICAS DE HOJE</h2>
            <?php
            $sql_stats = "SELECT 
                COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
                COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas
                FROM mensagens_comunicacao 
                WHERE numero_whatsapp = '554796164699' 
                AND DATE(data_hora) = CURDATE()";
            
            $result_stats = $mysqli->query($sql_stats);
            if ($result_stats && $result_stats->num_rows > 0) {
                $stats = $result_stats->fetch_assoc();
                echo '<table>';
                echo '<tr><th>Métrica</th><th>Valor</th><th>Status</th></tr>';
                echo '<tr><td>📥 Mensagens Recebidas</td><td>' . $stats['recebidas'] . '</td><td><span class="status status-ok">OK</span></td></tr>';
                echo '<tr><td>📤 Mensagens Enviadas</td><td>' . $stats['enviadas'] . '</td><td><span class="status status-ok">OK</span></td></tr>';
                
                $diferenca = $stats['recebidas'] - $stats['enviadas'];
                if ($diferenca > 0) {
                    echo '<tr><td>⚠️ Diferença</td><td>' . $diferenca . ' não respondidas</td><td><span class="status status-warning">PROBLEMA</span></td></tr>';
                } else {
                    echo '<tr><td>✅ Balanço</td><td>Equilibrado</td><td><span class="status status-ok">OK</span></td></tr>';
                }
                echo '</table>';
            }
            ?>
        </div>

        <!-- 7. LOGS DO SISTEMA -->
        <div class="section">
            <h2>📝 7. LOGS DO SISTEMA</h2>
            <div class="log-area">
                <?php
                $log_file = ini_get('error_log');
                if (empty($log_file)) {
                    $log_file = '/var/log/apache2/error.log';
                }
                
                if (file_exists($log_file)) {
                    $logs = file($log_file);
                    $recent_logs = array_slice($logs, -20);
                    foreach ($recent_logs as $log) {
                        if (strpos($log, 'WEBHOOK') !== false || 
                            strpos($log, 'ANA') !== false || 
                            strpos($log, 'WHATSAPP') !== false ||
                            strpos($log, 'CURL') !== false) {
                            echo htmlspecialchars(trim($log)) . "\n";
                        }
                    }
                } else {
                    echo "❌ Arquivo de log não encontrado: $log_file\n";
                }
                ?>
            </div>
        </div>

        <!-- 8. DIAGNÓSTICO FINAL -->
        <div class="section">
            <h2>🎯 8. DIAGNÓSTICO FINAL</h2>
            <?php
            $problemas = [];
            
            // Verificar configurações
            if (!defined('WHATSAPP_ROBOT_URL')) {
                $problemas[] = "❌ WHATSAPP_ROBOT_URL não definido";
            }
            
            // Verificar VPS
            if ($http_code != 200) {
                $problemas[] = "❌ VPS não está respondendo (HTTP: $http_code)";
            }
            
            // Verificar banco
            if (!$mysqli->ping()) {
                $problemas[] = "❌ Conexão com banco de dados perdida";
            }
            
            if (empty($problemas)) {
                echo '<div class="success">';
                echo '<strong>✅ SISTEMA FUNCIONANDO CORRETAMENTE</strong><br>';
                echo 'Todas as verificações passaram. Se as mensagens não estão chegando, o problema pode estar nos logs de erro específicos.';
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '<strong>❌ PROBLEMAS IDENTIFICADOS:</strong><br>';
                foreach ($problemas as $problema) {
                    echo $problema . '<br>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>📋 INSTRUÇÕES DE USO</h2>
            <ol>
                <li><strong>Teste 1:</strong> Use o "Teste de Envio Direto" para verificar se o VPS está enviando mensagens</li>
                <li><strong>Teste 2:</strong> Use o "Teste do Webhook" para verificar se Ana está processando e respondendo</li>
                <li><strong>Verificação:</strong> Compare as mensagens recebidas vs enviadas nas estatísticas</li>
                <li><strong>Logs:</strong> Monitore os logs do sistema para identificar erros específicos</li>
            </ol>
        </div>
    </div>

    <script>
        // Auto-refresh a cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 