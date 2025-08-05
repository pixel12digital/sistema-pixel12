<?php
/**
 * ✅ VERIFICAR CORREÇÃO FINAL
 * 
 * Script para verificar se as correções aplicadas no servidor resolveram os problemas
 * Baseado nos comandos SSH executados
 */

echo "✅ VERIFICANDO CORREÇÃO FINAL\n";
echo "============================\n\n";

$vps_ip = '212.85.11.238';

// ===== 1. VERIFICAR STATUS ATUAL =====
echo "1️⃣ STATUS ATUAL DAS VPS\n";
echo "------------------------\n";

// VPS 3000
echo "🔍 Verificando VPS 3000...\n";
$ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3000 === 200) {
    $status_3000 = json_decode($response_3000, true);
    echo "  ✅ VPS 3000 respondendo\n";
    echo "  📊 Ready: " . ($status_3000['ready'] ? 'true' : 'false') . "\n";
    echo "  📱 Sessões: " . ($status_3000['sessions'] ?? 0) . "\n";
    
    if (isset($status_3000['clients_status'])) {
        foreach ($status_3000['clients_status'] as $session => $status) {
            echo "  🔍 Sessão $session: " . ($status['status'] ?? 'unknown') . "\n";
            if (isset($status['message'])) {
                echo "    💬 " . $status['message'] . "\n";
            }
        }
    }
} else {
    echo "  ❌ VPS 3000 não responde (HTTP $http_code_3000)\n";
}

echo "\n";

// VPS 3001
echo "🔍 Verificando VPS 3001...\n";
$ch = curl_init("http://$vps_ip:3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code_3001 === 200) {
    $status_3001 = json_decode($response_3001, true);
    echo "  ✅ VPS 3001 respondendo\n";
    echo "  📊 Ready: " . ($status_3001['ready'] ? 'true' : 'false') . "\n";
    echo "  📱 Porta: " . ($status_3001['port'] ?? 'unknown') . "\n";
    
    if (isset($status_3001['clients_status'])) {
        foreach ($status_3001['clients_status'] as $session => $status) {
            echo "  🔍 Sessão $session: " . ($status['ready'] ? 'ready' : 'not ready') . "\n";
            echo "    📱 QR: " . ($status['hasQR'] ? 'disponível' : 'não disponível') . "\n";
        }
    }
} else {
    echo "  ❌ VPS 3001 não responde (HTTP $http_code_3001)\n";
}

echo "\n";

// ===== 2. TESTAR ENDPOINTS ESPECÍFICOS =====
echo "2️⃣ TESTANDO ENDPOINTS ESPECÍFICOS\n";
echo "---------------------------------\n";

$endpoints_teste = [
    '/status' => 'Status geral',
    '/qr' => 'QR Code',
    '/session/start/default' => 'Iniciar sessão default',
    '/session/start/comercial' => 'Iniciar sessão comercial',
    '/webhook/config' => 'Configuração webhook'
];

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
        
        if ($http_code !== 200 && $endpoint === '/qr') {
            $error_data = json_decode($response, true);
            if ($error_data && isset($error_data['message'])) {
                echo "    💬 Erro: " . $error_data['message'] . "\n";
            }
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
    } else {
        echo "  VPS 3001: ❌ (não responde)\n";
    }
    
    echo "\n";
}

// ===== 3. VERIFICAR PROBLEMAS RESTANTES =====
echo "3️⃣ PROBLEMAS RESTANTES\n";
echo "----------------------\n";

$problemas = [];

// Verificar se VPS 3000 ainda tem problema de Chromium
if ($http_code_3000 === 200 && isset($status_3000['clients_status'])) {
    foreach ($status_3000['clients_status'] as $session => $status) {
        if (isset($status['message']) && strpos($status['message'], 'Could not find expected browser (chrome)') !== false) {
            $problemas[] = "VPS 3000: Chromium ainda não está funcionando";
            echo "❌ VPS 3000: Chromium ainda não está funcionando\n";
            echo "   💡 O Chromium foi instalado mas pode precisar de configuração adicional\n";
            break;
        }
    }
}

// Verificar se VPS 3001 está funcionando corretamente
if ($http_code_3001 === 200 && $status_3001['ready']) {
    echo "✅ VPS 3001: Funcionando corretamente\n";
} else {
    $problemas[] = "VPS 3001: Não está funcionando corretamente";
    echo "❌ VPS 3001: Não está funcionando corretamente\n";
}

echo "\n";

// ===== 4. SOLUÇÕES ADICIONAIS =====
if (!empty($problemas)) {
    echo "4️⃣ SOLUÇÕES ADICIONAIS\n";
    echo "----------------------\n";
    
    if (in_array("VPS 3000: Chromium ainda não está funcionando", $problemas)) {
        echo "🔧 SOLUÇÃO ADICIONAL PARA VPS 3000:\n";
        echo "1. Conectar via SSH: ssh root@212.85.11.238\n";
        echo "2. Verificar se Chromium está instalado: which chromium-browser\n";
        echo "3. Verificar variável PATH: echo \$PATH\n";
        echo "4. Tentar instalar via snap: snap install chromium\n";
        echo "5. Ou configurar Puppeteer para usar Chromium instalado\n";
        echo "6. Reiniciar processo: pm2 restart whatsapp-3000\n";
        echo "\n";
    }
}

// ===== 5. RESUMO FINAL =====
echo "5️⃣ RESUMO FINAL\n";
echo "----------------\n";

echo "📊 STATUS ATUAL:\n";
echo "• VPS 3000: " . ($http_code_3000 === 200 ? '✅' : '❌') . " (Ready: " . ($status_3000['ready'] ?? false ? '✅' : '❌') . ")\n";
echo "• VPS 3001: " . ($http_code_3001 === 200 ? '✅' : '❌') . " (Ready: " . ($status_3001['ready'] ?? false ? '✅' : '❌') . ")\n";

echo "\n🎯 PROGRESSO:\n";
if ($http_code_3001 === 200 && $status_3001['ready']) {
    echo "✅ VPS 3001 está funcionando perfeitamente!\n";
    echo "✅ Pode ser usada como VPS principal\n";
}

if ($http_code_3000 === 200) {
    echo "✅ VPS 3000 está respondendo\n";
    if ($status_3000['ready']) {
        echo "✅ VPS 3000 está pronta para uso\n";
    } else {
        echo "⚠️ VPS 3000 ainda precisa de ajustes no Chromium\n";
    }
}

echo "\n💡 RECOMENDAÇÃO:\n";
if ($http_code_3001 === 200 && $status_3001['ready']) {
    echo "Use a VPS 3001 como principal enquanto corrige a VPS 3000\n";
    echo "A VPS 3001 está funcionando perfeitamente!\n";
} else {
    echo "Ambas as VPS precisam de ajustes adicionais\n";
}

echo "\n✅ Verificação concluída!\n";
?> 