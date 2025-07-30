<?php
/**
 * Monitoramento Manual Otimizado - Sem Duplicidade
 * Funciona apenas para clientes monitorados manualmente
 * Envia mensagens no dia do vencimento incluindo faturas vencidas
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🤖 Monitoramento Manual Otimizado - Sem Duplicidade</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Identificar clientes monitorados manualmente que precisam de mensagens
    echo "<h2>🔍 1. Identificando Clientes Monitorados Manualmente</h2>";
    
    $sql_clientes_monitorados = "SELECT DISTINCT 
                                    cm.cliente_id,
                                    c.nome,
                                    c.celular,
                                    c.contact_name,
                                    cm.data_criacao as data_monitoramento,
                                    COUNT(cob.id) as total_cobrancas,
                                    COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                                    COUNT(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 1 END) as cobrancas_a_vencer,
                                    COUNT(CASE WHEN cob.status IN ('PENDING') AND DATE(cob.vencimento) = CURDATE() THEN 1 END) as cobrancas_vencendo_hoje,
                                    SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                                    SUM(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN cob.valor ELSE 0 END) as valor_a_vencer
                                FROM clientes_monitoramento cm
                                JOIN clientes c ON cm.cliente_id = c.id
                                LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                                WHERE cm.monitorado = 1
                                AND c.celular IS NOT NULL
                                AND c.celular != ''
                                GROUP BY cm.cliente_id, c.nome, c.celular, c.contact_name, cm.data_criacao
                                HAVING (cobrancas_vencidas > 0 OR cobrancas_a_vencer > 0)
                                ORDER BY cobrancas_vencendo_hoje DESC, valor_vencido DESC, valor_a_vencer DESC";
    
    $result_clientes = $mysqli->query($sql_clientes_monitorados);
    
    if (!$result_clientes) {
        throw new Exception("Erro ao buscar clientes monitorados: " . $mysqli->error);
    }
    
    $clientes_monitorados = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes_monitorados[] = $row;
    }
    
    echo "<p><strong>Total de clientes monitorados manualmente encontrados:</strong> " . count($clientes_monitorados) . "</p>";
    
    if (empty($clientes_monitorados)) {
        echo "<p style='color: green;'>✅ Nenhum cliente monitorado manualmente encontrado!</p>";
        return;
    }
    
    // 2. Processar cada cliente individualmente
    echo "<h2>⚙️ 2. Processando Clientes Monitorados Manualmente</h2>";
    
    $clientes_processados = 0;
    $mensagens_agendadas = 0;
    $erros = 0;
    $log_detalhado = [];
    
    foreach ($clientes_monitorados as $cliente) {
        $clientes_processados++;
        
        try {
            echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
            echo "<h3>Processando: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
            
            // Sincronizar dados do cliente individualmente (apenas faturas)
            echo "<p>🔄 Sincronizando dados de fatura...</p>";
            
            // Buscar TODAS as faturas do cliente (vencidas e a vencer)
            $sql_faturas = "SELECT 
                                cob.id,
                                cob.valor,
                                cob.vencimento,
                                cob.url_fatura,
                                cob.status,
                                DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido,
                                DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer,
                                CASE 
                                    WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 'vencida'
                                    WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 'a_vencer'
                                    ELSE 'outro'
                                END as tipo_fatura
                            FROM cobrancas cob
                            WHERE cob.cliente_id = {$cliente['cliente_id']}
                            AND cob.status IN ('PENDING', 'OVERDUE')
                            ORDER BY cob.vencimento ASC";
            
            $result_faturas = $mysqli->query($sql_faturas);
            
            if (!$result_faturas || $result_faturas->num_rows === 0) {
                echo "<p style='color: orange;'>⚠️ Nenhuma fatura encontrada</p>";
                continue;
            }
            
            $faturas = [];
            $faturas_vencidas = [];
            $faturas_a_vencer = [];
            $faturas_vencendo_hoje = [];
            
            while ($fatura = $result_faturas->fetch_assoc()) {
                $faturas[] = $fatura;
                
                if ($fatura['tipo_fatura'] === 'vencida') {
                    $faturas_vencidas[] = $fatura;
                } elseif ($fatura['tipo_fatura'] === 'a_vencer') {
                    $faturas_a_vencer[] = $fatura;
                    
                    // Verificar se vence hoje
                    if (date('Y-m-d', strtotime($fatura['vencimento'])) === date('Y-m-d')) {
                        $faturas_vencendo_hoje[] = $fatura;
                    }
                }
            }
            
            echo "<p><strong>Total de faturas encontradas:</strong> " . count($faturas) . "</p>";
            echo "<p><strong>Faturas vencidas:</strong> " . count($faturas_vencidas) . "</p>";
            echo "<p><strong>Faturas a vencer:</strong> " . count($faturas_a_vencer) . "</p>";
            echo "<p><strong>Faturas vencendo hoje:</strong> " . count($faturas_vencendo_hoje) . "</p>";
            
            // 3. Verificar se já existe mensagem agendada para este cliente
            $sql_verificar = "SELECT id FROM mensagens_agendadas 
                             WHERE cliente_id = {$cliente['cliente_id']} 
                             AND status = 'agendada' 
                             AND data_agendada > NOW()";
            
            $result_verificar = $mysqli->query($sql_verificar);
            
            if ($result_verificar && $result_verificar->num_rows > 0) {
                echo "<p style='color: orange;'>⚠️ Cliente já possui mensagem agendada - pulando</p>";
                continue;
            }
            
            // 4. Determinar estratégia de envio baseada no tipo de faturas
            $estrategia = determinarEstrategiaEnvioManual($faturas_vencidas, $faturas_a_vencer, $faturas_vencendo_hoje);
            
            echo "<p><strong>Estratégia de envio:</strong> $estrategia</p>";
            
            // 5. Criar UMA ÚNICA mensagem com todas as faturas
            $mensagem = montarMensagemCompleta($faturas, $cliente);
            $horario_envio = calcularHorarioEnvioManual($estrategia);
            $prioridade = determinarPrioridadeManual($estrategia);
            
            // 6. Agendar a mensagem única
            if (agendarMensagemUnica($cliente['cliente_id'], $mensagem, $horario_envio, $prioridade, $mysqli)) {
                $mensagens_agendadas++;
                echo "<p style='color: green;'>✅ Mensagem única agendada para " . count($faturas) . " faturas</p>";
                echo "<p><strong>Horário de envio:</strong> " . date('d/m/Y H:i', strtotime($horario_envio)) . "</p>";
                echo "<p><strong>Prioridade:</strong> $prioridade</p>";
            } else {
                throw new Exception("Erro ao agendar mensagem");
            }
            
            // Log do processamento
            $log_data = date('Y-m-d H:i:s') . " - MONITORAMENTO MANUAL OTIMIZADO: Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}): 1 mensagem agendada para " . count($faturas) . " faturas (estratégia: $estrategia)\n";
            file_put_contents('painel/logs/monitoramento_manual_otimizado.log', $log_data, FILE_APPEND);
            
            $log_detalhado[] = [
                'cliente' => $cliente['nome'],
                'cliente_id' => $cliente['cliente_id'],
                'faturas_total' => count($faturas),
                'faturas_vencidas' => count($faturas_vencidas),
                'faturas_a_vencer' => count($faturas_a_vencer),
                'faturas_vencendo_hoje' => count($faturas_vencendo_hoje),
                'valor_vencido' => $cliente['valor_vencido'],
                'valor_a_vencer' => $cliente['valor_a_vencer'],
                'estrategia' => $estrategia,
                'mensagens_agendadas' => 1,
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
    echo "<h3>✅ Monitoramento Manual Otimizado Concluído</h3>";
    echo "<p><strong>Clientes processados:</strong> $clientes_processados</p>";
    echo "<p><strong>Mensagens agendadas:</strong> $mensagens_agendadas</p>";
    echo "<p><strong>Erros:</strong> $erros</p>";
    echo "<p><strong>Média de faturas por mensagem:</strong> " . ($mensagens_agendadas > 0 ? round(array_sum(array_column(array_filter($log_detalhado, function($l) { return $l['status'] === 'sucesso'; }), 'faturas_total')) / $mensagens_agendadas, 1) : 0) . "</p>";
    echo "</div>";
    
    // 4. Detalhamento dos resultados
    if (!empty($log_detalhado)) {
        echo "<h3>📋 Detalhamento por Cliente</h3>";
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: left;'>Cliente</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>ID</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Faturas Total</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Vencidas</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>A Vencer</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Vencendo Hoje</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Estratégia</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Mensagens</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Status</th>";
        echo "</tr>";
        
        foreach ($log_detalhado as $log) {
            $status_color = $log['status'] === 'sucesso' ? 'green' : 'red';
            $status_text = $log['status'] === 'sucesso' ? '✅ Sucesso' : '❌ Erro';
            
            echo "<tr>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>{$log['cliente']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$log['cliente_id']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['faturas_total'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['faturas_vencidas'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['faturas_a_vencer'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['faturas_vencendo_hoje'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['estrategia'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . ($log['mensagens_agendadas'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center; color: $status_color;'>$status_text</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Verificação final
    echo "<h2>🔍 4. Verificação Final</h2>";
    
    $sql_verificacao = "SELECT COUNT(*) as total FROM clientes_monitoramento WHERE monitorado = 1";
    $result_verificacao = $mysqli->query($sql_verificacao);
    $verificacao = $result_verificacao->fetch_assoc();
    
    echo "<p><strong>Total de clientes monitorados manualmente:</strong> {$verificacao['total']}</p>";
    
    if ($mensagens_agendadas > 0) {
        echo "<p style='color: green;'>✅ Monitoramento manual otimizado implementado com sucesso!</p>";
        echo "<p><strong>Características do sistema:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Funciona apenas para clientes monitorados manualmente</li>";
        echo "<li>✅ Sem duplicidade de mensagens</li>";
        echo "<li>✅ Sincronização individual de clientes</li>";
        echo "<li>✅ Mensagens no dia do vencimento incluindo faturas vencidas</li>";
        echo "<li>✅ Todas as faturas em uma única mensagem</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Nenhuma mensagem foi agendada (todos os clientes já possuem mensagens agendadas).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Monitoramento manual otimizado concluído em " . date('d/m/Y H:i:s') . "</em></p>";

/**
 * Determina a estratégia de envio baseada nas faturas (manual)
 */
function determinarEstrategiaEnvioManual($faturas_vencidas, $faturas_a_vencer, $faturas_vencendo_hoje) {
    $total_vencidas = count($faturas_vencidas);
    $total_a_vencer = count($faturas_a_vencer);
    $total_vencendo_hoje = count($faturas_vencendo_hoje);
    
    if ($total_vencendo_hoje > 0) {
        return 'vencendo_hoje';
    } elseif ($total_vencidas > 0 && $total_a_vencer > 0) {
        return 'mista';
    } elseif ($total_vencidas > 0) {
        return 'vencidas';
    } elseif ($total_a_vencer > 0) {
        return 'a_vencer';
    } else {
        return 'outro';
    }
}

/**
 * Calcula o horário de envio baseado na estratégia (manual)
 */
function calcularHorarioEnvioManual($estrategia) {
    switch ($estrategia) {
        case 'vencendo_hoje':
            return date('Y-m-d H:i:s', strtotime('+2 hours')); // Em 2 horas
        case 'vencidas':
            return date('Y-m-d H:i:s', strtotime('+1 day 10:00:00')); // Amanhã 10:00
        case 'a_vencer':
            return date('Y-m-d H:i:s', strtotime('+1 day 14:00:00')); // Amanhã 14:00
        case 'mista':
            return date('Y-m-d H:i:s', strtotime('+1 day 16:00:00')); // Amanhã 16:00
        default:
            return date('Y-m-d H:i:s', strtotime('+1 day 10:00:00')); // Amanhã 10:00
    }
}

/**
 * Determina a prioridade baseada na estratégia (manual)
 */
function determinarPrioridadeManual($estrategia) {
    switch ($estrategia) {
        case 'vencendo_hoje':
            return 'urgente';
        case 'vencidas':
            return 'alta';
        case 'mista':
            return 'alta';
        case 'a_vencer':
            return 'normal';
        default:
            return 'baixa';
    }
}

/**
 * Monta mensagem completa com todas as faturas
 */
function montarMensagemCompleta($faturas, $cliente_info) {
    $nome = $cliente_info['contact_name'] ?: $cliente_info['nome'];
    
    $mensagem = "Olá {$nome}! \n\n";
    
    // Separar faturas por tipo
    $faturas_vencidas = array_filter($faturas, function($f) { return $f['tipo_fatura'] === 'vencida'; });
    $faturas_a_vencer = array_filter($faturas, function($f) { return $f['tipo_fatura'] === 'a_vencer'; });
    
    $valor_total_vencido = 0;
    $valor_total_a_vencer = 0;
    
    // Seção de faturas vencidas
    if (!empty($faturas_vencidas)) {
        $mensagem .= "⚠️ Faturas VENCIDAS:\n\n";
        
        foreach ($faturas_vencidas as $fatura) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            $vencimento = date('d/m/Y', strtotime($fatura['vencimento']));
            $dias_vencida = intval($fatura['dias_vencido']);
            
            $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor\n";
            $mensagem .= "  Venceu em $vencimento ({$dias_vencida} dias vencida)\n";
            $mensagem .= "  🔗 {$fatura['url_fatura']}\n\n";
            
            $valor_total_vencido += floatval($fatura['valor']);
        }
        
        $mensagem .= "💰 Total vencido: R$ " . number_format($valor_total_vencido, 2, ',', '.') . "\n\n";
    }
    
    // Seção de faturas a vencer
    if (!empty($faturas_a_vencer)) {
        $mensagem .= "📅 Faturas A VENCER:\n\n";
        
        foreach ($faturas_a_vencer as $fatura) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            $vencimento = date('d/m/Y', strtotime($fatura['vencimento']));
            $dias_para_vencer = intval($fatura['dias_para_vencer']);
            
            $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor\n";
            $mensagem .= "  Vence em $vencimento (em {$dias_para_vencer} dias)\n";
            $mensagem .= "  🔗 {$fatura['url_fatura']}\n\n";
            
            $valor_total_a_vencer += floatval($fatura['valor']);
        }
        
        $mensagem .= "💰 Total a vencer: R$ " . number_format($valor_total_a_vencer, 2, ',', '.') . "\n\n";
    }
    
    // Resumo final
    $valor_total_geral = $valor_total_vencido + $valor_total_a_vencer;
    $mensagem .= "📊 RESUMO GERAL:\n";
    $mensagem .= "• Total de faturas: " . count($faturas) . "\n";
    $mensagem .= "• Faturas vencidas: " . count($faturas_vencidas) . "\n";
    $mensagem .= "• Faturas a vencer: " . count($faturas_a_vencer) . "\n";
    $mensagem .= "• Valor total: R$ " . number_format($valor_total_geral, 2, ',', '.') . "\n\n";
    
    $mensagem .= "Para consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\n";
    $mensagem .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
    
    return $mensagem;
}

/**
 * Agenda uma mensagem única (evita duplicidade)
 */
function agendarMensagemUnica($cliente_id, $mensagem, $horario_envio, $prioridade, $mysqli) {
    try {
        // Verificar se já existe mensagem agendada para este cliente
        $sql_verificar = "SELECT id FROM mensagens_agendadas 
                         WHERE cliente_id = $cliente_id 
                         AND status = 'agendada' 
                         AND data_agendada > NOW()";
        
        $result_verificar = $mysqli->query($sql_verificar);
        
        if ($result_verificar && $result_verificar->num_rows > 0) {
            return false; // Já existe mensagem agendada
        }
        
        $mensagem_escaped = $mysqli->real_escape_string($mensagem);
        $horario_escaped = $mysqli->real_escape_string($horario_envio);
        $prioridade_escaped = $mysqli->real_escape_string($prioridade);
        
        $sql = "INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                VALUES ($cliente_id, '$mensagem_escaped', 'cobranca_completa', '$prioridade_escaped', '$horario_escaped', 'agendada', NOW())";
        
        if ($mysqli->query($sql)) {
            return true;
        } else {
            error_log("Erro ao agendar mensagem única: " . $mysqli->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao agendar mensagem única: " . $e->getMessage());
        return false;
    }
}
?> 