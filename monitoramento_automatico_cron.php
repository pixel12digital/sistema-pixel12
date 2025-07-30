<?php
/**
 * Monitoramento Automático - Script Cron
 * Executar via cron: 0 */6 * * * php C:\xampp\htdocs\loja-virtual-revenda/monitoramento_automatico_cron.php
 * 
 * Este script executa a cada 6 horas para adicionar automaticamente
 * clientes com cobranças vencidas ao monitoramento
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/painel/db.php';

try {
    // Buscar clientes com cobranças vencidas que não estão monitorados
    $sql = "SELECT DISTINCT 
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
            HAVING cobrancas_vencidas > 0";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar clientes: " . $mysqli->error);
    }
    
    $clientes_processados = 0;
    $clientes_adicionados = 0;
    
    while ($cliente = $result->fetch_assoc()) {
        $clientes_processados++;
        
        try {
            // Adicionar ao monitoramento
            $sql_adicionar = "INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                              VALUES ({$cliente['cliente_id']}, 1, NOW(), NOW())";
            
            if ($mysqli->query($sql_adicionar)) {
                $clientes_adicionados++;
                
                // Buscar faturas vencidas para agendar mensagens
                $sql_faturas = "SELECT id, valor, vencimento, url_fatura, status, DATEDIFF(CURDATE(), vencimento) as dias_vencido
                                FROM cobrancas 
                                WHERE cliente_id = {$cliente['cliente_id']}
                                AND status IN ('PENDING', 'OVERDUE')
                                AND vencimento < CURDATE()
                                ORDER BY vencimento ASC";
                
                $result_faturas = $mysqli->query($sql_faturas);
                
                if ($result_faturas && $result_faturas->num_rows > 0) {
                    $faturas = [];
                    while ($fatura = $result_faturas->fetch_assoc()) {
                        $faturas[] = $fatura;
                    }
                    
                    // Agrupar faturas por estratégia
                    $faturas_recentes = array_filter($faturas, function($f) { return $f['dias_vencido'] <= 7; });
                    $faturas_medias = array_filter($faturas, function($f) { return $f['dias_vencido'] > 7 && $f['dias_vencido'] <= 30; });
                    $faturas_antigas = array_filter($faturas, function($f) { return $f['dias_vencido'] > 30; });
                    
                    // Agendar mensagens
                    if (!empty($faturas_recentes)) {
                        $mensagem = montarMensagemCobrancaVencida($faturas_recentes, $cliente);
                        $horario = date('Y-m-d H:i:s', strtotime('+1 day 10:00:00'));
                        agendarMensagem($cliente['cliente_id'], $mensagem, $horario, 'alta', $mysqli);
                    }
                    
                    if (!empty($faturas_medias)) {
                        $mensagem = montarMensagemCobrancaVencida($faturas_medias, $cliente);
                        $horario = date('Y-m-d H:i:s', strtotime('+3 days 14:00:00'));
                        agendarMensagem($cliente['cliente_id'], $mensagem, $horario, 'normal', $mysqli);
                    }
                    
                    if (!empty($faturas_antigas)) {
                        $mensagem = montarMensagemCobrancaVencida($faturas_antigas, $cliente);
                        $horario = date('Y-m-d H:i:s', strtotime('+7 days 16:00:00'));
                        agendarMensagem($cliente['cliente_id'], $mensagem, $horario, 'baixa', $mysqli);
                    }
                }
                
                // Log do sucesso
                $log_data = date('Y-m-d H:i:s') . " - CRON: Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}) adicionado ao monitoramento automático\n";
                file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', $log_data, FILE_APPEND);
            }
            
        } catch (Exception $e) {
            $log_erro = date('Y-m-d H:i:s') . " - CRON ERRO: Cliente {$cliente['nome']}: " . $e->getMessage() . "\n";
            file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', $log_erro, FILE_APPEND);
        }
    }
    
    // Log final
    $log_final = date('Y-m-d H:i:s') . " - CRON FINALIZADO: $clientes_processados processados, $clientes_adicionados adicionados\n";
    file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', $log_final, FILE_APPEND);
    
} catch (Exception $e) {
    $log_erro = date('Y-m-d H:i:s') . " - CRON ERRO GERAL: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/painel/logs/monitoramento_automatico_cron.log', $log_erro, FILE_APPEND);
}

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

function agendarMensagem($cliente_id, $mensagem, $horario_envio, $prioridade, $mysqli) {
    try {
        $mensagem_escaped = $mysqli->real_escape_string($mensagem);
        $horario_escaped = $mysqli->real_escape_string($horario_envio);
        $prioridade_escaped = $mysqli->real_escape_string($prioridade);
        
        $sql = "INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                VALUES ($cliente_id, '$mensagem_escaped', 'cobranca_vencida', '$prioridade_escaped', '$horario_escaped', 'agendada', NOW())";
        
        return $mysqli->query($sql);
    } catch (Exception $e) {
        return false;
    }
}
?>