<?php
/**
 * Teste da Correção do QR Code
 */

echo "🧪 TESTE DA CORREÇÃO DO QR CODE\n";
echo "==============================\n\n";

echo "📋 TESTANDO PROXY CORRIGIDO\n";
echo "---------------------------\n\n";

// Testar proxy com porta 3000
echo "🔗 Testando proxy porta 3000 (default):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=qr&porta=3000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $http_code\n";
if ($http_code == 200) {
    $json = json_decode($response, true);
    if ($json) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            $qr = $json['qr'];
            if (empty($qr)) {
                echo "❌ QR: não disponível\n";
            } elseif (str_starts_with($qr, 'undefined')) {
                echo "❌ QR: inválido (começa com 'undefined')\n";
                echo "   QR: " . substr($qr, 0, 100) . "...\n";
            } else {
                echo "✅ QR: válido (" . strlen($qr) . " chars)\n";
                echo "   QR: " . substr($qr, 0, 50) . "...\n";
            }
        }
        if (isset($json['debug'])) {
            echo "🔍 Debug info disponível\n";
            if (isset($json['debug']['qr_valid'])) {
                echo "   QR válido: " . ($json['debug']['qr_valid'] ? 'sim' : 'não') . "\n";
            }
            if (isset($json['debug']['qr_length'])) {
                echo "   QR length: " . $json['debug']['qr_length'] . "\n";
            }
        }
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "---\n\n";

// Testar proxy com porta 3001
echo "🔗 Testando proxy porta 3001 (comercial):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=qr&porta=3001');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $http_code\n";
if ($http_code == 200) {
    $json = json_decode($response, true);
    if ($json) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            $qr = $json['qr'];
            if (empty($qr)) {
                echo "❌ QR: não disponível\n";
            } elseif (str_starts_with($qr, 'undefined')) {
                echo "❌ QR: inválido (começa com 'undefined')\n";
                echo "   QR: " . substr($qr, 0, 100) . "...\n";
            } else {
                echo "✅ QR: válido (" . strlen($qr) . " chars)\n";
                echo "   QR: " . substr($qr, 0, 50) . "...\n";
            }
        }
        if (isset($json['debug'])) {
            echo "🔍 Debug info disponível\n";
            if (isset($json['debug']['qr_valid'])) {
                echo "   QR válido: " . ($json['debug']['qr_valid'] ? 'sim' : 'não') . "\n";
            }
            if (isset($json['debug']['qr_length'])) {
                echo "   QR length: " . $json['debug']['qr_length'] . "\n";
            }
        }
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "---\n\n";

echo "📋 TESTANDO ENDPOINTS DIRETOS NA VPS\n";
echo "------------------------------------\n\n";

// Testar endpoints diretos
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

foreach ($vps_urls as $porta => $vps_url) {
    echo "🔗 Testando VPS porta $porta:\n";
    
    // Testar endpoint /qr
    $session = ($porta == '3000') ? 'default' : 'comercial';
    $qr_url = "$vps_url/qr?session=$session";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $qr_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 Status: $http_code\n";
    if ($http_code == 200) {
        $json = json_decode($response, true);
        if ($json && isset($json['qr'])) {
            $qr = $json['qr'];
            if (empty($qr)) {
                echo "❌ QR: não disponível\n";
            } elseif (str_starts_with($qr, 'undefined')) {
                echo "❌ QR: inválido (começa com 'undefined')\n";
                echo "   QR: " . substr($qr, 0, 100) . "...\n";
            } else {
                echo "✅ QR: válido (" . strlen($qr) . " chars)\n";
                echo "   QR: " . substr($qr, 0, 50) . "...\n";
            }
        }
    } else {
        echo "❌ Erro HTTP: $http_code\n";
    }
    echo "---\n";
}

echo "✅ Teste concluído!\n";
echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Se os QR Codes estiverem válidos, teste no painel administrativo\n";
echo "2. Se ainda houver 'undefined', execute os logs na VPS para investigar\n";
echo "3. Monitore os logs de erro do PHP para debug adicional\n";
?> 