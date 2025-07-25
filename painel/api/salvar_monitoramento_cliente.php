<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../config.php');
require_once('../db.php');

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cliente_id'])) {
    echo json_encode(['success' => false, 'error' => 'Cliente ID não informado']);
    exit;
}

$cliente_id = intval($input['cliente_id']);
$monitorado = isset($input['monitorado']) ? intval($input['monitorado']) : 0;

try {
    // Verificar se cliente existe
    $cliente = $mysqli->query("SELECT id, nome FROM clientes WHERE id = $cliente_id LIMIT 1")->fetch_assoc();
    
    if (!$cliente) {
        echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
        exit;
    }

    // Se está tentando adicionar ao monitoramento, validar se pode
    if ($monitorado) {
        // Buscar cobranças do cliente
        $sql_cobrancas = "SELECT 
                            id, 
                            valor, 
                            vencimento, 
                            status,
                            DATEDIFF(CURDATE(), vencimento) as dias_vencido
                          FROM cobrancas 
                          WHERE cliente_id = $cliente_id";
        
        $result_cobrancas = $mysqli->query($sql_cobrancas);
        
        if (!$result_cobrancas) {
            throw new Exception("Erro ao buscar cobranças: " . $mysqli->error);
        }
        
        $total_cobrancas = 0;
        $cobrancas_vencidas = 0;
        $cobrancas_pagas = 0;
        
        while ($cobranca = $result_cobrancas->fetch_assoc()) {
            $total_cobrancas++;
            
            if (in_array($cobranca['status'], ['PENDING', 'OVERDUE']) && intval($cobranca['dias_vencido']) > 0) {
                $cobrancas_vencidas++;
            } elseif (in_array($cobranca['status'], ['RECEIVED', 'CONFIRMED'])) {
                $cobrancas_pagas++;
            }
        }
        
        // Validar se pode monitorar
        if ($total_cobrancas === 0) {
            echo json_encode(['success' => false, 'error' => 'Cliente não possui cobranças cadastradas']);
            exit;
        } elseif ($cobrancas_vencidas === 0) {
            if ($cobrancas_pagas > 0 && $cobrancas_pagas === $total_cobrancas) {
                echo json_encode(['success' => false, 'error' => 'Todas as cobranças do cliente já foram pagas/recebidas']);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'Cliente não possui cobranças vencidas']);
                exit;
            }
        }
    }

    // Verificar se já existe registro de monitoramento
    $existe = $mysqli->query("SELECT id FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1")->fetch_assoc();

    if ($existe) {
        // Atualizar registro existente
        $sql = "UPDATE clientes_monitoramento SET 
                monitorado = $monitorado,
                data_atualizacao = NOW()
                WHERE cliente_id = $cliente_id";
    } else {
        // Criar novo registro
        $sql = "INSERT INTO clientes_monitoramento (cliente_id, monitorado, data_criacao, data_atualizacao) 
                VALUES ($cliente_id, $monitorado, NOW(), NOW())";
    }

    if (!$mysqli->query($sql)) {
        throw new Exception("Erro ao salvar monitoramento: " . $mysqli->error);
    }

    // Log da ação
    $acao = $monitorado ? 'adicionado ao' : 'removido do';
    $log_data = date('Y-m-d H:i:s') . " - Cliente {$cliente['nome']} (ID: $cliente_id) $acao monitoramento automático\n";
    file_put_contents('../logs/monitoramento_clientes.log', $log_data, FILE_APPEND);

    // Após salvar monitoramento, se ativado, enviar mensagem de monitoramento por WhatsApp e e-mail
    if ($monitorado) {
        // Buscar dados do cliente
        $res_cli = $mysqli->query("SELECT nome, celular, email FROM clientes WHERE id = $cliente_id LIMIT 1");
        $cli = $res_cli ? $res_cli->fetch_assoc() : null;
        if ($cli && $cli['celular']) {
            $mensagem = "Olá {$cli['nome']}!\n\nSeu cadastro foi ativado para monitoramento automático de cobranças. Você receberá lembretes de vencimento e notificações importantes por WhatsApp e e-mail (se cadastrado).\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital";
            // Enviar WhatsApp
            $numero_limpo = preg_replace('/\D/', '', $cli['celular']);
            $numero_formatado = '55' . $numero_limpo . '@c.us';
            $payload = json_encode([
                'to' => $numero_formatado,
                'message' => $mensagem
            ]);
            $ch = curl_init("http://212.85.11.238:3000/send");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            $log_envio = date('Y-m-d H:i:s') . " - WhatsApp monitoramento para cliente $cliente_id ({$cli['nome']}): ";
            if ($error) {
                $log_envio .= "ERRO: $error\n";
            } else {
                $log_envio .= ($http_code === 200 ? 'ENVIADO' : 'FALHA') . "\n";
            }
            file_put_contents('../logs/monitoramento_clientes.log', $log_envio, FILE_APPEND);
            // Enviar e-mail se houver
            if ($cli['email'] && filter_var($cli['email'], FILTER_VALIDATE_EMAIL)) {
                $assunto = 'Ativação de Monitoramento - Pixel12 Digital';
                $headers = "From: Pixel12 Digital <nao-responder@pixel12digital.com.br>\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $enviado_email = mail($cli['email'], $assunto, $mensagem, $headers);
                $log_email = date('Y-m-d H:i:s') . " - Email monitoramento para $cliente_id ({$cli['email']}): " . ($enviado_email ? 'ENVIADO' : 'FALHA') . "\n";
                file_put_contents('../logs/monitoramento_clientes.log', $log_email, FILE_APPEND);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Status de monitoramento atualizado com sucesso',
        'cliente_id' => $cliente_id,
        'monitorado' => $monitorado
    ]);

} catch (Exception $e) {
    error_log("Erro ao salvar monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 