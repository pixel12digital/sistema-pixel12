<?php
require_once 'painel/config.php';

echo "<h1>🔍 Verificação da Chave Atual</h1>";

echo "<h2>Configuração Atual</h2>";
echo "<p><strong>Ambiente:</strong> " . (strpos($_SERVER['SERVER_NAME'] ?? '', 'localhost') !== false ? 'LOCAL' : 'PRODUÇÃO') . "</p>";
echo "<p><strong>Chave Atual:</strong> " . ASAAS_API_KEY . "</p>";
echo "<p><strong>Tipo:</strong> " . (strpos(ASAAS_API_KEY, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO') . "</p>";
echo "<p><strong>URL da API:</strong> " . ASAAS_API_URL . "</p>";

echo "<h2>Teste da Chave Atual</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . ASAAS_API_KEY
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

echo "<h2>Comparação de Chaves</h2>";
echo "<p><strong>Chave no config.php:</strong> " . substr(ASAAS_API_KEY, 0, 30) . "...</p>";
echo "<p><strong>Chave que você testou:</strong> \$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjZjZWNkODQ1LWIxZTUtNDE0MS1iZTNmLTFmYTdlM2U0YzcxMDo6JGFhY2hfZmFjNDFlYmMtYzAyNi00Y2FjLWEzOWEtZmI2YWZkNGU5ZjBl</p>";

if (ASAAS_API_KEY === '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjZjZWNkODQ1LWIxZTUtNDE0MS1iZTNmLTFmYTdlM2U0YzcxMDo6JGFhY2hfZmFjNDFlYmMtYzAyNi00Y2FjLWEzOWEtZmI2YWZkNGU5ZjBl') {
    echo "<div style='background:#d4edda;color:#155724;padding:15px;border-radius:5px;'>";
    echo "<strong>✅ Chaves são iguais!</strong>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;'>";
    echo "<strong>❌ Chaves são diferentes!</strong> O sistema está usando uma chave diferente da que você testou.";
    echo "</div>";
}
?> 