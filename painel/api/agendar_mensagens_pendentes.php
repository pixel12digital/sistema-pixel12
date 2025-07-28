<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

try {
    // Buscar clientes monitorados que não têm mensagens agendadas
    $sql = "SELECT DISTINCT 
                cm.cliente_id,
                c.nome as cliente_nome,
                c.celular,
                c.contact_name,
                COUNT(cob.id) as total_faturas_vencidas,
                SUM(cob.valor) as valor_total_vencido
            FROM clientes_monitoramento cm
            JOIN clientes c ON cm.cliente_id = c.id
            JOIN cobrancas cob ON c.id = cob.cliente_id
            LEFT JOIN mensagens_agendadas ma ON cm.cliente_id = ma.cliente_id AND ma.status = 'agendada'
            WHERE cm.monitorado = 1
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.vencimento < CURDATE()
            AND ma.id IS NULL
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY cm.cliente_id, c.nome, c.celular, c.contact_name
            HAVING total_faturas_vencidas > 0";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar clientes: " . $mysqli->error);
    }
    
    $clientes_processados = 0;
    $mensagens_agendadas = 0;
    $erros = 0;
    
    while ($cliente = $result->fetch_assoc()) {
        $clientes_processados++;
        
        try {
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
                continue;
            }
            
            $faturas = [];
            while ($fatura = $result_faturas->fetch_assoc()) {
                $faturas[] = $fatura;
            }
            
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
                }
            }
            
            // 2. Faturas vencidas médias (8-30 dias) - enviar em 3 dias
            if (!empty($faturas_vencidas_medias)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_medias, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+3 days 14:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'normal', $mysqli)) {
                    $agendamentos_cliente++;
                }
            }
            
            // 3. Faturas vencidas antigas (mais de 30 dias) - enviar em 7 dias
            if (!empty($faturas_vencidas_antigas)) {
                $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_antigas, $cliente);
                $horario_envio = date('Y-m-d H:i:s', strtotime('+7 days 16:00:00'));
                
                if (agendarMensagem($cliente['cliente_id'], $mensagem, $horario_envio, 'baixa', $mysqli)) {
                    $agendamentos_cliente++;
                }
            }
            
            $mensagens_agendadas += $agendamentos_cliente;
            
            // Log do agendamento
            $log_data = date('Y-m-d H:i:s') . " - Cliente {$cliente['cliente_nome']} (ID: {$cliente['cliente_id']}): $agendamentos_cliente mensagens agendadas para " . count($faturas) . " faturas vencidas\n";
            file_put_contents('../logs/agendamento_mensagens.log', $log_data, FILE_APPEND);
            
        } catch (Exception $e) {
            $erros++;
            error_log("Erro ao processar cliente {$cliente['cliente_nome']}: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Agendamento de mensagens pendentes concluído',
        'clientes_processados' => $clientes_processados,
        'mensagens_agendadas' => $mensagens_agendadas,
        'erros' => $erros
    ]);

} catch (Exception $e) {
    error_log("Erro ao agendar mensagens pendentes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Monta mensagem de cobrança vencida
 */
function montarMensagemCobrancaVencida($faturas, $cliente_info) {
    $nome = $cliente_info['contact_name'] ?: $cliente_info['cliente_nome'];
    
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