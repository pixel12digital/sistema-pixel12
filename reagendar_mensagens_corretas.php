<?php
/**
 * Re-agendar Mensagens com Datas Corretas
 * Corrige as mensagens para serem agendadas a partir de amanhã
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔄 Re-agendar Mensagens com Datas Corretas</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Identificar clientes monitorados que precisam de mensagens
    echo "<h2>🔍 1. Identificando Clientes que Precisam de Mensagens</h2>";
    
    $sql_clientes = "SELECT 
                        cm.cliente_id,
                        c.nome,
                        c.celular,
                        c.contact_name,
                        COUNT(cob.id) as total_faturas,
                        COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as faturas_vencidas,
                        COUNT(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 1 END) as faturas_a_vencer
                    FROM clientes_monitoramento cm
                    JOIN clientes c ON cm.cliente_id = c.id
                    LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                    WHERE cm.monitorado = 1
                    AND c.celular IS NOT NULL
                    AND c.celular != ''
                    AND cob.status IN ('PENDING', 'OVERDUE')
                    GROUP BY cm.cliente_id, c.nome, c.celular, c.contact_name
                    HAVING (faturas_vencidas > 0 OR faturas_a_vencer > 0)
                    ORDER BY total_faturas DESC, c.nome ASC";
    
    $result_clientes = $mysqli->query($sql_clientes);
    
    if (!$result_clientes) {
        throw new Exception("Erro ao buscar clientes: " . $mysqli->error);
    }
    
    $clientes_monitorados = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes_monitorados[] = $row;
    }
    
    echo "<p><strong>Total de clientes monitorados encontrados:</strong> " . count($clientes_monitorados) . "</p>";
    
    if (empty($clientes_monitorados)) {
        echo "<p style='color: green;'>✅ Nenhum cliente monitorado encontrado!</p>";
        return;
    }
    
    // 2. Funções do sistema otimizado (com datas corretas)
    function determinarEstrategiaEnvioManual($faturas_vencidas, $faturas_a_vencer, $faturas_vencendo_hoje) {
        if (!empty($faturas_vencendo_hoje)) {
            return 'vencendo_hoje';
        } elseif (!empty($faturas_vencidas)) {
            return 'vencidas';
        } elseif (!empty($faturas_a_vencer)) {
            return 'a_vencer';
        }
        return 'sem_faturas';
    }
    
    function calcularHorarioEnvioManual($estrategia) {
        switch ($estrategia) {
            case 'vencendo_hoje':
                return date('Y-m-d 09:00:00', strtotime('+1 day')); // Enviar amanhã às 9h
            case 'vencidas':
                return date('Y-m-d 10:00:00', strtotime('+1 day')); // Enviar amanhã às 10h
            case 'a_vencer':
                return date('Y-m-d 11:00:00', strtotime('+1 day')); // Enviar amanhã às 11h
            default:
                return date('Y-m-d 12:00:00', strtotime('+1 day'));
        }
    }
    
    function determinarPrioridadeManual($estrategia) {
        switch ($estrategia) {
            case 'vencendo_hoje':
                return 'alta';
            case 'vencidas':
                return 'alta';
            case 'a_vencer':
                return 'media';
            default:
                return 'baixa';
        }
    }
    
    function montarMensagemCompleta($faturas, $cliente_info) {
        $mensagem = "Olá {$cliente_info['contact_name']}! 👋\n\n";
        
        $faturas_vencidas = array_filter($faturas, function($f) { 
            return $f['tipo_fatura'] === 'vencida'; 
        });
        $faturas_a_vencer = array_filter($faturas, function($f) { 
            return $f['tipo_fatura'] === 'a_vencer'; 
        });
        
        if (!empty($faturas_vencidas)) {
            $mensagem .= "⚠️ Você possui " . count($faturas_vencidas) . " fatura(s) vencida(s):\n\n";
            foreach ($faturas_vencidas as $fatura) {
                $dias_vencido = $fatura['dias_vencido'];
                $mensagem .= "📄 Fatura #{$fatura['id']}\n";
                $mensagem .= "💰 Valor: R$ " . number_format($fatura['valor'], 2, ',', '.') . "\n";
                $mensagem .= "📅 Vencimento: " . date('d/m/Y', strtotime($fatura['vencimento'])) . " ({$dias_vencido} dias vencida)\n";
                $mensagem .= "🔗 Link: {$fatura['url_fatura']}\n\n";
            }
        }
        
        if (!empty($faturas_a_vencer)) {
            $mensagem .= "📋 Você possui " . count($faturas_a_vencer) . " fatura(s) a vencer:\n\n";
            foreach ($faturas_a_vencer as $fatura) {
                $dias_para_vencer = $fatura['dias_para_vencer'];
                $mensagem .= "📄 Fatura #{$fatura['id']}\n";
                $mensagem .= "💰 Valor: R$ " . number_format($fatura['valor'], 2, ',', '.') . "\n";
                $mensagem .= "📅 Vencimento: " . date('d/m/Y', strtotime($fatura['vencimento'])) . " (em {$dias_para_vencer} dias)\n";
                $mensagem .= "🔗 Link: {$fatura['url_fatura']}\n\n";
            }
        }
        
        $mensagem .= "💳 Para facilitar o pagamento, utilize os links acima.\n\n";
        $mensagem .= "📞 Em caso de dúvidas, entre em contato conosco.\n\n";
        $mensagem .= "Obrigado! 🙏";
        
        return $mensagem;
    }
    
    function agendarMensagemUnica($cliente_id, $mensagem, $horario_envio, $prioridade, $mysqli) {
        // Escapar strings para evitar SQL injection
        $cliente_id = (int)$cliente_id;
        $mensagem = $mysqli->real_escape_string($mensagem);
        $horario_envio = $mysqli->real_escape_string($horario_envio);
        $prioridade = $mysqli->real_escape_string($prioridade);
        
        // Remover mensagens antigas
        $sql_delete = "DELETE FROM mensagens_agendadas 
                       WHERE cliente_id = $cliente_id AND status = 'agendada'";
        $mysqli->query($sql_delete);
        
        // Inserir nova mensagem otimizada
        $sql_insert = "INSERT INTO mensagens_agendadas 
                       (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                       VALUES ($cliente_id, '$mensagem', 'cobranca_completa', '$prioridade', '$horario_envio', 'agendada', NOW())";
        
        return $mysqli->query($sql_insert);
    }
    
    // 3. Processar cada cliente
    echo "<h2>🔄 2. Re-agendando Mensagens</h2>";
    
    $reagendados = 0;
    $erros = 0;
    
    foreach ($clientes_monitorados as $cliente) {
        echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
        echo "<h3>Re-agendando: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
        
        try {
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
            
            if (!$result_faturas) {
                throw new Exception("Erro ao buscar faturas");
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
                    if ($fatura['dias_para_vencer'] == 0) {
                        $faturas_vencendo_hoje[] = $fatura;
                    }
                }
            }
            
            echo "<p><strong>Faturas encontradas:</strong> " . count($faturas) . "</p>";
            echo "<p><strong>Faturas vencidas:</strong> " . count($faturas_vencidas) . "</p>";
            echo "<p><strong>Faturas a vencer:</strong> " . count($faturas_a_vencer) . "</p>";
            echo "<p><strong>Faturas vencendo hoje:</strong> " . count($faturas_vencendo_hoje) . "</p>";
            
            // Determinar estratégia e horário
            $estrategia = determinarEstrategiaEnvioManual($faturas_vencidas, $faturas_a_vencer, $faturas_vencendo_hoje);
            $horario_envio = calcularHorarioEnvioManual($estrategia);
            $prioridade = determinarPrioridadeManual($estrategia);
            
            echo "<p><strong>Estratégia:</strong> $estrategia</p>";
            echo "<p><strong>Horário de envio:</strong> " . date('d/m/Y H:i:s', strtotime($horario_envio)) . "</p>";
            echo "<p><strong>Prioridade:</strong> $prioridade</p>";
            
            // Montar mensagem completa
            $mensagem_completa = montarMensagemCompleta($faturas, $cliente);
            
            // Agendar mensagem única
            $sucesso = agendarMensagemUnica($cliente['cliente_id'], $mensagem_completa, $horario_envio, $prioridade, $mysqli);
            
            if ($sucesso) {
                echo "<p style='color: green;'>✅ Mensagem re-agendada com sucesso!</p>";
                echo "<p><strong>Nova mensagem:</strong> tipo 'cobranca_completa' com " . count($faturas) . " faturas</p>";
                echo "<p><strong>Data de envio:</strong> " . date('d/m/Y H:i:s', strtotime($horario_envio)) . "</p>";
                $reagendados++;
            } else {
                throw new Exception("Erro ao agendar nova mensagem: " . $mysqli->error);
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
            $erros++;
        }
        
        echo "</div>";
    }
    
    // 4. Relatório final
    echo "<h2>📊 3. Relatório Final</h2>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Re-agendamento Concluído</h3>";
    echo "<p><strong>Total de clientes processados:</strong> " . count($clientes_monitorados) . "</p>";
    echo "<p><strong>Clientes re-agendados:</strong> $reagendados</p>";
    echo "<p><strong>Erros encontrados:</strong> $erros</p>";
    echo "</div>";
    
    if ($reagendados > 0) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🎉 SUCESSO!</h3>";
        echo "<p><strong>$reagendados clientes</strong> agora têm mensagens agendadas com datas corretas (a partir de amanhã).</p>";
        echo "<p><strong>Próximo passo:</strong> Execute o script de verificação para confirmar os agendamentos.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Re-agendamento concluído em " . date('d/m/Y H:i:s') . "</em></p>";
?> 