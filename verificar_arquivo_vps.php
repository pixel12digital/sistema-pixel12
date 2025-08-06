<?php
/**
 * 🔍 VERIFICAR ARQUIVO NA VPS
 * 
 * Este script verifica se as mudanças foram aplicadas no arquivo whatsapp-api-server.js
 */

header('Content-Type: text/plain; charset=utf-8');

echo "🔍 VERIFICANDO ARQUIVO NA VPS\n";
echo "=============================\n\n";

// Configurações da VPS
$vps_ip = '212.85.11.238';
$vps_user = 'root';
$arquivo = '/var/whatsapp-api/whatsapp-api-server.js';

echo "🎯 Verificando arquivo: $arquivo\n";
echo "🌐 VPS: $vps_ip\n\n";

// Comando para verificar o conteúdo do arquivo
$comando = "ssh $vps_user@$vps_ip 'cat $arquivo | grep -A 5 -B 5 \"webhookConfig\|webhookUrl\"'";

echo "🔍 Executando comando:\n";
echo "$comando\n\n";

$output = shell_exec($comando);

if ($output) {
    echo "✅ Conteúdo encontrado:\n";
    echo str_repeat("-", 50) . "\n";
    echo $output;
    echo str_repeat("-", 50) . "\n\n";
    
    // Verificar se webhookConfig está presente
    if (strpos($output, 'webhookConfig') !== false) {
        echo "✅ webhookConfig encontrado no arquivo!\n";
    } else {
        echo "❌ webhookConfig NÃO encontrado no arquivo!\n";
    }
    
    // Verificar se webhookUrl ainda está presente
    if (strpos($output, 'webhookUrl') !== false) {
        echo "❌ webhookUrl ainda está presente no arquivo!\n";
    } else {
        echo "✅ webhookUrl foi removido do arquivo!\n";
    }
    
} else {
    echo "❌ Erro ao executar comando ou arquivo não encontrado\n";
}

// Verificar se os endpoints foram atualizados
echo "\n🔍 Verificando endpoints...\n";
$comando_endpoints = "ssh $vps_user@$vps_ip 'cat $arquivo | grep -A 10 -B 5 \"webhook/config\"'";

$output_endpoints = shell_exec($comando_endpoints);

if ($output_endpoints) {
    echo "✅ Endpoints encontrados:\n";
    echo str_repeat("-", 50) . "\n";
    echo $output_endpoints;
    echo str_repeat("-", 50) . "\n\n";
} else {
    echo "❌ Endpoints não encontrados!\n";
}

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Se webhookConfig não foi encontrado, aplique as mudanças\n";
echo "2. Se webhookUrl ainda está presente, remova-o\n";
echo "3. Se os endpoints não foram atualizados, corrija-os\n";
echo "4. Reinicie os serviços após as mudanças\n\n";
?> 