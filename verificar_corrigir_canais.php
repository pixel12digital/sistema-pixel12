<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICAÇÃO E CORREÇÃO DOS CANAIS WHATSAPP\n";
echo "=============================================\n\n";

// 1. Verificar configuração atual dos canais
echo "📊 CONFIGURAÇÃO ATUAL DOS CANAIS:\n";
$sql = "SELECT id, nome_exibicao, identificador, status, porta, tipo FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Nome: {$row['nome_exibicao']} | Porta: {$row['porta']} | Status: {$row['status']}\n";
        echo "   Identificador: " . ($row['identificador'] ?: 'NÃO CONFIGURADO') . "\n\n";
    }
} else {
    echo "❌ Erro na consulta: " . $mysqli->error . "\n";
}

// 2. Verificar status dos servidores
echo "🖥️ STATUS DOS SERVIDORES:\n";

// Canal 3000 (Financeiro)
echo "\n📱 Canal 3000 (Financeiro):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Servidor funcionando\n";
    echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Desconectado') . "\n";
    if (isset($data['clients_status']['default']['status'])) {
        echo "   WhatsApp: " . $data['clients_status']['default']['status'] . "\n";
    }
} else {
    echo "   ❌ Servidor não responde (HTTP $httpCode)\n";
}

// Canal 3001 (Comercial)
echo "\n📱 Canal 3001 (Comercial):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3001/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Servidor funcionando\n";
    echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Desconectado') . "\n";
    if (isset($data['clients_status']['default']['status'])) {
        echo "   WhatsApp: " . $data['clients_status']['default']['status'] . "\n";
    }
} else {
    echo "   ❌ Servidor não responde (HTTP $httpCode)\n";
}

// 3. Verificar webhooks
echo "\n🔗 CONFIGURAÇÃO DOS WEBHOOKS:\n";

// Webhook Canal 3000
echo "\n📡 Canal 3000 Webhook:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Webhook configurado\n";
    echo "   URL: " . ($data['webhook_url'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Erro ao verificar webhook (HTTP $httpCode)\n";
}

// Webhook Canal 3001
echo "\n📡 Canal 3001 Webhook:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3001/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Webhook configurado\n";
    echo "   URL: " . ($data['webhook_url'] ?? 'N/A') . "\n";
} else {
    echo "   ❌ Erro ao verificar webhook (HTTP $httpCode)\n";
}

// 4. Verificar bancos de dados
echo "\n🗄️ VERIFICAÇÃO DOS BANCOS DE DADOS:\n";

// Banco Principal (Financeiro)
echo "\n📊 Banco Principal (pixel12digital):\n";
try {
    $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao";
    $result = $mysqli->query($sql);
    if ($result) {
        $total = $result->fetch_assoc()['total'];
        echo "   ✅ Banco acessível\n";
        echo "   Total de mensagens: $total\n";
        
        if ($total > 0) {
            $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 3";
            $result = $mysqli->query($sql);
            echo "   📨 Últimas mensagens:\n";
            while ($row = $result->fetch_assoc()) {
                echo "      ID {$row['id']} - {$row['data_hora']} - Canal {$row['canal_id']} - " . substr($row['mensagem'], 0, 30) . "...\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

// Banco Comercial
echo "\n📊 Banco Comercial (pixel12digital_comercial):\n";
try {
    $mysqli_comercial = new mysqli(DB_HOST, DB_USER, DB_PASS, 'pixel12digital_comercial');
    if (!$mysqli_comercial->connect_error) {
        $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao";
        $result = $mysqli_comercial->query($sql);
        if ($result) {
            $total = $result->fetch_assoc()['total'];
            echo "   ✅ Banco acessível\n";
            echo "   Total de mensagens: $total\n";
            
            if ($total > 0) {
                $sql = "SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 3";
                $result = $mysqli_comercial->query($sql);
                echo "   📨 Últimas mensagens:\n";
                while ($row = $result->fetch_assoc()) {
                    echo "      ID {$row['id']} - {$row['data_hora']} - Canal {$row['canal_id']} - " . substr($row['mensagem'], 0, 30) . "...\n";
                }
            }
        }
        $mysqli_comercial->close();
    } else {
        echo "   ❌ Erro ao conectar: " . $mysqli_comercial->connect_error . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n🎯 DIAGNÓSTICO CONCLUÍDO!\n";
?> 