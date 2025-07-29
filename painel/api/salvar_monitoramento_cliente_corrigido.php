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

    // Preparar resposta de sucesso
    $response = [
        'success' => true,
        'message' => 'Status de monitoramento atualizado com sucesso',
        'cliente_id' => $cliente_id,
        'monitorado' => $monitorado,
        'avisos' => []
    ];

    // Após salvar monitoramento com sucesso, tentar enviar mensagens (sem bloquear o salvamento)
    if ($monitorado) {
        try {
            // Buscar dados do cliente
            $res_cli = $mysqli->query("SELECT nome, celular, email FROM clientes WHERE id = $cliente_id LIMIT 1");
            $cli = $res_cli ? $res_cli->fetch_assoc() : null;
            
            if ($cli && $cli['celular']) {
                $mensagem = "Olá {$cli['nome']}!\n\nSeu cadastro foi ativado para monitoramento automático de cobranças. Você receberá lembretes de vencimento e notificações importantes por WhatsApp e e-mail (se cadastrado).\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital";
                
                // Enviar WhatsApp (sem bloquear em caso de erro)
                try {
                    $numero_limpo = preg_replace('/\D/', '', $cli['celular']);
                    $numero_formatado = '55' . $numero_limpo . '@c.us';
                    $payload = json_encode([
                        'sessionName' => 'default',
                        'number' => $numero_formatado,
                        'message' => $mensagem
                    ]);
                    
                    $ch = curl_init("http://212.85.11.238:3000/send/text");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $response_whatsapp = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    curl_close($ch);
                    
                    $log_envio = date('Y-m-d H:i:s') . " - WhatsApp monitoramento para cliente $cliente_id ({$cli['nome']}): ";
                    if ($error) {
                        $log_envio .= "ERRO: $error\n";
                        $response['avisos'][] = "WhatsApp: Erro de conexão ($error)";
                    } else {
                        $status = ($http_code === 200 ? 'ENVIADO' : 'FALHA');
                        $log_envio .= $status . "\n";
                        if ($http_code !== 200) {
                            $response['avisos'][] = "WhatsApp: Falha no envio (HTTP $http_code)";
                        }
                    }
                    file_put_contents('../logs/monitoramento_clientes.log', $log_envio, FILE_APPEND);
                    
                } catch (Exception $e) {
                    $log_erro = date('Y-m-d H:i:s') . " - Erro WhatsApp monitoramento cliente $cliente_id: " . $e->getMessage() . "\n";
                    file_put_contents('../logs/monitoramento_clientes.log', $log_erro, FILE_APPEND);
                    $response['avisos'][] = "WhatsApp: " . $e->getMessage();
                }
                
                // Enviar e-mail se houver (sem bloquear em caso de erro)
                if ($cli['email'] && filter_var($cli['email'], FILTER_VALIDATE_EMAIL)) {
                    try {
                        $assunto = 'Ativação de Monitoramento - Pixel12 Digital';
                        $headers = "From: Pixel12 Digital <nao-responder@pixel12digital.com.br>\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        $enviado_email = mail($cli['email'], $assunto, $mensagem, $headers);
                        
                        $log_email = date('Y-m-d H:i:s') . " - Email monitoramento para $cliente_id ({$cli['email']}): " . ($enviado_email ? 'ENVIADO' : 'FALHA') . "\n";
                        file_put_contents('../logs/monitoramento_clientes.log', $log_email, FILE_APPEND);
                        
                        if (!$enviado_email) {
                            $response['avisos'][] = "Email: Falha no envio";
                        }
                    } catch (Exception $e) {
                        $log_erro = date('Y-m-d H:i:s') . " - Erro Email monitoramento cliente $cliente_id: " . $e->getMessage() . "\n";
                        file_put_contents('../logs/monitoramento_clientes.log', $log_erro, FILE_APPEND);
                        $response['avisos'][] = "Email: " . $e->getMessage();
                    }
                }
            }
            
            // AGENDAR MENSAGENS AUTOMATICAMENTE PARA FATURAS VENCIDAS (sem bloquear em caso de erro)
            try {
                agendarMensagensFaturasVencidas($cliente_id, $mysqli);
            } catch (Exception $e) {
                $log_erro = date('Y-m-d H:i:s') . " - Erro agendamento mensagens cliente $cliente_id: " . $e->getMessage() . "\n";
                file_put_contents('../logs/monitoramento_clientes.log', $log_erro, FILE_APPEND);
                $response['avisos'][] = "Agendamento: " . $e->getMessage();
            }
            
        } catch (Exception $e) {
            // Log do erro geral, mas não falhar o salvamento
            $log_erro = date('Y-m-d H:i:s') . " - Erro geral envio mensagens cliente $cliente_id: " . $e->getMessage() . "\n";
            file_put_contents('../logs/monitoramento_clientes.log', $log_erro, FILE_APPEND);
            $response['avisos'][] = "Mensagens: " . $e->getMessage();
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Erro ao salvar monitoramento: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Agenda mensagens automaticamente para faturas vencidas do cliente
 */
function agendarMensagensFaturasVencidas($cliente_id, $mysqli) {
    try {
        // Buscar faturas vencidas do cliente
        $sql = "SELECT 
                    cob.id,
                    cob.valor,
                    cob.vencimento,
                    cob.url_fatura,
                    cob.status,
                    c.nome as cliente_nome,
                    c.celular,
                    c.contact_name,
                    DATEDIFF(CURDATE(), cob.vencimento) as dias_vencido
                FROM cobrancas cob
                JOIN clientes c ON cob.cliente_id = c.id
                WHERE cob.cliente_id = $cliente_id
                AND cob.status IN ('PENDING', 'OVERDUE')
                AND cob.vencimento < CURDATE()
                ORDER BY cob.vencimento ASC";
        
        $result = $mysqli->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            // Não há faturas vencidas para agendar
            return;
        }
        
        $faturas = [];
        while ($row = $result->fetch_assoc()) {
            $faturas[] = $row;
        }
        
        // Agrupar faturas por estratégia de envio
        $faturas_vencidas_recentes = []; // Até 7 dias vencidas
        $faturas_vencidas_medias = [];   // 8-30 dias vencidas
        $faturas_vencidas_antigas = [];  // Mais de 30 dias vencidas
        
        foreach ($faturas as $fatura) {
            $dias_vencida = intval($fatura['dias_vencido']);
            
            if ($dias_vencida <= 7) {
                $faturas_vencidas_recentes[] = $fatura;
            } elseif ($dias_vencida <= 30) {
                $faturas_vencidas_medias[] = $fatura;
            } else {
                $faturas_vencidas_antigas[] = $fatura;
            }
        }
        
        // Agendar mensagens com diferentes estratégias
        $mensagens_agendadas = 0;
        
        // 1. Faturas vencidas recentes (até 7 dias) - enviar nos próximos 2 dias
        if (!empty($faturas_vencidas_recentes)) {
            $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_recentes, $faturas[0]);
            $horario_envio = calcularHorarioAdequado('+1 day 10:00:00'); // Amanhã às 10h
            
            if (agendarMensagem($cliente_id, $mensagem, $horario_envio, 'alta', $mysqli)) {
                $mensagens_agendadas++;
            }
        }
        
        // 2. Faturas vencidas médias (8-30 dias) - enviar em 3 dias
        if (!empty($faturas_vencidas_medias)) {
            $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_medias, $faturas[0]);
            $horario_envio = calcularHorarioAdequado('+3 days 14:00:00'); // Em 3 dias às 14h
            
            if (agendarMensagem($cliente_id, $mensagem, $horario_envio, 'normal', $mysqli)) {
                $mensagens_agendadas++;
            }
        }
        
        // 3. Faturas vencidas antigas (mais de 30 dias) - enviar em 7 dias
        if (!empty($faturas_vencidas_antigas)) {
            $mensagem = montarMensagemCobrancaVencida($faturas_vencidas_antigas, $faturas[0]);
            $horario_envio = calcularHorarioAdequado('+7 days 16:00:00'); // Em 7 dias às 16h
            
            if (agendarMensagem($cliente_id, $mensagem, $horario_envio, 'baixa', $mysqli)) {
                $mensagens_agendadas++;
            }
        }
        
        // Log do agendamento
        $log_data = date('Y-m-d H:i:s') . " - Agendadas $mensagens_agendadas mensagens para cliente $cliente_id com " . count($faturas) . " faturas vencidas\n";
        file_put_contents('../logs/agendamento_mensagens.log', $log_data, FILE_APPEND);
        
    } catch (Exception $e) {
        error_log("Erro ao agendar mensagens para cliente $cliente_id: " . $e->getMessage());
        throw $e; // Re-throw para ser capturado pelo caller
    }
}

/**
 * Calcula horário adequado para envio, respeitando horários comerciais e evitando feriados/finais de semana
 */
function calcularHorarioAdequado($horario_proposto) {
    $data_proposta = new DateTime($horario_proposto);
    
    // Verificar se é final de semana
    $dia_semana = $data_proposta->format('N'); // 1=Segunda, 7=Domingo
    if ($dia_semana >= 6) { // Sábado ou Domingo
        // Mover para segunda-feira
        $dias_para_adicionar = 8 - $dia_semana; // 2 para sábado, 1 para domingo
        $data_proposta->add(new DateInterval("P{$dias_para_adicionar}D"));
    }
    
    // Verificar se é feriado
    while (ehFeriado($data_proposta)) {
        $data_proposta->add(new DateInterval('P1D'));
        // Se chegou no final de semana, pular para segunda
        $dia_semana = $data_proposta->format('N');
        if ($dia_semana >= 6) {
            $dias_para_adicionar = 8 - $dia_semana;
            $data_proposta->add(new DateInterval("P{$dias_para_adicionar}D"));
        }
    }
    
    return $data_proposta->format('Y-m-d H:i:s');
}

/**
 * Verifica se uma data é feriado
 */
function ehFeriado($data) {
    $ano = $data->format('Y');
    $mes = $data->format('m');
    $dia = $data->format('d');
    
    // Feriados fixos
    $feriados_fixos = [
        '01-01' => 'Ano Novo',
        '04-21' => 'Tiradentes',
        '05-01' => 'Dia do Trabalho',
        '09-07' => 'Independência',
        '10-12' => 'Nossa Senhora',
        '11-02' => 'Finados',
        '11-15' => 'Proclamação da República',
        '12-25' => 'Natal'
    ];
    
    $chave = $mes . '-' . $dia;
    if (isset($feriados_fixos[$chave])) {
        return true;
    }
    
    // Feriados móveis (Páscoa, Carnaval, etc.)
    $pascoa = calcularPascoa($ano);
    $carnaval = clone $pascoa;
    $carnaval->sub(new DateInterval('P47D')); // 47 dias antes da Páscoa
    $corpus_christi = clone $pascoa;
    $corpus_christi->add(new DateInterval('P60D')); // 60 dias após a Páscoa
    
    $feriados_moveis = [
        $carnaval->format('Y-m-d') => 'Carnaval',
        $pascoa->format('Y-m-d') => 'Páscoa',
        $corpus_christi->format('Y-m-d') => 'Corpus Christi'
    ];
    
    $data_str = $data->format('Y-m-d');
    if (isset($feriados_moveis[$data_str])) {
        return true;
    }
    
    return false;
}

/**
 * Calcula a data da Páscoa para um ano específico (Algoritmo de Meeus/Jones/Butcher)
 */
function calcularPascoa($ano) {
    $a = $ano % 19;
    $b = floor($ano / 100);
    $c = $ano % 100;
    $d = floor($b / 4);
    $e = $b % 4;
    $f = floor(($b + 8) / 25);
    $g = floor(($b - $f + 1) / 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = floor($c / 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = floor(($a + 11 * $h + 22 * $l) / 451);
    $mes = floor(($h + $l - 7 * $m + 114) / 31);
    $dia = (($h + $l - 7 * $m + 114) % 31) + 1;
    
    return new DateTime("$ano-$mes-$dia");
}

/**
 * Monta mensagem de cobrança vencida
 */
function montarMensagemCobrancaVencida($faturas, $cliente_info) {
    $nome = $cliente_info['contact_name'] ?: $cliente_info['cliente_nome'];
    
    $mensagem = "Olá {$nome}! \n\n";
    $mensagem .= "⚠️ Você possui faturas em aberto:\n\n";
    
    $valor_total = 0;
    foreach ($faturas as $fatura) {
        $valor = number_format($fatura['valor'], 2, ',', '.');
        $vencimento = date('d/m/Y', strtotime($fatura['vencimento']));
        $dias_vencida = intval($fatura['dias_vencido']);
        
        $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor - Venceu em $vencimento ({$dias_vencida} dias vencida)\n";
        $valor_total += floatval($fatura['valor']);
    }
    
    $mensagem .= "\n💰 Valor total em aberto: R$ " . number_format($valor_total, 2, ',', '.') . "\n";
    $mensagem .= "🔗 Link para pagamento: {$faturas[0]['url_fatura']}\n\n";
    $mensagem .= "Para consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\n";
    $mensagem .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";
    
    return $mensagem;
}

/**
 * Agenda uma mensagem específica
 */
function agendarMensagem($cliente_id, $mensagem, $horario_envio, $prioridade, $mysqli) {
    try {
        $mensagem_escaped = $mysqli->real_escape_string($mensagem);
        $horario_escaped = $mysqli->real_escape_string($horario_envio);
        $prioridade_escaped = $mysqli->real_escape_string($prioridade);
        
        $sql = "INSERT INTO mensagens_agendadas (cliente_id, mensagem, tipo, prioridade, data_agendada, status, data_criacao) 
                VALUES ($cliente_id, '$mensagem_escaped', 'cobranca_vencida', '$prioridade_escaped', '$horario_escaped', 'agendada', NOW())";
        
        if ($mysqli->query($sql)) {
            return true;
        } else {
            error_log("Erro ao agendar mensagem: " . $mysqli->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao agendar mensagem: " . $e->getMessage());
        return false;
    }
}
?> 