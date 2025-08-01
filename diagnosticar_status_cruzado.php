<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 DIAGNÓSTICO STATUS CRUZADO\n";
echo "=============================\n\n";

// Verificar configuração atual dos canais
echo "📋 CONFIGURAÇÃO ATUAL DOS CANAIS:\n";
echo "=================================\n";
$sql = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

echo "\n📋 STATUS REAL DOS SERVIDORES:\n";
echo "==============================\n";

// Verificar porta 3000
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

// Verificar porta 3001
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

// Analisar se há problema na lógica de atualização
echo "🔍 ANÁLISE DO PROBLEMA:\n";
echo "=======================\n";

$sessions_3000 = json_decode($response_3000, true);
$sessions_3001 = json_decode($response_3001, true);

echo "Sessões na porta 3000: " . ($sessions_3000['total'] ?? 0) . "\n";
echo "Sessões na porta 3001: " . ($sessions_3001['total'] ?? 0) . "\n\n";

// Verificar se há sessões conectadas
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
echo "O problema pode estar na lógica de atualização de status do painel.\n";
echo "Quando um canal conecta, o sistema pode estar atualizando todos os canais.\n\n";

echo "🔧 SOLUÇÃO:\n";
echo "===========\n";
echo "1. Verificar se a lógica de atualização está correta\n";
echo "2. Garantir que cada canal atualize apenas seu próprio status\n";
echo "3. Verificar se não há conflito na lógica de sessões\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Conectar apenas um canal por vez\n";
echo "2. Verificar se o status está correto no banco\n";
echo "3. Testar desconexão independente\n";
?> 