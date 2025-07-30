<?php
/**
 * 🧪 TESTE MENSAGEM AUTOMÁTICA
 * Testa a nova mensagem que deixa claro que é automática e fornece contato
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste Mensagem Automática</h2>";
echo "<p><strong>Cliente real:</strong> +55 69 9324-5042 (Detetive Aguiar)</p>";
echo "<p><strong>Envio para:</strong> 47 96164699 (seu número)</p>";

// Buscar cliente real (Detetive Aguiar - +55 69 9324-5042)
$numero_cliente_real = '6993245042';
$numero_limpo_cliente = preg_replace('/\D/', '', $numero_cliente_real);

echo "<h3>🔍 Buscando cliente real: +55 69 9324-5042</h3>";

$sql = "SELECT id, nome, celular, contact_name, asaas_id FROM clientes 
        WHERE celular LIKE '%$numero_limpo_cliente%' 
        OR celular LIKE '%$numero_cliente_real%'
        OR celular LIKE '%6993245042%'
        OR celular LIKE '%556993245042%'
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    echo "<p>✅ Cliente real encontrado:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
    echo "<li><strong>Celular:</strong> " . $cliente['celular'] . "</li>";
    echo "<li><strong>Contact Name:</strong> " . $cliente['contact_name'] . "</li>";
    echo "<li><strong>Asaas ID:</strong> " . ($cliente['asaas_id'] ?: 'NÃO CADASTRADO') . "</li>";
    echo "</ul>";
    
    $cliente_id = $cliente['id'];
    
    // Testar função com sincronização e mensagem automática
    echo "<h3>🤖 Testando Função com Mensagem Automática</h3>";
    
    function sincronizarFaturasClienteAsaas($cliente_id, $mysqli) {
        try {
            // 1. Buscar dados do cliente (incluindo asaas_id)
            $sql_cliente = "SELECT asaas_id, nome FROM clientes WHERE id = $cliente_id LIMIT 1";
            $result_cliente = $mysqli->query($sql_cliente);
            
            if (!$result_cliente || $result_cliente->num_rows == 0) {
                return ['success' => false, 'message' => 'Cliente não encontrado'];
            }
            
            $cliente = $result_cliente->fetch_assoc();
            $asaas_customer_id = $cliente['asaas_id'];
            
            if (!$asaas_customer_id) {
                return ['success' => false, 'message' => 'Cliente sem ID do Asaas'];
            }
            
            // 2. Buscar faturas no banco local
            $sql_local = "SELECT 
                            asaas_payment_id,
                            valor,
                            status,
                            vencimento,
                            url_fatura
                        FROM cobrancas 
                        WHERE cliente_id = $cliente_id 
                        AND status IN ('PENDING', 'OVERDUE')
                        ORDER BY vencimento ASC";
            
            $result_local = $mysqli->query($sql_local);
            $faturas_locais = [];
            
            if ($result_local) {
                while ($row = $result_local->fetch_assoc()) {
                    $faturas_locais[$row['asaas_payment_id']] = $row;
                }
            }
            
            // 3. Buscar faturas no Asaas
            $faturas_asaas = buscarFaturasAsaas($asaas_customer_id);
            
            if (!$faturas_asaas['success']) {
                return $faturas_asaas; // Retorna erro da API
            }
            
            $faturas_asaas_data = $faturas_asaas['data'];
            
            // 4. Comparar e sincronizar
            $atualizacoes = 0;
            $novas_faturas = 0;
            
            foreach ($faturas_asaas_data as $fatura_asaas) {
                $asaas_payment_id = $fatura_asaas['id'];
                $status_asaas = $fatura_asaas['status'];
                $valor_asaas = $fatura_asaas['value'];
                $vencimento_asaas = $fatura_asaas['dueDate'];
                $url_asaas = $fatura_asaas['invoiceUrl'] ?? '';
                
                // Verificar se fatura existe localmente
                if (isset($faturas_locais[$asaas_payment_id])) {
                    $fatura_local = $faturas_locais[$asaas_payment_id];
                    
                    // Verificar se precisa atualizar
                    if ($fatura_local['status'] !== $status_asaas || 
                        $fatura_local['valor'] != $valor_asaas ||
                        $fatura_local['vencimento'] !== $vencimento_asaas ||
                        $fatura_local['url_fatura'] !== $url_asaas) {
                        
                        // Atualizar fatura local
                        $sql_update = "UPDATE cobrancas SET 
                                        status = '" . $mysqli->real_escape_string($status_asaas) . "',
                                        valor = " . floatval($valor_asaas) . ",
                                        vencimento = '" . $mysqli->real_escape_string($vencimento_asaas) . "',
                                        url_fatura = '" . $mysqli->real_escape_string($url_asaas) . "',
                                        data_atualizacao = NOW()
                                      WHERE asaas_payment_id = '" . $mysqli->real_escape_string($asaas_payment_id) . "'";
                        
                        if ($mysqli->query($sql_update)) {
                            $atualizacoes++;
                        }
                    }
                } else {
                    // Nova fatura - inserir no banco local
                    $sql_insert = "INSERT INTO cobrancas (
                                    asaas_payment_id, 
                                    cliente_id, 
                                    valor, 
                                    status, 
                                    vencimento, 
                                    url_fatura, 
                                    data_criacao, 
                                    data_atualizacao
                                  ) VALUES (
                                    '" . $mysqli->real_escape_string($asaas_payment_id) . "',
                                    $cliente_id,
                                    " . floatval($valor_asaas) . ",
                                    '" . $mysqli->real_escape_string($status_asaas) . "',
                                    '" . $mysqli->real_escape_string($vencimento_asaas) . "',
                                    '" . $mysqli->real_escape_string($url_asaas) . "',
                                    NOW(),
                                    NOW()
                                  )";
                    
                    if ($mysqli->query($sql_insert)) {
                        $novas_faturas++;
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "Sincronização concluída: $atualizacoes atualizações, $novas_faturas novas faturas",
                'atualizacoes' => $atualizacoes,
                'novas_faturas' => $novas_faturas
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na sincronização: ' . $e->getMessage()
            ];
        }
    }
    
    function buscarFaturasAsaas($asaas_customer_id) {
        try {
            // Buscar chave da API
            global $mysqli;
            $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
            $api_key = $config ? $config['valor'] : ASAAS_API_KEY;
            
            // Fazer requisição para API do Asaas
            $ch = curl_init();
            $url = ASAAS_API_URL . "/payments?customer=" . urlencode($asaas_customer_id) . "&limit=100";
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'access_token: ' . $api_key
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return [
                    'success' => false,
                    'message' => 'Erro de conexão: ' . $error
                ];
            }
            
            if ($http_code !== 200) {
                return [
                    'success' => false,
                    'message' => "Erro HTTP $http_code: $response"
                ];
            }
            
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Erro ao decodificar resposta: ' . json_last_error_msg()
                ];
            }
            
            return [
                'success' => true,
                'data' => $data['data'] ?? []
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na requisição: ' . $e->getMessage()
            ];
        }
    }
    
    function buscarFaturasCliente($cliente_id, $mysqli) {
        // 1. SINCRONIZAÇÃO INDIVIDUAL COM ASAAS
        $sincronizacao = sincronizarFaturasClienteAsaas($cliente_id, $mysqli);
        
        // 2. Buscar faturas vencidas (OVERDUE) - após sincronização
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
        
        // 3. Buscar apenas a PRÓXIMA fatura a vencer (PENDING) - a mais próxima
        $sql_proxima_vencer = "SELECT 
                            cob.id,
                            cob.valor,
                            cob.status,
                            DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                            cob.url_fatura,
                            DATEDIFF(cob.vencimento, CURDATE()) as dias_para_vencer
                        FROM cobrancas cob
                        WHERE cob.cliente_id = $cliente_id
                        AND cob.status = 'PENDING'
                        ORDER BY cob.vencimento ASC
                        LIMIT 1";
        
        $result_proxima_vencer = $mysqli->query($sql_proxima_vencer);
        
        // Verificar se há faturas
        $total_vencidas = $result_vencidas ? $result_vencidas->num_rows : 0;
        $tem_proxima_vencer = $result_proxima_vencer ? $result_proxima_vencer->num_rows : 0;
        
        if ($total_vencidas == 0 && $tem_proxima_vencer == 0) {
            return "🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*";
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
        
        // Seção da PRÓXIMA fatura a vencer (apenas uma)
        if ($tem_proxima_vencer > 0) {
            $resposta .= "🟡 *Próxima Fatura a Vencer:*\n";
            
            $fatura = $result_proxima_vencer->fetch_assoc();
            $valor = number_format($fatura['valor'], 2, ',', '.');
            $dias_para_vencer = $fatura['dias_para_vencer'];
            
            $resposta .= "• Fatura #{$fatura['id']} - R$ $valor\n";
            $resposta .= "  Vence em {$fatura['vencimento_formatado']} (em {$dias_para_vencer} dias)\n";
            
            if ($fatura['url_fatura']) {
                $resposta .= "  💳 Pagar: {$fatura['url_fatura']}\n";
            }
            $resposta .= "\n";
        }
        
        // Resumo final - APENAS faturas vencidas no total em aberto
        if ($total_vencidas > 0) {
            $valor_total_vencidas_formatado = number_format($valor_total_vencidas, 2, ',', '.');
            $resposta .= "📊 *Resumo Geral:*\n";
            $resposta .= "💰 Valor total em aberto: R$ $valor_total_vencidas_formatado\n\n";
        }
        
        // Mensagem final simpática
        if ($total_vencidas > 0) {
            $resposta .= "⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n";
        }
        
        $resposta .= "💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n";
        $resposta .= "🤖 *Esta é uma mensagem automática*\n";
        $resposta .= "📞 Para conversar com nossa equipe, entre em contato: *47 997309525*";
        
        return $resposta;
    }
    
    $resposta_faturas = buscarFaturasCliente($cliente_id, $mysqli);
    echo "<h4>Nova mensagem com informação automática:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_faturas);
    echo "</pre>";
    
    // Enviar para você (47 96164699)
    echo "<h3>📤 Enviando Nova Mensagem Automática para Você (47 96164699)</h3>";
    
    // Número de destino (seu número)
    $numero_destino = '4796164699';
    $numero_limpo_destino = preg_replace('/\D/', '', $numero_destino);
    $numero_formatado = '55' . $numero_limpo_destino . '@c.us';
    
    echo "<p><strong>Cliente real:</strong> " . $cliente['nome'] . " (+55 69 9324-5042)</p>";
    echo "<p><strong>Enviando para:</strong> 47 96164699 (seu número)</p>";
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
            echo "<p style='color: green;'>✅ Nova mensagem automática enviada com sucesso para você!</p>";
            
            // Salvar no banco de dados
            $mensagem_escaped = $mysqli->real_escape_string($resposta_faturas);
            $data_hora = date('Y-m-d H:i:s');
            
            $sql_save = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                        VALUES (36, $cliente_id, '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'enviado', '$numero_limpo_cliente')";
            
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
    echo "<p>❌ Cliente real NÃO encontrado no banco de dados</p>";
}

echo "<hr>";
echo "<h3>📊 Resumo da Nova Mensagem Automática</h3>";
echo "<p><strong>Melhorias implementadas:</strong></p>";
echo "<ul>";
echo "<li>✅ Deixa claro que é uma mensagem automática</li>";
echo "<li>✅ Fornece contato para atendimento humano</li>";
echo "<li>✅ Mantém sincronização com Asaas</li>";
echo "<li>✅ Nova formatação simpática</li>";
echo "<li>✅ Total em aberto apenas faturas vencidas</li>";
echo "<li>✅ Próxima fatura apenas a mais próxima</li>";
echo "</ul>";

echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver a nova mensagem automática!</strong></p>";
?> 