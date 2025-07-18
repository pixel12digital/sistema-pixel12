<?php
/**
 * CORREÇÃO DO SISTEMA DE ENVIO DO PAINEL
 * 
 * Problema identificado: O sistema detecta ambiente local e tenta conectar no localhost
 * Solução: Forçar uso do banco remoto mesmo em ambiente local
 */

echo "=== CORREÇÃO DO SISTEMA DE ENVIO ===\n\n";

// 1. Verificar arquivo atual
$arquivo_original = 'painel/chat_enviar.php';
$arquivo_backup = 'painel/chat_enviar.php.backup.' . date('Y-m-d_H-i-s');

if (!file_exists($arquivo_original)) {
    echo "❌ Arquivo $arquivo_original não encontrado\n";
    exit;
}

// 2. Fazer backup
echo "1. Fazendo backup do arquivo original...\n";
if (copy($arquivo_original, $arquivo_backup)) {
    echo "✅ Backup criado: $arquivo_backup\n";
} else {
    echo "❌ Erro ao criar backup\n";
    exit;
}

// 3. Ler conteúdo atual
echo "\n2. Lendo arquivo atual...\n";
$conteudo = file_get_contents($arquivo_original);

// 4. Aplicar correções
echo "3. Aplicando correções...\n";

// Correção 1: Forçar uso do banco remoto
$correcao_banco = '
// CORREÇÃO: Forçar uso do banco remoto mesmo em ambiente local
if (!isset($mysqli) || $mysqli->connect_errno) {
    $mysqli = new mysqli(\'srv1607.hstgr.io\', \'u342734079_revendaweb\', \'Los@ngo#081081\', \'u342734079_revendaweb\');
    if ($mysqli->connect_errno) {
        echo json_encode([\'success\' => false, \'error\' => \'Erro ao conectar ao banco remoto\']);
        exit;
    }
    $mysqli->set_charset(\'utf8mb4\');
}';

// Inserir correção após as validações
$posicao_insercao = strpos($conteudo, '// Usar cache para verificar cliente');
if ($posicao_insercao !== false) {
    $conteudo = substr_replace($conteudo, $correcao_banco . "\n\n", $posicao_insercao, 0);
    echo "✅ Correção do banco aplicada\n";
}

// Correção 2: Melhorar timeout da API
$conteudo = str_replace(
    'curl_setopt($ch, CURLOPT_TIMEOUT, 10);',
    'curl_setopt($ch, CURLOPT_TIMEOUT, 30);',
    $conteudo
);
echo "✅ Timeout da API aumentado\n";

// Correção 3: Adicionar logs de debug
$log_debug = '
    // Log de debug
    error_log("[WHATSAPP] Enviando mensagem para $numero: " . substr($mensagem, 0, 50) . "...");
    error_log("[WHATSAPP] API URL: $api_url");
    error_log("[WHATSAPP] Payload: " . json_encode($api_data));';

$posicao_log = strpos($conteudo, '$api_response = curl_exec($ch);');
if ($posicao_log !== false) {
    $conteudo = substr_replace($conteudo, $log_debug . "\n\n    ", $posicao_log, 0);
    echo "✅ Logs de debug adicionados\n";
}

// 5. Salvar arquivo corrigido
echo "\n4. Salvando arquivo corrigido...\n";
if (file_put_contents($arquivo_original, $conteudo)) {
    echo "✅ Arquivo corrigido salvo com sucesso\n";
} else {
    echo "❌ Erro ao salvar arquivo\n";
    exit;
}

// 6. Testar a correção
echo "\n5. Testando correção...\n";
$teste_url = 'http://localhost/loja-virtual-revenda/painel/chat_enviar.php';
echo "URL de teste: $teste_url\n";
echo "Para testar, envie uma mensagem pelo painel e verifique os logs\n";

// 7. Criar script de teste
$script_teste = '<?php
// Teste rápido da correção
$_POST["cliente_id"] = 156;
$_POST["mensagem"] = "Teste correção - " . date("H:i:s");
$_POST["canal_id"] = 36;

include "chat_enviar.php";
?>';

file_put_contents('painel/teste_correcao.php', $script_teste);
echo "✅ Script de teste criado: painel/teste_correcao.php\n";

echo "\n=== CORREÇÃO CONCLUÍDA ===\n";
echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Teste o envio pelo painel\n";
echo "2. Verifique os logs em logs/debug_cobrancas.log\n";
echo "3. Se necessário, execute: php painel/teste_correcao.php\n";
echo "4. Para reverter: cp $arquivo_backup $arquivo_original\n";
?> 