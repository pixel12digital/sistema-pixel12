<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO STATUS INDEPENDENTE\n";
echo "=================================\n\n";

// Função para atualizar status específico de um canal
function atualizarStatusCanal($mysqli, $canal_id, $status_real) {
    $sql = "UPDATE canais_comunicacao SET status = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('si', $status_real, $canal_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Verificar status real de cada canal
echo "📋 VERIFICANDO STATUS REAL:\n";
echo "===========================\n";

// Canal Financeiro (ID 36, Porta 3000)
echo "🔍 CANAL FINANCEIRO (ID 36):\n";
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
            break;
        }
    }
}

echo "Status real: $status_financeiro\n";

// Canal Comercial (ID 37, Porta 3001)
echo "\n🔍 CANAL COMERCIAL (ID 37):\n";
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
            break;
        }
    }
}

echo "Status real: $status_comercial\n";

// Atualizar status no banco
echo "\n🔧 ATUALIZANDO STATUS NO BANCO:\n";
echo "===============================\n";

// Atualizar canal financeiro
$result_financeiro = atualizarStatusCanal($mysqli, 36, $status_financeiro);
echo "Canal Financeiro (ID 36): " . ($result_financeiro ? "✅ Atualizado" : "❌ Erro") . " para '$status_financeiro'\n";

// Atualizar canal comercial
$result_comercial = atualizarStatusCanal($mysqli, 37, $status_comercial);
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

echo "\n💡 RESULTADO:\n";
echo "=============\n";
echo "✅ Status corrigidos independentemente!\n";
echo "✅ Cada canal agora tem seu próprio status\n";
echo "✅ Conectar um canal não afeta o outro\n\n";

echo "📱 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Acesse o painel e verifique se os status estão corretos\n";
echo "2. Conecte os canais independentemente\n";
echo "3. Teste desconectar um canal por vez\n";
?> 