<?php
/**
 * Verificar Faturas do Wilmar
 * Confirma se há mais de uma fatura em aberto
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔍 Verificar Faturas do Wilmar</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // Buscar dados do Wilmar
    $sql_wilmar = "SELECT id, nome, celular, contact_name FROM clientes WHERE nome LIKE '%WILMAR AUGUSTO IBERS%' LIMIT 1";
    $result_wilmar = $mysqli->query($sql_wilmar);
    
    if (!$result_wilmar || $result_wilmar->num_rows == 0) {
        echo "<p style='color: red;'>❌ Cliente Wilmar não encontrado!</p>";
        return;
    }
    
    $wilmar = $result_wilmar->fetch_assoc();
    
    echo "<h2>👤 Dados do Cliente</h2>";
    echo "<p><strong>ID:</strong> {$wilmar['id']}</p>";
    echo "<p><strong>Nome:</strong> {$wilmar['nome']}</p>";
    echo "<p><strong>Celular:</strong> {$wilmar['celular']}</p>";
    echo "<p><strong>Contact Name:</strong> {$wilmar['contact_name']}</p>";
    
    // Buscar TODAS as faturas do Wilmar
    $sql_faturas = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.vencimento,
                        cob.url_fatura,
                        cob.status,
                        cob.asaas_payment_id,
                        DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido,
                        DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer,
                        CASE 
                            WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 'vencida'
                            WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 'a_vencer'
                            ELSE 'outro'
                        END as tipo_fatura
                    FROM cobrancas cob
                    WHERE cob.cliente_id = {$wilmar['id']}
                    AND cob.status IN ('PENDING', 'OVERDUE')
                    ORDER BY cob.vencimento ASC";
    
    $result_faturas = $mysqli->query($sql_faturas);
    
    if (!$result_faturas) {
        throw new Exception("Erro ao buscar faturas: " . $mysqli->error);
    }
    
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
    
    echo "<h2>📊 Faturas Encontradas</h2>";
    echo "<p><strong>Total de faturas:</strong> " . count($faturas) . "</p>";
    echo "<p><strong>Faturas vencidas:</strong> " . count($faturas_vencidas) . "</p>";
    echo "<p><strong>Faturas a vencer:</strong> " . count($faturas_a_vencer) . "</p>";
    
    if (empty($faturas)) {
        echo "<p style='color: orange;'>⚠️ Nenhuma fatura encontrada para o Wilmar!</p>";
        return;
    }
    
    // Mostrar detalhes de todas as faturas
    echo "<h2>📋 Detalhes das Faturas</h2>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>ID</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Valor</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Vencimento</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Status</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Tipo</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Dias</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Asaas ID</th>";
    echo "</tr>";
    
    foreach ($faturas as $fatura) {
        $status_color = $fatura['status'] === 'OVERDUE' ? 'red' : 'orange';
        $tipo_color = $fatura['tipo_fatura'] === 'vencida' ? 'red' : 'blue';
        
        $dias_text = '';
        if ($fatura['tipo_fatura'] === 'vencida') {
            $dias_text = $fatura['dias_vencido'] . ' dias vencida';
        } elseif ($fatura['tipo_fatura'] === 'a_vencer') {
            $dias_text = 'em ' . $fatura['dias_para_vencer'] . ' dias';
        }
        
        echo "<tr>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$fatura['id']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>R$ " . number_format($fatura['valor'], 2, ',', '.') . "</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>" . date('d/m/Y', strtotime($fatura['vencimento'])) . "</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center; color: $status_color;'>{$fatura['status']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center; color: $tipo_color;'>{$fatura['tipo_fatura']}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>$dias_text</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$fatura['asaas_payment_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar mensagem agendada
    echo "<h2>📨 Mensagem Agendada</h2>";
    
    $sql_mensagem = "SELECT 
                        id,
                        mensagem,
                        tipo,
                        prioridade,
                        data_agendada,
                        status,
                        data_criacao
                    FROM mensagens_agendadas 
                    WHERE cliente_id = {$wilmar['id']} 
                    AND status = 'agendada' 
                    AND data_agendada > NOW()
                    ORDER BY data_agendada ASC";
    
    $result_mensagem = $mysqli->query($sql_mensagem);
    
    if ($result_mensagem && $result_mensagem->num_rows > 0) {
        $mensagem = $result_mensagem->fetch_assoc();
        
        echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>Mensagem #{$mensagem['id']}</h3>";
        echo "<p><strong>Tipo:</strong> {$mensagem['tipo']}</p>";
        echo "<p><strong>Prioridade:</strong> {$mensagem['prioridade']}</p>";
        echo "<p><strong>Data Agendada:</strong> " . date('d/m/Y H:i:s', strtotime($mensagem['data_agendada'])) . "</p>";
        echo "<p><strong>Status:</strong> {$mensagem['status']}</p>";
        echo "<p><strong>Criação:</strong> " . date('d/m/Y H:i:s', strtotime($mensagem['data_criacao'])) . "</p>";
        echo "</div>";
        
        echo "<h3>Conteúdo da Mensagem:</h3>";
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0; white-space: pre-wrap; font-family: monospace;'>";
        echo htmlspecialchars($mensagem['mensagem']);
        echo "</div>";
        
        // Contar faturas mencionadas na mensagem
        $faturas_na_mensagem = substr_count($mensagem['mensagem'], 'Fatura #');
        echo "<p><strong>Faturas mencionadas na mensagem:</strong> $faturas_na_mensagem</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhuma mensagem agendada encontrada para o Wilmar!</p>";
    }
    
    // Análise do problema
    echo "<h2>🔍 Análise do Problema</h2>";
    
    if (count($faturas) > 1 && $result_mensagem && $result_mensagem->num_rows > 0) {
        $mensagem = $result_mensagem->fetch_assoc();
        $faturas_na_mensagem = substr_count($mensagem['mensagem'], 'Fatura #');
        
        if ($faturas_na_mensagem < count($faturas)) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>❌ PROBLEMA IDENTIFICADO</h3>";
            echo "<p><strong>Total de faturas no banco:</strong> " . count($faturas) . "</p>";
            echo "<p><strong>Faturas mencionadas na mensagem:</strong> $faturas_na_mensagem</p>";
            echo "<p><strong>Diferença:</strong> " . (count($faturas) - $faturas_na_mensagem) . " faturas não estão sendo incluídas na mensagem!</p>";
            echo "<p>Isso confirma que o sistema não está enviando todas as faturas em uma única mensagem consolidada.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>✅ SISTEMA FUNCIONANDO CORRETAMENTE</h3>";
            echo "<p>Todas as faturas estão sendo incluídas na mensagem.</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Verificação concluída em " . date('d/m/Y H:i:s') . "</em></p>";
?> 