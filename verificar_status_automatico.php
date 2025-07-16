<?php
/**
 * Script para verificar status das mensagens WhatsApp automaticamente
 * Execute via cron: 0,5,10,15,20,25,30,35,40,45,50,55 * * * * php /caminho/para/verificar_status_automatico.php
 */

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Log do início da execução
$log_file = __DIR__ . '/logs/status_check_' . date('Y-m-d') . '.log';
$log_data = date('Y-m-d H:i:s') . ' - Iniciando verificação de status das mensagens' . PHP_EOL;
file_put_contents($log_file, $log_data, FILE_APPEND);

// Incluir arquivo de verificação
$verificacao_url = 'http://localhost:8080/loja-virtual-revenda/painel/api/verificar_status_mensagens.php';

try {
    $ch = curl_init($verificacao_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $http_code === 200) {
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            $log_data = date('Y-m-d H:i:s') . ' - Verificação concluída: ' . 
                       $data['mensagens_verificadas'] . ' mensagens verificadas, ' . 
                       count($data['resultados']) . ' atualizações' . PHP_EOL;
            
            // Log detalhado dos resultados
            foreach ($data['resultados'] as $resultado) {
                if (isset($resultado['status_anterior']) && isset($resultado['status_novo'])) {
                    $log_data .= date('Y-m-d H:i:s') . ' - Mensagem ID ' . $resultado['id'] . 
                                ': ' . $resultado['status_anterior'] . ' → ' . $resultado['status_novo'] . 
                                ' (WhatsApp: ' . $resultado['whatsapp_status'] . ')' . PHP_EOL;
                }
                if (isset($resultado['action']) && $resultado['action'] === 'retry_enviado') {
                    $log_data .= date('Y-m-d H:i:s') . ' - Retry enviado para mensagem ID ' . $resultado['id'] . 
                                ' (nova mensagem: ' . $resultado['new_message_id'] . ')' . PHP_EOL;
                }
            }
        } else {
            $log_data = date('Y-m-d H:i:s') . ' - Erro na verificação: ' . json_encode($data) . PHP_EOL;
        }
    } else {
        $log_data = date('Y-m-d H:i:s') . ' - Erro HTTP: ' . $http_code . ' - ' . $response . PHP_EOL;
    }
    
} catch (Exception $e) {
    $log_data = date('Y-m-d H:i:s') . ' - Exceção: ' . $e->getMessage() . PHP_EOL;
}

// Salvar log
file_put_contents($log_file, $log_data, FILE_APPEND);

// Log do fim da execução
$log_data = date('Y-m-d H:i:s') . ' - Verificação finalizada' . PHP_EOL . '---' . PHP_EOL;
file_put_contents($log_file, $log_data, FILE_APPEND);

echo "Verificação de status concluída em " . date('Y-m-d H:i:s') . "\n";
?> 