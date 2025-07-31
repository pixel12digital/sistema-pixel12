<?php
/**
 * FORÇAR ATUALIZAÇÃO DO STATUS DOS CANAIS
 * 
 * Este script força a atualização do status dos canais
 * diretamente no banco de dados
 */

echo "🔧 FORÇAR ATUALIZAÇÃO DO STATUS DOS CANAIS\n";
echo "==========================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

// 1. Verificar status atual
echo "📊 STATUS ATUAL NO BANCO:\n";
$canais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id");
while ($canal = $canais->fetch_assoc()) {
    $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
    echo "  $status_icon ID {$canal['id']} - {$canal['nome_exibicao']} (Porta {$canal['porta']}) - {$canal['status']}\n";
}

// 2. Verificar VPS e atualizar status
echo "\n🔍 VERIFICANDO VPS E ATUALIZANDO STATUS:\n";
$vps_ip = '212.85.11.238';

$canais_update = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id");
while ($canal = $canais_update->fetch_assoc()) {
    $porta = $canal['porta'];
    $canal_id = $canal['id'];
    
    echo "\n📱 Verificando {$canal['nome_exibicao']} (Porta $porta):\n";
    
    // Verificar VPS
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:$porta/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        $isConnected = false;
        
        if ($data) {
            // Verificar se está conectado
            if (isset($data['ready']) && $data['ready'] === true) {
                $isConnected = true;
            }
            
            if (isset($data['status']) && in_array($data['status'], ['connected', 'already_connected', 'authenticated', 'ready'])) {
                $isConnected = true;
            }
            
            if (isset($data['clients_status']['default']['status']) && 
                in_array($data['clients_status']['default']['status'], ['connected', 'already_connected', 'authenticated', 'ready'])) {
                $isConnected = true;
            }
        }
        
        $novo_status = $isConnected ? 'conectado' : 'pendente';
        $status_atual = $canal['status'];
        
        echo "  ✅ VPS respondendo (HTTP $http_code)\n";
        echo "  📱 WhatsApp conectado: " . ($isConnected ? 'SIM' : 'NÃO') . "\n";
        echo "  📊 Status atual: $status_atual\n";
        echo "  📊 Status detectado: $novo_status\n";
        
        // Atualizar se necessário
        if ($novo_status !== $status_atual) {
            $update = $mysqli->query("UPDATE canais_comunicacao SET status = '$novo_status' WHERE id = $canal_id");
            if ($update) {
                echo "  ✅ Status atualizado no banco!\n";
            } else {
                echo "  ❌ Erro ao atualizar: " . $mysqli->error . "\n";
            }
        } else {
            echo "  ✅ Status já está correto\n";
        }
        
    } else {
        echo "  ❌ VPS não respondendo (HTTP $http_code)\n";
        // Forçar status como pendente
        $update = $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente' WHERE id = $canal_id");
        if ($update) {
            echo "  ✅ Status definido como pendente\n";
        }
    }
}

// 3. Verificar status final
echo "\n📊 STATUS FINAL NO BANCO:\n";
$canais_final = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id");
while ($canal = $canais_final->fetch_assoc()) {
    $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
    echo "  $status_icon ID {$canal['id']} - {$canal['nome_exibicao']} (Porta {$canal['porta']}) - {$canal['status']}\n";
}

// 4. Testar API de status
echo "\n🔍 TESTANDO API DE STATUS:\n";
$status_url = "https://app.pixel12digital.com.br/painel/api/status_canais.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $status_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $status_url\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ API respondendo\n";
    $data = json_decode($response, true);
    if ($data && isset($data['canais'])) {
        echo "  📋 Canais na API:\n";
        foreach ($data['canais'] as $canal) {
            $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
            echo "    $status_icon {$canal['nome']} (Porta {$canal['porta']}) - {$canal['status']}\n";
        }
    } else {
        echo "  ⚠️ Estrutura antiga da API (ainda não atualizada)\n";
        echo "  📄 Resposta: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "  ❌ API não respondendo\n";
}

echo "\n🎯 RESULTADO:\n";
echo "✅ Status dos canais atualizado no banco!\n";
echo "📋 Agora você precisa:\n";
echo "1. Fazer git pull na Hostinger\n";
echo "2. Testar o chat do painel\n";
echo "3. Verificar se os canais aparecem como conectados\n";

echo "\n🌐 LINKS PARA TESTE:\n";
echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "• Status API: https://app.pixel12digital.com.br/painel/api/status_canais.php\n";
echo "• VPS Status: http://212.85.11.238:3000/status e http://212.85.11.238:3001/status\n";
?> 