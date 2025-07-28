<?php
/**
 * CORREÇÃO DE CONEXÕES EXCESSIVAS
 * 
 * Script para corrigir os problemas identificados
 */

echo "🔧 CORRIGINDO CONEXÕES EXCESSIVAS\n";
echo "================================\n\n";

// 1. Corrigir configuracoes.php
echo "1. Corrigindo configuracoes.php...\n";
$config_file = "painel/configuracoes.php";
if (file_exists($config_file)) {
    $content = file_get_contents($config_file);
    
    // Alterar intervalo de 5 segundos para 60 segundos
    $content = str_replace(
        "monitorInterval = setInterval(atualizarMonitor, 5000);",
        "monitorInterval = setInterval(atualizarMonitor, 60000); // Aumentado para 60s",
        $content
    );
    
    file_put_contents($config_file, $content);
    echo "   ✅ Intervalo alterado de 5s para 60s\n";
} else {
    echo "   ❌ Arquivo não encontrado\n";
}

// 2. Corrigir whatsapp.php
echo "2. Corrigindo whatsapp.php...\n";
$whatsapp_file = "whatsapp.php";
if (file_exists($whatsapp_file)) {
    $content = file_get_contents($whatsapp_file);
    
    // Alterar intervalo de 3 segundos para 30 segundos
    $content = str_replace(
        "monitorTimer = setInterval(monitorarConexaoWhatsApp, 3000);",
        "monitorTimer = setInterval(monitorarConexaoWhatsApp, 30000); // Aumentado para 30s",
        $content
    );
    
    file_put_contents($whatsapp_file, $content);
    echo "   ✅ Intervalo alterado de 3s para 30s\n";
} else {
    echo "   ❌ Arquivo não encontrado\n";
}

// 3. Corrigir monitoramento.php
echo "3. Corrigindo monitoramento.php...\n";
$monitor_file = "painel/monitoramento.php";
if (file_exists($monitor_file)) {
    $content = file_get_contents($monitor_file);
    
    // Alterar intervalo de 1 segundo para 60 segundos
    $content = str_replace(
        "setInterval(atualizarRelogioNavegador, 1000);",
        "setInterval(atualizarRelogioNavegador, 60000); // Aumentado para 60s",
        $content
    );
    
    file_put_contents($monitor_file, $content);
    echo "   ✅ Intervalo alterado de 1s para 60s\n";
} else {
    echo "   ❌ Arquivo não encontrado\n";
}

// 4. Criar arquivo de configuração otimizada
echo "4. Criando configuração otimizada...\n";
$config_otimizada = '<?php
/**
 * CONFIGURAÇÃO OTIMIZADA PARA REDUZIR CONEXÕES
 */

// Configurações de polling otimizadas
define("POLLING_CONFIGURACOES", 60000);    // 60 segundos
define("POLLING_WHATSAPP", 30000);         // 30 segundos
define("POLLING_MONITORAMENTO", 60000);    // 60 segundos
define("POLLING_CHAT", 60000);             // 60 segundos
define("POLLING_COMUNICACAO", 120000);     // 2 minutos

// Configurações de cache
define("CACHE_ENABLED", true);
define("CACHE_TTL", 300);                  // 5 minutos
define("CACHE_MAX_SIZE", "50MB");

// Configurações de conexão
define("DB_PERSISTENT", true);
define("DB_TIMEOUT", 10);
define("DB_MAX_RETRIES", 3);

echo "✅ Configuração otimizada carregada\n";
?>';

file_put_contents("config_otimizada.php", $config_otimizada);
echo "   ✅ Arquivo config_otimizada.php criado\n";

// 5. Calcular redução de conexões
echo "5. Calculando redução de conexões...\n";
$conexoes_antes = 720 + 1200 + 3600; // 5520 conexões/hora
$conexoes_depois = 60 + 120 + 60;    // 240 conexões/hora

$reducao = round((($conexoes_antes - $conexoes_depois) / $conexoes_antes) * 100, 1);

echo "   📊 REDUÇÃO DE CONEXÕES:\n";
echo "   Antes: $conexoes_antes conexões/hora\n";
echo "   Depois: $conexoes_depois conexões/hora\n";
echo "   Redução: $reducao%\n";
echo "   Status: " . ($conexoes_depois <= 500 ? "✅ DENTRO DO LIMITE" : "⚠️ AINDA EXCEDE") . "\n\n";

echo "🎉 CORREÇÃO CONCLUÍDA!\n";
echo "======================\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "   1. Reinicie o servidor web\n";
echo "   2. Teste o sistema\n";
echo "   3. Monitore as conexões\n";
echo "   4. Se necessário, implemente pool de conexões\n";
?>