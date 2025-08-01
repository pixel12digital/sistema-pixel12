<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔄 Forçar Atualização no Servidor</h1>";

// 1. Verificar arquivo local
echo "<h2>1. Verificação Local</h2>";
$arquivo_local = __DIR__ . '/painel/ajax_whatsapp.php';
$conteudo_local = file_get_contents($arquivo_local);

if (strpos($conteudo_local, '$status_endpoint = "/status"') !== false) {
    echo "<p style='color: green;'>✅ Arquivo local correto</p>";
} else {
    echo "<p style='color: red;'>❌ Arquivo local incorreto</p>";
    exit;
}

// 2. Fazer upload para o servidor
echo "<h2>2. Upload para Servidor</h2>";

$url_upload = 'https://pixel12digital.com.br/app/upload_ajax_whatsapp.php';
$post_data = [
    'arquivo' => base64_encode($conteudo_local),
    'nome_arquivo' => 'ajax_whatsapp.php',
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_upload);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Upload HTTP Code:</strong> $http_code</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>Erro cURL:</strong> $curl_error</p>";
} else {
    $data = json_decode($response, true);
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    
    if (isset($data['success']) && $data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Upload realizado com sucesso!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro no upload</p>";
    }
}

// 3. Teste após upload
echo "<h2>3. Teste Após Upload</h2>";

sleep(2); // Aguardar 2 segundos

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
    'Cache-Control: no-cache, no-store, must-revalidate',
    'Pragma: no-cache',
    'Expires: 0'
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Teste HTTP Code:</strong> $http_code</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>Erro cURL:</strong> $curl_error</p>";
} else {
    $data = json_decode($response, true);
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    
    if ($data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ QR Code funcionando após upload!</p>";
        echo "<p><strong>QR Code:</strong> " . substr($data['qr'], 0, 50) . "...</p>";
        echo "<p><strong>Mensagem:</strong> {$data['message']}</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ QR Code ainda não funcionando</p>";
        echo "<p><strong>Erro:</strong> {$data['error']}</p>";
        echo "<p><strong>Debug:</strong> " . json_encode($data['debug']) . "</p>";
    }
}

echo "<h2>🎯 Próximos Passos</h2>";
echo "<p><strong>Se funcionou:</strong></p>";
echo "<ol>";
echo "<li>Teste o botão 'Conectar' no painel</li>";
echo "<li>Escaneie o QR code com o WhatsApp</li>";
echo "</ol>";

echo "<p><strong>Se não funcionou:</strong></p>";
echo "<ol>";
echo "<li>Pode ser cache do servidor web</li>";
echo "<li>Tente acessar diretamente: https://pixel12digital.com.br/app/painel/comunicacao.php</li>";
echo "<li>Limpe cache do navegador (Ctrl+F5)</li>";
echo "</ol>";
?> 