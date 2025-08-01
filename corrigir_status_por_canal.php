<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO LÓGICA DE STATUS POR CANAL\n";
echo "========================================\n\n";

// Verificar configuração atual
echo "📋 CONFIGURAÇÃO ATUAL:\n";
echo "======================\n";
$sql = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

echo "\n🔍 VERIFICANDO STATUS REAL POR PORTA:\n";
echo "=====================================\n";

// Verificar porta 3000 (Financeiro - sessão default)
echo "📱 PORTA 3000 (FINANCEIRO - SESSÃO DEFAULT):\n";
$vps_url_3000 = "http://212.85.11.238:3000";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3000 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3000 = curl_exec($ch);
curl_close($ch);

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

// Verificar porta 3001 (Comercial - sessão comercial)
echo "\n📱 PORTA 3001 (COMERCIAL - SESSÃO COMERCIAL):\n";
$vps_url_3001 = "http://212.85.11.238:3001";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url_3001 . "/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response_3001 = curl_exec($ch);
curl_close($ch);

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

// Atualizar status específico de cada canal
echo "\n🔧 ATUALIZANDO STATUS ESPECÍFICO:\n";
echo "=================================\n";

// Atualizar canal financeiro (ID 36)
$sql_financeiro = "UPDATE canais_comunicacao SET status = ? WHERE id = 36";
$stmt = $mysqli->prepare($sql_financeiro);
$stmt->bind_param('s', $status_financeiro);
$result_financeiro = $stmt->execute();
$stmt->close();

echo "Canal Financeiro (ID 36): " . ($result_financeiro ? "✅ Atualizado" : "❌ Erro") . " para '$status_financeiro'\n";

// Atualizar canal comercial (ID 37)
$sql_comercial = "UPDATE canais_comunicacao SET status = ? WHERE id = 37";
$stmt = $mysqli->prepare($sql_comercial);
$stmt->bind_param('s', $status_comercial);
$result_comercial = $stmt->execute();
$stmt->close();

echo "Canal Comercial (ID 37): " . ($result_comercial ? "✅ Atualizado" : "❌ Erro") . " para '$status_comercial'\n";

// Verificar configuração final
echo "\n📋 CONFIGURAÇÃO FINAL:\n";
echo "======================\n";
$sql_final = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result_final = $mysqli->query($sql_final);

if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

echo "\n💡 PROBLEMA IDENTIFICADO:\n";
echo "=========================\n";
echo "❌ O ajax_whatsapp.php está sempre verificando a sessão 'default'\n";
echo "❌ Mesmo quando consulta porta 3001, lê status da sessão 'default'\n";
echo "❌ Isso faz ambos os canais mostrarem o mesmo status\n\n";

echo "🔧 SOLUÇÃO NECESSÁRIA:\n";
echo "=====================\n";
echo "1. Modificar ajax_whatsapp.php para verificar sessão específica\n";
echo "2. Usar sessão 'default' para porta 3000\n";
echo "3. Usar sessão 'comercial' para porta 3001\n";
echo "4. Cada canal terá seu próprio status independente\n\n";

echo "📱 STATUS ATUAL REAL:\n";
echo "====================\n";
echo "✅ Canal Financeiro (Porta 3000): $status_financeiro\n";
echo "✅ Canal Comercial (Porta 3001): $status_comercial\n";
echo "✅ Status atualizados independentemente!\n";
?> 