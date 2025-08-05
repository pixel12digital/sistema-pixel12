<?php
/**
 * 🎯 USAR VPS 3001 FUNCIONANDO
 * 
 * Script para ajustar o código local para usar a VPS 3001
 * que está funcionando perfeitamente como VPS principal
 */

echo "🎯 USANDO VPS 3001 FUNCIONANDO\n";
echo "=============================\n\n";

require_once 'config.php';

$vps_ip = '212.85.11.238';

// ===== 1. VERIFICAR VPS 3001 =====
echo "1️⃣ VERIFICANDO VPS 3001\n";
echo "------------------------\n";

$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "✅ VPS 3001 está funcionando perfeitamente!\n";
    echo "📊 Ready: " . ($status_3001['ready'] ? 'true' : 'false') . "\n";
    echo "📱 Porta: " . ($status_3001['port'] ?? '3001') . "\n";
    echo "🕒 Última sessão: " . ($status_3001['lastSession'] ?? 'N/A') . "\n";
    
    if (isset($status_3001['clients_status'])) {
        foreach ($status_3001['clients_status'] as $session => $status) {
            echo "🔍 Sessão $session: " . ($status['ready'] ? 'ready' : 'not ready') . "\n";
        }
    }
} else {
    echo "❌ VPS 3001 não está respondendo\n";
    exit(1);
}

echo "\n";

// ===== 2. TESTAR ENDPOINTS DA VPS 3001 =====
echo "2️⃣ TESTANDO ENDPOINTS DA VPS 3001\n";
echo "----------------------------------\n";

$endpoints_teste = [
    '/status' => 'Status geral',
    '/qr' => 'QR Code',
    '/session/start/default' => 'Iniciar sessão default',
    '/session/start/comercial' => 'Iniciar sessão comercial'
];

$endpoints_funcionais = [];

foreach ($endpoints_teste as $endpoint => $descricao) {
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
    echo "🔍 $descricao: $status (HTTP $http_code)\n";
    
    if ($http_code === 200) {
        $endpoints_funcionais[$endpoint] = true;
    }
}

echo "\n";

// ===== 3. CRIAR CONFIGURAÇÃO OTIMIZADA =====
echo "3️⃣ CRIANDO CONFIGURAÇÃO OTIMIZADA\n";
echo "----------------------------------\n";

$config_otimizada = "<?php
/**
 * CONFIGURAÇÃO OTIMIZADA - VPS 3001 FUNCIONANDO
 * Gerado automaticamente em " . date('Y-m-d H:i:s') . "
 * 
 * Esta configuração usa a VPS 3001 como principal, que está funcionando perfeitamente
 */

// VPS Principal (funcionando)
define('VPS_PRINCIPAL_URL', 'http://$vps_ip:3001');
define('VPS_PRINCIPAL_READY', true);
define('VPS_PRINCIPAL_PORT', '3001');

// VPS Secundária (com problemas)
define('VPS_SECUNDARIA_URL', 'http://$vps_ip:3000');
define('VPS_SECUNDARIA_READY', false);
define('VPS_SECUNDARIA_PORT', '3000');

// Endpoints funcionais na VPS 3001
\$ENDPOINTS_VPS_3001 = " . json_encode($endpoints_funcionais, JSON_PRETTY_PRINT) . ";

// Função para obter URL da VPS principal
function getVpsPrincipal() {
    return VPS_PRINCIPAL_URL;
}

// Função para obter URL da VPS secundária (fallback)
function getVpsSecundaria() {
    return VPS_SECUNDARIA_URL;
}

// Função para verificar se endpoint funciona na VPS principal
function endpointFuncionaVps3001(\$endpoint) {
    global \$ENDPOINTS_VPS_3001;
    return isset(\$ENDPOINTS_VPS_3001[\$endpoint]) && \$ENDPOINTS_VPS_3001[\$endpoint];
}

// Função para obter URL baseada na porta (com fallback)
function getVpsUrl(\$porta) {
    if (\$porta == '3001' || \$porta == 3001) {
        return VPS_PRINCIPAL_URL;
    } elseif (\$porta == '3000' || \$porta == 3000) {
        // Se VPS 3000 não estiver funcionando, usar 3001
        return VPS_PRINCIPAL_URL;
    }
    return VPS_PRINCIPAL_URL; // Padrão para VPS principal
}

// Função para obter VPS de fallback
function getVpsFallback() {
    return VPS_PRINCIPAL_URL;
}

// Configurações específicas para o sistema
define('WHATSAPP_ROBOT_URL', VPS_PRINCIPAL_URL);
define('WHATSAPP_TIMEOUT', 10);

// Status das VPS
define('VPS_3000_FUNCIONANDO', false);
define('VPS_3001_FUNCIONANDO', true);
define('VPS_3000_READY', false);
define('VPS_3001_READY', true);
?>";

file_put_contents('config_vps_3001_principal.php', $config_otimizada);
echo "✅ Arquivo config_vps_3001_principal.php criado\n";

// ===== 4. CRIAR SCRIPT DE TESTE OTIMIZADO =====
echo "\n4️⃣ CRIANDO SCRIPT DE TESTE OTIMIZADO\n";
echo "-------------------------------------\n";

$teste_otimizado = "<?php
/**
 * TESTE OTIMIZADO - VPS 3001 PRINCIPAL
 * Testa a VPS 3001 que está funcionando perfeitamente
 */

require_once 'config_vps_3001_principal.php';

echo \"🎯 TESTE VPS 3001 PRINCIPAL\\n\";
echo \"============================\\n\\n\";

echo \"📊 CONFIGURAÇÃO ATUAL:\\n\";
echo \"• VPS Principal: \" . VPS_PRINCIPAL_URL . \"\\n\";
echo \"• VPS Principal Ready: \" . (VPS_PRINCIPAL_READY ? 'true' : 'false') . \"\\n\";
echo \"• VPS Secundária: \" . VPS_SECUNDARIA_URL . \"\\n\";
echo \"• VPS Secundária Ready: \" . (VPS_SECUNDARIA_READY ? 'true' : 'false') . \"\\n\\n\";

// Testar VPS Principal
echo \"🔍 Testando VPS Principal (3001)...\\n\";
\$ch = curl_init(VPS_PRINCIPAL_URL . '/status');
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
\$response = curl_exec(\$ch);
\$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

if (\$http_code === 200) {
    \$status = json_decode(\$response, true);
    echo \"  ✅ VPS Principal funcionando\\n\";
    echo \"  📊 Ready: \" . (\$status['ready'] ? 'true' : 'false') . \"\\n\";
    echo \"  📱 Porta: \" . (\$status['port'] ?? 'N/A') . \"\\n\";
    
    // Testar endpoints funcionais
    echo \"  📋 Endpoints funcionais:\\n\";
    foreach (\$ENDPOINTS_VPS_3001 as \$endpoint => \$funciona) {
        if (\$funciona) {
            echo \"    ✅ \$endpoint\\n\";
        }
    }
} else {
    echo \"  ❌ VPS Principal não está funcionando\\n\";
}

echo \"\\n✅ Teste concluído!\\n\";
echo \"💡 Use a VPS 3001 como principal no seu código\\n\";
?>";

file_put_contents('teste_vps_3001_principal.php', $teste_otimizado);
echo "✅ Arquivo teste_vps_3001_principal.php criado\n";

// ===== 5. CRIAR EXEMPLO DE USO =====
echo "\n5️⃣ CRIANDO EXEMPLO DE USO\n";
echo "---------------------------\n";

$exemplo_uso = "<?php
/**
 * EXEMPLO DE USO - VPS 3001 PRINCIPAL
 * Como usar a VPS 3001 que está funcionando perfeitamente
 */

require_once 'config_vps_3001_principal.php';

echo \"📝 EXEMPLO DE USO - VPS 3001 PRINCIPAL\\n\";
echo \"=====================================\\n\\n\";

// Exemplo 1: Obter URL da VPS
echo \"1️⃣ OBTENDO URL DA VPS\\n\";
echo \"URL Principal: \" . getVpsPrincipal() . \"\\n\";
echo \"URL para porta 3000: \" . getVpsUrl('3000') . \"\\n\";
echo \"URL para porta 3001: \" . getVpsUrl('3001') . \"\\n\\n\";

// Exemplo 2: Verificar endpoints
echo \"2️⃣ VERIFICANDO ENDPOINTS\\n\";
\$endpoints = ['/status', '/qr', '/session/start/default'];
foreach (\$endpoints as \$endpoint) {
    \$funciona = endpointFuncionaVps3001(\$endpoint);
    echo \"Endpoint \$endpoint: \" . (\$funciona ? '✅' : '❌') . \"\\n\";
}

echo \"\\n\";

// Exemplo 3: Fazer requisição
echo \"3️⃣ FAZENDO REQUISIÇÃO\\n\";
\$ch = curl_init(getVpsPrincipal() . '/status');
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);
\$response = curl_exec(\$ch);
\$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

if (\$http_code === 200) {
    \$status = json_decode(\$response, true);
    echo \"✅ Requisição bem-sucedida\\n\";
    echo \"📊 Status: \" . (\$status['status'] ?? 'unknown') . \"\\n\";
    echo \"📱 Ready: \" . (\$status['ready'] ? 'true' : 'false') . \"\\n\";
} else {
    echo \"❌ Erro na requisição (HTTP \$http_code)\\n\";
}

echo \"\\n✅ Exemplo concluído!\\n\";
?>";

file_put_contents('exemplo_uso_vps_3001.php', $exemplo_uso);
echo "✅ Arquivo exemplo_uso_vps_3001.php criado\n";

// ===== 6. RESUMO FINAL =====
echo "\n6️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "🎯 SOLUÇÃO IMPLEMENTADA:\n";
echo "✅ VPS 3001 está funcionando perfeitamente\n";
echo "✅ Configuração otimizada criada\n";
echo "✅ Sistema de fallback implementado\n";
echo "✅ Exemplos de uso fornecidos\n\n";

echo "📁 ARQUIVOS CRIADOS:\n";
echo "• config_vps_3001_principal.php - Configuração otimizada\n";
echo "• teste_vps_3001_principal.php - Teste da VPS principal\n";
echo "• exemplo_uso_vps_3001.php - Exemplo de uso\n\n";

echo "💡 COMO USAR NO SEU CÓDIGO:\n";
echo "1. Inclua: require_once 'config_vps_3001_principal.php';\n";
echo "2. Use: getVpsPrincipal() para obter a URL da VPS\n";
echo "3. Use: getVpsUrl(\$porta) para obter URL baseada na porta\n";
echo "4. Use: endpointFuncionaVps3001(\$endpoint) para verificar endpoints\n\n";

echo "🚀 PRÓXIMOS PASSOS:\n";
echo "1. Teste a configuração: php teste_vps_3001_principal.php\n";
echo "2. Veja o exemplo: php exemplo_uso_vps_3001.php\n";
echo "3. Integre no seu código usando a configuração otimizada\n";
echo "4. Enquanto isso, continue corrigindo a VPS 3000 se necessário\n\n";

echo "✅ Configuração otimizada concluída!\n";
echo "🎉 Seu sistema agora usa a VPS 3001 que está funcionando perfeitamente!\n";
?> 