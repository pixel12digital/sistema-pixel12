<?php
require_once 'config.php';
require_once 'painel/db.php';

/**
 * 🔧 CORREÇÃO AUTOMÁTICA DE REGISTROS PROBLEMÁTICOS
 * 
 * Este script corrige registros com dados vazios que podem causar problemas
 * Executar após análise manual dos dados
 */

class CorretorRegistrosProblematicos {
    private $mysqli;
    private $log_file;
    private $backup_file;
    
    public function __construct() {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->mysqli->connect_error) {
            throw new Exception("Erro de conexão: " . $this->mysqli->connect_error);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $this->log_file = "logs/correcao_automatica_{$timestamp}.log";
        $this->backup_file = "backups/correcao_automatica_{$timestamp}.sql";
        
        // Criar diretórios se não existirem
        if (!is_dir('logs')) mkdir('logs', 0755, true);
        if (!is_dir('backups')) mkdir('backups', 0755, true);
    }
    
    /**
     * Log de mensagens
     */
    private function log($mensagem, $tipo = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$tipo] $mensagem\n";
        
        echo $log_entry;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Criar backup dos registros que serão modificados
     */
    private function criarBackup($registros) {
        $this->log("=== CRIANDO BACKUP DE SEGURANÇA ===");
        
        $backup_content = "-- Backup de registros problemáticos - " . date('Y-m-d H:i:s') . "\n";
        $backup_content .= "-- Total de registros: " . count($registros) . "\n\n";
        
        foreach ($registros as $registro) {
            $backup_content .= "-- ID: {$registro['id']} | Nome: {$registro['nome']}\n";
            $backup_content .= "INSERT INTO clientes_backup (id, nome, email, cpf_cnpj, asaas_id, telefone, data_criacao) VALUES ";
            $backup_content .= "({$registro['id']}, '{$registro['nome']}', '{$registro['email']}', '{$registro['cpf_cnpj']}', ";
            $backup_content .= "'{$registro['asaas_id']}', '{$registro['telefone']}', '{$registro['data_criacao']}');\n\n";
        }
        
        file_put_contents($this->backup_file, $backup_content);
        $this->log("✅ Backup salvo em: $this->backup_file");
    }
    
    /**
     * Analisar registros problemáticos
     */
    public function analisarRegistrosProblematicos() {
        $this->log("=== ANÁLISE DE REGISTROS PROBLEMÁTICOS ===");
        
        $problemas = [
            'email_vazio' => [
                'sql' => "SELECT id, nome, email, cpf_cnpj, asaas_id, telefone, data_criacao 
                         FROM clientes WHERE email IS NULL OR email = ''",
                'descricao' => 'Registros com email vazio'
            ],
            'cpf_vazio' => [
                'sql' => "SELECT id, nome, email, cpf_cnpj, asaas_id, telefone, data_criacao 
                         FROM clientes WHERE cpf_cnpj IS NULL OR cpf_cnpj = ''",
                'descricao' => 'Registros com CPF/CNPJ vazio'
            ],
            'nome_vazio' => [
                'sql' => "SELECT id, nome, email, cpf_cnpj, asaas_id, telefone, data_criacao 
                         FROM clientes WHERE nome IS NULL OR nome = ''",
                'descricao' => 'Registros com nome vazio'
            ],
            'asaas_id_vazio' => [
                'sql' => "SELECT id, nome, email, cpf_cnpj, asaas_id, telefone, data_criacao 
                         FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''",
                'descricao' => 'Registros sem ID do Asaas'
            ]
        ];
        
        $todos_registros = [];
        
        foreach ($problemas as $tipo => $config) {
            $this->log("Analisando {$config['descricao']}...");
            
            $result = $this->mysqli->query($config['sql']);
            if ($result && $result->num_rows > 0) {
                $this->log("   Encontrados {$result->num_rows} registros");
                
                while ($row = $result->fetch_assoc()) {
                    $row['tipo_problema'] = $tipo;
                    $todos_registros[] = $row;
                    
                    $this->log("   - ID: {$row['id']} | Nome: {$row['nome']} | Email: {$row['email']} | CPF: {$row['cpf_cnpj']}");
                }
            } else {
                $this->log("   ✅ Nenhum registro encontrado");
            }
        }
        
        $this->log("Total de registros problemáticos: " . count($todos_registros));
        
        return $todos_registros;
    }
    
    /**
     * Corrigir registros com nome vazio
     */
    private function corrigirNomesVazios($registros) {
        $this->log("=== CORRIGINDO NOMES VAZIOS ===");
        
        $corrigidos = 0;
        
        foreach ($registros as $registro) {
            if ($registro['tipo_problema'] === 'nome_vazio') {
                $novo_nome = "Cliente ID {$registro['id']}";
                
                if (!empty($registro['email'])) {
                    $novo_nome = "Cliente " . explode('@', $registro['email'])[0];
                } elseif (!empty($registro['cpf_cnpj'])) {
                    $novo_nome = "Cliente " . substr($registro['cpf_cnpj'], -4);
                }
                
                $sql = "UPDATE clientes SET nome = ? WHERE id = ?";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param('si', $novo_nome, $registro['id']);
                
                if ($stmt->execute()) {
                    $this->log("   ✅ ID {$registro['id']}: Nome corrigido para '$novo_nome'");
                    $corrigidos++;
                } else {
                    $this->log("   ❌ ID {$registro['id']}: Erro ao corrigir nome", 'ERROR');
                }
                $stmt->close();
            }
        }
        
        $this->log("Nomes corrigidos: $corrigidos");
        return $corrigidos;
    }
    
    /**
     * Remover registros sem asaas_id (se não têm dependências)
     */
    private function removerRegistrosSemAsaasId($registros) {
        $this->log("=== REMOVENDO REGISTROS SEM ASAAS_ID ===");
        
        $removidos = 0;
        
        foreach ($registros as $registro) {
            if ($registro['tipo_problema'] === 'asaas_id_vazio') {
                // Verificar se tem dependências
                $sql_deps = "SELECT COUNT(*) as total FROM cobrancas WHERE cliente_id = ?";
                $stmt = $this->mysqli->prepare($sql_deps);
                $stmt->bind_param('i', $registro['id']);
                $stmt->execute();
                $stmt->bind_result($total_deps);
                $stmt->fetch();
                $stmt->close();
                
                if ($total_deps == 0) {
                    // Verificar outras dependências
                    $sql_outras_deps = "SELECT 
                        (SELECT COUNT(*) FROM pedidos WHERE cliente_id = ?) +
                        (SELECT COUNT(*) FROM mensagens WHERE cliente_id = ?) +
                        (SELECT COUNT(*) FROM assinaturas WHERE cliente_id = ?) as total";
                    
                    $stmt = $this->mysqli->prepare($sql_outras_deps);
                    $stmt->bind_param('iii', $registro['id'], $registro['id'], $registro['id']);
                    $stmt->execute();
                    $stmt->bind_result($total_outras);
                    $stmt->fetch();
                    $stmt->close();
                    
                    if ($total_outras == 0) {
                        // Remover registro
                        $sql_delete = "DELETE FROM clientes WHERE id = ?";
                        $stmt = $this->mysqli->prepare($sql_delete);
                        $stmt->bind_param('i', $registro['id']);
                        
                        if ($stmt->execute()) {
                            $this->log("   ✅ ID {$registro['id']}: Registro removido (sem dependências)");
                            $removidos++;
                        } else {
                            $this->log("   ❌ ID {$registro['id']}: Erro ao remover", 'ERROR');
                        }
                        $stmt->close();
                    } else {
                        $this->log("   ⚠️  ID {$registro['id']}: Mantido (tem $total_outras dependências)");
                    }
                } else {
                    $this->log("   ⚠️  ID {$registro['id']}: Mantido (tem $total_deps cobranças)");
                }
            }
        }
        
        $this->log("Registros removidos: $removidos");
        return $removidos;
    }
    
    /**
     * Marcar registros com email vazio para atualização futura
     */
    private function marcarEmailsVazios($registros) {
        $this->log("=== MARCANDO REGISTROS COM EMAIL VAZIO ===");
        
        $marcados = 0;
        
        foreach ($registros as $registro) {
            if ($registro['tipo_problema'] === 'email_vazio') {
                // Adicionar campo de observação
                $observacao = "Email vazio - Requer atualização manual";
                
                $sql = "UPDATE clientes SET observacoes = CONCAT(COALESCE(observacoes, ''), ' | ', ?) WHERE id = ?";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param('si', $observacao, $registro['id']);
                
                if ($stmt->execute()) {
                    $this->log("   ✅ ID {$registro['id']}: Marcado para atualização de email");
                    $marcados++;
                } else {
                    $this->log("   ❌ ID {$registro['id']}: Erro ao marcar", 'ERROR');
                }
                $stmt->close();
            }
        }
        
        $this->log("Registros marcados: $marcados");
        return $marcados;
    }
    
    /**
     * Marcar registros com CPF vazio para atualização futura
     */
    private function marcarCpfsVazios($registros) {
        $this->log("=== MARCANDO REGISTROS COM CPF VAZIO ===");
        
        $marcados = 0;
        
        foreach ($registros as $registro) {
            if ($registro['tipo_problema'] === 'cpf_vazio') {
                // Adicionar campo de observação
                $observacao = "CPF/CNPJ vazio - Requer atualização manual";
                
                $sql = "UPDATE clientes SET observacoes = CONCAT(COALESCE(observacoes, ''), ' | ', ?) WHERE id = ?";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param('si', $observacao, $registro['id']);
                
                if ($stmt->execute()) {
                    $this->log("   ✅ ID {$registro['id']}: Marcado para atualização de CPF/CNPJ");
                    $marcados++;
                } else {
                    $this->log("   ❌ ID {$registro['id']}: Erro ao marcar", 'ERROR');
                }
                $stmt->close();
            }
        }
        
        $this->log("Registros marcados: $marcados");
        return $marcados;
    }
    
    /**
     * Executar correção completa
     */
    public function executarCorrecao() {
        try {
            $this->log("🚀 INICIANDO CORREÇÃO AUTOMÁTICA DE REGISTROS PROBLEMÁTICOS");
            $this->log("Data/Hora: " . date('Y-m-d H:i:s'));
            
            // 1. Analisar registros problemáticos
            $registros = $this->analisarRegistrosProblematicos();
            
            if (empty($registros)) {
                $this->log("✅ Nenhum registro problemático encontrado!");
                return true;
            }
            
            // 2. Criar backup
            $this->criarBackup($registros);
            
            // 3. Corrigir nomes vazios
            $nomes_corrigidos = $this->corrigirNomesVazios($registros);
            
            // 4. Remover registros sem asaas_id (se não têm dependências)
            $registros_removidos = $this->removerRegistrosSemAsaasId($registros);
            
            // 5. Marcar registros com email vazio
            $emails_marcados = $this->marcarEmailsVazios($registros);
            
            // 6. Marcar registros com CPF vazio
            $cpfs_marcados = $this->marcarCpfsVazios($registros);
            
            // 7. Resumo final
            $this->log("=== RESUMO DA CORREÇÃO ===");
            $this->log("📊 Nomes corrigidos: $nomes_corrigidos");
            $this->log("🗑️  Registros removidos: $registros_removidos");
            $this->log("📝 Emails marcados: $emails_marcados");
            $this->log("📝 CPFs marcados: $cpfs_marcados");
            $this->log("💾 Backup: $this->backup_file");
            $this->log("📋 Log: $this->log_file");
            
            $this->log("✅ CORREÇÃO CONCLUÍDA COM SUCESSO!");
            
        } catch (Exception $e) {
            $this->log("❌ ERRO CRÍTICO: " . $e->getMessage(), 'ERROR');
            return false;
        }
        
        return true;
    }
}

// Executar correção
try {
    $corretor = new CorretorRegistrosProblematicos();
    $sucesso = $corretor->executarCorrecao();
    
    if (!$sucesso) {
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
?> 