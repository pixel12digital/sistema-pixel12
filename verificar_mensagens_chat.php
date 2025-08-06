<?php
/**
 * 🔍 VERIFICAR MENSAGENS NO CHAT
 * 
 * Este script verifica se as mensagens estão sendo salvas e exibidas corretamente
 */

echo "🔍 VERIFICANDO MENSAGENS NO CHAT\n";
echo "===============================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

// ===== 1. VERIFICAR ESTRUTURA DA TABELA =====
echo "1️⃣ VERIFICANDO ESTRUTURA DA TABELA:\n";
echo "====================================\n";

$sql_estrutura = "DESCRIBE mensagens_comunicacao";
$result_estrutura = $mysqli->query($sql_estrutura);

if ($result_estrutura) {
    echo "✅ Tabela mensagens_comunicacao existe\n";
    $campos = [];
    while ($row = $result_estrutura->fetch_assoc()) {
        $campos[] = $row['Field'];
    }
    echo "📋 Campos: " . implode(', ', $campos) . "\n";
} else {
    echo "❌ Erro ao verificar estrutura: " . $mysqli->error . "\n";
    exit;
}

// ===== 2. VERIFICAR ÚLTIMAS MENSAGENS =====
echo "\n2️⃣ VERIFICANDO ÚLTIMAS MENSAGENS:\n";
echo "==================================\n";

$sql_ultimas = "SELECT id, canal_id, cliente_id, numero_whatsapp, mensagem, data_hora, direcao, status, tipo 
                FROM mensagens_comunicacao 
                ORDER BY id DESC 
                LIMIT 10";

$result_ultimas = $mysqli->query($sql_ultimas);

if ($result_ultimas && $result_ultimas->num_rows > 0) {
    echo "✅ Últimas mensagens encontradas:\n";
    while ($row = $result_ultimas->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Canal: {$row['canal_id']} | Cliente: {$row['cliente_id']} | Número: {$row['numero_whatsapp']} | Direção: {$row['direcao']} | Status: {$row['status']} | Tipo: {$row['tipo']} | Data: {$row['data_hora']}\n";
        echo "     Mensagem: " . substr($row['mensagem'], 0, 100) . "...\n";
    }
} else {
    echo "⚠️ Nenhuma mensagem encontrada na tabela\n";
}

// ===== 3. VERIFICAR CLIENTES =====
echo "\n3️⃣ VERIFICANDO CLIENTES:\n";
echo "=========================\n";

$sql_clientes = "SELECT id, nome, celular, telefone FROM clientes ORDER BY id DESC LIMIT 5";
$result_clientes = $mysqli->query($sql_clientes);

if ($result_clientes && $result_clientes->num_rows > 0) {
    echo "✅ Últimos clientes:\n";
    while ($row = $result_clientes->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Nome: {$row['nome']} | Celular: {$row['celular']} | Telefone: {$row['telefone']}\n";
    }
} else {
    echo "⚠️ Nenhum cliente encontrado\n";
}

// ===== 4. VERIFICAR CANAIS =====
echo "\n4️⃣ VERIFICANDO CANAIS:\n";
echo "======================\n";

$sql_canais = "SELECT id, nome_exibicao, porta, status FROM canais_comunicacao WHERE id IN (36, 37)";
$result_canais = $mysqli->query($sql_canais);

if ($result_canais && $result_canais->num_rows > 0) {
    echo "✅ Canais encontrados:\n";
    while ($row = $result_canais->fetch_assoc()) {
        echo "   - ID: {$row['id']} | Exibição: {$row['nome_exibicao']} | Porta: {$row['porta']} | Status: {$row['status']}\n";
    }
} else {
    echo "⚠️ Canais não encontrados\n";
}

// ===== 5. TESTAR API DE MENSAGENS =====
echo "\n5️⃣ TESTANDO API DE MENSAGENS:\n";
echo "==============================\n";

// Buscar primeiro cliente
$cliente_teste = $mysqli->query("SELECT id, nome FROM clientes ORDER BY id DESC LIMIT 1")->fetch_assoc();

if ($cliente_teste) {
    $cliente_id = $cliente_teste['id'];
    echo "🧪 Testando API com cliente: {$cliente_teste['nome']} (ID: $cliente_id)\n";
    
    $api_url = "https://app.pixel12digital.com.br/painel/api/mensagens_cliente.php?cliente_id=$cliente_id";
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $api_response = curl_exec($ch);
    $api_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $api_error = curl_error($ch);
    curl_close($ch);
    
    if ($api_error) {
        echo "❌ Erro cURL API: $api_error\n";
    } elseif ($api_http_code === 200) {
        $api_data = json_decode($api_response, true);
        if ($api_data && isset($api_data['success']) && $api_data['success']) {
            echo "✅ API funcionando corretamente\n";
            echo "📄 Mensagens encontradas: " . count($api_data['mensagens']) . "\n";
            
            if (!empty($api_data['mensagens'])) {
                echo "📋 Últimas mensagens da API:\n";
                foreach (array_slice($api_data['mensagens'], -3) as $msg) {
                    echo "   - ID: {$msg['id']} | Direção: {$msg['direcao']} | Status: {$msg['status']} | Data: {$msg['data_hora']}\n";
                    echo "     Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
                }
            }
        } else {
            echo "⚠️ API retornou erro: " . json_encode($api_data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ API não respondeu - HTTP: $api_http_code\n";
        echo "📄 Resposta: " . substr($api_response, 0, 200) . "...\n";
    }
} else {
    echo "⚠️ Nenhum cliente encontrado para teste\n";
}

// ===== 6. VERIFICAR PROBLEMAS ESPECÍFICOS =====
echo "\n6️⃣ VERIFICANDO PROBLEMAS ESPECÍFICOS:\n";
echo "=====================================\n";

// Verificar se há mensagens sem cliente_id
$sql_sem_cliente = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE cliente_id IS NULL OR cliente_id = 0";
$result_sem_cliente = $mysqli->query($sql_sem_cliente);
if ($result_sem_cliente) {
    $sem_cliente = $result_sem_cliente->fetch_assoc();
    echo "📊 Mensagens sem cliente_id: {$sem_cliente['total']}\n";
}

// Verificar se há mensagens com número vazio
$sql_sem_numero = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE numero_whatsapp IS NULL OR numero_whatsapp = ''";
$result_sem_numero = $mysqli->query($sql_sem_numero);
if ($result_sem_numero) {
    $sem_numero = $result_sem_numero->fetch_assoc();
    echo "📊 Mensagens sem número: {$sem_numero['total']}\n";
}

// Verificar se há mensagens duplicadas
$sql_duplicadas = "SELECT COUNT(*) as total FROM (
    SELECT mensagem, data_hora, COUNT(*) as count 
    FROM mensagens_comunicacao 
    GROUP BY mensagem, data_hora 
    HAVING COUNT(*) > 1
) as duplicadas";
$result_duplicadas = $mysqli->query($sql_duplicadas);
if ($result_duplicadas) {
    $duplicadas = $result_duplicadas->fetch_assoc();
    echo "📊 Mensagens duplicadas: {$duplicadas['total']}\n";
}

// ===== 7. DIAGNÓSTICO FINAL =====
echo "\n7️⃣ DIAGNÓSTICO FINAL:\n";
echo "=====================\n";

$problemas = [];

// Verificar se há mensagens
if (!$result_ultimas || $result_ultimas->num_rows === 0) {
    $problemas[] = "Nenhuma mensagem encontrada na tabela";
}

// Verificar se há clientes
if (!$result_clientes || $result_clientes->num_rows === 0) {
    $problemas[] = "Nenhum cliente encontrado";
}

// Verificar se há canais
if (!$result_canais || $result_canais->num_rows === 0) {
    $problemas[] = "Canais não encontrados";
}

// Verificar se API está funcionando
if (isset($api_http_code) && $api_http_code !== 200) {
    $problemas[] = "API de mensagens não está funcionando (HTTP: $api_http_code)";
}

if (empty($problemas)) {
    echo "✅ Sistema parece estar funcionando corretamente\n";
    echo "🔍 Se as mensagens não aparecem no chat, verificar:\n";
    echo "   1. Se o JavaScript está carregando as mensagens\n";
    echo "   2. Se há problemas de cache no navegador\n";
    echo "   3. Se há erros no console do navegador\n";
    echo "   4. Se o cliente_id está sendo passado corretamente\n";
} else {
    echo "❌ Problemas detectados:\n";
    foreach ($problemas as $problema) {
        echo "   - $problema\n";
    }
}

echo "\n🎯 RECOMENDAÇÕES:\n";
echo "1. Verificar se o webhook está salvando mensagens com cliente_id correto\n";
echo "2. Verificar se a API está retornando mensagens corretamente\n";
echo "3. Verificar se o JavaScript está carregando as mensagens\n";
echo "4. Verificar se há problemas de cache no navegador\n";
echo "5. Verificar se há erros no console do navegador\n";
?> 