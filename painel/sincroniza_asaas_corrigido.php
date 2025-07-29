<?php
/**
 * Sincronização Corrigida com Asaas
 * - Lida com timeouts do MySQL
 * - Reconecta automaticamente
 * - Preserva dados editados manualmente
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Função melhorada para logging
function logDetalhado($mensagem, $tipo = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$tipo] $mensagem\n";
    
    // Log para arquivo
    $log_file = __DIR__ . '/../logs/sincronizacao_corrigida.log';
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Log para console
    echo $log_entry;
}

// Função para reconectar ao banco se necessário
function verificarConexao($mysqli) {
    if (!$mysqli->ping()) {
        logDetalhado("Conexão perdida, reconectando...", "WARN");
        $mysqli->close();
        global $mysqli;
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_error) {
            logDetalhado("ERRO ao reconectar: " . $mysqli->connect_error, "ERROR");
            return false;
        }
        logDetalhado("Reconectado com sucesso", "INFO");
    }
    return true;
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

// Função para sincronizar cliente de forma protegida
function sincronizarClienteProtegido($mysqli, $clienteAsaas) {
    try {
        // Verificar conexão antes de cada operação
        if (!verificarConexao($mysqli)) {
            return false;
        }
        
        // Verificar se o cliente já existe
        $stmt = $mysqli->prepare("SELECT id, nome, email, telefone, celular, endereco, 
                                        telefone_editado_manual, celular_editado_manual, 
                                        email_editado_manual, nome_editado_manual, endereco_editado_manual
                                 FROM clientes WHERE asaas_id = ?");
        $stmt->bind_param('s', $clienteAsaas['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $clienteExistente = $result->fetch_assoc();
        $stmt->close();
        
        $dados = [
            'asaas_id' => $clienteAsaas['id'],
            'nome' => $clienteAsaas['name'] ?? '',
            'email' => $clienteAsaas['email'] ?? '',
            'telefone' => $clienteAsaas['phone'] ?? '',
            'celular' => $clienteAsaas['mobilePhone'] ?? '',
            'endereco' => $clienteAsaas['address'] ?? '',
            'cpf_cnpj' => $clienteAsaas['cpfCnpj'] ?? '',
            'data_criacao' => date('Y-m-d H:i:s'),
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
        
        if ($clienteExistente) {
            // Cliente existe - atualizar apenas campos não editados manualmente
            $updates = [];
            $params = [];
            $types = '';
            
            // Só atualizar campos que não foram editados manualmente
            if (!$clienteExistente['nome_editado_manual']) {
                $updates[] = "nome = ?";
                $params[] = $dados['nome'];
                $types .= 's';
            }
            
            if (!$clienteExistente['email_editado_manual']) {
                $updates[] = "email = ?";
                $params[] = $dados['email'];
                $types .= 's';
            }
            
            if (!$clienteExistente['telefone_editado_manual']) {
                $updates[] = "telefone = ?";
                $params[] = $dados['telefone'];
                $types .= 's';
            }
            
            if (!$clienteExistente['celular_editado_manual']) {
                $updates[] = "celular = ?";
                $params[] = $dados['celular'];
                $types .= 's';
            }
            
            if (!$clienteExistente['endereco_editado_manual']) {
                $updates[] = "endereco = ?";
                $params[] = $dados['endereco'];
                $types .= 's';
            }
            
            // Sempre atualizar CPF/CNPJ e data de atualização
            $updates[] = "cpf_cnpj = ?";
            $params[] = $dados['cpf_cnpj'];
            $types .= 's';
            
            $updates[] = "data_atualizacao = ?";
            $params[] = $dados['data_atualizacao'];
            $types .= 's';
            
            if (!empty($updates)) {
                $sql = "UPDATE clientes SET " . implode(', ', $updates) . " WHERE id = ?";
                $params[] = $clienteExistente['id'];
                $types .= 'i';
                
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
                
                logDetalhado("Cliente atualizado (protegido): " . $clienteAsaas['id']);
                return true;
            } else {
                logDetalhado("Cliente preservado (todos os campos editados manualmente): " . $clienteAsaas['id']);
                return 'preservado';
            }
        } else {
            // Cliente não existe - inserir novo
            $sql = "INSERT INTO clientes (asaas_id, nome, email, telefone, celular, endereco, cpf_cnpj, data_criacao, data_atualizacao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssssssss', 
                $dados['asaas_id'], $dados['nome'], $dados['email'], 
                $dados['telefone'], $dados['celular'], $dados['endereco'], 
                $dados['cpf_cnpj'], $dados['data_criacao'], $dados['data_atualizacao']
            );
            $stmt->execute();
            $stmt->close();
            
            logDetalhado("Cliente inserido: " . $clienteAsaas['id']);
            return true;
        }
    } catch (Exception $e) {
        logDetalhado("ERRO ao sincronizar cliente " . $clienteAsaas['id'] . ": " . $e->getMessage(), "ERROR");
        return false;
    }
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
        $erros[] = "Valor inválido: " . ($cob['value'] ?? 'nulo');
    }
    
    if (empty($cob['dueDate'])) {
        $erros[] = "Data de vencimento está vazia";
    }
    
    return $erros;
}

// Função para preparar dados de cobrança
function prepararDadosCobranca($cob, $cliente_id) {
    return [
        'asaas_payment_id' => $cob['id'],
        'cliente_id' => $cliente_id,
        'valor' => $cob['value'],
        'status' => $cob['status'],
        'vencimento' => $cob['dueDate'],
        'data_pagamento' => $cob['paymentDate'] ?? null,
        'descricao' => $cob['description'] ?? '',
        'tipo' => $cob['billingType'] ?? 'BOLETO',
        'tipo_pagamento' => $cob['paymentType'] ?? null,
        'url_fatura' => $cob['invoiceUrl'] ?? null,
        'parcela' => $cob['installment'] ?? null,
        'assinatura_id' => $cob['subscription'] ?? null,
        'data_criacao' => date('Y-m-d H:i:s'),
        'data_atualizacao' => date('Y-m-d H:i:s')
    ];
}

// Função para inserir/atualizar cobrança
function inserirCobranca($mysqli, $dados) {
    try {
        // Verificar conexão
        if (!verificarConexao($mysqli)) {
            return false;
        }
        
        // Verificar se a cobrança já existe
        $stmt = $mysqli->prepare("SELECT id FROM cobrancas WHERE asaas_payment_id = ?");
        $stmt->bind_param('s', $dados['asaas_payment_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cobrancaExistente = $result->fetch_assoc();
        $stmt->close();
        
        if ($cobrancaExistente) {
            // Atualizar cobrança existente
            $sql = "UPDATE cobrancas SET 
                    cliente_id = ?, valor = ?, status = ?, vencimento = ?, 
                    data_pagamento = ?, descricao = ?, tipo = ?, tipo_pagamento = ?, 
                    url_fatura = ?, parcela = ?, assinatura_id = ?, data_atualizacao = ?
                    WHERE asaas_payment_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('idsssssssssss', 
                $dados['cliente_id'], $dados['valor'], $dados['status'], $dados['vencimento'],
                $dados['data_pagamento'], $dados['descricao'], $dados['tipo'], $dados['tipo_pagamento'],
                $dados['url_fatura'], $dados['parcela'], $dados['assinatura_id'], $dados['data_atualizacao'],
                $dados['asaas_payment_id']
            );
            $stmt->execute();
            $stmt->close();
        } else {
            // Inserir nova cobrança
            $sql = "INSERT INTO cobrancas (asaas_payment_id, cliente_id, valor, status, vencimento, 
                    data_pagamento, descricao, tipo, tipo_pagamento, url_fatura, parcela, assinatura_id, 
                    data_criacao, data_atualizacao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sidsissssssss', 
                $dados['asaas_payment_id'], $dados['cliente_id'], $dados['valor'], $dados['status'], $dados['vencimento'],
                $dados['data_pagamento'], $dados['descricao'], $dados['tipo'], $dados['tipo_pagamento'],
                $dados['url_fatura'], $dados['parcela'], $dados['assinatura_id'], $dados['data_criacao'], $dados['data_atualizacao']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        return true;
    } catch (Exception $e) {
        logDetalhado("ERRO ao inserir/atualizar cobrança " . $dados['asaas_payment_id'] . ": " . $e->getMessage(), "ERROR");
        return false;
    }
}

// INÍCIO DA SINCRONIZAÇÃO CORRIGIDA
try {
    logDetalhado("==== INICIANDO SINCRONIZAÇÃO CORRIGIDA COM ASAAS ====");
    
    // Configurar timeout do MySQL para evitar "MySQL server has gone away"
    $mysqli->query("SET SESSION wait_timeout=28800");
    $mysqli->query("SET SESSION interactive_timeout=28800");
    
    // 1. Sincronizar clientes (protegido)
    logDetalhado("--- ETAPA 1: SINCRONIZANDO CLIENTES (PROTEGIDO) ---");
    $clientes = [];
    $offset = 0;
    $paginaAtual = 0;
    $maxPaginas = 50;
    $clientesSucessos = 0;
    $clientesErros = 0;
    $clientesPreservados = 0;
    
    do {
        $resp = getAsaas("/customers?limit=100&offset=$offset");
        $paginaAtual++;
        
        if ($paginaAtual > $maxPaginas) {
            logDetalhado("ERRO: Limite de páginas de clientes atingido.", "ERROR");
            break;
        }
        
        if ($resp === null) {
            logDetalhado("ERRO: Falha ao buscar clientes. Parando.", "ERROR");
            exit(1);
        }
        
        if (!empty($resp['data'])) {
            logDetalhado("Encontrados " . count($resp['data']) . " clientes na página " . ($offset/100 + 1));
            
            foreach ($resp['data'] as $cli) {
                $clientes[] = $cli;
                $resultado = sincronizarClienteProtegido($mysqli, $cli);
                
                if ($resultado === true) {
                    $clientesSucessos++;
                } elseif ($resultado === 'preservado') {
                    $clientesPreservados++;
                } else {
                    $clientesErros++;
                }
            }
        } else {
            logDetalhado("Nenhum cliente encontrado na página " . ($offset/100 + 1));
        }
        
        $offset += 100;
        
        // Pausa entre páginas para evitar sobrecarga
        usleep(100000); // 0.1 segundo
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    logDetalhado("Clientes sincronizados: " . count($clientes));
    logDetalhado("Sucessos: $clientesSucessos, Preservados: $clientesPreservados, Erros: $clientesErros");
    
    // 2. Contar total de cobranças para progresso
    logDetalhado("--- CONTANDO TOTAL DE COBRANÇAS ---");
    $totalCobrancas = 0;
    $offset = 0;
    $paginaAtual = 0;
    
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
            $totalCobrancas += count($resp['data']);
            logDetalhado("Contadas " . count($resp['data']) . " cobranças na página " . ($offset/100 + 1) . " (Total acumulado: $totalCobrancas)");
        } else {
            logDetalhado("Nenhuma cobrança encontrada na página " . ($offset/100 + 1));
        }
        
        $offset += 100;
        usleep(100000); // 0.1 segundo
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    logDetalhado("TOTAL_COBRANCAS_ESPERADO: $totalCobrancas");
    logDetalhado("--- FIM CONTAGEM DE COBRANÇAS ---");
    
    // 3. Sincronizar cobranças (espelho completo do Asaas)
    logDetalhado("--- ETAPA 2: SINCRONIZANDO COBRANÇAS (ESPELHO COMPLETO) ---");
    $cobrancas = [];
    $offset = 0;
    $paginaAtual = 0;
    $cobrancasSucessos = 0;
    $cobrancasErros = 0;
    $processados = 0;
    
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
                $processados++;
                
                // Log de progresso a cada 10 cobranças
                if ($processados % 10 === 0) {
                    $progresso = round(($processados / $totalCobrancas) * 100, 1);
                    logDetalhado("PROGRESSO: $processados/$totalCobrancas ($progresso%) - Processando cobrança: $cobranca_id");
                } else {
                    logDetalhado("Processando cobrança: $cobranca_id");
                }
                
                // Validar dados
                $erros_validacao = validarDadosCobranca($cob);
                if (!empty($erros_validacao)) {
                    logDetalhado("ERRO de validação na cobrança $cobranca_id:", "ERROR");
                    foreach ($erros_validacao as $erro) {
                        logDetalhado("  - $erro", "ERROR");
                    }
                    $cobrancasErros++;
                    continue;
                }
                
                // Buscar cliente
                $stmt = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
                $stmt->bind_param('s', $cob['customer']);
                $stmt->execute();
                $result = $stmt->get_result();
                $cliente = $result->fetch_assoc();
                $stmt->close();
                
                if (!$cliente) {
                    logDetalhado("ERRO: Cliente não encontrado para cobrança $cobranca_id (customer: " . $cob['customer'] . ")", "ERROR");
                    $cobrancasErros++;
                    continue;
                }
                
                // Preparar e inserir cobrança
                $dados = prepararDadosCobranca($cob, $cliente['id']);
                if (inserirCobranca($mysqli, $dados)) {
                    $cobrancasSucessos++;
                } else {
                    $cobrancasErros++;
                }
            }
        } else {
            logDetalhado("Nenhuma cobrança encontrada na página " . ($offset/100 + 1));
        }
        
        $offset += 100;
        usleep(100000); // 0.1 segundo
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    logDetalhado("Cobranças sincronizadas: " . count($cobrancas));
    logDetalhado("Sucessos: $cobrancasSucessos, Erros: $cobrancasErros");
    
    // 4. Resumo final
    logDetalhado("==== RESUMO DA SINCRONIZAÇÃO CORRIGIDA ====");
    logDetalhado("Clientes processados: " . count($clientes));
    logDetalhado("Clientes sincronizados: $clientesSucessos");
    logDetalhado("Clientes preservados: $clientesPreservados");
    logDetalhado("Clientes com erro: $clientesErros");
    logDetalhado("Cobranças processadas: " . count($cobrancas));
    logDetalhado("Cobranças sincronizadas: $cobrancasSucessos");
    logDetalhado("Cobranças com erro: $cobrancasErros");
    logDetalhado("==== SINCRONIZAÇÃO CORRIGIDA CONCLUÍDA COM SUCESSO ====");
    
} catch (Exception $e) {
    logDetalhado("ERRO FATAL: " . $e->getMessage() . " | Arquivo: " . $e->getFile() . " | Linha: " . $e->getLine(), "FATAL");
    logDetalhado("Sincronização finalizada com erro inesperado.");
    exit(1);
}
?> 