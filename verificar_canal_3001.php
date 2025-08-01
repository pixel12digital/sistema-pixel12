<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação Específica - Canal 3001</h1>";
echo "<p>Verificando se o canal 3001 está conectado com o número 4797309525...</p>";

// Configurações específicas para porta 3001
$vps_url = 'http://212.85.11.238:3001';
$sessionName = 'comercial';
$numero_esperado = '4797309525';

echo "<h2>📋 Configurações</h2>";
echo "<p><strong>URL VPS:</strong> $vps_url</p>";
echo "<p><strong>Sessão:</strong> $sessionName</p>";
echo "<p><strong>Número Esperado:</strong> $numero_esperado</p>";

// Teste 1: Status da sessão
echo "<h2>📊 Teste 1: Status da Sessão</h2>";
$endpoint = "/session/{$sessionName}/status";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $http_code</p>";
echo "<p><strong>Curl Error:</strong> " . ($curl_error ?: 'Nenhum') . "</p>";
echo "<p><strong>Resposta Completa:</strong> <pre>" . htmlspecialchars($response) . "</pre></p>";

if ($http_code == 200 && !empty($response)) {
    $data = json_decode($response, true);
    if ($data && isset($data['status']['status'])) {
        $vps_status = $data['status']['status'];
        $number = $data['status']['number'] ?? null;
        
        // Aplicar a mesma lógica do ajax_whatsapp.php
        $is_ready = false;
        $status_message = 'Desconectado';
        
        if (in_array($vps_status, ['connected', 'ready', 'authenticated', 'already_connected'])) {
            $is_ready = true;
            $status_message = 'Conectado';
        } elseif ($vps_status === 'connecting') {
            $is_ready = false;
            $status_message = 'Conectando...';
        } elseif ($vps_status === 'disconnected' || $vps_status === 'not_found') {
            $is_ready = false;
            $status_message = 'Desconectado';
        } else {
            if (isset($data['status']['number']) && !empty($data['status']['number'])) {
                $is_ready = true;
                $status_message = 'Conectado (por número)';
            } else {
                $is_ready = false;
                $status_message = 'Status desconhecido: ' . $vps_status;
            }
        }
        
        echo "<h3>📱 Resultado da Verificação</h3>";
        echo "<p><strong>Status VPS:</strong> <span style='color: " . ($is_ready ? 'green' : 'red') . "; font-weight: bold;'>$vps_status</span></p>";
        echo "<p><strong>Status Interpretado:</strong> <span style='color: " . ($is_ready ? 'green' : 'red') . "; font-weight: bold;'>$status_message</span></p>";
        echo "<p><strong>Conectado:</strong> " . ($is_ready ? '✅ Sim' : '❌ Não') . "</p>";
        echo "<p><strong>Número Atual:</strong> " . ($number ?: 'Não informado') . "</p>";
        
        // Verificar se o número corresponde ao esperado
        if ($number && $number === $numero_esperado) {
            echo "<p><strong>✅ Número Correto:</strong> Sim - corresponde ao esperado ($numero_esperado)</p>";
        } elseif ($number) {
            echo "<p><strong>⚠️ Número Diferente:</strong> $number (esperado: $numero_esperado)</p>";
        } else {
            echo "<p><strong>❌ Número Ausente:</strong> Não há número informado</p>";
        }
        
        // Verificar se tem número
        if ($number && !empty($number)) {
            echo "<p><strong>✅ Tem número:</strong> Sim - indica conexão ativa</p>";
        } else {
            echo "<p><strong>❌ Tem número:</strong> Não</p>";
        }
        
        // Resumo final
        echo "<h3>🎯 Resumo Final</h3>";
        if ($is_ready && $number === $numero_esperado) {
            echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>✅ CONFIRMADO: Canal 3001 conectado com número 4797309525</p>";
        } elseif ($is_ready) {
            echo "<p style='color: orange; font-weight: bold; font-size: 1.2em;'>⚠️ PARCIAL: Canal 3001 conectado, mas número diferente ($number)</p>";
        } else {
            echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'>❌ NEGADO: Canal 3001 não está conectado</p>";
        }
        
    } else {
        echo "<p><strong>Status:</strong> <span style='color: orange;'>Sessão não encontrada ou erro no parse</span></p>";
        echo "<p><strong>Erro de Parse:</strong> " . json_last_error_msg() . "</p>";
        echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'>❌ NEGADO: Sessão não encontrada</p>";
    }
} else {
    echo "<p><strong>Erro:</strong> <span style='color: red;'>VPS não respondeu</span></p>";
    if ($curl_error) {
        echo "<p><strong>Erro de Conexão:</strong> $curl_error</p>";
    }
    echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'>❌ NEGADO: VPS não respondeu</p>";
}

// Teste 2: Verificar se há outras sessões ativas
echo "<h2>🔍 Teste 2: Verificar Outras Sessões</h2>";
$test_endpoint = "/status";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $vps_url . $test_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Status Geral VPS:</strong> <pre>" . htmlspecialchars($response) . "</pre></p>";

echo "<h2>📋 Conclusão</h2>";
echo "<p>Este teste confirma se o canal 3001 está realmente conectado com o número 4797309525.</p>";
echo "<p><a href='painel/comunicacao.php'>← Voltar para a interface de comunicação</a></p>";
?> 