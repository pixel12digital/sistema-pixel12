<?php
/**
 * Teste direto do webhook (sem HTTP)
 */

echo "=== TESTE DIRETO DO WEBHOOK ===\n\n";

// Simular environment do webhook
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_USER_AGENT'] = 'WhatsApp-Test';
$_SERVER['HTTP_REFERER'] = '';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Dados de teste
$dados_teste = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'Teste direto webhook - ' . date('H:i:s'),
    'message' => 'Teste direto webhook - ' . date('H:i:s')
];

echo "Dados do teste:\n";
echo json_encode($dados_teste, JSON_PRETTY_PRINT) . "\n\n";

// Simular entrada JSON
$mock_input = json_encode($dados_teste);

// Override file_get_contents para simular php://input
function custom_file_get_contents($filename) {
    global $mock_input;
    if ($filename === 'php://input') {
        return $mock_input;
    }
    return file_get_contents($filename);
}

// Testar diretamente as funções do webhook
echo "EXECUTANDO LÓGICA DO WEBHOOK:\n\n";

require_once 'config.php';
require_once 'painel/db.php';

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }
    
    // Simular recebimento de dados
    $input = $mock_input;
    $dados = json_decode($input, true);
    
    if (!$dados) {
        throw new Exception("JSON inválido");
    }
    
    echo "✅ JSON decodificado com sucesso\n";
    
    // Processar dados
    $numero_remetente = str_replace('@c.us', '', $dados['from'] ?? '');
    $mensagem = $dados['body'] ?? $dados['message'] ?? '';
    $canal_id = 36; // Canal Ana
    
    echo "Remetente: $numero_remetente\n";
    echo "Mensagem: $mensagem\n";
    echo "Canal ID: $canal_id\n\n";
    
    // 1. Encontrar ou criar cliente
    $cliente_id = null;
    $numero_limpo = preg_replace('/[^0-9]/', '', $numero_remetente);
    
    echo "Procurando cliente com número: $numero_limpo\n";
    
    $stmt = $mysqli->prepare("SELECT id, nome FROM clientes WHERE celular = ? OR celular = ? OR telefone = ? LIMIT 1");
    if ($stmt) {
        $numero_formatado = "+$numero_limpo";
        $stmt->bind_param('sss', $numero_limpo, $numero_formatado, $numero_limpo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $cliente_id = $row['id'];
            echo "✅ Cliente existente encontrado: ID $cliente_id - {$row['nome']}\n";
        } else {
            // Criar novo cliente
            $stmt_create = $mysqli->prepare("INSERT INTO clientes (nome, celular, data_criacao) VALUES (?, ?, NOW())");
            if ($stmt_create) {
                $nome_temporario = "WhatsApp " . substr($numero_limpo, -4);
                $stmt_create->bind_param('ss', $nome_temporario, $numero_limpo);
                if ($stmt_create->execute()) {
                    $cliente_id = $mysqli->insert_id;
                    echo "✅ Novo cliente criado: ID $cliente_id - $nome_temporario\n";
                }
                $stmt_create->close();
            }
        }
        $stmt->close();
    }
    
    if (!$cliente_id) {
        throw new Exception("Não foi possível criar/encontrar cliente");
    }
    
    // 2. Salvar mensagem
    echo "\nSalvando mensagem...\n";
    $numero_cliente = preg_replace('/[^0-9]/', '', $numero_remetente);
    
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, cliente_id, numero_whatsapp, mensagem, direcao, data_hora, tipo) VALUES (?, ?, ?, ?, 'recebido', NOW(), 'text')");
    if ($stmt) {
        $stmt->bind_param('iiss', $canal_id, $cliente_id, $numero_cliente, $mensagem);
        if ($stmt->execute()) {
            $message_id = $mysqli->insert_id;
            echo "✅ Mensagem salva: ID $message_id\n";
        } else {
            throw new Exception("Erro ao salvar mensagem: " . $mysqli->error);
        }
        $stmt->close();
    }
    
    // 3. Verificar se aparecerá no chat
    echo "\nVerificando se aparecerá no chat...\n";
    $result = $mysqli->query("
        SELECT c.id, c.nome, c.celular, COUNT(m.id) as total_msgs,
               MAX(m.data_hora) as ultima_msg
        FROM clientes c
        LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
        WHERE c.id = $cliente_id
        GROUP BY c.id
    ");
    
    if ($result && $conv = $result->fetch_assoc()) {
        echo "✅ Cliente aparecerá no chat:\n";
        echo "   ID: {$conv['id']}\n";
        echo "   Nome: {$conv['nome']}\n";
        echo "   Celular: {$conv['celular']}\n";
        echo "   Total mensagens: {$conv['total_msgs']}\n";
        echo "   Última mensagem: {$conv['ultima_msg']}\n";
    }
    
    $mysqli->close();
    echo "\n✅ TESTE CONCLUÍDO COM SUCESSO!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?> 