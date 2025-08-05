<?php
/**
 * TESTE FINAL DE STATUS - WHATSAPP
 */

echo "🔧 TESTE FINAL DE STATUS - WHATSAPP\n";
echo "===================================\n\n";

$vps_ip = '212.85.11.238';
$portas = [3000, 3001];

// Testar cada porta
foreach ($portas as $porta) {
    echo "📡 Testando porta $porta...\n";
    
    $url = "http://{$vps_ip}:{$porta}/status";
    
    // Usar file_get_contents
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'WhatsApp-Test/1.0'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        echo "✅ Porta $porta: RESPONDENDO\n";
        
        $data = json_decode($response, true);
        if ($data) {
            $ready = isset($data['ready']) ? ($data['ready'] ? 'SIM' : 'NÃO') : 'N/A';
            echo "   📊 Ready: $ready\n";
            
            if (isset($data['status'])) {
                echo "   📊 Status: {$data['status']}\n";
            }
        }
    } else {
        echo "❌ Porta $porta: NÃO RESPONDE\n";
    }
    echo "\n";
}

echo "📋 DIAGNÓSTICO:\n";
echo "===============\n\n";

echo "🔍 PROBLEMA IDENTIFICADO:\n";
echo "   • Os canais WhatsApp estão mostrando 'Verificando...'\n";
echo "   • Isso indica que o VPS não está respondendo corretamente\n\n";

echo "🔧 SOLUÇÃO:\n";
echo "===========\n\n";

echo "1. Acesse o VPS via SSH:\n";
echo "   ssh root@212.85.11.238\n\n";

echo "2. Verifique se o processo está rodando:\n";
echo "   pm2 list\n\n";

echo "3. Se o processo não estiver rodando, inicie-o:\n";
echo "   pm2 start whatsapp-multi-session\n\n";

echo "4. Se estiver rodando mas com problemas, reinicie:\n";
echo "   pm2 restart whatsapp-multi-session\n\n";

echo "5. Verifique os logs:\n";
echo "   pm2 logs whatsapp-multi-session\n\n";

echo "6. Teste localmente no VPS:\n";
echo "   curl http://localhost:3000/status\n";
echo "   curl http://localhost:3001/status\n\n";

echo "7. Verifique recursos do servidor:\n";
echo "   top\n";
echo "   free -h\n";
echo "   df -h\n\n";

echo "📱 APÓS CORRIGIR NO VPS:\n";
echo "=======================\n\n";

echo "1. Aguarde 2-3 minutos\n";
echo "2. Acesse o painel de comunicação\n";
echo "3. Os status devem atualizar automaticamente\n";
echo "4. Se não atualizar, recarregue a página\n\n";

echo "✅ INSTRUÇÕES CONCLUÍDAS!\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 