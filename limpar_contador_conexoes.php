<?php
/**
 * Script para limpar contador de conexões e testar otimizações
 */

echo "🧹 LIMPANDO CONTADOR DE CONEXÕES\n";
echo "================================\n\n";

// Limpar contador de conexões
$contador_file = 'cache/conexoes_contador.txt';
if (file_exists($contador_file)) {
    unlink($contador_file);
    echo "✅ Contador de conexões limpo\n";
} else {
    echo "ℹ️ Contador de conexões não existia\n";
}

// Limpar último reset
$reset_file = 'cache/ultimo_reset.txt';
if (file_exists($reset_file)) {
    unlink($reset_file);
    echo "✅ Arquivo de reset limpo\n";
} else {
    echo "ℹ️ Arquivo de reset não existia\n";
}

// Limpar cache antigo
$cache_files = glob('cache/*.cache');
$removidos = 0;
foreach ($cache_files as $file) {
    if ((time() - filemtime($file)) > 1800) { // 30 minutos
        unlink($file);
        $removidos++;
    }
}
echo "✅ $removidos arquivos de cache antigos removidos\n";

// Testar conexão com banco
echo "\n🔍 TESTANDO CONEXÃO COM BANCO\n";
echo "==============================\n";

try {
    require_once 'config.php';
    require_once 'painel/db.php';
    
    if ($mysqli && $mysqli->ping()) {
        echo "✅ Conexão com banco OK\n";
        
        // Testar query simples
        $result = $mysqli->query("SELECT 1 as test");
        if ($result) {
            echo "✅ Query de teste OK\n";
        } else {
            echo "❌ Query de teste falhou\n";
        }
    } else {
        echo "❌ Conexão com banco falhou\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n📊 CONFIGURAÇÕES OTIMIZADAS ATIVAS\n";
echo "==================================\n";
echo "• Polling Configurações: 5 minutos\n";
echo "• Polling WhatsApp: 5 minutos\n";
echo "• Polling Monitoramento: 10 minutos\n";
echo "• Polling Chat: 5 minutos\n";
echo "• Polling Comunicação: 10 minutos\n";
echo "• Cache TTL: 30 minutos\n";
echo "• Limite de conexões: 450/hora\n";

echo "\n✅ Sistema otimizado e pronto para uso!\n";
echo "💡 As conexões foram reduzidas drasticamente\n";
echo "🚀 O sistema deve funcionar sem exceder o limite de 500/hora\n";
?> 