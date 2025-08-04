<?php
/**
 * ENVIAR MENSAGENS REAIS VIA WHATSAPP
 * Envia mensagens reais para o número 554796164699 através dos canais 3000 e 3001
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "=== ENVIANDO MENSAGENS REAIS VIA WHATSAPP ===\n";
echo "Número de destino: 554796164699\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

$numero_destino = '554796164699';
$timestamp = date('H:i:s');

try {
    // 1. ENVIAR MENSAGEM VIA CANAL 3000 (Pixel12Digital)
    echo "1. ENVIANDO MENSAGEM VIA CANAL 3000:\n";
    echo "   =================================\n";
    
    $mensagem_3000 = "🧪 TESTE REAL CANAL 3000 - {$timestamp} - Mensagem enviada via API WhatsApp";
    
    // URL da API do WhatsApp (canal 3000) - ENDPOINT CORRETO
    $url_3000 = 'http://212.85.11.238:3000/send';
    
    $dados_3000 = [
        'to' => $numero_destino,
        'message' => $mensagem_3000
    ];
    
    echo "   📤 Enviando para: {$url_3000}\n";
    echo "   📱 Número: {$numero_destino}\n";
    echo "   💬 Mensagem: {$mensagem_3000}\n";
    
    // Fazer requisição HTTP
    $ch_3000 = curl_init();
    curl_setopt($ch_3000, CURLOPT_URL, $url_3000);
    curl_setopt($ch_3000, CURLOPT_POST, true);
    curl_setopt($ch_3000, CURLOPT_POSTFIELDS, json_encode($dados_3000));
    curl_setopt($ch_3000, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch_3000, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_3000, CURLOPT_TIMEOUT, 30);
    
    $response_3000 = curl_exec($ch_3000);
    $http_code_3000 = curl_getinfo($ch_3000, CURLINFO_HTTP_CODE);
    $error_3000 = curl_error($ch_3000);
    curl_close($ch_3000);
    
    if ($error_3000) {
        echo "   ❌ Erro cURL: {$error_3000}\n";
    } else {
        echo "   📊 HTTP Code: {$http_code_3000}\n";
        echo "   📄 Resposta: {$response_3000}\n";
        
        if ($http_code_3000 == 200) {
            echo "   ✅ Mensagem enviada com sucesso via canal 3000\n";
            
            // Salvar no banco
            $sql_salvar_3000 = "INSERT INTO mensagens_comunicacao 
                                (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                                VALUES (36, ?, ?, 'texto', NOW(), 'enviado', 'enviado')";
            
            $stmt_salvar_3000 = $mysqli->prepare($sql_salvar_3000);
            $stmt_salvar_3000->bind_param('ss', $numero_destino, $mensagem_3000);
            $stmt_salvar_3000->execute();
            echo "   💾 Mensagem salva no banco de dados\n";
        } else {
            echo "   ❌ Falha ao enviar mensagem via canal 3000\n";
        }
    }
    echo "\n";
    
    // 2. ENVIAR MENSAGEM VIA CANAL 3001 (Comercial)
    echo "2. ENVIANDO MENSAGEM VIA CANAL 3001:\n";
    echo "   =================================\n";
    
    $mensagem_3001 = "🧪 TESTE REAL CANAL 3001 - {$timestamp} - Mensagem enviada via API WhatsApp";
    
    // URL da API do WhatsApp (canal 3001) - ENDPOINT CORRETO
    $url_3001 = 'http://212.85.11.238:3001/send';
    
    $dados_3001 = [
        'to' => $numero_destino,
        'message' => $mensagem_3001
    ];
    
    echo "   📤 Enviando para: {$url_3001}\n";
    echo "   📱 Número: {$numero_destino}\n";
    echo "   💬 Mensagem: {$mensagem_3001}\n";
    
    // Fazer requisição HTTP
    $ch_3001 = curl_init();
    curl_setopt($ch_3001, CURLOPT_URL, $url_3001);
    curl_setopt($ch_3001, CURLOPT_POST, true);
    curl_setopt($ch_3001, CURLOPT_POSTFIELDS, json_encode($dados_3001));
    curl_setopt($ch_3001, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch_3001, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_3001, CURLOPT_TIMEOUT, 30);
    
    $response_3001 = curl_exec($ch_3001);
    $http_code_3001 = curl_getinfo($ch_3001, CURLINFO_HTTP_CODE);
    $error_3001 = curl_error($ch_3001);
    curl_close($ch_3001);
    
    if ($error_3001) {
        echo "   ❌ Erro cURL: {$error_3001}\n";
    } else {
        echo "   📊 HTTP Code: {$http_code_3001}\n";
        echo "   📄 Resposta: {$response_3001}\n";
        
        if ($http_code_3001 == 200) {
            echo "   ✅ Mensagem enviada com sucesso via canal 3001\n";
            
            // Salvar no banco
            $sql_salvar_3001 = "INSERT INTO mensagens_comunicacao 
                                (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                                VALUES (37, ?, ?, 'texto', NOW(), 'enviado', 'enviado')";
            
            $stmt_salvar_3001 = $mysqli->prepare($sql_salvar_3001);
            $stmt_salvar_3001->bind_param('ss', $numero_destino, $mensagem_3001);
            $stmt_salvar_3001->execute();
            echo "   💾 Mensagem salva no banco de dados\n";
        } else {
            echo "   ❌ Falha ao enviar mensagem via canal 3001\n";
        }
    }
    echo "\n";
    
    // 3. TESTAR ENDPOINT ALTERNATIVO /send/text
    echo "3. TESTANDO ENDPOINT /send/text:\n";
    echo "   =============================\n";
    
    // Testar canal 3000 com /send/text
    $url_text_3000 = 'http://212.85.11.238:3000/send/text';
    $dados_text_3000 = [
        'sessionName' => 'default',
        'number' => $numero_destino,
        'message' => "🧪 TESTE /send/text CANAL 3000 - {$timestamp}"
    ];
    
    echo "   📤 Testando /send/text canal 3000: {$url_text_3000}\n";
    
    $ch_text_3000 = curl_init();
    curl_setopt($ch_text_3000, CURLOPT_URL, $url_text_3000);
    curl_setopt($ch_text_3000, CURLOPT_POST, true);
    curl_setopt($ch_text_3000, CURLOPT_POSTFIELDS, json_encode($dados_text_3000));
    curl_setopt($ch_text_3000, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch_text_3000, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_text_3000, CURLOPT_TIMEOUT, 10);
    
    $response_text_3000 = curl_exec($ch_text_3000);
    $http_text_3000 = curl_getinfo($ch_text_3000, CURLINFO_HTTP_CODE);
    curl_close($ch_text_3000);
    
    echo "   - HTTP Code: {$http_text_3000}\n";
    echo "   - Resposta: {$response_text_3000}\n";
    
    // Testar canal 3001 com /send/text
    $url_text_3001 = 'http://212.85.11.238:3001/send/text';
    $dados_text_3001 = [
        'sessionName' => 'comercial',
        'number' => $numero_destino,
        'message' => "🧪 TESTE /send/text CANAL 3001 - {$timestamp}"
    ];
    
    echo "   📤 Testando /send/text canal 3001: {$url_text_3001}\n";
    
    $ch_text_3001 = curl_init();
    curl_setopt($ch_text_3001, CURLOPT_URL, $url_text_3001);
    curl_setopt($ch_text_3001, CURLOPT_POST, true);
    curl_setopt($ch_text_3001, CURLOPT_POSTFIELDS, json_encode($dados_text_3001));
    curl_setopt($ch_text_3001, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch_text_3001, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_text_3001, CURLOPT_TIMEOUT, 10);
    
    $response_text_3001 = curl_exec($ch_text_3001);
    $http_text_3001 = curl_getinfo($ch_text_3001, CURLINFO_HTTP_CODE);
    curl_close($ch_text_3001);
    
    echo "   - HTTP Code: {$http_text_3001}\n";
    echo "   - Resposta: {$response_text_3001}\n";
    echo "\n";
    
    // 4. VERIFICAR STATUS DOS CANAIS
    echo "4. VERIFICANDO STATUS DOS CANAIS:\n";
    echo "   =============================\n";
    
    // Verificar canal 3000
    $url_status_3000 = 'http://212.85.11.238:3000/status';
    $ch_status_3000 = curl_init();
    curl_setopt($ch_status_3000, CURLOPT_URL, $url_status_3000);
    curl_setopt($ch_status_3000, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_status_3000, CURLOPT_TIMEOUT, 10);
    
    $status_3000 = curl_exec($ch_status_3000);
    $http_status_3000 = curl_getinfo($ch_status_3000, CURLINFO_HTTP_CODE);
    curl_close($ch_status_3000);
    
    echo "   📱 Canal 3000:\n";
    echo "   - HTTP Code: {$http_status_3000}\n";
    echo "   - Status: {$status_3000}\n";
    
    // Verificar canal 3001
    $url_status_3001 = 'http://212.85.11.238:3001/status';
    $ch_status_3001 = curl_init();
    curl_setopt($ch_status_3001, CURLOPT_URL, $url_status_3001);
    curl_setopt($ch_status_3001, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_status_3001, CURLOPT_TIMEOUT, 10);
    
    $status_3001 = curl_exec($ch_status_3001);
    $http_status_3001 = curl_getinfo($ch_status_3001, CURLINFO_HTTP_CODE);
    curl_close($ch_status_3001);
    
    echo "   📱 Canal 3001:\n";
    echo "   - HTTP Code: {$http_status_3001}\n";
    echo "   - Status: {$status_3001}\n";
    echo "\n";
    
    // 5. INSTRUÇÕES PARA VERIFICAÇÃO
    echo "5. INSTRUÇÕES PARA VERIFICAÇÃO:\n";
    echo "   ===========================\n";
    echo "   📱 Verifique se as mensagens chegaram no WhatsApp do número 554796164699\n";
    echo "   📱 Se não chegaram, verifique:\n";
    echo "      - Se o número está correto\n";
    echo "      - Se os canais estão conectados\n";
    echo "      - Se há bloqueios ou restrições\n";
    echo "   📊 Para verificar mensagens no banco: php teste_mensagens_canais_3000_3001.php\n";
    
    echo "\n=== FIM DO TESTE ===\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 