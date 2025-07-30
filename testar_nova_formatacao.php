<?php
/**
 * 🧪 TESTE DA NOVA FORMATAÇÃO - FATURAS VENCIDAS E A VENCER
 * Simula a nova formatação simpática para faturas em aberto
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste da Nova Formatação - Faturas Vencidas e a Vencer</h2>";
echo "<p><strong>Número de teste:</strong> 47 96164699 (seu número)</p>";

// Buscar cliente de teste (47 96164699)
$numero_teste = '4796164699';
$numero_limpo = preg_replace('/\D/', '', $numero_teste);

echo "<h3>🔍 Buscando cliente de teste</h3>";

$sql = "SELECT id, nome, celular, contact_name FROM clientes 
        WHERE celular LIKE '%$numero_limpo%' 
        OR celular LIKE '%$numero_teste%'
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    echo "<p>✅ Cliente de teste encontrado:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
    echo "<li><strong>Celular:</strong> " . $cliente['celular'] . "</li>";
    echo "</ul>";
    
    $cliente_id = $cliente['id'];
    
    // Verificar faturas vencidas e a vencer
    echo "<h3>💰 Verificando Faturas Vencidas e a Vencer</h3>";
    
    // Faturas vencidas
    $sql_vencidas = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura,
                        DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    AND cob.status = 'OVERDUE'
                    ORDER BY cob.vencimento ASC";
    
    $result_vencidas = $mysqli->query($sql_vencidas);
    $total_vencidas = $result_vencidas ? $result_vencidas->num_rows : 0;
    
    // Faturas a vencer
    $sql_a_vencer = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura,
                        DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    AND cob.status = 'PENDING'
                    ORDER BY cob.vencimento ASC";
    
    $result_a_vencer = $mysqli->query($sql_a_vencer);
    $total_a_vencer = $result_a_vencer ? $result_a_vencer->num_rows : 0;
    
    echo "<p><strong>Faturas vencidas:</strong> $total_vencidas</p>";
    echo "<p><strong>Faturas a vencer:</strong> $total_a_vencer</p>";
    
    // Criar faturas de teste se não existirem
    if ($total_vencidas == 0 && $total_a_vencer == 0) {
        echo "<p>⚠️ Cliente não possui faturas vencidas ou a vencer. Criando faturas de teste...</p>";
        
        // Criar fatura vencida
        $sql_insert_vencida = "INSERT INTO cobrancas (cliente_id, valor, status, vencimento, url_fatura) 
                               VALUES ($cliente_id, 89.90, 'OVERDUE', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'https://www.asaas.com/i/teste_vencida')";
        
        // Criar fatura a vencer
        $sql_insert_a_vencer = "INSERT INTO cobrancas (cliente_id, valor, status, vencimento, url_fatura) 
                                VALUES ($cliente_id, 129.90, 'PENDING', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'https://www.asaas.com/i/teste_a_vencer')";
        
        if ($mysqli->query($sql_insert_vencida) && $mysqli->query($sql_insert_a_vencer)) {
            echo "<p>✅ Faturas de teste criadas com sucesso!</p>";
            $total_vencidas = 1;
            $total_a_vencer = 1;
        } else {
            echo "<p>❌ Erro ao criar faturas de teste: " . $mysqli->error . "</p>";
        }
    }
    
    // Testar nova função de busca de faturas
    echo "<h3>🧪 Testando Nova Função buscarFaturasCliente()</h3>";
    
    function buscarFaturasCliente($cliente_id, $mysqli) {
        // Buscar faturas vencidas (OVERDUE)
        $sql_vencidas = "SELECT 
                            cob.id,
                            cob.valor,
                            cob.status,
                            DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                            cob.url_fatura,
                            DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido
                        FROM cobrancas cob
                        WHERE cob.cliente_id = $cliente_id
                        AND cob.status = 'OVERDUE'
                        ORDER BY cob.vencimento ASC";
        
        $result_vencidas = $mysqli->query($sql_vencidas);
        
        // Buscar faturas a vencer (PENDING)
        $sql_a_vencer = "SELECT 
                            cob.id,
                            cob.valor,
                            cob.status,
                            DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                            cob.url_fatura,
                            DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer
                        FROM cobrancas cob
                        WHERE cob.cliente_id = $cliente_id
                        AND cob.status = 'PENDING'
                        ORDER BY cob.vencimento ASC";
        
        $result_a_vencer = $mysqli->query($sql_a_vencer);
        
        // Verificar se há faturas
        $total_vencidas = $result_vencidas ? $result_vencidas->num_rows : 0;
        $total_a_vencer = $result_a_vencer ? $result_a_vencer->num_rows : 0;
        
        if ($total_vencidas == 0 && $total_a_vencer == 0) {
            return "🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊";
        }
        
        // Buscar nome do cliente
        $sql_cliente = "SELECT contact_name, nome FROM clientes WHERE id = $cliente_id LIMIT 1";
        $result_cliente = $mysqli->query($sql_cliente);
        $cliente = $result_cliente->fetch_assoc();
        $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
        
        $resposta = "Olá $nome_cliente! 👋\n\n";
        $resposta .= "📋 Aqui está o resumo das suas faturas:\n\n";
        
        // Seção de faturas vencidas
        if ($total_vencidas > 0) {
            $resposta .= "🔴 *Faturas Vencidas:*\n";
            $valor_total_vencidas = 0;
            
            while ($fatura = $result_vencidas->fetch_assoc()) {
                $valor = number_format($fatura['valor'], 2, ',', '.');
                $dias_vencido = $fatura['dias_vencido'];
                $valor_total_vencidas += $fatura['valor'];
                
                $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
                $resposta .= "  Venceu em {$fatura['vencimento_formatado']} ({$dias_vencido} dias atrás)\n";
                
                if ($fatura['url_fatura']) {
                    $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
                }
                $resposta .= "\n";
            }
            
            $valor_total_vencidas_formatado = number_format($valor_total_vencidas, 2, ',', '.');
            $resposta .= "💰 *Total vencido: R$ $valor_total_vencidas_formatado*\n\n";
        }
        
        // Seção de faturas a vencer
        if ($total_a_vencer > 0) {
            $resposta .= "🟡 *Faturas a Vencer:*\n";
            $valor_total_a_vencer = 0;
            
            while ($fatura = $result_a_vencer->fetch_assoc()) {
                $valor = number_format($fatura['valor'], 2, ',', '.');
                $dias_para_vencer = $fatura['dias_para_vencer'];
                $valor_total_a_vencer += $fatura['valor'];
                
                $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
                $resposta .= "  Vence em {$fatura['vencimento_formatado']} (em {$dias_para_vencer} dias)\n";
                
                if ($fatura['url_fatura']) {
                    $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
                }
                $resposta .= "\n";
            }
            
            $valor_total_a_vencer_formatado = number_format($valor_total_a_vencer, 2, ',', '.');
            $resposta .= "💰 *Total a vencer: R$ $valor_total_a_vencer_formatado*\n\n";
        }
        
        // Resumo final
        $valor_total_geral = ($valor_total_vencidas ?? 0) + ($valor_total_a_vencer ?? 0);
        if ($valor_total_geral > 0) {
            $valor_total_geral_formatado = number_format($valor_total_geral, 2, ',', '.');
            $resposta .= "📊 *Resumo Geral:*\n";
            $resposta .= "💰 Valor total em aberto: R$ $valor_total_geral_formatado\n\n";
        }
        
        // Mensagem final simpática
        if ($total_vencidas > 0) {
            $resposta .= "⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n";
        }
        
        $resposta .= "💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n";
        $resposta .= "Se precisar de ajuda, estamos aqui! 😊";
        
        return $resposta;
    }
    
    $resposta_faturas = buscarFaturasCliente($cliente_id, $mysqli);
    echo "<h4>Nova resposta gerada:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_faturas);
    echo "</pre>";
    
    // Simular envio para você
    echo "<h3>📤 Enviando Nova Formatação para Você (47 96164699)</h3>";
    
    $numero_formatado = '55' . $numero_limpo . '@c.us';
    
    echo "<p><strong>Número formatado:</strong> $numero_formatado</p>";
    
    // Preparar payload para envio
    $payload = json_encode([
        'sessionName' => 'default',
        'number' => $numero_formatado,
        'message' => $resposta_faturas
    ]);
    
    echo "<h4>Payload de envio:</h4>";
    echo "<pre style='background: #f0f8ff; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($payload);
    echo "</pre>";
    
    // Enviar via VPS
    $ch = curl_init("http://212.85.11.238:3000/send/text");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "<h4>Resposta do servidor:</h4>";
    echo "<p><strong>HTTP Code:</strong> $http_code</p>";
    
    if ($error) {
        echo "<p style='color: red;'><strong>Erro de conexão:</strong> $error</p>";
    } else {
        echo "<p><strong>Resposta:</strong></p>";
        echo "<pre style='background: #f0fff0; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
        
        $response_data = json_decode($response, true);
        
        if ($response_data && isset($response_data['success']) && $response_data['success']) {
            echo "<p style='color: green;'>✅ Nova formatação enviada com sucesso para você!</p>";
            
            // Salvar no banco de dados
            $mensagem_escaped = $mysqli->real_escape_string($resposta_faturas);
            $data_hora = date('Y-m-d H:i:s');
            
            $sql_save = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                        VALUES (36, $cliente_id, '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'enviado', '$numero_limpo')";
            
            if ($mysqli->query($sql_save)) {
                echo "<p style='color: green;'>✅ Mensagem salva no banco de dados</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao salvar no banco: " . $mysqli->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro ao enviar mensagem</p>";
        }
    }
    
} else {
    echo "<p>❌ Cliente de teste NÃO encontrado no banco de dados</p>";
    echo "<p>Número testado: $numero_teste</p>";
    echo "<p>Número limpo: $numero_limpo</p>";
}

echo "<hr>";
echo "<h3>📊 Resumo da Nova Formatação</h3>";
echo "<p><strong>Melhorias implementadas:</strong></p>";
echo "<ul>";
echo "<li>✅ Mostra apenas faturas vencidas (OVERDUE) e a vencer (PENDING)</li>";
echo "<li>✅ Mensagem mais simpática e personalizada</li>";
echo "<li>✅ Organização por seções (vencidas/a vencer)</li>";
echo "<li>✅ Cálculo de dias vencidos e dias para vencer</li>";
echo "<li>✅ Totais por categoria e geral</li>";
echo "<li>✅ Links de pagamento organizados</li>";
echo "<li>✅ Dicas e orientações para o cliente</li>";
echo "</ul>";

echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver a nova formatação!</strong></p>";
?> 