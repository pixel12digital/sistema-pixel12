<?php
/**
 * Sincronização de Cobranças do Asaas
 * Versão focada apenas em cobranças, preservando dados existentes
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Função melhorada para logging
function logDetalhado($mensagem, $tipo = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$tipo] $mensagem\n";
    
    // Log para arquivo
    $log_file = __DIR__ . '/../logs/sincronizacao_melhorada.log';
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Log para console
    echo $log_entry;
}

// Função para fazer requisições à API do Asaas
function getAsaas($endpoint) {
    $url = ASAAS_API_URL . $endpoint;
    $headers = [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        logDetalhado("ERRO CURL: $error", "ERROR");
        return null;
    }
    
    if ($http_code !== 200) {
        logDetalhado("ERRO HTTP: $http_code - $response", "ERROR");
        return null;
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logDetalhado("ERRO JSON: " . json_last_error_msg(), "ERROR");
        return null;
    }
    
    return $data;
}

// Função para validar dados de cobrança
function validarDadosCobranca($cob) {
    $erros = [];
    
    if (empty($cob['id'])) {
        $erros[] = "ID da cobrança está vazio";
    }
    
    if (empty($cob['customer'])) {
        $erros[] = "ID do cliente está vazio";
    }
    
    if (!isset($cob['value']) || $cob['value'] <= 0) {
        $erros[] = "Valor inválido: " . ($cob['value'] ?? 'NULL');
    }
    
    if (empty($cob['status'])) {
        $erros[] = "Status está vazio";
    }
    
    if (empty($cob['dueDate'])) {
        $erros[] = "Data de vencimento está vazia";
    }
    
    return $erros;
}

// Função para preparar dados de cobrança
function prepararDadosCobranca($cob, $cliente_id) {
    $dados = [];
    
    $dados['asaas_payment_id'] = $cob['id'];
    $dados['cliente_id'] = $cliente_id;
    $dados['valor'] = floatval($cob['value']);
    $dados['status'] = $cob['status'];
    $dados['vencimento'] = $cob['dueDate'];
    $dados['descricao'] = $cob['description'] ?? '';
    $dados['tipo'] = $cob['billingType'] ?? '';
    $dados['tipo_pagamento'] = $cob['billingType'] ?? '';
    $dados['url_fatura'] = $cob['invoiceUrl'] ?? '';
    $dados['parcela'] = $cob['installmentNumber'] ?? '';
    $dados['assinatura_id'] = $cob['subscription'] ?? null;
    
    // Tratar data de pagamento
    if (!empty($cob['paymentDate'])) {
        $dados['data_pagamento'] = date('Y-m-d', strtotime($cob['paymentDate']));
    } else {
        $dados['data_pagamento'] = null;
    }
    
    // Tratar data de criação
    if (!empty($cob['dateCreated'])) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $cob['dateCreated'])) {
            $dados['data_criacao'] = $cob['dateCreated'] . ' 00:00:00';
        } else {
            $dados['data_criacao'] = date('Y-m-d H:i:s', strtotime($cob['dateCreated']));
        }
    } else {
        $dados['data_criacao'] = date('Y-m-d H:i:s');
    }
    
    $dados['data_atualizacao'] = date('Y-m-d H:i:s');
    
    return $dados;
}

// Função para inserir cobrança com tratamento de erro melhorado
function inserirCobranca($mysqli, $dados) {
    try {
        // Verificar conexão
        if (!$mysqli->ping()) {
            logDetalhado("Conexão MySQL perdida, reconectando...", "WARN");
            $mysqli->close();
            require __DIR__ . '/db.php';
        }
        
        $sql = "INSERT INTO cobrancas (
            asaas_payment_id, cliente_id, valor, status, vencimento, descricao, 
            tipo, tipo_pagamento, url_fatura, parcela, assinatura_id, 
            data_criacao, data_pagamento, data_atualizacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            valor = VALUES(valor),
            status = VALUES(status),
            vencimento = VALUES(vencimento),
            descricao = VALUES(descricao),
            tipo = VALUES(tipo),
            tipo_pagamento = VALUES(tipo_pagamento),
            url_fatura = VALUES(url_fatura),
            parcela = VALUES(parcela),
            assinatura_id = VALUES(assinatura_id),
            data_pagamento = VALUES(data_pagamento),
            data_atualizacao = VALUES(data_atualizacao)";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar statement: " . $mysqli->error);
        }
        
        $stmt->bind_param('sidsssssssssss',
            $dados['asaas_payment_id'],
            $dados['cliente_id'],
            $dados['valor'],
            $dados['status'],
            $dados['vencimento'],
            $dados['descricao'],
            $dados['tipo'],
            $dados['tipo_pagamento'],
            $dados['url_fatura'],
            $dados['parcela'],
            $dados['assinatura_id'],
            $dados['data_criacao'],
            $dados['data_pagamento'],
            $dados['data_atualizacao']
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Erro ao executar statement: " . $stmt->error);
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        logDetalhado("ERRO ao inserir cobrança: " . $e->getMessage(), "ERROR");
        logDetalhado("Dados da cobrança: " . json_encode($dados), "DEBUG");
        return false;
    }
}

// Função para buscar cliente
function buscarCliente($mysqli, $asaas_id) {
    $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
    if (!$stmt) {
        logDetalhado("Erro ao preparar busca de cliente: " . $mysqli->error, "ERROR");
        return null;
    }
    
    $stmt->bind_param('s', $asaas_id);
    $stmt->execute();
    $stmt->bind_result($cliente_id);
    $stmt->fetch();
    $stmt->close();
    
    return $cliente_id;
}

// INÍCIO DA SINCRONIZAÇÃO
try {
    logDetalhado("==== INICIANDO SINCRONIZAÇÃO DE COBRANÇAS DO ASAAS ====");
    
    // Sincronizar apenas cobranças
    logDetalhado("--- SINCRONIZANDO COBRANÇAS ---");
    $cobrancas = [];
    $offset = 0;
    $maxPaginas = 50; // Limite de 5000 cobranças
    $paginaAtual = 0;
    $sucessos = 0;
    $erros = 0;
    
    do {
        $resp = getAsaas("/payments?limit=100&offset=$offset");
        $paginaAtual++;
        
        if ($paginaAtual > $maxPaginas) {
            logDetalhado("ERRO: Limite de páginas de cobranças atingido.", "ERROR");
            break;
        }
        
        if ($resp === null) {
            logDetalhado("ERRO: Falha ao buscar cobranças. Parando.", "ERROR");
            exit(1);
        }
        
        if (!empty($resp['data'])) {
            logDetalhado("Encontradas " . count($resp['data']) . " cobranças na página " . ($offset/100 + 1));
            
            foreach ($resp['data'] as $cob) {
                $cobrancas[] = $cob;
                $cobranca_id = $cob['id'];
                logDetalhado("Processando cobrança: $cobranca_id");
                
                // Validar dados
                $erros_validacao = validarDadosCobranca($cob);
                if (!empty($erros_validacao)) {
                    logDetalhado("ERRO de validação na cobrança $cobranca_id:", "ERROR");
                    foreach ($erros_validacao as $erro) {
                        logDetalhado("  - $erro", "ERROR");
                    }
                    $erros++;
                    continue;
                }
                
                // Buscar cliente
                $cliente_id = buscarCliente($mysqli, $cob['customer']);
                if (!$cliente_id) {
                    logDetalhado("ERRO: Cliente não encontrado para cobrança $cobranca_id (asaas_id: {$cob['customer']})", "ERROR");
                    $erros++;
                    continue;
                }
                
                // Preparar e inserir dados
                $dados = prepararDadosCobranca($cob, $cliente_id);
                if (inserirCobranca($mysqli, $dados)) {
                    logDetalhado("Cobrança processada com sucesso: $cobranca_id");
                    $sucessos++;
                } else {
                    logDetalhado("ERRO ao processar cobrança: $cobranca_id", "ERROR");
                    $erros++;
                }
            }
        } else {
            logDetalhado("Nenhuma cobrança encontrada na página " . ($offset/100 + 1));
        }
        
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    logDetalhado("Cobranças sincronizadas: " . count($cobrancas));
    // Adiciona o total de cobranças a processar no log para uso do frontend
    logDetalhado("TOTAL_COBRANCAS_ESPERADO: " . count($cobrancas));
    logDetalhado("Sucessos: $sucessos, Erros: $erros");
    logDetalhado("--- FIM SINCRONIZAÇÃO DE COBRANÇAS ---");
    
    // Registrar data/hora da última sincronização
    logDetalhado("--- REGISTRANDO DATA/HORA DA ÚLTIMA SINCRONIZAÇÃO ---");
    $ultima_sync_path = __DIR__ . '/../logs/ultima_sincronizacao.log';
    if (@file_put_contents($ultima_sync_path, date('Y-m-d H:i:s')) === false) {
        logDetalhado("ERRO ao gravar arquivo de última sincronização: $ultima_sync_path", "ERROR");
        exit(1);
    } else {
        logDetalhado("Arquivo de última sincronização gravado com sucesso: $ultima_sync_path");
    }
    
    logDetalhado("==== SINCRONIZAÇÃO DE COBRANÇAS CONCLUÍDA COM SUCESSO ====");
    logDetalhado("Resumo: $sucessos cobranças processadas com sucesso, $erros erros");
    exit(0);
    
} catch (Throwable $e) {
    logDetalhado("ERRO FATAL: " . $e->getMessage() . " | Arquivo: " . $e->getFile() . " | Linha: " . $e->getLine(), "FATAL");
    logDetalhado("Sincronização finalizada com erro inesperado.", "FATAL");
    exit(1);
}
?> 