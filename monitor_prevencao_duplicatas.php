<?php
require_once 'config.php';
require_once 'painel/db.php';

/**
 * 🔍 MONITOR DE PREVENÇÃO DE DUPLICATAS
 * 
 * Este script monitora e previne duplicatas no banco de dados
 * Executar diariamente via cron job
 */

class MonitorDuplicatas {
    private $mysqli;
    private $log_file;
    
    public function __construct() {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->mysqli->connect_error) {
            throw new Exception("Erro de conexão: " . $this->mysqli->connect_error);
        }
        
        $this->log_file = 'logs/monitor_duplicatas_' . date('Y-m-d') . '.log';
        
        // Criar diretório de logs se não existir
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true);
        }
    }
    
    /**
     * Log de mensagens com timestamp
     */
    private function log($mensagem, $tipo = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$tipo] $mensagem\n";
        
        echo $log_entry;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Verificar duplicatas por diferentes critérios
     */
    public function verificarDuplicatas() {
        $this->log("=== INICIANDO VERIFICAÇÃO DE DUPLICATAS ===");
        
        $criterios = [
            'asaas_id' => 'ID do Asaas',
            'email' => 'Email',
            'cpf_cnpj' => 'CPF/CNPJ',
            'telefone' => 'Telefone'
        ];
        
        $duplicatas_encontradas = false;
        
        foreach ($criterios as $campo => $descricao) {
            $this->log("Verificando duplicatas por $descricao...");
            
            $sql = "SELECT $campo, COUNT(*) as total, GROUP_CONCAT(id) as ids
                    FROM clientes 
                    WHERE $campo IS NOT NULL AND $campo != '' 
                    GROUP BY $campo 
                    HAVING COUNT(*) > 1";
            
            $result = $this->mysqli->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $duplicatas_encontradas = true;
                $this->log("❌ ENCONTRADAS DUPLICATAS por $descricao!", 'ERROR');
                
                while ($row = $result->fetch_assoc()) {
                    $this->log("   $campo: '{$row[$campo]}' - {$row['total']} registros (IDs: {$row['ids']})", 'ERROR');
                    
                    // Mostrar detalhes dos registros duplicados
                    $ids = explode(',', $row['ids']);
                    foreach ($ids as $id) {
                        $sql_detalhes = "SELECT id, nome, email, cpf_cnpj, asaas_id, data_criacao 
                                       FROM clientes WHERE id = $id";
                        $detalhes = $this->mysqli->query($sql_detalhes);
                        if ($detalhes && $detalhe = $detalhes->fetch_assoc()) {
                            $this->log("      ID: {$detalhe['id']} | Nome: {$detalhe['nome']} | Criado: {$detalhe['data_criacao']}", 'ERROR');
                        }
                    }
                }
            } else {
                $this->log("✅ Nenhuma duplicata encontrada por $descricao");
            }
        }
        
        if (!$duplicatas_encontradas) {
            $this->log("✅ VERIFICAÇÃO CONCLUÍDA: Nenhuma duplicata encontrada!");
        } else {
            $this->log("⚠️  ATENÇÃO: Duplicatas encontradas! Verificar manualmente.", 'WARN');
        }
        
        return !$duplicatas_encontradas;
    }
    
    /**
     * Verificar registros problemáticos
     */
    public function verificarRegistrosProblematicos() {
        $this->log("=== VERIFICANDO REGISTROS PROBLEMÁTICOS ===");
        
        $problemas = [
            'email_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE email IS NULL OR email = ''",
            'cpf_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE cpf_cnpj IS NULL OR cpf_cnpj = ''",
            'asaas_id_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE asaas_id IS NULL OR asaas_id = ''",
            'nome_vazio' => "SELECT COUNT(*) as total FROM clientes WHERE nome IS NULL OR nome = ''"
        ];
        
        $total_problemas = 0;
        
        foreach ($problemas as $tipo => $sql) {
            $result = $this->mysqli->query($sql);
            if ($result) {
                $total = $result->fetch_assoc()['total'];
                if ($total > 0) {
                    $this->log("⚠️  $tipo: $total registros", 'WARN');
                    $total_problemas += $total;
                }
            }
        }
        
        if ($total_problemas == 0) {
            $this->log("✅ Nenhum registro problemático encontrado");
        } else {
            $this->log("⚠️  Total de registros problemáticos: $total_problemas", 'WARN');
        }
        
        return $total_problemas;
    }
    
    /**
     * Verificar integridade dos índices únicos
     */
    public function verificarIndicesUnicos() {
        $this->log("=== VERIFICANDO ÍNDICES ÚNICOS ===");
        
        $indices_esperados = ['idx_asaas_id_unique', 'idx_email_unique', 'idx_cpf_cnpj_unique'];
        $indices_ativos = [];
        
        $sql = "SHOW INDEX FROM clientes WHERE Non_unique = 0 AND Key_name != 'PRIMARY'";
        $result = $this->mysqli->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $indices_ativos[] = $row['Key_name'];
                $this->log("✅ Índice único ativo: {$row['Key_name']} em {$row['Column_name']}");
            }
        }
        
        foreach ($indices_esperados as $indice) {
            if (!in_array($indice, $indices_ativos)) {
                $this->log("❌ Índice único ausente: $indice", 'ERROR');
                return false;
            }
        }
        
        $this->log("✅ Todos os índices únicos estão ativos");
        return true;
    }
    
    /**
     * Verificar registros recentes (últimas 24h)
     */
    public function verificarRegistrosRecentes() {
        $this->log("=== VERIFICANDO REGISTROS RECENTES ===");
        
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $result = $this->mysqli->query($sql);
        
        if ($result) {
            $total = $result->fetch_assoc()['total'];
            $this->log("📅 Clientes criados nas últimas 24h: $total");
            
            if ($total > 0) {
                // Verificar se há duplicatas nos registros recentes
                $sql_recentes = "SELECT c1.*, c2.id as duplicata_id 
                                FROM clientes c1 
                                INNER JOIN clientes c2 ON (
                                    (c1.email = c2.email AND c1.email != '' AND c1.id != c2.id) OR
                                    (c1.cpf_cnpj = c2.cpf_cnpj AND c1.cpf_cnpj != '' AND c1.id != c2.id) OR
                                    (c1.asaas_id = c2.asaas_id AND c1.asaas_id != '' AND c1.id != c2.id)
                                )
                                WHERE c1.data_criacao >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                
                $result_recentes = $this->mysqli->query($sql_recentes);
                if ($result_recentes && $result_recentes->num_rows > 0) {
                    $this->log("❌ ATENÇÃO: Duplicatas encontradas nos registros recentes!", 'ERROR');
                    while ($row = $result_recentes->fetch_assoc()) {
                        $this->log("   ID: {$row['id']} duplicado com ID: {$row['duplicata_id']}", 'ERROR');
                    }
                    return false;
                } else {
                    $this->log("✅ Nenhuma duplicata nos registros recentes");
                }
            }
        }
        
        return true;
    }
    
    /**
     * Gerar relatório de estatísticas
     */
    public function gerarEstatisticas() {
        $this->log("=== ESTATÍSTICAS DO BANCO ===");
        
        $stats = [
            'total_clientes' => "SELECT COUNT(*) as total FROM clientes",
            'clientes_hoje' => "SELECT COUNT(*) as total FROM clientes WHERE DATE(data_criacao) = CURDATE()",
            'clientes_semana' => "SELECT COUNT(*) as total FROM clientes WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            'clientes_mes' => "SELECT COUNT(*) as total FROM clientes WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ];
        
        foreach ($stats as $nome => $sql) {
            $result = $this->mysqli->query($sql);
            if ($result) {
                $total = $result->fetch_assoc()['total'];
                $this->log("📊 $nome: $total");
            }
        }
    }
    
    /**
     * Executar verificação completa
     */
    public function executarVerificacao() {
        try {
            $this->log("🚀 INICIANDO MONITOR DE PREVENÇÃO DE DUPLICATAS");
            $this->log("Data/Hora: " . date('Y-m-d H:i:s'));
            
            $status = true;
            
            // 1. Verificar duplicatas
            $status &= $this->verificarDuplicatas();
            
            // 2. Verificar registros problemáticos
            $problemas = $this->verificarRegistrosProblematicos();
            
            // 3. Verificar índices únicos
            $status &= $this->verificarIndicesUnicos();
            
            // 4. Verificar registros recentes
            $status &= $this->verificarRegistrosRecentes();
            
            // 5. Gerar estatísticas
            $this->gerarEstatisticas();
            
            // 6. Conclusão
            if ($status) {
                $this->log("✅ VERIFICAÇÃO COMPLETA: Sistema está saudável!");
            } else {
                $this->log("⚠️  VERIFICAÇÃO COMPLETA: Problemas detectados!", 'WARN');
            }
            
            $this->log("=== FIM DA VERIFICAÇÃO ===\n");
            
        } catch (Exception $e) {
            $this->log("❌ ERRO CRÍTICO: " . $e->getMessage(), 'ERROR');
            return false;
        }
        
        return $status;
    }
    
    /**
     * Limpar logs antigos (manter apenas 7 dias)
     */
    public function limparLogsAntigos() {
        $this->log("=== LIMPANDO LOGS ANTIGOS ===");
        
        $arquivos = glob('logs/monitor_duplicatas_*.log');
        $limite = strtotime('-7 days');
        $removidos = 0;
        
        foreach ($arquivos as $arquivo) {
            if (filemtime($arquivo) < $limite) {
                unlink($arquivo);
                $removidos++;
            }
        }
        
        $this->log("🗑️  Logs removidos: $removidos");
    }
}

// Executar monitoramento
try {
    $monitor = new MonitorDuplicatas();
    $sucesso = $monitor->executarVerificacao();
    
    // Limpar logs antigos
    $monitor->limparLogsAntigos();
    
    if (!$sucesso) {
        exit(1); // Código de erro para cron jobs
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
?> 