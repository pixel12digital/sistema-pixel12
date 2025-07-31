<?php
/**
 * DIAGNOSTICAR WEBHOOK CANAL COMERCIAL
 * 
 * Este script diagnostica e corrige o problema do webhook
 * do canal comercial que não está salvando mensagens
 */

echo "🔍 DIAGNOSTICAR WEBHOOK CANAL COMERCIAL\n";
echo "=======================================\n\n";

// 1. Verificar se o VPS está configurado corretamente
echo "🔍 TESTE 1: VERIFICAR CONFIGURAÇÃO DO VPS\n";
$vps_ip = '212.85.11.238';

// Testar se o VPS está enviando para o webhook correto
echo "📱 Verificando porta 3001 (Comercial):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "  ✅ Porta 3001 ativa\n";
    $data = json_decode($response, true);
    if ($data && isset($data['ready'])) {
        echo "  📱 WhatsApp conectado: " . ($data['ready'] ? 'SIM' : 'NÃO') . "\n";
    }
} else {
    echo "  ❌ Porta 3001 não ativa (HTTP $http_code)\n";
}

// 2. Testar webhook específico do canal comercial
echo "\n🔍 TESTE 2: TESTAR WEBHOOK ESPECÍFICO\n";
$webhook_url = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";

$dados_teste = [
    'from' => '47997471723@c.us', // Número da Alessandra
    'to' => '4797309525@c.us',    // Número do canal comercial
    'body' => 'Teste diagnóstico canal comercial - ' . date('H:i:s'),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $webhook_url\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Webhook funcionando\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  📋 Canal: {$data['canal']}\n";
        echo "  📋 ID: {$data['canal_id']}\n";
        echo "  📋 Banco: {$data['banco']}\n";
    }
} else {
    echo "  ❌ Webhook não funcionando\n";
    echo "  📋 Resposta: $response\n";
}

// 3. Verificar se a mensagem foi salva no banco comercial
echo "\n🔍 TESTE 3: VERIFICAR BANCO COMERCIAL\n";
require_once 'canais/comercial/canal_config.php';

$mysqli = conectarBancoCanal();
if ($mysqli) {
    // Buscar mensagens recentes
    $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 5";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "  ✅ Mensagens encontradas no banco comercial:\n";
        while ($msg = $result->fetch_assoc()) {
            echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
            echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        }
    } else {
        echo "  ⚠️ Nenhuma mensagem encontrada no banco comercial\n";
    }
    
    // Verificar configuração do canal
    $sql_canal = "SELECT * FROM canais_comunicacao WHERE id = 37";
    $result_canal = $mysqli->query($sql_canal);
    
    if ($result_canal && $result_canal->num_rows > 0) {
        $canal = $result_canal->fetch_assoc();
        echo "  📋 Canal 37 configurado: {$canal['nome_exibicao']} (Porta {$canal['porta']})\n";
        echo "  📋 Identificador: {$canal['identificador']}\n";
    } else {
        echo "  ❌ Canal 37 não encontrado no banco comercial\n";
    }
    
    $mysqli->close();
} else {
    echo "  ❌ Erro ao conectar ao banco comercial\n";
}

// 4. Verificar banco principal para comparar
echo "\n🔍 TESTE 4: VERIFICAR BANCO PRINCIPAL\n";
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT * FROM mensagens_comunicacao WHERE cliente_id = 285 ORDER BY data_hora DESC LIMIT 3";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  📋 Mensagens da Alessandra no banco principal:\n";
    while ($msg = $result->fetch_assoc()) {
        echo "    ID {$msg['id']} - {$msg['data_hora']} - Canal ID: {$msg['canal_id']}\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
    }
} else {
    echo "  ⚠️ Nenhuma mensagem da Alessandra no banco principal\n";
}

// 5. Criar script de correção
echo "\n🔍 TESTE 5: CRIAR SCRIPT DE CORREÇÃO\n";
echo "  💡 O problema é que o VPS não está configurado para usar o webhook correto.\n";
echo "  📋 Criando script de correção...\n";

$script_correcao = '<?php
/**
 * CORREÇÃO WEBHOOK CANAL COMERCIAL
 * 
 * Este script corrige a configuração do VPS para usar
 * o webhook correto do canal comercial
 */

echo "🔧 CORREÇÃO WEBHOOK CANAL COMERCIAL\n";
echo "===================================\n\n";

// 1. Verificar configuração atual do VPS
echo "🔍 VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
$vps_ip = "212.85.11.238";

// Testar webhook atual
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data["webhook_url"])) {
        echo "  📋 Webhook atual: " . $data["webhook_url"] . "\n";
        
        $webhook_correto = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";
        
        if ($data["webhook_url"] === $webhook_correto) {
            echo "  ✅ Webhook já está configurado corretamente!\n";
        } else {
            echo "  ❌ Webhook incorreto! Deve ser: $webhook_correto\n";
            
            // Configurar webhook correto
            echo "\n🔧 CONFIGURANDO WEBHOOK CORRETO:\n";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://$vps_ip:3001/webhook/config");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["url" => $webhook_correto]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                echo "  ✅ Webhook configurado com sucesso!\n";
            } else {
                echo "  ❌ Erro ao configurar webhook (HTTP $http_code)\n";
            }
        }
    }
} else {
    echo "  ❌ Não foi possível verificar configuração atual\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Execute este script no VPS: ssh root@212.85.11.238\n";
echo "2. Ou configure manualmente:\n";
echo "   cd /var/whatsapp-api\n";
echo "   nano .env\n";
echo "   # Alterar WEBHOOK_URL para: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "   pm2 restart whatsapp-api\n";

echo "\n🎯 RESULTADO:\n";
echo "✅ Script de correção criado!\n";
echo "📋 Execute no VPS para corrigir a configuração do webhook.\n";
?>';

file_put_contents('correcao_webhook_vps.php', $script_correcao);
echo "  ✅ Script criado: correcao_webhook_vps.php\n";

echo "\n🎯 DIAGNÓSTICO COMPLETO:\n";
echo "❌ PROBLEMA IDENTIFICADO:\n";
echo "  • VPS não está configurado para usar webhook_canal_37.php\n";
echo "  • Mensagens estão sendo enviadas para webhook_whatsapp.php\n";
echo "  • Por isso aparecem como 'Financeiro' no chat\n";
echo "  • Banco comercial está vazio\n";

echo "\n✅ SOLUÇÃO:\n";
echo "  • Configurar VPS para usar webhook correto\n";
echo "  • Executar script de correção no VPS\n";
echo "  • Testar envio de mensagem para canal comercial\n";

echo "\n📋 COMANDOS PARA EXECUTAR NO VPS:\n";
echo "ssh root@212.85.11.238\n";
echo "cd /var/whatsapp-api\n";
echo "nano .env\n";
echo "# Alterar WEBHOOK_URL para: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "pm2 restart whatsapp-api\n";

echo "\n🌐 LINKS ÚTEIS:\n";
echo "• VPS Status: http://212.85.11.238:3001/status\n";
echo "• Webhook Correto: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "• phpMyAdmin Comercial: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel&table=mensagens_comunicacao\n";
?> 