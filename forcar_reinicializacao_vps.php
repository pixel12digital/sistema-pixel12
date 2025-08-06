<?php
/**
 * 🚨 FORÇAR REINICIALIZAÇÃO DOS SERVIÇOS WHATSAPP NA VPS
 * 
 * Este script força a reinicialização completa dos serviços
 */

echo "🚨 FORÇANDO REINICIALIZAÇÃO DOS SERVIÇOS WHATSAPP\n";
echo "=================================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$vps_user = 'root';

echo "🎯 PROBLEMAS IDENTIFICADOS:\n";
echo "==========================\n";
echo "1. ❌ VPS 3000: ready: false - precisa reinicializar\n";
echo "2. ✅ VPS 3001: ready: true - funcionando\n";
echo "3. ❌ QR Codes não estão sendo gerados\n";
echo "4. ❌ Endpoint /session/start não existe\n\n";

echo "🔧 APLICANDO REINICIALIZAÇÃO FORÇADA:\n";
echo "=====================================\n\n";

// 1. Verificar status atual
echo "1️⃣ Verificando status atual...\n";
$ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "   📊 Status atual VPS 3000:\n";
    echo "      - Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "      - Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    echo "      - Port: " . ($data['port'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Erro ao verificar status (HTTP: $http_code)\n";
}

// 2. Verificar VPS 3001
echo "\n2️⃣ Verificando VPS 3001...\n";
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "   📊 Status atual VPS 3001:\n";
    echo "      - Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "      - Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    echo "      - Port: " . ($data['port'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Erro ao verificar status (HTTP: $http_code)\n";
}

echo "\n🔧 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "=================================\n";
echo "Execute estes comandos em sequência no servidor:\n\n";

echo "1. Conectar via SSH:\n";
echo "   ssh $vps_user@$vps_ip\n\n";

echo "2. Navegar para o diretório:\n";
echo "   cd /var/whatsapp-api\n\n";

echo "3. Parar todos os serviços:\n";
echo "   pm2 stop all\n";
echo "   pm2 delete all\n\n";

echo "4. Limpar processos que possam estar usando as portas:\n";
echo "   sudo fuser -k 3000/tcp\n";
echo "   sudo fuser -k 3001/tcp\n\n";

echo "5. Reiniciar os serviços:\n";
echo "   PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n";
echo "   PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001\n\n";

echo "6. Verificar status:\n";
echo "   pm2 status\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n\n";

echo "7. Salvar configuração:\n";
echo "   pm2 save\n\n";

echo "8. Testar endpoints:\n";
echo "   curl http://127.0.0.1:3000/status\n";
echo "   curl http://127.0.0.1:3001/status\n\n";

echo "🎯 ALTERNATIVA - SCRIPT AUTOMATIZADO:\n";
echo "====================================\n";
echo "Se você quiser automatizar, crie este script na VPS:\n\n";

$script_content = '#!/bin/bash
echo "🚨 REINICIALIZANDO SERVIÇOS WHATSAPP..."
cd /var/whatsapp-api

echo "1. Parando serviços..."
pm2 stop all
pm2 delete all

echo "2. Limpando portas..."
sudo fuser -k 3000/tcp 2>/dev/null || true
sudo fuser -k 3001/tcp 2>/dev/null || true

echo "3. Reiniciando serviços..."
PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000
PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001

echo "4. Aguardando inicialização..."
sleep 5

echo "5. Verificando status..."
pm2 status

echo "6. Salvando configuração..."
pm2 save

echo "7. Testando endpoints..."
curl -s http://127.0.0.1:3000/status | jq .
curl -s http://127.0.0.1:3001/status | jq .

echo "✅ REINICIALIZAÇÃO CONCLUÍDA!"
';

echo $script_content;

echo "\n🎯 INSTRUÇÕES FINAIS:\n";
echo "=====================\n";
echo "1. Execute os comandos acima na VPS\n";
echo "2. Aguarde alguns minutos para a inicialização\n";
echo "3. Verifique se os QR Codes aparecem\n";
echo "4. Teste a conexão no painel\n\n";

echo "🔧 SE AINDA HOUVER PROBLEMAS:\n";
echo "=============================\n";
echo "1. Verificar logs detalhados:\n";
echo "   pm2 logs whatsapp-3000 --lines 100\n";
echo "   pm2 logs whatsapp-3001 --lines 100\n\n";

echo "2. Verificar se há erros no código:\n";
echo "   cat /var/whatsapp-api/whatsapp-api-server.js | grep -n 'error\\|Error'\n\n";

echo "3. Verificar se as dependências estão instaladas:\n";
echo "   cd /var/whatsapp-api\n";
echo "   npm list\n\n";

echo "4. Reinstalar dependências se necessário:\n";
echo "   npm install\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos na VPS para resolver o problema!\n";
?> 