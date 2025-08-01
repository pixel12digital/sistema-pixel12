<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧹 Limpeza de Cache QR Code</h1>";

// 1. Limpar cache do navegador
echo "<h2>1. Headers de Cache</h2>";
echo "<p>Adicionando headers para forçar atualização...</p>";

// 2. Verificar se o arquivo foi atualizado
echo "<h2>2. Verificação do Arquivo</h2>";
$arquivo = __DIR__ . '/painel/ajax_whatsapp.php';
$conteudo = file_get_contents($arquivo);

if (strpos($conteudo, '$status_endpoint = "/status"') !== false) {
    echo "<p style='color: green;'>✅ Arquivo ajax_whatsapp.php atualizado corretamente</p>";
} else {
    echo "<p style='color: red;'>❌ Arquivo ajax_whatsapp.php NÃO foi atualizado</p>";
}

if (strpos($conteudo, 'Usar endpoint de status geral em vez de QR específico') !== false) {
    echo "<p style='color: green;'>✅ Comentário de correção encontrado</p>";
} else {
    echo "<p style='color: red;'>❌ Comentário de correção NÃO encontrado</p>";
}

// 3. Teste direto do endpoint
echo "<h2>3. Teste Direto do Endpoint</h2>";
$vps_url = "http://212.85.11.238:3001";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Status da VPS (3001):</strong> HTTP $http_code</p>";

if ($http_code == 200) {
    $data = json_decode($response, true);
    if (isset($data['qr']) && !empty($data['qr'])) {
        echo "<p style='color: green;'>✅ QR Code disponível na VPS!</p>";
        echo "<p><strong>QR Code:</strong> " . substr($data['qr'], 0, 50) . "...</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ QR Code não disponível na VPS</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Erro ao acessar VPS</p>";
}

// 4. Teste do ajax_whatsapp.php com cache busting
echo "<h2>4. Teste com Cache Busting</h2>";

$test_data = [
    'action' => 'qr',
    'porta' => '3001',
    'canal_id' => '37',
    '_' => time() // Cache busting
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://pixel12digital.com.br/app/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Cache-Control: no-cache',
    'Pragma: no-cache'
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Resposta do ajax_whatsapp.php:</strong></p>";
echo "<p><strong>HTTP Code:</strong> $http_code</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>Erro cURL:</strong> $curl_error</p>";
} else {
    $data = json_decode($response, true);
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    
    if ($data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ QR Code funcionando!</p>";
        echo "<p><strong>QR Code:</strong> " . substr($data['qr'], 0, 50) . "...</p>";
        echo "<p><strong>Mensagem:</strong> {$data['message']}</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ QR Code não funcionando</p>";
        echo "<p><strong>Erro:</strong> {$data['error']}</p>";
    }
}

echo "<h2>🎯 Instruções</h2>";
echo "<p><strong>Se o teste 4 funcionou:</strong></p>";
echo "<ol>";
echo "<li>O QR code está funcionando</li>";
echo "<li>Teste o botão 'Conectar' no painel</li>";
echo "<li>Escaneie o QR code com o WhatsApp</li>";
echo "</ol>";

echo "<p><strong>Se o teste 4 não funcionou:</strong></p>";
echo "<ol>";
echo "<li>Limpe o cache do navegador (Ctrl+F5)</li>";
echo "<li>Teste novamente o botão 'Conectar'</li>";
echo "<li>Se persistir, pode ser cache do servidor</li>";
echo "</ol>";
?> 