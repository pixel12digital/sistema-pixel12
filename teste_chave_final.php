<?php
/**
 * Teste Final da Chave da API Asaas
 */

echo "<h1>🔑 Teste Final da Chave da API</h1>";

$chave = '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjZjZWNkODQ1LWIxZTUtNDE0MS1iZTNmLTFmYTdlM2U0YzcxMDo6JGFhY2hfZmFjNDFlYmMtYzAyNi00Y2FjLWEzOWEtZmI2YWZkNGU5ZjBl';

echo "<p><strong>Chave para testar:</strong> " . substr($chave, 0, 30) . "...</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.asaas.com/api/v3/customers?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . $chave
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>Código HTTP:</strong> $httpCode</p>";
echo "<p><strong>Erro cURL:</strong> " . ($curlError ?: 'Nenhum') . "</p>";

if ($httpCode == 200) {
    echo "<div style='background:#d4edda;color:#155724;padding:15px;border-radius:5px;'>";
    echo "<strong>✅ Chave válida!</strong> Conexão estabelecida com sucesso.";
    echo "</div>";
} elseif ($httpCode == 401) {
    echo "<div style='background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;'>";
    echo "<strong>❌ Chave inválida (401)</strong>";
    $response = json_decode($result, true);
    if ($response && isset($response['errors'][0]['description'])) {
        echo "<br>Detalhes: " . $response['errors'][0]['description'];
    }
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;'>";
    echo "<strong>⚠️ Erro HTTP $httpCode</strong>";
    if ($curlError) {
        echo "<br>Erro de conexão: $curlError";
    }
    echo "</div>";
}

if ($result) {
    echo "<h3>Resposta da API:</h3>";
    echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;'>";
    echo htmlspecialchars($result);
    echo "</pre>";
}

echo "<h2>📋 Instruções para o Frontend:</h2>";
echo "<div style='background:#e7f3ff;border:1px solid #0066cc;padding:15px;border-radius:5px;'>";
echo "<p><strong>1.</strong> Copie esta chave exata:</p>";
echo "<code style='background:#f8f9fa;padding:5px;border-radius:3px;'>$chave</code>";
echo "<p><strong>2.</strong> Cole no campo 'Nova Chave da API' no frontend</p>";
echo "<p><strong>3.</strong> Clique em 'Testar Nova Chave'</p>";
echo "<p><strong>4.</strong> Se der sucesso, clique em 'Aplicar Nova Chave'</p>";
echo "</div>";
?> 