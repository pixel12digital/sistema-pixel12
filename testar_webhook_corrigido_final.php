<?php
/**
 * Teste do webhook corrigido
 */

echo "=== TESTE DO WEBHOOK CORRIGIDO ===\n\n";

// 1. Simular dados de mensagem do WhatsApp
$dados_teste = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us', // Canal Ana
    'body' => 'Teste do webhook corrigido - ' . date('H:i:s'),
    'message' => 'Teste do webhook corrigido - ' . date('H:i:s')
];

echo "Enviando dados para webhook:\n";
echo json_encode($dados_teste, JSON_PRETTY_PRINT) . "\n\n";

// 2. Fazer POST para o webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/loja-virtual-revenda/painel/receber_mensagem_ana_local.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "RESPOSTA DO WEBHOOK:\n";
echo "HTTP Code: $http_code\n";
if ($error) {
    echo "Erro cURL: $error\n";
}
echo "Resposta: $response\n\n";

// 3. Verificar no banco se a mensagem foi salva
require_once 'config.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    echo "VERIFICAÇÃO NO BANCO:\n";
    
    // Buscar mensagem recém criada
    $result = $mysqli->query("
        SELECT m.*, c.nome, c.celular 
        FROM mensagens_comunicacao m 
        LEFT JOIN clientes c ON m.cliente_id = c.id 
        WHERE m.mensagem LIKE '%Teste do webhook corrigido%' 
        ORDER BY m.id DESC 
        LIMIT 1
    ");
    
    if ($result && $row = $result->fetch_assoc()) {
        echo "✅ Mensagem encontrada no banco:\n";
        echo "   ID: {$row['id']}\n";
        echo "   Canal ID: {$row['canal_id']}\n";
        echo "   Cliente ID: {$row['cliente_id']}\n";
        echo "   Número WhatsApp: {$row['numero_whatsapp']}\n";
        echo "   Cliente: {$row['nome']} ({$row['celular']})\n";
        echo "   Mensagem: {$row['mensagem']}\n";
        echo "   Direção: {$row['direcao']}\n";
        echo "   Data: {$row['data_hora']}\n\n";
        
        // Verificar se cliente aparecerá no chat
        $conversa = $mysqli->query("
            SELECT c.id, c.nome, c.celular, COUNT(m.id) as total_msgs
            FROM clientes c
            LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
            WHERE c.id = {$row['cliente_id']}
            GROUP BY c.id
        ");
        
        if ($conversa && $conv = $conversa->fetch_assoc()) {
            echo "✅ Cliente aparecerá no chat:\n";
            echo "   Cliente: {$conv['nome']} ({$conv['celular']})\n";
            echo "   Total mensagens: {$conv['total_msgs']}\n";
        }
        
    } else {
        echo "❌ Mensagem NÃO encontrada no banco\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 