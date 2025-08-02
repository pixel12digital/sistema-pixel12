<?php
/**
 * 🔍 DIAGNOSTICAR ERRO HTTP 500 NO WEBHOOK
 * 
 * Testa o webhook localmente para capturar erro específico
 */

echo "🔍 DIAGNOSTICANDO ERRO HTTP 500\n";
echo "===============================\n\n";

// Simular dados de entrada do WhatsApp
$test_data = [
    'from' => '5547999999999',
    'body' => 'Olá, teste da Ana'
];

echo "📋 Dados de teste:\n";
echo "From: " . $test_data['from'] . "\n";
echo "Body: " . $test_data['body'] . "\n\n";

// Simular variáveis globais que o webhook espera
$_POST = $test_data;
$GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($test_data);

// Capturar erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 TESTE 1: Verificar dependências básicas\n";
echo "==========================================\n";

// Verificar se arquivos existem
$arquivos_necessarios = [
    'config.php',
    'painel/db.php',
    'painel/cache_invalidator.php',
    'painel/api/integrador_ana_local.php'
];

foreach ($arquivos_necessarios as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo\n";
    } else {
        echo "❌ $arquivo MISSING\n";
    }
}

echo "\n🧪 TESTE 2: Testar conexão com banco\n";
echo "====================================\n";

try {
    require_once 'config.php';
    echo "✅ config.php carregado\n";
    
    require_once 'painel/db.php';
    echo "✅ db.php carregado\n";
    
    if (isset($mysqli) && $mysqli->ping()) {
        echo "✅ Conexão MySQL ativa\n";
    } else {
        echo "❌ MySQL não conectado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao conectar banco: " . $e->getMessage() . "\n";
}

echo "\n🧪 TESTE 3: Testar cache invalidator\n";
echo "====================================\n";

try {
    require_once 'painel/cache_invalidator.php';
    echo "✅ cache_invalidator.php carregado\n";
    
    $invalidator = new CacheInvalidator();
    echo "✅ CacheInvalidator instanciado\n";
    
} catch (Exception $e) {
    echo "❌ Erro no cache invalidator: " . $e->getMessage() . "\n";
}

echo "\n🧪 TESTE 4: Testar integrador Ana\n";
echo "=================================\n";

try {
    require_once 'painel/api/integrador_ana_local.php';
    echo "✅ integrador_ana_local.php carregado\n";
    
    if (isset($mysqli)) {
        $integrador = new IntegradorAnaLocal($mysqli);
        echo "✅ IntegradorAnaLocal instanciado\n";
        
        // Testar processamento de mensagem
        $resultado = $integrador->processarMensagem($test_data);
        echo "✅ Processamento executado\n";
        echo "Sucesso: " . ($resultado['success'] ? 'SIM' : 'NÃO') . "\n";
        echo "Resposta Ana: " . substr($resultado['resposta_ana'] ?? 'N/A', 0, 50) . "...\n";
        
    } else {
        echo "❌ Variável mysqli não disponível\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro no integrador: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🧪 TESTE 5: Simular processamento completo\n";
echo "==========================================\n";

try {
    // Simular o fluxo completo do webhook
    $input = json_encode($test_data);
    
    // Dados normalizados
    $from = $test_data['from'];
    $body = $test_data['body'];
    $timestamp = time();
    
    echo "✅ Dados normalizados\n";
    echo "From: $from\n";
    echo "Body: $body\n";
    echo "Timestamp: $timestamp\n";
    
    // Verificar bloqueios
    if (isset($mysqli)) {
        $from_escaped = $mysqli->real_escape_string($from);
        $bloqueio_query = "SELECT * FROM bloqueios_ana WHERE numero_cliente = '$from_escaped' AND ativo = 1 LIMIT 1";
        $bloqueio = $mysqli->query($bloqueio_query);
        
        if ($bloqueio && $bloqueio->num_rows > 0) {
            echo "⚠️ Cliente tem bloqueio ativo\n";
        } else {
            echo "✅ Cliente não tem bloqueios\n";
        }
    }
    
    // Testar inserção de mensagem
    if (isset($mysqli)) {
        $canal_id = 36;
        $body_escaped = $mysqli->real_escape_string($body);
        
        $sql_teste = "SELECT COUNT(*) as count FROM mensagens_comunicacao WHERE canal_id = $canal_id";
        $resultado_count = $mysqli->query($sql_teste);
        
        if ($resultado_count) {
            $row = $resultado_count->fetch_assoc();
            echo "✅ Tabela mensagens_comunicacao acessível (registros: {$row['count']})\n";
        } else {
            echo "❌ Erro ao acessar tabela mensagens_comunicacao\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro no processamento completo: " . $e->getMessage() . "\n";
}

echo "\n🔧 TESTE 6: Executar webhook isoladamente\n";
echo "========================================\n";

// Capturar saída do webhook
ob_start();
$old_input = null;

try {
    // Simular dados de entrada
    file_put_contents('php://memory', json_encode($test_data));
    
    // Incluir o webhook sem executar
    echo "Tentando incluir webhook...\n";
    
    // Buffer para capturar erros
    $webhook_content = file_get_contents('painel/receber_mensagem_ana_local.php');
    echo "✅ Conteúdo do webhook lido\n";
    
    // Verificar se há syntax errors
    if (php_check_syntax_string($webhook_content)) {
        echo "✅ Sintaxe do webhook válida\n";
    } else {
        echo "❌ Erro de sintaxe no webhook\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao executar webhook: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo $output;

echo "\n📊 RESUMO DO DIAGNÓSTICO\n";
echo "========================\n";

echo "🎯 PRÓXIMAS AÇÕES:\n";
echo "1. Se todos os testes passaram, o problema pode ser no servidor web\n";
echo "2. Se algum teste falhou, corrija o componente específico\n";
echo "3. Teste com dados diferentes para verificar edge cases\n";
echo "4. Verifique logs do Apache/Nginx para erros específicos\n";

// Função para verificar sintaxe
function php_check_syntax_string($code) {
    return @eval('return true; ?>' . $code) === true;
}

?> 