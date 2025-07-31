<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔍 Verificação de Status dos Canais</h1>";

echo "<h2>📊 Status no Banco de Dados:</h2>";

// Verificar status dos canais no banco
$canais = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY porta");

echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<thead>";
echo "<tr style='background: #f8fafc;'>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>ID</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Canal</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Porta</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Status DB</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Identificador</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($canal = $canais->fetch_assoc()) {
    $status_color = $canal['status'] === 'conectado' ? '#22c55e' : '#f59e0b';
    
    echo "<tr>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$canal['id']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600;'>{$canal['nome_exibicao']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$canal['porta']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; color: $status_color; font-weight: 600;'>{$canal['status']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$canal['identificador']}</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";

echo "<h2>🌐 Teste da API de Canais:</h2>";

// Testar a API que o chat usa
$api_url = 'http://localhost/loja-virtual-revenda/painel/api/listar_canais_whatsapp.php';

echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<h3>URL da API: $api_url</h3>";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo "<p style='color: #ef4444;'>❌ Erro de conexão: $curl_error</p>";
} else {
    echo "<p style='color: #22c55e;'>✅ Conexão estabelecida (HTTP: $http_code)</p>";
    echo "<h4>Resposta da API:</h4>";
    echo "<pre style='background: #f8fafc; padding: 1rem; border-radius: 4px; overflow-x: auto;'>" . htmlspecialchars($response) . "</pre>";
    
    // Decodificar JSON para análise
    $data = json_decode($response, true);
    if ($data && isset($data['canais'])) {
        echo "<h4>Análise dos Canais:</h4>";
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 1rem;'>";
        echo "<thead>";
        echo "<tr style='background: #f8fafc;'>";
        echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Canal</th>";
        echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Status</th>";
        echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Número</th>";
        echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Porta</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($data['canais'] as $canal) {
            $status_color = $canal['status'] === 'conectado' ? '#22c55e' : '#f59e0b';
            echo "<tr>";
            echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600;'>{$canal['nome']}</td>";
            echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; color: $status_color; font-weight: 600;'>{$canal['status']}</td>";
            echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$canal['numero']}</td>";
            echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$canal['porta']}</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    }
}

echo "</div>";

echo "<h2>🔧 Possíveis Soluções:</h2>";

echo "<div style='background: #f0f9ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<h3>Se os canais estão 'pendente' mas deveriam estar 'conectado':</h3>";
echo "<ol>";
echo "<li><strong>Verificar se os canais estão realmente conectados:</strong></li>";
echo "<ul>";
echo "<li>Testar conectividade: <code>curl http://212.85.11.238:3000/status</code></li>";
echo "<li>Testar conectividade: <code>curl http://212.85.11.238:3001/status</code></li>";
echo "</ul>";
echo "<li><strong>Atualizar status no banco:</strong></li>";
echo "<ul>";
echo "<li>Executar: <code>php atualizar_status_canais.php</code></li>";
echo "</ul>";
echo "<li><strong>Limpar cache do navegador:</strong></li>";
echo "<ul>";
echo "<li>Recarregar a página com Ctrl+F5</li>";
echo "<li>Verificar se não há cache do JavaScript</li>";
echo "</ul>";
echo "</ol>";
echo "</div>";

echo "<h2>🎯 Próximos Passos:</h2>";
echo "<ol>";
echo "<li>Verifique se os canais estão realmente conectados nas portas 3000 e 3001</li>";
echo "<li>Se estiverem conectados mas aparecerem como 'pendente', atualize o status no banco</li>";
echo "<li>Teste a API diretamente para ver se retorna o status correto</li>";
echo "<li>Verifique se há algum cache interferindo na exibição</li>";
echo "</ol>";

echo "<p style='color: green; font-weight: bold;'>✅ Verificação completa!</p>";
?> 