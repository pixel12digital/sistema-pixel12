<?php
// Desabilitar exibição de erros para evitar HTML na resposta JSON
error_reporting(0);
ini_set('display_errors', 0);

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Permitir especificar qual arquivo de log ler
    $logType = isset($_GET['log']) ? $_GET['log'] : 'sincronizacao_corrigida.log';

    // Definir arquivo de log baseado no tipo
    if ($logType === 'sincronizacao_melhorada.log') {
        $logFile = __DIR__ . '/../../logs/sincronizacao_melhorada.log';
    } elseif ($logType === 'sincronizacao_protegida.log') {
        $logFile = __DIR__ . '/../../logs/sincronizacao_protegida.log';
    } else {
        $logFile = __DIR__ . '/../../logs/sincronizacao_corrigida.log';
    }

    $all = isset($_GET['all']) && $_GET['all'] == '1';
    $lines = $all ? 1000 : 200; // Aumentar para 200 linhas para capturar a conclusão
    $result = [];

    // Verificar se o arquivo de log existe
    if (!file_exists($logFile)) {
        // Se não existir, retornar status inicial em vez de criar logs falsos
        echo json_encode([
            'lines' => [],
            'status' => 'not_started',
            'progress' => 0,
            'processed' => 0,
            'updated' => 0,
            'errors' => 0,
            'last_message' => 'Sincronização não iniciada',
            'total_expected' => null,
            'timestamp' => date('Y-m-d H:i:s'),
            'file_exists' => false,
            'file_size' => 0,
            'total_lines' => 0,
            'log_type' => $logType,
            'log_file' => basename($logFile),
            'message' => 'Arquivo de log não encontrado. A sincronização ainda não foi executada.'
        ]);
        exit;
    }

    // Ler as últimas linhas do arquivo de log
    $file = new SplFileObject($logFile);
    $file->seek(PHP_INT_MAX);
    $totalLines = $file->key();
    
    $startLine = max(0, $totalLines - $lines);
    $file->seek($startLine);
    
    while (!$file->eof()) {
        $line = trim($file->current());
        if (!empty($line)) {
            $result[] = $line;
        }
        $file->next();
    }
    
    $file = null; // Fechar arquivo

    // Analisar status da sincronização
    $status = 'not_started';
    $progress = 0;
    $processed = 0;
    $updated = 0;
    $errors = 0;
    $lastMessage = '';
    $totalExpected = null;
    $isProcessing = false;

    if (!empty($result)) {
        $lastMessage = end($result);
        
        // Detectar status baseado nas mensagens
        foreach ($result as $log) {
            $logLower = strtolower($log);
            
            // Detectar início
            if (strpos($logLower, 'iniciando sincronização corrigida') !== false) {
                $status = 'starting';
                $isProcessing = true;
            }
            
            // Detectar processamento
            if (strpos($logLower, 'sincronizando clientes') !== false || 
                strpos($logLower, 'sincronizando cobranças') !== false ||
                strpos($logLower, 'processando cobrança:') !== false) {
                $status = 'processing';
                $isProcessing = true;
            }
            
            // Detectar sucesso
            if (strpos($logLower, 'sincronização corrigida concluída com sucesso') !== false ||
                strpos($logLower, 'sincronizacao corrigida concluida com sucesso') !== false ||
                stripos($log, 'SINCRONIZAÇÃO CORRIGIDA CONCLUÍDA COM SUCESSO') !== false) {
                $status = 'success';
                $progress = 100;
                $isProcessing = false;
            }
            
            // Detectar erro
            if (strpos($logLower, 'erro fatal') !== false || 
                strpos($logLower, 'sincronização finalizada com erro') !== false) {
                $status = 'error';
                $isProcessing = false;
            }
            
            // Contar processados
            if (strpos($logLower, 'processando cobrança:') !== false) {
                $processed++;
            }
            
            // Contar atualizados
            if (strpos($logLower, 'cliente atualizado') !== false || 
                strpos($logLower, 'cliente inserido') !== false ||
                strpos($logLower, 'cobrança') !== false && strpos($logLower, 'sucesso') !== false) {
                $updated++;
            }
            
            // Contar erros (excluir logs informativos)
            if (strpos($logLower, '[error]') !== false || 
                strpos($logLower, '[fatal]') !== false ||
                (strpos($logLower, 'erro') !== false && strpos($logLower, '0 erros') === false
                && strpos($logLower, 'mysql error:') === false)) { // Ignorar logs informativos
                $errors++;
            }
        }

        // Verificar se a sincronização foi concluída com sucesso (verificação adicional)
        if ($status !== 'success') {
            foreach ($result as $log) {
                if (stripos($log, 'SINCRONIZAÇÃO CORRIGIDA CONCLUÍDA COM SUCESSO') !== false ||
                    stripos($log, 'sincronização corrigida concluída com sucesso') !== false ||
                    stripos($log, 'sincronizacao corrigida concluida com sucesso') !== false) {
                    $status = 'success';
                    $progress = 100;
                    $isProcessing = false;
                    break;
                }
            }
        }

        // Extrair o total esperado de cobranças
        foreach ($result as $log) {
            if (strpos($log, 'TOTAL_COBRANCAS_ESPERADO:') !== false) {
                $partes = explode('TOTAL_COBRANCAS_ESPERADO:', $log);
                if (isset($partes[1])) {
                    $totalExpected = (int)trim($partes[1]);
                }
            }
        }
        
        // Extrair números do resumo final se a sincronização foi concluída
        if ($status === 'success') {
            $clientesProcessados = 0;
            $clientesSincronizados = 0;
            $cobrancasProcessadas = 0;
            $cobrancasSincronizadas = 0;
            $clientesComErro = 0;
            $cobrancasComErro = 0;
            
            foreach ($result as $log) {
                $logLower = strtolower($log);
                
                // Extrair número de clientes sincronizados
                if (strpos($logLower, 'clientes sincronizados:') !== false) {
                    preg_match('/clientes sincronizados:\s*(\d+)/', $logLower, $matches);
                    if (isset($matches[1])) {
                        $clientesSincronizados = (int)$matches[1];
                    }
                }
                
                // Extrair número de cobranças sincronizadas
                if (strpos($logLower, 'cobranças sincronizadas:') !== false) {
                    preg_match('/cobranças sincronizadas:\s*(\d+)/', $logLower, $matches);
                    if (isset($matches[1])) {
                        $cobrancasSincronizadas = (int)$matches[1];
                    }
                }
                
                // Extrair número de clientes processados
                if (strpos($logLower, 'clientes processados:') !== false) {
                    preg_match('/clientes processados:\s*(\d+)/', $logLower, $matches);
                    if (isset($matches[1])) {
                        $clientesProcessados = (int)$matches[1];
                    }
                }
                
                // Extrair número de cobranças processadas
                if (strpos($logLower, 'cobranças processadas:') !== false) {
                    preg_match('/cobranças processadas:\s*(\d+)/', $logLower, $matches);
                    if (isset($matches[1])) {
                        $cobrancasProcessadas = (int)$matches[1];
                    }
                }
                
                // Extrair número de erros
                if (strpos($logLower, 'clientes com erro:') !== false) {
                    preg_match('/clientes com erro:\s*(\d+)/', $logLower, $matches);
                    if (isset($matches[1])) {
                        $clientesComErro = (int)$matches[1];
                    }
                }
                
                if (strpos($logLower, 'cobranças com erro:') !== false) {
                    preg_match('/cobranças com erro:\s*(\d+)/', $logLower, $matches);
                    if (isset($matches[1])) {
                        $cobrancasComErro = (int)$matches[1];
                    }
                }
            }
            
            // Usar os números corretos do resumo final
            $processed = $clientesProcessados + $cobrancasProcessadas;
            $updated = $clientesSincronizados + $cobrancasSincronizadas;
            $errors = $clientesComErro + $cobrancasComErro;
        }
        
        // Calcular progresso real se estiver processando
        if ($isProcessing && $totalExpected && $totalExpected > 0) {
            $progress = min(95, round(($processed / $totalExpected) * 100));
        }
        
        // Se encontrou o total esperado e o status é success, usar o total real
        if ($totalExpected && $status === 'success') {
            $processed = $totalExpected;
            $updated = $totalExpected;
        }
    }

    // Adicionar informações extras sobre o status
    $statusInfo = [
        'lines' => $result,
        'status' => $status,
        'progress' => $progress,
        'processed' => $processed,
        'updated' => $updated,
        'errors' => $errors,
        'last_message' => $lastMessage,
        'total_expected' => $totalExpected,
        'timestamp' => date('Y-m-d H:i:s'),
        'file_exists' => file_exists($logFile),
        'file_size' => file_exists($logFile) ? filesize($logFile) : 0,
        'total_lines' => count($result),
        'log_type' => $logType,
        'log_file' => basename($logFile)
    ];

    echo json_encode($statusInfo);

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Erro ao ler logs: ' . $e->getMessage(),
        'lines' => [],
        'status' => 'error',
        'progress' => 0,
        'processed' => 0,
        'updated' => 0,
        'errors' => 1
    ]);
}
?> 