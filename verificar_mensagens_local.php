<?php
/**
 * VERIFICAÇÃO LOCAL DE MENSAGENS PERDIDAS
 * 
 * Script que funciona localmente para diagnosticar o problema
 */

echo "🔍 VERIFICAÇÃO LOCAL DE MENSAGENS PERDIDAS\n";
echo "==========================================\n\n";

echo "📊 1. VERIFICANDO LOGS LOCAIS\n";
echo "-------------------------------\n";

$log_file = 'logs/webhook_whatsapp_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    echo "✅ Log encontrado: $log_file\n";
    echo "📏 Tamanho: " . formatBytes(filesize($log_file)) . "\n\n";
    
    $logs = file($log_file);
    $total_logs = count($logs);
    echo "📊 Total de linhas no log: $total_logs\n\n";
    
    // Procurar por mensagens específicas
    $mensagens_encontradas = [];
    $mensagens_perdidas = [];
    
    foreach ($logs as $linha) {
        $linha = trim($linha);
        if (strpos($linha, 'boa tarde') !== false) {
            $mensagens_encontradas[] = $linha;
        }
        if (strpos($linha, 'oi') !== false && strpos($linha, 'oie') === false) {
            $mensagens_encontradas[] = $linha;
        }
        if (strpos($linha, 'oie') !== false) {
            $mensagens_encontradas[] = $linha;
        }
    }
    
    if (count($mensagens_encontradas) > 0) {
        echo "🔍 Mensagens encontradas nos logs:\n";
        foreach ($mensagens_encontradas as $msg) {
            echo "   • $msg\n";
        }
    } else {
        echo "❌ Nenhuma mensagem encontrada nos logs\n";
    }
    
    // Verificar últimas 20 linhas
    echo "\n📝 Últimas 20 linhas do log:\n";
    $ultimas_linhas = array_slice($logs, -20);
    foreach ($ultimas_linhas as $linha) {
        echo "   " . trim($linha) . "\n";
    }
    
} else {
    echo "❌ Arquivo de log não encontrado: $log_file\n";
    echo "💡 Dica: Execute este script no servidor onde está o sistema\n";
}

echo "\n🌐 2. TESTANDO CONECTIVIDADE\n";
echo "-----------------------------\n";

$webhook_url = 'https://pixel12digital.com.br/app/api/webhook_whatsapp.php';

// Teste de conectividade
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erro na conexão: $error\n";
} else {
    echo "✅ Webhook responde: HTTP $http_code\n";
}

echo "\n📡 3. VERIFICANDO CONFIGURAÇÃO WHATSAPP\n";
echo "----------------------------------------\n";

echo "🔧 Para resolver o problema de mensagens perdidas:\n\n";

echo "1. 📱 Acesse o WhatsApp Business API\n";
echo "2. 🔗 Verifique se o webhook está configurado corretamente:\n";
echo "   URL: https://pixel12digital.com.br/app/api/webhook_whatsapp.php\n";
echo "3. ✅ Confirme se o webhook está ativo\n";
echo "4. 🔄 Teste a conectividade\n\n";

echo "📊 4. USANDO O SISTEMA DE AÇÕES RÁPIDAS\n";
echo "----------------------------------------\n";

echo "💡 Acesse o painel e use as ações rápidas:\n";
echo "1. 🧪 Testar Webhook\n";
echo "2. 📊 Verificar Status\n";
echo "3. 📡 Monitor Tempo Real\n\n";

echo "🎯 5. DIAGNÓSTICO BASEADO NAS IMAGENS\n";
echo "-------------------------------------\n";

echo "✅ CONFIRMADO:\n";
echo "• Mensagem 'oie' (16:06) ESTÁ no chat do sistema\n";
echo "• Mensagem 'Não recebi minha fatura' (16:05) ESTÁ no chat\n";
echo "• Robô WhatsApp está 'Conectado'\n\n";

echo "❌ PROBLEMA IDENTIFICADO:\n";
echo "• Mensagem 'boa tarde' (17:03) NÃO está no chat\n";
echo "• Mensagem 'boa tarde' (17:44) NÃO está no chat\n";
echo "• Mensagem 'oi' (17:42) NÃO está no chat\n\n";

echo "🔍 CAUSA PROVÁVEL:\n";
echo "• Problema INTERMITENTE de conectividade\n";
echo "• WhatsApp não está enviando TODAS as mensagens para o webhook\n";
echo "• Possível rate limiting ou timeout\n\n";

echo "🛠️ SOLUÇÕES RECOMENDADAS:\n";
echo "1. 📡 Verificar configuração do webhook no WhatsApp Business API\n";
echo "2. 🔄 Reiniciar a conexão do WhatsApp\n";
echo "3. 📊 Monitorar logs em tempo real\n";
echo "4. ⚡ Usar o sistema de ações rápidas para diagnóstico\n";
echo "5. 🔍 Verificar se há problemas de rede ou servidor\n\n";

echo "✅ VERIFICAÇÃO CONCLUÍDA\n";
echo "========================\n";

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?> 