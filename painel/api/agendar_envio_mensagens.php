<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Receber dados
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cliente_id']) || !isset($input['mensagem'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$cliente_id = intval($input['cliente_id']);
$mensagem = $input['mensagem'];
$tipo = $input['tipo'] ?? 'cobranca_vencida';
$prioridade = $input['prioridade'] ?? 'normal'; // alta, normal, baixa

try {
    // Buscar cliente
    $cliente = $mysqli->query("SELECT nome, celular FROM clientes WHERE id = $cliente_id LIMIT 1")->fetch_assoc();
    
    if (!$cliente) {
        throw new Exception("Cliente não encontrado");
    }
    
    // Configurações de horário
    $config_horarios = [
        'inicio_dia' => '09:00',
        'fim_dia' => '18:00',
        'intervalo_min' => 3, // minutos entre mensagens
        'max_por_hora' => 10, // máximo de mensagens por hora
        'max_por_dia' => 50   // máximo de mensagens por dia
    ];
    
    // Verificar quantas mensagens já foram enviadas hoje
    $hoje = date('Y-m-d');
    $sql_count = "SELECT COUNT(*) as total FROM mensagens_agendadas WHERE DATE(data_agendada) = '$hoje' AND status = 'agendada'";
    $result_count = $mysqli->query($sql_count);
    $total_hoje = $result_count->fetch_assoc()['total'];
    
    if ($total_hoje >= $config_horarios['max_por_dia']) {
        throw new Exception("Limite diário de mensagens atingido ($total_hoje/{$config_horarios['max_por_dia']})");
    }
    
    // Calcular horário de envio
    $horario_envio = calcularHorarioEnvio($config_horarios, $prioridade);
    
    // Salvar mensagem agendada
    $mensagem_escaped = $mysqli->real_escape_string($mensagem);
    $tipo_escaped = $mysqli->real_escape_string($tipo);
    $prioridade_escaped = $mysqli->real_escape_string($prioridade);
    
    $sql = "INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
            VALUES ($cliente_id, '$mensagem_escaped', '$tipo_escaped', '$prioridade_escaped', '$horario_envio', 'agendada', NOW())";
    
    if (!$mysqli->query($sql)) {
        throw new Exception("Erro ao agendar mensagem: " . $mysqli->error);
    }
    
    $agendamento_id = $mysqli->insert_id;
    
    // Log do agendamento
    $log_data = date('Y-m-d H:i:s') . " - Mensagem agendada para cliente $cliente_id ({$cliente['nome']}) - Horário: $horario_envio - Prioridade: $prioridade\n";
    file_put_contents('../logs/agendamento_mensagens.log', $log_data, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Mensagem agendada com sucesso',
        'agendamento_id' => $agendamento_id,
        'horario_envio' => $horario_envio,
        'cliente_nome' => $cliente['nome'],
        'total_hoje' => $total_hoje + 1
    ]);

} catch (Exception $e) {
    error_log("Erro ao agendar mensagem: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Calcula o horário ideal para envio da mensagem
 */
function calcularHorarioEnvio($config, $prioridade) {
    $hoje = date('Y-m-d');
    $inicio = strtotime("$hoje {$config['inicio_dia']}");
    $fim = strtotime("$hoje {$config['fim_dia']}");
    
    // Buscar mensagens já agendadas para hoje
    global $mysqli;
    $sql = "SELECT data_agendada FROM mensagens_agendadas 
            WHERE DATE(data_agendada) = '$hoje' AND status = 'agendada'
            ORDER BY data_agendada ASC";
    
    $result = $mysqli->query($sql);
    $mensagens_agendadas = [];
    
    while ($row = $result->fetch_assoc()) {
        $mensagens_agendadas[] = strtotime($row['data_agendada']);
    }
    
    // Definir horário baseado na prioridade
    switch ($prioridade) {
        case 'alta':
            // Enviar nos próximos 30 minutos
            $horario_base = time() + (30 * 60);
            break;
        case 'normal':
            // Distribuir ao longo do dia
            $horario_base = $inicio + (rand(0, 8) * 60 * 60); // 0-8 horas após início
            break;
        case 'baixa':
            // Enviar no final do dia
            $horario_base = $fim - (rand(1, 3) * 60 * 60); // 1-3 horas antes do fim
            break;
        default:
            $horario_base = $inicio + (rand(2, 6) * 60 * 60);
    }
    
    // Ajustar horário para respeitar intervalo mínimo
    $horario_final = $horario_base;
    
    foreach ($mensagens_agendadas as $msg_time) {
        $diff = abs($horario_final - $msg_time);
        if ($diff < ($config['intervalo_min'] * 60)) {
            // Ajustar horário para respeitar intervalo
            $horario_final = $msg_time + ($config['intervalo_min'] * 60);
        }
    }
    
    // Garantir que está dentro do horário comercial
    if ($horario_final < $inicio) {
        $horario_final = $inicio + (rand(0, 2) * 60 * 60);
    } elseif ($horario_final > $fim) {
        $horario_final = $fim - (rand(1, 2) * 60 * 60);
    }
    
    return date('Y-m-d H:i:s', $horario_final);
}
?> 