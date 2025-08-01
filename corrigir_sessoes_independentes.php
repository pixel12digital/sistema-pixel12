<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO SESSÕES INDEPENDENTES\n";
echo "===================================\n\n";

// 1. Corrigir sessão do canal financeiro
echo "📋 CORRIGINDO CANAL FINANCEIRO:\n";
echo "===============================\n";
$sql_update_financeiro = "UPDATE canais_comunicacao SET sessao = 'default', status = 'desconectado' WHERE id = 36";
if ($mysqli->query($sql_update_financeiro)) {
    echo "✅ Canal financeiro configurado para sessão 'default'\n";
} else {
    echo "❌ Erro: " . $mysqli->error . "\n";
}

// 2. Verificar canal comercial
echo "\n📋 VERIFICANDO CANAL COMERCIAL:\n";
echo "===============================\n";
$sql_check_comercial = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE id = 37";
$result = $mysqli->query($sql_check_comercial);
if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
}

// 3. Verificar configuração final
echo "\n📋 CONFIGURAÇÃO FINAL:\n";
echo "======================\n";
$sql_final = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result_final = $mysqli->query($sql_final);

if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

// 4. Verificar se os servidores estão prontos
echo "\n📋 VERIFICANDO SERVIDORES:\n";
echo "==========================\n";

// Porta 3000
$vps_url_3000 = "http://212.85.11.238:3000";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3000 = curl_exec($ch);
$http_code_3000 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Porta 3000: HTTP $http_code_3000 - " . ($http_code_3000 == 200 ? "✅ Online" : "❌ Offline") . "\n";

// Porta 3001
$vps_url_3001 = "http://212.85.11.238:3001";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3001 = curl_exec($ch);
$http_code_3001 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Porta 3001: HTTP $http_code_3001 - " . ($http_code_3001 == 200 ? "✅ Online" : "❌ Offline") . "\n";

echo "\n💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "2. Clique em 'Conectar' no canal Financeiro (porta 3000)\n";
echo "3. Clique em 'Conectar' no canal Comercial (porta 3001)\n";
echo "4. Cada canal deve conectar independentemente\n";
echo "5. Teste desconectar um canal por vez\n\n";

echo "🔧 CONFIGURAÇÃO CORRETA:\n";
echo "=======================\n";
echo "• Canal Financeiro: Porta 3000, Sessão 'default'\n";
echo "• Canal Comercial: Porta 3001, Sessão 'comercial'\n";
echo "• Cada canal tem seu próprio servidor e sessão\n";
echo "• Desconectar um não afeta o outro\n";
?> 