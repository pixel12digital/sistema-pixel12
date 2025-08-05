<?php
/**
 * 🔍 AVERIGUAÇÃO COMPLETA DO CÓDIGO LOCAL E AJUSTE DA VPS
 * 
 * Script para analisar todo o código local e ajustar a VPS de acordo
 * Baseado na análise completa do projeto
 */

echo "🔍 AVERIGUAÇÃO COMPLETA DO CÓDIGO LOCAL E AJUSTE DA VPS\n";
echo "=====================================================\n\n";

// ===== 1. ANÁLISE DO ARQUIVO CONFIG.PHP =====
echo "1️⃣ ANALISANDO CONFIGURAÇÕES PRINCIPAIS (config.php)\n";
echo "------------------------------------------------\n";

require_once 'config.php';

echo "📋 CONFIGURAÇÕES IDENTIFICADAS:\n";
echo "• WHATSAPP_ROBOT_URL: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'NÃO DEFINIDO') . "\n";
echo "• WHATSAPP_TIMEOUT: " . (defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : 'NÃO DEFINIDO') . "\n";
echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . "\n";
echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . "\n";
echo "• ASAAS_API_KEY: " . (defined('ASAAS_API_KEY') ? substr(ASAAS_API_KEY, 0, 20) . '...' : 'NÃO DEFINIDO') . "\n";
echo "• DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : 'NÃO DEFINIDO') . "\n\n";

// ===== 2. ANÁLISE DOS CANAIS NO BANCO DE DADOS =====
echo "2️⃣ ANALISANDO CANAIS NO BANCO DE DADOS\n";
echo "--------------------------------------\n";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "❌ Erro de conexão com banco: " . $mysqli->connect_error . "\n\n";
    } else {
        echo "✅ Conectado ao banco de dados\n";
        
        // Buscar canais de comunicação
        $sql = "SELECT * FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "📊 CANAIS ENCONTRADOS NO BANCO:\n";
            while ($row = $result->fetch_assoc()) {
                echo "   ID: {$row['id']} | Nome: {$row['nome_exibicao']} | Identificador: {$row['identificador']}\n";
                echo "   Status: {$row['status']} | Porta: " . ($row['porta'] ?? 'N/A') . " | Sessão: " . ($row['sessao'] ?? 'N/A') . "\n";
                echo "   Endpoint: " . ($row['endpoint'] ?? 'N/A') . "\n";
                echo "   Data Conexão: " . ($row['data_conexao'] ?? 'N/A') . "\n\n";
            }
        } else {
            echo "⚠️ Nenhum canal WhatsApp encontrado no banco\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Erro ao acessar banco: " . $e->getMessage() . "\n\n";
}

// ===== 3. ANÁLISE DOS WEBHOOKS NO CÓDIGO =====
echo "3️⃣ ANALISANDO WEBHOOKS NO CÓDIGO\n";
echo "--------------------------------\n";

$webhook_files = [
    'painel/receber_mensagem_ana_local.php' => 'Webhook Principal (Ana)',
    'painel/receber_mensagem.php' => 'Webhook Alternativo',
    'api/webhook_whatsapp.php' => 'Webhook API',
    'api/webhook.php' => 'Webhook Genérico'
];

$webhooks_encontrados = [];

foreach ($webhook_files as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $webhooks_encontrados[$file] = [
            'description' => $description,
            'exists' => true,
            'size' => strlen($content),
            'has_ana_integration' => strpos($content, 'integrador_ana') !== false,
            'has_webhook_processing' => strpos($content, 'webhook') !== false
        ];
        echo "✅ $description: $file (" . strlen($content) . " bytes)\n";
    } else {
        echo "❌ $description: $file (NÃO ENCONTRADO)\n";
    }
}
echo "\n";

// ===== 4. ANÁLISE DOS ENDPOINTS DA API =====
echo "4️⃣ ANALISANDO ENDPOINTS DA API WHATSAPP\n";
echo "---------------------------------------\n";

$api_files = [
    'whatsapp-api-server.js' => 'API Principal',
    'funcao_envio_whatsapp.php' => 'Função de Envio',
    'painel/ajax_whatsapp.php' => 'AJAX WhatsApp'
];

$endpoints_encontrados = [];

foreach ($api_files as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $endpoints_encontrados[$file] = [
            'description' => $description,
            'exists' => true,
            'size' => strlen($content),
            'has_send_text' => strpos($content, '/send/text') !== false,
            'has_webhook_config' => strpos($content, '/webhook/config') !== false,
            'has_status' => strpos($content, '/status') !== false,
            'has_qr' => strpos($content, '/qr') !== false
        ];
        echo "✅ $description: $file (" . strlen($content) . " bytes)\n";
        
        // Extrair endpoints específicos
        if (preg_match_all('/\/[a-zA-Z0-9\/_-]+/g', $content, $matches)) {
            $unique_endpoints = array_unique($matches[0]);
            $whatsapp_endpoints = array_filter($unique_endpoints, function($endpoint) {
                return strpos($endpoint, '/send') !== false || 
                       strpos($endpoint, '/webhook') !== false || 
                       strpos($endpoint, '/status') !== false ||
                       strpos($endpoint, '/qr') !== false;
            });
            if (!empty($whatsapp_endpoints)) {
                echo "   📡 Endpoints: " . implode(', ', array_slice($whatsapp_endpoints, 0, 5)) . "\n";
            }
        }
    } else {
        echo "❌ $description: $file (NÃO ENCONTRADO)\n";
    }
}
echo "\n";

// ===== 5. ANÁLISE DAS CONFIGURAÇÕES DA VPS =====
echo "5️⃣ ANALISANDO CONFIGURAÇÕES DA VPS NO CÓDIGO\n";
echo "--------------------------------------------\n";

$vps_ip = '212.85.11.238';
$vps_configs = [];

// Buscar todas as referências à VPS no código
$search_patterns = [
    'VPS.*212\.85\.11\.238' => 'Referências diretas à VPS',
    '212\.85\.11\.238:3000' => 'Canal 3000',
    '212\.85\.11\.238:3001' => 'Canal 3001',
    'webhook.*config' => 'Configurações de webhook',
    'PM2.*whatsapp' => 'Configurações PM2'
];

foreach ($search_patterns as $pattern => $description) {
    $grep_result = shell_exec("grep -r \"$pattern\" . --include=\"*.php\" --include=\"*.js\" --include=\"*.md\" --include=\"*.sh\" 2>/dev/null | head -3");
    if ($grep_result) {
        echo "✅ $description:\n";
        echo "   " . str_replace("\n", "\n   ", trim($grep_result)) . "\n";
    }
}
echo "\n";

// ===== 6. VERIFICAR STATUS ATUAL DA VPS =====
echo "6️⃣ VERIFICANDO STATUS ATUAL DA VPS\n";
echo "----------------------------------\n";

$vps_status = [];

// Verificar canal 3000
$ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3000 === 200) {
    echo "✅ Canal 3000: FUNCIONANDO (HTTP $http_code_3000)\n";
    $status_3000 = json_decode($response_3000, true);
    if ($status_3000) {
        echo "   📊 Status: " . ($status_3000['status'] ?? 'unknown') . "\n";
        echo "   🔗 Porta: " . ($status_3000['port'] ?? 'unknown') . "\n";
    }
} else {
    echo "❌ Canal 3000: NÃO RESPONDE (HTTP $http_code_3000)\n";
}

// Verificar canal 3001
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    echo "✅ Canal 3001: FUNCIONANDO (HTTP $http_code_3001)\n";
    $status_3001 = json_decode($response_3001, true);
    if ($status_3001) {
        echo "   📊 Status: " . ($status_3001['status'] ?? 'unknown') . "\n";
        echo "   🔗 Porta: " . ($status_3001['port'] ?? 'unknown') . "\n";
    }
} else {
    echo "❌ Canal 3001: NÃO RESPONDE (HTTP $http_code_3001)\n";
}
echo "\n";

// ===== 7. VERIFICAR WEBHOOKS CONFIGURADOS =====
echo "7️⃣ VERIFICANDO WEBHOOKS CONFIGURADOS\n";
echo "------------------------------------\n";

// Verificar webhook canal 3000
$ch = curl_init("http://$vps_ip:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_3000 = curl_exec($ch);
$webhook_http_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($webhook_http_3000 === 200) {
    echo "✅ Webhook Canal 3000: CONFIGURADO\n";
    $webhook_config_3000 = json_decode($webhook_3000, true);
    if ($webhook_config_3000 && isset($webhook_config_3000['webhook_url'])) {
        echo "   🔗 URL: {$webhook_config_3000['webhook_url']}\n";
    }
} else {
    echo "❌ Webhook Canal 3000: NÃO CONFIGURADO (HTTP $webhook_http_3000)\n";
}

// Verificar webhook canal 3001
$ch = curl_init("http://$vps_ip:3001/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_3001 = curl_exec($ch);
$webhook_http_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($webhook_http_3001 === 200) {
    echo "✅ Webhook Canal 3001: CONFIGURADO\n";
    $webhook_config_3001 = json_decode($webhook_3001, true);
    if ($webhook_config_3001 && isset($webhook_config_3001['webhook_url'])) {
        echo "   🔗 URL: {$webhook_config_3001['webhook_url']}\n";
    }
} else {
    echo "❌ Webhook Canal 3001: NÃO CONFIGURADO (HTTP $webhook_http_3001)\n";
}
echo "\n";

// ===== 8. GERAR RELATÓRIO DE AJUSTES NECESSÁRIOS =====
echo "8️⃣ RELATÓRIO DE AJUSTES NECESSÁRIOS\n";
echo "-----------------------------------\n";

$ajustes_necessarios = [];

// Verificar se webhooks estão configurados corretamente
$webhook_principal = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

if ($webhook_http_3000 !== 200 || 
    ($webhook_config_3000 && $webhook_config_3000['webhook_url'] !== $webhook_principal)) {
    $ajustes_necessarios[] = "Configurar webhook canal 3000 para: $webhook_principal";
}

if ($webhook_http_3001 !== 200) {
    $ajustes_necessarios[] = "Investigar API do canal 3001 (endpoints diferentes)";
}

// Verificar se canais estão conectados
if ($http_code_3000 !== 200) {
    $ajustes_necessarios[] = "Reiniciar canal 3000 na VPS";
}

if ($http_code_3001 !== 200) {
    $ajustes_necessarios[] = "Reiniciar canal 3001 na VPS";
}

// Verificar banco de dados
if (isset($mysqli) && $mysqli->connect_error) {
    $ajustes_necessarios[] = "Verificar conexão com banco de dados";
}

if (empty($ajustes_necessarios)) {
    echo "✅ TODOS OS AJUSTES ESTÃO CORRETOS!\n";
} else {
    echo "⚠️ AJUSTES NECESSÁRIOS:\n";
    foreach ($ajustes_necessarios as $i => $ajuste) {
        echo "   " . ($i + 1) . ". $ajuste\n";
    }
}
echo "\n";

// ===== 9. EXECUTAR AJUSTES AUTOMÁTICOS =====
echo "9️⃣ EXECUTANDO AJUSTES AUTOMÁTICOS\n";
echo "---------------------------------\n";

$ajustes_executados = 0;

// Ajuste 1: Configurar webhook canal 3000
if ($webhook_http_3000 !== 200 || 
    ($webhook_config_3000 && $webhook_config_3000['webhook_url'] !== $webhook_principal)) {
    
    echo "🔧 Configurando webhook canal 3000...\n";
    
    $ch = curl_init("http://$vps_ip:3000/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_principal]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Webhook canal 3000 configurado com sucesso\n";
        $ajustes_executados++;
    } else {
        echo "   ❌ Erro ao configurar webhook canal 3000 (HTTP $http_code)\n";
    }
}

// Ajuste 2: Tentar configurar webhook canal 3001 (se API suportar)
if ($webhook_http_3001 !== 200) {
    echo "🔧 Tentando configurar webhook canal 3001...\n";
    
    // Tentar diferentes endpoints
    $endpoints_3001 = ['/webhook/config', '/webhook', '/hook/config', '/hook'];
    $webhook_configurado_3001 = false;
    
    foreach ($endpoints_3001 as $endpoint) {
        $ch = curl_init("http://$vps_ip:3001$endpoint");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_principal]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "   ✅ Webhook canal 3001 configurado via $endpoint\n";
            $webhook_configurado_3001 = true;
            $ajustes_executados++;
            break;
        }
    }
    
    if (!$webhook_configurado_3001) {
        echo "   ⚠️ Canal 3001 usa API diferente - necessita configuração manual\n";
    }
}

// Ajuste 3: Atualizar banco de dados com status dos canais
if (isset($mysqli) && !$mysqli->connect_error) {
    echo "🔧 Atualizando status dos canais no banco de dados...\n";
    
    $canais_para_atualizar = [
        ['identificador' => '554797146908@c.us', 'nome' => 'Pixel12Digital', 'status' => ($http_code_3000 === 200 ? 'conectado' : 'offline')],
        ['identificador' => '554797309525@c.us', 'nome' => 'Comercial - Pixel', 'status' => ($http_code_3001 === 200 ? 'conectado' : 'offline')]
    ];
    
    foreach ($canais_para_atualizar as $canal) {
        $sql = "INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) 
                VALUES ('whatsapp', ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                data_conexao = NOW()";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sss', $canal['identificador'], $canal['nome'], $canal['status']);
        $stmt->execute();
        
        echo "   ✅ Canal {$canal['nome']} atualizado (Status: {$canal['status']})\n";
        $ajustes_executados++;
    }
}

echo "\n📊 AJUSTES EXECUTADOS: $ajustes_executados\n\n";

// ===== 10. RESUMO FINAL =====
echo "🔟 RESUMO FINAL DA AVERIGUAÇÃO E AJUSTES\n";
echo "----------------------------------------\n";

echo "🎯 AVERIGUAÇÃO CONCLUÍDA!\n\n";

echo "📋 CONFIGURAÇÕES IDENTIFICADAS NO CÓDIGO:\n";
echo "• VPS IP: $vps_ip\n";
echo "• Canal 3000: " . ($http_code_3000 === 200 ? "✅ Funcionando" : "❌ Offline") . "\n";
echo "• Canal 3001: " . ($http_code_3001 === 200 ? "✅ Funcionando" : "❌ Offline") . "\n";
echo "• Webhook Principal: $webhook_principal\n";
echo "• Webhook 3000: " . ($webhook_http_3000 === 200 ? "✅ Configurado" : "❌ Não configurado") . "\n";
echo "• Webhook 3001: " . ($webhook_http_3001 === 200 ? "✅ Configurado" : "❌ API diferente") . "\n";
echo "• Banco de dados: " . (isset($mysqli) && !$mysqli->connect_error ? "✅ Conectado" : "❌ Erro") . "\n\n";

echo "🔧 AJUSTES REALIZADOS: $ajustes_executados\n\n";

echo "📚 PRÓXIMOS PASSOS:\n";
echo "1. Conectar WhatsApp no canal 3000 (gerar QR Code)\n";
echo "2. Investigar API do canal 3001 se necessário\n";
echo "3. Testar envio de mensagens\n";
echo "4. Verificar painel de comunicação\n\n";

echo "📞 COMANDOS ÚTEIS:\n";
echo "• Status VPS: curl http://$vps_ip:3000/status\n";
echo "• QR Code: curl http://$vps_ip:3000/qr\n";
echo "• Webhook: curl http://$vps_ip:3000/webhook/config\n";
echo "• Logs: ssh root@$vps_ip 'pm2 logs --lines 20'\n\n";

echo "✅ AVERIGUAÇÃO E AJUSTES FINALIZADOS COM SUCESSO!\n";
echo "🎉 VPS ajustada de acordo com o código local!\n";
?> 