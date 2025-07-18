<?php
/**
 * Verificação de Dados de Cobranças
 */

require_once 'config.php';

echo "<h1>🔍 Verificação de Dados de Cobranças</h1>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Erro ao conectar ao banco de dados: ' . $conn->connect_error);
    }

    echo "<h2>📊 Estatísticas das Tabelas</h2>";
    
    // Verificar tabela cobrancas
    $result = $conn->query("SELECT COUNT(*) as total FROM cobrancas");
    $totalCobrancas = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM clientes");
    $totalClientes = $result->fetch_assoc()['total'];
    
    echo "<div style='background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;border-radius:8px;margin-bottom:20px;'>";
    echo "<p><strong>Total de Cobranças:</strong> $totalCobrancas</p>";
    echo "<p><strong>Total de Clientes:</strong> $totalClientes</p>";
    echo "</div>";

    if ($totalCobrancas == 0) {
        echo "<div style='background:#fef2f2;border:1px solid #fecaca;padding:15px;border-radius:8px;margin-bottom:20px;'>";
        echo "<h3>⚠️ Nenhuma cobrança encontrada!</h3>";
        echo "<p>Isso explica por que a página de faturas está vazia.</p>";
        echo "</div>";
    }

    // Verificar estrutura da tabela cobrancas
    echo "<h2>🏗️ Estrutura da Tabela Cobranças</h2>";
    $result = $conn->query("DESCRIBE cobrancas");
    echo "<table style='width:100%;border-collapse:collapse;margin-bottom:20px;'>";
    echo "<tr style='background:#f3f4f6;'><th style='padding:10px;border:1px solid #d1d5db;text-align:left;'>Campo</th><th style='padding:10px;border:1px solid #d1d5db;text-align:left;'>Tipo</th><th style='padding:10px;border:1px solid #d1d5db;text-align:left;'>Null</th><th style='padding:10px;border:1px solid #d1d5db;text-align:left;'>Key</th><th style='padding:10px;border:1px solid #d1d5db;text-align:left;'>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['Field'] . "</td>";
        echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['Type'] . "</td>";
        echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['Null'] . "</td>";
        echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['Key'] . "</td>";
        echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Testar a query do api/cobrancas.php
    echo "<h2>🧪 Teste da Query do API</h2>";
    
    $sql = "SELECT c.*, cli.nome AS cliente_nome, cli.email AS cliente_email, cli.contact_name AS cliente_contact_name,
      (SELECT MAX(data_hora) FROM mensagens_comunicacao m WHERE m.cobranca_id = c.id AND m.direcao = 'enviado') AS ultima_interacao,
      (
        SELECT status FROM mensagens_comunicacao m2 
        WHERE m2.cobranca_id = c.id AND m2.direcao = 'enviado' 
        ORDER BY data_hora DESC LIMIT 1
      ) AS whatsapp_status,
      (
        SELECT motivo_erro FROM mensagens_comunicacao m3 
        WHERE m3.cobranca_id = c.id AND m3.direcao = 'enviado' 
        ORDER BY data_hora DESC LIMIT 1
      ) AS whatsapp_motivo_erro,
      (
        SELECT id FROM mensagens_comunicacao m4
        WHERE m4.cobranca_id = c.id AND m4.direcao = 'enviado'
        ORDER BY data_hora DESC LIMIT 1
      ) AS whatsapp_msg_id
      FROM cobrancas c
      LEFT JOIN clientes cli ON c.cliente_id = cli.id
      ORDER BY c.vencimento ASC
      LIMIT 5";

    $result = $conn->query($sql);
    
    if ($result === false) {
        echo "<div style='background:#fef2f2;border:1px solid #fecaca;padding:15px;border-radius:8px;'>";
        echo "<h3>❌ Erro na Query</h3>";
        echo "<p><strong>Erro:</strong> " . $conn->error . "</p>";
        echo "</div>";
    } else {
        $cobrancas = [];
        while ($row = $result->fetch_assoc()) {
            $cobrancas[] = $row;
        }
        
        echo "<div style='background:#f0fdf4;border:1px solid #bbf7d0;padding:15px;border-radius:8px;'>";
        echo "<h3>✅ Query executada com sucesso</h3>";
        echo "<p><strong>Resultados encontrados:</strong> " . count($cobrancas) . "</p>";
        echo "</div>";
        
        if (count($cobrancas) > 0) {
            echo "<h3>📋 Primeiros 5 resultados:</h3>";
            echo "<table style='width:100%;border-collapse:collapse;'>";
            echo "<tr style='background:#f3f4f6;'>";
            echo "<th style='padding:8px;border:1px solid #d1d5db;'>ID</th>";
            echo "<th style='padding:8px;border:1px solid #d1d5db;'>Cliente</th>";
            echo "<th style='padding:8px;border:1px solid #d1d5db;'>Valor</th>";
            echo "<th style='padding:8px;border:1px solid #d1d5db;'>Vencimento</th>";
            echo "<th style='padding:8px;border:1px solid #d1d5db;'>Status</th>";
            echo "</tr>";
            
            foreach ($cobrancas as $cob) {
                echo "<tr>";
                echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $cob['id'] . "</td>";
                echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . ($cob['cliente_nome'] ?: 'N/A') . "</td>";
                echo "<td style='padding:8px;border:1px solid #d1d5db;'>R$ " . number_format($cob['valor'], 2, ',', '.') . "</td>";
                echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $cob['vencimento'] . "</td>";
                echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $cob['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    // Testar endpoint da API
    echo "<h2>🌐 Teste do Endpoint da API</h2>";
    
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/loja-virtual-revenda/api/cobrancas.php';
    echo "<p><strong>URL da API:</strong> <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($apiUrl, false, $context);
    $httpCode = $http_response_header[0] ?? 'Unknown';
    
    echo "<div style='background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;border-radius:8px;'>";
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
    echo "<p><strong>Resposta:</strong></p>";
    echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;max-height:200px;overflow:auto;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
    echo "</div>";

    // Verificar se há dados de sincronização
    echo "<h2>🔄 Dados de Sincronização</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM mensagens_comunicacao");
    $totalMensagens = $result->fetch_assoc()['total'];
    
    echo "<p><strong>Total de mensagens de comunicação:</strong> $totalMensagens</p>";
    
    if ($totalMensagens > 0) {
        $result = $conn->query("SELECT * FROM mensagens_comunicacao ORDER BY data_hora DESC LIMIT 3");
        echo "<h3>Últimas mensagens:</h3>";
        echo "<table style='width:100%;border-collapse:collapse;'>";
        echo "<tr style='background:#f3f4f6;'>";
        echo "<th style='padding:8px;border:1px solid #d1d5db;'>Data/Hora</th>";
        echo "<th style='padding:8px;border:1px solid #d1d5db;'>Cliente ID</th>";
        echo "<th style='padding:8px;border:1px solid #d1d5db;'>Direção</th>";
        echo "<th style='padding:8px;border:1px solid #d1d5db;'>Status</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['data_hora'] . "</td>";
            echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['cliente_id'] . "</td>";
            echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['direcao'] . "</td>";
            echo "<td style='padding:8px;border:1px solid #d1d5db;'>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    $conn->close();

} catch (Exception $e) {
    echo "<div style='background:#fef2f2;border:1px solid #fecaca;padding:15px;border-radius:8px;'>";
    echo "<h3>❌ Erro</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h2>📋 Próximos Passos</h2>";
echo "<div style='background:#f0f9ff;border:1px solid #0ea5e9;padding:15px;border-radius:8px;'>";
echo "<ol>";
echo "<li>Se não há cobranças, execute a sincronização com Asaas</li>";
echo "<li>Se há erro na query, verifique a estrutura das tabelas</li>";
echo "<li>Se a API não responde, verifique permissões de arquivo</li>";
echo "<li>Teste a sincronização clicando em '🔄 Sincronizar com Asaas'</li>";
echo "</ol>";
echo "</div>";
?> 