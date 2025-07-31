<?php
/**
 * Atualizador de Configuração de Canais WhatsApp
 * Adiciona novos canais automaticamente ao sistema
 */

require_once "config.php";
require_once "painel/db.php";

echo "🔧 ATUALIZADOR DE CONFIGURAÇÃO DE CANAIS\n";
echo "========================================\n\n";

// Configurações dos canais
$canais = [
    [
        'porta' => 3000,
        'nome' => 'Canal Financeiro',
        'tipo' => 'whatsapp',
        'url' => 'http://212.85.11.238:3000'
    ],
    [
        'porta' => 3001,
        'nome' => 'Canal Comercial',
        'tipo' => 'whatsapp',
        'url' => 'http://212.85.11.238:3001'
    ],
    [
        'porta' => 3002,
        'nome' => 'Canal Suporte',
        'tipo' => 'whatsapp',
        'url' => 'http://212.85.11.238:3002'
    ],
    [
        'porta' => 3003,
        'nome' => 'Canal Marketing',
        'tipo' => 'whatsapp',
        'url' => 'http://212.85.11.238:3003'
    ]
];

echo "1️⃣ Verificando canais existentes no banco...\n";
$stmt = $pdo->prepare("SELECT porta, nome FROM canais_comunicacao WHERE tipo = 'whatsapp'");
$stmt->execute();
$canais_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$portas_existentes = array_column($canais_existentes, 'porta');
echo "   📊 Canais existentes: " . implode(', ', $portas_existentes) . "\n\n";

echo "2️⃣ Adicionando novos canais...\n";
foreach ($canais as $canal) {
    if (!in_array($canal['porta'], $portas_existentes)) {
        echo "   ➕ Adicionando canal {$canal['porta']} ({$canal['nome']})...\n";
        
        $stmt = $pdo->prepare("INSERT INTO canais_comunicacao (nome, tipo, porta, status, data_criacao) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute([$canal['nome'], $canal['tipo'], $canal['porta']]);
        
        echo "   ✅ Canal {$canal['porta']} adicionado!\n";
    } else {
        echo "   ⏭️ Canal {$canal['porta']} já existe, pulando...\n";
    }
}

echo "\n3️⃣ Atualizando arquivo config.php...\n";
$config_content = file_get_contents('config.php');

// Verificar se as constantes já existem
$constantes_para_adicionar = [];
foreach ($canais as $canal) {
    $constante = "WHATSAPP_ROBOT_URL_{$canal['porta']}";
    if (strpos($config_content, $constante) === false) {
        $constantes_para_adicionar[] = "define('$constante', '{$canal['url']}');";
    }
}

if (!empty($constantes_para_adicionar)) {
    // Adicionar antes do fechamento do PHP
    $novas_constantes = "\n// Configurações dos canais WhatsApp\n" . implode("\n", $constantes_para_adicionar) . "\n";
    
    if (strpos($config_content, '?>') !== false) {
        $config_content = str_replace('?>', $novas_constantes . '?>', $config_content);
    } else {
        $config_content .= $novas_constantes;
    }
    
    file_put_contents('config.php', $config_content);
    echo "   ✅ Constantes adicionadas ao config.php\n";
} else {
    echo "   ⏭️ Todas as constantes já existem\n";
}

echo "\n4️⃣ Atualizando ajax_whatsapp.php...\n";
$ajax_content = file_get_contents('painel/ajax_whatsapp.php');

// Criar mapeamento de portas
$mapeamento_portas = [];
foreach ($canais as $canal) {
    $mapeamento_portas[] = "    '{$canal['porta']}' => '{$canal['url']}'";
}

$novo_mapeamento = "\$porta_urls = [\n" . implode(",\n", $mapeamento_portas) . "\n];\n\n// Usar a porta correta\n\$porta = \$_POST['porta'] ?? '3000';\n\$vps_url = \$porta_urls[\$porta] ?? \$porta_urls['3000'];";

// Verificar se o mapeamento já existe
if (strpos($ajax_content, '$porta_urls') === false) {
    // Adicionar após a linha que define $vps_url
    $ajax_content = preg_replace(
        '/\$vps_url\s*=\s*WHATSAPP_ROBOT_URL;/',
        $novo_mapeamento,
        $ajax_content
    );
    
    file_put_contents('painel/ajax_whatsapp.php', $ajax_content);
    echo "   ✅ Mapeamento de portas adicionado ao ajax_whatsapp.php\n";
} else {
    echo "   ⏭️ Mapeamento de portas já existe\n";
}

echo "\n5️⃣ Verificando conectividade dos canais...\n";
foreach ($canais as $canal) {
    echo "   🔍 Testando canal {$canal['porta']}...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $canal['url'] . "/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "   ✅ Canal {$canal['porta']} está online\n";
    } else {
        echo "   ❌ Canal {$canal['porta']} não está respondendo (HTTP $http_code)\n";
    }
}

echo "\n6️⃣ Criando script de teste para todos os canais...\n";
$teste_content = "<?php\n/**\n * Teste de Todos os Canais WhatsApp\n */\n\necho \"🔍 TESTE DE TODOS OS CANAIS\n\";\necho \"============================\n\n\";\n\n";

foreach ($canais as $canal) {
    $teste_content .= "// Teste do Canal {$canal['porta']} - {$canal['nome']}\n";
    $teste_content .= "echo \"📞 Testando {$canal['nome']} (Porta {$canal['porta']})...\\n\";\n";
    $teste_content .= "\$ch = curl_init();\n";
    $teste_content .= "curl_setopt(\$ch, CURLOPT_URL, '{$canal['url']}/status');\n";
    $teste_content .= "curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);\n";
    $teste_content .= "curl_setopt(\$ch, CURLOPT_TIMEOUT, 5);\n";
    $teste_content .= "\$response = curl_exec(\$ch);\n";
    $teste_content .= "\$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);\n";
    $teste_content .= "curl_close(\$ch);\n\n";
    $teste_content .= "if (\$http_code == 200) {\n";
    $teste_content .= "    echo \"   ✅ {$canal['nome']} está online\\n\";\n";
    $teste_content .= "} else {\n";
    $teste_content .= "    echo \"   ❌ {$canal['nome']} não está respondendo (HTTP \$http_code)\\n\";\n";
    $teste_content .= "}\n\n";
}

$teste_content .= "echo \"🎯 Teste concluído!\\n\";\n?>";

file_put_contents('teste_todos_canais.php', $teste_content);
echo "   ✅ Script teste_todos_canais.php criado\n";

echo "\n📋 RESUMO DA ATUALIZAÇÃO\n";
echo "=======================\n";
echo "✅ Canais adicionados ao banco de dados\n";
echo "✅ Configurações atualizadas no config.php\n";
echo "✅ Mapeamento de portas no ajax_whatsapp.php\n";
echo "✅ Script de teste criado\n";
echo "\n🎯 Sistema atualizado com sucesso!\n";
echo "\n💡 Para testar todos os canais, execute:\n";
echo "   php teste_todos_canais.php\n";
?> 