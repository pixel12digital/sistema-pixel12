<?php
/**
 * Remover Clientes Adicionados Automaticamente
 * Remove clientes que foram adicionados automaticamente ao monitoramento
 * Mantém apenas os que foram marcados manualmente
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🗑️ Remover Clientes Adicionados Automaticamente</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Identificar clientes que foram adicionados automaticamente
    echo "<h2>🔍 1. Identificando Clientes Adicionados Automaticamente</h2>";
    
    // Buscar clientes que foram adicionados hoje (provavelmente automaticamente)
    $sql_automaticos = "SELECT 
                            cm.cliente_id,
                            c.nome,
                            c.celular,
                            cm.data_criacao,
                            cm.data_atualizacao,
                            COUNT(cob.id) as total_cobrancas,
                            COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                            COUNT(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 1 END) as cobrancas_a_vencer
                        FROM clientes_monitoramento cm
                        JOIN clientes c ON cm.cliente_id = c.id
                        LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                        WHERE cm.monitorado = 1
                        AND DATE(cm.data_criacao) = CURDATE()
                        AND TIME(cm.data_criacao) >= '18:00:00'
                        GROUP BY cm.cliente_id, c.nome, c.celular, cm.data_criacao, cm.data_atualizacao
                        ORDER BY cm.data_criacao DESC";
    
    $result_automaticos = $mysqli->query($sql_automaticos);
    
    if (!$result_automaticos) {
        throw new Exception("Erro ao buscar clientes automáticos: " . $mysqli->error);
    }
    
    $clientes_automaticos = [];
    while ($row = $result_automaticos->fetch_assoc()) {
        $clientes_automaticos[] = $row;
    }
    
    echo "<p><strong>Total de clientes adicionados automaticamente encontrados:</strong> " . count($clientes_automaticos) . "</p>";
    
    if (empty($clientes_automaticos)) {
        echo "<p style='color: green;'>✅ Nenhum cliente adicionado automaticamente encontrado!</p>";
        return;
    }
    
    // 2. Mostrar detalhes dos clientes que serão removidos
    echo "<h2>📋 2. Clientes que Serão Removidos</h2>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: left;'>Cliente</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>ID</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Data Adição</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Cobranças Vencidas</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Cobranças a Vencer</th>";
    echo "</tr>";
    
    foreach ($clientes_automaticos as $cliente) {
        echo "<tr>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>{$cliente['nome']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$cliente['cliente_id']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . date('d/m/Y H:i', strtotime($cliente['data_criacao'])) . "</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$cliente['cobrancas_vencidas']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$cliente['cobrancas_a_vencer']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Remover clientes automaticos
    echo "<h2>🗑️ 3. Removendo Clientes Automáticos</h2>";
    
    $clientes_removidos = 0;
    $mensagens_removidas = 0;
    $erros = 0;
    
    foreach ($clientes_automaticos as $cliente) {
        try {
            echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
            echo "<h3>Removendo: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
            
            // 1. Remover mensagens agendadas para este cliente
            $sql_remover_mensagens = "DELETE FROM mensagens_agendadas 
                                     WHERE cliente_id = {$cliente['cliente_id']} 
                                     AND status = 'agendada'";
            
            if ($mysqli->query($sql_remover_mensagens)) {
                $mensagens_removidas += $mysqli->affected_rows;
                echo "<p style='color: blue;'>🗑️ {$mysqli->affected_rows} mensagens agendadas removidas</p>";
            } else {
                throw new Exception("Erro ao remover mensagens: " . $mysqli->error);
            }
            
            // 2. Remover do monitoramento
            $sql_remover_monitoramento = "DELETE FROM clientes_monitoramento 
                                         WHERE cliente_id = {$cliente['cliente_id']}";
            
            if ($mysqli->query($sql_remover_monitoramento)) {
                $clientes_removidos++;
                echo "<p style='color: green;'>✅ Cliente removido do monitoramento</p>";
            } else {
                throw new Exception("Erro ao remover do monitoramento: " . $mysqli->error);
            }
            
            // Log da remoção
            $log_data = date('Y-m-d H:i:s') . " - REMOÇÃO AUTOMÁTICA: Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}) removido do monitoramento (adicionado automaticamente)\n";
            file_put_contents('painel/logs/remocao_clientes_automaticos.log', $log_data, FILE_APPEND);
            
            echo "</div>";
            
        } catch (Exception $e) {
            $erros++;
            echo "<p style='color: red;'>❌ Erro ao remover cliente {$cliente['nome']}: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    // 4. Relatório final
    echo "<h2>📊 4. Relatório Final</h2>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Remoção Concluída</h3>";
    echo "<p><strong>Clientes removidos:</strong> $clientes_removidos</p>";
    echo "<p><strong>Mensagens removidas:</strong> $mensagens_removidas</p>";
    echo "<p><strong>Erros:</strong> $erros</p>";
    echo "</div>";
    
    // 5. Verificação final
    echo "<h2>🔍 5. Verificação Final</h2>";
    
    $sql_verificacao = "SELECT COUNT(*) as total FROM clientes_monitoramento WHERE monitorado = 1";
    $result_verificacao = $mysqli->query($sql_verificacao);
    $verificacao = $result_verificacao->fetch_assoc();
    
    echo "<p><strong>Total de clientes monitorados após remoção:</strong> {$verificacao['total']}</p>";
    
    if ($clientes_removidos > 0) {
        echo "<p style='color: green;'>✅ Clientes adicionados automaticamente foram removidos com sucesso!</p>";
        echo "<p><strong>Próximos passos:</strong></p>";
        echo "<ul>";
        echo "<li>Adicione manualmente apenas os clientes que deseja monitorar</li>";
        echo "<li>O sistema otimizado continuará funcionando para clientes monitorados manualmente</li>";
        echo "<li>Mensagens serão enviadas no dia do vencimento incluindo faturas vencidas</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Nenhum cliente foi removido.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Remoção concluída em " . date('d/m/Y H:i:s') . "</em></p>";
?> 