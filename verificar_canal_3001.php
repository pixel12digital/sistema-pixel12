<?php
/**
 * Verificar se o canal 3001 está enviando mensagens do número 554797309525
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "=== VERIFICAÇÃO DO CANAL 3001 ===\n\n";

try {
    // 1. Verificar configuração do canal 3001 no banco
    echo "1. CONFIGURAÇÃO DO CANAL 3001 NO BANCO:\n";
    $sql = "SELECT * FROM canais_comunicacao WHERE porta = 3001";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $canal = $result->fetch_assoc();
        echo "   ID: {$canal['id']}\n";
        echo "   Nome: {$canal['nome_exibicao']}\n";
        echo "   Identificador: {$canal['identificador']}\n";
        echo "   Status: {$canal['status']}\n";
        echo "   Porta: {$canal['porta']}\n";
        
        // Verificar se o identificador corresponde ao número esperado
        $numero_esperado = '554797309525';
        if (strpos($canal['identificador'], $numero_esperado) !== false) {
            echo "   ✅ Identificador contém o número esperado: $numero_esperado\n";
        } else {
            echo "   ❌ Identificador NÃO contém o número esperado: $numero_esperado\n";
            echo "   Identificador atual: {$canal['identificador']}\n";
        }
    } else {
        echo "   ❌ Canal 3001 não encontrado no banco\n";
    }
    echo "\n";
    
    // 2. Verificar mensagens enviadas pelo canal 3001
    echo "2. MENSAGENS ENVIADAS PELO CANAL 3001:\n";
    $sql = "SELECT m.*, c.nome_exibicao as canal_nome 
            FROM mensagens_comunicacao m 
            JOIN canais_comunicacao c ON m.canal_id = c.id 
            WHERE c.porta = 3001 AND m.direcao = 'enviado' 
            ORDER BY m.data_hora DESC 
            LIMIT 10";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "   Últimas 10 mensagens enviadas:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "   - ID: {$msg['id']} | Data: {$msg['data_hora']} | Canal: {$msg['canal_nome']}\n";
            echo "     Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "   Nenhuma mensagem encontrada para o canal 3001\n";
    }
    echo "\n";
    
    // 3. Verificar logs de debug do chat_enviar.php
    echo "3. LOGS DE DEBUG DO CHAT_ENVIAR.PHP:\n";
    $log_file = 'painel/debug_chat_enviar.log';
    if (file_exists($log_file)) {
        $logs = file($log_file);
        $logs_recentes = array_slice($logs, -20); // Últimas 20 linhas
        
        echo "   Últimas 20 linhas do log:\n";
        foreach ($logs_recentes as $log) {
            if (strpos($log, '3001') !== false || strpos($log, 'Comercial') !== false) {
                echo "   " . trim($log) . "\n";
            }
        }
    } else {
        echo "   Arquivo de log não encontrado: $log_file\n";
    }
    echo "\n";
    
    // 4. Testar envio de mensagem via API do canal 3001
    echo "4. TESTE DE ENVIO VIA API DO CANAL 3001:\n";
    
    $api_url = "http://212.85.11.238:3001/status";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ API do canal 3001 está respondendo\n";
        $status_data = json_decode($response, true);
        if ($status_data) {
            echo "   Status: " . json_encode($status_data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "   ❌ API do canal 3001 não está respondendo (HTTP: $http_code)\n";
    }
    echo "\n";
    
    // 5. Verificar se o número 554797309525 está sendo usado corretamente
    echo "5. VERIFICAÇÃO DO NÚMERO 554797309525:\n";
    
    // Buscar mensagens que mencionam este número
    $sql = "SELECT * FROM mensagens_comunicacao 
            WHERE mensagem LIKE '%554797309525%' 
            ORDER BY data_hora DESC 
            LIMIT 5";
    
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "   Mensagens que mencionam o número 554797309525:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "   - ID: {$msg['id']} | Data: {$msg['data_hora']} | Direção: {$msg['direcao']}\n";
            echo "     Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
        }
    } else {
        echo "   Nenhuma mensagem encontrada mencionando o número 554797309525\n";
    }
    echo "\n";
    
    // 6. Resumo da verificação
    echo "6. RESUMO DA VERIFICAÇÃO:\n";
    
    // Verificar se o canal 3001 existe e está configurado corretamente
    $sql = "SELECT * FROM canais_comunicacao WHERE porta = 3001";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $canal = $result->fetch_assoc();
        $numero_esperado = '554797309525';
        
        if (strpos($canal['identificador'], $numero_esperado) !== false) {
            echo "   ✅ Canal 3001 está configurado corretamente com o número $numero_esperado\n";
        } else {
            echo "   ❌ Canal 3001 NÃO está configurado com o número $numero_esperado\n";
            echo "   Número atual: {$canal['identificador']}\n";
        }
        
        if ($canal['status'] === 'conectado') {
            echo "   ✅ Canal 3001 está conectado\n";
        } else {
            echo "   ❌ Canal 3001 NÃO está conectado (Status: {$canal['status']})\n";
        }
    } else {
        echo "   ❌ Canal 3001 não encontrado no banco de dados\n";
    }
    
    echo "\n=== FIM DA VERIFICAÇÃO ===\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante a verificação: " . $e->getMessage() . "\n";
}
?> 