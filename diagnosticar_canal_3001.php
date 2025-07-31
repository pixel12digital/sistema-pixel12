<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 DIAGNÓSTICO COMPLETO - CANAL 3001 COMERCIAL\n";
echo "===============================================\n\n";

echo "📋 OBJETIVO:\n";
echo "   ✅ Identificar por que mensagens do canal 3001 não estão sendo salvas\n";
echo "   ✅ Verificar configuração do webhook\n";
echo "   ✅ Corrigir problemas identificados\n\n";

// 1. Verificar status atual dos canais
echo "📊 STATUS ATUAL DOS CANAIS:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

// 2. Verificar se o servidor na porta 3001 está funcionando
echo "🔍 VERIFICANDO SERVIDOR PORTA 3001:\n";
$vps_ip = '212.85.11.238';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Servidor na porta 3001 está funcionando!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
        if (isset($data['clients_status']['default']['number'])) {
            echo "   Número: " . $data['clients_status']['default']['number'] . "\n";
        }
    }
} else {
    echo "❌ Servidor na porta 3001 não está respondendo!\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
}

// 3. Verificar se o canal 3001 tem identificador configurado
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO DO CANAL 3001:\n";
$canal_3001 = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001")->fetch_assoc();

if ($canal_3001) {
    echo "📱 Canal 3001 encontrado:\n";
    echo "   ID: {$canal_3001['id']}\n";
    echo "   Nome: {$canal_3001['nome_exibicao']}\n";
    echo "   Status: {$canal_3001['status']}\n";
    echo "   Identificador: " . ($canal_3001['identificador'] ?: 'NÃO CONFIGURADO') . "\n";
    
    if (empty($canal_3001['identificador'])) {
        echo "\n⚠️ PROBLEMA IDENTIFICADO: Canal 3001 não tem identificador configurado!\n";
        echo "   Isso impede que o webhook identifique qual canal usar.\n";
    }
} else {
    echo "❌ Canal 3001 não encontrado no banco de dados!\n";
}

// 4. Verificar mensagens no banco principal
echo "\n📊 VERIFICANDO MENSAGENS NO BANCO PRINCIPAL:\n";
$mensagens_3001 = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE canal_id = 37")->fetch_assoc();
echo "   Mensagens do canal 3001 (ID 37): {$mensagens_3001['total']}\n";

$mensagens_3000 = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE canal_id = 36")->fetch_assoc();
echo "   Mensagens do canal 3000 (ID 36): {$mensagens_3000['total']}\n";

// 5. Verificar se existe webhook específico para canal 3001
echo "\n🔍 VERIFICANDO WEBHOOK ESPECÍFICO:\n";
$webhook_file = 'api/webhook_canal_37.php';
if (file_exists($webhook_file)) {
    echo "✅ Webhook específico encontrado: $webhook_file\n";
} else {
    echo "❌ Webhook específico não encontrado: $webhook_file\n";
    echo "   O sistema está usando o webhook principal para todos os canais.\n";
}

// 6. Verificar configuração do webhook principal
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO DO WEBHOOK PRINCIPAL:\n";
$webhook_principal = 'api/webhook_whatsapp.php';
if (file_exists($webhook_principal)) {
    echo "✅ Webhook principal existe: $webhook_principal\n";
    
    // Verificar se o webhook principal identifica canais por porta
    $webhook_content = file_get_contents($webhook_principal);
    if (strpos($webhook_content, 'porta') !== false || strpos($webhook_content, '3001') !== false) {
        echo "✅ Webhook principal parece ter lógica para identificar canais\n";
    } else {
        echo "⚠️ Webhook principal pode não ter lógica para identificar canais por porta\n";
    }
} else {
    echo "❌ Webhook principal não encontrado!\n";
}

// 7. Verificar se o canal 3001 está configurado corretamente no banco
echo "\n🔧 CORREÇÕES NECESSÁRIAS:\n";

if ($canal_3001 && empty($canal_3001['identificador'])) {
    echo "1. 🔧 Configurar identificador do canal 3001...\n";
    
    // Tentar obter o número do servidor 3001
    if ($http_code === 200 && $data && isset($data['clients_status']['default']['number'])) {
        $numero_servidor = $data['clients_status']['default']['number'];
        echo "   Número obtido do servidor: $numero_servidor\n";
        
        $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero_servidor@c.us' WHERE porta = 3001");
        if ($update) {
            echo "   ✅ Identificador configurado: $numero_servidor@c.us\n";
        } else {
            echo "   ❌ Erro ao configurar identificador: " . $mysqli->error . "\n";
        }
    } else {
        echo "   ⚠️ Não foi possível obter o número do servidor 3001\n";
        echo "   Você precisa conectar o WhatsApp na porta 3001 primeiro.\n";
    }
}

// 8. Verificar se o webhook está configurado corretamente na VPS
echo "\n🔍 VERIFICANDO CONFIGURAÇÃO DO WEBHOOK NA VPS:\n";
$webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook configurado na porta 3001!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . ($data['message'] ?? 'Configurado com sucesso') . "\n";
    }
} else {
    echo "❌ Erro ao configurar webhook na porta 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n";
}

// 9. Testar envio de mensagem para o canal 3001
echo "\n🧪 TESTANDO ENVIO PARA CANAL 3001:\n";
$numero_teste = '4796164699@c.us';
$mensagem_teste = 'Teste canal 3001 - ' . date('H:i:s');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => $numero_teste,
    'message' => $mensagem_teste
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Envio para canal 3001 funcionando!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . ($data['message'] ?? 'Enviado com sucesso') . "\n";
    }
} else {
    echo "❌ Erro ao enviar para canal 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n";
}

// 10. Resumo e recomendações
echo "\n📋 RESUMO E RECOMENDAÇÕES:\n";
echo "==========================\n\n";

if ($http_code === 200 && $canal_3001 && !empty($canal_3001['identificador'])) {
    echo "✅ CANAL 3001 ESTÁ CONFIGURADO CORRETAMENTE!\n\n";
    echo "🎯 PRÓXIMOS PASSOS:\n";
    echo "   1. Envie uma mensagem para o número do canal 3001\n";
    echo "   2. Verifique se a mensagem aparece no chat do sistema\n";
    echo "   3. Se não aparecer, verifique os logs do webhook\n\n";
} else {
    echo "⚠️ PROBLEMAS IDENTIFICADOS:\n\n";
    
    if ($http_code !== 200) {
        echo "   ❌ Servidor na porta 3001 não está funcionando\n";
        echo "      - Configure o servidor WhatsApp na porta 3001 da VPS\n";
        echo "      - Conecte o WhatsApp via QR Code\n\n";
    }
    
    if (!$canal_3001) {
        echo "   ❌ Canal 3001 não está configurado no banco\n";
        echo "      - Execute: php configurar_canal_comercial_simples.php\n\n";
    }
    
    if ($canal_3001 && empty($canal_3001['identificador'])) {
        echo "   ❌ Canal 3001 não tem identificador configurado\n";
        echo "      - Conecte o WhatsApp na porta 3001 primeiro\n";
        echo "      - Execute este script novamente para configurar automaticamente\n\n";
    }
    
    echo "🔧 COMANDOS PARA CORRIGIR:\n";
    echo "   1. Configurar servidor na VPS: ssh root@212.85.11.238\n";
    echo "   2. Criar servidor na porta 3001: cd /var && mkdir whatsapp-api-3001\n";
    echo "   3. Copiar arquivos: cp -r whatsapp-api/* whatsapp-api-3001/\n";
    echo "   4. Alterar porta: sed -i 's/3000/3001/' whatsapp-api-3001/whatsapp-api-server.js\n";
    echo "   5. Iniciar servidor: pm2 start whatsapp-api-3001/whatsapp-api-server.js --name whatsapp-3001\n";
    echo "   6. Conectar WhatsApp via QR Code\n";
    echo "   7. Executar este script novamente\n\n";
}

echo "📊 ESTATÍSTICAS FINAIS:\n";
echo "   - Canal 3000 (Financeiro): " . ($mensagens_3000['total'] ?? 0) . " mensagens\n";
echo "   - Canal 3001 (Comercial): " . ($mensagens_3001['total'] ?? 0) . " mensagens\n";
echo "   - Status 3001: " . ($http_code === 200 ? 'Funcionando' : 'Não funcionando') . "\n";

echo "\n🎯 DIAGNÓSTICO CONCLUÍDO!\n";
echo "Para mais detalhes, consulte os logs do sistema.\n";
?> 