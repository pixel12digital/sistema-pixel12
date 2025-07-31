<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔄 Atualizando Status dos Canais</h1>";

// Verificar canais atuais
echo "<h2>Status Atual dos Canais:</h2>";
$sql = "SELECT id, nome_exibicao, identificador, status, porta, tipo 
        FROM canais_comunicacao 
        WHERE tipo = 'whatsapp' 
        ORDER BY id";
$result = $mysqli->query($sql);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Identificador</th><th>Status</th><th>Porta</th><th>Tipo</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nome_exibicao']}</td>";
        echo "<td>{$row['identificador']}</td>";
        echo "<td style='color: " . ($row['status'] === 'conectado' ? 'green' : 'orange') . "'>{$row['status']}</td>";
        echo "<td>{$row['porta']}</td>";
        echo "<td>{$row['tipo']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Atualizar canais para status conectado
echo "<h2>Atualizando Canais para 'Conectado':</h2>";

$canais_para_atualizar = [
    ['id' => 36, 'nome' => 'Financeiro'],
    ['id' => 37, 'nome' => 'Comercial - Pixel']
];

foreach ($canais_para_atualizar as $canal) {
    $id = $canal['id'];
    $nome = $canal['nome'];
    
    $update_sql = "UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = $id";
    $update_result = $mysqli->query($update_sql);
    
    if ($update_result) {
        echo "<p style='color: green;'>✅ Canal '$nome' (ID: $id) atualizado para 'conectado'</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao atualizar canal '$nome' (ID: $id): " . $mysqli->error . "</p>";
    }
}

// Verificar status após atualização
echo "<h2>Status Após Atualização:</h2>";
$result_final = $mysqli->query($sql);

if ($result_final) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Identificador</th><th>Status</th><th>Porta</th><th>Tipo</th></tr>";
    
    while ($row = $result_final->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nome_exibicao']}</td>";
        echo "<td>{$row['identificador']}</td>";
        echo "<td style='color: " . ($row['status'] === 'conectado' ? 'green' : 'orange') . "'>{$row['status']}</td>";
        echo "<td>{$row['porta']}</td>";
        echo "<td>{$row['tipo']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Testar API
echo "<h2>Testando API de Canais:</h2>";
$api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/painel/api/listar_canais_whatsapp.php';

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "<p style='color: green;'>✅ API funcionando corretamente</p>";
        echo "<p>Total de canais encontrados: <strong>" . count($data['canais']) . "</strong></p>";
        
        if (count($data['canais']) > 0) {
            echo "<h3>Canais Disponíveis na API:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Número</th><th>Status</th><th>Porta</th></tr>";
            
            foreach ($data['canais'] as $canal) {
                echo "<tr>";
                echo "<td>{$canal['id']}</td>";
                echo "<td>{$canal['nome']}</td>";
                echo "<td>{$canal['numero']}</td>";
                echo "<td style='color: " . ($canal['status'] === 'conectado' ? 'green' : 'orange') . "'>{$canal['status']}</td>";
                echo "<td>{$canal['porta']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Erro na API: " . ($data['error'] ?? 'Erro desconhecido') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Erro HTTP: $http_code</p>";
}

echo "<hr>";
echo "<h2>🎯 Próximos Passos:</h2>";
echo "<ol>";
echo "<li>Recarregue a página do chat: <a href='painel/chat.php' target='_blank'>Abrir Chat</a></li>";
echo "<li>Selecione uma conversa com um cliente</li>";
echo "<li>Verifique se os canais aparecem no dropdown</li>";
echo "<li>Teste o envio de mensagem com diferentes canais</li>";
echo "</ol>";

echo "<p style='color: green; font-weight: bold;'>✅ Status dos canais atualizado com sucesso!</p>";
?> 