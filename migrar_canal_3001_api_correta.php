<?php
/**
 * 🔄 MIGRAR CANAL 3001 PARA API CORRETA
 * 
 * Script para migrar o canal 3001 da API atual para whatsapp-api-server.js
 * Baseado nos problemas identificados
 */

echo "🔄 MIGRANDO CANAL 3001 PARA API CORRETA\n";
echo "=======================================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';
$webhook_principal = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// ===== 1. VERIFICAR STATUS ATUAL DO CANAL 3001 =====
echo "1️⃣ VERIFICANDO STATUS ATUAL DO CANAL 3001\n";
echo "------------------------------------------\n";

// Verificar se canal 3001 está funcionando
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "✅ Canal 3001 está funcionando\n";
    echo "📊 Status: " . ($status_3001['status'] ?? 'unknown') . "\n";
    
    // Verificar se é API diferente
    if (isset($status_3001['clients_status'])) {
        echo "👥 Sessões: " . count($status_3001['clients_status']) . "\n";
        foreach ($status_3001['clients_status'] as $sessao => $status) {
            echo "  - $sessao: " . ($status['status'] ?? 'unknown') . "\n";
        }
    } else {
        echo "⚠️ API diferente detectada (não usa whatsapp-api-server.js)\n";
    }
} else {
    echo "❌ Canal 3001 não responde (HTTP $http_code_3001)\n";
    echo "🔧 Necessita reiniciar o serviço\n";
}

echo "\n";

// ===== 2. VERIFICAR CANAL 3000 (REFERÊNCIA) =====
echo "2️⃣ VERIFICANDO CANAL 3000 (REFERÊNCIA)\n";
echo "---------------------------------------\n";

$ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3000 === 200) {
    $status_3000 = json_decode($response_3000, true);
    echo "✅ Canal 3000 está funcionando (API correta)\n";
    echo "📊 Status: " . ($status_3000['status'] ?? 'unknown') . "\n";
    
    if (isset($status_3000['clients_status'])) {
        echo "👥 Sessões: " . count($status_3000['clients_status']) . "\n";
        foreach ($status_3000['clients_status'] as $sessao => $status) {
            echo "  - $sessao: " . ($status['status'] ?? 'unknown') . "\n";
        }
    }
} else {
    echo "❌ Canal 3000 não responde (HTTP $http_code_3000)\n";
}

echo "\n";

// ===== 3. COMPARAR ENDPOINTS DOS CANAIS =====
echo "3️⃣ COMPARANDO ENDPOINTS DOS CANAIS\n";
echo "-----------------------------------\n";

$endpoints_comparacao = [
    '/send/text' => 'Envio de mensagens',
    '/webhook/config' => 'Configuração de webhook',
    '/status' => 'Status do servidor',
    '/qr' => 'QR Code',
    '/webhook/test' => 'Teste de webhook'
];

echo "📊 COMPARAÇÃO DE ENDPOINTS:\n";
echo "Endpoint                    | Canal 3000 | Canal 3001 | Status\n";
echo "----------------------------|------------|------------|--------\n";

foreach ($endpoints_comparacao as $endpoint => $descricao) {
    // Testar canal 3000
    $ch = curl_init("http://$vps_ip:3000$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $response = curl_exec($ch);
    $http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Testar canal 3001
    $ch = curl_init("http://$vps_ip:3001$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $response = curl_exec($ch);
    $http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status_3000 = ($http_code_3000 === 200) ? "✅" : "❌";
    $status_3001 = ($http_code_3001 === 200) ? "✅" : "❌";
    $status_geral = ($http_code_3000 === 200 && $http_code_3001 === 200) ? "✅" : "⚠️";
    
    printf("%-28s | %-10s | %-10s | %s\n", 
           substr($endpoint, 0, 28), 
           $status_3000, 
           $status_3001, 
           $status_geral);
}

echo "\n";

// ===== 4. GERAR COMANDOS DE MIGRAÇÃO =====
echo "4️⃣ GERANDO COMANDOS DE MIGRAÇÃO\n";
echo "--------------------------------\n";

echo "🔧 COMANDOS PARA MIGRAR CANAL 3001:\n\n";

echo "1️⃣ CONECTAR NA VPS:\n";
echo "ssh root@$vps_ip\n\n";

echo "2️⃣ PARAR SERVIÇO ATUAL:\n";
echo "pm2 stop whatsapp-3001\n";
echo "pm2 delete whatsapp-3001\n\n";

echo "3️⃣ COPIAR API CORRETA:\n";
echo "cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server-3001.js\n\n";

echo "4️⃣ MODIFICAR PORTA NO ARQUIVO:\n";
echo "sed -i 's/const PORT = 3000/const PORT = 3001/g' /var/whatsapp-api/whatsapp-api-server-3001.js\n";
echo "sed -i 's/sessionName: \"default\"/sessionName: \"comercial\"/g' /var/whatsapp-api/whatsapp-api-server-3001.js\n\n";

echo "5️⃣ CONFIGURAR PM2:\n";
echo "pm2 start /var/whatsapp-api/whatsapp-api-server-3001.js --name whatsapp-3001\n";
echo "pm2 save\n\n";

echo "6️⃣ VERIFICAR STATUS:\n";
echo "pm2 status\n";
echo "curl http://$vps_ip:3001/status\n\n";

echo "7️⃣ CONFIGURAR WEBHOOK:\n";
echo "curl -X POST http://$vps_ip:3001/webhook/config \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"url\":\"$webhook_principal\"}'\n\n";

echo "8️⃣ TESTAR FUNCIONALIDADES:\n";
echo "curl http://$vps_ip:3001/webhook/config\n";
echo "curl -X POST http://$vps_ip:3001/webhook/test\n";
echo "curl http://$vps_ip:3001/qr\n\n";

// ===== 5. CRIAR SCRIPT DE MIGRAÇÃO AUTOMÁTICA =====
echo "5️⃣ CRIANDO SCRIPT DE MIGRAÇÃO AUTOMÁTICA\n";
echo "----------------------------------------\n";

$script_migracao = "#!/bin/bash
# Script para migrar canal 3001 para API correta
# Executar na VPS: bash migrar_canal_3001.sh

echo \"🔄 MIGRANDO CANAL 3001 PARA API CORRETA\"
echo \"=====================================\"

# 1. Parar serviço atual
echo \"1️⃣ Parando serviço atual...\"
pm2 stop whatsapp-3001
pm2 delete whatsapp-3001

# 2. Verificar se arquivo existe
echo \"2️⃣ Verificando arquivo da API...\"
if [ ! -f \"/var/whatsapp-api/whatsapp-api-server.js\" ]; then
    echo \"❌ Arquivo whatsapp-api-server.js não encontrado\"
    exit 1
fi

# 3. Copiar e modificar arquivo
echo \"3️⃣ Copiando e modificando arquivo...\"
cp /var/whatsapp-api/whatsapp-api-server.js /var/whatsapp-api/whatsapp-api-server-3001.js

# 4. Modificar porta e sessão
echo \"4️⃣ Modificando configurações...\"
sed -i 's/const PORT = 3000/const PORT = 3001/g' /var/whatsapp-api/whatsapp-api-server-3001.js
sed -i 's/sessionName: \"default\"/sessionName: \"comercial\"/g' /var/whatsapp-api/whatsapp-api-server-3001.js

# 5. Iniciar novo serviço
echo \"5️⃣ Iniciando novo serviço...\"
pm2 start /var/whatsapp-api/whatsapp-api-server-3001.js --name whatsapp-3001
pm2 save

# 6. Aguardar inicialização
echo \"6️⃣ Aguardando inicialização...\"
sleep 5

# 7. Verificar status
echo \"7️⃣ Verificando status...\"
pm2 status
curl -s http://$vps_ip:3001/status

# 8. Configurar webhook
echo \"8️⃣ Configurando webhook...\"
curl -X POST http://$vps_ip:3001/webhook/config \\
  -H \"Content-Type: application/json\" \\
  -d '{\"url\":\"$webhook_principal\"}'

echo \"✅ MIGRAÇÃO CONCLUÍDA!\"
echo \"🎉 Canal 3001 migrado para API correta!\"
";

// Salvar script
file_put_contents('migrar_canal_3001.sh', $script_migracao);
echo "✅ Script de migração criado: migrar_canal_3001.sh\n\n";

// ===== 6. VERIFICAR SE MIGRAÇÃO É NECESSÁRIA =====
echo "6️⃣ VERIFICANDO SE MIGRAÇÃO É NECESSÁRIA\n";
echo "----------------------------------------\n";

$migracao_necessaria = false;
$problemas_3001 = [];

// Verificar endpoints críticos do canal 3001
$endpoints_criticos = ['/send/text', '/webhook/config', '/qr'];
foreach ($endpoints_criticos as $endpoint) {
    $ch = curl_init("http://$vps_ip:3001$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        $migracao_necessaria = true;
        $problemas_3001[] = "Endpoint $endpoint não funciona (HTTP $http_code)";
    }
}

if ($migracao_necessaria) {
    echo "⚠️ MIGRAÇÃO NECESSÁRIA!\n";
    echo "Problemas identificados no canal 3001:\n";
    foreach ($problemas_3001 as $problema) {
        echo "  • $problema\n";
    }
    echo "\n✅ Solução: Executar script de migração\n";
} else {
    echo "✅ MIGRAÇÃO NÃO NECESSÁRIA!\n";
    echo "Canal 3001 está funcionando corretamente\n";
}

echo "\n";

// ===== 7. RESUMO FINAL =====
echo "7️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 ANÁLISE DE MIGRAÇÃO CONCLUÍDA!\n\n";

echo "📊 STATUS DOS CANAIS:\n";
echo "• Canal 3000: " . ($http_code_3000 === 200 ? "✅ Funcionando (API correta)" : "❌ Problemas") . "\n";
echo "• Canal 3001: " . ($http_code_3001 === 200 ? "⚠️ Funcionando (API diferente)" : "❌ Não responde") . "\n";

echo "\n📋 ARQUIVOS CRIADOS:\n";
echo "• migrar_canal_3001.sh - Script de migração automática\n";

echo "\n🔧 PRÓXIMOS PASSOS:\n";
if ($migracao_necessaria) {
    echo "1. Executar script de migração na VPS\n";
    echo "2. Verificar se canal 3001 está funcionando\n";
    echo "3. Configurar webhook do canal 3001\n";
    echo "4. Testar funcionalidades completas\n";
} else {
    echo "1. Verificar se migração é realmente necessária\n";
    echo "2. Testar funcionalidades atuais\n";
    echo "3. Monitorar logs se necessário\n";
}

echo "\n📞 COMANDOS ÚTEIS:\n";
echo "• Executar migração: ssh root@$vps_ip 'bash migrar_canal_3001.sh'\n";
echo "• Status: curl http://$vps_ip:3001/status\n";
echo "• Webhook: curl http://$vps_ip:3001/webhook/config\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs whatsapp-3001 --lines 20'\n\n";

echo "✅ ANÁLISE FINALIZADA!\n";
echo "🎉 Script de migração criado com sucesso!\n";
?> 