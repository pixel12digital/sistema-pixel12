<?php
/**
 * Script para Diagnosticar Erro 500 no Chat
 * Executa testes básicos para identificar a causa do erro
 */

header('Content-Type: text/plain; charset=utf-8');
echo "=== DIAGNÓSTICO ERRO 500 CHAT ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Verificar se existe e é legível
echo "1. VERIFICANDO ARQUIVOS BÁSICOS:\n";
$arquivos_essenciais = [
    'config.php',
    'db.php', 
    'cache_manager.php',
    'chat.php'
];

foreach ($arquivos_essenciais as $arquivo) {
    if (file_exists($arquivo)) {
        $tamanho = filesize($arquivo);
        $permissoes = substr(sprintf('%o', fileperms($arquivo)), -4);
        echo "   ✅ $arquivo ($tamanho bytes, $permissoes)\n";
    } else {
        echo "   ❌ $arquivo - ARQUIVO NÃO ENCONTRADO!\n";
    }
}

// 2. Testar sintaxe PHP
echo "\n2. VERIFICANDO SINTAXE PHP:\n";
foreach ($arquivos_essenciais as $arquivo) {
    if (file_exists($arquivo)) {
        $output = [];
        $return_var = 0;
        exec("php -l $arquivo 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "   ✅ $arquivo - Sintaxe OK\n";
        } else {
            echo "   ❌ $arquivo - ERRO DE SINTAXE:\n";
            foreach ($output as $line) {
                echo "      $line\n";
            }
        }
    }
}

// 3. Testar conexão com banco
echo "\n3. TESTANDO CONEXÃO COM BANCO:\n";
try {
    require_once 'config.php';
    echo "   ✅ config.php carregado\n";
    
    require_once 'db.php';
    echo "   ✅ db.php carregado\n";
    
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        if ($mysqli->ping()) {
            echo "   ✅ Conexão com MySQL ativa\n";
            
            // Testar consultas básicas
            $result = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   ✅ Query básica OK (Total clientes: {$row['total']})\n";
            } else {
                echo "   ❌ Erro na query básica: " . $mysqli->error . "\n";
            }
        } else {
            echo "   ❌ Conexão MySQL inativa\n";
        }
    } else {
        echo "   ❌ Variável \$mysqli não encontrada ou inválida\n";
    }
} catch (Exception $e) {
    echo "   ❌ ERRO: " . $e->getMessage() . "\n";
}

// 4. Testar cache_manager
echo "\n4. TESTANDO CACHE MANAGER:\n";
try {
    require_once 'cache_manager.php';
    echo "   ✅ cache_manager.php carregado\n";
    
    // Testar função básica de cache
    if (function_exists('cache_remember')) {
        echo "   ✅ Função cache_remember existe\n";
        
        // Teste simples
        $teste = cache_remember('teste_diagnostico', function() {
            return 'Cache funcionando';
        }, 10);
        
        if ($teste === 'Cache funcionando') {
            echo "   ✅ Cache básico funcionando\n";
        } else {
            echo "   ❌ Cache básico não está funcionando\n";
        }
    } else {
        echo "   ❌ Função cache_remember não encontrada\n";
    }
    
    // Testar cache_conversas se tivermos conexão com BD
    if (isset($mysqli) && $mysqli instanceof mysqli && $mysqli->ping()) {
        echo "   🔄 Testando cache_conversas...\n";
        
        if (function_exists('cache_conversas')) {
            $conversas = cache_conversas($mysqli);
            echo "   ✅ cache_conversas executou (Total: " . count($conversas) . " conversas)\n";
        } else {
            echo "   ❌ Função cache_conversas não encontrada\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ ERRO no cache_manager: " . $e->getMessage() . "\n";
}

// 5. Verificar logs de erro
echo "\n5. VERIFICANDO LOGS DE ERRO:\n";
$logs_possiveis = [
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log', 
    'error_log',
    '../error_log',
    '../../error_log'
];

foreach ($logs_possiveis as $log) {
    if (file_exists($log) && is_readable($log)) {
        echo "   📄 Log encontrado: $log\n";
        
        // Últimas 10 linhas do log
        $linhas = array_slice(file($log), -10);
        foreach ($linhas as $linha) {
            if (stripos($linha, 'chat.php') !== false || stripos($linha, 'fatal') !== false) {
                echo "      ⚠️  " . trim($linha) . "\n";
            }
        }
    }
}

// 6. Verificar diretório de cache
echo "\n6. VERIFICANDO DIRETÓRIO DE CACHE:\n";
$cache_dir = sys_get_temp_dir() . '/loja_virtual_cache/';
if (is_dir($cache_dir)) {
    echo "   ✅ Diretório de cache existe: $cache_dir\n";
    
    if (is_writable($cache_dir)) {
        echo "   ✅ Diretório de cache é gravável\n";
        
        // Listar arquivos de cache
        $arquivos_cache = glob($cache_dir . '*');
        echo "   📁 Arquivos de cache: " . count($arquivos_cache) . "\n";
    } else {
        echo "   ❌ Diretório de cache NÃO é gravável\n";
    }
} else {
    echo "   ⚠️  Diretório de cache não existe, tentando criar...\n";
    if (mkdir($cache_dir, 0755, true)) {
        echo "   ✅ Diretório de cache criado\n";
    } else {
        echo "   ❌ Falha ao criar diretório de cache\n";
    }
}

// 7. Testar carregamento parcial do chat.php
echo "\n7. TESTANDO CARREGAMENTO PARCIAL DO CHAT:\n";
try {
    // Capturar saída e erros
    ob_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Tentar incluir apenas as primeiras linhas do chat
    $chat_lines = file('chat.php');
    $partial_code = implode('', array_slice($chat_lines, 0, 20));
    
    eval('?>' . $partial_code);
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "   ✅ Primeiras 20 linhas do chat.php executaram OK\n";
    
} catch (ParseError $e) {
    echo "   ❌ ERRO DE SINTAXE no chat.php: " . $e->getMessage() . "\n";
    echo "      Linha: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "   ❌ ERRO GERAL no chat.php: " . $e->getMessage() . "\n";
}

// 8. Verificar se há problemas de memória/tempo
echo "\n8. VERIFICANDO LIMITES DO SISTEMA:\n";
echo "   📊 Memória limite: " . ini_get('memory_limit') . "\n";
echo "   📊 Tempo execução: " . ini_get('max_execution_time') . "s\n";
echo "   📊 Memória usada: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n";

// 9. Sugestões de correção
echo "\n9. SUGESTÕES DE CORREÇÃO:\n";
echo "   🔧 Se erro de sintaxe: revisar últimas alterações no código\n";
echo "   🔧 Se erro de banco: verificar credenciais e estrutura das tabelas\n";
echo "   🔧 Se erro de cache: limpar cache manualmente\n";
echo "   🔧 Se erro de memória: aumentar memory_limit no PHP\n";
echo "   🔧 Se erro de arquivo: verificar permissões do servidor\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?> 