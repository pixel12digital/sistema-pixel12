<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "📱 CONECTANDO CANAL FINANCEIRO\n";
echo "==============================\n\n";

// Verificar status atual
echo "📋 STATUS ATUAL:\n";
echo "================\n";

// Verificar porta 3000 (Financeiro)
echo "🔍 PORTA 3000 (FINANCEIRO):\n";
$vps_url_3000 = "http://212.85.11.238:3000";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_3000\n";
echo "Resposta: $response_3000\n\n";

$sessions_3000 = json_decode($response_3000, true);

// Verificar se a sessão default está conectada
$default_connected = false;
if (($sessions_3000['total'] ?? 0) > 0) {
    foreach ($sessions_3000['sessions'] ?? [] as $session) {
        if ($session['name'] === 'default') {
            $default_connected = ($session['status']['status'] === 'connected');
            echo "Sessão 'default': " . $session['status']['status'] . "\n";
            break;
        }
    }
}

if ($default_connected) {
    echo "✅ Canal Financeiro já está conectado!\n";
} else {
    echo "🔄 Canal Financeiro precisa ser conectado\n";
    
    // Verificar se há QR code disponível
    echo "\n📱 VERIFICANDO QR CODE:\n";
    echo "=======================\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/qr?session=default");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $qr_response = curl_exec($ch);
    $qr_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "QR HTTP Code: $qr_http_code\n";
    echo "QR Resposta: $qr_response\n\n";
    
    $qr_data = json_decode($qr_response, true);
    
    if ($qr_http_code == 200 && isset($qr_data['qr'])) {
        echo "✅ QR Code disponível!\n";
        echo "📱 Escaneie o QR Code com WhatsApp 554797146908\n";
        echo "🔗 URL do QR: http://212.85.11.238:3000/qr?session=default\n\n";
        
        // Atualizar status no banco para 'qr_ready'
        $sql = "UPDATE canais_comunicacao SET status = 'qr_ready' WHERE id = 36";
        if ($mysqli->query($sql)) {
            echo "✅ Status atualizado para 'qr_ready' no banco\n";
        }
    } else {
        echo "❌ QR Code não disponível\n";
        echo "💡 Tente acessar o painel e clicar em 'Conectar'\n";
    }
}

// Verificar configuração final
echo "\n📋 CONFIGURAÇÃO FINAL:\n";
echo "======================\n";
$sql_final = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result_final = $mysqli->query($sql_final);

if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

echo "\n💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
if ($default_connected) {
    echo "✅ Canal Financeiro já está conectado!\n";
    echo "✅ Ambos os canais estão funcionando independentemente\n";
} else {
    echo "1. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
    echo "2. Clique em 'Conectar' no canal Financeiro\n";
    echo "3. Escaneie o QR Code com WhatsApp 554797146908\n";
    echo "4. Aguarde a conexão ser estabelecida\n";
}
?> 