<?php
/**
 * Atualização Segura de Clientes do Asaas
 * Preenche apenas campos vazios, preservando dados existentes
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Função para logging
function logDetalhado($mensagem, $tipo = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$tipo] $mensagem\n";
    
    // Log para arquivo
    $log_file = __DIR__ . '/../logs/atualizacao_clientes.log';
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

// Função para atualizar cliente de forma segura
function atualizarClienteSeguro($mysqli, $cli) {
    try {
        // Primeiro, buscar dados atuais do cliente
        $stmt = $mysqli->prepare("SELECT * FROM clientes WHERE asaas_id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Erro ao preparar busca de cliente: " . $mysqli->error);
        }
        
        $stmt->bind_param('s', $cli['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente_atual = $result->fetch_assoc();
        $stmt->close();
        
        if (!$cliente_atual) {
            logDetalhado("Cliente não encontrado no banco local: {$cli['id']}", "WARN");
            return false;
        }
        
        // Preparar campos para atualização (apenas campos vazios)
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
            'companyName' => 'razao_social'
        ];
        
        foreach ($mapeamento as $campo_asaas => $campo_banco) {
            $valor_asaas = $cli[$campo_asaas] ?? null;
            $valor_atual = $cliente_atual[$campo_banco] ?? null;
            
            // Só atualiza se o campo atual estiver vazio e o valor do Asaas não estiver vazio
            if (empty($valor_atual) && !empty($valor_asaas)) {
                $campos_para_atualizar[] = "$campo_banco = ?";
                $valores[] = $valor_asaas;
                $tipos .= 's';
                logDetalhado("Campo '$campo_banco' será preenchido com valor do Asaas", "INFO");
            }
        }
        
        // Se não há campos para atualizar, retorna sucesso
        if (empty($campos_para_atualizar)) {
            logDetalhado("Cliente {$cli['id']} já está atualizado", "INFO");
            return true;
        }
        
        // Adicionar data_atualizacao
        $campos_para_atualizar[] = "data_atualizacao = ?";
        $valores[] = date('Y-m-d H:i:s');
        $tipos .= 's';
        
        // Construir e executar query
        $sql = "UPDATE clientes SET " . implode(', ', $campos_para_atualizar) . " WHERE asaas_id = ?";
        $valores[] = $cli['id'];
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
        logDetalhado("Cliente {$cli['id']} atualizado com sucesso", "INFO");
        return true;
        
    } catch (Exception $e) {
        logDetalhado("ERRO ao atualizar cliente: " . $e->getMessage(), "ERROR");
        return false;
    }
}

// INÍCIO DA ATUALIZAÇÃO
try {
    logDetalhado("==== INICIANDO ATUALIZAÇÃO SEGURA DE CLIENTES ====");
    
    $clientes = [];
    $offset = 0;
    $maxPaginas = 50;
    $paginaAtual = 0;
    $atualizados = 0;
    $erros = 0;
    
    do {
        $resp = getAsaas("/customers?limit=100&offset=$offset");
        $paginaAtual++;
        
        if ($paginaAtual > $maxPaginas) {
            logDetalhado("ERRO: Limite de páginas atingido.", "ERROR");
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
                $asaas_id = $cli['id'];
                
                if (atualizarClienteSeguro($mysqli, $cli)) {
                    $atualizados++;
                } else {
                    $erros++;
                }
            }
        } else {
            logDetalhado("Nenhum cliente encontrado na página " . ($offset/100 + 1));
        }
        
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100);
    
    logDetalhado("==== ATUALIZAÇÃO SEGURA CONCLUÍDA ====");
    logDetalhado("Total de clientes processados: " . count($clientes));
    logDetalhado("Clientes atualizados: $atualizados");
    logDetalhado("Erros: $erros");
    exit(0);
    
} catch (Throwable $e) {
    logDetalhado("ERRO FATAL: " . $e->getMessage(), "FATAL");
    exit(1);
}
?> 