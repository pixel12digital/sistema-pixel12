<?php
/**
 * CORREÇÃO SIMPLES DO WHATSAPP
 * Script básico para testar conectividade
 */

echo "🔧 CORREÇÃO SIMPLES DO WHATSAPP\n";
echo "==============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

echo "1. 🔍 TESTANDO CONECTIVIDADE:\n";

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    echo "   📡 Testando porta $porta...\n";
    
    // Usar file_get_contents como alternativa ao curl
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'WhatsApp-Correction/1.0'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        echo "   ✅ Porta $porta: Respondendo\n";
        
        // Tentar decodificar JSON
        $data = json_decode($response, true);
        if ($data) {
            $ready = isset($data['ready']) ? ($data['ready'] ? 'true' : 'false') : 'N/A';
            echo "   📊 Ready: $ready\n";
        }
    } else {
        echo "   ❌ Porta $porta: Não respondendo\n";
    }
    echo "\n";
}

echo "2. 🔄 TENTANDO REINICIAR SERVIÇOS:\n";

// Tentar executar comando SSH
$ssh_command = "ssh -o ConnectTimeout=10 root@212.85.11.238 'pm2 restart whatsapp-multi-session'";
echo "   Executando: $ssh_command\n";

$output = shell_exec($ssh_command . ' 2>&1');
if ($output) {
    echo "   📋 Saída:\n";
    echo "   " . str_replace("\n", "\n   ", $output) . "\n";
} else {
    echo "   ⚠️  Não foi possível executar o comando SSH\n";
}

echo "3. ⏳ AGUARDANDO 10 SEGUNDOS...\n";
sleep(10);

echo "4. 🔍 TESTANDO NOVAMENTE:\n";

foreach ($portas as $porta) {
    $url = "http://{$vps_ip}:{$porta}/status";
    echo "   📡 Testando porta $porta...\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'WhatsApp-Correction/1.0'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        echo "   ✅ Porta $porta: Funcionando!\n";
        
        $data = json_decode($response, true);
        if ($data && isset($data['ready']) && $data['ready']) {
            echo "   🎉 Serviço pronto!\n";
        } else {
            echo "   ⚠️  Serviço respondendo mas não está pronto\n";
        }
    } else {
        echo "   ❌ Porta $porta: Ainda com problema\n";
    }
    echo "\n";
}

echo "5. 📋 INSTRUÇÕES:\n";
echo "   • Acesse o painel de comunicação\n";
echo "   • Se os canais ainda mostram 'Verificando...', aguarde 2-3 minutos\n";
echo "   • Para conectar novos canais, use o botão 'Conectar'\n\n";

echo "✅ CORREÇÃO CONCLUÍDA!\n";
?> 