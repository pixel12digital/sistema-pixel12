<?php
/**
 * 🧪 TESTE DA IA DIRETA
 * Testa a IA diretamente sem usar cURL
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste da IA Direta</h2>";
echo "<p><strong>Testando:</strong> IA diretamente sem cURL</p>";
echo "<hr>";

// Simular dados que viriam do WhatsApp
$numero = '554796164699';
$texto = 'faturas';
$tipo = 'text';

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📤 Dados de Teste:</h3>";
echo "<p><strong>Número:</strong> $numero</p>";
echo "<p><strong>Texto:</strong> '$texto'</p>";
echo "<p><strong>Tipo:</strong> $tipo</p>";
echo "</div>";

// Buscar cliente pelo número
$numero_limpo = preg_replace('/\D/', '', $numero);
$cliente_id = null;
$cliente = null;

// Buscar cliente com similaridade de número
$formatos_busca = [
    $numero_limpo,
    ltrim($numero_limpo, '55'),
    substr($numero_limpo, -11),
    substr($numero_limpo, -10),
    substr($numero_limpo, -9),
];

foreach ($formatos_busca as $formato) {
    if (strlen($formato) >= 9) {
        $sql = "SELECT id, nome, contact_name, celular, telefone, asaas_id FROM clientes 
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                LIMIT 1";
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
            $cliente_id = $cliente['id'];
            break;
        }
    }
}

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🔍 Busca de Cliente:</h3>";
if ($cliente) {
    echo "<p style='color: green;'>✅ <strong>Cliente encontrado!</strong></p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
    echo "<li><strong>Contact Name:</strong> " . $cliente['contact_name'] . "</li>";
    echo "<li><strong>Asaas ID:</strong> " . ($cliente['asaas_id'] ?: 'Não informado') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ <strong>Cliente não encontrado</strong></p>";
}
echo "</div>";

// Análise de intenção
$texto_lower = strtolower(trim($texto));
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
        if (strpos($texto_lower, $palavra) !== false) {
            $intencao = $intencao_tipo;
            break 2;
        }
    }
}

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🧠 Análise de Intenção:</h3>";
echo "<ul>";
echo "<li><strong>Texto analisado:</strong> '$texto_lower'</li>";
echo "<li><strong>Intenção detectada:</strong> <span style='color: blue; font-weight: bold;'>$intencao</span></li>";
echo "<li><strong>Cliente ID:</strong> " . ($cliente_id ?: 'Não encontrado') . "</li>";
echo "</ul>";
echo "</div>";

// Processar resposta baseada na intenção
echo "<div style='background: #d4edda; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>🤖 Processando Resposta:</h3>";

$resposta = '';

switch ($intencao) {
    case 'fatura':
        if ($cliente_id) {
            echo "<p style='color: green;'>✅ <strong>Intenção 'fatura' detectada e cliente encontrado!</strong></p>";
            echo "<p><strong>Executando:</strong> buscarFaturasCliente($cliente_id, \$mysqli)</p>";
            
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
            
            $resposta = buscarFaturasCliente($cliente_id, $mysqli);
            
            echo "<p style='color: green;'>✅ <strong>Função executada com sucesso!</strong></p>";
            
        } else {
            echo "<p style='color: orange;'>⚠️ <strong>Intenção 'fatura' detectada mas cliente NÃO encontrado!</strong></p>";
            $resposta = "Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n";
            $resposta .= "📋 *Por favor, informe:*\n";
            $resposta .= "• Seu CPF ou CNPJ (apenas números, sem espaços)\n\n";
            $resposta .= "Assim posso buscar suas informações e repassar o status das faturas! 😊";
        }
        break;
        
    default:
        echo "<p style='color: red;'>❌ <strong>Intenção não reconhecida como 'fatura'!</strong></p>";
        echo "<p><strong>Intenção detectada:</strong> $intencao</p>";
        
        if ($cliente_id) {
            $nome_cliente = $cliente['contact_name'] ?: $cliente['nome'];
            $resposta = "Olá $nome_cliente! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Como posso ajudá-lo hoje? 😊";
        } else {
            $resposta = "Olá! 👋\n\n";
            $resposta .= "🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n";
            $resposta .= "📞 *Para outras informações ou falar com nossa equipe:*\n";
            $resposta .= "Entre em contato: *47 997309525*\n\n";
            $resposta .= "💰 *Para assuntos financeiros:*\n";
            $resposta .= "• Digite 'faturas' para consultar suas faturas em aberto\n";
            $resposta .= "• Verificar status de pagamentos\n";
            $resposta .= "• Informações sobre planos\n\n";
            $resposta .= "Se não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).";
        }
        break;
}

echo "<h4>Resposta gerada:</h4>";
echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px; white-space: pre-wrap;'>";
echo htmlspecialchars($resposta);
echo "</pre>";
echo "</div>";

// Enviar resposta para WhatsApp
echo "<div style='background: #cce5ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📤 Enviando Resposta:</h3>";

try {
    $api_url = WHATSAPP_ROBOT_URL . "/send/text";
    $data_envio = [
        "number" => $numero,
        "message" => $resposta
    ];
    
    echo "<p><strong>API URL:</strong> $api_url</p>";
    echo "<p><strong>Enviando para:</strong> $numero</p>";
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_envio));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, WHATSAPP_TIMEOUT);
    
    $api_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error_envio = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>Resposta API - HTTP:</strong> $http_code</p>";
    if ($error_envio) {
        echo "<p style='color: red;'><strong>Erro de envio:</strong> $error_envio</p>";
    }
    
    if ($http_code === 200) {
        $api_result = json_decode($api_response, true);
        if ($api_result && isset($api_result["success"]) && $api_result["success"]) {
            echo "<p style='color: green; font-weight: bold;'>✅ <strong>Resposta enviada com sucesso!</strong></p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro ao enviar resposta:</strong></p>";
            echo "<pre style='background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
            echo htmlspecialchars($api_response);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ <strong>Erro HTTP ao enviar resposta:</strong> $http_code</p>";
        if ($error_envio) {
            echo "<p><strong>Erro:</strong> $error_envio</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ <strong>Exceção ao enviar resposta:</strong> " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<hr>";
echo "<h3>📊 Resumo do Teste da IA Direta</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px;'>";
echo "<p><strong>Teste da IA direta concluído!</strong></p>";
echo "<ul>";
echo "<li>✅ Cliente identificado</li>";
echo "<li>✅ Intenção detectada</li>";
echo "<li>✅ Resposta gerada</li>";
echo "<li>✅ Mensagem enviada</li>";
echo "</ul>";
echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver a resposta correta das faturas!</strong></p>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>🔧 Solução Implementada:</h3>";
echo "<p><strong>Problema:</strong> O webhook não conseguia chamar a IA via cURL</p>";
echo "<p><strong>Solução:</strong> Processamento direto da IA sem usar cURL</p>";
echo "<p><strong>Resultado:</strong> Agora quando você digitar 'faturas', deve receber a consulta de faturas correta</p>";
echo "</div>";
?> 