<?php
/**
 * Script de Monitoramento Automático de Clientes
 * Executar via cron: 0,30 * * * * php /caminho/para/painel/cron/monitoramento_automatico.php
 */

require_once __DIR__ . '/../config.php';
require_once '../db.php';

// Função para sincronizar status das faturas do cliente com o Asaas
function sincronizarStatusAsaas($cliente_id, $mysqli) {
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    if (!$config || !$config['valor']) return false;
    $api_key = $config['valor'];
    $sql = "SELECT cob.id, cob.asaas_id, cob.status, cob.valor, cob.vencimento, cob.url_fatura FROM cobrancas cob WHERE cob.cliente_id = $cliente_id AND cob.status IN ('PENDING', 'OVERDUE') AND cob.asaas_id IS NOT NULL";
    $result = $mysqli->query($sql);
    if (!$result) return false;
    $atualizacoes = 0;
    while ($cobranca = $result->fetch_assoc()) {
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

// Log do início da execução
$log_data = date('Y-m-d H:i:s') . " - Iniciando monitoramento automático\n";
file_put_contents('../logs/monitoramento_automatico.log', $log_data, FILE_APPEND);

try {
    // Buscar clientes monitorados com cobranças vencidas ou vencendo hoje
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
                        cob.vencimento, '|',
                        cob.status
                    ) SEPARATOR '||'
                ) as faturas_info
            FROM clientes c
            JOIN clientes_monitoramento cm ON c.id = cm.cliente_id
            JOIN cobrancas cob ON c.id = cob.cliente_id
            WHERE cm.monitorado = 1
            AND cob.status IN ('PENDING', 'OVERDUE')
            AND cob.vencimento <= CURDATE()
            AND c.celular IS NOT NULL
            AND c.celular != ''
            GROUP BY c.id, c.nome, c.celular, c.contact_name
            ORDER BY cob.vencimento ASC";
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao buscar cobranças: " . $mysqli->error);
    }

    $clientes_processados = 0;
    $mensagens_enviadas = 0;
    $erros = 0;

    while ($row = $result->fetch_assoc()) {
        $clientes_processados++;
        try {
            // Sincronizar status com Asaas antes de qualquer envio
            sincronizarStatusAsaas($row['cliente_id'], $mysqli);

            // Buscar novamente as faturas após sincronização
            $sql_faturas = "SELECT id, valor, vencimento, url_fatura, status FROM cobrancas WHERE cliente_id = {$row['cliente_id']} AND status IN ('PENDING', 'OVERDUE') AND vencimento <= CURDATE() ORDER BY vencimento ASC";
            $res_faturas = $mysqli->query($sql_faturas);
            $faturas = [];
            while ($f = $res_faturas->fetch_assoc()) {
                $faturas[] = $f;
            }
            if (empty($faturas)) continue;

            // Verificar se já foi enviada mensagem hoje para este cliente
            $hoje = date('Y-m-d');
            $sql_check = "SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE cliente_id = {$row['cliente_id']} AND tipo = 'cobranca_vencida' AND DATE(data_hora) = '$hoje'";
            $check_result = $mysqli->query($sql_check);
            $check_data = $check_result->fetch_assoc();
            if ($check_data['total'] > 0) continue;

            // Separar faturas vencendo hoje e vencidas
            $faturas_vencendo_hoje = [];
            $faturas_vencidas = [];
            $hoje_data = date('Y-m-d');
            foreach ($faturas as $fatura) {
                if ($fatura['vencimento'] == $hoje_data) {
                    $faturas_vencendo_hoje[] = $fatura;
                } else if ($fatura['vencimento'] < $hoje_data) {
                    $faturas_vencidas[] = $fatura;
                }
            }

            // Lógica de espaçamento para vencidas antigas (a cada 3 dias)
            $enviar_vencidas = [];
            foreach ($faturas_vencidas as $fatura) {
                $dias_vencida = (strtotime($hoje_data) - strtotime($fatura['vencimento'])) / 86400;
                if ($dias_vencida <= 3) {
                    $enviar_vencidas[] = $fatura; // Envia diariamente até 3 dias
                } else if ($dias_vencida > 3 && $dias_vencida % 3 == 0) {
                    $enviar_vencidas[] = $fatura; // Envia a cada 3 dias
                }
            }

            // Se houver fatura vencendo hoje, priorizar envio no primeiro horário
            $enviar = false;
            if (!empty($faturas_vencendo_hoje)) {
                $enviar = true;
            } else if (!empty($enviar_vencidas)) {
                $enviar = true;
            }
            if (!$enviar) continue;

            // Agrupar todas as faturas em aberto (vencendo hoje + vencidas)
            $todas_faturas = array_merge($faturas_vencendo_hoje, $enviar_vencidas);
            if (empty($todas_faturas)) continue;

            // Montar mensagem
            $nome = $row['contact_name'] ?: $row['cliente_nome'];
            $mensagem = "Olá {$nome}! \n\n";
            $mensagem .= "⚠️ Você possui faturas em aberto:\n\n";
            foreach ($todas_faturas as $fatura) {
                $valor = number_format($fatura['valor'], 2, ',', '.');
                $venc = date('d/m/Y', strtotime($fatura['vencimento']));
                $status = $fatura['vencimento'] == $hoje_data ? 'Vence hoje' : 'Venceu em ' . $venc;
                $mensagem .= "• Fatura #{$fatura['id']} - R$ $valor - $status\n";
            }
            $mensagem .= "\n🔗 Link para pagamento: {$todas_faturas[0]['url_fatura']}\n\n";
            $mensagem .= "Para consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\n";
            $mensagem .= "Atenciosamente,\nEquipe Financeira Pixel12 Digital";

            // Enviar mensagem via VPS
            $numero_limpo = preg_replace('/\D/', '', $row['cliente_celular']);
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
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($error) throw new Exception("Erro de conexão: " . $error);
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
            $sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status) VALUES (1, {$row['cliente_id']}, '$mensagem_escaped', 'cobranca_vencida', '$data_hora', 'enviado', 'enviado')";
            if (!$mysqli->query($sql)) {
                error_log("Erro ao salvar mensagem automática: " . $mysqli->error);
            }
            $mensagens_enviadas++;
            $nome = $row['contact_name'] ?: $row['cliente_nome'];
            $log_envio = date('Y-m-d H:i:s') . " - Mensagem de cobrança enviada para {$nome} (ID: {$row['cliente_id']})\n";
            file_put_contents('../logs/monitoramento_automatico.log', $log_envio, FILE_APPEND);
            sleep(2);
        } catch (Exception $e) {
            $erros++;
            $nome = $row['contact_name'] ?: $row['cliente_nome'];
            $log_erro = date('Y-m-d H:i:s') . " - Erro ao processar cliente {$nome}: " . $e->getMessage() . "\n";
            file_put_contents('../logs/monitoramento_automatico.log', $log_erro, FILE_APPEND);
        }
    }
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