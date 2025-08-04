<?php
/**
 * Configurar polling automático para WhatsApp
 */

echo "=== CONFIGURANDO POLLING AUTOMÁTICO WHATSAPP ===\n\n";

// 1. Testar polling manual primeiro
echo "1. TESTE MANUAL DO POLLING:\n";
echo "Executando polling uma vez para testar...\n\n";

ob_start();
include __DIR__ . '/polling_mensagens_whatsapp.php';
$output = ob_get_clean();

echo $output;
echo "\n" . str_repeat("-", 60) . "\n\n";

// 2. Configurar execução automática (Windows Task Scheduler)
echo "2. CONFIGURAÇÃO AUTOMÁTICA:\n";

$script_path = str_replace('/', '\\', __DIR__ . '/polling_mensagens_whatsapp.php');
$php_path = PHP_BINARY;

echo "Script: $script_path\n";
echo "PHP: $php_path\n\n";

// Criar arquivo .bat para execução
$bat_content = "@echo off\n";
$bat_content .= "cd /d \"" . __DIR__ . "\"\n";
$bat_content .= "\"$php_path\" polling_mensagens_whatsapp.php >> polling.log 2>&1\n";

$bat_file = __DIR__ . '/polling_whatsapp.bat';
file_put_contents($bat_file, $bat_content);

echo "✅ Arquivo .bat criado: $bat_file\n\n";

// 3. Comando para Task Scheduler
echo "3. COMANDOS PARA CONFIGURAR AUTOMATIZAÇÃO:\n\n";

echo "📋 OPÇÃO 1 - Task Scheduler (Windows):\n";
echo "Comando para criar tarefa automática:\n\n";

$task_command = "schtasks /create /tn \"WhatsApp_Polling\" /tr \"\\\"$bat_file\\\"\" /sc minute /mo 1 /ru SYSTEM";
echo "```\n$task_command\n```\n\n";

echo "📋 OPÇÃO 2 - Comando manual no PowerShell (como Administrador):\n";
echo "```powershell\n";
echo "# Executar como Administrador\n";
echo "$task_command\n";
echo "```\n\n";

echo "📋 OPÇÃO 3 - Interface gráfica:\n";
echo "1. Abrir 'Agendador de Tarefas' (Task Scheduler)\n";
echo "2. Criar Tarefa Básica\n";
echo "3. Nome: WhatsApp Polling\n";
echo "4. Disparador: Diariamente\n";
echo "5. Repetir a cada: 1 minuto\n";
echo "6. Ação: Iniciar programa\n";
echo "7. Programa: $bat_file\n\n";

// 4. Teste de execução periódica
echo "4. TESTE DE EXECUÇÃO PERIÓDICA:\n";
echo "Executando 3 vezes com intervalo de 10 segundos...\n\n";

for ($i = 1; $i <= 3; $i++) {
    echo "--- Execução $i ---\n";
    
    ob_start();
    include __DIR__ . '/polling_mensagens_whatsapp.php';
    $output = ob_get_clean();
    
    echo $output;
    
    if ($i < 3) {
        echo "Aguardando 10 segundos...\n\n";
        sleep(10);
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

// 5. Status final e instruções
echo "🎯 POLLING CONFIGURADO COM SUCESSO!\n\n";

echo "📊 STATUS:\n";
echo "✅ Script de polling criado\n";
echo "✅ Arquivo .bat gerado\n";
echo "✅ Comandos de automatização prontos\n\n";

echo "🚀 PRÓXIMOS PASSOS:\n";
echo "1. Execute o comando do Task Scheduler (como Administrador)\n";
echo "2. Verifique se a tarefa foi criada no Agendador de Tarefas\n";
echo "3. Teste enviando mensagens do WhatsApp\n";
echo "4. Monitore os logs em polling.log\n\n";

echo "📝 VERIFICAR FUNCIONAMENTO:\n";
echo "- Envie mensagem do seu WhatsApp para o canal\n";
echo "- Aguarde até 1 minuto (ciclo do polling)\n";
echo "- Verifique se apareceu no chat do sistema\n";
echo "- Ana deve responder automaticamente\n\n";

echo "🔍 LOGS E MONITORAMENTO:\n";
echo "- Log do polling: polling.log\n";
echo "- Log do sistema: " . ini_get('error_log') . "\n";
echo "- Banco de dados: Tabela mensagens_comunicacao\n\n";

echo "⚠️ IMPORTANTE:\n";
echo "Esta é uma solução TEMPORÁRIA. Recomenda-se migrar para uma API\n";
echo "com webhook bidirecional assim que possível (Evolution API, Baileys, etc.)\n\n";

// 6. Criar arquivo de status
$status = [
    'data_configuracao' => date('Y-m-d H:i:s'),
    'polling_ativo' => true,
    'script_path' => $script_path,
    'bat_file' => $bat_file,
    'php_path' => $php_path
];

file_put_contents(__DIR__ . '/polling_status.json', json_encode($status, JSON_PRETTY_PRINT));

echo "📄 Status salvo em: polling_status.json\n";
echo "🎉 CONFIGURAÇÃO CONCLUÍDA!\n";
?> 