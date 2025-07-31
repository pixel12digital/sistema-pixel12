<?php
require_once "config.php";
require_once "painel/db.php";

echo "🔄 RESTAURANDO FUNCIONALIDADE ORIGINAL\n";
echo "====================================\n\n";

// 1. Verificar configuração atual
echo "📋 CONFIGURAÇÃO ATUAL:\n";
echo "   WHATSAPP_ROBOT_URL: " . WHATSAPP_ROBOT_URL . "\n";

// 2. Verificar se há um arquivo de backup ou configuração original
echo "\n🔍 VERIFICANDO CONFIGURAÇÕES ORIGINAIS:\n";

// Verificar se existe um arquivo de configuração original
$config_files = [
    'config_original.php',
    'config_backup.php',
    'config.php.bak',
    'config_whatsapp_original.php'
];

$found_original = false;
foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "   ✅ Encontrado arquivo de backup: $file\n";
        $found_original = true;
        break;
    }
}

if (!$found_original) {
    echo "   ⚠️ Nenhum arquivo de backup encontrado\n";
}

// 3. Verificar se há outro servidor WhatsApp na VPS
echo "\n🔍 VERIFICANDO OUTROS SERVIDORES NA VPS:\n";

$ports_to_check = [3000, 3001, 3002, 3003, 3004, 3005, 8080, 8000, 5000, 4000];

foreach ($ports_to_check as $port) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:$port/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Porta $port: SERVIDOR ATIVO\n";
        
        // Verificar se tem endpoints de envio
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:$port/send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 || $http_code === 405) {
            echo "      📤 TEM ENDPOINT DE ENVIO!\n";
        } else {
            echo "      ❌ SEM ENDPOINT DE ENVIO\n";
        }
    }
}

// 4. Verificar se há configuração no banco que indique a URL correta
echo "\n📋 VERIFICANDO CONFIGURAÇÕES NO BANCO:\n";

$result = $mysqli->query("SHOW TABLES LIKE 'configuracoes'");
if ($result && $result->num_rows > 0) {
    echo "   ✅ Tabela de configurações encontrada\n";
    
    $result = $mysqli->query("SELECT * FROM configuracoes WHERE chave LIKE '%whatsapp%' OR chave LIKE '%vps%' OR chave LIKE '%api%'");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "   📊 " . $row['chave'] . ": " . $row['valor'] . "\n";
        }
    }
} else {
    echo "   ⚠️ Tabela de configurações não encontrada\n";
}

// 5. Verificar se há variáveis de ambiente ou arquivos .env
echo "\n🔍 VERIFICANDO ARQUIVOS DE CONFIGURAÇÃO:\n";

$env_files = [
    '.env',
    '.env.local',
    '.env.production',
    'config.env'
];

foreach ($env_files as $file) {
    if (file_exists($file)) {
        echo "   ✅ Arquivo encontrado: $file\n";
        $content = file_get_contents($file);
        if (strpos($content, 'WHATSAPP') !== false || strpos($content, 'VPS') !== false) {
            echo "      📊 Contém configurações WhatsApp/VPS\n";
        }
    }
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "   1. Verificar se há um servidor WhatsApp diferente na VPS\n";
echo "   2. Restaurar configuração original do sistema\n";
echo "   3. Verificar se o problema está no ajax_whatsapp.php\n";
echo "   4. Testar com a URL original que funcionava\n";

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 