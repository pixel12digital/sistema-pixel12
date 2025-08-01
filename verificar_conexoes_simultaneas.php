<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO CONEXÕES SIMULTÂNEAS\n";
echo "===================================\n\n";

// Verificar configuração dos canais no banco
echo "📋 CONFIGURAÇÃO DOS CANAIS:\n";
echo "===========================\n";
$sql = "SELECT id, nome_exibicao, identificador, porta, status, sessao FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

echo "\n📋 STATUS DOS SERVIDORES:\n";
echo "=========================\n";

// Verificar porta 3000 (Financeiro)
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

// Verificar porta 3001 (Comercial)
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

// Analisar se há conflito
echo "🔍 ANÁLISE DE CONFLITO:\n";
echo "======================\n";

$sessions_3000 = json_decode($response_3000, true);
$sessions_3001 = json_decode($response_3001, true);

echo "Sessões na porta 3000: " . ($sessions_3000['total'] ?? 0) . "\n";
echo "Sessões na porta 3001: " . ($sessions_3001['total'] ?? 0) . "\n\n";

if (($sessions_3000['total'] ?? 0) > 0) {
    echo "📱 SESSÕES ATIVAS NA PORTA 3000:\n";
    foreach ($sessions_3000['sessions'] ?? [] as $session) {
        echo "- {$session['name']}: {$session['status']['status']}\n";
    }
}

if (($sessions_3001['total'] ?? 0) > 0) {
    echo "📱 SESSÕES ATIVAS NA PORTA 3001:\n";
    foreach ($sessions_3001['sessions'] ?? [] as $session) {
        echo "- {$session['name']}: {$session['status']['status']}\n";
    }
}

echo "\n💡 DIAGNÓSTICO:\n";
echo "===============\n";
echo "1. Cada porta deve ter suas próprias sessões independentes\n";
echo "2. Porta 3000 deve ter sessão 'default'\n";
echo "3. Porta 3001 deve ter sessão 'comercial'\n";
echo "4. Se desconectar uma, a outra não deve ser afetada\n\n";

echo "🔧 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Conectar canal financeiro na porta 3000\n";
echo "2. Conectar canal comercial na porta 3001\n";
echo "3. Testar desconexão independente\n";
?> 