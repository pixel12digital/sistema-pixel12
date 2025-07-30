<?php
/**
 * 🧪 TESTE CLIENTE REAL - DETETIVE AGUIAR
 * Busca informações reais do cliente +55 69 9324-5042 e simula envio para 47 96164699
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste Cliente Real - Detetive Aguiar</h2>";
echo "<p><strong>Cliente real:</strong> +55 69 9324-5042 (Detetive Aguiar)</p>";
echo "<p><strong>Envio para:</strong> 47 96164699 (seu número)</p>";

// Buscar cliente real (Detetive Aguiar - +55 69 9324-5042)
$numero_cliente_real = '6993245042';
$numero_limpo_cliente = preg_replace('/\D/', '', $numero_cliente_real);

echo "<h3>🔍 Buscando cliente real: +55 69 9324-5042</h3>";

$sql = "SELECT id, nome, celular, contact_name FROM clientes 
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
    echo "</ul>";
    
    $cliente_id = $cliente['id'];
    
    // Verificar faturas reais do cliente
    echo "<h3>💰 Verificando Faturas Reais do Cliente</h3>";
    
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
    
    // Mostrar detalhes das faturas
    if ($total_vencidas > 0) {
        echo "<h4>🔴 Faturas Vencidas (Reais):</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Valor</th><th>Vencimento</th><th>Dias Vencido</th><th>URL</th></tr>";
        
        while ($fatura = $result_vencidas->fetch_assoc()) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            echo "<tr>";
            echo "<td>" . $fatura['id'] . "</td>";
            echo "<td>R$ $valor</td>";
            echo "<td>" . $fatura['vencimento_formatado'] . "</td>";
            echo "<td>" . $fatura['dias_vencido'] . " dias</td>";
            echo "<td>" . ($fatura['url_fatura'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    if ($total_a_vencer > 0) {
        echo "<h4>🟡 Faturas a Vencer (Reais):</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Valor</th><th>Vencimento</th><th>Dias para Vencer</th><th>URL</th></tr>";
        
        while ($fatura = $result_a_vencer->fetch_assoc()) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            echo "<tr>";
            echo "<td>" . $fatura['id'] . "</td>";
            echo "<td>R$ $valor</td>";
            echo "<td>" . $fatura['vencimento_formatado'] . "</td>";
            echo "<td>" . $fatura['dias_para_vencer'] . " dias</td>";
            echo "<td>" . ($fatura['url_fatura'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Testar processamento de IA com dados reais
    echo "<h3>🤖 Testando Processamento de IA com Dados Reais</h3>";
    
    // Simular mensagem "faturas"
    $mensagem_teste = "faturas";
    $texto_lower = strtolower(trim($mensagem_teste));
    
    // Palavras-chave para identificar intenções
    $palavras_chave = [
        'fatura' => ['fatura', 'boleto', 'conta', 'pagamento', 'vencimento', 'pagar', 'faturas'],
        'plano' => ['plano', 'pacote', 'serviço', 'assinatura', 'mensalidade'],
        'suporte' => ['suporte', 'ajuda', 'problema', 'erro', 'não funciona', 'bug'],
        'comercial' => ['comercial', 'venda', 'preço', 'orçamento', 'proposta', 'site'],
        'cpf' => ['cpf', 'documento', 'identificação', 'cadastro'],
        'saudacao' => ['oi', 'olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi', 'oie']
    ];
    
    // Identificar intenção
    $intencao = 'geral';
    foreach ($palavras_chave as $intencao_tipo => $palavras) {
        foreach ($palavras as $palavra) {
            if (strpos($texto_lower, $palavra) !== false) {
                $intencao = $intencao_tipo;
                break 2;
            }
        }
    }
    
    echo "<p><strong>Mensagem:</strong> '$mensagem_teste'</p>";
    echo "<p><strong>Intenção detectada:</strong> '$intencao'</p>";
    
    // Gerar resposta com dados reais
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
    
    // Gerar resposta com dados reais
    switch ($intencao) {
        case 'fatura':
            if ($cliente_id) {
                $resposta_ia = buscarFaturasCliente($cliente_id, $mysqli);
                echo "<p style='color: green;'>✅ Sistema processou dados reais do cliente!</p>";
            } else {
                $resposta_ia = "Olá! Para verificar suas faturas, preciso do seu CPF.";
            }
            break;
        default:
            $resposta_ia = "Resposta padrão";
            break;
    }
    
    echo "<h4>Resposta gerada com dados reais:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_ia);
    echo "</pre>";
    
    // Enviar para você (47 96164699) em vez do cliente real
    echo "<h3>📤 Enviando Dados Reais para Você (47 96164699)</h3>";
    
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
        'message' => $resposta_ia
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
            echo "<p style='color: green;'>✅ Dados reais do cliente enviados com sucesso para você!</p>";
            
            // Salvar no banco de dados (como se fosse do cliente real)
            $mensagem_escaped = $mysqli->real_escape_string($resposta_ia);
            $data_hora = date('Y-m-d H:i:s');
            
            $sql_save = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, numero_whatsapp) 
                        VALUES (36, $cliente_id, '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'enviado', '$numero_limpo_cliente')";
            
            if ($mysqli->query($sql_save)) {
                echo "<p style='color: green;'>✅ Mensagem salva no banco de dados (cliente real)</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao salvar no banco: " . $mysqli->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro ao enviar mensagem</p>";
        }
    }
    
} else {
    echo "<p>❌ Cliente real NÃO encontrado no banco de dados</p>";
    echo "<p>Número testado: $numero_cliente_real</p>";
    echo "<p>Número limpo: $numero_limpo_cliente</p>";
    
    // Tentar buscar por outros formatos
    echo "<h3>🔍 Tentando outros formatos de busca</h3>";
    
    $formatos_teste = [
        '6993245042',
        '556993245042',
        '6993245042',
        '93245042'
    ];
    
    foreach ($formatos_teste as $formato) {
        $sql_teste = "SELECT id, nome, celular, contact_name FROM clientes 
                      WHERE celular LIKE '%$formato%' 
                      OR celular LIKE '%" . substr($formato, -8) . "%'
                      LIMIT 1";
        
        $result_teste = $mysqli->query($sql_teste);
        
        if ($result_teste && $result_teste->num_rows > 0) {
            $cliente_teste = $result_teste->fetch_assoc();
            echo "<p>✅ Cliente encontrado com formato '$formato':</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $cliente_teste['id'] . "</li>";
            echo "<li><strong>Nome:</strong> " . $cliente_teste['nome'] . "</li>";
            echo "<li><strong>Celular:</strong> " . $cliente_teste['celular'] . "</li>";
            echo "</ul>";
            break;
        }
    }
}

echo "<hr>";
echo "<h3>📊 Resumo do Teste com Cliente Real</h3>";
echo "<p><strong>Objetivo:</strong> Testar sistema com dados reais do cliente Detetive Aguiar</p>";
echo "<p><strong>Processo:</strong></p>";
echo "<ul>";
echo "<li>✅ Busca cliente real no banco de dados</li>";
echo "<li>✅ Verifica faturas reais (vencidas e a vencer)</li>";
echo "<li>✅ Processa mensagem 'faturas' com dados reais</li>";
echo "<li>✅ Gera resposta com nova formatação</li>";
echo "<li>✅ Envia para seu número (47 96164699) em vez do cliente real</li>";
echo "</ul>";

echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver os dados reais do cliente!</strong></p>";
?> 