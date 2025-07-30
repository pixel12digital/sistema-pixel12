<?php
/**
 * Corrigir Clientes em Monitoramento sem Mensagens Agendadas
 * Adiciona mensagens agendadas para clientes que estão monitorados mas não têm mensagens
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔧 Corrigir Clientes em Monitoramento sem Mensagens Agendadas</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Identificar clientes monitorados sem mensagens agendadas
    echo "<h2>🔍 1. Identificando Clientes Problemáticos</h2>";
    
    $sql_problemas = "SELECT DISTINCT 
                        cm.cliente_id,
                        c.nome,
                        c.celular,
                        c.contact_name,
                        cm.data_criacao as data_monitoramento,
                        COUNT(cob.id) as total_cobrancas,
                        COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                        SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                        MAX(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN DATEDIFF(CURDATE(), cob.vencimento) ELSE 0 END) as dias_vencido
                    FROM clientes_monitoramento cm
                    JOIN clientes c ON cm.cliente_id = c.id
                    LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                    LEFT JOIN mensagens_agendadas ma ON cm.cliente_id = ma.cliente_id AND ma.status = 'agendada'
                    WHERE cm.monitorado = 1
                    AND ma.id IS NULL
                    AND c.celular IS NOT NULL
                    AND c.celular != ''
                    GROUP BY cm.cliente_id, c.nome, c.celular, c.contact_name, cm.data_criacao
                    HAVING cobrancas_vencidas > 0
                    ORDER BY valor_vencido DESC";
    
    $result_problemas = $mysqli->query($sql_problemas);
    
    if (!$result_problemas) {
        throw new Exception("Erro ao buscar clientes problemáticos: " . $mysqli->error);
    }
    
    $clientes_problematicos = [];
    while ($row = $result_problemas->fetch_assoc()) {
        $clientes_problematicos[] = $row;
    }
    
    echo "<p><strong>Total de clientes problemáticos encontrados:</strong> " . count($clientes_problematicos) . "</p>";
    
    if (empty($clientes_problematicos)) {
        echo "<p style='color: green;'>✅ Nenhum cliente problemático encontrado!</p>";
        return;
    }
    
    // 2. Processar cada cliente problemático
    echo "<h2>⚙️ 2. Processando Correções</h2>";
    
    $clientes_processados = 0;
    $mensagens_agendadas = 0;
    $erros = 0;
    $log_detalhado = [];
    
    foreach ($clientes_problematicos as $cliente) {
        $clientes_processados++;
        
        try {
            echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
            echo "<h3>Processando: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
            
            // Buscar faturas vencidas do cliente
            $sql_faturas = "SELECT 
                                cob.id,
                                cob.valor,
                                cob.vencimento,
                                cob.url_fatura,
                                cob.status,
                                DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido
                            FROM cobrancas cob
                            WHERE cob.cliente_id = {$cliente['cliente_id']}
                            AND cob.status IN ('PENDING', 'OVERDUE')
                            AND cob.vencimento < CURDATE()
                            ORDER BY cob.vencimento ASC";
            
            $result_faturas = $mysqli->query($sql_faturas);
            
            if (!$result_faturas || $result_faturas->num_rows === 0) {
                echo "<p style='color: orange;'>⚠️ Nenhuma fatura vencida encontrada</p>";
                continue;
            }
            
            $faturas = [];
            while ($fatura = $result_faturas->fetch_assoc()) {
                $faturas[] = $fatura;
            }
            
            echo "<p><strong>Faturas vencidas encontradas:</strong> " . count($faturas) . "</p>";
            
            // Agrupar faturas por estratégia de envio
            $faturas_vencidas_recentes = []; // Até 7 dias vencidas
            $faturas_vencidas_medias = [];   // 8-30 dias vencidas
            $faturas_vencidas_antigas = [];  // Mais de 30 dias vencidas
            
            foreach ($faturas as $fatura) {
                $dias_vencida = intval($fatura['dias_vencido']);
                
                if ($dias_vencida <= 7) {
                    $faturas_vencidas_recentes[] = $fatura;
                } elseif ($dias_vencida <= 30) {
                    $faturas_vencidas_medias[] = $fatura;
                } else {
                    $faturas_vencidas_antigas[] = $fatura;
                }
            }
            
            // Agendar mensagens com diferentes estratégias
            $agendamentos_cliente = 0;
            
            // 1. Faturas vencidas recentes (até 7 dias) - enviar amanhã
            if (!empty($faturas_vencidas_recentes)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_recentes, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+1 day 10:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'alta', $mysqli)) {
                    $agendamentos_cliente++;
                    echo "<p style='color: green;'>✅ Mensagem agendada para faturas recentes (amanhã 10:00)</p>";
                }
            }
            
            // 2. Faturas vencidas médias (8-30 dias) - enviar em 3 dias
            if (!empty($faturas_vencidas_medias)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_medias, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+3 days 14:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'normal', $mysqli)) {
                    $agendamentos_cliente++;
                    echo "<p style='color: green;'>✅ Mensagem agendada para faturas médias (em 3 dias 14:00)</p>";
                }
            }
            
            // 3. Faturas vencidas antigas (mais de 30 dias) - enviar em 7 dias
            if (!empty($faturas_vencidas_antigas)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_antigas, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+7 days 16:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'baixa', $mysqli)) {
                    $agendamentos_cliente++;
                    echo "<p style='color: green;'>✅ Mensagem agendada para faturas antigas (em 7 dias 16:00)</p>";
                }
            }
            
            $mensagens_agendadas += $agendamentos_cliente;
            
            echo "<p><strong>Total de mensagens agendadas para este cliente:</strong> $agendamentos_cliente</p>";
            
            // Log do agendamento
            $log_data = date('Y-m-d H:i:s') . " - CORREÇÃO: Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}): $agendamentos_cliente mensagens agendadas para " . count($faturas) . " faturas vencidas\n";
            file_put_contents('painel/logs/correcao_monitoramento.log', $log_data, FILE_APPEND);
            
            $log_detalhado[] = [
                'cliente' => $cliente['nome'],
                'cliente_id' => $cliente['cliente_id'],
                'faturas_vencidas' => count($faturas),
                'valor_vencido' => $cliente['valor_vencido'],
                'mensagens_agendadas' => $agendamentos_cliente,
                'status' => 'sucesso'
            ];
            
            echo "</div>";
            
        } catch (Exception $e) {
            $erros++;
            echo "<p style='color: red;'>❌ Erro ao processar cliente {$cliente['nome']}: " . $e->getMessage() . "</p>";
            echo "</div>";
            
            $log_detalhado[] = [
                'cliente' => $cliente['nome'],
                'cliente_id' => $cliente['cliente_id'],
                'status' => 'erro',
                'erro' => $e->getMessage()
            ];
        }
    }
    
    // 3. Relatório final
    echo "<h2>📊 3. Relatório Final</h2>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Correção Concluída</h3>";
    echo "<p><strong>Clientes processados:</strong> $clientes_processados</p>";
    echo "<p><strong>Mensagens agendadas:</strong> $mensagens_agendadas</p>";
    echo "<p><strong>Erros:</strong> $erros</p>";
    echo "</div>";
    
    // 4. Detalhamento dos resultados
    if (!empty($log_detalhado)) {
        echo "<h3>📋 Detalhamento por Cliente</h3>";
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: left;'>Cliente</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>ID</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Faturas Vencidas</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Valor Vencido</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Mensagens Agendadas</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Status</th>";
        echo "</tr>";
        
        foreach ($log_detalhado as $log) {
            $status_color = $log['status'] === 'sucesso' ? 'green' : 'red';
            $status_text = $log['status'] === 'sucesso' ? '✅ Sucesso' : '❌ Erro';
            
            echo "<tr>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>{$log['cliente']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$log['cliente_id']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['faturas_vencidas'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . (isset($log['valor_vencido']) ? 'R$ ' . number_format($log['valor_vencido'], 2, ',', '.') : 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['mensagens_agendadas'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center; color: $status_color;'>$status_text</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Verificação final
    echo "<h2>🔍 4. Verificação Final</h2>";
    
    $sql_verificacao = "SELECT COUNT(*) as total FROM clientes_monitoramento cm
                        LEFT JOIN mensagens_agendadas ma ON cm.cliente_id = ma.cliente_id AND ma.status = 'agendada'
                        WHERE cm.monitorado = 1 AND ma.id IS NULL";
    
    $result_verificacao = $mysqli->query($sql_verificacao);
    $verificacao = $result_verificacao->fetch_assoc();
    
    if ($verificacao['total'] == 0) {
        echo "<p style='color: green;'>✅ Todos os clientes monitorados agora têm mensagens agendadas!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Ainda existem {$verificacao['total']} clientes monitorados sem mensagens agendadas.</p>";
        echo "<p>Execute este script novamente se necessário.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Correção concluída em " . date('d/m/Y H:i:s') . "</em></p>";

/**
 * Monta mensagem de cobrança vencida
 */
function montarMensagemCobrancaVencida($faturas, $cliente_info) {
    $nome = $cliente_info['contact_name'] ?: $cliente_info['nome'];
    
    $mensagem = "Olá {$nome}! \n\n";
    $mensagem .= "⚠️ Você possui faturas em aberto:\n\n";
    
    $valor_total = 0;
    foreach ($faturas as $fatura) {
        $valor = number_format($fatura['valor'], 2, ',', '.');
        $vencimento = date('d/m/Y', strtotime($fatura['vencimento']));
        $dias_vencida = intval($fatura['dias_vencido']);
        
        $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor - Venceu em $vencimento ({$dias_vencida} dias vencida)\n";
        $valor_total += floatval($fatura['valor']);
    }
    
    $mensagem .= "\n💰 Valor total em aberto: R$ " . number_format($valor_total, 2, ',', '.') . "\n";
    $mensagem .= "🔗 Link para pagamento: {$faturas[0]['url_fatura']}\n\n";
    $mensagem .= "Para consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\n";
    $mensagem .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
    
    return $mensagem;
}

/**
 * Agenda uma mensagem específica
 */
function agendarMensagem($cliente_id, $mensagem, $horario_envio, $prioridade, $mysqli) {
    try {
        $mensagem_escaped = $mysqli->real_escape_string($mensagem);
        $horario_escaped = $mysqli->real_escape_string($horario_envio);
        $prioridade_escaped = $mysqli->real_escape_string($prioridade);
        
        $sql = "INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                VALUES ($cliente_id, '$mensagem_escaped', 'cobranca_vencida', '$prioridade_escaped', '$horario_escaped', 'agendada', NOW())";
        
        if ($mysqli->query($sql)) {
            return true;
        } else {
            error_log("Erro ao agendar mensagem: " . $mysqli->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao agendar mensagem: " . $e->getMessage());
        return false;
    }
}
?> 