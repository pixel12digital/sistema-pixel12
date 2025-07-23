<?php
// Configurar codificação UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Logar erros fatais no arquivo de log
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        file_put_contents(__DIR__ . '/../logs/sincroniza_asaas_debug.log', date('Y-m-d H:i:s') . ' - FATAL ERROR: ' . print_r($error, true) . "\n", FILE_APPEND);
    }
});

require_once __DIR__ . '/../config.php';
require_once 'db.php';

if (!isset($mysqli) || !$mysqli || $mysqli->connect_errno) {
    die('Erro ao conectar ao banco de dados: ' . (isset($mysqli) && $mysqli ? $mysqli->connect_error : 'variável $mysqli não definida'));
}

// Limpar o log antes de iniciar nova sincronização
file_put_contents(__DIR__ . '/../logs/sincroniza_asaas_debug.log', '');

function logDetalhado($mensagem) {
    $logFile = __DIR__ . '/../logs/sincroniza_asaas_debug.log';
    $data = date('Y-m-d H:i:s') . ' - ' . $mensagem . "\n";
    file_put_contents($logFile, $data, FILE_APPEND);
}

function printAndLog($mensagem) {
    echo $mensagem . "<br>\n";
    logDetalhado($mensagem);
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Asaas-API-Test/1.0');
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    logDetalhado("[RESP] HTTP CODE: $httpCode");
    logDetalhado("[RESP] CURL ERROR: $curlError");
    logDetalhado("[RESP] RAW: $result");
    if ($httpCode !== 200) {
        logDetalhado("[ERRO] HTTP $httpCode ao acessar: $url");
        logDetalhado("[ERRO] Resposta: $result");
        return null;
    }
    $data = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logDetalhado("[ERRO] JSON: " . json_last_error_msg());
        logDetalhado("[ERRO] Resposta: $result");
        return null;
    }
    return $data;
}

try {
    printAndLog('==== INÍCIO DA SINCRONIZAÇÃO ====');
    printAndLog('Iniciando sincronização com Asaas...');

    // 1. Sincronizar clientes
    printAndLog('--- ETAPA 1: Buscando clientes ---');
    $clientes = [];
    $offset = 0;
    do {
        $resp = getAsaas("/customers?limit=100&offset=$offset");
        if ($resp === null) {
            printAndLog('ERRO: Falha ao buscar clientes. Parando.');
            printAndLog('Sincronização finalizada com erro.');
            exit(1);
        }
        if (!empty($resp['data'])) {
            printAndLog('Encontrados ' . count($resp['data']) . ' clientes na página ' . ($offset/100 + 1));
            foreach ($resp['data'] as $cli) {
                $clientes[] = $cli;
                // Variáveis intermediárias para bind_param
                $asaas_id = $cli['id'] ?? null;
                $nome = $cli['name'] ?? 'Desconhecido';
                printAndLog('Processando cliente: ' . $asaas_id);
                if (!$asaas_id) {
                    printAndLog('ERRO: Cliente sem asaas_id, ignorado: ' . json_encode($cli));
                    continue;
                }
                $email = $cli['email'] ?? '';
                $telefone = $cli['phone'] ?? '';
                $celular = $cli['mobilePhone'] ?? '';
                $cep = $cli['postalCode'] ?? '';
                $rua = $cli['address'] ?? '';
                $numero = $cli['addressNumber'] ?? '';
                $complemento = $cli['complement'] ?? '';
                $bairro = $cli['province'] ?? '';
                $cidade = $cli['city'] ?? '';
                $estado = $cli['state'] ?? '';
                $pais = $cli['country'] ?? '';
                $notificacao_desativada = isset($cli['notificationDisabled']) ? (int)$cli['notificationDisabled'] : 0;
                $emails_adicionais = $cli['additionalEmails'] ?? '';
                $referencia_externa = $cli['externalReference'] ?? '';
                $observacoes = $cli['observations'] ?? '';
                $razao_social = $cli['company'] ?? '';
                $criado_em_asaas = isset($cli['createdAt']) ? date('Y-m-d H:i:s', strtotime($cli['createdAt'])) : null;
                $cpf_cnpj = $cli['cpfCnpj'] ?? '';
                $data_criacao = date('Y-m-d H:i:s');
                $data_atualizacao = date('Y-m-d H:i:s');
                try {
                    printAndLog('Preparando statement para cliente: ' . $asaas_id);
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
                    if (!$stmt) {
                        printAndLog('ERRO ao preparar statement para cliente: ' . $mysqli->error . ' | Cliente: ' . json_encode($cli));
                        continue;
                    }
                    printAndLog('Bindando parâmetros para cliente: ' . $asaas_id);
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
                    printAndLog('Executando statement para cliente: ' . $asaas_id);
                    $result = $stmt->execute();
                    printAndLog('Resultado do execute: ' . var_export($result, true) . ' | MySQL error: ' . $stmt->error);
                    if (!$result) {
                        printAndLog('ERRO SQL ao inserir cliente: ' . $stmt->error . ' | Cliente: ' . json_encode($cli));
                    }
                    $stmt->close();
                } catch (Throwable $e) {
                    printAndLog('ERRO FATAL ao processar cliente: ' . $e->getMessage() . ' | Cliente: ' . json_encode($cli));
                }
                printAndLog('Cliente processado: ' . $asaas_id);
            }
        } else {
            printAndLog('Nenhum cliente encontrado na página ' . ($offset/100 + 1));
        }
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    printAndLog('Clientes sincronizados: ' . count($clientes));
    printAndLog('--- FIM ETAPA 1 ---');

    // 2. Sincronizar cobranças
    printAndLog('--- ETAPA 2: Buscando cobranças ---');
    $cobrancas = [];
    $offset = 0;
    $maxPaginas = 30; // Limite de 3000 cobranças (30 páginas)
    $paginaAtual = 0;
    do {
        $resp = getAsaas("/payments?limit=100&offset=$offset");
        $paginaAtual++;
        if ($paginaAtual > $maxPaginas) {
            printAndLog('ERRO: Limite de páginas de cobranças atingido. Possível loop infinito.');
            break;
        }
        if ($resp === null) {
            printAndLog('ERRO: Falha ao buscar cobranças. Parando.');
            printAndLog('Sincronização finalizada com erro.');
            exit(1);
        }
        if (!empty($resp['data'])) {
            printAndLog('Encontradas ' . count($resp['data']) . ' cobranças na página ' . ($offset/100 + 1));
            foreach ($resp['data'] as $cob) {
                // Reconectar ao MySQL se a conexão foi perdida
                if (!$mysqli->ping()) {
                    $mysqli->close();
                    require __DIR__ . '/db.php';
                    printAndLog('Reconectado ao MySQL automaticamente.');
                }
                $cobrancas[] = $cob;
                $cobranca_id = $cob['id'] ?? null;
                printAndLog('Processando cobrança: ' . $cobranca_id);
                // Buscar o id local do cliente a partir do asaas_id
                $cliente_id = null;
                $asaas_id = $cob['customer'];
                $stmt_cliente = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
                $stmt_cliente->bind_param('s', $asaas_id);
                $stmt_cliente->execute();
                $stmt_cliente->bind_result($cliente_id);
                $stmt_cliente->fetch();
                $stmt_cliente->close();
                if (!$cliente_id) {
                    // Buscar cliente na API do Asaas
                    $cli = getAsaas("/customers/" . $asaas_id);
                    if ($cli && isset($cli['id'])) {
                        // Inserir cliente no banco local (evita duplicidade pelo asaas_id UNIQUE)
                        $asaas_id_cli = $cli['id'] ?? null;
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
                        $stmt_ins = $mysqli->prepare("INSERT INTO clientes (
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
                        $stmt_ins->bind_param(
                            'ssssssssssssisssssssss',
                            $asaas_id_cli,
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
                        $stmt_ins->execute();
                        $stmt_ins->close();
                        // Buscar novamente o id do cliente
                        $stmt_cliente = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
                        $stmt_cliente->bind_param('s', $asaas_id);
                        $stmt_cliente->execute();
                        $stmt_cliente->bind_result($cliente_id);
                        $stmt_cliente->fetch();
                        $stmt_cliente->close();
                        printAndLog("Cliente $asaas_id importado automaticamente para cobrança {$cob['id']}");
                    }
                }
                if (!$cliente_id) {
                    printAndLog("ERRO: cobrança {$cob['id']} ignorada, cliente não encontrado nem após tentativa de importação (asaas_id: $asaas_id)");
                    continue;
                }
                // Upsert cobrança no banco local
                $data_pagamento = isset($cob['paymentDate']) && $cob['paymentDate'] ? date('Y-m-d', strtotime($cob['paymentDate'])) : null;
                $data_criacao_raw = $cob['dateCreated'] ?? null;
                if ($data_criacao_raw) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_criacao_raw)) {
                        $data_criacao = $data_criacao_raw . ' 00:00:00';
                    } else {
                        $data_criacao = date('Y-m-d H:i:s', strtotime($data_criacao_raw));
                    }
                } else {
                    $data_criacao = date('Y-m-d H:i:s');
                }
                try {
                    $stmt = $mysqli->prepare("REPLACE INTO cobrancas (asaas_payment_id, cliente_id, valor, status, vencimento, descricao, tipo, tipo_pagamento, url_fatura, parcela, assinatura_id, data_criacao, data_pagamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('sidssssssssss',
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
                        $data_criacao,
                        $data_pagamento
                    );
                    if (!$stmt->execute()) {
                        printAndLog('ERRO SQL ao inserir cobrança: ' . $stmt->error . ' | Cobrança: ' . json_encode($cob));
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    printAndLog('ERRO SQL ao inserir cobrança (Exception): ' . $e->getMessage() . ' | Cobrança: ' . json_encode($cob));
                }
                printAndLog('Cobrança processada: ' . $cobranca_id);
            }
        } else {
            printAndLog('Nenhuma cobrança encontrada na página ' . ($offset/100 + 1));
        }
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    printAndLog('Cobranças sincronizadas: ' . count($cobrancas));
    printAndLog('--- FIM ETAPA 2 ---');

    // Após sincronizar cobranças, excluir do banco local as que não existem mais no Asaas
    printAndLog('--- ETAPA 3: Excluindo cobranças locais que não existem mais no Asaas ---');
    if (!empty($cobrancas)) {
        try {
            $ids_asaas = array_map(function($cob) { return $cob['id']; }, $cobrancas);
            $ids_asaas_str = "'" . implode("','", array_map('addslashes', $ids_asaas)) . "'";
            printAndLog('Total de IDs de cobranças do Asaas: ' . count($ids_asaas));
            printAndLog('Primeiros 5 IDs: ' . implode(', ', array_slice($ids_asaas, 0, 5)));
            printAndLog('Últimos 5 IDs: ' . implode(', ', array_slice($ids_asaas, -5)));
            $sql_del = "DELETE FROM cobrancas WHERE asaas_payment_id NOT IN ($ids_asaas_str)";
            printAndLog('Comando SQL de exclusão: ' . $sql_del);
            if (!$mysqli->query($sql_del)) {
                printAndLog('ERRO SQL ao excluir cobranças: ' . $mysqli->error);
                printAndLog('Sincronização finalizada com erro.');
                exit(1);
            }
            printAndLog('Cobranças locais excluídas (não existem mais no Asaas): ' . $mysqli->affected_rows);
        } catch (Exception $e) {
            printAndLog('ERRO ao excluir cobranças locais: ' . $e->getMessage());
            printAndLog('Sincronização finalizada com erro.');
            exit(1);
        }
    }
    printAndLog('--- FIM ETAPA 3 ---');

    // 3. Registrar data/hora da última sincronização
    printAndLog('--- ETAPA 4: Registrando data/hora da última sincronização ---');
    $ultima_sync_path = __DIR__ . '/../logs/ultima_sincronizacao.log';
    if (@file_put_contents($ultima_sync_path, date('Y-m-d H:i:s')) === false) {
        printAndLog('ERRO ao gravar arquivo de última sincronização: ' . $ultima_sync_path);
        printAndLog('Sincronização finalizada com erro.');
        exit(1);
    } else {
        printAndLog('Arquivo de última sincronização gravado com sucesso: ' . $ultima_sync_path);
    }
    printAndLog('--- FIM ETAPA 4 ---');
    printAndLog('==== FIM DA SINCRONIZAÇÃO ====');
    printAndLog('Sincronização concluída com sucesso!');
    exit(0);
} catch (Throwable $e) {
    printAndLog('ERRO FATAL: ' . $e->getMessage() . ' | Arquivo: ' . $e->getFile() . ' | Linha: ' . $e->getLine());
    printAndLog('Sincronização finalizada com erro inesperado.');
    exit(1);
} 