<?php
require_once "config.php";
require_once "painel/db.php";

echo "🔄 ATUALIZANDO STATUS DO CANAL FINANCEIRO\n";
echo "========================================\n\n";

// 1. Verificar status atual no banco
echo "📋 STATUS ATUAL NO BANCO:\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3000");
if ($row = $result->fetch_assoc()) {
    echo "   Canal: " . $row['nome_exibicao'] . "\n";
    echo "   Porta: " . $row['porta'] . "\n";
    echo "   Status: " . $row['status'] . "\n";
    echo "   Identificador: " . ($row['identificador'] ?: 'VAZIO') . "\n";
    echo "   Data Conexão: " . ($row['data_conexao'] ?: 'NULL') . "\n";
} else {
    echo "   ❌ Canal não encontrado\n";
    exit;
}

echo "\n";

// 2. Verificar status real na VPS
echo "📡 VERIFICANDO STATUS REAL NA VPS:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ VPS respondeu (HTTP 200)\n";
    echo "   📊 Status: " . ($data['ready'] ? 'CONECTADO' : 'DESCONECTADO') . "\n";
    
    if (isset($data['clients_status']['default'])) {
        $client = $data['clients_status']['default'];
        echo "   📱 Client Status: " . ($client['status'] ?? 'N/A') . "\n";
        echo "   📞 Client Number: " . ($client['number'] ?? 'N/A') . "\n";
    }
    
    // 3. Atualizar banco se estiver conectado
    if ($data['ready'] && isset($data['clients_status']['default']['status']) && $data['clients_status']['default']['status'] === 'connected') {
        echo "\n🔄 ATUALIZANDO BANCO DE DADOS:\n";
        
        $update = $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE porta = 3000");
        if ($update) {
            echo "   ✅ Status atualizado para 'conectado'\n";
            echo "   ✅ Data de conexão atualizada\n";
        } else {
            echo "   ❌ Erro ao atualizar: " . $mysqli->error . "\n";
        }
        
        // Se há um número na resposta, atualizar também
        if (isset($data['clients_status']['default']['number']) && $data['clients_status']['default']['number']) {
            $numero = $data['clients_status']['default']['number'];
            $update_numero = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero' WHERE porta = 3000");
            if ($update_numero) {
                echo "   ✅ Número atualizado: $numero\n";
            } else {
                echo "   ❌ Erro ao atualizar número: " . $mysqli->error . "\n";
            }
        }
    } else {
        echo "\n⚠️ WhatsApp não está conectado na VPS\n";
    }
} else {
    echo "   ❌ VPS não respondeu (HTTP $http_code)\n";
}

echo "\n";

// 4. Verificar status final no banco
echo "📋 STATUS FINAL NO BANCO:\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3000");
if ($row = $result->fetch_assoc()) {
    echo "   Canal: " . $row['nome_exibicao'] . "\n";
    echo "   Porta: " . $row['porta'] . "\n";
    echo "   Status: " . $row['status'] . "\n";
    echo "   Identificador: " . ($row['identificador'] ?: 'VAZIO') . "\n";
    echo "   Data Conexão: " . ($row['data_conexao'] ?: 'NULL') . "\n";
}

echo "\n✅ ATUALIZAÇÃO CONCLUÍDA!\n";
?> 