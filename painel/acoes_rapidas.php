<?php
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'mensagem' => 'Acesso negado', 'tipo' => 'erro']);
    exit;
}

// Incluir configurações globais
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensagem' => 'Método não permitido', 'tipo' => 'erro']);
    exit;
}

$acao = $_POST['acao'] ?? '';

try {
    switch ($acao) {
        case 'testar_webhook':
            $resultado = testarWebhook();
            break;
            
        case 'verificar_status':
            $resultado = verificarStatus();
            break;
            
        case 'limpar_logs':
            $resultado = limparLogs();
            break;
            
        case 'otimizar_sistema':
            $resultado = otimizarSistema();
            break;
            
        case 'backup_rapido':
            $resultado = backupRapido();
            break;
            
        case 'monitor_tempo_real':
            $resultado = monitorTempoReal();
            break;
            
        default:
            $resultado = ['success' => false, 'mensagem' => 'Ação não reconhecida', 'tipo' => 'erro'];
    }
    
    echo json_encode($resultado);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'mensagem' => 'Erro interno: ' . $e->getMessage(), 
        'tipo' => 'erro'
    ]);
}

function testarWebhook() {
    // Dados de teste
    $test_data = [
        'event' => 'onmessage',
        'data' => [
            'from' => '554796164699',
            'text' => 'Teste de webhook às ' . date('H:i:s'),
            'type' => 'text'
        ]
    ];
    
    // URL do webhook
    $webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';
    
    // Faz requisição POST
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'mensagem' => "Erro na conexão: $error",
            'tipo' => 'erro'
        ];
    }
    
    if ($http_code === 200) {
        // Verifica se a mensagem foi salva no banco
        $mysqli = conectarDB();
        $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE mensagem LIKE '%Teste de webhook%' AND data_hora >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        $result = $mysqli->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            return [
                'success' => true,
                'mensagem' => "✅ Webhook funcionando perfeitamente!<br>• HTTP Code: $http_code<br>• Mensagem de teste salva no banco<br>• Sistema operacional",
                'tipo' => 'sucesso'
            ];
        } else {
            return [
                'success' => false,
                'mensagem' => "⚠️ Webhook respondeu (HTTP $http_code) mas mensagem não foi salva no banco",
                'tipo' => 'erro'
            ];
        }
    } else {
        return [
            'success' => false,
            'mensagem' => "❌ Webhook não está respondendo corretamente (HTTP $http_code)",
            'tipo' => 'erro'
        ];
    }
}

function verificarStatus() {
    $mysqli = conectarDB();
    $status = [];
    
    // Verifica mensagens hoje
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    $status['mensagens_hoje'] = $row['total'];
    
    // Verifica última mensagem
    $sql = "SELECT mensagem, data_hora FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status['ultima_mensagem'] = $row['mensagem'] . ' (' . date('H:i', strtotime($row['data_hora'])) . ')';
    } else {
        $status['ultima_mensagem'] = 'Nenhuma mensagem';
    }
    
    // Verifica tamanho do log
    $log_file = '../logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
    if (file_exists($log_file)) {
        $status['tamanho_log'] = formatBytes(filesize($log_file));
    } else {
        $status['tamanho_log'] = 'Arquivo não existe';
    }
    
    // Verifica status do webhook
    $webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status['status_webhook'] = $http_code === 200 ? 'Online' : 'Offline (HTTP ' . $http_code . ')';
    
    // Verifica conexões ativas
    $sql = "SHOW STATUS LIKE 'Threads_connected'";
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status['conexoes_ativas'] = $row['Value'];
    }
    
    $mensagem = "📊 <strong>Status do Sistema:</strong><br>";
    $mensagem .= "• Mensagens hoje: <strong>{$status['mensagens_hoje']}</strong><br>";
    $mensagem .= "• Última mensagem: <strong>{$status['ultima_mensagem']}</strong><br>";
    $mensagem .= "• Status webhook: <strong>{$status['status_webhook']}</strong><br>";
    $mensagem .= "• Tamanho log: <strong>{$status['tamanho_log']}</strong><br>";
    
    if (isset($status['conexoes_ativas'])) {
        $mensagem .= "• Conexões ativas: <strong>{$status['conexoes_ativas']}</strong>";
    }
    
    return [
        'success' => true,
        'mensagem' => $mensagem,
        'tipo' => 'info'
    ];
}

function limparLogs() {
    $logs_removidos = 0;
    $espaco_liberado = 0;
    
    // Remove logs antigos (mais de 7 dias)
    $log_dir = '../logs/';
    if (is_dir($log_dir)) {
        $files = glob($log_dir . 'webhook_whatsapp_*.log');
        $data_limite = date('Y-m-d', strtotime('-7 days'));
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/webhook_whatsapp_(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                $data_arquivo = $matches[1];
                if ($data_arquivo < $data_limite) {
                    $tamanho = filesize($file);
                    if (unlink($file)) {
                        $logs_removidos++;
                        $espaco_liberado += $tamanho;
                    }
                }
            }
        }
    }
    
    // Limpa arquivos temporários
    $temp_dir = '../temp/';
    if (is_dir($temp_dir)) {
        $files = glob($temp_dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 3600) { // Mais de 1 hora
                $tamanho = filesize($file);
                if (unlink($file)) {
                    $espaco_liberado += $tamanho;
                }
            }
        }
    }
    
    $mensagem = "🧹 <strong>Limpeza Concluída:</strong><br>";
    $mensagem .= "• Logs removidos: <strong>$logs_removidos</strong><br>";
    $mensagem .= "• Espaço liberado: <strong>" . formatBytes($espaco_liberado) . "</strong><br>";
    $mensagem .= "• Sistema otimizado para melhor performance";
    
    return [
        'success' => true,
        'mensagem' => $mensagem,
        'tipo' => 'sucesso'
    ];
}

function otimizarSistema() {
    $mysqli = conectarDB();
    $otimizacoes = [];
    
    // Otimiza tabelas
    $tabelas = ['mensagens_comunicacao', 'clientes', 'canais_comunicacao'];
    foreach ($tabelas as $tabela) {
        $sql = "OPTIMIZE TABLE $tabela";
        if ($mysqli->query($sql)) {
            $otimizacoes[] = "Tabela $tabela otimizada";
        }
    }
    
    // Remove mensagens duplicadas (mesmo texto, mesmo número, mesmo minuto)
    $sql = "DELETE m1 FROM mensagens_comunicacao m1 
            INNER JOIN mensagens_comunicacao m2 
            WHERE m1.id > m2.id 
            AND m1.mensagem = m2.mensagem 
            AND m1.numero_whatsapp = m2.numero_whatsapp 
            AND ABS(TIMESTAMPDIFF(MINUTE, m1.data_hora, m2.data_hora)) < 1";
    $mysqli->query($sql);
    $duplicadas_removidas = $mysqli->affected_rows;
    
    // Limpa cache
    $cache_dir = '../cache/';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > 1800) { // Mais de 30 minutos
                unlink($file);
            }
        }
    }
    
    $mensagem = "⚡ <strong>Otimização Concluída:</strong><br>";
    $mensagem .= "• Tabelas otimizadas: <strong>" . count($otimizacoes) . "</strong><br>";
    $mensagem .= "• Duplicatas removidas: <strong>$duplicadas_removidas</strong><br>";
    $mensagem .= "• Cache limpo<br>";
    $mensagem .= "• Sistema mais rápido e eficiente";
    
    return [
        'success' => true,
        'mensagem' => $mensagem,
        'tipo' => 'sucesso'
    ];
}

function backupRapido() {
    $mysqli = conectarDB();
    $backup_dir = '../backups/';
    
    // Cria diretório se não existir
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . 'backup_rapido_' . $timestamp . '.sql';
    
    // Backup das configurações importantes
    $configuracoes = [];
    
    // Configurações de canais
    $sql = "SELECT * FROM canais_comunicacao";
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_assoc()) {
        $configuracoes['canais'][] = $row;
    }
    
    // Estatísticas de mensagens
    $sql = "SELECT COUNT(*) as total_mensagens, 
            COUNT(DISTINCT numero_whatsapp) as numeros_unicos,
            COUNT(DISTINCT cliente_id) as clientes_unicos
            FROM mensagens_comunicacao";
    $result = $mysqli->query($sql);
    $configuracoes['estatisticas'] = $result->fetch_assoc();
    
    // Salva backup
    $backup_data = [
        'timestamp' => $timestamp,
        'configuracoes' => $configuracoes,
        'versao_sistema' => '1.0'
    ];
    
    if (file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT))) {
        $tamanho = filesize($backup_file);
        $mensagem = "💾 <strong>Backup Criado:</strong><br>";
        $mensagem .= "• Arquivo: <strong>backup_rapido_$timestamp.sql</strong><br>";
        $mensagem .= "• Tamanho: <strong>" . formatBytes($tamanho) . "</strong><br>";
        $mensagem .= "• Canais: <strong>" . count($configuracoes['canais']) . "</strong><br>";
        $mensagem .= "• Mensagens: <strong>{$configuracoes['estatisticas']['total_mensagens']}</strong>";
        
        return [
            'success' => true,
            'mensagem' => $mensagem,
            'tipo' => 'sucesso'
        ];
    } else {
        return [
            'success' => false,
            'mensagem' => '❌ Erro ao criar backup',
            'tipo' => 'erro'
        ];
    }
}

function monitorTempoReal() {
    $mysqli = conectarDB();
    
    // Mensagens hoje
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()";
    $result = $mysqli->query($sql);
    $mensagens_hoje = $result->fetch_assoc()['total'];
    
    // Última mensagem
    $sql = "SELECT mensagem, data_hora FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ultima_mensagem = substr($row['mensagem'], 0, 30) . '... (' . date('H:i', strtotime($row['data_hora'])) . ')';
    } else {
        $ultima_mensagem = 'Nenhuma';
    }
    
    // Status webhook
    $webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status_webhook = $http_code === 200 ? 'Online' : 'Offline';
    
    // Tamanho log
    $log_file = '../logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
    if (file_exists($log_file)) {
        $tamanho_log = formatBytes(filesize($log_file));
    } else {
        $tamanho_log = '0 KB';
    }
    
    return [
        'success' => true,
        'dados' => [
            'mensagens_hoje' => $mensagens_hoje,
            'ultima_mensagem' => $ultima_mensagem,
            'status_webhook' => $status_webhook,
            'tamanho_log' => $tamanho_log
        ]
    ];
}

function conectarDB() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception('Erro na conexão com o banco: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 