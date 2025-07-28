<?php
/**
 * INVESTIGAR CONSUMO DE CONEXÕES
 * 
 * Script para identificar o que está consumindo conexões em excesso
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 INVESTIGANDO CONSUMO DE CONEXÕES\n";
echo "===================================\n\n";

// 1. Verificar logs de webhook recentes
echo "1️⃣ LOGS DE WEBHOOK RECENTES\n";
echo "============================\n\n";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $total_logs = count($logs);
    echo "📄 Total de logs hoje: $total_logs\n";
    
    if ($total_logs > 0) {
        echo "📊 Últimas 10 requisições:\n";
        $ultimas_logs = array_slice($logs, -10);
        foreach ($ultimas_logs as $log) {
            $hora = substr($log, 0, 19);
            echo "   [$hora] " . substr($log, 20, 50) . "...\n";
        }
    }
} else {
    echo "❌ Arquivo de log não encontrado\n";
}

// 2. Verificar mensagens salvas hoje
echo "\n2️⃣ MENSAGENS SALVAS HOJE\n";
echo "=========================\n\n";

$sql_hoje = "SELECT COUNT(*) as total, 
                    COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
                    COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas,
                    COUNT(DISTINCT numero_whatsapp) as numeros_unicos
             FROM mensagens_comunicacao 
             WHERE DATE(data_hora) = CURDATE()";

$result_hoje = $mysqli->query($sql_hoje);
if ($result_hoje) {
    $stats_hoje = $result_hoje->fetch_assoc();
    echo "📊 Estatísticas de hoje:\n";
    echo "   Total mensagens: {$stats_hoje['total']}\n";
    echo "   Recebidas: {$stats_hoje['recebidas']}\n";
    echo "   Enviadas: {$stats_hoje['enviadas']}\n";
    echo "   Números únicos: {$stats_hoje['numeros_unicos']}\n";
} else {
    echo "❌ Erro ao consultar mensagens de hoje\n";
}

// 3. Verificar requisições por hora
echo "\n3️⃣ REQUISIÇÕES POR HORA\n";
echo "=========================\n\n";

$sql_hora = "SELECT 
                HOUR(data_hora) as hora,
                COUNT(*) as total_requisicoes,
                COUNT(DISTINCT numero_whatsapp) as numeros_unicos
             FROM mensagens_comunicacao 
             WHERE DATE(data_hora) = CURDATE()
             GROUP BY HOUR(data_hora)
             ORDER BY total_requisicoes DESC
             LIMIT 5";

$result_hora = $mysqli->query($sql_hora);
if ($result_hora && $result_hora->num_rows > 0) {
    echo "📊 Top 5 horas com mais requisições:\n";
    while ($hora = $result_hora->fetch_assoc()) {
        echo "   {$hora['hora']}h: {$hora['total_requisicoes']} requisições ({$hora['numeros_unicos']} números únicos)\n";
    }
} else {
    echo "❌ Nenhuma requisição encontrada hoje\n";
}

// 4. Verificar se há loops ou requisições repetitivas
echo "\n4️⃣ VERIFICANDO POSSÍVEIS LOOPS\n";
echo "================================\n\n";

// Verificar mensagens duplicadas no mesmo minuto
$sql_duplicadas = "SELECT 
                    numero_whatsapp,
                    DATE_FORMAT(data_hora, '%Y-%m-%d %H:%i') as minuto,
                    COUNT(*) as total_mensagens,
                    GROUP_CONCAT(mensagem ORDER BY data_hora) as mensagens
                   FROM mensagens_comunicacao 
                   WHERE DATE(data_hora) = CURDATE()
                   GROUP BY numero_whatsapp, DATE_FORMAT(data_hora, '%Y-%m-%d %H:%i')
                   HAVING COUNT(*) > 3
                   ORDER BY total_mensagens DESC
                   LIMIT 5";

$result_duplicadas = $mysqli->query($sql_duplicadas);
if ($result_duplicadas && $result_duplicadas->num_rows > 0) {
    echo "⚠️ POSSÍVEIS LOOPS DETECTADOS:\n";
    while ($duplicada = $result_duplicadas->fetch_assoc()) {
        echo "   📱 {$duplicada['numero_whatsapp']} em {$duplicada['minuto']}: {$duplicada['total_mensagens']} mensagens\n";
        echo "      Mensagens: " . substr($duplicada['mensagens'], 0, 100) . "...\n";
        echo "      " . str_repeat("-", 40) . "\n";
    }
} else {
    echo "✅ Nenhum loop detectado\n";
}

// 5. Verificar scripts que fazem muitas consultas
echo "\n5️⃣ VERIFICANDO SCRIPTS ATIVOS\n";
echo "===============================\n\n";

// Verificar se há processos PHP ativos
$processos_php = [];
if (function_exists('exec')) {
    $output = [];
    exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV', $output);
    if (count($output) > 1) {
        echo "🔄 Processos PHP ativos:\n";
        foreach ($output as $linha) {
            if (strpos($linha, 'php.exe') !== false) {
                echo "   $linha\n";
            }
        }
    } else {
        echo "✅ Nenhum processo PHP ativo detectado\n";
    }
} else {
    echo "ℹ️ Não é possível verificar processos (exec desabilitado)\n";
}

// 6. Verificar arquivos de cache e logs
echo "\n6️⃣ VERIFICANDO ARQUIVOS DE CACHE\n";
echo "==================================\n\n";

$cache_files = glob('cache/*');
$log_files = glob('logs/*');

echo "📁 Arquivos de cache: " . count($cache_files) . "\n";
echo "📄 Arquivos de log: " . count($log_files) . "\n";

if (count($log_files) > 0) {
    echo "📊 Tamanho dos logs:\n";
    foreach ($log_files as $log_file) {
        $size = filesize($log_file);
        $size_mb = round($size / 1024 / 1024, 2);
        echo "   " . basename($log_file) . ": {$size_mb} MB\n";
    }
}

// 7. Recomendações
echo "\n7️⃣ RECOMENDAÇÕES\n";
echo "=================\n\n";

echo "🔧 Para reduzir o consumo de conexões:\n\n";

echo "1. **Verificar webhook:**\n";
echo "   - O webhook pode estar sendo chamado em loop\n";
echo "   - Verificar se há retry automático configurado\n";
echo "   - Verificar se o WhatsApp está enviando mensagens duplicadas\n\n";

echo "2. **Otimizar consultas:**\n";
echo "   - Usar conexões persistentes\n";
echo "   - Implementar pool de conexões\n";
echo "   - Reduzir consultas desnecessárias\n\n";

echo "3. **Monitorar logs:**\n";
echo "   - Verificar logs do Apache/Nginx\n";
echo "   - Monitorar logs do PHP\n";
echo "   - Verificar logs do WhatsApp\n\n";

echo "4. **Limpar cache:**\n";
echo "   - Limpar arquivos de cache antigos\n";
echo "   - Rotacionar logs grandes\n";
echo "   - Verificar se há scripts rodando em background\n\n";

echo "5. **Configuração do servidor:**\n";
echo "   - Aumentar limite de conexões (se possível)\n";
echo "   - Configurar timeout de conexões\n";
echo "   - Implementar rate limiting\n\n";

echo "✅ Investigação concluída!\n";
?> 