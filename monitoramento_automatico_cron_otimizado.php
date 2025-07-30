<?php
/**
 * Monitoramento Automático Otimizado - Script Cron
 * Executar via cron: 0 */6 * * * php C:\xampp\htdocs\loja-virtual-revenda/monitoramento_automatico_cron_otimizado.php
 * 
 * Este script executa a cada 6 horas para adicionar automaticamente
 * clientes com cobranças ao monitoramento (sem duplicidade)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/painel/db.php';

try {
    // Buscar clientes com cobranças que não estão monitorados
    $sql = "SELECT DISTINCT 
                c.id as cliente_id,
                c.nome,
                c.celular,
                c.contact_name,
                COUNT(cob.id) as total_cobrancas,
                COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                COUNT(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 1 END) as cobrancas_a_vencer,
                SUM(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN cob.valor ELSE 0 END) as valor_vencido,
                SUM(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN cob.valor ELSE 0 END) as valor_a_vencer
            FROM clientes c
            JOIN cobrancas cob ON c.id = cob.cliente_id
            LEFT JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            WHERE cob.status IN ('PENDING', 'OVERDUE')
            AND (cm.cliente_id IS NULL OR cm.monitorado = 0)
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY c.id, c.nome, c.celular, c.contact_name
            HAVING (cobrancas_vencidas > 0 OR cobrancas_a_vencer > 0)";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar clientes: " . $mysqli->error);
    }
    
    $clientes_processados = 0;
    $clientes_adicionados = 0;
    $mensagens_agendadas = 0;
    
    while ($cliente = $result->fetch_assoc()) {
        $clientes_processados++;
        
        try {
            // Adicionar ao monitoramento
            $sql_adicionar = "INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                              VALUES ({$cliente['cliente_id']}, 1, NOW(), NOW())
                              ON DUPLICATE KEY UPDATE monitorado = 1, data_atualizacao = NOW()";
            
            if ($mysqli->query($sql_adicionar)) {
                $clientes_adicionados++;
                
                // Verificar se já existe mensagem agendada
                $sql_verificar = "SELECT id FROM mensagens_agendadas 
                                 WHERE cliente_id = {$cliente['cliente_id']} 
                                 AND status = 'agendada' 
                                 AND data_agendada > NOW()";
                
                $result_verificar = $mysqli->query($sql_verificar);
                
                if ($result_verificar && $result_verificar->num_rows > 0) {
                    continue; // Já existe mensagem agendada
                }
                
                // Buscar todas as faturas do cliente
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
                
                if ($result_faturas && $result_faturas->num_rows > 0) {
                    $faturas = [];
                    $faturas_vencidas = [];
                    $faturas_a_vencer = [];
                    
                    while ($fatura = $result_faturas->fetch_assoc()) {
                        $faturas[] = $fatura;
                        
                        if ($fatura['tipo_fatura'] === 'vencida') {
                            $faturas_vencidas[] = $fatura;
                        } elseif ($fatura['tipo_fatura'] === 'a_vencer') {
                            $faturas_a_vencer[] = $fatura;
                        }
                    }
                    
                    // Determinar estratégia e agendar mensagem única
                    $estrategia = determinarEstrategiaEnvio($faturas_vencidas, $faturas_a_vencer);
                    $mensagem = montarMensagemCompleta($faturas, $cliente);
                    $horario = calcularHorarioEnvio($estrategia);
                    $prioridade = determinarPrioridade($estrategia);
                    
                    if (agendarMensagemUnica($cliente['cliente_id'], $mensagem, $horario, $prioridade, $mysqli)) {
                        $mensagens_agendadas++;
                    }
                }
                
                // Log do sucesso
                $log_data = date('Y-m-d H:i:s') . " - CRON OTIMIZADO: Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}) adicionado ao monitoramento com 1 mensagem para " . count($faturas) . " faturas\n";
                file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron_otimizado.log', $log_data, FILE_APPEND);
            }
            
        } catch (Exception $e) {
            $log_erro = date('Y-m-d H:i:s') . " - CRON OTIMIZADO ERRO: Cliente {$cliente['nome']}: " . $e->getMessage() . "\n";
            file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron_otimizado.log', $log_erro, FILE_APPEND);
        }
    }
    
    // Log final
    $log_final = date('Y-m-d H:i:s') . " - CRON OTIMIZADO FINALIZADO: $clientes_processados processados, $clientes_adicionados adicionados, $mensagens_agendadas mensagens\n";
    file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron_otimizado.log', $log_final, FILE_APPEND);
    
} catch (Exception $e) {
    $log_erro = date('Y-m-d H:i:s') . " - CRON OTIMIZADO ERRO GERAL: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron_otimizado.log', $log_erro, FILE_APPEND);
}

function determinarEstrategiaEnvio($faturas_vencidas, $faturas_a_vencer) {
    $total_vencidas = count($faturas_vencidas);
    $total_a_vencer = count($faturas_a_vencer);
    
    if ($total_vencidas > 0 && $total_a_vencer > 0) {
        return 'mista';
    } elseif ($total_vencidas > 0) {
        return 'vencidas';
    } elseif ($total_a_vencer > 0) {
        return 'a_vencer';
    } else {
        return 'outro';
    }
}

function calcularHorarioEnvio($estrategia) {
    switch ($estrategia) {
        case 'vencidas':
            return date('Y-m-d H:i:s', strtotime('+1 day 10:00:00'));
        case 'a_vencer':
            return date('Y-m-d H:i:s', strtotime('+2 days 14:00:00'));
        case 'mista':
            return date('Y-m-d H:i:s', strtotime('+1 day 16:00:00'));
        default:
            return date('Y-m-d H:i:s', strtotime('+3 days 10:00:00'));
    }
}

function determinarPrioridade($estrategia) {
    switch ($estrategia) {
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

function agendarMensagemUnica($cliente_id, $mensagem, $horario_envio, $prioridade, $mysqli) {
    try {
        // Verificar se já existe mensagem agendada
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
        
        return $mysqli->query($sql);
    } catch (Exception $e) {
        return false;
    }
}
?>