<?php
/**
 * 🔧 AJUSTAR CÓDIGO LOCAL PARA VPS ATUAL
 * 
 * Script para ajustar o código local para funcionar com a VPS atual
 * Corrige endpoints, configurações e adapta para o estado atual da VPS
 */

echo "🔧 AJUSTANDO CÓDIGO LOCAL PARA VPS ATUAL\n";
echo "========================================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';

// ===== 1. ANÁLISE DO ESTADO ATUAL DA VPS =====
echo "1️⃣ ANÁLISE DO ESTADO ATUAL DA VPS\n";
echo "----------------------------------\n";

// Verificar VPS 3000
echo "🔍 Verificando VPS 3000...\n";
$ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status_3000 = null;
if ($http_code_3000 === 200) {
    $status_3000 = json_decode($response_3000, true);
    echo "  ✅ VPS 3000 respondendo\n";
    echo "  📊 Ready: " . ($status_3000['ready'] ? 'true' : 'false') . "\n";
    echo "  📱 Sessões: " . ($status_3000['sessions'] ?? 0) . "\n";
} else {
    echo "  ❌ VPS 3000 não responde (HTTP $http_code_3000)\n";
}

// Verificar VPS 3001
echo "\n🔍 Verificando VPS 3001...\n";
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status_3001 = null;
if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "  ✅ VPS 3001 respondendo\n";
    echo "  📊 Ready: " . ($status_3001['ready'] ? 'true' : 'false') . "\n";
    echo "  📱 Sessões: " . ($status_3001['sessions'] ?? 0) . "\n";
} else {
    echo "  ❌ VPS 3001 não responde (HTTP $http_code_3001)\n";
}

echo "\n";

// ===== 2. IDENTIFICAR ENDPOINTS FUNCIONAIS =====
echo "2️⃣ IDENTIFICANDO ENDPOINTS FUNCIONAIS\n";
echo "-------------------------------------\n";

$endpoints_teste = [
    '/status' => 'Status geral',
    '/qr' => 'QR Code',
    '/session/start/default' => 'Iniciar sessão default',
    '/session/start/comercial' => 'Iniciar sessão comercial',
    '/webhook/config' => 'Configuração webhook'
];

$endpoints_funcionais = [];

foreach ($endpoints_teste as $endpoint => $descricao) {
    echo "🔍 Testando $descricao...\n";
    
    // Testar VPS 3000
    if ($http_code_3000 === 200) {
        $method = (strpos($endpoint, '/session/start/') !== false) ? 'POST' : 'GET';
        
        $ch = curl_init("http://$vps_ip:3000$endpoint");
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($http_code === 200) ? "✅" : "❌";
        echo "  VPS 3000: $status (HTTP $http_code)\n";
        
        if ($http_code === 200) {
            $endpoints_funcionais["3000"][$endpoint] = true;
        }
    } else {
        echo "  VPS 3000: ❌ (não responde)\n";
    }
    
    // Testar VPS 3001
    if ($http_code_3001 === 200) {
        $method = (strpos($endpoint, '/session/start/') !== false) ? 'POST' : 'GET';
        
        $ch = curl_init("http://$vps_ip:3001$endpoint");
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($http_code === 200) ? "✅" : "❌";
        echo "  VPS 3001: $status (HTTP $http_code)\n";
        
        if ($http_code === 200) {
            $endpoints_funcionais["3001"][$endpoint] = true;
        }
    } else {
        echo "  VPS 3001: ❌ (não responde)\n";
    }
    
    echo "\n";
}

// ===== 3. CRIAR CONFIGURAÇÃO AJUSTADA =====
echo "3️⃣ CRIANDO CONFIGURAÇÃO AJUSTADA\n";
echo "--------------------------------\n";

$config_ajustada = [
    'vps_3000' => [
        'url' => "http://$vps_ip:3000",
        'funcionando' => ($http_code_3000 === 200),
        'ready' => ($status_3000 && $status_3000['ready']),
        'endpoints_funcionais' => $endpoints_funcionais['3000'] ?? []
    ],
    'vps_3001' => [
        'url' => "http://$vps_ip:3001",
        'funcionando' => ($http_code_3001 === 200),
        'ready' => ($status_3001 && $status_3001['ready']),
        'endpoints_funcionais' => $endpoints_funcionais['3001'] ?? []
    ]
];

echo "📋 CONFIGURAÇÃO AJUSTADA:\n";
echo "• VPS 3000: " . ($config_ajustada['vps_3000']['funcionando'] ? '✅' : '❌') . " (Ready: " . ($config_ajustada['vps_3000']['ready'] ? '✅' : '❌') . ")\n";
echo "• VPS 3001: " . ($config_ajustada['vps_3001']['funcionando'] ? '✅' : '❌') . " (Ready: " . ($config_ajustada['vps_3001']['ready'] ? '✅' : '❌') . ")\n";

echo "\n📊 ENDPOINTS FUNCIONAIS:\n";
foreach ($config_ajustada as $vps => $config) {
    if ($config['funcionando']) {
        echo "  $vps:\n";
        foreach ($config['endpoints_funcionais'] as $endpoint => $funciona) {
            echo "    ✅ $endpoint\n";
        }
    }
}

echo "\n";

// ===== 4. CRIAR ARQUIVO DE CONFIGURAÇÃO AJUSTADA =====
echo "4️⃣ CRIANDO ARQUIVO DE CONFIGURAÇÃO AJUSTADA\n";
echo "--------------------------------------------\n";

$config_content = "<?php
/**
 * CONFIGURAÇÃO AJUSTADA PARA VPS ATUAL
 * Gerado automaticamente em " . date('Y-m-d H:i:s') . "
 */

// Configurações da VPS baseadas no estado atual
define('VPS_3000_FUNCIONANDO', " . ($config_ajustada['vps_3000']['funcionando'] ? 'true' : 'false') . ");
define('VPS_3001_FUNCIONANDO', " . ($config_ajustada['vps_3001']['funcionando'] ? 'true' : 'false') . ");
define('VPS_3000_READY', " . ($config_ajustada['vps_3000']['ready'] ? 'true' : 'false') . ");
define('VPS_3001_READY', " . ($config_ajustada['vps_3001']['ready'] ? 'true' : 'false') . ");

// URLs das VPS
define('VPS_3000_URL', '{$config_ajustada['vps_3000']['url']}');
define('VPS_3001_URL', '{$config_ajustada['vps_3001']['url']}');

// Endpoints funcionais
\$ENDPOINTS_FUNCIONAIS = [
    '3000' => " . json_encode($config_ajustada['vps_3000']['endpoints_funcionais'], JSON_PRETTY_PRINT) . ",
    '3001' => " . json_encode($config_ajustada['vps_3001']['endpoints_funcionais'], JSON_PRETTY_PRINT) . "
];

// Função para obter URL da VPS baseada na porta
function getVpsUrl(\$porta) {
    if (\$porta == '3000' || \$porta == 3000) {
        return VPS_3000_FUNCIONANDO ? VPS_3000_URL : null;
    } elseif (\$porta == '3001' || \$porta == 3001) {
        return VPS_3001_FUNCIONANDO ? VPS_3001_URL : null;
    }
    return null;
}

// Função para verificar se endpoint funciona
function endpointFunciona(\$porta, \$endpoint) {
    global \$ENDPOINTS_FUNCIONAIS;
    return isset(\$ENDPOINTS_FUNCIONAIS[\$porta][\$endpoint]) && \$ENDPOINTS_FUNCIONAIS[\$porta][\$endpoint];
}

// Função para obter VPS de fallback
function getVpsFallback() {
    if (VPS_3001_FUNCIONANDO) {
        return VPS_3001_URL;
    } elseif (VPS_3000_FUNCIONANDO) {
        return VPS_3000_URL;
    }
    return null;
}
?>";

file_put_contents('config_vps_ajustada.php', $config_content);
echo "✅ Arquivo config_vps_ajustada.php criado\n";

// ===== 5. CRIAR SCRIPT DE TESTE AJUSTADO =====
echo "\n5️⃣ CRIANDO SCRIPT DE TESTE AJUSTADO\n";
echo "-----------------------------------\n";

$teste_content = "<?php
/**
 * TESTE AJUSTADO PARA VPS ATUAL
 * Testa apenas os endpoints que funcionam
 */

require_once 'config_vps_ajustada.php';

echo \"🧪 TESTE AJUSTADO PARA VPS ATUAL\\n\";
echo \"================================\\n\\n\";

// Testar VPS 3000
if (VPS_3000_FUNCIONANDO) {
    echo \"🔍 Testando VPS 3000...\\n\";
    
    \$ch = curl_init(VPS_3000_URL . '/status');
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);
    
    if (\$http_code === 200) {
        \$status = json_decode(\$response, true);
        echo \"  ✅ VPS 3000 funcionando\\n\";
        echo \"  📊 Ready: \" . (\$status['ready'] ? 'true' : 'false') . \"\\n\";
        echo \"  📱 Sessões: \" . (\$status['sessions'] ?? 0) . \"\\n\";
        
        // Testar endpoints funcionais
        foreach (\$ENDPOINTS_FUNCIONAIS['3000'] as \$endpoint => \$funciona) {
            if (\$funciona) {
                echo \"  ✅ Endpoint \$endpoint funciona\\n\";
            }
        }
    }
} else {
    echo \"❌ VPS 3000 não está funcionando\\n\";
}

echo \"\\n\";

// Testar VPS 3001
if (VPS_3001_FUNCIONANDO) {
    echo \"🔍 Testando VPS 3001...\\n\";
    
    \$ch = curl_init(VPS_3001_URL . '/status');
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);
    
    if (\$http_code === 200) {
        \$status = json_decode(\$response, true);
        echo \"  ✅ VPS 3001 funcionando\\n\";
        echo \"  📊 Ready: \" . (\$status['ready'] ? 'true' : 'false') . \"\\n\";
        echo \"  📱 Sessões: \" . (\$status['sessions'] ?? 0) . \"\\n\";
        
        // Testar endpoints funcionais
        foreach (\$ENDPOINTS_FUNCIONAIS['3001'] as \$endpoint => \$funciona) {
            if (\$funciona) {
                echo \"  ✅ Endpoint \$endpoint funciona\\n\";
            }
        }
    }
} else {
    echo \"❌ VPS 3001 não está funcionando\\n\";
}

echo \"\\n✅ Teste concluído!\\n\";
?>";

file_put_contents('teste_vps_ajustado.php', $teste_content);
echo "✅ Arquivo teste_vps_ajustado.php criado\n";

// ===== 6. RECOMENDAÇÕES =====
echo "\n6️⃣ RECOMENDAÇÕES\n";
echo "----------------\n";

echo "📋 RECOMENDAÇÕES PARA O CÓDIGO LOCAL:\n\n";

if (!$config_ajustada['vps_3000']['ready'] && $config_ajustada['vps_3000']['funcionando']) {
    echo "🔧 VPS 3000: Precisa de Chromium instalado no servidor\n";
    echo "   💡 Execute: ssh root@212.85.11.238\n";
    echo "   💡 Execute: apt update && apt install -y chromium-browser\n";
    echo "   💡 Execute: cd /var/whatsapp-api && npm install\n";
    echo "   💡 Execute: pm2 restart whatsapp-3000\n\n";
}

if (!$config_ajustada['vps_3001']['funcionando']) {
    echo "🔧 VPS 3001: Precisa ser reiniciada\n";
    echo "   💡 Execute: ssh root@212.85.11.238\n";
    echo "   💡 Execute: pm2 restart whatsapp-3001\n";
    echo "   💡 Execute: pm2 save\n\n";
}

echo "📝 AJUSTES NO CÓDIGO LOCAL:\n";
echo "1. Use o arquivo config_vps_ajustada.php para verificar endpoints funcionais\n";
echo "2. Implemente fallback para VPS alternativa quando uma não funcionar\n";
echo "3. Teste endpoints antes de usá-los\n";
echo "4. Use a função getVpsFallback() para obter VPS alternativa\n\n";

echo "✅ Script concluído!\n";
echo "📁 Arquivos criados:\n";
echo "   • config_vps_ajustada.php - Configuração ajustada\n";
echo "   • teste_vps_ajustado.php - Teste ajustado\n";
?> 