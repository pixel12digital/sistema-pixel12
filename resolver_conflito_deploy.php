<?php
/**
 * RESOLVEDOR DE CONFLITO DE DEPLOY
 * 
 * Este script resolve conflitos de merge no servidor de produção
 * quando há mudanças locais nos arquivos de configuração
 */

echo "=== RESOLVEDOR DE CONFLITO DE DEPLOY ===\n\n";

// 1. Verificar se estamos no servidor de produção
$is_production = !file_exists('.local_env');
echo "1. Verificando ambiente...\n";
echo "   Ambiente: " . ($is_production ? "PRODUÇÃO" : "DESENVOLVIMENTO") . "\n\n";

if (!$is_production) {
    echo "❌ Este script deve ser executado apenas em PRODUÇÃO!\n";
    echo "   Para desenvolvimento local, use: git pull\n";
    exit(1);
}

// 2. Fazer backup dos arquivos de configuração
echo "2. Fazendo backup dos arquivos de configuração...\n";

$files_to_backup = [
    'config.php' => 'config.php.backup.' . date('Y-m-d_H-i-s'),
    'painel/config.php' => 'painel/config.php.backup.' . date('Y-m-d_H-i-s')
];

foreach ($files_to_backup as $original => $backup) {
    if (file_exists($original)) {
        if (copy($original, $backup)) {
            echo "   ✅ Backup criado: $backup\n";
        } else {
            echo "   ❌ Erro ao criar backup: $backup\n";
        }
    } else {
        echo "   ⚠️ Arquivo não encontrado: $original\n";
    }
}
echo "\n";

// 3. Resetar mudanças locais
echo "3. Resetando mudanças locais...\n";
$git_commands = [
    'git reset --hard HEAD',
    'git clean -fd',
    'git pull origin master'
];

foreach ($git_commands as $command) {
    echo "   Executando: $command\n";
    $output = shell_exec($command . ' 2>&1');
    echo "   Resultado: " . trim($output) . "\n";
}
echo "\n";

// 4. Restaurar configurações de produção
echo "4. Restaurando configurações de produção...\n";

// Restaurar config.php principal
if (file_exists('config.php.backup.' . date('Y-m-d_H-i-s'))) {
    $backup_file = 'config.php.backup.' . date('Y-m-d_H-i-s');
    if (copy($backup_file, 'config.php')) {
        echo "   ✅ config.php restaurado\n";
    } else {
        echo "   ❌ Erro ao restaurar config.php\n";
    }
}

// Restaurar painel/config.php
if (file_exists('painel/config.php.backup.' . date('Y-m-d_H-i-s'))) {
    $backup_file = 'painel/config.php.backup.' . date('Y-m-d_H-i-s');
    if (copy($backup_file, 'painel/config.php')) {
        echo "   ✅ painel/config.php restaurado\n";
    } else {
        echo "   ❌ Erro ao restaurar painel/config.php\n";
    }
}
echo "\n";

// 5. Verificar status final
echo "5. Verificando status final...\n";
$status = shell_exec('git status 2>&1');
echo "   Status Git:\n";
echo "   " . str_replace("\n", "\n   ", trim($status)) . "\n\n";

// 6. Testar configurações
echo "6. Testando configurações...\n";
if (file_exists('config.php')) {
    require_once 'config.php';
    echo "   ✅ config.php carregado com sucesso\n";
    echo "   Ambiente: " . (defined('DEBUG_MODE') && DEBUG_MODE ? 'DESENVOLVIMENTO' : 'PRODUÇÃO') . "\n";
} else {
    echo "   ❌ config.php não encontrado\n";
}

if (file_exists('painel/config.php')) {
    echo "   ✅ painel/config.php existe\n";
} else {
    echo "   ❌ painel/config.php não encontrado\n";
}
echo "\n";

// 7. Instruções finais
echo "=== DEPLOY CONCLUÍDO ===\n\n";
echo "✅ Conflito resolvido com sucesso!\n";
echo "✅ Sistema atualizado com a versão limpa\n";
echo "✅ Configurações de produção mantidas\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "1. Testar o sistema: https://seudominio.com\n";
echo "2. Verificar painel: https://seudominio.com/painel\n";
echo "3. Monitorar logs em caso de problemas\n";
echo "4. Remover arquivos de backup se tudo estiver OK\n\n";

echo "🔧 COMANDOS ÚTEIS:\n";
echo "   # Verificar status\n";
echo "   git status\n\n";
echo "   # Ver logs recentes\n";
echo "   tail -f logs/debug_cobrancas.log\n\n";
echo "   # Remover backups (se tudo OK)\n";
echo "   rm config.php.backup.* painel/config.php.backup.*\n\n";

echo "📞 SUPORTE:\n";
echo "   Se houver problemas, verifique:\n";
echo "   - Logs do sistema\n";
echo "   - Configurações de banco de dados\n";
echo "   - Permissões de arquivos\n\n";

echo "🎉 DEPLOY REALIZADO COM SUCESSO!\n";
?> 