<?php
/**
 * Script para iniciar o serviço WhatsApp no VPS
 * Tenta diferentes métodos de inicialização
 */

echo "=== INICIANDO SERVIÇO VPS WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// Primeiro, vamos testar se o VPS está respondendo
echo "1. 🔍 Testando conectividade com o VPS...\n";

$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $porta => $vps_url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ VPS $porta está respondendo (HTTP $http_code)\n";
        $data = json_decode($response, true);
        if ($data) {
            echo "   📊 Ready: " . ($data['ready'] ? 'true' : 'false') . "\n";
            if (isset($data['clients_status'])) {
                echo "   📱 Sessões: " . implode(', ', array_keys($data['clients_status'])) . "\n";
            }
        }
    } else {
        echo "   ❌ VPS $porta não está respondendo (HTTP $http_code)\n";
    }
}

echo "\n2. 🚀 INSTRUÇÕES PARA INICIAR O SERVIÇO:\n";
echo "Execute os seguintes comandos no VPS (212.85.11.238):\n\n";

echo "🔍 PASSO 1: Verificar se o diretório existe:\n";
echo "   ls -la /var/whatsapp-api/\n";
echo "   ls -la /opt/whatsapp-api/\n";
echo "   ls -la /home/*/whatsapp-api/\n\n";

echo "🔍 PASSO 2: Se encontrar o diretório, navegar até ele:\n";
echo "   cd /var/whatsapp-api\n";
echo "   # ou\n";
echo "   cd /opt/whatsapp-api\n";
echo "   # ou\n";
echo "   cd /home/usuario/whatsapp-api\n\n";

echo "🔍 PASSO 3: Verificar arquivos disponíveis:\n";
echo "   ls -la\n";
echo "   cat package.json\n";
echo "   cat index.js | head -20\n\n";

echo "🔍 PASSO 4: Tentar iniciar o serviço:\n";
echo "   # Opção A: npm start\n";
echo "   npm start\n\n";
echo "   # Opção B: node direto\n";
echo "   node index.js\n";
echo "   # ou\n";
echo "   node app.js\n";
echo "   # ou\n";
echo "   node server.js\n\n";

echo "🔍 PASSO 5: Se funcionar, manter rodando em background:\n";
echo "   # Usar nohup\n";
echo "   nohup node index.js > whatsapp.log 2>&1 &\n\n";
echo "   # Ou usar screen\n";
echo "   screen -S whatsapp\n";
echo "   node index.js\n";
echo "   # Pressionar Ctrl+A, depois D para sair\n\n";
echo "   # Ou usar tmux\n";
echo "   tmux new-session -d -s whatsapp 'node index.js'\n\n";

echo "🔍 PASSO 6: Configurar PM2 (recomendado):\n";
echo "   # Instalar PM2 se não estiver instalado\n";
echo "   npm install -g pm2\n\n";
echo "   # Iniciar com PM2\n";
echo "   pm2 start index.js --name whatsapp-multi-session\n";
echo "   pm2 save\n";
echo "   pm2 startup\n\n";

echo "🔍 PASSO 7: Criar serviço systemd:\n";
echo "   sudo nano /etc/systemd/system/whatsapp-multi-session.service\n\n";
echo "   # Conteúdo do arquivo:\n";
echo "   [Unit]\n";
echo "   Description=WhatsApp Multi-Session API\n";
echo "   After=network.target\n\n";
echo "   [Service]\n";
echo "   Type=simple\n";
echo "   User=root\n";
echo "   WorkingDirectory=/var/whatsapp-api\n";
echo "   ExecStart=/usr/bin/node index.js\n";
echo "   Restart=always\n";
echo "   RestartSec=10\n";
echo "   Environment=NODE_ENV=production\n\n";
echo "   [Install]\n";
echo "   WantedBy=multi-user.target\n\n";
echo "   # Depois:\n";
echo "   systemctl daemon-reload\n";
echo "   systemctl enable whatsapp-multi-session\n";
echo "   systemctl start whatsapp-multi-session\n\n";

echo "3. 🧪 TESTE APÓS INICIAR:\n";
echo "Após iniciar o serviço, execute:\n";
echo "   curl http://localhost:3000/status\n";
echo "   curl http://localhost:3001/status\n";
echo "   curl http://212.85.11.238:3000/status\n";
echo "   curl http://212.85.11.238:3001/status\n\n";

echo "4. ✅ RESULTADO ESPERADO:\n";
echo "Se o serviço estiver funcionando, você deve ver:\n";
echo "   {\n";
echo "     \"success\": true,\n";
echo "     \"ready\": true,\n";
echo "     \"clients_status\": {}\n";
echo "   }\n\n";

echo "5. 🔧 SE NÃO ENCONTRAR O DIRETÓRIO:\n";
echo "O serviço pode estar em outro local. Execute:\n";
echo "   find / -name '*whatsapp*' -type d 2>/dev/null\n";
echo "   find / -name 'index.js' -path '*/whatsapp*' 2>/dev/null\n";
echo "   find / -name 'package.json' -path '*/whatsapp*' 2>/dev/null\n\n";

echo "6. 📋 COMANDOS RÁPIDOS PARA TESTAR:\n";
echo "   # Verificar se há algum processo rodando\n";
echo "   ps aux | grep -i whatsapp\n";
echo "   ps aux | grep -i node\n\n";
echo "   # Verificar portas em uso\n";
echo "   netstat -tlnp | grep :300\n";
echo "   lsof -i :3000\n";
echo "   lsof -i :3001\n\n";

echo "7. 🚨 SE NADA FUNCIONAR:\n";
echo "Pode ser necessário reinstalar o serviço. Execute:\n";
echo "   # Verificar se há backup ou repositório\n";
echo "   ls -la /var/backups/\n";
echo "   ls -la /opt/\n";
echo "   ls -la /home/\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Execute os comandos de diagnóstico\n";
echo "2. Identifique onde está o serviço\n";
echo "3. Inicie o serviço usando um dos métodos acima\n";
echo "4. Teste a conectividade\n";
echo "5. Volte ao painel e tente conectar o WhatsApp\n\n";

echo "=== FIM DAS INSTRUÇÕES ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 