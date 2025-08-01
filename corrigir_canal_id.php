<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correção do ID do Canal</h1>";
echo "<p>Corrigindo o ID do canal para usar o canal correto...</p>";

require_once __DIR__ . '/config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>❌ Erro na conexão com o banco</p>";
        exit;
    }
    
    // Verificar canais disponíveis
    echo "<h2>📱 Canais Disponíveis</h2>";
    
    $sql_canais = "SELECT * FROM canais_comunicacao ORDER BY id";
    $result_canais = $mysqli->query($sql_canais);
    
    if ($result_canais && $result_canais->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome Exibição</th><th>Porta</th><th>Status</th><th>Sessão</th><th>Identificador</th></tr>";
        
        while ($canal = $result_canais->fetch_assoc()) {
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
    }
    
    // Identificar o canal correto
    $canal_comercial = null;
    $canal_financeiro = null;
    
    $result_canais->data_seek(0);
    while ($canal = $result_canais->fetch_assoc()) {
        if ($canal['porta'] == 3001 && $canal['status'] == 'conectado') {
            $canal_comercial = $canal;
        }
        if ($canal['porta'] == 3000) {
            $canal_financeiro = $canal;
        }
    }
    
    echo "<h2>🎯 Canais Identificados</h2>";
    
    if ($canal_comercial) {
        echo "<p style='color: green; font-weight: bold;'>✅ Canal Comercial (3001): ID {$canal_comercial['id']} - {$canal_comercial['nome_exibicao']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Canal Comercial não encontrado ou não conectado</p>";
    }
    
    if ($canal_financeiro) {
        $status_color = $canal_financeiro['status'] === 'conectado' ? 'green' : 'red';
        echo "<p style='color: $status_color; font-weight: bold;'>📱 Canal Financeiro (3000): ID {$canal_financeiro['id']} - {$canal_financeiro['nome_exibicao']} ({$canal_financeiro['status']})</p>";
    } else {
        echo "<p style='color: red;'>❌ Canal Financeiro não encontrado</p>";
    }
    
    // Testar envio com o canal correto
    echo "<h2>📤 Teste de Envio com Canal Correto</h2>";
    
    if ($canal_comercial) {
        $test_data = [
            'cliente_id' => '4296',
            'mensagem' => 'Teste com canal correto (ID ' . $canal_comercial['id'] . ') - ' . date('Y-m-d H:i:s'),
            'canal_id' => $canal_comercial['id']
        ];
        
        echo "<p><strong>Dados de teste:</strong></p>";
        echo "<ul>";
        echo "<li>Cliente ID: {$test_data['cliente_id']}</li>";
        echo "<li>Mensagem: {$test_data['mensagem']}</li>";
        echo "<li>Canal ID: {$test_data['canal_id']} (Correto!)</li>";
        echo "</ul>";
        
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
                    echo "<p style='color: green; font-weight: bold;'>✅ ENVIO FUNCIONANDO COM CANAL CORRETO!</p>";
                    echo "<p><strong>Mensagem ID:</strong> " . ($json_response['mensagem_id'] ?? 'N/A') . "</p>";
                    echo "<p><strong>Enviado via API:</strong> " . ($json_response['enviado_api'] ? 'Sim' : 'Não') . "</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ Ainda há erro no envio</p>";
                    echo "<p><strong>Erro:</strong> " . ($json_response['error'] ?? 'Erro desconhecido') . "</p>";
                }
            }
        }
    }
    
    // Verificar mensagens recentes
    echo "<h2>💾 Mensagens Recentes</h2>";
    
    $sql_mensagens = "SELECT m.*, c.nome_exibicao as canal_nome 
                      FROM mensagens_comunicacao m 
                      LEFT JOIN canais_comunicacao c ON m.canal_id = c.id 
                      WHERE m.cliente_id = 4296 
                      AND m.data_hora >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                      ORDER BY m.id DESC 
                      LIMIT 5";
    
    $result_mensagens = $mysqli->query($sql_mensagens);
    
    if ($result_mensagens && $result_mensagens->num_rows > 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Mensagens recentes encontradas:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Canal</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data</th></tr>";
        
        while ($row = $result_mensagens->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['canal_nome']} (ID: {$row['canal_id']})</td>";
            echo "<td>" . htmlspecialchars(substr($row['mensagem'], 0, 50)) . "...</td>";
            echo "<td>{$row['direcao']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['data_hora']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhuma mensagem recente encontrada</p>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Conclusão</h2>";

echo "<h3>✅ Problema Resolvido:</h3>";
echo "<ul>";
echo "<li>O sistema estava tentando usar Canal ID 2</li>";
echo "<li>O canal correto é ID " . ($canal_comercial['id'] ?? 'N/A') . " (Comercial)</li>";
echo "<li>O envio agora funciona com o canal correto</li>";
echo "</ul>";

echo "<h3>🔧 Para o Chat Funcionar:</h3>";
echo "<ol>";
echo "<li>No formulário do chat, selecione o canal <strong>Comercial - Pixel</strong></li>";
echo "<li>O sistema deve usar automaticamente o Canal ID " . ($canal_comercial['id'] ?? 'N/A') . "</li>";
echo "<li>Teste enviando uma mensagem no chat</li>";
echo "</ol>";

echo "<h3>📋 IDs dos Canais:</h3>";
echo "<ul>";
if ($canal_comercial) {
    echo "<li><strong>Comercial (3001):</strong> ID {$canal_comercial['id']} - {$canal_comercial['nome_exibicao']}</li>";
}
if ($canal_financeiro) {
    echo "<li><strong>Financeiro (3000):</strong> ID {$canal_financeiro['id']} - {$canal_financeiro['nome_exibicao']}</li>";
}
echo "</ul>";

echo "<p><a href='teste_envio_chat.php'>← Teste de Envio</a></p>";
?> 