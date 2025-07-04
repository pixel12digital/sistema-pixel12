<?php
require_once __DIR__ . '/../config.php';
require_once 'db.php';

echo date('Y-m-d H:i:s') . " - Iniciando sincronização com Asaas...\n";

// LOG DETALHADO: Função para registrar logs detalhados em arquivo
function logDetalhado($mensagem) {
    $logFile = __DIR__ . '/../logs/sincroniza_asaas_debug.log';
    $data = date('Y-m-d H:i:s') . ' - ' . $mensagem . "\n";
    file_put_contents($logFile, $data, FILE_APPEND);
}

function getAsaas($endpoint) {
    $ch = curl_init();
    $url = ASAAS_API_URL . $endpoint;
    $header = [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY
    ];
    logDetalhado("[REQ] URL: $url");
    logDetalhado("[REQ] HEADER: " . json_encode($header));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    logDetalhado("[RESP] HTTP CODE: $httpCode");
    logDetalhado("[RESP] CURL ERROR: $curlError");
    logDetalhado("[RESP] RAW: $result");
    if ($httpCode !== 200) {
        echo "Erro HTTP $httpCode ao acessar: $url\n";
        echo "Resposta: $result\n";
        logDetalhado("[ERRO] HTTP $httpCode ao acessar: $url");
        logDetalhado("[ERRO] Resposta: $result");
        return null;
    }
    $data = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erro ao decodificar JSON: " . json_last_error_msg() . "\n";
        echo "Resposta: $result\n";
        logDetalhado("[ERRO] JSON: " . json_last_error_msg());
        logDetalhado("[ERRO] Resposta: $result");
        return null;
    }
    return $data;
}

// 1. Sincronizar clientes
echo "Buscando clientes...\n";
$clientes = [];
$offset = 0;
do {
    $resp = getAsaas("/customers?limit=100&offset=$offset");
    if ($resp === null) {
        echo "Falha ao buscar clientes. Parando.\n";
        break;
    }
    
    if (!empty($resp['data'])) {
        echo "Encontrados " . count($resp['data']) . " clientes na página " . ($offset/100 + 1) . "\n";
        foreach ($resp['data'] as $cli) {
            $clientes[] = $cli;
            // Variáveis intermediárias para bind_param
            $asaas_id = $cli['id'] ?? null;
            $nome = $cli['name'] ?? null;
            $email = $cli['email'] ?? null;
            $telefone = $cli['phone'] ?? null;
            $celular = $cli['mobilePhone'] ?? null;
            $cep = $cli['postalCode'] ?? null;
            $rua = $cli['address'] ?? null;
            $numero = $cli['addressNumber'] ?? null;
            $complemento = $cli['complement'] ?? null;
            $bairro = $cli['province'] ?? null;
            $cidade = $cli['city'] ?? null;
            $estado = $cli['state'] ?? null;
            $pais = $cli['country'] ?? null;
            $notificacao_desativada = isset($cli['notificationDisabled']) ? (int)$cli['notificationDisabled'] : null;
            $emails_adicionais = $cli['additionalEmails'] ?? null;
            $referencia_externa = $cli['externalReference'] ?? null;
            $observacoes = $cli['observations'] ?? null;
            $razao_social = $cli['company'] ?? null;
            $criado_em_asaas = isset($cli['createdAt']) ? date('Y-m-d H:i:s', strtotime($cli['createdAt'])) : null;
            $cpf_cnpj = $cli['cpfCnpj'] ?? null;
            $data_criacao = date('Y-m-d H:i:s');
            $data_atualizacao = date('Y-m-d H:i:s');
            // Upsert cliente no banco local
            $stmt = $mysqli->prepare("INSERT INTO clientes (
                asaas_id, nome, email, telefone, celular, cep, rua, numero, complemento, bairro, cidade, estado, pais, notificacao_desativada, emails_adicionais, referencia_externa, observacoes, razao_social, criado_em_asaas, cpf_cnpj, data_criacao, data_atualizacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                nome = VALUES(nome),
                email = VALUES(email),
                telefone = VALUES(telefone),
                celular = VALUES(celular),
                cep = VALUES(cep),
                rua = VALUES(rua),
                numero = VALUES(numero),
                complemento = VALUES(complemento),
                bairro = VALUES(bairro),
                cidade = VALUES(cidade),
                estado = VALUES(estado),
                pais = VALUES(pais),
                notificacao_desativada = VALUES(notificacao_desativada),
                emails_adicionais = VALUES(emails_adicionais),
                referencia_externa = VALUES(referencia_externa),
                observacoes = VALUES(observacoes),
                razao_social = VALUES(razao_social),
                criado_em_asaas = VALUES(criado_em_asaas),
                cpf_cnpj = VALUES(cpf_cnpj),
                data_atualizacao = VALUES(data_atualizacao)");
            $stmt->bind_param(
                'ssssssssssssisssssssss',
                $asaas_id,
                $nome,
                $email,
                $telefone,
                $celular,
                $cep,
                $rua,
                $numero,
                $complemento,
                $bairro,
                $cidade,
                $estado,
                $pais,
                $notificacao_desativada,
                $emails_adicionais,
                $referencia_externa,
                $observacoes,
                $razao_social,
                $criado_em_asaas,
                $cpf_cnpj,
                $data_criacao,
                $data_atualizacao
            );
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Nenhum cliente encontrado na página " . ($offset/100 + 1) . "\n";
    }
    $offset += 100;
} while (!empty($resp['data']) && count($resp['data']) === 100);

echo "Clientes sincronizados: " . count($clientes) . "\n";

// 2. Sincronizar cobranças
echo "Buscando cobranças...\n";
$cobrancas = [];
$offset = 0;
do {
    $resp = getAsaas("/payments?limit=100&offset=$offset");
    if ($resp === null) {
        echo "Falha ao buscar cobranças. Parando.\n";
        break;
    }
    
    if (!empty($resp['data'])) {
        echo "Encontradas " . count($resp['data']) . " cobranças na página " . ($offset/100 + 1) . "\n";
        foreach ($resp['data'] as $cob) {
            $cobrancas[] = $cob;
            // Buscar o id local do cliente a partir do asaas_id
            $cliente_id = null;
            $asaas_id = $cob['customer'];
            $stmt_cliente = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
            $stmt_cliente->bind_param('s', $asaas_id);
            $stmt_cliente->execute();
            $stmt_cliente->bind_result($cliente_id);
            $stmt_cliente->fetch();
            $stmt_cliente->close();
            if (!$cliente_id) continue; // Pula se não encontrar cliente
            // Upsert cobrança no banco local
            $stmt = $mysqli->prepare("REPLACE INTO cobrancas (asaas_payment_id, cliente_id, valor, status, vencimento, descricao, tipo, tipo_pagamento, url_fatura, parcela, assinatura_id, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // CORREÇÃO: Usar dateCreated (campo correto da API Asaas)
            $data_criacao_raw = $cob['dateCreated'] ?? null;
            if ($data_criacao_raw) {
                // Se dateCreated está no formato YYYY-MM-DD, adicionar hora
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_criacao_raw)) {
                    $data_criacao = $data_criacao_raw . ' 00:00:00';
                } else {
                    $data_criacao = date('Y-m-d H:i:s', strtotime($data_criacao_raw));
                }
            } else {
                // Se não tem data de criação, usar data atual
                $data_criacao = date('Y-m-d H:i:s');
            }
            
            $stmt->bind_param('sidsssssssss',
                $cob['id'],
                $cliente_id,
                $cob['value'],
                $cob['status'],
                $cob['dueDate'],
                $cob['description'],
                $cob['billingType'],
                $cob['billingType'],
                $cob['invoiceUrl'],
                $cob['installmentNumber'],
                $cob['subscription'],
                $data_criacao
            );
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Nenhuma cobrança encontrada na página " . ($offset/100 + 1) . "\n";
    }
    $offset += 100;
} while (!empty($resp['data']) && count($resp['data']) === 100);

echo "Cobranças sincronizadas: " . count($cobrancas) . "\n";

// Após sincronizar cobranças, excluir do banco local as que não existem mais no Asaas
if (!empty($cobrancas)) {
    // Monta um array com todos os IDs de cobranças do Asaas
    $ids_asaas = array_map(function($cob) { return $cob['id']; }, $cobrancas);
    $ids_asaas_str = "'" . implode("','", array_map('addslashes', $ids_asaas)) . "'";
    // Exclui do banco local todas as cobranças cujo asaas_payment_id não está mais no Asaas
    $sql_del = "DELETE FROM cobrancas WHERE asaas_payment_id NOT IN ($ids_asaas_str)";
    $mysqli->query($sql_del);
    echo "Cobranças locais excluídas (não existem mais no Asaas): " . $mysqli->affected_rows . "\n";
}

// 3. Registrar data/hora da última sincronização
file_put_contents('ultima_sincronizacao.log', date('Y-m-d H:i:s'));
echo "Sincronização concluída em " . date('Y-m-d H:i:s') . "\n"; 