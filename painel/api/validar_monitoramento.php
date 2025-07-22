<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

// Receber cliente_id
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode([
        'success' => false,
        'error' => 'ID do cliente não informado'
    ]);
    exit;
}

try {
    // Verificar se o cliente existe
    $sql_cliente = "SELECT id, nome, celular FROM clientes WHERE id = $cliente_id LIMIT 1";
    $result_cliente = $mysqli->query($sql_cliente);
    
    if (!$result_cliente || $result_cliente->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Cliente não encontrado'
        ]);
        exit;
    }
    
    $cliente = $result_cliente->fetch_assoc();
    
    // Verificar se o cliente tem celular cadastrado
    if (empty($cliente['celular'])) {
        echo json_encode([
            'success' => false,
            'pode_monitorar' => false,
            'motivo' => 'Cliente não possui número de celular cadastrado'
        ]);
        exit;
    }
    
    // Buscar cobranças do cliente
    $sql_cobrancas = "SELECT 
                        id, 
                        valor, 
                        vencimento, 
                        status,
                        DATEDIFF(CURDATE(), vencimento) as dias_vencido
                      FROM cobrancas 
                      WHERE cliente_id = $cliente_id 
                      ORDER BY vencimento ASC";
    
    $result_cobrancas = $mysqli->query($sql_cobrancas);
    
    if (!$result_cobrancas) {
        throw new Exception("Erro ao buscar cobranças: " . $mysqli->error);
    }
    
    $cobrancas = [];
    $total_cobrancas = 0;
    $cobrancas_vencidas = 0;
    $valor_total_vencido = 0;
    $cobrancas_pagas = 0;
    $valor_total_pago = 0;
    
    while ($cobranca = $result_cobrancas->fetch_assoc()) {
        $total_cobrancas++;
        $cobranca['dias_vencido'] = intval($cobranca['dias_vencido']);
        
        if (in_array($cobranca['status'], ['PENDING', 'OVERDUE']) && $cobranca['dias_vencido'] > 0) {
            $cobrancas_vencidas++;
            $valor_total_vencido += floatval($cobranca['valor']);
        } elseif (in_array($cobranca['status'], ['RECEIVED', 'CONFIRMED'])) {
            $cobrancas_pagas++;
            $valor_total_pago += floatval($cobranca['valor']);
        }
        
        $cobrancas[] = $cobranca;
    }
    
    // Lógica de validação inteligente
    $pode_monitorar = false;
    $motivo = '';
    
    if ($total_cobrancas === 0) {
        $motivo = 'Cliente não possui cobranças cadastradas';
    } elseif ($cobrancas_vencidas === 0) {
        if ($cobrancas_pagas > 0 && $cobrancas_pagas === $total_cobrancas) {
            $motivo = 'Todas as cobranças do cliente já foram pagas/recebidas';
        } else {
            $motivo = 'Cliente não possui cobranças vencidas';
        }
    } else {
        // Cliente tem cobranças vencidas - pode monitorar
        $pode_monitorar = true;
        $motivo = "Cliente possui $cobrancas_vencidas cobrança(s) vencida(s) - R$ " . number_format($valor_total_vencido, 2, ',', '.');
    }
    
    // Verificar se já está sendo monitorado
    $sql_monitoramento = "SELECT monitorado FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1";
    $result_monitoramento = $mysqli->query($sql_monitoramento);
    $ja_monitorado = false;
    
    if ($result_monitoramento && $result_monitoramento->num_rows > 0) {
        $monitoramento = $result_monitoramento->fetch_assoc();
        $ja_monitorado = $monitoramento['monitorado'] == 1;
    }
    
    // Se já está monitorado, permitir desmonitorar
    if ($ja_monitorado) {
        $pode_monitorar = true;
        $motivo = 'Cliente já está sendo monitorado';
    }
    
    // Verificar última mensagem enviada
    $sql_ultima_mensagem = "SELECT data_hora, tipo 
                           FROM mensagens_comunicacao 
                           WHERE cliente_id = $cliente_id 
                           AND tipo = 'cobranca_vencida'
                           ORDER BY data_hora DESC 
                           LIMIT 1";
    
    $result_ultima_mensagem = $mysqli->query($sql_ultima_mensagem);
    $ultima_mensagem = null;
    
    if ($result_ultima_mensagem && $result_ultima_mensagem->num_rows > 0) {
        $ultima_mensagem = $result_ultima_mensagem->fetch_assoc();
    }
    
    echo json_encode([
        'success' => true,
        'pode_monitorar' => $pode_monitorar,
        'motivo' => $motivo,
        'cliente' => [
            'id' => $cliente['id'],
            'nome' => $cliente['nome'],
            'celular' => $cliente['celular']
        ],
        'estatisticas' => [
            'total_cobrancas' => $total_cobrancas,
            'cobrancas_vencidas' => $cobrancas_vencidas,
            'valor_total_vencido' => $valor_total_vencido,
            'cobrancas_pagas' => $cobrancas_pagas,
            'valor_total_pago' => $valor_total_pago,
            'ja_monitorado' => $ja_monitorado
        ],
        'ultima_mensagem' => $ultima_mensagem,
        'cobrancas' => $cobrancas
    ]);

} catch (Exception $e) {
    error_log("Erro ao validar monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'pode_monitorar' => false,
        'motivo' => 'Erro interno do sistema'
    ]);
}
?> 