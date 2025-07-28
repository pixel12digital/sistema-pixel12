<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/painel/db.php';

echo "<h2>📋 MENSAGEM AGENDADA - CLIENTE EDUARDO</h2>";

try {
    // Buscar mensagem agendada para o cliente Eduardo (ID 274)
    $sql = "SELECT 
                ma.*,
                c.nome as cliente_nome,
                c.celular,
                c.contact_name
            FROM mensagens_agendadas ma
            JOIN clientes c ON ma.cliente_id = c.id
            WHERE ma.cliente_id = 274
            ORDER BY ma.data_agendada ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        echo "<p style='color: red;'>❌ Erro na consulta: " . $mysqli->error . "</p>";
        exit;
    }
    
    if ($result->num_rows === 0) {
        echo "<p style='color: orange;'>⚠️ Nenhuma mensagem agendada encontrada para o cliente Eduardo (ID: 274)</p>";
        
        // Vamos verificar se existem mensagens agendadas para outros clientes
        $sql_all = "SELECT COUNT(*) as total FROM mensagens_agendadas";
        $result_all = $mysqli->query($sql_all);
        if ($result_all) {
            $total = $result_all->fetch_assoc()['total'];
            echo "<p>Total de mensagens agendadas no sistema: $total</p>";
        }
        exit;
    }
    
    echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<h3>📅 DETALHES DO AGENDAMENTO</h3>";
        echo "<p><strong>Cliente:</strong> " . htmlspecialchars($row['cliente_nome']) . "</p>";
        echo "<p><strong>Celular:</strong> " . htmlspecialchars($row['celular']) . "</p>";
        echo "<p><strong>Contact Name:</strong> " . htmlspecialchars($row['contact_name']) . "</p>";
        echo "<p><strong>Data Agendada:</strong> " . date('d/m/Y H:i:s', strtotime($row['data_agendada'])) . "</p>";
        echo "<p><strong>Tipo:</strong> " . ucfirst($row['tipo']) . "</p>";
        echo "<p><strong>Prioridade:</strong> " . ucfirst($row['prioridade']) . "</p>";
        echo "<p><strong>Status:</strong> " . ucfirst($row['status']) . "</p>";
        
        echo "<h3>💬 TEOR DA MENSAGEM</h3>";
        echo "<div style='background: white; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; white-space: pre-wrap;'>";
        echo htmlspecialchars($row['mensagem']);
        echo "</div>";
        
        echo "<h3>📊 ANÁLISE DA MENSAGEM</h3>";
        $mensagem = $row['mensagem'];
        $linhas = explode("\n", $mensagem);
        echo "<p><strong>Total de linhas:</strong> " . count($linhas) . "</p>";
        echo "<p><strong>Total de caracteres:</strong> " . strlen($mensagem) . "</p>";
        
        // Contar faturas mencionadas
        $faturas_count = substr_count($mensagem, 'Fatura #');
        echo "<p><strong>Faturas mencionadas:</strong> " . $faturas_count . "</p>";
        
        // Verificar se tem link de pagamento
        if (strpos($mensagem, 'Link para pagamento:') !== false) {
            echo "<p><strong>✅ Link de pagamento:</strong> Incluído</p>";
        } else {
            echo "<p><strong>❌ Link de pagamento:</strong> Não encontrado</p>";
        }
        
        // Verificar se tem valor total
        if (strpos($mensagem, 'Valor total em aberto:') !== false) {
            echo "<p><strong>✅ Valor total:</strong> Incluído</p>";
        } else {
            echo "<p><strong>❌ Valor total:</strong> Não encontrado</p>";
        }
        
        // Verificar se tem observação
        if (!empty($row['observacao'])) {
            echo "<p><strong>Observação:</strong> " . htmlspecialchars($row['observacao']) . "</p>";
        }
    }
    
    echo "</div>";
    
    // Mostrar também as faturas do cliente para contexto
    echo "<h3>📋 FATURAS DO CLIENTE (CONTEXTO)</h3>";
    $sql_faturas = "SELECT 
                        id,
                        valor,
                        vencimento,
                        status,
                        DATEDIFF(CURDATE(), vencimento) as dias_vencido
                    FROM cobrancas 
                    WHERE cliente_id = 274 
                    AND status IN ('PENDING', 'OVERDUE')
                    AND vencimento < CURDATE()
                    ORDER BY vencimento ASC";
    
    $result_faturas = $mysqli->query($sql_faturas);
    
    if ($result_faturas && $result_faturas->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Valor</th><th>Vencimento</th><th>Dias Vencida</th><th>Status</th>";
        echo "</tr>";
        
        while ($fatura = $result_faturas->fetch_assoc()) {
            $cor = $fatura['dias_vencido'] > 30 ? 'red' : ($fatura['dias_vencido'] > 7 ? 'orange' : 'green');
            echo "<tr>";
            echo "<td>{$fatura['id']}</td>";
            echo "<td>R$ " . number_format($fatura['valor'], 2, ',', '.') . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($fatura['vencimento'])) . "</td>";
            echo "<td style='color: $cor;'>{$fatura['dias_vencido']} dias</td>";
            echo "<td>{$fatura['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma fatura vencida encontrada para este cliente.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='http://localhost:8080/painel/monitoramento.php'>← Voltar ao Monitoramento</a></p>";
?> 