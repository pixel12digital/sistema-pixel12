<?php
/**
 * 🔍 ANÁLISE PRECISA DO PROBLEMA
 * 
 * Baseada em dados reais do código, não em suposições
 */

echo "🔍 ANÁLISE PRECISA DO PROBLEMA\n";
echo "=============================\n\n";

// ===== 1. CONFIGURAÇÃO ATUAL DO AJAX_WHATSAPP.PHP =====
echo "1️⃣ CONFIGURAÇÃO ATUAL DO AJAX_WHATSAPP.PHP:\n";
echo "============================================\n";

$arquivo_ajax = 'painel/ajax_whatsapp.php';
if (file_exists($arquivo_ajax)) {
    $conteudo = file_get_contents($arquivo_ajax);
    
    // Extrair configurações reais
    preg_match('/\$vps_url\s*=\s*[\'"]([^\'"]+)[\'"]/', $conteudo, $matches);
    $vps_url_padrao = $matches[1] ?? 'NÃO ENCONTRADO';
    
    preg_match('/if\s*\(\$porta\s*==\s*[\'"]3000[\'"]\s*\|\|\s*\$porta\s*==\s*3000\)\s*\{[^}]*\$vps_url\s*=\s*[\'"]([^\'"]+)[\'"]/', $conteudo, $matches);
    $vps_url_3000 = $matches[1] ?? 'NÃO ENCONTRADO';
    
    preg_match('/else\s*\{[^}]*\$vps_url\s*=\s*[\'"]([^\'"]+)[\'"]/', $conteudo, $matches);
    $vps_url_3001 = $matches[1] ?? 'NÃO ENCONTRADO';
    
    echo "✅ VPS URL Padrão: $vps_url_padrao\n";
    echo "✅ VPS URL Porta 3000: $vps_url_3000\n";
    echo "✅ VPS URL Porta 3001: $vps_url_3001\n";
} else {
    echo "❌ Arquivo ajax_whatsapp.php não encontrado\n";
}

// ===== 2. CONFIGURAÇÃO ATUAL DO COMUNICACAO.PHP =====
echo "\n2️⃣ CONFIGURAÇÃO ATUAL DO COMUNICACAO.PHP:\n";
echo "===========================================\n";

$arquivo_comunicacao = 'painel/comunicacao.php';
if (file_exists($arquivo_comunicacao)) {
    $conteudo = file_get_contents($arquivo_comunicacao);
    
    preg_match('/const AJAX_WHATSAPP_URL\s*=\s*[\'"]([^\'"]+)[\'"]/', $conteudo, $matches);
    $ajax_url = $matches[1] ?? 'NÃO ENCONTRADO';
    
    echo "✅ AJAX_WHATSAPP_URL: $ajax_url\n";
} else {
    echo "❌ Arquivo comunicacao.php não encontrado\n";
}

// ===== 3. TESTE DE CONECTIVIDADE REAL =====
echo "\n3️⃣ TESTE DE CONECTIVIDADE REAL:\n";
echo "================================\n";

$vps_ips = ['212.85.11.238:3000', '212.85.11.238:3001'];

foreach ($vps_ips as $vps_ip) {
    $url = "http://$vps_ip/status";
    echo "🌐 Testando: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "❌ Erro cURL: $curl_error\n";
    } else {
        echo "✅ HTTP Code: $http_code\n";
        if ($http_code == 200) {
            $data = json_decode($response, true);
            $ready = isset($data['ready']) ? ($data['ready'] ? 'true' : 'false') : 'N/A';
            echo "✅ Ready: $ready\n";
        }
    }
    echo "\n";
}

// ===== 4. ANÁLISE DO PROBLEMA =====
echo "4️⃣ ANÁLISE PRECISA DO PROBLEMA:\n";
echo "===============================\n";

echo "🎯 PROBLEMA IDENTIFICADO:\n";
echo "========================\n";

if (strpos($ajax_url, 'localhost:8080') !== false) {
    echo "❌ PROBLEMA 1: URL incorreta no comunicacao.php\n";
    echo "   - Atual: $ajax_url\n";
    echo "   - Deveria ser: URL de produção\n";
    echo "   - Impacto: Painel não consegue acessar o proxy PHP\n\n";
}

if ($vps_url_3000 === 'http://212.85.11.238:3000') {
    echo "❌ PROBLEMA 2: VPS 3000 pode estar com problemas\n";
    echo "   - URL configurada: $vps_url_3000\n";
    echo "   - Status: Precisa verificar se está funcionando\n\n";
}

if ($vps_url_3001 === 'http://212.85.11.238:3001') {
    echo "✅ VPS 3001 configurada corretamente\n";
    echo "   - URL: $vps_url_3001\n\n";
}

echo "🔧 SOLUÇÃO PRECISA:\n";
echo "==================\n";

echo "1. Corrigir URL no comunicacao.php para produção\n";
echo "2. Verificar se VPS 3000 está funcionando\n";
echo "3. Se VPS 3000 não funcionar, usar apenas VPS 3001\n";

echo "\n📋 COMANDOS PARA EXECUTAR:\n";
echo "=========================\n";
echo "1. Verificar status das VPS:\n";
echo "   curl -s http://212.85.11.238:3000/status | jq .\n";
echo "   curl -s http://212.85.11.238:3001/status | jq .\n\n";

echo "2. Corrigir URL no comunicacao.php:\n";
echo "   - Alterar localhost:8080 para URL de produção\n\n";

echo "3. Se necessário, forçar uso da VPS 3001:\n";
echo "   - Modificar ajax_whatsapp.php para usar apenas 3001\n";
?> 