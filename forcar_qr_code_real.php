<?php
/**
 * 🚨 FORÇAR GERAÇÃO DE QR CODES REAIS
 * 
 * Este script força a reinicialização dos serviços e geração de QR Codes reais
 */

echo "🚨 FORÇANDO GERAÇÃO DE QR CODES REAIS\n";
echo "=====================================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$vps_user = 'root';

echo "🎯 PROBLEMA IDENTIFICADO:\n";
echo "========================\n";
echo "1. ❌ QR Codes não estão sendo gerados\n";
echo "2. ❌ Serviços não estão inicializando corretamente\n";
echo "3. ❌ Sessões não estão sendo criadas\n";
echo "4. ❌ Endpoints não estão respondendo corretamente\n\n";

echo "🔧 SOLUÇÃO DEFINITIVA:\n";
echo "=====================\n\n";

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
    echo "   📊 VPS 3000:\n";
    echo "      - Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "      - Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    echo "      - Port: " . ($data['port'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Erro ao verificar VPS 3000 (HTTP: $http_code)\n";
}

$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $data = json_decode($response, true);
    echo "   📊 VPS 3001:\n";
    echo "      - Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "      - Ready: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    echo "      - Port: " . ($data['port'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Erro ao verificar VPS 3001 (HTTP: $http_code)\n";
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

echo "4. Limpar processos e portas:\n";
echo "   sudo fuser -k 3000/tcp 2>/dev/null || true\n";
echo "   sudo fuser -k 3001/tcp 2>/dev/null || true\n";
echo "   sleep 2\n\n";

echo "5. Verificar se as portas estão livres:\n";
echo "   netstat -tlnp | grep :3000\n";
echo "   netstat -tlnp | grep :3001\n\n";

echo "6. Reiniciar serviços com configuração correta:\n";
echo "   PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000\n";
echo "   sleep 3\n";
echo "   PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001\n\n";

echo "7. Aguardar inicialização:\n";
echo "   sleep 10\n\n";

echo "8. Verificar status:\n";
echo "   pm2 status\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n\n";

echo "9. Testar endpoints:\n";
echo "   curl -s http://127.0.0.1:3000/status | jq .\n";
echo "   curl -s http://127.0.0.1:3001/status | jq .\n\n";

echo "10. Testar QR Codes:\n";
echo "    curl -s \"http://127.0.0.1:3000/qr?session=default\" | jq .\n";
echo "    curl -s \"http://127.0.0.1:3001/qr?session=comercial\" | jq .\n\n";

echo "11. Salvar configuração:\n";
echo "    pm2 save\n\n";

echo "🎯 SCRIPT AUTOMATIZADO PARA VPS:\n";
echo "===============================\n";
echo "Crie este script na VPS e execute:\n\n";

$script_vps = '#!/bin/bash
echo "🚨 FORÇANDO GERAÇÃO DE QR CODES REAIS..."
cd /var/whatsapp-api

echo "1. Parando serviços..."
pm2 stop all
pm2 delete all

echo "2. Limpando portas..."
sudo fuser -k 3000/tcp 2>/dev/null || true
sudo fuser -k 3001/tcp 2>/dev/null || true
sleep 2

echo "3. Verificando portas..."
netstat -tlnp | grep :3000 || echo "Porta 3000 livre"
netstat -tlnp | grep :3001 || echo "Porta 3001 livre"

echo "4. Reiniciando serviços..."
PORT=3000 pm2 start whatsapp-api-server.js --name whatsapp-3000
sleep 3
PORT=3001 pm2 start whatsapp-api-server.js --name whatsapp-3001

echo "5. Aguardando inicialização..."
sleep 10

echo "6. Verificando status..."
pm2 status

echo "7. Verificando logs..."
pm2 logs whatsapp-3000 --lines 10
pm2 logs whatsapp-3001 --lines 10

echo "8. Testando endpoints..."
echo "VPS 3000:"
curl -s http://127.0.0.1:3000/status | jq . || echo "Erro ao testar VPS 3000"

echo "VPS 3001:"
curl -s http://127.0.0.1:3001/status | jq . || echo "Erro ao testar VPS 3001"

echo "9. Testando QR Codes..."
echo "QR Code VPS 3000:"
curl -s "http://127.0.0.1:3000/qr?session=default" | jq . || echo "Erro ao testar QR 3000"

echo "QR Code VPS 3001:"
curl -s "http://127.0.0.1:3001/qr?session=comercial" | jq . || echo "Erro ao testar QR 3001"

echo "10. Salvando configuração..."
pm2 save

echo "✅ REINICIALIZAÇÃO CONCLUÍDA!"
echo "Agora teste a conexão no painel!"
';

echo $script_vps;

echo "\n🎯 INSTRUÇÕES FINAIS:\n";
echo "=====================\n";
echo "1. Execute os comandos acima na VPS\n";
echo "2. Aguarde 2-3 minutos para a inicialização completa\n";
echo "3. Verifique se os QR Codes aparecem\n";
echo "4. Teste a conexão no painel\n\n";

echo "🔧 SE AINDA HOUVER PROBLEMAS:\n";
echo "============================\n";
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

echo "5. Verificar se o arquivo está correto:\n";
echo "   cat /var/whatsapp-api/whatsapp-api-server.js | head -50\n\n";

echo "🎯 SCRIPT FINALIZADO!\n";
echo "=====================\n";
echo "Execute os comandos na VPS para resolver o problema!\n";
?> 