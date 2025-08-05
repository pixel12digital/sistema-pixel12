<?php
/**
 * 🔧 CORRIGIR VPS - PROBLEMA DO CHROMIUM
 * 
 * Script para resolver o problema identificado:
 * "Could not find expected browser (chrome) locally. Run `npm install` to download the correct Chromium revision"
 */

echo "🔧 CORRIGINDO VPS - PROBLEMA DO CHROMIUM\n";
echo "========================================\n\n";

$vps_ip = '212.85.11.238';

// ===== 1. DIAGNÓSTICO ATUAL =====
echo "1️⃣ DIAGNÓSTICO ATUAL\n";
echo "--------------------\n";

// Verificar VPS 3000
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
    
    if (isset($status_3000['clients_status']['default'])) {
        $default_status = $status_3000['clients_status']['default'];
        echo "  🔍 Status default: " . ($default_status['status'] ?? 'unknown') . "\n";
        if (isset($default_status['message'])) {
            echo "  💬 Mensagem: " . $default_status['message'] . "\n";
        }
    }
} else {
    echo "  ❌ VPS 3000 não responde (HTTP $http_code_3000)\n";
}

echo "\n";

// Verificar VPS 3001
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
    echo "  📱 Sessões: " . ($status_3001['sessions'] ?? 0) . "\n";
} else {
    echo "  ❌ VPS 3001 não responde (HTTP $http_code_3001)\n";
}

echo "\n";

// ===== 2. IDENTIFICAR PROBLEMAS =====
echo "2️⃣ PROBLEMAS IDENTIFICADOS\n";
echo "---------------------------\n";

$problemas = [];

if ($http_code_3000 === 200 && isset($status_3000['clients_status']['default']['message'])) {
    $message = $status_3000['clients_status']['default']['message'];
    if (strpos($message, 'Could not find expected browser (chrome)') !== false) {
        $problemas[] = "VPS 3000: Chromium não instalado";
        echo "❌ VPS 3000: Chromium não instalado\n";
        echo "   💡 Solução: Instalar Chromium no servidor\n";
    }
}

if ($http_code_3000 === 200 && !$status_3000['ready']) {
    $problemas[] = "VPS 3000: Não está pronto";
    echo "❌ VPS 3000: Não está pronto\n";
}

if ($http_code_3001 !== 200) {
    $problemas[] = "VPS 3001: Não responde";
    echo "❌ VPS 3001: Não responde\n";
}

echo "\n";

// ===== 3. SOLUÇÕES =====
echo "3️⃣ SOLUÇÕES NECESSÁRIAS\n";
echo "----------------------\n";

if (in_array("VPS 3000: Chromium não instalado", $problemas)) {
    echo "🔧 SOLUÇÃO PARA VPS 3000:\n";
    echo "1. Conectar via SSH: ssh root@212.85.11.238\n";
    echo "2. Navegar para o diretório: cd /var/whatsapp-api\n";
    echo "3. Instalar dependências: npm install\n";
    echo "4. Ou instalar Chromium: apt update && apt install -y chromium-browser\n";
    echo "5. Reiniciar processo: pm2 restart whatsapp-3000\n";
    echo "6. Salvar configuração: pm2 save\n";
    echo "\n";
}

if (in_array("VPS 3001: Não responde", $problemas)) {
    echo "🔧 SOLUÇÃO PARA VPS 3001:\n";
    echo "1. Conectar via SSH: ssh root@212.85.11.238\n";
    echo "2. Verificar processos: pm2 list\n";
    echo "3. Reiniciar processo: pm2 restart whatsapp-3001\n";
    echo "4. Ou reiniciar todos: pm2 restart all\n";
    echo "5. Salvar configuração: pm2 save\n";
    echo "\n";
}

// ===== 4. TESTAR ENDPOINTS CORRETOS =====
echo "4️⃣ TESTANDO ENDPOINTS CORRETOS\n";
echo "------------------------------\n";

$endpoints_corretos = [
    '/status' => 'Status geral',
    '/qr' => 'QR Code (sem parâmetro)',
    '/session/start/default' => 'Iniciar sessão default',
    '/session/start/comercial' => 'Iniciar sessão comercial'
];

foreach ($endpoints_corretos as $endpoint => $descricao) {
    echo "🔍 Testando $descricao...\n";
    
    // Testar VPS 3000
    if ($http_code_3000 === 200) {
        $method = ($endpoint === '/session/start/default' || $endpoint === '/session/start/comercial') ? 'POST' : 'GET';
        
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
        $method = ($endpoint === '/session/start/default' || $endpoint === '/session/start/comercial') ? 'POST' : 'GET';
        
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

// ===== 5. COMANDOS SSH PRONTOS =====
echo "5️⃣ COMANDOS SSH PRONTOS\n";
echo "------------------------\n";

echo "📋 COMANDOS PARA EXECUTAR NO SERVIDOR:\n\n";

echo "# 1. Conectar ao servidor\n";
echo "ssh root@212.85.11.238\n\n";

echo "# 2. Verificar status atual\n";
echo "pm2 list\n";
echo "pm2 logs whatsapp-3000 --lines 10\n";
echo "pm2 logs whatsapp-3001 --lines 10\n\n";

echo "# 3. Instalar Chromium (se necessário)\n";
echo "apt update\n";
echo "apt install -y chromium-browser\n\n";

echo "# 4. Navegar para o diretório e instalar dependências\n";
echo "cd /var/whatsapp-api\n";
echo "npm install\n\n";

echo "# 5. Reiniciar processos\n";
echo "pm2 restart whatsapp-3000\n";
echo "pm2 restart whatsapp-3001\n";
echo "pm2 save\n\n";

echo "# 6. Verificar se funcionou\n";
echo "curl http://localhost:3000/status\n";
echo "curl http://localhost:3001/status\n\n";

echo "✅ Script concluído!\n";
echo "💡 Execute os comandos SSH acima para corrigir os problemas\n";
?> 