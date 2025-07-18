<?php
/**
 * Resolver Conflito Git em Produção
 * Script para resolver problemas de merge no subdomínio
 */

echo "=== RESOLVENDO CONFLITO GIT EM PRODUÇÃO ===\n\n";

// 1. Verificar status atual
echo "1. VERIFICANDO STATUS ATUAL:\n";
$output = [];
exec('git status 2>&1', $output, $return_code);

if ($return_code !== 0) {
    echo "❌ Erro ao verificar status do Git\n";
    echo "Saída: " . implode("\n", $output) . "\n";
    exit(1);
}

echo implode("\n", $output) . "\n\n";

// 2. Verificar se há alterações não commitadas
echo "2. VERIFICANDO ALTERAÇÕES NÃO COMMITADAS:\n";
$output = [];
exec('git diff --name-only 2>&1', $output, $return_code);

if (!empty($output)) {
    echo "Arquivos modificados:\n";
    foreach ($output as $file) {
        echo "- $file\n";
    }
    echo "\n";
    
    // 3. Fazer stash das alterações
    echo "3. FAZENDO STASH DAS ALTERAÇÕES:\n";
    $output = [];
    exec('git stash push -m "Alterações temporárias antes do pull" 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        echo "✅ Stash criado com sucesso!\n";
        echo implode("\n", $output) . "\n";
    } else {
        echo "❌ Erro ao fazer stash\n";
        echo implode("\n", $output) . "\n";
        exit(1);
    }
} else {
    echo "✅ Nenhuma alteração não commitada encontrada\n";
}

// 4. Fazer pull das alterações
echo "4. FAZENDO PULL DAS ALTERAÇÕES:\n";
$output = [];
exec('git pull origin master 2>&1', $output, $return_code);

if ($return_code === 0) {
    echo "✅ Pull realizado com sucesso!\n";
    echo implode("\n", $output) . "\n";
} else {
    echo "❌ Erro no pull\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

// 5. Aplicar stash se existir
echo "5. APLICANDO STASH (se existir):\n";
$output = [];
exec('git stash list 2>&1', $output, $return_code);

if (!empty($output) && strpos(implode("\n", $output), 'stash@{0}') !== false) {
    $output = [];
    exec('git stash pop 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        echo "✅ Stash aplicado com sucesso!\n";
        echo implode("\n", $output) . "\n";
    } else {
        echo "⚠️ Conflito ao aplicar stash - resolva manualmente\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "ℹ️ Nenhum stash para aplicar\n";
}

// 6. Verificar status final
echo "6. VERIFICANDO STATUS FINAL:\n";
$output = [];
exec('git status 2>&1', $output, $return_code);

echo implode("\n", $output) . "\n\n";

// 7. Verificar se os arquivos críticos estão presentes
echo "7. VERIFICANDO ARQUIVOS CRÍTICOS:\n";
$arquivos_criticos = [
    'painel/config.php',
    'painel/verificador_automatico_chave_otimizado.php',
    'painel/monitoramento_otimizado.js',
    'painel/faturas.php',
    'atualizar_chave_producao.php',
    'diagnostico_producao.php'
];

foreach ($arquivos_criticos as $arquivo) {
    if (file_exists($arquivo)) {
        $size = filesize($arquivo);
        echo "✅ $arquivo ($size bytes)\n";
    } else {
        echo "❌ $arquivo (NÃO ENCONTRADO)\n";
    }
}

echo "\n=== RESOLUÇÃO CONCLUÍDA ===\n";
echo "Agora execute: php atualizar_chave_producao.php\n";
?> 