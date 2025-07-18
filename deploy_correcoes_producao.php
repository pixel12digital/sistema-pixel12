<?php
/**
 * Deploy das Correções para Produção
 * Script para aplicar as correções de URLs no servidor
 */
echo "=== DEPLOY DAS CORREÇÕES PARA PRODUÇÃO ===\n\n";

// 1. Verificar se estamos no ambiente correto
echo "1. Verificando ambiente...\n";
$is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
            strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
            strpos($_SERVER['SCRIPT_NAME'] ?? '', 'xampp') !== false;

if ($is_local) {
    echo "⚠️  ATENÇÃO: Este script deve ser executado no servidor de produção!\n";
    echo "Execute via SSH ou painel de controle do Hostinger.\n";
    echo "Caminho: public_html/app/deploy_correcoes_producao.php\n\n";
    exit(1);
}

echo "✅ Ambiente de produção detectado\n\n";

// 2. Fazer backup dos arquivos
echo "2. Fazendo backup dos arquivos...\n";
$files_to_backup = [
    'painel/assets/cobrancas.js',
    'index.js',
    'whatsapp-api-server.js'
];

foreach ($files_to_backup as $file) {
    if (file_exists($file)) {
        $backup_file = $file . '.backup.' . date('Y-m-d_H-i-s');
        copy($file, $backup_file);
        echo "✅ Backup criado: $backup_file\n";
    }
}
echo "\n";

// 3. Aplicar correções
echo "3. Aplicando correções...\n";

// Corrigir painel/assets/cobrancas.js
$file = 'painel/assets/cobrancas.js';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace('/loja-virtual-revenda/api/', '/api/', $content);
    $content = str_replace('/loja-virtual-revenda/painel/api/', '/painel/api/', $content);
    file_put_contents($file, $content);
    echo "✅ painel/assets/cobrancas.js corrigido\n";
}

// Corrigir index.js
$file = 'index.js';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace('http://localhost:8080/loja-virtual-revenda/', '', $content);
    file_put_contents($file, $content);
    echo "✅ index.js corrigido\n";
}

// Corrigir whatsapp-api-server.js
$file = 'whatsapp-api-server.js';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace('http://localhost:8080/loja-virtual-revenda/', '', $content);
    file_put_contents($file, $content);
    echo "✅ whatsapp-api-server.js corrigido\n";
}

// 4. Verificar outros arquivos
echo "\n4. Verificando outros arquivos...\n";
$files_to_check = [
    'painel/faturas.php',
    'painel/clientes.php',
    'painel/dashboard.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, '/loja-virtual-revenda/') !== false) {
            echo "⚠️  Corrigindo: $file\n";
            $content = str_replace('/loja-virtual-revenda/', '/', $content);
            file_put_contents($file, $content);
            echo "✅ Corrigido: $file\n";
        }
    }
}

// 5. Limpar cache se necessário
echo "\n5. Limpando cache...\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpo\n";
}

// 6. Verificar permissões
echo "\n6. Verificando permissões...\n";
$files_to_check_permissions = [
    'painel/assets/cobrancas.js',
    'api/cobrancas.php'
];

foreach ($files_to_check_permissions as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        if (($perms & 0x0080) && ($perms & 0x0020) && ($perms & 0x0004)) {
            echo "✅ Permissões OK: $file\n";
        } else {
            echo "⚠️  Ajustando permissões: $file\n";
            chmod($file, 0644);
        }
    }
}

echo "\n=== DEPLOY CONCLUÍDO ===\n";
echo "✅ Todas as correções foram aplicadas!\n";
echo "🌐 Teste a interface: https://app.pixel12digital.com.br/painel/faturas.php\n";
echo "📝 Se houver problemas, os backups estão disponíveis com extensão .backup\n";
?> 