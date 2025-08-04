<?php
/**
 * 🔍 WEBHOOK DEBUG SIMPLES
 * 
 * Apenas loga dados recebidos para entender o problema
 */

header('Content-Type: application/json');

// Capturar dados
$input = file_get_contents('php://input');
$dados = json_decode($input, true);

// Log detalhado
$timestamp = date('Y-m-d H:i:s');
$log_entry = "\n\n=== WEBHOOK DEBUG $timestamp ===\n";
$log_entry .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido') . "\n";
$log_entry .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido') . "\n";
$log_entry .= "Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'nenhum') . "\n";
$log_entry .= "Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'desconhecido') . "\n";
$log_entry .= "Raw Input: " . $input . "\n";
$log_entry .= "Parsed Data: " . print_r($dados, true) . "\n";
$log_entry .= "=== FIM DEBUG ===\n";

// Salvar log
file_put_contents(__DIR__ . '/webhook_debug.log', $log_entry, FILE_APPEND);

// Simular salvamento forçado no Canal Ana
require_once 'config.php';
require_once 'painel/db.php';

if ($dados && isset($dados['from']) && isset($dados['body'])) {
    $numero_remetente = str_replace('@c.us', '', $dados['from']);
    $mensagem = $dados['body'];
    
    // FORÇAR SALVAMENTO NO CANAL ANA
    $canal_ana = '554797146908';
    $canal_id = 36;
    
    try {
        $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, direcao, data_hora, tipo) VALUES (?, ?, ?, 'recebido', NOW(), 'text')");
        if ($stmt) {
            $stmt->bind_param('iss', $canal_id, $canal_ana, $mensagem);
            if ($stmt->execute()) {
                $message_id = $mysqli->insert_id;
                $log_entry .= "SALVOU NO CANAL ANA: ID $message_id\n";
                file_put_contents(__DIR__ . '/webhook_debug.log', "SUCESSO: Mensagem $message_id salva no canal $canal_ana\n", FILE_APPEND);
                
                // Retornar sucesso
                echo json_encode([
                    'success' => true,
                    'message_id' => $message_id,
                    'canal_correto' => $canal_ana,
                    'debug' => 'Salvo no canal correto!'
                ]);
                exit;
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/webhook_debug.log', "ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// Se chegou aqui, algo deu errado
echo json_encode(['success' => false, 'error' => 'Dados incompletos ou erro no salvamento']);

?> 