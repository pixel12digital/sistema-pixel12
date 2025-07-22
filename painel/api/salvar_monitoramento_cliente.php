<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

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