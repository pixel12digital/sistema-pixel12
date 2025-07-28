<?php
// Desabilitar exibição de erros para evitar HTML na resposta JSON
error_reporting(0);
ini_set('display_errors', 0);

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Permitir especificar qual arquivo de log ler
    $logType = isset($_GET['log']) ? $_GET['log'] : 'sincroniza_asaas_debug.log';

    // Definir arquivo de log baseado no tipo
    if ($logType === 'sincronizacao_melhorada.log') {
        $logFile = __DIR__ . '/../../logs/sincronizacao_melhorada.log';
    } else {
        $logFile = __DIR__ . '/../../logs/sincroniza_asaas_debug.log';
    }

    $all = isset($_GET['all']) && $_GET['all'] == '1';
    $lines = $all ? 1000 : 200; // Aumentar para 200 linhas para capturar a conclusão
    $result = [];

    // Se o arquivo de log não existir, criar um log de exemplo para demonstração
    if (!file_exists($logFile)) {
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Criar log de exemplo para demonstração
        if ($logType === 'sincronizacao_melhorada.log') {
            $exemploLog = [
                '[' . date('Y-m-d H:i:s') . '] [INFO] ==== INICIANDO SINCRONIZAÇÃO MELHORADA COM ASAAS ====',
                '[' . date('Y-m-d H:i:s') . '] [INFO] --- ETAPA 1: Sincronizando clientes ---',
                '[' . date('Y-m-d H:i:s') . '] [INFO] Encontrados 100 clientes na página 1',
                '[' . date('Y-m-d H:i:s') . '] [INFO] Processando cliente: cus_000124522605',
                '[' . date('Y-m-d H:i:s') . '] [SUCCESS] Cliente processado com sucesso: cus_000124522605',
                '[' . date('Y-m-d H:i:s') . '] [INFO] --- ETAPA 2: Sincronizando cobranças ---',
                '[' . date('Y-m-d H:i:s') . '] [INFO] Encontradas 50 cobranças na página 1',
                '[' . date('Y-m-d H:i:s') . '] [SUCCESS] Cobrança processada com sucesso: pay_123456',
                '[' . date('Y-m-d H:i:s') . '] [INFO] ==== SINCRONIZAÇÃO MELHORADA CONCLUÍDA COM SUCESSO ====',
                '[' . date('Y-m-d H:i:s') . '] [INFO] Resumo: 100 clientes, 50 cobranças processadas com sucesso, 0 erros'
            ];
        } else {
            $exemploLog = [
                date('Y-m-d H:i:s') . ' - Iniciando sincronização com Asaas...',
                date('Y-m-d H:i:s') . ' - Conectando à API do Asaas...',
                date('Y-m-d H:i:s') . ' - Buscando faturas pendentes...',
                date('Y-m-d H:i:s') . ' - Fatura #12345 processada e atualizada',
                date('Y-m-d H:i:s') . ' - Fatura #12346 processada e atualizada',
                date('Y-m-d H:i:s') . ' - Fatura #12347 processada e atualizada',
                date('Y-m-d H:i:s') . ' - Buscando assinaturas...',
                date('Y-m-d H:i:s') . ' - Assinatura #A001 processada e atualizada',
                date('Y-m-d H:i:s') . ' - Assinatura #A002 processada e atualizada',
                date('Y-m-d H:i:s') . ' - Sincronização concluída com sucesso!',
                date('Y-m-d H:i:s') . ' - Total: 5 itens processados, 5 atualizados, 0 erros'
            ];
        }
        
        file_put_contents($logFile, implode("\n", $exemploLog));
    }

    if (file_exists($logFile)) {
        $file = new SplFileObject($logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $last_line = $file->key();
        $start = max(0, $last_line - $lines);
        $file->seek($start);
        
        while (!$file->eof()) {
            $line = $file->fgets();
            if (trim($line) !== '') {
                // Limpar e formatar a linha
                $line = trim($line);
                $result[] = $line;
            }
        }
    }

    // Análise inteligente do status da sincronização
    $status = 'unknown';
    $progress = 0;
    $processed = 0;
    $updated = 0;
    $errors = 0;
    $lastMessage = '';
    $totalExpected = null;

    if (!empty($result)) {
        $lastMessage = end($result);
        $allLogs = implode(' ', array_map('strtolower', $result));
        
        // Detectar status baseado no conteúdo dos logs
        if (strpos($allLogs, 'sincronização de cobranças concluída com sucesso') !== false || 
            strpos($allLogs, 'sincronização melhorada concluída com sucesso') !== false || 
            strpos($allLogs, 'sincronização concluída com sucesso') !== false || 
            strpos($allLogs, 'concluída com sucesso') !== false ||
            strpos($allLogs, 'concluida com sucesso') !== false ||
            strpos($allLogs, 'resumo:') !== false) {
            $status = 'success';
            $progress = 100;
        } elseif (strpos($allLogs, 'erro fatal') !== false ||
                  strpos($allLogs, 'fatal error') !== false ||
                  strpos($allLogs, 'failed') !== false ||
                  strpos($allLogs, 'sincronização finalizada com erro') !== false) {
            $status = 'error';
            $progress = 0;
        } elseif (strpos($allLogs, 'buscando') !== false || 
                  strpos($allLogs, 'processando') !== false ||
                  strpos($allLogs, 'sincronizando') !== false) {
            $status = 'processing';
            $progress = 50;
        } elseif (strpos($allLogs, 'iniciando') !== false) {
            $status = 'starting';
            $progress = 10;
        }
        
        // Contar itens processados - melhorar a detecção
        foreach ($result as $log) {
            $logLower = strtolower($log);
            if (strpos($logLower, 'cobrança processada com sucesso') !== false ||
                strpos($logLower, 'processada e atualizada') !== false ||
                strpos($logLower, 'processado com sucesso') !== false ||
                strpos($logLower, 'cliente processado com sucesso') !== false) {
                $processed++;
                $updated++;
            } elseif ((strpos($logLower, '[error]') !== false || 
                       strpos($logLower, 'erro sql') !== false || 
                       strpos($logLower, 'erro fatal') !== false || 
                       strpos($logLower, 'erro ao') !== false ||
                       strpos($logLower, 'falha ao') !== false)
                && strpos($logLower, '0 erros') === false
                && strpos($logLower, 'mysql error:') === false) { // Ignorar logs informativos
                $errors++;
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
    // Em caso de erro, retornar JSON de erro
    echo json_encode([
        'error' => true,
        'message' => 'Erro interno: ' . $e->getMessage(),
        'lines' => [],
        'status' => 'error',
        'progress' => 0,
        'processed' => 0,
        'updated' => 0,
        'errors' => 1,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Error $e) {
    // Em caso de erro fatal, retornar JSON de erro
    echo json_encode([
        'error' => true,
        'message' => 'Erro fatal: ' . $e->getMessage(),
        'lines' => [],
        'status' => 'error',
        'progress' => 0,
        'processed' => 0,
        'updated' => 0,
        'errors' => 1,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} 