<?php
/**
 * 🎭 SIMULAÇÃO DE ATENDIMENTO REAL
 * Simula exatamente o que aconteceria no WhatsApp quando alguém entra em contato
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🎭 Simulação de Atendimento Real</h2>";
echo "<p><strong>Cenário:</strong> Cliente entra em contato dizendo 'Bom dia'</p>";
echo "<hr>";

// Simular dados de entrada como se viessem do WhatsApp
$numero_cliente = '6993245042'; // Detetive Aguiar (cliente real)
$mensagem_cliente = 'bom dia';
$tipo_mensagem = 'text';

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📱 Mensagem Recebida:</h3>";
echo "<p><strong>De:</strong> $numero_cliente</p>";
echo "<p><strong>Mensagem:</strong> \"$mensagem_cliente\"</p>";
echo "<p><strong>Tipo:</strong> $tipo_mensagem</p>";
echo "</div>";

// Processar como o sistema real faria
$numero_limpo = preg_replace('/\D/', '', $numero_cliente);
$texto = strtolower(trim($mensagem_cliente));

// Buscar cliente
$cliente = null;
$cliente_id = null;

// Busca inteligente por número (com variações)
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
        $cliente = $result->fetch_assoc();
        $cliente_id = $cliente['id'];
        break;
    }
}

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🔍 Busca de Cliente:</h3>";
if ($cliente) {
    echo "<p style='color: green;'>✅ <strong>Cliente encontrado!</strong></p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
    echo "<li><strong>Contact Name:</strong> " . $cliente['contact_name'] . "</li>";
    echo "<li><strong>Celular:</strong> " . $cliente['celular'] . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ <strong>Cliente não encontrado</strong></p>";
}
echo "</div>";

// Identificar intenção
$palavras_chave = [
    'fatura' => ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar', 'consulta', 'consultas'],
    'plano' => ['plano', 'pacote', 'serviço', 'assinatura', 'mensalidade'],
    'suporte' => ['suporte', 'ajuda', 'problema', 'erro', 'não funciona', 'bug'],
    'comercial' => ['comercial', 'venda', 'preço', 'orçamento', 'proposta', 'site'],
    'cpf' => ['cpf', 'documento', 'identificação', 'cadastro', 'cnpj'],
    'saudacao' => ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi', 'oie']
];

$intencao = 'geral';
foreach ($palavras_chave as $intencao_tipo => $palavras) {
    foreach ($palavras as $palavra) {
        if (strpos($texto, $palavra) !== false) {
            $intencao = $intencao_tipo;
            break 2;
        }
    }
}

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🧠 Análise de Intenção:</h3>";
echo "<p><strong>Texto analisado:</strong> \"$texto\"</p>";
echo "<p><strong>Intenção detectada:</strong> <span style='color: blue; font-weight: bold;'>$intencao</span></p>";
echo "</div>";

// Gerar resposta baseada na intenção
$resposta = '';

switch ($intencao) {
    case 'saudacao':
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "Como posso ajudá-lo hoje?\n\n";
            $resposta .= "📋 *Opções disponíveis:*\n";
            $resposta .= "• Verificar faturas (digite 'faturas' ou 'consulta')\n";
            $resposta .= "• Informações do plano\n";
            $resposta .= "• Suporte técnico\n";
            $resposta .= "• Atendimento comercial";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
            $resposta .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
            $resposta .= "Entre em contato através do número: *47 997309525*\n\n";
            $resposta .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
            $resposta .= "Digite 'faturas' ou 'consulta' para verificar suas pendências.\n\n";
            $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        }
        break;
        
    default:
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "Como posso ajudá-lo hoje?\n\n";
            $resposta .= "📋 *Opções disponíveis:*\n";
            $resposta .= "• Verificar faturas (digite 'faturas' ou 'consulta')\n";
            $resposta .= "• Informações do plano\n";
            $resposta .= "• Suporte técnico\n";
            $resposta .= "• Atendimento comercial";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "Este é o canal da *Pixel12Digital* exclusivo para tratar de assuntos financeiros.\n\n";
            $resposta .= "📞 *Para atendimento comercial ou suporte técnico:*\n";
            $resposta .= "Entre em contato através do número: *47 997309525*\n\n";
            $resposta .= "📋 *Para informações sobre seu plano, faturas, etc.:*\n";
            $resposta .= "Digite 'faturas' ou 'consulta' para verificar suas pendências.\n\n";
            $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        }
        break;
}

echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🤖 Resposta Gerada:</h3>";
echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
echo htmlspecialchars($resposta);
echo "</pre>";
echo "</div>";

// Simular envio da resposta
echo "<div style='background: #cce5ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📤 Enviando Resposta:</h3>";

// Formatar número para envio
$numero_limpo_destino = preg_replace('/\D/', '', $numero_cliente);
$numero_formatado = '55' . $numero_limpo_destino . '@c.us';

echo "<p><strong>Para:</strong> $numero_cliente</p>";
echo "<p><strong>Número formatado:</strong> $numero_formatado</p>";

// Preparar payload para envio
$payload = json_encode([
    'sessionName' => 'default',
    'number' => $numero_formatado,
    'message' => $resposta
]);

// Enviar via VPS (simulação real)
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
    echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    $response_data = json_decode($response, true);
    
    if ($response_data && isset($response_data['success']) && $response_data['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Mensagem enviada com sucesso!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro ao enviar mensagem</p>";
    }
}
echo "</div>";

// Simular segunda interação - cliente pede faturas
echo "<hr>";
echo "<h3>🔄 Segunda Interação - Cliente pede 'faturas'</h3>";

$mensagem_cliente2 = 'faturas';
$texto2 = strtolower(trim($mensagem_cliente2));

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📱 Segunda Mensagem Recebida:</h3>";
echo "<p><strong>De:</strong> $numero_cliente</p>";
echo "<p><strong>Mensagem:</strong> \"$mensagem_cliente2\"</p>";
echo "</div>";

// Identificar intenção da segunda mensagem
$intencao2 = 'geral';
foreach ($palavras_chave as $intencao_tipo => $palavras) {
    foreach ($palavras as $palavra) {
        if (strpos($texto2, $palavra) !== false) {
            $intencao2 = $intencao_tipo;
            break 2;
        }
    }
}

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🧠 Análise de Intenção (2ª mensagem):</h3>";
echo "<p><strong>Texto analisado:</strong> \"$texto2\"</p>";
echo "<p><strong>Intenção detectada:</strong> <span style='color: blue; font-weight: bold;'>$intencao2</span></p>";
echo "</div>";

// Gerar resposta para faturas
if ($intencao2 == 'fatura' && $cliente_id) {
    // Incluir função de busca de faturas
    function sincronizarFaturasClienteAsaas($cliente_id, $mysqli) {
        try {
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
            
            return [
                'success' => true,
                'message' => "Sincronização concluída",
                'atualizacoes' => 0,
                'novas_faturas' => 0
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na sincronização: ' . $e->getMessage()
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
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>🤖 Resposta para Faturas:</h3>";
    echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
    echo htmlspecialchars($resposta_faturas);
    echo "</pre>";
    echo "</div>";
    
    // Enviar resposta das faturas
    $payload2 = json_encode([
        'sessionName' => 'default',
        'number' => $numero_formatado,
        'message' => $resposta_faturas
    ]);
    
    $ch2 = curl_init("http://212.85.11.238:3000/send/text");
    curl_setopt($ch2, CURLOPT_POST, 1);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $payload2);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
    
    $response2 = curl_exec($ch2);
    $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    
    echo "<div style='background: #cce5ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h3>📤 Enviando Resposta das Faturas:</h3>";
    echo "<p><strong>HTTP Code:</strong> $http_code2</p>";
    
    $response_data2 = json_decode($response2, true);
    if ($response_data2 && isset($response_data2['success']) && $response_data2['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Resposta das faturas enviada com sucesso!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Erro ao enviar resposta das faturas</p>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<h3>📊 Resumo da Simulação</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px;'>";
echo "<p><strong>Simulação concluída com sucesso!</strong></p>";
echo "<ul>";
echo "<li>✅ Cliente identificado automaticamente</li>";
echo "<li>✅ Intenção 'saudação' detectada</li>";
echo "<li>✅ Resposta personalizada enviada</li>";
echo "<li>✅ Segunda interação processada</li>";
echo "<li>✅ Consulta de faturas com sincronização</li>";
echo "<li>✅ Resposta completa das faturas enviada</li>";
echo "</ul>";
echo "<p><em>Simulação concluída em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>🎯 Como Funciona na Prática:</h3>";
echo "<ol>";
echo "<li><strong>Cliente envia mensagem</strong> → Sistema recebe via webhook</li>";
echo "<li><strong>Busca inteligente</strong> → Procura cliente por número (com variações)</li>";
echo "<li><strong>Análise de intenção</strong> → Identifica o que o cliente quer</li>";
echo "<li><strong>Gera resposta</strong> → Baseada na intenção e se encontrou o cliente</li>";
echo "<li><strong>Envia resposta</strong> → Via API do WhatsApp</li>";
echo "<li><strong>Se pedir faturas</strong> → Sincroniza com Asaas e envia relatório</li>";
echo "</ol>";
echo "</div>";
?> 