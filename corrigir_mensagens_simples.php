<?php
/**
 * Corrigir Mensagens Problemáticas - Versão Simples
 * Remove mensagens antigas e re-agenda com sistema otimizado
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🛠️ Corrigir Mensagens Problemáticas</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Identificar clientes com problemas
    echo "<h2>🔍 1. Identificando Clientes com Problemas</h2>";
    
    $sql_problemas = "SELECT 
                        cm.cliente_id,
                        c.nome,
                        c.celular,
                        c.contact_name,
                        ma.id as mensagem_id,
                        ma.tipo as tipo_mensagem,
                        COUNT(cob.id) as total_faturas,
                        COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as faturas_vencidas,
                        COUNT(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 1 END) as faturas_a_vencer
                    FROM clientes_monitoramento cm
                    JOIN clientes c ON cm.cliente_id = c.id
                    JOIN mensagens_agendadas ma ON cm.cliente_id = ma.cliente_id
                    LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                    WHERE cm.monitorado = 1
                    AND ma.status = 'agendada'
                    AND ma.data_agendada > NOW()
                    AND c.celular IS NOT NULL
                    AND c.celular != ''
                    AND cob.status IN ('PENDING', 'OVERDUE')
                    GROUP BY cm.cliente_id, c.nome, c.celular, c.contact_name, ma.id, ma.tipo
                    HAVING total_faturas > 1
                    ORDER BY total_faturas DESC, c.nome ASC";
    
    $result_problemas = $mysqli->query($sql_problemas);
    
    if (!$result_problemas) {
        throw new Exception("Erro ao buscar clientes com problemas: " . $mysqli->error);
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
    
    // 2. Funções do sistema otimizado
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
                       WHERE cliente_id = $cliente_id AND status = 'agendada' AND data_agendada > NOW()";
        $mysqli->query($sql_delete);
        
        // Inserir nova mensagem otimizada
        $sql_insert = "INSERT INTO mensagens_agendadas 
                       (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                       VALUES ($cliente_id, '$mensagem', 'cobranca_completa', '$prioridade', '$horario_envio', 'agendada', NOW())";
        
        return $mysqli->query($sql_insert);
    }
    
    // 3. Processar cada cliente problemático
    echo "<h2>🔄 2. Processando Correções</h2>";
    
    $corrigidos = 0;
    $erros = 0;
    
    foreach ($clientes_problematicos as $cliente) {
        echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
        echo "<h3>Corrigindo: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
        
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
                echo "<p><strong>Mensagem removida:</strong> #{$cliente['mensagem_id']} (tipo: {$cliente['tipo_mensagem']})</p>";
                echo "<p><strong>Nova mensagem:</strong> tipo 'cobranca_completa' com " . count($faturas) . " faturas</p>";
                $corrigidos++;
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
    echo "<h3>✅ Correção Concluída</h3>";
    echo "<p><strong>Total de clientes processados:</strong> " . count($clientes_problematicos) . "</p>";
    echo "<p><strong>Clientes corrigidos:</strong> $corrigidos</p>";
    echo "<p><strong>Erros encontrados:</strong> $erros</p>";
    echo "</div>";
    
    if ($corrigidos > 0) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🎉 SUCESSO!</h3>";
        echo "<p><strong>$corrigidos clientes</strong> agora estão recebendo mensagens otimizadas com todas as faturas consolidadas.</p>";
        echo "<p><strong>Próximo passo:</strong> Execute o script de verificação novamente para confirmar a correção.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Correção concluída em " . date('d/m/Y H:i:s') . "</em></p>";
?> 