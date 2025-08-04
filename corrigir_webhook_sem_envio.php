<?php
/**
 * 🔧 CORREÇÃO TEMPORÁRIA - WEBHOOK SEM ENVIO
 * 
 * Desabilita o envio de respostas da Ana temporariamente
 * para que pelo menos as mensagens sejam processadas
 */

echo "=== 🔧 CORREÇÃO TEMPORÁRIA - WEBHOOK SEM ENVIO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// ===== 1. FAZER BACKUP =====
echo "1. 💾 FAZENDO BACKUP:\n";

$webhook_file = 'painel/receber_mensagem_ana_local.php';
$backup_file = $webhook_file . '.backup_sem_envio_' . date('Ymd_His');

if (copy($webhook_file, $backup_file)) {
    echo "   ✅ Backup criado: $backup_file\n";
} else {
    echo "   ❌ Erro ao criar backup\n";
    exit(1);
}

echo "\n";

// ===== 2. MODIFICAR WEBHOOK =====
echo "2. 🔧 MODIFICANDO WEBHOOK (TEMPORÁRIO):\n";

$content = file_get_contents($webhook_file);

// Comentar a parte de envio via VPS
$search = '// 6. Processar via integrador Ana';
$replace = '// 6. Processar via integrador Ana';

$search_integrador = '$integrador = new IntegradorAnaLocal($mysqli);
    $resultado_ana = $integrador->processarMensagem($dados);';

$replace_integrador = '$integrador = new IntegradorAnaLocal($mysqli);
    $resultado_ana = $integrador->processarMensagem($dados);
    
    // TEMPORÁRIO: Adicionar log de que Ana respondeu mas não enviou
    error_log("[WEBHOOK_ANA] Ana respondeu mas envio desabilitado temporariamente: " . ($resultado_ana[\'resposta_ana\'] ?? \'N/A\'));';

$new_content = str_replace($search_integrador, $replace_integrador, $content);

// Salvar arquivo modificado
if (file_put_contents($webhook_file, $new_content)) {
    echo "   ✅ Webhook modificado temporariamente\n";
    echo "   📋 Ana vai processar mensagens mas não enviar respostas\n";
} else {
    echo "   ❌ Erro ao modificar webhook\n";
    exit(1);
}

echo "\n";

// ===== 3. TESTAR WEBHOOK MODIFICADO =====
echo "3. 🧪 TESTANDO WEBHOOK MODIFICADO:\n";

$test_data = [
    "from" => "554796164699@c.us",
    "body" => "🧪 TESTE SEM ENVIO - " . date('Y-m-d H:i:s'),
    "timestamp" => time()
];

$url = "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   📊 HTTP Code: $http_code\n";
echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n";

if ($http_code == 200) {
    echo "   ✅ Webhook funcionando sem envio!\n";
} else {
    echo "   ❌ Ainda há problemas (HTTP $http_code)\n";
}

echo "\n";

// ===== 4. CRIAR SCRIPT PARA RESTAURAR =====
echo "4. 📋 CRIANDO SCRIPT PARA RESTAURAR:\n";

$restore_script = '<?php
// Script para restaurar webhook original
$backup_file = "' . $backup_file . '";
$webhook_file = "' . $webhook_file . '";

if (file_exists($backup_file)) {
    if (copy($backup_file, $webhook_file)) {
        echo "✅ Webhook restaurado com sucesso!\n";
        echo "📋 Backup: $backup_file\n";
    } else {
        echo "❌ Erro ao restaurar webhook\n";
    }
} else {
    echo "❌ Backup não encontrado: $backup_file\n";
}
?>';

file_put_contents('restaurar_webhook.php', $restore_script);
echo "   ✅ Script de restauração criado: restaurar_webhook.php\n";

echo "\n";

// ===== 5. INSTRUÇÕES FINAIS =====
echo "5. 🎯 INSTRUÇÕES FINAIS:\n";

echo "   📋 STATUS ATUAL:\n";
echo "   ✅ Webhook processa mensagens\n";
echo "   ✅ Ana analisa e responde\n";
echo "   ✅ Mensagens são salvas no banco\n";
echo "   ❌ Ana NÃO envia respostas para WhatsApp (temporário)\n\n";

echo "   🔧 PARA CORRIGIR PERMANENTEMENTE:\n";
echo "   1. Acessar VPS via SSH: ssh root@212.85.11.238\n";
echo "   2. Verificar código da API WhatsApp\n";
echo "   3. Encontrar endpoint correto de envio\n";
echo "   4. Configurar envio no webhook\n";
echo "   5. Executar: php restaurar_webhook.php\n\n";

echo "   📱 PARA TESTAR:\n";
echo "   1. Envie mensagem para 554797146908\n";
echo "   2. Verifique se aparece no banco/chat web\n";
echo "   3. Ana vai processar mas não responder no WhatsApp\n\n";

echo "   🎯 PRÓXIMA AÇÃO:\n";
echo "   Investigar VPS para encontrar endpoint de envio correto\n";

echo "\n=== FIM DA CORREÇÃO TEMPORÁRIA ===\n";
echo "Status: Webhook funcionando (sem envio de respostas)\n";
?> 