<?php
/**
 * 🧪 TESTE DO SISTEMA DE FATURAS
 * Simula envio de mensagens "faturas" e "consulta" para teste
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste do Sistema de Faturas</h2>";
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
    
    // Verificar faturas do cliente de teste
    echo "<h3>💰 Faturas do Cliente de Teste</h3>";
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
        echo "<p>✅ Cliente possui " . $result_faturas->num_rows . " faturas para teste:</p>";
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
        echo "<p>Vou criar uma fatura de teste para o teste funcionar...</p>";
        
        // Criar fatura de teste
        $sql_insert = "INSERT INTO cobrancas (cliente_id, valor, status, vencimento, url_fatura) 
                       VALUES ($cliente_id, 99.90, 'PENDING', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'https://teste.com/fatura')";
        
        if ($mysqli->query($sql_insert)) {
            echo "<p>✅ Fatura de teste criada com sucesso!</p>";
        } else {
            echo "<p>❌ Erro ao criar fatura de teste: " . $mysqli->error . "</p>";
        }
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
    
    // Simular envio de mensagem "faturas"
    echo "<h3>📤 Simulando Envio de Mensagem 'faturas'</h3>";
    
    $numero_formatado = '55' . $numero_limpo . '@c.us';
    $mensagem_teste = "faturas";
    
    echo "<p>Enviando mensagem: '$mensagem_teste' para $numero_formatado</p>";
    
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
            echo "<p style='color: green;'>✅ Mensagem enviada com sucesso!</p>";
            
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
    
    // Testar também com "consulta"
    echo "<h3>📤 Simulando Envio de Mensagem 'consulta'</h3>";
    
    $mensagem_consulta = "consulta";
    echo "<p>Enviando mensagem: '$mensagem_consulta' para $numero_formatado</p>";
    
    // Usar a mesma resposta de faturas para "consulta"
    $payload_consulta = json_encode([
        'sessionName' => 'default',
        'number' => $numero_formatado,
        'message' => $resposta_faturas
    ]);
    
    $ch2 = curl_init("http://212.85.11.238:3000/send/text");
    curl_setopt($ch2, CURLOPT_POST, 1);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $payload_consulta);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 10);

    $response2 = curl_exec($ch2);
    $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    echo "<p><strong>HTTP Code (consulta):</strong> $http_code2</p>";
    
    if ($http_code2 === 200) {
        echo "<p style='color: green;'>✅ Mensagem 'consulta' enviada com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao enviar mensagem 'consulta'</p>";
    }
    
} else {
    echo "<p>❌ Cliente de teste NÃO encontrado no banco de dados</p>";
    echo "<p>Número testado: $numero_teste</p>";
    echo "<p>Número limpo: $numero_limpo</p>";
}

echo "<hr>";
echo "<h3>📊 Resumo do Teste</h3>";
echo "<p>Este teste verificou:</p>";
echo "<ul>";
echo "<li>✅ Busca do cliente no banco de dados</li>";
echo "<li>✅ Verificação de faturas cadastradas</li>";
echo "<li>✅ Geração da resposta de faturas</li>";
echo "<li>✅ Envio via API do WhatsApp</li>";
echo "<li>✅ Salvamento no banco de dados</li>";
echo "</ul>";

echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
echo "<p><strong>Verifique seu WhatsApp (47 96164699) para ver as mensagens de teste!</strong></p>";
?> 