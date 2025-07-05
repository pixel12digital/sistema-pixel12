<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    // Suporte a envio via formulário (multipart/form-data) e via JSON
    $is_form = isset($_POST['cliente_id']);
    if ($is_form) {
        $cliente_id = intval($_POST['cliente_id'] ?? 0);
        $canal_id = intval($_POST['canal_id'] ?? 0); // opcional, pode ser ajustado
        $mensagem = trim($_POST['mensagem'] ?? '');
        $anexo_path = null;
        if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION);
            $filename = 'anexo_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $dest = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $dest)) {
                $anexo_path = 'uploads/' . $filename;
            }
        }
        // Buscar canal_id do cliente (última conversa)
        if (!$canal_id && $cliente_id) {
            $res = $mysqli->query("SELECT canal_id FROM mensagens_comunicacao WHERE cliente_id = $cliente_id ORDER BY data_hora DESC LIMIT 1");
            $row = $res ? $res->fetch_assoc() : null;
            $canal_id = $row ? intval($row['canal_id']) : 1;
        }
        if (!$cliente_id || !$canal_id || (!$mensagem && !$anexo_path)) {
            throw new Exception('Dados incompletos');
        }
        $cliente = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id")->fetch_assoc();
        if (!$cliente) throw new Exception('Cliente não encontrado');
        $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE id = $canal_id")->fetch_assoc();
        if (!$canal) throw new Exception('Canal não encontrado');
        $mensagem_escaped = $mysqli->real_escape_string($mensagem);
        $anexo_escaped = $anexo_path ? ("'" . $mysqli->real_escape_string($anexo_path) . "'") : 'NULL';
        $tipo = $anexo_path ? 'anexo' : 'texto';
        $data_hora = date('Y-m-d H:i:s');
        $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, anexo, tipo, data_hora, direcao, status) VALUES ($canal_id, $cliente_id, '$mensagem_escaped', $anexo_escaped, '$tipo', '$data_hora', 'enviado', 'enviado')";
        if (!$mysqli->query($sql)) throw new Exception('Erro ao salvar mensagem no banco: ' . $mysqli->error);
        header('Location: chat.php?cliente_id=' . $cliente_id);
        exit;
    }
    
    // Ler dados JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Dados inválidos');
    }
    
    $cliente_id = intval($data['cliente_id'] ?? 0);
    $canal_id = intval($data['canal_id'] ?? 0);
    $mensagem = trim($data['mensagem'] ?? '');
    
    if (!$cliente_id || !$canal_id || !$mensagem) {
        throw new Exception('Dados incompletos');
    }
    
    // Verificar se o cliente existe
    $cliente = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id")->fetch_assoc();
    if (!$cliente) {
        throw new Exception('Cliente não encontrado');
    }
    
    // Verificar se o canal existe e está conectado
    $canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE id = $canal_id AND status = 'conectado'")->fetch_assoc();
    if (!$canal) {
        throw new Exception('Canal não encontrado ou não conectado');
    }
    
    // Salvar mensagem no banco de dados
    $mensagem_escaped = $mysqli->real_escape_string($mensagem);
    $data_hora = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
            VALUES ($canal_id, $cliente_id, '$mensagem_escaped', 'texto', '$data_hora', 'enviado', 'enviado')";
    
    if (!$mysqli->query($sql)) {
        throw new Exception('Erro ao salvar mensagem no banco: ' . $mysqli->error);
    }
    
    $mensagem_id = $mysqli->insert_id;
    
    // Tentar enviar via API do WhatsApp (se disponível)
    $enviado_api = false;
    if ($canal['tipo'] === 'whatsapp') {
        try {
            // Buscar número do cliente
            $numero_cliente = preg_replace('/\D/', '', $cliente['celular'] ?? $cliente['telefone'] ?? '');
            
            if ($numero_cliente) {
                // Chamar API do WhatsApp
                $api_data = [
                    'canal_id' => $canal_id,
                    'numero' => $numero_cliente,
                    'mensagem' => $mensagem
                ];
                
                $ch = curl_init('http://app.pixel12digital.com.br:9100/api/send');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code === 200 && $response) {
                    $api_response = json_decode($response, true);
                    if ($api_response && isset($api_response['success']) && $api_response['success']) {
                        $enviado_api = true;
                        // Atualizar status da mensagem
                        $mysqli->query("UPDATE mensagens_comunicacao SET status = 'entregue' WHERE id = $mensagem_id");
                    }
                }
            }
        } catch (Exception $e) {
            // Log do erro, mas não falha o envio
            error_log("Erro ao enviar via API WhatsApp: " . $e->getMessage());
        }
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem enviada com sucesso',
        'mensagem_id' => $mensagem_id,
        'enviado_api' => $enviado_api,
        'data_hora' => $data_hora
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 