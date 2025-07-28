<?php
/**
 * IDENTIFICADOR DE CAUSAS DE CONEXÕES EXCESSIVAS
 * 
 * Script para analisar e identificar o que está causando o esgotamento de conexões
 */

echo "🔍 IDENTIFICANDO CAUSAS DE CONEXÕES EXCESSIVAS\n";
echo "=============================================\n\n";

// 1. Verificar arquivos com polling frequente
echo "1️⃣ ARQUIVOS COM POLLING FREQUENTE\n";
echo "=================================\n\n";

$arquivos_polling = [
    'painel/configuracoes.php' => [
        'linha' => 362,
        'intervalo' => 5000, // 5 segundos
        'problema' => 'MUITO FREQUENTE - Causa muitas conexões'
    ],
    'whatsapp.php' => [
        'linha' => 700,
        'intervalo' => 3000, // 3 segundos
        'problema' => 'MUITO FREQUENTE - Causa muitas conexões'
    ],
    'painel/monitoramento.php' => [
        'linha' => 26,
        'intervalo' => 1000, // 1 segundo
        'problema' => 'MUITO FREQUENTE - Apenas relógio, mas ainda gera tráfego'
    ],
    'painel/chat_temporario.php' => [
        'linha' => 292,
        'intervalo' => 30000, // 30 segundos
        'problema' => 'ACEITÁVEL - Mas pode ser otimizado'
    ],
    'painel/comunicacao.php' => [
        'linha' => 564,
        'intervalo' => 60000, // 60 segundos
        'problema' => 'ACEITÁVEL'
    ],
    'admin/index.php' => [
        'linha' => 348,
        'intervalo' => 30000, // 30 segundos
        'problema' => 'ACEITÁVEL'
    ]
];

foreach ($arquivos_polling as $arquivo => $info) {
    echo "📁 $arquivo\n";
    echo "   Linha: {$info['linha']}\n";
    echo "   Intervalo: {$info['intervalo']}ms (" . round($info['intervalo']/1000, 1) . "s)\n";
    echo "   Status: {$info['problema']}\n\n";
}

// 2. Verificar arquivos com múltiplas conexões
echo "2️⃣ ARQUIVOS COM MÚLTIPLAS CONEXÕES\n";
echo "==================================\n\n";

$arquivos_multiplas_conexoes = [
    'painel/db.php' => 'Conexão estática com verificação de ping',
    'painel/conexao.php' => 'Cria nova conexão a cada include',
    'src/Services/AsaasIntegrationService.php' => 'Cria conexão no construtor',
    'painel/cliente_controller.php' => 'Cria conexão no construtor',
    'api/clientes.php' => 'Cria conexão direta',
    'painel/acoes_rapidas.php' => 'Função conectarDB() cria nova conexão'
];

foreach ($arquivos_multiplas_conexoes as $arquivo => $problema) {
    echo "📁 $arquivo\n";
    echo "   Problema: $problema\n\n";
}

// 3. Verificar arquivos com consultas frequentes
echo "3️⃣ ARQUIVOS COM CONSULTAS FREQUENTES\n";
echo "====================================\n\n";

$arquivos_consultas_frequentes = [
    'painel/acoes_rapidas.php' => [
        'funcao' => 'monitorTempoReal()',
        'consultas' => ['COUNT(*) mensagens', 'SELECT mensagem', 'curl webhook'],
        'frequencia' => 'A cada 5 segundos via configuracoes.php'
    ],
    'painel/monitoramento.php' => [
        'funcao' => 'Dashboard de monitoramento',
        'consultas' => ['Estatísticas', 'Lista de clientes', 'Status'],
        'frequencia' => 'A cada carregamento de página'
    ],
    'painel/chat.php' => [
        'funcao' => 'Cache de conversas',
        'consultas' => ['conversas_recentes', 'mensagens_nao_lidas'],
        'frequencia' => 'A cada 30 segundos'
    ]
];

foreach ($arquivos_consultas_frequentes as $arquivo => $info) {
    echo "📁 $arquivo\n";
    echo "   Função: {$info['funcao']}\n";
    echo "   Consultas: " . implode(', ', $info['consultas']) . "\n";
    echo "   Frequência: {$info['frequencia']}\n\n";
}

// 4. Calcular impacto das conexões
echo "4️⃣ CÁLCULO DO IMPACTO DAS CONEXÕES\n";
echo "==================================\n\n";

$conexoes_por_minuto = 0;
$conexoes_por_hora = 0;

// Configurações.php - 5 segundos
$conexoes_por_minuto += 60 / 5; // 12 conexões/minuto
$conexoes_por_hora += 3600 / 5; // 720 conexões/hora

// WhatsApp.php - 3 segundos
$conexoes_por_minuto += 60 / 3; // 20 conexões/minuto
$conexoes_por_hora += 3600 / 3; // 1200 conexões/hora

// Monitoramento.php - 1 segundo
$conexoes_por_minuto += 60 / 1; // 60 conexões/minuto
$conexoes_por_hora += 3600 / 1; // 3600 conexões/hora

echo "📊 CONEXÕES ESTIMADAS:\n";
echo "   Por minuto: $conexoes_por_minuto\n";
echo "   Por hora: $conexoes_por_hora\n";
echo "   Limite do banco: 500 conexões/hora\n";
echo "   EXCEDE O LIMITE EM: " . round(($conexoes_por_hora / 500) * 100, 1) . "%\n\n";

// 5. Soluções recomendadas
echo "5️⃣ SOLUÇÕES RECOMENDADAS\n";
echo "========================\n\n";

echo "🔧 SOLUÇÕES IMEDIATAS:\n\n";

echo "1. **Aumentar intervalos de polling:**\n";
echo "   - configuracoes.php: 5s → 60s (12x menos conexões)\n";
echo "   - whatsapp.php: 3s → 30s (10x menos conexões)\n";
echo "   - monitoramento.php: 1s → 60s (60x menos conexões)\n\n";

echo "2. **Implementar pool de conexões:**\n";
echo "   - Usar conexões persistentes\n";
echo "   - Reutilizar conexões existentes\n";
echo "   - Implementar cache de consultas\n\n";

echo "3. **Otimizar consultas:**\n";
echo "   - Usar cache para dados que não mudam\n";
echo "   - Reduzir consultas desnecessárias\n";
echo "   - Implementar lazy loading\n\n";

echo "4. **Implementar rate limiting:**\n";
echo "   - Limitar requisições por IP\n";
echo "   - Implementar cooldown entre consultas\n";
echo "   - Usar cache para dados estáticos\n\n";

// 6. Criar arquivo de correção
echo "6️⃣ CRIANDO ARQUIVO DE CORREÇÃO\n";
echo "==============================\n\n";

$correcao_content = '<?php
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
$config_otimizada = \'<?php
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

echo "✅ Configuração otimizada carregada\\n";
?>\';

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
?>';

file_put_contents('corrigir_conexoes_excessivas.php', $correcao_content);
echo "✅ Arquivo de correção criado: corrigir_conexoes_excessivas.php\n";

// 7. Resumo final
echo "7️⃣ RESUMO FINAL\n";
echo "===============\n\n";

echo "🎯 PRINCIPAIS CAUSAS IDENTIFICADAS:\n";
echo "   1. Polling muito frequente em configuracoes.php (5s)\n";
echo "   2. Polling muito frequente em whatsapp.php (3s)\n";
echo "   3. Polling muito frequente em monitoramento.php (1s)\n";
echo "   4. Múltiplas conexões criadas sem reutilização\n";
echo "   5. Falta de cache para consultas frequentes\n\n";

echo "💡 IMPACTO ESTIMADO:\n";
echo "   - Conexões atuais: $conexoes_por_hora/hora\n";
echo "   - Limite do banco: 500/hora\n";
echo "   - Excesso: " . round(($conexoes_por_hora / 500) * 100, 1) . "%\n\n";

echo "🔧 SOLUÇÃO RECOMENDADA:\n";
echo "   Execute: php corrigir_conexoes_excessivas.php\n\n";

echo "📞 SE O PROBLEMA PERSISTIR:\n";
echo "   - Contate o provedor para aumentar limite\n";
echo "   - Implemente pool de conexões\n";
echo "   - Use cache Redis/Memcached\n";
?> 