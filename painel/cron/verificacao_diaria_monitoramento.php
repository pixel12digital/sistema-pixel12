<?php
/**
 * Verificação Diária Automática do Sistema de Monitoramento
 * Executar via cron: 0 8 * * * php /caminho/para/painel/cron/verificacao_diaria_monitoramento.php
 * 
 * Este script executa diariamente às 8h para verificar se o sistema está funcionando corretamente
 */

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Função para log
function logVerificacao($mensagem) {
    $log_data = date('Y-m-d H:i:s') . " - " . $mensagem . "\n";
    file_put_contents(__DIR__ . '/../logs/verificacao_diaria.log', $log_data, FILE_APPEND);
}

// Função para enviar alerta por email (opcional)
function enviarAlerta($assunto, $mensagem) {
    // Implementar envio de email se necessário
    logVerificacao("ALERTA: $assunto - $mensagem");
}

echo "<h1>🔍 Verificação Diária do Sistema de Monitoramento</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

logVerificacao("Iniciando verificação diária do sistema de monitoramento");

try {
    $problemas_encontrados = [];
    $status_geral = "OK";
    
    // 1. Verificar clientes monitorados sem mensagens agendadas
    echo "<h2>📊 1. Verificando Clientes Monitorados</h2>";
    
    $sql_monitorados = "SELECT 
                            cm.cliente_id,
                            c.nome,
                            c.celular,
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
                        GROUP BY cm.cliente_id, c.nome, c.celular
                        HAVING (faturas_vencidas > 0 OR faturas_a_vencer > 0)
                        ORDER BY total_faturas DESC";
    
    $result_monitorados = $mysqli->query($sql_monitorados);
    
    if (!$result_monitorados) {
        throw new Exception("Erro ao verificar clientes monitorados: " . $mysqli->error);
    }
    
    $clientes_monitorados = [];
    while ($row = $result_monitorados->fetch_assoc()) {
        $clientes_monitorados[] = $row;
    }
    
    echo "<p><strong>Total de clientes monitorados com faturas:</strong> " . count($clientes_monitorados) . "</p>";
    
    // 2. Verificar mensagens agendadas para cada cliente
    echo "<h2>📋 2. Verificando Mensagens Agendadas</h2>";
    
    $clientes_sem_mensagens = [];
    $mensagens_problematicas = [];
    
    foreach ($clientes_monitorados as $cliente) {
        // Verificar se tem mensagem agendada
        $sql_mensagem = "SELECT id, tipo, data_agendada, status 
                        FROM mensagens_agendadas 
                        WHERE cliente_id = {$cliente['cliente_id']} 
                        AND status = 'agendada' 
                        AND data_agendada > NOW()
                        ORDER BY data_agendada ASC";
        
        $result_mensagem = $mysqli->query($sql_mensagem);
        $mensagem = $result_mensagem->fetch_assoc();
        
        if (!$mensagem) {
            $clientes_sem_mensagens[] = $cliente;
            $problemas_encontrados[] = "Cliente {$cliente['nome']} (ID: {$cliente['cliente_id']}) não tem mensagem agendada";
        } else {
            // Verificar se a mensagem contém todas as faturas
            $sql_faturas = "SELECT COUNT(*) as total FROM cobrancas 
                           WHERE cliente_id = {$cliente['cliente_id']} 
                           AND status IN ('PENDING', 'OVERDUE')";
            $result_faturas = $mysqli->query($sql_faturas);
            $faturas_count = $result_faturas->fetch_assoc()['total'];
            
            // Contar "Fatura #" na mensagem
            $sql_mensagem_texto = "SELECT mensagem FROM mensagens_agendadas WHERE id = {$mensagem['id']}";
            $result_texto = $mysqli->query($sql_mensagem_texto);
            $mensagem_texto = $result_texto->fetch_assoc()['mensagem'];
            $faturas_na_mensagem = substr_count($mensagem_texto, 'Fatura #');
            
            if ($faturas_na_mensagem != $faturas_count) {
                $mensagens_problematicas[] = [
                    'cliente' => $cliente['nome'],
                    'cliente_id' => $cliente['cliente_id'],
                    'faturas_banco' => $faturas_count,
                    'faturas_mensagem' => $faturas_na_mensagem,
                    'tipo_mensagem' => $mensagem['tipo']
                ];
                $problemas_encontrados[] = "Cliente {$cliente['nome']} tem {$faturas_count} faturas mas mensagem só menciona {$faturas_na_mensagem}";
            }
        }
    }
    
    echo "<p><strong>Clientes sem mensagens agendadas:</strong> " . count($clientes_sem_mensagens) . "</p>";
    echo "<p><strong>Mensagens problemáticas:</strong> " . count($mensagens_problematicas) . "</p>";
    
    // 3. Verificar mensagens vencidas (não enviadas)
    echo "<h2>⏰ 3. Verificando Mensagens Vencidas</h2>";
    
    $sql_vencidas = "SELECT COUNT(*) as total FROM mensagens_agendadas 
                     WHERE status = 'agendada' 
                     AND data_agendada < NOW() 
                     AND data_agendada > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $result_vencidas = $mysqli->query($sql_vencidas);
    $mensagens_vencidas = $result_vencidas->fetch_assoc()['total'];
    
    echo "<p><strong>Mensagens vencidas (últimas 24h):</strong> $mensagens_vencidas</p>";
    
    if ($mensagens_vencidas > 0) {
        $problemas_encontrados[] = "Existem $mensagens_vencidas mensagens vencidas não processadas";
    }
    
    // 4. Verificar status do cron job
    echo "<h2>⚙️ 4. Verificando Status do Cron Job</h2>";
    
    $cron_log_file = __DIR__ . '/../logs/processamento_agendadas.log';
    $cron_ok = false;
    
    if (file_exists($cron_log_file)) {
        $ultima_execucao = filemtime($cron_log_file);
        $tempo_desde_execucao = time() - $ultima_execucao;
        
        if ($tempo_desde_execucao < 3600) { // Menos de 1 hora
            $cron_ok = true;
            echo "<p style='color: green;'>✅ Cron job executado recentemente (" . date('H:i:s', $ultima_execucao) . ")</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Cron job não executado recentemente (última execução: " . date('d/m/Y H:i:s', $ultima_execucao) . ")</p>";
            $problemas_encontrados[] = "Cron job não executado nas últimas horas";
        }
    } else {
        echo "<p style='color: red;'>❌ Arquivo de log do cron job não encontrado</p>";
        $problemas_encontrados[] = "Arquivo de log do cron job não existe";
    }
    
    // 5. Verificar conectividade com Asaas
    echo "<h2>🔗 5. Verificando Conectividade com Asaas</h2>";
    
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    if ($config && $config['valor']) {
        $api_key = $config['valor'];
        
        // Teste simples de conectividade
        $ch = curl_init("https://www.asaas.com/api/v3/payments");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'access_token: ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "<p style='color: green;'>✅ Conectividade com Asaas OK</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro na conectividade com Asaas (HTTP $http_code)</p>";
            $problemas_encontrados[] = "Erro na conectividade com Asaas (HTTP $http_code)";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ API Key do Asaas não configurada</p>";
        $problemas_encontrados[] = "API Key do Asaas não configurada";
    }
    
    // 6. Relatório final
    echo "<h2>📊 6. Relatório Final</h2>";
    
    if (empty($problemas_encontrados)) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ SISTEMA FUNCIONANDO PERFEITAMENTE</h3>";
        echo "<p><strong>Status:</strong> Todos os sistemas estão operacionais</p>";
        echo "<p><strong>Clientes monitorados:</strong> " . count($clientes_monitorados) . "</p>";
        echo "<p><strong>Mensagens agendadas:</strong> " . (count($clientes_monitorados) - count($clientes_sem_mensagens)) . "</p>";
        echo "<p><strong>Cron job:</strong> Funcionando</p>";
        echo "<p><strong>Asaas:</strong> Conectado</p>";
        echo "</div>";
        
        logVerificacao("Verificação concluída: SISTEMA OK");
    } else {
        $status_geral = "PROBLEMAS";
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>⚠️ PROBLEMAS ENCONTRADOS</h3>";
        echo "<p><strong>Total de problemas:</strong> " . count($problemas_encontrados) . "</p>";
        echo "<ul>";
        foreach ($problemas_encontrados as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // Enviar alerta
        enviarAlerta("Problemas no Sistema de Monitoramento", implode("\n", $problemas_encontrados));
        logVerificacao("Verificação concluída: PROBLEMAS ENCONTRADOS - " . implode("; ", $problemas_encontrados));
    }
    
    // 7. Estatísticas gerais
    echo "<h2>📈 7. Estatísticas Gerais</h2>";
    
    $sql_stats = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN monitorado = 1 THEN 1 END) as monitorados,
                    COUNT(CASE WHEN monitorado = 0 THEN 1 END) as nao_monitorados
                  FROM clientes_monitoramento";
    $result_stats = $mysqli->query($sql_stats);
    $stats = $result_stats->fetch_assoc();
    
    $sql_mensagens_stats = "SELECT 
                              COUNT(*) as total_mensagens,
                              COUNT(CASE WHEN status = 'agendada' THEN 1 END) as agendadas,
                              COUNT(CASE WHEN status = 'enviada' THEN 1 END) as enviadas,
                              COUNT(CASE WHEN status = 'cancelada' THEN 1 END) as canceladas
                            FROM mensagens_agendadas";
    $result_mensagens_stats = $mysqli->query($sql_mensagens_stats);
    $mensagens_stats = $result_mensagens_stats->fetch_assoc();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th colspan='2'>Estatísticas do Sistema</th></tr>";
    echo "<tr><td>Total de clientes no monitoramento</td><td>{$stats['total_clientes']}</td></tr>";
    echo "<tr><td>Clientes monitorados ativos</td><td>{$stats['monitorados']}</td></tr>";
    echo "<tr><td>Clientes não monitorados</td><td>{$stats['nao_monitorados']}</td></tr>";
    echo "<tr><td>Total de mensagens</td><td>{$mensagens_stats['total_mensagens']}</td></tr>";
    echo "<tr><td>Mensagens agendadas</td><td>{$mensagens_stats['agendadas']}</td></tr>";
    echo "<tr><td>Mensagens enviadas</td><td>{$mensagens_stats['enviadas']}</td></tr>";
    echo "<tr><td>Mensagens canceladas</td><td>{$mensagens_stats['canceladas']}</td></tr>";
    echo "</table>";
    
    // 8. Salvar relatório no banco (comentado temporariamente)
    /*
    $relatorio = [
        'data_verificacao' => date('Y-m-d H:i:s'),
        'status_geral' => $status_geral,
        'total_clientes_monitorados' => count($clientes_monitorados),
        'clientes_sem_mensagens' => count($clientes_sem_mensagens),
        'mensagens_problematicas' => count($mensagens_problematicas),
        'mensagens_vencidas' => $mensagens_vencidas,
        'cron_ok' => $cron_ok ? 1 : 0,
        'problemas_encontrados' => json_encode($problemas_encontrados)
    ];
    
    $sql_insert = "INSERT INTO relatorios_verificacao 
                   (data_verificacao, status_geral, total_clientes_monitorados, clientes_sem_mensagens, 
                    mensagens_problematicas, mensagens_vencidas, cron_ok, problemas_encontrados) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($sql_insert);
    $stmt->bind_param("ssiiiss", 
        $relatorio['data_verificacao'],
        $relatorio['status_geral'],
        $relatorio['total_clientes_monitorados'],
        $relatorio['clientes_sem_mensagens'],
        $relatorio['mensagens_problematicas'],
        $relatorio['mensagens_vencidas'],
        $relatorio['cron_ok'],
        $relatorio['problemas_encontrados']
    );
    $stmt->execute();
    */
    
    // Log do resultado
    logVerificacao("Verificação concluída - Status: $status_geral - Clientes: " . count($clientes_monitorados) . " - Problemas: " . count($problemas_encontrados));
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na verificação: " . $e->getMessage() . "</p>";
    logVerificacao("ERRO na verificação: " . $e->getMessage());
    enviarAlerta("Erro na Verificação Diária", $e->getMessage());
}

echo "<hr>";
echo "<p><em>Verificação concluída em " . date('d/m/Y H:i:s') . "</em></p>";
logVerificacao("Verificação diária finalizada");
?> 