<?php
/**
 * Script de Correção Completa do Banco de Dados
 * - Remove duplicatas e identifica causas
 * - Protege dados editados manualmente
 * - Sincroniza corretamente com Asaas
 * - Verifica e corrige discrepâncias
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Função para logging
function logCorrecao($mensagem, $tipo = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$tipo] $mensagem\n";
    
    // Log para arquivo
    $log_file = __DIR__ . '/../logs/correcao_banco_asaas.log';
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
        logCorrecao("ERRO CURL: $error", "ERROR");
        return null;
    }
    
    if ($http_code !== 200) {
        logCorrecao("ERRO HTTP: $http_code - $response", "ERROR");
        return null;
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logCorrecao("ERRO JSON: " . json_last_error_msg(), "ERROR");
        return null;
    }
    
    return $data;
}

// Função para reconectar ao banco se necessário
function reconectarBanco() {
    global $mysqli;
    
    if (!$mysqli || $mysqli->ping() === false) {
        logCorrecao("Reconectando ao banco de dados...", "WARN");
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            logCorrecao("❌ ERRO: Falha ao reconectar: " . $mysqli->connect_error, "ERROR");
            return false;
        }
        
        $mysqli->set_charset("utf8mb4");
        logCorrecao("✅ Reconectado com sucesso", "INFO");
    }
    
    return true;
}

try {
    logCorrecao("==== INICIANDO CORREÇÃO COMPLETA DO BANCO DE DADOS ====");
    
    // 1. Verificar conexões
    logCorrecao("1. Verificando conexões...");
    if (!reconectarBanco()) {
        logCorrecao("❌ FALHA: Não foi possível conectar ao banco", "ERROR");
        exit(1);
    }
    
    // Testar API do Asaas
    $teste = getAsaas("/customers?limit=1");
    if ($teste === null) {
        logCorrecao("❌ FALHA: Não foi possível conectar à API do Asaas", "ERROR");
        exit(1);
    }
    logCorrecao("✅ Conexões verificadas com sucesso");
    
    // 2. Analisar e remover duplicatas de clientes
    logCorrecao("2. Analisando duplicatas de clientes...");
    
    // Verificar duplicatas por asaas_id
    $stmt = $mysqli->prepare("
        SELECT asaas_id, COUNT(*) as total, GROUP_CONCAT(id) as ids
        FROM clientes 
        WHERE asaas_id IS NOT NULL AND asaas_id != ''
        GROUP BY asaas_id 
        HAVING COUNT(*) > 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $duplicatasAsaas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    logCorrecao("Encontradas " . count($duplicatasAsaas) . " duplicatas por asaas_id");
    
    foreach ($duplicatasAsaas as $dup) {
        $ids = explode(',', $dup['ids']);
        $primeiroId = $ids[0]; // Manter o primeiro
        $idsParaRemover = array_slice($ids, 1); // Remover os demais
        
        logCorrecao("Duplicata asaas_id '{$dup['asaas_id']}': mantendo ID $primeiroId, removendo IDs: " . implode(', ', $idsParaRemover));
        
        foreach ($idsParaRemover as $idParaRemover) {
            // Verificar se há cobranças associadas
            $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM cobrancas WHERE cliente_id = ?");
            $stmt->bind_param('i', $idParaRemover);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['total'] > 0) {
                logCorrecao("⚠️ Cliente ID $idParaRemover tem {$row['total']} cobranças - transferindo para ID $primeiroId", "WARN");
                
                // Transferir cobranças para o primeiro cliente
                $stmt = $mysqli->prepare("UPDATE cobrancas SET cliente_id = ? WHERE cliente_id = ?");
                $stmt->bind_param('ii', $primeiroId, $idParaRemover);
                $stmt->execute();
                $stmt->close();
            }
            
            // Remover cliente duplicado
            $stmt = $mysqli->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param('i', $idParaRemover);
            $stmt->execute();
            $stmt->close();
            
            logCorrecao("✅ Cliente ID $idParaRemover removido");
        }
    }
    
    // Verificar duplicatas por email
    $stmt = $mysqli->prepare("
        SELECT email, COUNT(*) as total, GROUP_CONCAT(id) as ids
        FROM clientes 
        WHERE email IS NOT NULL AND email != ''
        GROUP BY email 
        HAVING COUNT(*) > 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $duplicatasEmail = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    logCorrecao("Encontradas " . count($duplicatasEmail) . " duplicatas por email");
    
    foreach ($duplicatasEmail as $dup) {
        $ids = explode(',', $dup['ids']);
        $primeiroId = $ids[0];
        $idsParaRemover = array_slice($ids, 1);
        
        logCorrecao("Duplicata email '{$dup['email']}': mantendo ID $primeiroId, removendo IDs: " . implode(', ', $idsParaRemover));
        
        foreach ($idsParaRemover as $idParaRemover) {
            // Verificar se há cobranças associadas
            $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM cobrancas WHERE cliente_id = ?");
            $stmt->bind_param('i', $idParaRemover);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['total'] > 0) {
                logCorrecao("⚠️ Cliente ID $idParaRemover tem {$row['total']} cobranças - transferindo para ID $primeiroId", "WARN");
                
                // Transferir cobranças
                $stmt = $mysqli->prepare("UPDATE cobrancas SET cliente_id = ? WHERE cliente_id = ?");
                $stmt->bind_param('ii', $primeiroId, $idParaRemover);
                $stmt->execute();
                $stmt->close();
            }
            
            // Remover cliente duplicado
            $stmt = $mysqli->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param('i', $idParaRemover);
            $stmt->execute();
            $stmt->close();
            
            logCorrecao("✅ Cliente ID $idParaRemover removido");
        }
    }
    
    // 3. Adicionar campos de proteção para dados manuais
    logCorrecao("3. Adicionando campos de proteção para dados manuais...");
    
    // Verificar se os campos de proteção existem
    $camposProtecao = [
        'telefone_editado_manual' => 'TINYINT(1) DEFAULT 0',
        'celular_editado_manual' => 'TINYINT(1) DEFAULT 0',
        'email_editado_manual' => 'TINYINT(1) DEFAULT 0',
        'nome_editado_manual' => 'TINYINT(1) DEFAULT 0',
        'endereco_editado_manual' => 'TINYINT(1) DEFAULT 0',
        'data_ultima_edicao_manual' => 'DATETIME NULL'
    ];
    
    foreach ($camposProtecao as $campo => $tipo) {
        $stmt = $mysqli->prepare("SHOW COLUMNS FROM clientes LIKE ?");
        $stmt->bind_param('s', $campo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows === 0) {
            logCorrecao("Adicionando campo de proteção: $campo");
            $sql = "ALTER TABLE clientes ADD COLUMN $campo $tipo";
            $mysqli->query($sql);
            
            if ($mysqli->error) {
                logCorrecao("❌ Erro ao adicionar campo $campo: " . $mysqli->error, "ERROR");
            } else {
                logCorrecao("✅ Campo $campo adicionado com sucesso");
            }
        } else {
            logCorrecao("Campo $campo já existe");
        }
    }
    
    // 4. Marcar dados que foram editados manualmente (baseado em diferenças com Asaas)
    logCorrecao("4. Identificando dados editados manualmente...");
    
    // Buscar clientes do Asaas
    $clientesAsaas = [];
    $offset = 0;
    $pagina = 0;
    
    do {
        $resp = getAsaas("/customers?limit=100&offset=$offset");
        $pagina++;
        
        if ($resp === null) {
            logCorrecao("❌ Erro ao buscar clientes na página $pagina", "ERROR");
            break;
        }
        
        if (!empty($resp['data'])) {
            foreach ($resp['data'] as $cliente) {
                $clientesAsaas[$cliente['id']] = $cliente;
            }
            logCorrecao("Página $pagina: " . count($resp['data']) . " clientes carregados");
        }
        
        $offset += 100;
    } while (!empty($resp['data']) && count($resp['data']) === 100 && $pagina < 50);
    
    logCorrecao("Total de clientes carregados do Asaas: " . count($clientesAsaas));
    
    // Comparar com clientes locais
    $stmt = $mysqli->prepare("SELECT id, asaas_id, nome, email, telefone, celular, endereco FROM clientes WHERE asaas_id IS NOT NULL");
    $stmt->execute();
    $result = $stmt->get_result();
    $clientesLocais = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $dadosEditados = 0;
    foreach ($clientesLocais as $clienteLocal) {
        $asaasId = $clienteLocal['asaas_id'];
        
        if (isset($clientesAsaas[$asaasId])) {
            $clienteAsaas = $clientesAsaas[$asaasId];
            $camposAlterados = [];
            
            // Comparar campos
            if ($clienteLocal['nome'] !== $clienteAsaas['name']) {
                $camposAlterados[] = 'nome';
            }
            if ($clienteLocal['email'] !== $clienteAsaas['email']) {
                $camposAlterados[] = 'email';
            }
            if ($clienteLocal['telefone'] !== $clienteAsaas['phone']) {
                $camposAlterados[] = 'telefone';
            }
            if ($clienteLocal['celular'] !== $clienteAsaas['mobilePhone']) {
                $camposAlterados[] = 'celular';
            }
            
            if (!empty($camposAlterados)) {
                logCorrecao("Cliente ID {$clienteLocal['id']} tem dados diferentes do Asaas: " . implode(', ', $camposAlterados));
                
                // Marcar campos como editados manualmente
                $updates = [];
                $types = '';
                $values = [];
                
                foreach ($camposAlterados as $campo) {
                    $campoProtecao = $campo . '_editado_manual';
                    $updates[] = "$campoProtecao = 1";
                }
                
                if (!empty($updates)) {
                    $updates[] = "data_ultima_edicao_manual = NOW()";
                    $sql = "UPDATE clientes SET " . implode(', ', $updates) . " WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param('i', $clienteLocal['id']);
                    $stmt->execute();
                    $stmt->close();
                    
                    $dadosEditados++;
                }
            }
        }
    }
    
    logCorrecao("Marcados $dadosEditados clientes com dados editados manualmente");
    
    // 5. Limpar cobranças órfãs (sem cliente)
    logCorrecao("5. Limpando cobranças órfãs...");
    
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) as total 
        FROM cobrancas c 
        LEFT JOIN clientes cl ON c.cliente_id = cl.id 
        WHERE cl.id IS NULL
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $cobrancasOrfas = $row['total'];
    logCorrecao("Encontradas $cobrancasOrfas cobranças órfãs");
    
    if ($cobrancasOrfas > 0) {
        $stmt = $mysqli->prepare("
            DELETE c FROM cobrancas c 
            LEFT JOIN clientes cl ON c.cliente_id = cl.id 
            WHERE cl.id IS NULL
        ");
        $stmt->execute();
        $stmt->close();
        logCorrecao("✅ Cobranças órfãs removidas");
    }
    
    // 6. Verificar e corrigir cobranças duplicadas
    logCorrecao("6. Verificando cobranças duplicadas...");
    
    $stmt = $mysqli->prepare("
        SELECT asaas_payment_id, COUNT(*) as total, GROUP_CONCAT(id) as ids
        FROM cobrancas 
        WHERE asaas_payment_id IS NOT NULL AND asaas_payment_id != ''
        GROUP BY asaas_payment_id 
        HAVING COUNT(*) > 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $cobrancasDuplicadas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    logCorrecao("Encontradas " . count($cobrancasDuplicadas) . " cobranças duplicadas");
    
    foreach ($cobrancasDuplicadas as $dup) {
        $ids = explode(',', $dup['ids']);
        $primeiroId = $ids[0];
        $idsParaRemover = array_slice($ids, 1);
        
        logCorrecao("Cobrança duplicada asaas_payment_id '{$dup['asaas_payment_id']}': mantendo ID $primeiroId, removendo IDs: " . implode(', ', $idsParaRemover));
        
        foreach ($idsParaRemover as $idParaRemover) {
            $stmt = $mysqli->prepare("DELETE FROM cobrancas WHERE id = ?");
            $stmt->bind_param('i', $idParaRemover);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // 7. Estatísticas finais
    logCorrecao("7. Gerando estatísticas finais...");
    
    // Contar clientes
    $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM clientes");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalClientes = $row['total'];
    $stmt->close();
    
    // Contar cobranças
    $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM cobrancas");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalCobrancas = $row['total'];
    $stmt->close();
    
    // Contar clientes com dados editados manualmente
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) as total 
        FROM clientes 
        WHERE telefone_editado_manual = 1 
           OR celular_editado_manual = 1 
           OR email_editado_manual = 1 
           OR nome_editado_manual = 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $clientesComDadosEditados = $row['total'];
    $stmt->close();
    
    logCorrecao("==== RESUMO DA CORREÇÃO ====");
    logCorrecao("Total de clientes: $totalClientes");
    logCorrecao("Total de cobranças: $totalCobrancas");
    logCorrecao("Clientes com dados editados manualmente: $clientesComDadosEditados");
    logCorrecao("Duplicatas de clientes removidas: " . (count($duplicatasAsaas) + count($duplicatasEmail)));
    logCorrecao("Cobranças órfãs removidas: $cobrancasOrfas");
    logCorrecao("Cobranças duplicadas removidas: " . count($cobrancasDuplicadas));
    
    logCorrecao("🔄 PRÓXIMOS PASSOS:");
    logCorrecao("1. Execute a sincronização para atualizar dados do Asaas");
    logCorrecao("2. Os dados marcados como 'editados manualmente' não serão sobrescritos");
    logCorrecao("3. Monitore os logs para verificar se a sincronização está funcionando corretamente");
    
    logCorrecao("==== CORREÇÃO CONCLUÍDA COM SUCESSO ====");
    
} catch (Exception $e) {
    logCorrecao("❌ ERRO: " . $e->getMessage(), "ERROR");
    logCorrecao("Arquivo: " . $e->getFile() . " | Linha: " . $e->getLine(), "ERROR");
} catch (Error $e) {
    logCorrecao("❌ ERRO FATAL: " . $e->getMessage(), "ERROR");
    logCorrecao("Arquivo: " . $e->getFile() . " | Linha: " . $e->getLine(), "ERROR");
}
?> 