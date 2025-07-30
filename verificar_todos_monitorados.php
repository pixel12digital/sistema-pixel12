<?php
/**
 * Verificar Todos os Clientes Monitorados
 * Confirma se todos estão recebendo todas as faturas em uma única mensagem
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔍 Verificar Todos os Clientes Monitorados</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Buscar todos os clientes monitorados
    echo "<h2>📊 1. Clientes Monitorados</h2>";
    
    $sql_clientes = "SELECT 
                        cm.cliente_id,
                        c.nome,
                        c.celular,
                        c.contact_name,
                        COUNT(cob.id) as total_cobrancas,
                        COUNT(CASE WHEN cob.status IN ('PENDING', 'OVERDUE') AND cob.vencimento < CURDATE() THEN 1 END) as cobrancas_vencidas,
                        COUNT(CASE WHEN cob.status IN ('PENDING') AND cob.vencimento >= CURDATE() THEN 1 END) as cobrancas_a_vencer
                    FROM clientes_monitoramento cm
                    JOIN clientes c ON cm.cliente_id = c.id
                    LEFT JOIN cobrancas cob ON c.id = cob.cliente_id
                    WHERE cm.monitorado = 1
                    AND c.celular IS NOT NULL
                    AND c.celular != ''
                    GROUP BY cm.cliente_id, c.nome, c.celular, c.contact_name
                    HAVING (cobrancas_vencidas > 0 OR cobrancas_a_vencer > 0)
                    ORDER BY total_cobrancas DESC, c.nome ASC";
    
    $result_clientes = $mysqli->query($sql_clientes);
    
    if (!$result_clientes) {
        throw new Exception("Erro ao buscar clientes monitorados: " . $mysqli->error);
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
    
    // 2. Verificar cada cliente individualmente
    echo "<h2>🔍 2. Verificação Individual por Cliente</h2>";
    
    $problemas_encontrados = [];
    $clientes_ok = [];
    $total_verificados = 0;
    
    foreach ($clientes_monitorados as $cliente) {
        $total_verificados++;
        
        echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
        echo "<h3>Verificando: {$cliente['nome']} (ID: {$cliente['cliente_id']})</h3>";
        
        // Buscar TODAS as faturas do cliente
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
            echo "<p style='color: red;'>❌ Erro ao buscar faturas</p>";
            continue;
        }
        
        $faturas = [];
        while ($fatura = $result_faturas->fetch_assoc()) {
            $faturas[] = $fatura;
        }
        
        echo "<p><strong>Total de faturas no banco:</strong> " . count($faturas) . "</p>";
        echo "<p><strong>Faturas vencidas:</strong> " . count(array_filter($faturas, function($f) { return $f['tipo_fatura'] === 'vencida'; })) . "</p>";
        echo "<p><strong>Faturas a vencer:</strong> " . count(array_filter($faturas, function($f) { return $f['tipo_fatura'] === 'a_vencer'; })) . "</p>";
        
        // Verificar mensagem agendada
        $sql_mensagem = "SELECT 
                            id,
                            mensagem,
                            tipo,
                            prioridade,
                            data_agendada,
                            status,
                            data_criacao
                        FROM mensagens_agendadas 
                        WHERE cliente_id = {$cliente['cliente_id']} 
                        AND status = 'agendada' 
                        AND data_agendada > NOW()
                        ORDER BY data_agendada ASC";
        
        $result_mensagem = $mysqli->query($sql_mensagem);
        
        if ($result_mensagem && $result_mensagem->num_rows > 0) {
            $mensagem = $result_mensagem->fetch_assoc();
            
            // Contar faturas mencionadas na mensagem
            $faturas_na_mensagem = substr_count($mensagem['mensagem'], 'Fatura #');
            
            echo "<p><strong>Mensagem agendada:</strong> #{$mensagem['id']}</p>";
            echo "<p><strong>Tipo:</strong> {$mensagem['tipo']}</p>";
            echo "<p><strong>Faturas mencionadas na mensagem:</strong> $faturas_na_mensagem</p>";
            
            // Verificar se há discrepância
            if (count($faturas) > 0 && $faturas_na_mensagem < count($faturas)) {
                echo "<p style='color: red;'>❌ PROBLEMA: " . (count($faturas) - $faturas_na_mensagem) . " faturas não estão na mensagem!</p>";
                
                $problemas_encontrados[] = [
                    'cliente' => $cliente['nome'],
                    'cliente_id' => $cliente['cliente_id'],
                    'faturas_banco' => count($faturas),
                    'faturas_mensagem' => $faturas_na_mensagem,
                    'diferenca' => count($faturas) - $faturas_na_mensagem,
                    'mensagem_id' => $mensagem['id'],
                    'tipo_mensagem' => $mensagem['tipo']
                ];
            } else {
                echo "<p style='color: green;'>✅ OK: Todas as faturas estão na mensagem</p>";
                $clientes_ok[] = $cliente['nome'];
            }
            
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma mensagem agendada encontrada</p>";
        }
        
        echo "</div>";
    }
    
    // 3. Relatório final
    echo "<h2>📊 3. Relatório Final</h2>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Resumo da Verificação</h3>";
    echo "<p><strong>Total de clientes verificados:</strong> $total_verificados</p>";
    echo "<p><strong>Clientes OK:</strong> " . count($clientes_ok) . "</p>";
    echo "<p><strong>Problemas encontrados:</strong> " . count($problemas_encontrados) . "</p>";
    echo "</div>";
    
    // 4. Detalhamento dos problemas
    if (!empty($problemas_encontrados)) {
        echo "<h2>❌ 4. Problemas Encontrados</h2>";
        
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>⚠️ CLIENTES COM PROBLEMAS</h3>";
        echo "<p>Os seguintes clientes não estão recebendo todas as faturas em uma única mensagem:</p>";
        echo "</div>";
        
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: left;'>Cliente</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>ID</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Faturas no Banco</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Faturas na Mensagem</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Diferença</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Mensagem ID</th>";
        echo "<th style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>Tipo</th>";
        echo "</tr>";
        
        foreach ($problemas_encontrados as $problema) {
            echo "<tr>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px;'>{$problema['cliente']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$problema['cliente_id']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$problema['faturas_banco']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$problema['faturas_mensagem']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center; color: red;'>{$problema['diferenca']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$problema['mensagem_id']}</td>";
            echo "<td style='border: 1px solid #dee2e6; padding: 10px; text-align: center;'>{$problema['tipo_mensagem']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 5. Análise do problema
        echo "<h2>🔍 5. Análise do Problema</h2>";
        
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🔍 CAUSA PROVÁVEL</h3>";
        echo "<p>O problema parece estar relacionado ao <strong>sistema antigo de mensagens</strong> que ainda está sendo usado.</p>";
        echo "<p>Observações:</p>";
        echo "<ul>";
        echo "<li>As mensagens têm tipo <strong>'cobranca_vencida'</strong> (sistema antigo)</li>";
        echo "<li>O sistema otimizado deveria usar tipo <strong>'cobranca_completa'</strong></li>";
        echo "<li>O sistema antigo envia apenas faturas vencidas, não todas as faturas</li>";
        echo "<li>Precisamos re-agendar as mensagens com o sistema otimizado</li>";
        echo "</ul>";
        echo "</div>";
        
        // 6. Solução proposta
        echo "<h2>🛠️ 6. Solução Proposta</h2>";
        
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ AÇÃO NECESSÁRIA</h3>";
        echo "<p>Para corrigir o problema, precisamos:</p>";
        echo "<ol>";
        echo "<li><strong>Remover mensagens antigas</strong> dos clientes com problemas</li>";
        echo "<li><strong>Re-agendar mensagens</strong> usando o sistema otimizado</li>";
        echo "<li><strong>Verificar novamente</strong> se todas as faturas estão sendo incluídas</li>";
        echo "</ol>";
        echo "<p><strong>Script recomendado:</strong> Criar um script para re-agendar mensagens dos clientes problemáticos</p>";
        echo "</div>";
        
    } else {
        echo "<h2>✅ 4. Todos os Clientes Estão OK</h2>";
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🎉 SISTEMA FUNCIONANDO PERFEITAMENTE</h3>";
        echo "<p>Todos os " . count($clientes_ok) . " clientes monitorados estão recebendo todas as faturas em uma única mensagem consolidada!</p>";
        echo "</div>";
    }
    
    // 7. Lista de clientes OK
    if (!empty($clientes_ok)) {
        echo "<h2>✅ 5. Clientes Funcionando Corretamente</h2>";
        echo "<p><strong>Total:</strong> " . count($clientes_ok) . " clientes</p>";
        echo "<ul>";
        foreach ($clientes_ok as $cliente) {
            echo "<li>$cliente</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Verificação completa concluída em " . date('d/m/Y H:i:s') . "</em></p>";
?> 