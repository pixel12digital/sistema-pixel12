<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🧹 Limpando cache e forçando atualização...\n\n";

// 1. Limpar cache do navegador (arquivos de cache)
echo "🗄️ Limpando arquivos de cache...\n";
$cache_dirs = ['cache/', 'painel/cache/'];
$files_removed = 0;

foreach ($cache_dirs as $dir) {
    if (is_dir($dir)) {
        $cache_files = glob($dir . '*.cache');
        foreach ($cache_files as $file) {
            if (unlink($file)) {
                $files_removed++;
            }
        }
    }
}

echo "✅ $files_removed arquivos de cache removidos\n";

// 2. Limpar logs de debug
echo "\n📝 Limpando logs de debug...\n";
$log_files = [
    'painel/debug_ajax_whatsapp.log',
    'painel/debug_chat_enviar.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        if (unlink($log_file)) {
            echo "✅ Log removido: $log_file\n";
        }
    }
}

// 3. Forçar atualização do status no banco
echo "\n🔄 Forçando atualização do status...\n";
$update = $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente', data_conexao = NULL WHERE porta = 3000");
if ($update) {
    echo "✅ Status dos canais resetado para 'pendente'\n";
} else {
    echo "❌ Erro ao atualizar status: " . $mysqli->error . "\n";
}

// 4. Verificar status atual
echo "\n📊 Status atual dos canais:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (Porta {$canal['porta']}) - {$canal['status']}\n";
    }
}

// 5. Testar conectividade
echo "\n🔍 Testando conectividade...\n";
$vps_ip = '212.85.11.238';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Servidor WhatsApp está funcionando\n";
    $data = json_decode($response, true);
    if ($data && isset($data['ready'])) {
        echo "   Status: " . ($data['ready'] ? 'Conectado' : 'Aguardando QR') . "\n";
    }
} else {
    echo "❌ Servidor não está respondendo\n";
    echo "   HTTP Code: $http_code\n";
    echo "   Erro: $error\n";
}

// 6. Instruções finais
echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "   1. Abra o navegador em modo privado/incógnito\n";
echo "   2. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   3. Pressione Ctrl+F5 para forçar reload completo\n";
echo "   4. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   5. Os canais devem aparecer como 'Desconectado'\n";
echo "   6. Clique em 'Conectar' para gerar QR code\n";

echo "\n💡 DICAS IMPORTANTES:\n";
echo "   - Use modo incógnito para evitar cache do navegador\n";
echo "   - Pressione Ctrl+F5 para forçar reload completo\n";
echo "   - Se ainda aparecer 'Conectado', limpe o cache do navegador\n";
echo "   - Verifique se não há extensões bloqueando requisições\n";

echo "\n✅ Limpeza e atualização concluídas!\n";
?> 