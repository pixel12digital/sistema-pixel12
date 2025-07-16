<?php
/**
 * Script de Deploy Automático para Hostinger
 * 
 * Execute apenas na Hostinger para atualizar o código
 * mantendo as configurações de produção intactas.
 * 
 * URL: https://seusite.com/deploy_hostinger.php?key=SUA_CHAVE_SECRETA
 */

// Chave de segurança simples
$deploy_key = 'loja_virtual_2025';
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $deploy_key) {
    http_response_code(403);
    die('❌ Acesso negado. Chave incorreta.');
}

echo "<h1>🚀 Deploy Automático - Hostinger</h1>\n";
echo "<pre>\n";

try {
    // 1. Backup dos arquivos de configuração
    echo "📋 1. Fazendo backup das configurações...\n";
    
    $configs_backup = [];
    
    if (file_exists('config.php')) {
        copy('config.php', 'config.backup.php');
        $configs_backup[] = 'config.php';
        echo "✅ config.php -> config.backup.php\n";
    }
    
    if (file_exists('painel/config.php')) {
        copy('painel/config.php', 'painel/config.backup.php');
        $configs_backup[] = 'painel/config.php';
        echo "✅ painel/config.php -> painel/config.backup.php\n";
    }
    
    // 2. Atualizar código via Git
    echo "\n🔄 2. Atualizando código do repositório...\n";
    
    // Verificar se git está disponível
    $git_available = shell_exec('which git') || shell_exec('git --version');
    
    if ($git_available) {
        // Reset para garantir que não há conflitos
        exec('git reset --hard HEAD 2>&1', $reset_output, $reset_code);
        echo "📋 Git reset: " . implode("\n", $reset_output) . "\n";
        
        // Pull do repositório
        exec('git pull origin main 2>&1', $pull_output, $pull_code);
        echo "📥 Git pull: " . implode("\n", $pull_output) . "\n";
        
        if ($pull_code === 0) {
            echo "✅ Código atualizado com sucesso!\n";
        } else {
            throw new Exception("Erro no git pull. Código: $pull_code");
        }
    } else {
        echo "⚠️ Git não disponível. Atualize manualmente via FileManager.\n";
    }
    
    // 3. Restaurar configurações
    echo "\n🔧 3. Restaurando configurações de produção...\n";
    
    foreach ($configs_backup as $config) {
        $backup_file = str_replace('.php', '.backup.php', $config);
        
        if (file_exists($backup_file)) {
            copy($backup_file, $config);
            unlink($backup_file);
            echo "✅ Restaurado: $config\n";
        }
    }
    
    // 4. Verificar permissões
    echo "\n🔐 4. Verificando permissões...\n";
    
    $folders_to_check = [
        'painel/cache/' => '755',
        'logs/' => '755'
    ];
    
    foreach ($folders_to_check as $folder => $permission) {
        if (is_dir($folder)) {
            chmod($folder, octdec($permission));
            echo "✅ $folder -> $permission\n";
        } else {
            echo "⚠️ Pasta não encontrada: $folder\n";
        }
    }
    
    // 5. Limpar cache
    echo "\n🧹 5. Limpando cache do sistema...\n";
    
    if (file_exists('painel/cache_cleanup.php')) {
        // Definir variáveis necessárias para o script
        $_GET['action'] = 'optimize';
        
        ob_start();
        include 'painel/cache_cleanup.php';
        $cache_output = ob_get_clean();
        
        echo "✅ Cache limpo: $cache_output\n";
    } else {
        echo "⚠️ Script de limpeza de cache não encontrado\n";
    }
    
    // 6. Verificar conectividade essencial
    echo "\n🔍 6. Verificando conectividade...\n";
    
    // Verificar banco de dados
    if (file_exists('painel/config.php') && file_exists('painel/db.php')) {
        try {
            require_once 'painel/config.php';
            require_once 'painel/db.php';
            
            if (isset($mysqli) && $mysqli->ping()) {
                echo "✅ Banco de dados: Conectado\n";
            } else {
                echo "❌ Banco de dados: Erro de conexão\n";
            }
        } catch (Exception $e) {
            echo "❌ Banco de dados: " . $e->getMessage() . "\n";
        }
    }
    
    // Verificar WhatsApp Robot (se rodando)
    $robot_url = 'http://localhost:3000/status';
    $robot_context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'ignore_errors' => true
        ]
    ]);
    
    $robot_response = @file_get_contents($robot_url, false, $robot_context);
    if ($robot_response) {
        echo "✅ WhatsApp Robot: Online\n";
    } else {
        echo "⚠️ WhatsApp Robot: Offline (normal se não estiver rodando)\n";
    }
    
    // 7. Relatório final
    echo "\n📊 7. Relatório do Deploy\n";
    echo "=====================================\n";
    echo "⏰ Data/Hora: " . date('Y-m-d H:i:s') . "\n";
    echo "🌐 Servidor: " . $_SERVER['SERVER_NAME'] . "\n";
    echo "📁 Diretório: " . getcwd() . "\n";
    echo "🔧 PHP: " . PHP_VERSION . "\n";
    echo "💾 Configs restaurados: " . count($configs_backup) . "\n";
    echo "=====================================\n";
    
    echo "\n🎉 DEPLOY CONCLUÍDO COM SUCESSO!\n";
    echo "\n🔗 Próximos passos:\n";
    echo "1. Testar o site: https://" . $_SERVER['SERVER_NAME'] . "\n";
    echo "2. Verificar chat: https://" . $_SERVER['SERVER_NAME'] . "/painel/chat.php\n";
    echo "3. Monitorar logs: https://" . $_SERVER['SERVER_NAME'] . "/painel/cache_cleanup.php?action=report\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO NO DEPLOY:\n";
    echo $e->getMessage() . "\n";
    
    // Tentar restaurar backup em caso de erro
    echo "\n🔧 Tentando restaurar backup...\n";
    
    if (file_exists('config.backup.php')) {
        copy('config.backup.php', 'config.php');
        unlink('config.backup.php');
        echo "✅ config.php restaurado\n";
    }
    
    if (file_exists('painel/config.backup.php')) {
        copy('painel/config.backup.php', 'painel/config.php');
        unlink('painel/config.backup.php');
        echo "✅ painel/config.php restaurado\n";
    }
    
    echo "\n⚠️ Verifique os erros e tente novamente.\n";
}

echo "</pre>\n";

// Log do deploy
$log_entry = date('Y-m-d H:i:s') . " - Deploy executado de " . $_SERVER['REMOTE_ADDR'] . "\n";
file_put_contents('logs/deploy.log', $log_entry, FILE_APPEND | LOCK_EX);
?> 