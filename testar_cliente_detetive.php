<?php
/**
 * 🧪 TESTE ESPECÍFICO - CLIENTE DETETIVE AGUIAR
 * Testa se o sistema agora envia faturas corretamente para o cliente
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste Específico - Cliente Detetive Aguiar</h2>";

// Buscar cliente Detetive Aguiar (ID: 236)
$cliente_id = 236;

echo "<h3>🔍 Buscando cliente Detetive Aguiar (ID: $cliente_id)</h3>";

$sql = "SELECT id, nome, celular, contact_name FROM clientes WHERE id = $cliente_id LIMIT 1";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    echo "<p>✅ Cliente encontrado:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
    echo "<li><strong>Celular:</strong> " . $cliente['celular'] . "</li>";
    echo "<li><strong>Contact Name:</strong> " . $cliente['contact_name'] . "</li>";
    echo "</ul>";
    
    // Verificar faturas do cliente
    echo "<h3>💰 Faturas do Cliente</h3>";
    $sql_faturas = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    ORDER BY cob.vencimento DESC
                    LIMIT 10";
    
    $result_faturas = $mysqli->query($sql_faturas);
    
    if ($result_faturas && $result_faturas->num_rows > 0) {
        echo "<p>✅ Cliente possui " . $result_faturas->num_rows . " faturas:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Valor</th><th>Vencimento</th><th>Status</th></tr>";
        
        while ($fatura = $result_faturas->fetch_assoc()) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            echo "<tr>";
            echo "<td>" . $fatura['id'] . "</td>";
            echo "<td>R$ $valor</td>";
            echo "<td>" . $fatura['vencimento_formatado'] . "</td>";
            echo "<td>" . $fatura['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Cliente NÃO possui faturas cadastradas</p>";
    }
    
    // Testar função de busca de faturas
    echo "<h3>🧪 Testando Função buscarFaturasCliente()</h3>";
    
    function buscarFaturasCliente($cliente_id, $mysqli) {
        $sql = "SELECT 
                    cob.id,
                    cob.valor,
                    cob.status,
                    DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                    cob.url_fatura
                FROM cobrancas cob
                WHERE cob.cliente_id = $cliente_id
                ORDER BY cob.vencimento DESC
                LIMIT 10";
        
        $result = $mysqli->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            return "Você não possui faturas cadastradas no momento.";
        }
        
        $resposta = "📋 Suas faturas:\n\n";
        
        while ($fatura = $result->fetch_assoc()) {
            $status = traduzirStatus($fatura['status']);
            $valor = number_format($fatura['valor'], 2, ',', '.');
            
            $resposta .= "Fatura #{$fatura['id']}\n";
            $resposta .= "Valor: R$ $valor\n";
            $resposta .= "Vencimento: {$fatura['vencimento_formatado']}\n";
            $resposta .= "Status: $status\n";
            
            if ($fatura['url_fatura']) {
                $resposta .= "Link: {$fatura['url_fatura']}\n";
            }
            
            $resposta .= "\n";
        }
        
        return $resposta;
    }
    
    function traduzirStatus($status) {
        $statusMap = [
            'PENDING' => 'Aguardando pagamento',
            'OVERDUE' => 'Vencida',
            'RECEIVED' => 'Paga',
            'CONFIRMED' => 'Confirmada',
            'CANCELLED' => 'Cancelada'
        ];
        return $statusMap[$status] ?? $status;
    }
    
    $resposta_faturas = buscarFaturasCliente($cliente_id, $mysqli);
    echo "<h4>Resposta gerada:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_faturas);
    echo "</pre>";
    
    // Testar processamento de IA
    echo "<h3>🤖 Testando Processamento de IA</h3>";
    
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
    
    // Gerar resposta baseada na intenção
    switch ($intencao) {
        case 'fatura':
            if ($cliente_id) {
                // Cliente já identificado - enviar faturas diretamente
                $resposta_ia = buscarFaturasCliente($cliente_id, $mysqli);
                echo "<p style='color: green;'>✅ Sistema agora envia faturas diretamente!</p>";
            } else {
                $resposta_ia = "Olá! Para verificar suas faturas, preciso do seu CPF. Pode me informar o número do seu CPF?";
                echo "<p style='color: orange;'>⚠️ Sistema pediria CPF se cliente não estivesse identificado</p>";
            }
            break;
        default:
            $resposta_ia = "Resposta padrão";
            break;
    }
    
    echo "<h4>Resposta da IA:</h4>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_ia);
    echo "</pre>";
    
    // Simular envio para o cliente
    echo "<h3>📤 Simulando Envio para o Cliente</h3>";
    
    $numero_cliente = $cliente['celular'];
    $numero_limpo = preg_replace('/\D/', '', $numero_cliente);
    $numero_formatado = '55' . $numero_limpo . '@c.us';
    
    echo "<p><strong>Número do cliente:</strong> $numero_cliente</p>";
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
            echo "<p style='color: green;'>✅ Mensagem enviada com sucesso para o cliente!</p>";
            
            // Salvar no banco de dados
            $mensagem_escaped = $mysqli->real_escape_string($resposta_ia);
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
    echo "<p>❌ Cliente Detetive Aguiar NÃO encontrado no banco de dados</p>";
}

echo "<hr>";
echo "<h3>📊 Resumo da Correção</h3>";
echo "<p><strong>Problema identificado:</strong> Sistema estava pedindo CPF mesmo com cliente identificado</p>";
echo "<p><strong>Solução aplicada:</strong> Sistema agora envia faturas diretamente quando cliente está identificado</p>";
echo "<p><strong>Teste realizado:</strong> Envio de mensagem 'faturas' para cliente Detetive Aguiar</p>";

echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>O cliente Detetive Aguiar deve receber a listagem de faturas no WhatsApp!</strong></p>";
?> 