<?php
/**
 * Script de Monitoramento Automático de Clientes
 * Executar via cron: 0,30 * * * * php /caminho/para/painel/cron/monitoramento_automatico.php
 */

require_once '../config.php';
require_once '../db.php';

// Log do início da execução
$log_data = date('Y-m-d H:i:s') . " - Iniciando monitoramento automático\n";
file_put_contents('../logs/monitoramento_automatico.log', $log_data, FILE_APPEND);

try {
    // Buscar clientes monitorados com cobranças vencidas
    $sql = "SELECT DISTINCT 
                c.id as cliente_id,
                c.nome as cliente_nome,
                c.celular as cliente_celular,
                GROUP_CONCAT(
                    CONCAT(
                        cob.id, '|',
                        cob.valor, '|',
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y'), '|',
                        cob.url_fatura
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
            GROUP BY c.id, c.nome, c.celular
            ORDER BY cob.vencimento ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar cobranças vencidas: " . $mysqli->error);
    }

    $clientes_processados = 0;
    $mensagens_enviadas = 0;
    $erros = 0;

    while ($row = $result->fetch_assoc()) {
        $clientes_processados++;
        
        try {
            // Processar faturas do cliente
            $faturas = [];
            $faturas_info = explode('||', $row['faturas_info']);
            
            foreach ($faturas_info as $fatura_info) {
                $dados = explode('|', $fatura_info);
                if (count($dados) >= 4) {
                    $faturas[] = [
                        'id' => $dados[0],
                        'valor' => $dados[1],
                        'vencimento_formatado' => $dados[2],
                        'url_fatura' => $dados[3]
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
            
            if ($check_data['total'] > 0) {
                // Já foi enviada mensagem hoje, pular
                continue;
            }

            // Calcular valor total vencido
            $valor_total = array_sum(array_column($faturas, 'valor'));
            
            // Usar primeira fatura como link principal
            $link_pagamento = $faturas[0]['url_fatura'] ?? '';

            // Montar mensagem
            $mensagem = "Olá {$row['cliente_nome']}! \n\n";
            $mensagem .= "⚠️ Você possui faturas em aberto:\n\n";
            
            foreach ($faturas as $fatura) {
                $valor = number_format($fatura['valor'], 2, ',', '.');
                $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor - Venceu em {$fatura['vencimento_formatado']}\n";
            }
            
            $mensagem .= "\n🔗 Link para pagamento: $link_pagamento\n\n";
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
            
            // Log do envio
            $log_envio = date('Y-m-d H:i:s') . " - Mensagem de cobrança vencida enviada para {$row['cliente_nome']} (ID: {$row['cliente_id']})\n";
            file_put_contents('../logs/monitoramento_automatico.log', $log_envio, FILE_APPEND);

            // Aguardar 2 segundos entre envios para evitar spam
            sleep(2);

        } catch (Exception $e) {
            $erros++;
            $log_erro = date('Y-m-d H:i:s') . " - Erro ao processar cliente {$row['cliente_nome']}: " . $e->getMessage() . "\n";
            file_put_contents('../logs/monitoramento_automatico.log', $log_erro, FILE_APPEND);
        }
    }

    // Log do resumo
    $log_resumo = date('Y-m-d H:i:s') . " - Monitoramento concluído: $clientes_processados clientes processados, $mensagens_enviadas mensagens enviadas, $erros erros\n";
    file_put_contents('../logs/monitoramento_automatico.log', $log_resumo, FILE_APPEND);

    echo json_encode([
        'success' => true,
        'clientes_processados' => $clientes_processados,
        'mensagens_enviadas' => $mensagens_enviadas,
        'erros' => $erros
    ]);

} catch (Exception $e) {
    $log_erro = date('Y-m-d H:i:s') . " - Erro geral no monitoramento: " . $e->getMessage() . "\n";
    file_put_contents('../logs/monitoramento_automatico.log', $log_erro, FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 