<?php
/**
 * 🧪 TESTE NOVA INTERAÇÃO
 * Testa a nova lógica de interação com saudação educada e busca inteligente
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste Nova Interação - Sistema Completo</h2>";
echo "<p><strong>Testando:</strong> Nova lógica de interação com busca inteligente</p>";

// Funções necessárias
function buscarClientePorNumero($numero_limpo, $mysqli) {
    // Tentar diferentes variações do número
    $variacoes = [
        $numero_limpo,
        substr($numero_limpo, -8), // Últimos 8 dígitos
        substr($numero_limpo, -9), // Últimos 9 dígitos
        substr($numero_limpo, -10), // Últimos 10 dígitos
        substr($numero_limpo, -11)  // Últimos 11 dígitos
    ];
    
    foreach ($variacoes as $variacao) {
        $sql = "SELECT id, nome, contact_name, celular, telefone, asaas_id FROM clientes 
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$variacao%' 
                OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$variacao%'
                LIMIT 1";
        
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    
    return null;
}

function buscarClientePorCPF($cpf_limpo, $mysqli) {
    $sql = "SELECT id, nome, contact_name, cpf_cnpj FROM clientes WHERE cpf_cnpj = '$cpf_limpo' LIMIT 1";
    $result = $mysqli->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

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

// Teste 1: Saudação inicial (cliente não encontrado)
echo "<h3>🧪 Teste 1: Saudação Inicial (Cliente Não Encontrado)</h3>";
$numero_teste1 = '47999999999'; // Número que não existe
$cliente_teste1 = buscarClientePorNumero($numero_teste1, $mysqli);

if (!$cliente_teste1) {
    $resposta_teste1 = "Olá! 👋\n\n";
    $resposta_teste1 .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
    $resposta_teste1 .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
    $resposta_teste1 .= "Entre em contato através do número: *47 997309525*\n\n";
    $resposta_teste1 .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
    $resposta_teste1 .= "Digite 'faturas' ou 'consulta' para verificar suas pendências.\n\n";
    $resposta_teste1 .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
    
    echo "<p><strong>Número:</strong> $numero_teste1</p>";
    echo "<p><strong>Cliente encontrado:</strong> Não</p>";
    echo "<h4>Resposta gerada:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_teste1);
    echo "</pre>";
}

// Teste 2: Busca por número (cliente encontrado)
echo "<h3>🧪 Teste 2: Busca por Número (Cliente Encontrado)</h3>";
$numero_teste2 = '6993245042'; // Detetive Aguiar
$cliente_teste2 = buscarClientePorNumero($numero_teste2, $mysqli);

if ($cliente_teste2) {
    echo "<p><strong>Número:</strong> $numero_teste2</p>";
    echo "<p><strong>Cliente encontrado:</strong> Sim</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente_teste2['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente_teste2['nome'] . "</li>";
    echo "<li><strong>Contact Name:</strong> " . $cliente_teste2['contact_name'] . "</li>";
    echo "</ul>";
    
    $resposta_teste2 = "Olá " . $cliente_teste2['contact_name'] . "! 👋\n\n";
    $resposta_teste2 .= "Como posso ajudá-lo hoje?\n\n";
    $resposta_teste2 .= "📋 *Opções disponíveis:*\n";
    $resposta_teste2 .= "• Verificar faturas (digite 'faturas' ou 'consulta')\n";
    $resposta_teste2 .= "• Informações do plano\n";
    $resposta_teste2 .= "• Suporte técnico\n";
    $resposta_teste2 .= "• Atendimento comercial";
    
    echo "<h4>Resposta gerada:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_teste2);
    echo "</pre>";
}

// Teste 3: Consulta de faturas (cliente encontrado)
echo "<h3>🧪 Teste 3: Consulta de Faturas (Cliente Encontrado)</h3>";
if ($cliente_teste2) {
    $resposta_faturas = buscarFaturasCliente($cliente_teste2['id'], $mysqli);
    
    echo "<h4>Resposta com sincronização:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_faturas);
    echo "</pre>";
}

// Teste 4: Busca por CPF (cliente não encontrado)
echo "<h3>🧪 Teste 4: Busca por CPF (Cliente Não Encontrado)</h3>";
$cpf_teste4 = '12345678901'; // CPF que não existe
$cliente_cpf_teste4 = buscarClientePorCPF($cpf_teste4, $mysqli);

if (!$cliente_cpf_teste4) {
    $resposta_teste4 = "❌ CPF/CNPJ não encontrado em nossa base de dados.\n\n";
    $resposta_teste4 .= "📞 Para atendimento personalizado, entre em contato: *47 997309525*\n\n";
    $resposta_teste4 .= "Nossa equipe ficará feliz em ajudá-lo! 😊";
    
    echo "<p><strong>CPF:</strong> $cpf_teste4</p>";
    echo "<p><strong>Cliente encontrado:</strong> Não</p>";
    echo "<h4>Resposta gerada:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_teste4);
    echo "</pre>";
}

// Teste 5: Pedido de faturas sem cliente identificado
echo "<h3>🧪 Teste 5: Pedido de Faturas (Sem Cliente Identificado)</h3>";
$resposta_teste5 = "Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n";
$resposta_teste5 .= "📋 *Por favor, informe:*\n";
$resposta_teste5 .= "• Seu CPF ou CNPJ (apenas números, sem espaços)\n\n";
$resposta_teste5 .= "Assim posso buscar suas informações e repassar o status das faturas! 😊";

echo "<h4>Resposta gerada:</h4>";
echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
echo htmlspecialchars($resposta_teste5);
echo "</pre>";

// Enviar teste completo para você
echo "<h3>📤 Enviando Teste Completo para Você (47 96164699)</h3>";

$numero_destino = '4796164699';
$numero_limpo_destino = preg_replace('/\D/', '', $numero_destino);
$numero_formatado = '55' . $numero_limpo_destino . '@c.us';

$mensagem_teste = "🧪 *TESTE NOVA INTERAÇÃO - SISTEMA COMPLETO*\n\n";
$mensagem_teste .= "✅ Nova lógica implementada:\n";
$mensagem_teste .= "• Saudação educada\n";
$mensagem_teste .= "• Busca inteligente por número\n";
$mensagem_teste .= "• Fallback para CPF/CNPJ\n";
$mensagem_teste .= "• Sincronização com Asaas\n";
$mensagem_teste .= "• Mensagem automática\n\n";
$mensagem_teste .= "📞 Contato equipe: *47 997309525*\n\n";
$mensagem_teste .= "🤖 Sistema pronto para produção!";

// Preparar payload para envio
$payload = json_encode([
    'sessionName' => 'default',
    'number' => $numero_formatado,
    'message' => $mensagem_teste
]);

echo "<p><strong>Enviando para:</strong> 47 96164699 (seu número)</p>";
echo "<p><strong>Número formatado:</strong> $numero_formatado</p>";

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
        echo "<p style='color: green;'>✅ Teste da nova interação enviado com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao enviar mensagem</p>";
    }
}

echo "<hr>";
echo "<h3>📊 Resumo da Nova Interação</h3>";
echo "<p><strong>Funcionalidades implementadas:</strong></p>";
echo "<ul>";
echo "<li>✅ Saudação educada e explicativa</li>";
echo "<li>✅ Busca inteligente por número (com variações)</li>";
echo "<li>✅ Fallback para CPF/CNPJ</li>";
echo "<li>✅ Sincronização automática com Asaas</li>";
echo "<li>✅ Mensagem clara sobre ser automática</li>";
echo "<li>✅ Contato para atendimento humano</li>";
echo "<li>✅ Instruções claras para o usuário</li>";
echo "</ul>";

echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para confirmar o teste!</strong></p>";
?> 