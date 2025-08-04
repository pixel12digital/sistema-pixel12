<?php
/**
 * 🔍 VERIFICADOR DE FORMATO DE DADOS - WEBHOOK
 * 
 * Verifica e corrige o formato dos dados recebidos do WhatsApp robot
 */

require_once 'config.php';

echo "=== 🔍 VERIFICADOR DE FORMATO DE DADOS - WEBHOOK ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// ===== 1. VERIFICAR ARQUIVO WEBHOOK =====
echo "1. 📋 VERIFICANDO ARQUIVO WEBHOOK:\n";

$webhook_file = 'painel/receber_mensagem_ana_local.php';
if (file_exists($webhook_file)) {
    echo "   ✅ Arquivo encontrado: $webhook_file\n";
    
    $content = file_get_contents($webhook_file);
    
    // Verificar se processa dados corretamente
    if (strpos($content, '$_POST') !== false || strpos($content, 'file_get_contents') !== false) {
        echo "   ✅ Processa dados POST/JSON\n";
    } else {
        echo "   ⚠️  Não detecta processamento de dados\n";
    }
    
    // Verificar campos esperados
    $campos_esperados = ['from', 'body', 'text', 'message'];
    $campos_encontrados = [];
    
    foreach ($campos_esperados as $campo) {
        if (strpos($content, $campo) !== false) {
            $campos_encontrados[] = $campo;
        }
    }
    
    echo "   📊 Campos encontrados: " . implode(', ', $campos_encontrados) . "\n";
    
} else {
    echo "   ❌ Arquivo não encontrado: $webhook_file\n";
}

echo "\n";

// ===== 2. TESTAR FORMATOS DE DADOS =====
echo "2. 🧪 TESTANDO FORMATOS DE DADOS:\n";

$testes = [
    [
        'nome' => 'Formato WhatsApp Robot (atual)',
        'dados' => [
            'event' => 'onmessage',
            'data' => [
                'from' => '554796164699',
                'text' => 'Teste formato robot',
                'type' => 'chat',
                'timestamp' => time(),
                'session' => 'default'
            ]
        ]
    ],
    [
        'nome' => 'Formato Webhook (esperado)',
        'dados' => [
            'from' => '554796164699@c.us',
            'body' => 'Teste formato webhook',
            'timestamp' => time()
        ]
    ],
    [
        'nome' => 'Formato Híbrido (correção)',
        'dados' => [
            'from' => '554796164699@c.us',
            'body' => 'Teste formato híbrido',
            'text' => 'Teste formato híbrido',
            'message' => 'Teste formato híbrido',
            'timestamp' => time()
        ]
    ]
];

foreach ($testes as $teste) {
    echo "   📋 Testando: {$teste['nome']}\n";
    
    $json_data = json_encode($teste['dados']);
    echo "   📄 Dados: " . substr($json_data, 0, 100) . "...\n";
    
    // Simular processamento
    $from = null;
    $body = null;
    
    if (isset($teste['dados']['data'])) {
        // Formato WhatsApp Robot
        $from = $teste['dados']['data']['from'] ?? null;
        $body = $teste['dados']['data']['text'] ?? null;
    } else {
        // Formato direto
        $from = $teste['dados']['from'] ?? null;
        $body = $teste['dados']['body'] ?? $teste['dados']['text'] ?? $teste['dados']['message'] ?? null;
    }
    
    if ($from && $body) {
        echo "   ✅ Dados válidos: from=$from, body=$body\n";
    } else {
        echo "   ❌ Dados incompletos: from=" . ($from ?: 'NULL') . ", body=" . ($body ?: 'NULL') . "\n";
    }
    
    echo "\n";
}

// ===== 3. VERIFICAR LOGS RECENTES =====
echo "3. 📋 VERIFICANDO LOGS RECENTES:\n";

$log_file = 'painel/debug_ajax_whatsapp.log';
if (file_exists($log_file)) {
    echo "   ✅ Log encontrado: $log_file\n";
    
    $logs = file($log_file);
    $logs_recentes = array_slice($logs, -10); // Últimas 10 linhas
    
    echo "   📊 Últimas 10 linhas:\n";
    foreach ($logs_recentes as $linha) {
        echo "      " . trim($linha) . "\n";
    }
} else {
    echo "   ℹ️  Log não encontrado: $log_file\n";
}

echo "\n";

// ===== 4. SUGESTÕES DE CORREÇÃO =====
echo "4. 🔧 SUGESTÕES DE CORREÇÃO:\n";

echo "   📋 PROBLEMA IDENTIFICADO:\n";
echo "   - WhatsApp robot envia: {\"event\":\"onmessage\",\"data\":{\"from\":\"554796164699\",\"text\":\"msg\"}}\n";
echo "   - Webhook espera: {\"from\":\"554796164699@c.us\",\"body\":\"msg\"}\n";
echo "\n";

echo "   🛠️  SOLUÇÕES:\n";
echo "   1. Corrigir webhook para aceitar formato do robot\n";
echo "   2. Corrigir robot para enviar formato correto\n";
echo "   3. Implementar adaptador de formato\n";
echo "\n";

echo "   🎯 PRÓXIMA AÇÃO:\n";
echo "   1. Executar correção de coluna primeiro\n";
echo "   2. Depois corrigir formato de dados\n";
echo "   3. Testar com mensagem real\n";

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
?> 