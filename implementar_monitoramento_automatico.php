<?php
/**
 * Implementar Monitoramento Automático
 * Adiciona automaticamente clientes ao monitoramento quando têm cobranças vencidas
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🤖 Implementar Monitoramento Automático</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Identificar clientes com cobranças vencidas que não estão monitorados
    echo "<h2>🔍 1. Identificando Clientes para Monitoramento Automático</h2>";
    
    $sql_clientes_para_monitorar = "SELECT DISTINCT 
                                        c.id as cliente_id,
                                        c.nome,
                                        c.celular,
                                        c.contact_name,
                                        COUNT(cob.id) as total_cobrancas,
                                        COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                                        SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                                        MAX(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN DATEDIFF(CURDATE(), cob.vencimento) ELSE 0 END) as dias_vencido
                                    FROM clientes c
                                    JOIN cobrancas cob ON c.id = cob.cliente_id
                                    LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
                                    WHERE cob.status IN ('PENDING', 'OVERDUE')
                                    AND cob.vencimento < CURDATE()
                                    AND (cm.cliente_id IS NULL OR cm.monitorado = 0)
                                    AND c.celular IS NOT NULL
                                    AND c.celular != ''
                                    GROUP BY c.id, c.nome, c.celular, c.contact_name
                                    HAVING cobrancas_vencidas > 0
                                    ORDER BY valor_vencido DESC";
    
    $result_clientes = $mysqli->query($sql_clientes_para_monitorar);
    
    if (!$result_clientes) {
        throw new Exception("Erro ao buscar clientes para monitoramento: " . $mysqli->error);
    }
    
    $clientes_para_monitorar = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes_para_monitorar[] = $row;
    }
    
    echo "<p><strong>Total de clientes identificados para monitoramento automático:</strong> " . count($clientes_para_monitorar) . "</p>";
    
    if (empty($clientes_para_monitorar)) {
        echo "<p style='color: green;'>✅ Nenhum cliente precisa ser adicionado ao monitoramento automático!</p>";
        return;
    }
    
    // 2. Processar cada cliente
    echo "<h2>⚙️ 2. Adicionando Clientes ao Monitoramento</h2>";
    
    $clientes_processados = 0;
    $clientes_adicionados = 0;
    $mensagens_agendadas = 0;
    $erros = 0;
    $log_detalhado = [];
    
    foreach ($clientes_para_monitorar as $cliente) {
        $clientes_processados++;
        
        try {
            echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
            echo "<h3>Processando: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
            
            // 1. Adicionar ao monitoramento
            $sql_adicionar = "INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                              VALUES ({$cliente['cliente_id']}, 1, NOW(), NOW())
                              ON DUPLICATE KEY UPDATE monitorado = 1, data_atualizacao = NOW()";
            
            if ($mysqli->query($sql_adicionar)) {
                $clientes_adicionados++;
                echo "<p style='color: green;'>✅ Cliente adicionado ao monitoramento</p>";
            } else {
                throw new Exception("Erro ao adicionar ao monitoramento: " . $mysqli->error);
            }
            
            // 2. Buscar faturas vencidas do cliente
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
            
            // 3. Agrupar faturas por estratégia de envio
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
            
            // 4. Agendar mensagens com diferentes estratégias
            $agendamentos_cliente = 0;
            
            // Faturas vencidas recentes (até 7 dias) - enviar amanhã
            if (!empty($faturas_vencidas_recentes)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_recentes, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+1 day 10:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'alta', $mysqli)) {
                    $agendamentos_cliente++;
                    echo "<p style='color: green;'>✅ Mensagem agendada para faturas recentes (amanhã 10:00)</p>";
                }
            }
            
            // Faturas vencidas médias (8-30 dias) - enviar em 3 dias
            if (!empty($faturas_vencidas_medias)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_medias, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+3 days 14:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'normal', $mysqli)) {
                    $agendamentos_cliente++;
                    echo "<p style='color: green;'>✅ Mensagem agendada para faturas médias (em 3 dias 14:00)</p>";
                }
            }
            
            // Faturas vencidas antigas (mais de 30 dias) - enviar em 7 dias
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
            
            // Log do processamento
            $log_data = date('Y-m-d H:i:s') . " - MONITORAMENTO AUTOMÁTICO: Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}): adicionado ao monitoramento com $agendamentos_cliente mensagens agendadas para " . count($faturas) . " faturas vencidas\n";
            file_put_contents('painel/logs/monitoramento_automatico.log', $log_data, FILE_APPEND);
            
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
    echo "<h3>✅ Monitoramento Automático Concluído</h3>";
    echo "<p><strong>Clientes processados:</strong> $clientes_processados</p>";
    echo "<p><strong>Clientes adicionados ao monitoramento:</strong> $clientes_adicionados</p>";
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
    
    // 5. Criar script cron para execução automática
    echo "<h2>⏰ 4. Configuração do Cron</h2>";
    
    $cron_script = "<?php
/**
 * Monitoramento Automático - Script Cron
 * Executar via cron: 0 */6 * * * php " . __DIR__ . "/monitoramento_automatico_cron.php
 * 
 * Este script executa a cada 6 horas para adicionar automaticamente
 * clientes com cobranças vencidas ao monitoramento
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/painel/db.php';

try {
    // Buscar clientes com cobranças vencidas que não estão monitorados
    \$sql = \"SELECT DISTINCT 
                c.id as cliente_id,
                c.nome,
                c.celular,
                c.contact_name,
                COUNT(cob.id) as total_cobrancas,
                COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido
            FROM clientes c
            JOIN cobrancas cob ON c.id = cob.cliente_id
            LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            WHERE cob.status IN ('PENDING', 'OVERDUE')
            AND cob.vencimento < CURDATE()
            AND (cm.cliente_id IS NULL OR cm.monitorado = 0)
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY c.id, c.nome, c.celular, c.contact_name
            HAVING cobrancas_vencidas > 0\";
    
    \$result = \$mysqli->query(\$sql);
    
    if (!\$result) {
        throw new Exception(\"Erro ao buscar clientes: \" . \$mysqli->error);
    }
    
    \$clientes_processados = 0;
    \$clientes_adicionados = 0;
    
    while (\$cliente = \$result->fetch_assoc()) {
        \$clientes_processados++;
        
        try {
            // Adicionar ao monitoramento
            \$sql_adicionar = \"INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                              VALUES ({\$cliente['cliente_id']}, 1, NOW(), NOW())\";
            
            if (\$mysqli->query(\$sql_adicionar)) {
                \$clientes_adicionados++;
                
                // Buscar faturas vencidas para agendar mensagens
                \$sql_faturas = \"SELECT id, valor, vencimento, url_fatura, status, DATEDIFF(CURDATE(), vencimento) as dias_vencido
                                FROM cobrancas 
                                WHERE cliente_id = {\$cliente['cliente_id']}
                                AND status IN ('PENDING', 'OVERDUE')
                                AND vencimento < CURDATE()
                                ORDER BY vencimento ASC\";
                
                \$result_faturas = \$mysqli->query(\$sql_faturas);
                
                if (\$result_faturas && \$result_faturas->num_rows > 0) {
                    \$faturas = [];
                    while (\$fatura = \$result_faturas->fetch_assoc()) {
                        \$faturas[] = \$fatura;
                    }
                    
                    // Agrupar faturas por estratégia
                    \$faturas_recentes = array_filter(\$faturas, function(\$f) { return \$f['dias_vencido'] <= 7; });
                    \$faturas_medias = array_filter(\$faturas, function(\$f) { return \$f['dias_vencido'] > 7 && \$f['dias_vencido'] <= 30; });
                    \$faturas_antigas = array_filter(\$faturas, function(\$f) { return \$f['dias_vencido'] > 30; });
                    
                    // Agendar mensagens
                    if (!empty(\$faturas_recentes)) {
                        \$mensagem = montarMensagemCobrancaVencida(\$faturas_recentes, \$cliente);
                        \$horario = date('Y-m-d H:i:s', strtotime('+1 day 10:00:00'));
                        agendarMensagem(\$cliente['cliente_id'], \$mensagem, \$horario, 'alta', \$mysqli);
                    }
                    
                    if (!empty(\$faturas_medias)) {
                        \$mensagem = montarMensagemCobrancaVencida(\$faturas_medias, \$cliente);
                        \$horario = date('Y-m-d H:i:s', strtotime('+3 days 14:00:00'));
                        agendarMensagem(\$cliente['cliente_id'], \$mensagem, \$horario, 'normal', \$mysqli);
                    }
                    
                    if (!empty(\$faturas_antigas)) {
                        \$mensagem = montarMensagemCobrancaVencida(\$faturas_antigas, \$cliente);
                        \$horario = date('Y-m-d H:i:s', strtotime('+7 days 16:00:00'));
                        agendarMensagem(\$cliente['cliente_id'], \$mensagem, \$horario, 'baixa', \$mysqli);
                    }
                }
                
                // Log do sucesso
                \$log_data = date('Y-m-d H:i:s') . \" - CRON: Cliente {\$cliente['nome']} (ID: {\$cliente['cliente_id']}) adicionado ao monitoramento automático\\n\";
                file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', \$log_data, FILE_APPEND);
            }
            
        } catch (Exception \$e) {
            \$log_erro = date('Y-m-d H:i:s') . \" - CRON ERRO: Cliente {\$cliente['nome']}: \" . \$e->getMessage() . \"\\n\";
            file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', \$log_erro, FILE_APPEND);
        }
    }
    
    // Log final
    \$log_final = date('Y-m-d H:i:s') . \" - CRON FINALIZADO: \$clientes_processados processados, \$clientes_adicionados adicionados\\n\";
    file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', \$log_final, FILE_APPEND);
    
} catch (Exception \$e) {
    \$log_erro = date('Y-m-d H:i:s') . \" - CRON ERRO GERAL: \" . \$e->getMessage() . \"\\n\";
    file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', \$log_erro, FILE_APPEND);
}

function montarMensagemCobrancaVencida(\$faturas, \$cliente_info) {
    \$nome = \$cliente_info['contact_name'] ?: \$cliente_info['nome'];
    
    \$mensagem = \"Olá {\$nome}! \\n\\n\";
    \$mensagem .= \"⚠️ Você possui faturas em aberto:\\n\\n\";
    
    \$valor_total = 0;
    foreach (\$faturas as \$fatura) {
        \$valor = number_format(\$fatura['valor'], 2, ',', '.');
        \$vencimento = date('d/m/Y', strtotime(\$fatura['vencimento']));
        \$dias_vencida = intval(\$fatura['dias_vencido']);
        
        \$mensagem .= \"• Fatura #{\$fatura['id']} - R\$ \$valor - Venceu em \$vencimento ({\$dias_vencida} dias vencida)\\n\";
        \$valor_total += floatval(\$fatura['valor']);
    }
    
    \$mensagem .= \"\\n💰 Valor total em aberto: R\$ \" . number_format(\$valor_total, 2, ',', '.') . \"\\n\";
    \$mensagem .= \"🔗 Link para pagamento: {\$faturas[0]['url_fatura']}\\n\\n\";
    \$mensagem .= \"Para consultar todas as suas faturas, responda \\\"faturas\\\" ou \\\"consulta\\\".\\n\\n\";
    \$mensagem .= \"Atenciosamente,\\nEquipe Financeira Pixel12 Digital\";
    
    return \$mensagem;
}

function agendarMensagem(\$cliente_id, \$mensagem, \$horario_envio, \$prioridade, \$mysqli) {
    try {
        \$mensagem_escaped = \$mysqli->real_escape_string(\$mensagem);
        \$horario_escaped = \$mysqli->real_escape_string(\$horario_envio);
        \$prioridade_escaped = \$mysqli->real_escape_string(\$prioridade);
        
        \$sql = \"INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                VALUES (\$cliente_id, '\$mensagem_escaped', 'cobranca_vencida', '\$prioridade_escaped', '\$horario_escaped', 'agendada', NOW())\";
        
        return \$mysqli->query(\$sql);
    } catch (Exception \$e) {
        return false;
    }
}
?>";
    
    file_put_contents('monitoramento_automatico_cron.php', $cron_script);
    
    echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>📝 Script Cron Criado</h3>";
    echo "<p><strong>Arquivo criado:</strong> monitoramento_automatico_cron.php</p>";
    echo "<p><strong>Comando cron recomendado:</strong></p>";
    echo "<code style='background: #f8f9fa; padding: 5px; border-radius: 3px;'>0 */6 * * * php " . __DIR__ . "/monitoramento_automatico_cron.php</code>";
    echo "<p><em>Este comando executa a cada 6 horas para adicionar automaticamente clientes com cobranças vencidas ao monitoramento.</em></p>";
    echo "</div>";
    
    // 6. Verificação final
    echo "<h2>🔍 5. Verificação Final</h2>";
    
    $sql_verificacao = "SELECT COUNT(*) as total FROM clientes_monitoramento WHERE monitorado = 1";
    $result_verificacao = $mysqli->query($sql_verificacao);
    $verificacao = $result_verificacao->fetch_assoc();
    
    echo "<p><strong>Total de clientes monitorados após implementação:</strong> {$verificacao['total']}</p>";
    
    if ($clientes_adicionados > 0) {
        echo "<p style='color: green;'>✅ Monitoramento automático implementado com sucesso!</p>";
        echo "<p><strong>Próximos passos:</strong></p>";
        echo "<ul>";
        echo "<li>Configure o cron job para execução automática</li>";
        echo "<li>Monitore os logs em painel/logs/monitoramento_automatico_cron.log</li>";
        echo "<li>Verifique periodicamente o painel de monitoramento</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Nenhum cliente foi adicionado automaticamente (todos já estavam monitorados ou não têm cobranças vencidas).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Implementação concluída em " . date('d/m/Y H:i:s') . "</em></p>";

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