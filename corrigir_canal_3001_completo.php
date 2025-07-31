<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORREÇÃO COMPLETA - CANAL 3001 COMERCIAL\n";
echo "===========================================\n\n";

echo "📋 OBJETIVO:\n";
echo "   ✅ Configurar servidor WhatsApp na porta 3001 da VPS\n";
echo "   ✅ Configurar identificador do canal 3001\n";
echo "   ✅ Testar recebimento de mensagens\n\n";

// 1. Verificar se o servidor 3001 está funcionando
echo "🔍 VERIFICANDO SERVIDOR 3001:\n";
$vps_ip = '212.85.11.238';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Servidor 3001 está funcionando!\n";
    $data = json_decode($response, true);
    if ($data && isset($data['clients_status']['default']['number'])) {
        $numero_servidor = $data['clients_status']['default']['number'];
        echo "   Número conectado: $numero_servidor\n";
        
        // Configurar identificador automaticamente
        echo "\n🔧 CONFIGURANDO IDENTIFICADOR DO CANAL 3001:\n";
        $identificador = $numero_servidor . '@c.us';
        
        $update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$identificador', status = 'conectado', data_conexao = NOW() WHERE porta = 3001");
        if ($update) {
            echo "✅ Identificador configurado: $identificador\n";
            echo "✅ Status atualizado para 'conectado'\n";
        } else {
            echo "❌ Erro ao configurar identificador: " . $mysqli->error . "\n";
        }
    } else {
        echo "⚠️ Servidor funcionando mas não tem número conectado\n";
        echo "   Conecte o WhatsApp via QR Code primeiro\n";
    }
} else {
    echo "❌ Servidor 3001 não está funcionando!\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Precisamos configurar o servidor na VPS.\n\n";
    
    echo "🔧 CONFIGURANDO SERVIDOR NA VPS:\n";
    echo "   Execute os seguintes comandos na VPS:\n\n";
    
    echo "   ssh root@212.85.11.238\n";
    echo "   cd /var\n";
    echo "   mkdir -p whatsapp-api-3001\n";
    echo "   cp -r whatsapp-api/* whatsapp-api-3001/\n";
    echo "   cd whatsapp-api-3001\n";
    echo "   sed -i 's/const PORT = 3000/const PORT = 3001/' whatsapp-api-server.js\n";
    echo "   pm2 start whatsapp-api-server.js --name whatsapp-3001\n";
    echo "   pm2 save\n\n";
    
    echo "   Depois conecte o WhatsApp via QR Code e execute este script novamente.\n";
    exit(1);
}

// 2. Verificar se o endpoint /send está funcionando
echo "\n🔍 VERIFICANDO ENDPOINT /SEND:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3001/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'to' => '4796164699@c.us',
    'message' => 'Teste endpoint /send - ' . date('H:i:s')
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Endpoint /send funcionando!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . ($data['message'] ?? 'Enviado com sucesso') . "\n";
    }
} else {
    echo "❌ Endpoint /send não está funcionando!\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n";
    echo "   O servidor 3001 não tem o endpoint /send implementado.\n";
}

// 3. Configurar webhook para o canal 3001
echo "\n🔧 CONFIGURANDO WEBHOOK PARA CANAL 3001:\n";
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
    echo "✅ Webhook configurado para canal 3001!\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Resposta: " . ($data['message'] ?? 'Configurado com sucesso') . "\n";
    }
} else {
    echo "❌ Erro ao configurar webhook para canal 3001\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n";
}

// 4. Verificar configuração final do canal
echo "\n📊 CONFIGURAÇÃO FINAL DO CANAL 3001:\n";
$canal_3001 = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001")->fetch_assoc();

if ($canal_3001) {
    $status_icon = $canal_3001['status'] === 'conectado' ? '🟢' : '🔴';
    echo "   {$status_icon} {$canal_3001['nome_exibicao']} (ID: {$canal_3001['id']})\n";
    echo "      Porta: {$canal_3001['porta']} | Status: {$canal_3001['status']}\n";
    echo "      Identificador: " . ($canal_3001['identificador'] ?: 'Não definido') . "\n";
    echo "      Data Conexão: " . ($canal_3001['data_conexao'] ?: 'Não conectado') . "\n";
}

// 5. Testar recebimento de mensagem simulada
echo "\n🧪 TESTANDO RECEBIMENTO DE MENSAGEM:\n";
$numero_teste = '4796164699@c.us';
$mensagem_teste = 'Teste recebimento canal 3001 - ' . date('H:i:s');

// Simular webhook recebido
$webhook_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => $numero_teste,
        'text' => $mensagem_teste,
        'type' => 'text',
        'timestamp' => time()
    ]
];

// Fazer requisição para o webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook processou mensagem de teste!\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n";
} else {
    echo "❌ Erro ao processar mensagem de teste\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Resposta: $response\n";
}

// 6. Verificar se a mensagem foi salva
echo "\n📊 VERIFICANDO MENSAGEM SALVA:\n";
$mensagem_salva = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE mensagem LIKE '%Teste recebimento canal 3001%' ORDER BY id DESC LIMIT 1")->fetch_assoc();

if ($mensagem_salva) {
    echo "✅ Mensagem de teste encontrada no banco!\n";
    echo "   ID: {$mensagem_salva['id']}\n";
    echo "   Canal ID: {$mensagem_salva['canal_id']}\n";
    echo "   Mensagem: {$mensagem_salva['mensagem']}\n";
    echo "   Data: {$mensagem_salva['data_hora']}\n";
    
    if ($mensagem_salva['canal_id'] == 37) {
        echo "   ✅ Mensagem associada ao canal correto (3001)\n";
    } else {
        echo "   ⚠️ Mensagem associada ao canal {$mensagem_salva['canal_id']} (esperado: 37)\n";
    }
} else {
    echo "❌ Mensagem de teste não encontrada no banco\n";
    echo "   Verifique se o webhook está funcionando corretamente\n";
}

// 7. Resumo final
echo "\n📋 RESUMO FINAL:\n";
echo "================\n\n";

$canal_3001_final = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001")->fetch_assoc();
$mensagens_3001_final = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE canal_id = 37")->fetch_assoc();

if ($canal_3001_final && $canal_3001_final['status'] === 'conectado' && !empty($canal_3001_final['identificador'])) {
    echo "✅ CANAL 3001 CONFIGURADO COM SUCESSO!\n\n";
    echo "🎯 STATUS:\n";
    echo "   - Servidor: Funcionando\n";
    echo "   - Identificador: {$canal_3001_final['identificador']}\n";
    echo "   - Status: {$canal_3001_final['status']}\n";
    echo "   - Mensagens: {$mensagens_3001_final['total']}\n\n";
    
    echo "🎯 PRÓXIMOS PASSOS:\n";
    echo "   1. Envie uma mensagem para o número {$canal_3001_final['identificador']}\n";
    echo "   2. Verifique se a mensagem aparece no chat do sistema\n";
    echo "   3. Se não aparecer, verifique os logs do webhook\n\n";
} else {
    echo "⚠️ PROBLEMAS PERSISTEM:\n\n";
    
    if (!$canal_3001_final) {
        echo "   ❌ Canal 3001 não encontrado no banco\n";
    }
    
    if ($canal_3001_final && $canal_3001_final['status'] !== 'conectado') {
        echo "   ❌ Canal 3001 não está conectado\n";
    }
    
    if ($canal_3001_final && empty($canal_3001_final['identificador'])) {
        echo "   ❌ Canal 3001 não tem identificador configurado\n";
    }
    
    echo "\n🔧 AÇÕES NECESSÁRIAS:\n";
    echo "   1. Conectar WhatsApp na porta 3001 via QR Code\n";
    echo "   2. Executar este script novamente\n";
    echo "   3. Verificar logs do servidor 3001\n";
}

echo "\n🎯 CORREÇÃO CONCLUÍDA!\n";
echo "Para mais detalhes, consulte os logs do sistema.\n";
?> 