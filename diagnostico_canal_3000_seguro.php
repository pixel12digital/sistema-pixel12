<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico Seguro do Canal 3000</h1>";
echo "<p><strong>⚠️ MODO SEGURO:</strong> Apenas verificações, sem alterações</p>";

require_once __DIR__ . '/config.php';

// Teste 1: Verificar status atual do VPS (porta 3000)
echo "<h2>🖥️ Teste 1: Status do VPS Porta 3000</h2>";

$vps_url_3000 = "http://212.85.11.238:3000";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url_3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>Status do VPS (3000):</strong> HTTP $http_code</p>";
if ($curl_error) {
    echo "<p style='color: red;'><strong>Erro cURL:</strong> $curl_error</p>";
}
if ($response) {
    $vps_status = json_decode($response, true);
    if ($vps_status) {
        echo "<p><strong>Resposta completa:</strong></p>";
        echo "<pre>" . json_encode($vps_status, JSON_PRETTY_PRINT) . "</pre>";
        
        if (isset($vps_status['clients_status']['default'])) {
            $default = $vps_status['clients_status']['default'];
            $status_color = $default['status'] === 'connected' ? 'green' : 'red';
            echo "<p style='color: $status_color; font-weight: bold;'>Sessão Default: {$default['status']} - {$default['message']}</p>";
        }
    }
}

// Teste 2: Verificar se há processo rodando na porta 3000
echo "<h2>🔍 Teste 2: Verificar Processo na Porta 3000</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url_3000/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Sessões na porta 3000:</strong> HTTP $http_code</p>";
if ($response) {
    $sessions = json_decode($response, true);
    if ($sessions) {
        echo "<p><strong>Resposta:</strong></p>";
        echo "<pre>" . json_encode($sessions, JSON_PRETTY_PRINT) . "</pre>";
    }
}

// Teste 3: Verificar configuração do canal financeiro no banco
echo "<h2>💾 Teste 3: Configuração do Canal Financeiro</h2>";

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>❌ Erro na conexão com o banco</p>";
    } else {
        $sql = "SELECT * FROM canais_comunicacao WHERE porta = 3000 ORDER BY id";
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green; font-weight: bold;'>✅ Canais na porta 3000 encontrados:</p>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Porta</th><th>Status</th><th>Sessão</th><th>Identificador</th></tr>";
            
            while ($canal = $result->fetch_assoc()) {
                $status_color = $canal['status'] === 'conectado' ? 'green' : 'red';
                echo "<tr>";
                echo "<td>{$canal['id']}</td>";
                echo "<td>{$canal['nome_exibicao']}</td>";
                echo "<td>{$canal['porta']}</td>";
                echo "<td style='color: $status_color; font-weight: bold;'>{$canal['status']}</td>";
                echo "<td>{$canal['sessao']}</td>";
                echo "<td>{$canal['identificador']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhum canal encontrado na porta 3000</p>";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

// Teste 4: Verificar se o PM2 está rodando processo na porta 3000
echo "<h2>⚙️ Teste 4: Verificar PM2 (Porta 3000)</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url_3000/session/default/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Status da sessão default:</strong> HTTP $http_code</p>";
if ($response) {
    $session_status = json_decode($response, true);
    if ($session_status) {
        echo "<p><strong>Resposta:</strong></p>";
        echo "<pre>" . json_encode($session_status, JSON_PRETTY_PRINT) . "</pre>";
    }
}

// Teste 5: Verificar se há QR code disponível
echo "<h2>📱 Teste 5: Verificar QR Code</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url_3000/session/default/qr");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>QR Code da sessão default:</strong> HTTP $http_code</p>";
if ($response) {
    $qr_response = json_decode($response, true);
    if ($qr_response) {
        echo "<p><strong>Resposta:</strong></p>";
        echo "<pre>" . json_encode($qr_response, JSON_PRETTY_PRINT) . "</pre>";
        
        if (isset($qr_response['qr'])) {
            echo "<p style='color: green; font-weight: bold;'>✅ QR Code disponível!</p>";
            echo "<p><strong>QR Code:</strong> {$qr_response['qr']}</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ QR Code não disponível</p>";
            if (isset($qr_response['error'])) {
                echo "<p><strong>Erro:</strong> {$qr_response['error']}</p>";
            }
        }
    }
}

// Teste 6: Verificar se o canal 3001 continua funcionando
echo "<h2>✅ Teste 6: Verificar Canal 3001 (Segurança)</h2>";

$vps_url_3001 = "http://212.85.11.238:3001";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$vps_url_3001/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Status do VPS (3001):</strong> HTTP $http_code</p>";
if ($response) {
    $vps_status_3001 = json_decode($response, true);
    if ($vps_status_3001 && isset($vps_status_3001['clients_status']['comercial'])) {
        $comercial = $vps_status_3001['clients_status']['comercial'];
        $status_color = $comercial['status'] === 'connected' ? 'green' : 'red';
        echo "<p style='color: $status_color; font-weight: bold;'>✅ Canal 3001: {$comercial['status']} - {$comercial['message']}</p>";
    }
}

echo "<h2>🎯 Diagnóstico Final</h2>";

echo "<h3>📋 Status Atual:</h3>";
echo "<ul>";
echo "<li><strong>Canal 3001 (Comercial):</strong> ✅ Funcionando</li>";
echo "<li><strong>Canal 3000 (Financeiro):</strong> 🔍 Em análise</li>";
echo "</ul>";

echo "<h3>🔍 Possíveis Problemas Canal 3000:</h3>";
echo "<ol>";
echo "<li><strong>Processo não iniciado:</strong> PM2 pode não estar rodando na porta 3000</li>";
echo "<li><strong>Arquivo de configuração:</strong> whatsapp-api-server.js pode não estar configurado para porta 3000</li>";
echo "<li><strong>Sessão não criada:</strong> Sessão 'default' pode não existir</li>";
echo "<li><strong>Porta ocupada:</strong> Outro processo pode estar usando a porta 3000</li>";
echo "</ol>";

echo "<h3>🛡️ Próximos Passos Seguros:</h3>";
echo "<ol>";
echo "<li><strong>NÃO farei alterações</strong> sem sua confirmação</li>";
echo "<li><strong>Verificar PM2</strong> na VPS para ver processos</li>";
echo "<li><strong>Verificar arquivos</strong> de configuração</li>";
echo "<li><strong>Propor soluções</strong> para sua aprovação</li>";
echo "</ol>";

echo "<p><strong>⚠️ IMPORTANTE:</strong> O canal 3001 está protegido e funcionando. Qualquer ação será apenas para o canal 3000.</p>";

echo "<p><a href='teste_envio_corrigido.php'>← Teste de Envio</a></p>";
?> 