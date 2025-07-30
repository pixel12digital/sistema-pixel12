<?php
/**
 * Diagnóstico: Clientes em Monitoramento sem Mensagens Agendadas
 * Verifica por que alguns clientes marcados como monitorados não têm mensagens agendadas
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔍 Diagnóstico: Clientes em Monitoramento sem Mensagens Agendadas</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Verificar clientes em monitoramento
    echo "<h2>📊 1. Clientes em Monitoramento</h2>";
    
    $sql_monitorados = "SELECT 
                            cm.cliente_id,
                            c.nome,
                            c.celular,
                            cm.monitorado,
                            cm.data_criacao as data_monitoramento,
                            cm.data_atualizacao as ultima_atualizacao
                        FROM clientes_monitoramento cm
                        JOIN clientes c ON cm.cliente_id = c.id
                        WHERE cm.monitorado = 1
                        ORDER BY c.nome";
    
    $result_monitorados = $mysqli->query($sql_monitorados);
    
    if (!$result_monitorados) {
        throw new Exception("Erro ao buscar clientes monitorados: " . $mysqli->error);
    }
    
    $clientes_monitorados = [];
    while ($row = $result_monitorados->fetch_assoc()) {
        $clientes_monitorados[] = $row;
    }
    
    echo "<p><strong>Total de clientes monitorados:</strong> " . count($clientes_monitorados) . "</p>";
    
    // 2. Verificar mensagens agendadas
    echo "<h2>📅 2. Mensagens Agendadas</h2>";
    
    $sql_mensagens = "SELECT 
                          ma.cliente_id,
                          c.nome as cliente_nome,
                          COUNT(ma.id) as total_mensagens,
                          SUM(CASE WHEN ma.status = 'agendada' THEN 1 ELSE 0 END) as mensagens_agendadas,
                          SUM(CASE WHEN ma.status = 'enviada' THEN 1 ELSE 0 END) as mensagens_enviadas,
                          MAX(ma.data_agendada) as proxima_mensagem
                      FROM mensagens_agendadas ma
                      JOIN clientes c ON ma.cliente_id = c.id
                      GROUP BY ma.cliente_id, c.nome
                      ORDER BY c.nome";
    
    $result_mensagens = $mysqli->query($sql_mensagens);
    
    if (!$result_mensagens) {
        throw new Exception("Erro ao buscar mensagens agendadas: " . $mysqli->error);
    }
    
    $mensagens_por_cliente = [];
    while ($row = $result_mensagens->fetch_assoc()) {
        $mensagens_por_cliente[$row['cliente_id']] = $row;
    }
    
    echo "<p><strong>Total de clientes com mensagens agendadas:</strong> " . count($mensagens_por_cliente) . "</p>";
    
    // 3. Verificar cobranças vencidas
    echo "<h2>💰 3. Cobranças Vencidas</h2>";
    
    $sql_cobrancas = "SELECT 
                          c.id as cliente_id,
                          c.nome,
                          COUNT(cob.id) as total_cobrancas,
                          SUM(cob.valor) as valor_total,
                          COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                          SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                          MAX(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN DATEDIFF(CURDATE(), cob.vencimento) ELSE 0 END) as dias_vencido
                      FROM clientes c
                      LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                      GROUP BY c.id, c.nome
                      HAVING cobrancas_vencidas > 0
                      ORDER BY valor_vencido DESC";
    
    $result_cobrancas = $mysqli->query($sql_cobrancas);
    
    if (!$result_cobrancas) {
        throw new Exception("Erro ao buscar cobranças: " . $mysqli->error);
    }
    
    $cobrancas_por_cliente = [];
    while ($row = $result_cobrancas->fetch_assoc()) {
        $cobrancas_por_cliente[$row['cliente_id']] = $row;
    }
    
    echo "<p><strong>Total de clientes com cobranças vencidas:</strong> " . count($cobrancas_por_cliente) . "</p>";
    
    // 4. Análise dos problemas
    echo "<h2>⚠️ 4. Análise dos Problemas</h2>";
    
    $problemas = [];
    
    foreach ($clientes_monitorados as $cliente) {
        $cliente_id = $cliente['cliente_id'];
        $tem_mensagens = isset($mensagens_por_cliente[$cliente_id]);
        $tem_cobrancas_vencidas = isset($cobrancas_por_cliente[$cliente_id]);
        
        if (!$tem_mensagens && $tem_cobrancas_vencidas) {
            $problemas[] = [
                'tipo' => 'SEM_MENSAGENS_COM_COBRANCAS',
                'cliente' => $cliente,
                'cobrancas' => $cobrancas_por_cliente[$cliente_id]
            ];
        } elseif (!$tem_mensagens && !$tem_cobrancas_vencidas) {
            $problemas[] = [
                'tipo' => 'SEM_MENSAGENS_SEM_COBRANCAS',
                'cliente' => $cliente,
                'cobrancas' => null
            ];
        } elseif ($tem_mensagens && !$tem_cobrancas_vencidas) {
            $problemas[] = [
                'tipo' => 'COM_MENSAGENS_SEM_COBRANCAS',
                'cliente' => $cliente,
                'mensagens' => $mensagens_por_cliente[$cliente_id]
            ];
        }
    }
    
    // 5. Relatório detalhado
    echo "<h2>📋 5. Relatório Detalhado</h2>";
    
    if (empty($problemas)) {
        echo "<p style='color: green;'>✅ Todos os clientes monitorados estão com mensagens agendadas corretamente!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Encontrados " . count($problemas) . " problemas:</p>";
        
        foreach ($problemas as $problema) {
            echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
            
            $cliente = $problema['cliente'];
            echo "<h3>Cliente: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
            echo "<p><strong>Celular:</strong> {$cliente['celular']}</p>";
            echo "<p><strong>Monitorado desde:</strong> " . date('d/m/Y H:i', strtotime($cliente['data_monitoramento'])) . "</p>";
            
            switch ($problema['tipo']) {
                case 'SEM_MENSAGENS_COM_COBRANCAS':
                    $cobrancas = $problema['cobrancas'];
                    echo "<p style='color: red;'><strong>❌ PROBLEMA:</strong> Cliente tem cobranças vencidas mas não tem mensagens agendadas</p>";
                    echo "<p><strong>Cobranças vencidas:</strong> {$cobrancas['cobrancas_vencidas']}</p>";
                    echo "<p><strong>Valor vencido:</strong> R$ " . number_format($cobrancas['valor_vencido'], 2, ',', '.') . "</p>";
                    echo "<p><strong>Dias vencido:</strong> {$cobrancas['dias_vencido']} dias</p>";
                    echo "<p><strong>Possível causa:</strong> Falha no agendamento automático ou cliente adicionado manualmente sem agendamento</p>";
                    break;
                    
                case 'SEM_MENSAGENS_SEM_COBRANCAS':
                    echo "<p style='color: orange;'><strong>⚠️ ATENÇÃO:</strong> Cliente monitorado mas não tem cobranças vencidas</p>";
                    echo "<p><strong>Possível causa:</strong> Cliente foi adicionado ao monitoramento mas suas cobranças foram pagas</p>";
                    break;
                    
                case 'COM_MENSAGENS_SEM_COBRANCAS':
                    $mensagens = $problema['mensagens'];
                    echo "<p style='color: blue;'><strong>ℹ️ INFO:</strong> Cliente tem mensagens agendadas mas não tem cobranças vencidas</p>";
                    echo "<p><strong>Mensagens agendadas:</strong> {$mensagens['mensagens_agendadas']}</p>";
                    echo "<p><strong>Próxima mensagem:</strong> " . ($mensagens['proxima_mensagem'] ? date('d/m/Y H:i', strtotime($mensagens['proxima_mensagem'])) : 'N/A') . "</p>";
                    echo "<p><strong>Possível causa:</strong> Cobranças foram pagas após agendamento das mensagens</p>";
                    break;
            }
            
            echo "</div>";
        }
    }
    
    // 6. Recomendações
    echo "<h2>💡 6. Recomendações</h2>";
    
    $sem_mensagens_com_cobrancas = array_filter($problemas, function($p) { return $p['tipo'] === 'SEM_MENSAGENS_COM_COBRANCAS'; });
    $sem_mensagens_sem_cobrancas = array_filter($problemas, function($p) { return $p['tipo'] === 'SEM_MENSAGENS_SEM_COBRANCAS'; });
    
    if (!empty($sem_mensagens_com_cobrancas)) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🚨 Ação Necessária</h3>";
        echo "<p><strong>Clientes com cobranças vencidas sem mensagens agendadas:</strong> " . count($sem_mensagens_com_cobrancas) . "</p>";
        echo "<p>Execute o botão '📅 Agendar Pendentes' no painel de monitoramento para criar mensagens para estes clientes.</p>";
        echo "</div>";
    }
    
    if (!empty($sem_mensagens_sem_cobrancas)) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>ℹ️ Verificação Recomendada</h3>";
        echo "<p><strong>Clientes monitorados sem cobranças vencidas:</strong> " . count($sem_mensagens_sem_cobrancas) . "</p>";
        echo "<p>Considere remover estes clientes do monitoramento se suas cobranças foram pagas.</p>";
        echo "</div>";
    }
    
    // 7. Estatísticas finais
    echo "<h2>📈 7. Estatísticas Finais</h2>";
    
    $total_monitorados = count($clientes_monitorados);
    $com_mensagens = count(array_filter($clientes_monitorados, function($c) use ($mensagens_por_cliente) {
        return isset($mensagens_por_cliente[$c['cliente_id']]);
    }));
    $com_cobrancas_vencidas = count(array_filter($clientes_monitorados, function($c) use ($cobrancas_por_cliente) {
        return isset($cobrancas_por_cliente[$c['cliente_id']]);
    }));
    
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: left;'>Métrica</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Valor</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Percentual</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>Total monitorados</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>$total_monitorados</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>100%</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>Com mensagens agendadas</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>$com_mensagens</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . round(($com_mensagens / $total_monitorados) * 100, 1) . "%</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>Com cobranças vencidas</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>$com_cobrancas_vencidas</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . round(($com_cobrancas_vencidas / $total_monitorados) * 100, 1) . "%</td>";
    echo "</tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Diagnóstico concluído em " . date('d/m/Y H:i:s') . "</em></p>";
?> 