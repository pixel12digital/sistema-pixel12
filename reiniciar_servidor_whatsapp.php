<?php
require_once "config.php";

echo "🔄 REINICIANDO SERVIDOR WHATSAPP NA VPS\n";
echo "=======================================\n\n";

$vps_ip = "212.85.11.238";
$porta = "3000";

echo "📡 VPS: $vps_ip:$porta\n\n";

// 1. Verificar status atual
echo "🔍 STATUS ATUAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:$porta/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Servidor ativo\n";
    echo "   📊 Status: " . ($data['ready'] ? 'CONECTADO' : 'DESCONECTADO') . "\n";
    echo "   📱 Client Status: " . ($data['clients_status']['default']['status'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Servidor não responde (HTTP $http_code)\n";
    exit;
}

echo "\n";

// 2. Tentar reiniciar via PM2 (se disponível)
echo "🔄 TENTANDO REINICIAR VIA PM2:\n";

$pm2_commands = [
    'pm2 restart whatsapp-api',
    'pm2 restart all',
    'pm2 restart 0',
    'pm2 restart 1'
];

foreach ($pm2_commands as $command) {
    echo "   🔍 Executando: $command\n";
    
    // Usar SSH para executar comando na VPS
    $ssh_command = "ssh root@$vps_ip '$command'";
    $output = shell_exec($ssh_command . ' 2>&1');
    
    if ($output) {
        echo "   📊 Output: " . substr($output, 0, 100) . "...\n";
    } else {
        echo "   ⚠️ Sem output\n";
    }
    
    echo "\n";
}

// 3. Aguardar e verificar status
echo "⏳ Aguardando 10 segundos para reinicialização...\n";
sleep(10);

echo "\n🔍 VERIFICANDO STATUS APÓS REINÍCIO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:$porta/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Servidor ativo\n";
    echo "   📊 Status: " . ($data['ready'] ? 'CONECTADO' : 'DESCONECTADO') . "\n";
    echo "   📱 Client Status: " . ($data['clients_status']['default']['status'] ?? 'N/A') . "\n";
    
    if ($data['ready']) {
        echo "\n🎉 SERVIDOR REINICIADO COM SUCESSO!\n";
        echo "   Agora tente enviar uma mensagem novamente.\n";
    } else {
        echo "\n⚠️ SERVIDOR REINICIADO MAS WHATSAPP DESCONECTADO\n";
        echo "   Você precisará reconectar via QR Code.\n";
    }
} else {
    echo "   ❌ Servidor não responde após reinício (HTTP $http_code)\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "   1. Se o servidor foi reiniciado, teste o envio de mensagens\n";
echo "   2. Se o WhatsApp desconectou, reconecte via QR Code\n";
echo "   3. Se o problema persistir, pode ser necessário:\n";
echo "      - Reiniciar a VPS completamente\n";
echo "      - Atualizar a API do WhatsApp\n";
echo "      - Verificar logs do servidor\n";

echo "\n✅ PROCESSO CONCLUÍDO!\n";
?> 