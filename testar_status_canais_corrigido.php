<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🧪 TESTANDO STATUS DOS CANAIS CORRIGIDO\n";
echo "=======================================\n\n";

// 1. Verificar status atual no banco
echo "📊 STATUS ATUAL NO BANCO:\n";
$canais = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");

while ($canal = $canais->fetch_assoc()) {
    $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
    echo "   {$status_icon} {$canal['nome_exibicao']} (Porta {$canal['porta']}): {$canal['status']}\n";
    echo "      Identificador: {$canal['identificador']}\n";
}

echo "\n🔍 TESTANDO VERIFICAÇÃO INDIVIDUAL:\n";

// 2. Testar verificação individual de cada canal
$canais_test = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");

while ($canal = $canais_test->fetch_assoc()) {
    $porta = $canal['porta'];
    $canal_id = $canal['id'];
    
    echo "\n📱 Testando {$canal['nome_exibicao']} (Porta $porta):\n";
    
    // Fazer requisição para ajax_whatsapp.php com porta específica
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php?action=status&porta=$porta");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($result && $httpCode === 200) {
        $json = json_decode($result, true);
        
        if ($json) {
            $isConnected = false;
            
            // Verificar se está conectado
            if (isset($json['ready']) && $json['ready'] === true) {
                $isConnected = true;
            }
            
            if (isset($json['status']) && in_array($json['status'], ['connected', 'already_connected', 'authenticated', 'ready'])) {
                $isConnected = true;
            }
            
            $status_detectado = $isConnected ? 'conectado' : 'pendente';
            $status_icon = $isConnected ? '🟢' : '🟡';
            
            echo "   {$status_icon} Status detectado: $status_detectado\n";
            echo "   Ready: " . ($json['ready'] ? 'true' : 'false') . "\n";
            echo "   Message: " . ($json['message'] ?? 'N/A') . "\n";
            
            // Verificar se precisa atualizar o banco
            if ($status_detectado !== $canal['status']) {
                echo "   ⚠️ Status diferente do banco! Atualizando...\n";
                $update = $mysqli->query("UPDATE canais_comunicacao SET status = '$status_detectado' WHERE id = $canal_id");
                if ($update) {
                    echo "   ✅ Status atualizado no banco\n";
                } else {
                    echo "   ❌ Erro ao atualizar: " . $mysqli->error . "\n";
                }
            } else {
                echo "   ✅ Status já está correto no banco\n";
            }
        } else {
            echo "   ❌ Erro ao decodificar JSON\n";
        }
    } else {
        echo "   ❌ Erro na requisição (HTTP $httpCode)\n";
    }
}

echo "\n📊 STATUS FINAL NO BANCO:\n";
$canais_final = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");

while ($canal = $canais_final->fetch_assoc()) {
    $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
    echo "   {$status_icon} {$canal['nome_exibicao']} (Porta {$canal['porta']}): {$canal['status']}\n";
}

echo "\n🎯 TESTE CONCLUÍDO!\n";
echo "Agora os canais devem aparecer corretamente como 'Conectado' no chat\n";
echo "em vez de 'Pendente' quando estiverem realmente funcionando.\n";
?> 