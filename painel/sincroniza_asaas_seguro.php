<?php
/**
 * Sincronização Segura com Asaas
 * Preserva dados editados manualmente e não sobrescreve campos recentemente modificados
 */

require_once __DIR__ . '/../config.php';
require_once 'db.php';

// Função para logging detalhado
function printAndLog($mensagem) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $mensagem\n";
    
    // Log para arquivo
    $log_file = __DIR__ . '/../logs/sincronizacao_segura.log';
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Log para console
    echo $log_entry;
}

// Função para fazer requisições à API do Asaas
function getAsaas($endpoint) {
    $url = ASAAS_API_URL . $endpoint;
    $headers = [
        'Content-Type: application/json',
        'access-token: ' . ASAAS_API_KEY
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
        printAndLog("ERRO CURL: $error");
        return null;
    }
    
    if ($http_code !== 200) {
        printAndLog("ERRO HTTP: $http_code - $response");
        return null;
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        printAndLog("ERRO JSON: " . json_last_error_msg());
        return null;
    }
    
    return $data;
}

// Função para sincronizar cliente de forma segura
function sincronizarClienteSeguro($mysqli, $cli) {
    try {
        $asaas_id = $cli['id'] ?? null;
        if (!$asaas_id) {
            printAndLog("ERRO: Cliente sem asaas_id, ignorado");
            return false;
        }
        
        // Buscar cliente atual no banco local
        $stmt = $mysqli->prepare("SELECT * FROM clientes WHERE asaas_id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Erro ao preparar busca de cliente: " . $mysqli->error);
        }
        
        $stmt->bind_param('s', $asaas_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente_atual = $result->fetch_assoc();
        $stmt->close();
        
        // Se cliente não existe, criar novo
        if (!$cliente_atual) {
            return criarNovoCliente($mysqli, $cli);
        }
        
        // Verificar se o cliente foi editado recentemente (últimas 24 horas)
        $data_ultima_edicao = $cliente_atual['data_atualizacao'] ?? null;
        $foi_editado_recentemente = false;
        
        if ($data_ultima_edicao) {
            $ultima_edicao = new DateTime($data_ultima_edicao);
            $agora = new DateTime();
            $diferenca = $agora->diff($ultima_edicao);
            $foi_editado_recentemente = $diferenca->days < 1; // Menos de 24 horas
        }
        
        if ($foi_editado_recentemente) {
            printAndLog("Cliente $asaas_id foi editado recentemente, preservando dados locais");
            return true; // Preserva dados locais
        }
        
        // Preparar campos para atualização (apenas se dados do Asaas são mais completos)
        $campos_para_atualizar = [];
        $valores = [];
        $tipos = '';
        
        // Mapeamento de campos do Asaas para o banco
        $mapeamento = [
            'name' => 'nome',
            'email' => 'email',
            'phone' => 'telefone',
            'mobilePhone' => 'celular',
            'postalCode' => 'cep',
            'cpfCnpj' => 'cpf_cnpj',
            'address' => 'rua',
            'addressNumber' => 'numero',
            'complement' => 'complemento',
            'province' => 'bairro',
            'city' => 'cidade',
            'state' => 'estado',
            'country' => 'pais',
            'notificationDisabled' => 'notificacao_desativada',
            'additionalEmails' => 'emails_adicionais',
            'externalReference' => 'referencia_externa',
            'observations' => 'observacoes',
            'company' => 'razao_social'
        ];
        
        foreach ($mapeamento as $campo_asaas => $campo_banco) {
            $valor_asaas = $cli[$campo_asaas] ?? null;
            $valor_atual = $cliente_atual[$campo_banco] ?? null;
            
            // Só atualiza se:
            // 1. O campo atual estiver vazio E o valor do Asaas não estiver vazio
            // 2. OU se o valor do Asaas for mais completo/atualizado
            $deve_atualizar = false;
            
            if (empty($valor_atual) && !empty($valor_asaas)) {
                $deve_atualizar = true;
                printAndLog("Campo '$campo_banco' será preenchido (estava vazio)");
            } elseif (!empty($valor_atual) && !empty($valor_asaas) && $valor_atual !== $valor_asaas) {
                // Para campos críticos, preservar dados locais
                $campos_criticos = ['nome', 'email', 'cpf_cnpj', 'telefone', 'celular'];
                if (!in_array($campo_banco, $campos_criticos)) {
                    $deve_atualizar = true;
                    printAndLog("Campo '$campo_banco' será atualizado (diferente do local)");
                } else {
                    printAndLog("Campo crítico '$campo_banco' preservado (dados locais mantidos)");
                }
            }
            
            if ($deve_atualizar) {
                $campos_para_atualizar[] = "$campo_banco = ?";
                $valores[] = $valor_asaas;
                $tipos .= 's';
            }
        }
        
        // Se não há campos para atualizar, retorna sucesso
        if (empty($campos_para_atualizar)) {
            printAndLog("Cliente $asaas_id já está atualizado");
            return true;
        }
        
        // Adicionar data_atualizacao
        $campos_para_atualizar[] = "data_atualizacao = ?";
        $valores[] = date('Y-m-d H:i:s');
        $tipos .= 's';
        
        // Construir e executar query
        $sql = "UPDATE clientes SET " . implode(', ', $campos_para_atualizar) . " WHERE asaas_id = ?";
        $valores[] = $asaas_id;
        $tipos .= 's';
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar update de cliente: " . $mysqli->error);
        }
        
        $stmt->bind_param($tipos, ...$valores);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Erro ao executar update de cliente: " . $stmt->error);
        }
        
        $stmt->close();
        printAndLog("Cliente $asaas_id atualizado com sucesso");
        return true;
        
    } catch (Exception $e) {
        printAndLog("ERRO ao sincronizar cliente: " . $e->getMessage());
        return false;
    }
}

// Função para criar novo cliente
function criarNovoCliente($mysqli, $cli) {
    try {
        $asaas_id = $cli['id'] ?? null;
        $nome = $cli['name'] ?? 'Desconhecido';
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
        $pais = $cli['country'] ?? 'Brasil';
        $notificacao_desativada = isset($cli['notificationDisabled']) ? (int)$cli['notificationDisabled'] : 0;
        $emails_adicionais = $cli['additionalEmails'] ?? '';
        $referencia_externa = $cli['externalReference'] ?? '';
        $observacoes = $cli['observations'] ?? '';
        $razao_social = $cli['company'] ?? '';
        $criado_em_asaas = isset($cli['createdAt']) ? date('Y-m-d H:i:s', strtotime($cli['createdAt'])) : null;
        $cpf_cnpj = $cli['cpfCnpj'] ?? '';
        $data_criacao = date('Y-m-d H:i:s');
        $data_atualizacao = date('Y-m-d H:i:s');
        
        $stmt = $mysqli->prepare("INSERT INTO clientes (
            asaas_id, nome, email, telefone, celular, cep, rua, numero, complemento, bairro, cidade, estado, pais, notificacao_desativada, emails_adicionais, referencia_externa, observacoes, razao_social, criado_em_asaas, cpf_cnpj, data_criacao, data_atualizacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar inserção de cliente: " . $mysqli->error);
        }
        
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
        
        $result = $stmt->execute();
        if (!$result) {
            throw new Exception("Erro ao inserir cliente: " . $stmt->error);
        }
        
        $stmt->close();
        printAndLog("Novo cliente $asaas_id criado com sucesso");
        return true;
        
    } catch (Exception $e) {
        printAndLog("ERRO ao criar cliente: " . $e->getMessage());
        return false;
    }
}

// Função para sincronizar cobranças (sem alterações, sempre sincroniza)
function sincronizarCobrancas($mysqli) {
    printAndLog('--- SINCRONIZANDO COBRANÇAS ---');
    $cobrancas = [];
    $offset = 0;
    $maxPaginas = 30;
    $paginaAtual = 0;
    
    do {
        $resp = getAsaas("/payments?limit=100&offset=$offset");
        $paginaAtual++;
        
        if ($paginaAtual > $maxPaginas) {
            printAndLog('ERRO: Limite de páginas de cobranças atingido.');
            break;
        }
        
        if ($resp === null) {
            printAndLog('ERRO: Falha ao buscar cobranças. Parando.');
            break;
        }
        
        if (!empty($resp['data'])) {
            printAndLog('Encontradas ' . count($resp['data']) . ' cobranças na página ' . $paginaAtual);
            
            foreach ($resp['data'] as $cob) {
                $cobrancas[] = $cob;
                $cobranca_id = $cob['id'] ?? null;
                printAndLog('Processando cobrança: ' . $cobranca_id);
                
                // Buscar o id local do cliente
                $cliente_id = null;
                $asaas_id = $cob['customer'];
                $stmt_cliente = $mysqli->prepare("SELECT id FROM clientes WHERE asaas_id = ? LIMIT 1");
                $stmt_cliente->bind_param('s', $asaas_id);
                $stmt_cliente->execute();
                $stmt_cliente->bind_result($cliente_id);
                $stmt_cliente->fetch();
                $stmt_cliente->close();
                
                if (!$cliente_id) {
                    printAndLog("ERRO: cobrança {$cob['id']} ignorada, cliente não encontrado (asaas_id: $asaas_id)");
                    continue;
                }
                
                // Upsert cobrança (sempre sincroniza cobranças)
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
                
                $valor = $cob['value'] ?? 0;
                $vencimento = isset($cob['dueDate']) ? date('Y-m-d', strtotime($cob['dueDate'])) : null;
                $status = $cob['status'] ?? '';
                $url_fatura = $cob['invoiceUrl'] ?? '';
                
                $stmt = $mysqli->prepare("INSERT INTO cobrancas (
                    asaas_payment_id, cliente_id, valor, vencimento, status, data_pagamento, data_criacao, url_fatura
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor),
                    vencimento = VALUES(vencimento),
                    status = VALUES(status),
                    data_pagamento = VALUES(data_pagamento),
                    url_fatura = VALUES(url_fatura)");
                
                if (!$stmt) {
                    printAndLog('ERRO ao preparar cobrança: ' . $mysqli->error);
                    continue;
                }
                
                $stmt->bind_param('sidsiss', 
                    $cobranca_id, $cliente_id, $valor, $vencimento, $status, $data_pagamento, $data_criacao, $url_fatura
                );
                
                $result = $stmt->execute();
                if (!$result) {
                    printAndLog('ERRO ao inserir cobrança: ' . $stmt->error);
                }
                
                $stmt->close();
                printAndLog('Cobrança processada: ' . $cobranca_id);
            }
        } else {
            printAndLog('Nenhuma cobrança encontrada na página ' . $paginaAtual);
        }
        
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    printAndLog('Cobranças sincronizadas: ' . count($cobrancas));
    return count($cobrancas);
}

// INÍCIO DA SINCRONIZAÇÃO SEGURA
try {
    printAndLog('==== INÍCIO DA SINCRONIZAÇÃO SEGURA ====');
    printAndLog('Iniciando sincronização segura com Asaas...');
    printAndLog('Dados editados recentemente serão preservados.');

    // 1. Sincronizar clientes de forma segura
    printAndLog('--- ETAPA 1: Sincronizando clientes (modo seguro) ---');
    $clientes = [];
    $offset = 0;
    $clientes_sincronizados = 0;
    $clientes_preservados = 0;
    
    do {
        $resp = getAsaas("/customers?limit=100&offset=$offset");
        if ($resp === null) {
            printAndLog('ERRO: Falha ao buscar clientes. Parando.');
            break;
        }
        
        if (!empty($resp['data'])) {
            printAndLog('Encontrados ' . count($resp['data']) . ' clientes na página ' . ($offset/100 + 1));
            
            foreach ($resp['data'] as $cli) {
                $clientes[] = $cli;
                $resultado = sincronizarClienteSeguro($mysqli, $cli);
                
                if ($resultado === true) {
                    $clientes_sincronizados++;
                } elseif ($resultado === 'preservado') {
                    $clientes_preservados++;
                }
            }
        } else {
            printAndLog('Nenhum cliente encontrado na página ' . ($offset/100 + 1));
        }
        
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    printAndLog("Clientes processados: " . count($clientes));
    printAndLog("Clientes sincronizados: $clientes_sincronizados");
    printAndLog("Clientes preservados: $clientes_preservados");
    printAndLog('--- FIM ETAPA 1 ---');

    // 2. Sincronizar cobranças (sempre sincroniza)
    $cobrancas_sincronizadas = sincronizarCobrancas($mysqli);
    printAndLog('--- FIM ETAPA 2 ---');

    printAndLog('==== SINCRONIZAÇÃO SEGURA FINALIZADA ====');
    printAndLog("Resumo:");
    printAndLog("- Clientes processados: " . count($clientes));
    printAndLog("- Clientes sincronizados: $clientes_sincronizados");
    printAndLog("- Clientes preservados: $clientes_preservados");
    printAndLog("- Cobranças sincronizadas: $cobrancas_sincronizadas");
    printAndLog("Dados editados recentemente foram preservados com sucesso!");

} catch (Exception $e) {
    printAndLog('ERRO FATAL: ' . $e->getMessage());
    printAndLog('Sincronização finalizada com erro.');
    exit(1);
}

$mysqli->close();
?> 