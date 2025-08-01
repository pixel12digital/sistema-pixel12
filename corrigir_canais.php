<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correção de Canais</h1>";
echo "<p>Verificando e corrigindo a configuração dos canais...</p>";

require_once __DIR__ . '/config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>❌ Erro na conexão com o banco</p>";
        exit;
    }
    
    // Teste 1: Verificar estrutura da tabela
    echo "<h2>📋 Teste 1: Estrutura da Tabela Canais</h2>";
    
    $sql_estrutura = "DESCRIBE canais_comunicacao";
    $result_estrutura = $mysqli->query($sql_estrutura);
    
    if ($result_estrutura) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        while ($row = $result_estrutura->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Teste 2: Verificar todos os canais
    echo "<h2>📱 Teste 2: Todos os Canais</h2>";
    
    $sql_canais = "SELECT * FROM canais_comunicacao ORDER BY id";
    $result_canais = $mysqli->query($sql_canais);
    
    if ($result_canais && $result_canais->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Porta</th><th>Status</th><th>Ativo</th><th>Última Atualização</th></tr>";
        
        while ($canal = $result_canais->fetch_assoc()) {
            $status_color = $canal['status'] === 'conectado' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$canal['id']}</td>";
            echo "<td>{$canal['nome']}</td>";
            echo "<td>{$canal['porta']}</td>";
            echo "<td style='color: $status_color;'>{$canal['status']}</td>";
            echo "<td>" . (isset($canal['ativo']) ? ($canal['ativo'] ? 'Sim' : 'Não') : 'N/A') . "</td>";
            echo "<td>{$canal['ultima_atualizacao']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum canal encontrado</p>";
    }
    
    // Teste 3: Verificar status do VPS
    echo "<h2>🖥️ Teste 3: Status do VPS</h2>";
    
    $vps_url = "http://212.85.11.238:3001";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$vps_url/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>Status do VPS:</strong> HTTP $http_code</p>";
    if ($response) {
        $vps_status = json_decode($response, true);
        if ($vps_status && isset($vps_status['clients_status'])) {
            echo "<h3>Sessões no VPS:</h3>";
            foreach ($vps_status['clients_status'] as $session => $status) {
                $status_color = $status['status'] === 'connected' ? 'green' : 'red';
                echo "<p style='color: $status_color;'><strong>$session:</strong> {$status['status']} - {$status['message']}</p>";
            }
        }
    }
    
    // Teste 4: Atualizar status dos canais baseado no VPS
    echo "<h2>🔄 Teste 4: Atualizar Status dos Canais</h2>";
    
    if ($response && $vps_status) {
        // Atualizar canal Comercial (ID 2) se estiver conectado no VPS
        if (isset($vps_status['clients_status']['comercial']) && 
            $vps_status['clients_status']['comercial']['status'] === 'connected') {
            
            $sql_update = "UPDATE canais_comunicacao SET 
                          status = 'conectado', 
                          ultima_atualizacao = NOW() 
                          WHERE id = 2";
            
            if ($mysqli->query($sql_update)) {
                echo "<p style='color: green;'>✅ Canal Comercial (ID 2) atualizado para conectado</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao atualizar canal: " . $mysqli->error . "</p>";
            }
        }
        
        // Atualizar canal Financeiro (ID 1) se estiver conectado no VPS
        if (isset($vps_status['clients_status']['default']) && 
            $vps_status['clients_status']['default']['status'] === 'connected') {
            
            $sql_update = "UPDATE canais_comunicacao SET 
                          status = 'conectado', 
                          ultima_atualizacao = NOW() 
                          WHERE id = 1";
            
            if ($mysqli->query($sql_update)) {
                echo "<p style='color: green;'>✅ Canal Financeiro (ID 1) atualizado para conectado</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao atualizar canal: " . $mysqli->error . "</p>";
            }
        }
    }
    
    // Teste 5: Verificar se o problema foi resolvido
    echo "<h2>✅ Teste 5: Verificar Correção</h2>";
    
    $sql_verificar = "SELECT * FROM canais_comunicacao WHERE id IN (1, 2) ORDER BY id";
    $result_verificar = $mysqli->query($sql_verificar);
    
    if ($result_verificar && $result_verificar->num_rows > 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Canais após correção:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Porta</th><th>Status</th><th>Última Atualização</th></tr>";
        
        while ($canal = $result_verificar->fetch_assoc()) {
            $status_color = $canal['status'] === 'conectado' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$canal['id']}</td>";
            echo "<td>{$canal['nome']}</td>";
            echo "<td>{$canal['porta']}</td>";
            echo "<td style='color: $status_color; font-weight: bold;'>{$canal['status']}</td>";
            echo "<td>{$canal['ultima_atualizacao']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Teste 6: Testar envio novamente
    echo "<h2>📤 Teste 6: Testar Envio Novamente</h2>";
    
    $test_data = [
        'cliente_id' => '4296',
        'mensagem' => 'Teste após correção dos canais - ' . date('Y-m-d H:i:s'),
        'canal_id' => '2'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://pixel12digital.com.br/app/painel/chat_enviar.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: Test-Script'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>Resposta HTTP:</strong> $http_code</p>";
    if ($response) {
        $json_response = json_decode($response, true);
        if ($json_response) {
            if (isset($json_response['success']) && $json_response['success']) {
                echo "<p style='color: green; font-weight: bold;'>✅ Envio funcionando após correção!</p>";
                echo "<p><strong>Mensagem ID:</strong> " . ($json_response['mensagem_id'] ?? 'N/A') . "</p>";
                echo "<p><strong>Enviado via API:</strong> " . ($json_response['enviado_api'] ? 'Sim' : 'Não') . "</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ Ainda há erro no envio</p>";
                echo "<p><strong>Erro:</strong> " . ($json_response['error'] ?? 'Erro desconhecido') . "</p>";
            }
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Conclusão</h2>";

echo "<h3>✅ O que foi feito:</h3>";
echo "<ul>";
echo "<li>Verificada estrutura da tabela de canais</li>";
echo "<li>Verificado status atual dos canais</li>";
echo "<li>Verificado status do VPS</li>";
echo "<li>Atualizado status dos canais baseado no VPS</li>";
echo "<li>Testado envio após correção</li>";
echo "</ul>";

echo "<h3>🔧 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Teste o envio de mensagens no chat agora</li>";
echo "<li>Se ainda não funcionar, verifique o console do navegador (F12)</li>";
echo "<li>Verifique se o canal está selecionado no formulário</li>";
echo "</ol>";

echo "<p><a href='teste_envio_chat.php'>← Teste de Envio</a></p>";
?> 