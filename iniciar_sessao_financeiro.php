<?php
require_once 'config.php';

echo "🚀 INICIANDO SESSÃO FINANCEIRO\n";
echo "==============================\n\n";

// Iniciar sessão default na porta 3000
echo "📱 INICIANDO SESSÃO 'DEFAULT' NA PORTA 3000:\n";
echo "============================================\n";

$vps_url_3000 = "http://212.85.11.238:3000";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/session/start/default");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Resposta: $response\n\n";

$result = json_decode($response, true);

if ($http_code == 200 && ($result['success'] ?? false)) {
    echo "✅ Sessão 'default' iniciada com sucesso!\n";
    
    // Aguardar um pouco para a sessão inicializar
    echo "⏳ Aguardando inicialização da sessão...\n";
    sleep(3);
    
    // Verificar se a sessão está pronta
    echo "\n📋 VERIFICANDO STATUS DA SESSÃO:\n";
    echo "===============================\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/sessions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $sessions_response = curl_exec($ch);
    curl_close($ch);
    
    echo "Resposta: $sessions_response\n\n";
    
    $sessions = json_decode($sessions_response, true);
    
    if (($sessions['total'] ?? 0) > 0) {
        foreach ($sessions['sessions'] ?? [] as $session) {
            if ($session['name'] === 'default') {
                echo "Sessão 'default': " . $session['status']['status'] . "\n";
                
                if ($session['status']['status'] === 'qr_ready') {
                    echo "✅ QR Code está pronto!\n";
                    echo "📱 Escaneie o QR Code com WhatsApp 554797146908\n";
                    echo "🔗 URL do QR: http://212.85.11.238:3000/qr?session=default\n\n";
                    
                    // Atualizar status no banco
                    require_once 'painel/db.php';
                    $sql = "UPDATE canais_comunicacao SET status = 'qr_ready' WHERE id = 36";
                    if ($mysqli->query($sql)) {
                        echo "✅ Status atualizado para 'qr_ready' no banco\n";
                    }
                } elseif ($session['status']['status'] === 'connected') {
                    echo "✅ Canal já está conectado!\n";
                    
                    // Atualizar status no banco
                    require_once 'painel/db.php';
                    $sql = "UPDATE canais_comunicacao SET status = 'conectado' WHERE id = 36";
                    if ($mysqli->query($sql)) {
                        echo "✅ Status atualizado para 'conectado' no banco\n";
                    }
                }
                break;
            }
        }
    }
    
} else {
    echo "❌ Erro ao iniciar sessão\n";
    echo "Erro: " . ($result['message'] ?? 'Erro desconhecido') . "\n";
}

echo "\n💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Se o QR Code apareceu, escaneie com WhatsApp 554797146908\n";
echo "2. Se não apareceu, acesse o painel e clique em 'Conectar'\n";
echo "3. Aguarde a conexão ser estabelecida\n";
?> 