<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';

try {
    // Buscar configurações do monitoramento
    $config = [
        'dias_minimos' => 1, // Só enviar após 1 dia vencido
        'limite_mensagens' => 1, // Máximo 1 mensagem por dia
        'monitorar_apenas_vencidas' => true, // Só monitorar PENDING/OVERDUE
        'verificar_status_asaas' => true // Verificar status real antes de enviar
    ];
    
    // Buscar clientes monitorados com cobranças vencidas
    $sql = "SELECT DISTINCT 
                c.id as cliente_id,
                c.nome as cliente_nome,
                c.celular as cliente_celular,
                c.contact_name,
                GROUP_CONCAT(
                    CONCAT(
                        cob.id, '|',
                        cob.valor, '|',
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y'), '|',
                        cob.url_fatura, '|',
                        cob.status
                    ) SEPARATOR '||'
                ) as faturas_info
            FROM clientes c
            JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            JOIN cobrancas cob ON c.id = cob.cliente_id
            WHERE cm.monitorado = 1
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.vencimento < CURDATE()
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY c.id, c.nome, c.celular, c.contact_name
            ORDER BY cob.vencimento ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar clientes monitorados: " . $mysqli->error);
    }

    $clientes_processados = 0;
    $mensagens_enviadas = 0;
    $erros = 0;
    $log_detalhado = [];

    while ($row = $result->fetch_assoc()) {
        $clientes_processados++;
        
        try {
            // Processar faturas do cliente
            $faturas = [];
            $faturas_info = explode('||', $row['faturas_info']);
            
            foreach ($faturas_info as $fatura_info) {
                $dados = explode('|', $fatura_info);
                if (count($dados) >= 5) {
                    $faturas[] = [
                        'id' => $dados[0],
                        'valor' => $dados[1],
                        'vencimento_formatado' => $dados[2],
                        'url_fatura' => $dados[3],
                        'status' => $dados[4]
                    ];
                }
            }

            // Verificar se já foi enviada mensagem hoje para este cliente
            $hoje = date('Y-m-d');
            $sql_check = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
                         WHERE cliente_id = {$row['cliente_id']} 
                         AND tipo = 'cobranca_vencida' 
                         AND DATE(data_hora) = '$hoje'";
            
            $check_result = $mysqli->query($sql_check);
            $check_data = $check_result->fetch_assoc();
            
            if ($check_data['total'] >= $config['limite_mensagens']) {
                $log_detalhado[] = "Cliente {$row['cliente_nome']}: Limite de mensagens diárias atingido";
                continue;
            }

            // Verificar dias mínimos vencidos
            $dias_vencido = 0;
            foreach ($faturas as $fatura) {
                $vencimento = DateTime::createFromFormat('d/m/Y', $fatura['vencimento_formatado']);
                $hoje = new DateTime();
                $dias = $hoje->diff($vencimento)->days;
                if ($dias > $dias_vencido) {
                    $dias_vencido = $dias;
                }
            }
            
            if ($dias_vencido < $config['dias_minimos']) {
                $log_detalhado[] = "Cliente {$row['cliente_nome']}: Apenas {$dias_vencido} dias vencido (mínimo: {$config['dias_minimos']})";
                continue;
            }

            // Verificar status real no Asaas se configurado
            if ($config['verificar_status_asaas']) {
                $status_atualizado = verificarStatusAsaas($row['cliente_id']);
                if ($status_atualizado) {
                    $log_detalhado[] = "Cliente {$row['cliente_nome']}: Status atualizado no Asaas";
                    
                    // Verificar se ainda há cobranças vencidas após atualização
                    $sql_verificar = "SELECT COUNT(*) as total FROM cobrancas 
                                    WHERE cliente_id = {$row['cliente_id']} 
                                    AND status IN ('PENDING', 'OVERDUE') 
                                    AND vencimento < CURDATE()";
                    $verificar_result = $mysqli->query($sql_verificar);
                    $verificar_data = $verificar_result->fetch_assoc();
                    
                    if ($verificar_data['total'] == 0) {
                        $log_detalhado[] = "Cliente {$row['cliente_nome']}: Todas as cobranças foram pagas, pulando envio";
                        continue;
                    }
                }
            }

            // Calcular valor total vencido
            $valor_total = array_sum(array_column($faturas, 'valor'));
            
            // Usar primeira fatura como link principal
            $link_pagamento = $faturas[0]['url_fatura'] ?? '';

            // Montar mensagem personalizada
            $nome = $row['contact_name'] ?: $row['cliente_nome'];
            $mensagem = "Olá {$nome}! \n\n";
            $mensagem .= "⚠️ Você possui faturas em aberto:\n\n";
            
            foreach ($faturas as $fatura) {
                $valor = number_format($fatura['valor'], 2, ',', '.');
                $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor - Venceu em {$fatura['vencimento_formatado']}\n";
            }
            
            $mensagem .= "\n💰 Valor total em aberto: R$ " . number_format($valor_total, 2, ',', '.') . "\n";
            $mensagem .= "🔗 Link para pagamento: $link_pagamento\n\n";
            $mensagem .= "Para consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\n";
            $mensagem .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";

            // Enviar mensagem via VPS
            $numero_limpo = preg_replace('/\D/', '', $row['cliente_celular']);
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

            if ($error) {
                throw new Exception("Erro de conexão: " . $error);
            }

            $response_data = json_decode($response, true);

            if ($http_code !== 200 || !$response_data || !isset($response_data['success'])) {
                throw new Exception("Erro na resposta da VPS: " . $response);
            }

            if (!$response_data['success']) {
                throw new Exception("Falha no envio: " . ($response_data['error'] ?? 'Erro desconhecido'));
            }

            // Salvar mensagem no banco
            $mensagem_escaped = $mysqli->real_escape_string($mensagem);
            $data_hora = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) 
                    VALUES (1, {$row['cliente_id']}, '$mensagem_escaped', 'cobranca_vencida', '$data_hora', 'enviado', 'enviado')";
            
            if (!$mysqli->query($sql)) {
                error_log("Erro ao salvar mensagem automática: " . $mysqli->error);
            }

            $mensagens_enviadas++;
            $log_detalhado[] = "✅ Mensagem enviada para {$nome} (R$ " . number_format($valor_total, 2, ',', '.') . ")";

            // Aguardar 2 segundos entre envios para evitar spam
            sleep(2);

        } catch (Exception $e) {
            $erros++;
            $log_detalhado[] = "❌ Erro ao processar {$row['cliente_nome']}: " . $e->getMessage();
        }
    }

    // Log do resumo
    $log_resumo = date('Y-m-d H:i:s') . " - Monitoramento manual executado: $clientes_processados clientes processados, $mensagens_enviadas mensagens enviadas, $erros erros\n";
    file_put_contents('../logs/monitoramento_manual.log', $log_resumo, FILE_APPEND);

    echo json_encode([
        'success' => true,
        'clientes_processados' => $clientes_processados,
        'mensagens_enviadas' => $mensagens_enviadas,
        'erros' => $erros,
        'log_detalhado' => $log_detalhado,
        'data_execucao' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Erro no monitoramento manual: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Verifica status real no Asaas
 */
function verificarStatusAsaas($cliente_id) {
    global $mysqli;
    
    // Buscar chave da API do Asaas
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    
    if (!$config || !$config['valor']) {
        return false;
    }
    
    $api_key = $config['valor'];
    
    // Buscar cobranças do cliente
    $sql = "SELECT cob.id, cob.asaas_id, cob.status, cob.valor, cob.vencimento, cob.url_fatura
            FROM cobrancas cob
            WHERE cob.cliente_id = $cliente_id
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.asaas_id IS NOT NULL";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        return false;
    }
    
    $atualizacoes = 0;
    
    while ($cobranca = $result->fetch_assoc()) {
        // Verificar status real no Asaas
        $ch = curl_init("https://www.asaas.com/api/v3/payments/{$cobranca['asaas_id']}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'access_token: ' . $api_key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $asaas_data = json_decode($response, true);
            
            if ($asaas_data && isset($asaas_data['status'])) {
                $status_real = $asaas_data['status'];
                $status_local = $cobranca['status'];
                
                // Se status divergiu, atualizar no banco
                if ($status_real !== $status_local) {
                    $status_escaped = $mysqli->real_escape_string($status_real);
                    $mysqli->query("UPDATE cobrancas SET status = '$status_escaped' WHERE id = {$cobranca['id']}");
                    $atualizacoes++;
                }
            }
        }
    }
    
    return $atualizacoes > 0;
}
?> 