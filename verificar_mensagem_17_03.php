<?php
/**
 * VERIFICAR MENSAGEM "BOA TARDE" DE 17:03
 * 
 * Script para verificar se a mensagem específica foi recebida
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICANDO MENSAGEM 'BOA TARDE' DE 17:03\n";
echo "=============================================\n\n";

// Verificar mensagens recebidas hoje
$sql = "SELECT mc.*, c.nome as cliente_nome, c.celular, cc.nome_exibicao as canal_nome
        FROM mensagens_comunicacao mc
        LEFT JOIN clientes c ON mc.cliente_id = c.id
        LEFT JOIN canais_comunicacao cc ON mc.canal_id = cc.id
        WHERE mc.data_hora >= '2025-07-28 17:00:00'
        AND mc.data_hora <= '2025-07-28 17:10:00'
        ORDER BY mc.data_hora DESC";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "📥 Mensagens recebidas entre 17:00 e 17:10:\n\n";
    while ($msg = $result->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $canal = $msg['canal_nome'] ?: 'Canal ' . $msg['canal_id'];
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente ($canal)\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 40) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada entre 17:00 e 17:10\n";
}

echo "\n🔍 VERIFICANDO MENSAGENS COM 'BOA TARDE':\n";
echo "==========================================\n\n";

// Buscar especificamente por "boa tarde"
$sql_boa_tarde = "SELECT mc.*, c.nome as cliente_nome, c.celular
                  FROM mensagens_comunicacao mc
                  LEFT JOIN clientes c ON mc.cliente_id = c.id
                  WHERE LOWER(mc.mensagem) LIKE '%boa tarde%'
                  AND mc.data_hora >= '2025-07-28 00:00:00'
                  ORDER BY mc.data_hora DESC";

$result_boa_tarde = $mysqli->query($sql_boa_tarde);

if ($result_boa_tarde && $result_boa_tarde->num_rows > 0) {
    echo "📥 Mensagens com 'boa tarde' encontradas:\n\n";
    while ($msg = $result_boa_tarde->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente\n";
        echo "      Mensagem: {$msg['mensagem']}\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem com 'boa tarde' encontrada hoje\n";
}

echo "\n🔍 VERIFICANDO ÚLTIMAS MENSAGENS DO CHARLES:\n";
echo "=============================================\n\n";

// Buscar últimas mensagens do Charles (554796164699)
$sql_charles = "SELECT mc.*, c.nome as cliente_nome, c.celular
                FROM mensagens_comunicacao mc
                LEFT JOIN clientes c ON mc.cliente_id = c.id
                WHERE mc.numero_whatsapp = '554796164699'
                OR mc.numero_whatsapp = '4796164699'
                OR c.celular = '554796164699'
                OR c.celular = '4796164699'
                ORDER BY mc.data_hora DESC
                LIMIT 10";

$result_charles = $mysqli->query($sql_charles);

if ($result_charles && $result_charles->num_rows > 0) {
    echo "📥 Últimas mensagens do Charles:\n\n";
    while ($msg = $result_charles->fetch_assoc()) {
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        $cliente = $msg['cliente_nome'] ?: 'Sem cliente';
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "   $direcao [$hora] $cliente\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 100) . "...\n";
        echo "      Número WhatsApp: " . ($msg['numero_whatsapp'] ?: 'N/A') . "\n";
        echo "      " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem do Charles encontrada\n";
}

echo "\n🔍 VERIFICANDO LOGS DE WEBHOOK:\n";
echo "================================\n\n";

// Verificar logs de webhook
$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $logs = file($log_file);
    $total_logs = count($logs);
    echo "📄 Total de logs hoje: $total_logs\n";
    
    if ($total_logs > 0) {
        echo "📊 Últimas 20 requisições:\n";
        $ultimas_logs = array_slice($logs, -20);
        foreach ($ultimas_logs as $log) {
            $hora = substr($log, 0, 19);
            $conteudo = substr($log, 20);
            
            // Verificar se contém "boa tarde" ou "17:03"
            if (strpos(strtolower($conteudo), 'boa tarde') !== false || strpos($hora, '17:03') !== false) {
                echo "   ⭐ [$hora] " . substr($conteudo, 0, 100) . "...\n";
            } else {
                echo "   [$hora] " . substr($conteudo, 0, 50) . "...\n";
            }
        }
    }
} else {
    echo "❌ Arquivo de log não encontrado\n";
}

echo "\n🔍 VERIFICANDO STATUS DO WEBHOOK:\n";
echo "==================================\n\n";

// Verificar se o webhook está funcionando
$webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';
echo "🔗 URL do webhook: $webhook_url\n";

// Testar se o webhook responde
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Status do webhook: HTTP $http_code\n";

if ($http_code === 200) {
    echo "✅ Webhook está respondendo\n";
} else {
    echo "❌ Webhook não está respondendo corretamente\n";
}

echo "\n🔍 VERIFICANDO CONFIGURAÇÃO DO WHATSAPP:\n";
echo "=========================================\n\n";

// Verificar configurações do WhatsApp
echo "📱 Configurações do WhatsApp:\n";
echo "   URL Robot: " . (defined('WHATSAPP_ROBOT_URL') ? WHATSAPP_ROBOT_URL : 'Não definida') . "\n";
echo "   Timeout: " . (defined('WHATSAPP_TIMEOUT') ? WHATSAPP_TIMEOUT : 'Não definido') . "\n";

echo "\n🔍 VERIFICANDO CANAL FINANCEIRO:\n";
echo "=================================\n\n";

// Verificar canal financeiro
$sql_canal = "SELECT * FROM canais_comunicacao WHERE tipo = 'whatsapp' AND (id = 36 OR nome_exibicao LIKE '%financeiro%')";
$result_canal = $mysqli->query($sql_canal);

if ($result_canal && $result_canal->num_rows > 0) {
    while ($canal = $result_canal->fetch_assoc()) {
        echo "📡 Canal: {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "   Status: {$canal['status']}\n";
        echo "   Data conexão: {$canal['data_conexao']}\n";
        echo "   " . str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Canal financeiro não encontrado\n";
}

echo "\n✅ Verificação concluída!\n";

// Recomendações
echo "\n💡 RECOMENDAÇÕES:\n";
echo "=================\n\n";

echo "1. **Se a mensagem não foi recebida:**\n";
echo "   - Verificar se o webhook está configurado corretamente no WhatsApp\n";
echo "   - Verificar se o servidor está acessível\n";
echo "   - Verificar logs de erro do servidor\n\n";

echo "2. **Se a mensagem foi recebida mas não aparece no chat:**\n";
echo "   - Verificar se o campo numero_whatsapp está sendo salvo\n";
echo "   - Verificar se há problemas de cache na interface\n";
echo "   - Verificar se há filtros aplicados no chat\n\n";

echo "3. **Para testar o webhook:**\n";
echo "   - Enviar uma nova mensagem de teste\n";
echo "   - Verificar logs em tempo real\n";
echo "   - Monitorar o banco de dados\n\n";
?> 