<?php
require_once 'config.php';

echo "🔧 CORRIGINDO LÓGICA DE PORTAS INDEPENDENTES\n";
echo "============================================\n\n";

// Testar consulta direta por porta
echo "📱 TESTE DIRETO POR PORTA:\n";
echo "==========================\n";

// Porta 3000 (Financeiro)
echo "🔍 PORTA 3000 (FINANCEIRO):\n";
$vps_url_3000 = "http://212.85.11.238:3000";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_3000\n";
echo "Resposta: $response_3000\n\n";

$sessions_3000 = json_decode($response_3000, true);
$status_financeiro = 'desconectado';

if (($sessions_3000['total'] ?? 0) > 0) {
    foreach ($sessions_3000['sessions'] ?? [] as $session) {
        if ($session['name'] === 'default') {
            $status_financeiro = ($session['status']['status'] === 'connected') ? 'conectado' : 'desconectado';
            echo "Sessão 'default': " . $session['status']['status'] . " → Status: $status_financeiro\n";
            break;
        }
    }
}

// Porta 3001 (Comercial)
echo "🔍 PORTA 3001 (COMERCIAL):\n";
$vps_url_3001 = "http://212.85.11.238:3001";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_3001\n";
echo "Resposta: $response_3001\n\n";

$sessions_3001 = json_decode($response_3001, true);
$status_comercial = 'desconectado';

if (($sessions_3001['total'] ?? 0) > 0) {
    foreach ($sessions_3001['sessions'] ?? [] as $session) {
        if ($session['name'] === 'comercial') {
            $status_comercial = ($session['status']['status'] === 'connected') ? 'conectado' : 'desconectado';
            echo "Sessão 'comercial': " . $session['status']['status'] . " → Status: $status_comercial\n";
            break;
        }
    }
}

// Testar via ajax_whatsapp.php
echo "📱 TESTE VIA AJAX_WHATSAPP.PHP:\n";
echo "==============================\n";

// Testar canal financeiro (ID 36)
echo "🔍 CANAL FINANCEIRO (ID 36):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php?action=status&canal_id=36');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_ajax_36 = curl_exec($ch);
$http_code_ajax_36 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_ajax_36\n";
echo "Resposta: $response_ajax_36\n\n";

$data_ajax_36 = json_decode($response_ajax_36, true);

// Testar canal comercial (ID 37)
echo "🔍 CANAL COMERCIAL (ID 37):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php?action=status&canal_id=37');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_ajax_37 = curl_exec($ch);
$http_code_ajax_37 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code_ajax_37\n";
echo "Resposta: $response_ajax_37\n\n";

$data_ajax_37 = json_decode($response_ajax_37, true);

// Comparar resultados
echo "📊 COMPARAÇÃO DOS RESULTADOS:\n";
echo "=============================\n";

echo "🔍 STATUS DIRETO:\n";
echo "- Porta 3000 (Financeiro): $status_financeiro\n";
echo "- Porta 3001 (Comercial): $status_comercial\n\n";

echo "🔍 STATUS VIA AJAX:\n";
echo "- Canal 36 (Financeiro): " . ($data_ajax_36['ready'] ? 'conectado' : 'desconectado') . "\n";
echo "- Canal 37 (Comercial): " . ($data_ajax_37['ready'] ? 'conectado' : 'desconectado') . "\n\n";

echo "🔍 DEBUG INFO:\n";
echo "- Canal 36 - Session: " . ($data_ajax_36['debug']['session_checked'] ?? 'N/A') . ", Porta: " . ($data_ajax_36['debug']['porta_used'] ?? 'N/A') . "\n";
echo "- Canal 37 - Session: " . ($data_ajax_37['debug']['session_checked'] ?? 'N/A') . ", Porta: " . ($data_ajax_37['debug']['porta_used'] ?? 'N/A') . "\n\n";

// Identificar problema
echo "💡 DIAGNÓSTICO:\n";
echo "===============\n";

$status_direto_36 = $status_financeiro;
$status_direto_37 = $status_comercial;
$status_ajax_36 = $data_ajax_36['ready'] ? 'conectado' : 'desconectado';
$status_ajax_37 = $data_ajax_37['ready'] ? 'conectado' : 'desconectado';

if ($status_direto_36 !== $status_ajax_36 || $status_direto_37 !== $status_ajax_37) {
    echo "❌ PROBLEMA IDENTIFICADO!\n";
    echo "❌ Status direto e via ajax não coincidem\n";
    echo "❌ A lógica do ajax_whatsapp.php está incorreta\n\n";
    
    echo "🔧 SOLUÇÃO NECESSÁRIA:\n";
    echo "=====================\n";
    echo "1. O ajax_whatsapp.php está consultando a porta errada\n";
    echo "2. Precisa garantir que cada canal consulte sua porta específica\n";
    echo "3. Verificar se a variável \$porta está sendo definida corretamente\n";
} else {
    echo "✅ Status coincidem!\n";
    echo "✅ Lógica está funcionando corretamente\n";
}

echo "\n📱 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Verificar se o problema está na definição da porta\n";
echo "2. Corrigir a lógica do ajax_whatsapp.php\n";
echo "3. Testar novamente após correções\n";
?> 