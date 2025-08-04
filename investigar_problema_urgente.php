<?php
/**
 * Investigação urgente - Problema no canal 3000
 */

echo "=== INVESTIGAÇÃO URGENTE - CANAL 3000 ===\n\n";

require_once 'config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    echo "✅ Conectado ao banco\n\n";
    
    // 1. Verificar mensagens dos últimos 10 minutos
    echo "1. MENSAGENS DOS ÚLTIMOS 10 MINUTOS:\n";
    $result = $mysqli->query("
        SELECT m.*, c.nome, c.celular
        FROM mensagens_comunicacao m 
        LEFT JOIN clientes c ON m.cliente_id = c.id 
        WHERE m.data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ORDER BY m.data_hora DESC 
        LIMIT 10
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "📱 Encontradas " . $result->num_rows . " mensagens:\n\n";
        
        while ($row = $result->fetch_assoc()) {
            $tempo = $row['data_hora'];
            $canal = $row['canal_id'];
            $direcao = $row['direcao'];
            $numero = $row['numero_whatsapp'];
            $mensagem = substr($row['mensagem'], 0, 50);
            $cliente = $row['nome'] ?? 'Sem nome';
            
            $icone = ($direcao === 'recebido') ? '📥 RECEBIDA' : '📤 ENVIADA';
            $canal_nome = ($canal == 36) ? 'Canal Ana' : 'Canal Humano';
            
            echo "$icone [$tempo] $canal_nome\n";
            echo "   👤 $cliente ($numero)\n";
            echo "   💬 \"$mensagem...\"\n\n";
        }
    } else {
        echo "❌ NENHUMA mensagem nos últimos 10 minutos!\n";
        echo "🚨 PROBLEMA CONFIRMADO!\n\n";
    }
    
    // 2. Verificar se sua mensagem chegou no banco
    echo "2. PROCURANDO SUA MENSAGEM (554796164699):\n";
    $result = $mysqli->query("
        SELECT * FROM mensagens_comunicacao 
        WHERE numero_whatsapp LIKE '%4796164699%' 
        AND data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ORDER BY data_hora DESC 
        LIMIT 3
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "✅ Mensagem encontrada no banco:\n\n";
        
        while ($row = $result->fetch_assoc()) {
            $tempo = $row['data_hora'];
            $canal = $row['canal_id'];
            $direcao = $row['direcao'];
            $mensagem = $row['mensagem'];
            
            echo "📥 [$tempo] Canal $canal | $direcao\n";
            echo "   💬 \"$mensagem\"\n\n";
        }
    } else {
        echo "❌ SUA MENSAGEM NÃO CHEGOU NO BANCO!\n";
        echo "🚨 WEBHOOK NÃO ESTÁ SENDO CHAMADO!\n\n";
    }
    
    // 3. Testar webhook manualmente AGORA
    echo "3. TESTANDO WEBHOOK MANUALMENTE:\n";
    $webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';
    $test_data = [
        'from' => '554796164699@c.us',
        'to' => '554797146908@c.us',
        'body' => 'TESTE URGENTE INVESTIGAÇÃO - ' . date('H:i:s'),
        'message' => 'TESTE URGENTE INVESTIGAÇÃO - ' . date('H:i:s')
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "Resposta: " . substr($response, 0, 200) . "...\n\n";
    
    if ($http_code == 200) {
        echo "✅ Webhook processou internamente\n";
        
        // Verificar se chegou no banco
        sleep(1);
        $check = $mysqli->query("
            SELECT * FROM mensagens_comunicacao 
            WHERE mensagem LIKE '%TESTE URGENTE INVESTIGAÇÃO%' 
            ORDER BY id DESC LIMIT 2
        ");
        
        if ($check && $check->num_rows > 0) {
            echo "✅ TESTE foi salvo no banco\n";
            while ($row = $check->fetch_assoc()) {
                echo "   ID: {$row['id']} | {$row['direcao']} | \"{$row['mensagem']}\"\n";
            }
        } else {
            echo "❌ TESTE NÃO foi salvo no banco\n";
        }
    } else {
        echo "❌ Webhook falhou: HTTP $http_code\n";
    }
    
    // 4. Verificar configuração do VPS
    echo "\n4. VERIFICANDO CONFIGURAÇÃO DO VPS:\n";
    $vps_urls = [
        'Canal 3000 (Ana)' => 'http://212.85.11.238:3000',
        'Canal 3001 (Humano)' => 'http://212.85.11.238:3001'
    ];
    
    foreach ($vps_urls as $nome => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '/webhook/config');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $config = json_decode($response, true);
            echo "$nome: " . ($config['webhook_url'] ?? 'Não configurado') . "\n";
        } else {
            echo "$nome: ❌ Erro HTTP $http_code\n";
        }
    }
    
    // 5. Verificar status das sessões
    echo "\n5. STATUS DAS SESSÕES WHATSAPP:\n";
    foreach ($vps_urls as $nome => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '/sessions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            $sessions = json_decode($response, true);
            if ($sessions && count($sessions) > 0) {
                foreach ($sessions as $session) {
                    $status = $session['status'] ?? 'unknown';
                    $name = $session['name'] ?? 'unnamed';
                    $icon = ($status === 'connected') ? '🟢' : '🔴';
                    echo "$nome - $name: $icon $status\n";
                }
            } else {
                echo "$nome: ❌ Nenhuma sessão encontrada\n";
            }
        } else {
            echo "$nome: ❌ Erro ao verificar sessões\n";
        }
    }
    
    $mysqli->close();
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📊 DIAGNÓSTICO URGENTE:\n\n";
    
    // Verificar se o último teste manual funcionou
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $check_test = $mysqli->query("
        SELECT COUNT(*) as count FROM mensagens_comunicacao 
        WHERE mensagem LIKE '%TESTE URGENTE INVESTIGAÇÃO%'
    ");
    $test_worked = $check_test->fetch_assoc()['count'] > 0;
    $mysqli->close();
    
    if ($test_worked) {
        echo "✅ WEBHOOK INTERNO FUNCIONANDO\n";
        echo "❌ VPS NÃO ESTÁ ENVIANDO WEBHOOKS\n\n";
        echo "🎯 PROBLEMA: API do VPS parou de enviar webhooks!\n";
        echo "💡 SOLUÇÕES:\n";
        echo "1. Reconfigurar webhook no VPS\n";
        echo "2. Reiniciar sessões WhatsApp\n";
        echo "3. Ativar sistema de polling como backup\n";
    } else {
        echo "❌ WEBHOOK INTERNO COM PROBLEMA\n";
        echo "🎯 PROBLEMA: Erro interno no sistema!\n";
        echo "💡 SOLUÇÕES:\n";
        echo "1. Verificar logs de erro do servidor\n";
        echo "2. Verificar se algum arquivo foi alterado\n";
        echo "3. Revisar últimas mudanças\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?> 