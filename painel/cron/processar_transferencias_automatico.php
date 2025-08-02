<?php
/**
 * 🚀 PROCESSADOR AUTOMÁTICO DE TRANSFERÊNCIAS - PIXEL12DIGITAL
 * 
 * Script cron que executa a cada minuto para processar transferências pendentes
 * e verificar bloqueios da Ana
 * 
 * Configurar no crontab:
 * * * * * * /usr/bin/php /caminho/para/painel/cron/processar_transferencias_automatico.php >> /var/log/transferencias.log 2>&1
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../api/executar_transferencias.php';

// Log de execução
function log_execucao($mensagem) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $mensagem\n";
}

log_execucao("🚀 Iniciando processamento automático de transferências");

try {
    // Verificar se há transferências pendentes
    $pendentes_rafael = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael WHERE status = 'pendente'")->fetch_assoc()['total'];
    $pendentes_humanos = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano WHERE status = 'pendente' AND (departamento != 'SUP' OR departamento IS NULL)")->fetch_assoc()['total'];
    $pendentes_suporte = $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano WHERE status = 'pendente' AND departamento = 'SUP'")->fetch_assoc()['total']; // NOVO
    
    log_execucao("📊 Pendentes: $pendentes_rafael para Rafael, $pendentes_humanos para humanos, $pendentes_suporte para suporte"); // ATUALIZADO
    
    if ($pendentes_rafael > 0 || $pendentes_humanos > 0 || $pendentes_suporte > 0) { // ATUALIZADO
        log_execucao("⚡ Processando transferências pendentes...");
        
        // Executar transferências
        $executor = new ExecutorTransferencias($mysqli);
        $resultado = $executor->processarTransferenciasPendentes();
        
        if ($resultado['success']) {
            log_execucao("✅ Transferências processadas com sucesso:");
            log_execucao("   📱 Rafael: {$resultado['transferencias_rafael']} notificações enviadas");
            log_execucao("   👥 Humanos: {$resultado['transferencias_humanas']} transferências realizadas");
            log_execucao("   🔧 Suporte: {$resultado['transferencias_suporte']} transferências realizadas"); // NOVO
            
            // Log detalhado se houver
            if (!empty($resultado['detalhes'])) {
                foreach ($resultado['detalhes'] as $tipo => $detalhes) {
                    foreach ($detalhes as $detalhe) {
                        log_execucao("   ➤ $tipo: Cliente {$detalhe['cliente']} - {$detalhe['acao']}");
                    }
                }
            }
        } else {
            log_execucao("❌ Erro ao processar transferências:");
            foreach ($resultado['erros'] as $erro) {
                log_execucao("   ⚠️ $erro");
            }
        }
    } else {
        log_execucao("😴 Nenhuma transferência pendente");
    }
    
    // Verificar bloqueios antigos (mais de 24h sem atividade)
    log_execucao("🔍 Verificando bloqueios antigos...");
    
    $bloqueios_antigos = $mysqli->query("
        SELECT numero_cliente, data_bloqueio 
        FROM bloqueios_ana 
        WHERE ativo = 1 
        AND data_bloqueio < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);
    
    if (!empty($bloqueios_antigos)) {
        log_execucao("🔓 Encontrados " . count($bloqueios_antigos) . " bloqueios antigos para revisar");
        
        foreach ($bloqueios_antigos as $bloqueio) {
            // Verificar se cliente teve atividade recente no Canal 3001
            $atividade_recente = $mysqli->query("
                SELECT COUNT(*) as total 
                FROM mensagens_comunicacao 
                WHERE numero_whatsapp = '{$bloqueio['numero_cliente']}' 
                AND canal_id = 37 
                AND data_hora > '{$bloqueio['data_bloqueio']}'
            ")->fetch_assoc()['total'];
            
            if ($atividade_recente == 0) {
                // Sem atividade - manter bloqueio mas logar
                log_execucao("   ⏰ Cliente {$bloqueio['numero_cliente']}: bloqueado há " . 
                           date('H:i', strtotime($bloqueio['data_bloqueio'])) . 
                           " sem atividade no Canal 3001");
            } else {
                log_execucao("   ✅ Cliente {$bloqueio['numero_cliente']}: ativo no Canal 3001 ($atividade_recente mensagens)");
            }
        }
    }
    
    // Estatísticas rápidas do dia
    $stats_hoje = [
        'rafael' => $mysqli->query("SELECT COUNT(*) as total FROM transferencias_rafael WHERE DATE(data_transferencia) = CURDATE()")->fetch_assoc()['total'],
        'humanos' => $mysqli->query("SELECT COUNT(*) as total FROM transferencias_humano WHERE DATE(data_transferencia) = CURDATE()")->fetch_assoc()['total'],
        'bloqueios' => $mysqli->query("SELECT COUNT(*) as total FROM bloqueios_ana WHERE ativo = 1")->fetch_assoc()['total']
    ];
    
    log_execucao("📈 Estatísticas do dia: {$stats_hoje['rafael']} → Rafael, {$stats_hoje['humanos']} → Humanos, {$stats_hoje['bloqueios']} bloqueados");
    
    // Atualizar última execução
    $sql = "INSERT INTO sistema_config (chave, valor, data_atualizacao) 
            VALUES ('ultima_execucao_transferencias', NOW(), NOW()) 
            ON DUPLICATE KEY UPDATE valor = NOW(), data_atualizacao = NOW()";
    $mysqli->query($sql);
    
    log_execucao("🏁 Processamento concluído com sucesso");
    
} catch (Exception $e) {
    log_execucao("💥 ERRO CRÍTICO: " . $e->getMessage());
    log_execucao("📍 Arquivo: " . $e->getFile() . " Linha: " . $e->getLine());
    
    // Salvar erro no banco para análise
    $erro_msg = $mysqli->real_escape_string($e->getMessage());
    $mysqli->query("INSERT INTO logs_sistema (tipo, mensagem, data_log) VALUES ('erro_transferencias', '$erro_msg', NOW())");
    
    exit(1);
}

$mysqli->close();
?> 