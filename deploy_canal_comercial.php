<?php
/**
 * DEPLOY CANAL COMERCIAL - VPS
 * 
 * Este script faz o deploy dos arquivos do canal comercial para o VPS
 */

echo "🚀 DEPLOY CANAL COMERCIAL - VPS\n";
echo "===============================\n\n";

// Configurações
$vps_ip = '212.85.11.238';
$vps_user = 'root';
$vps_path = '/var/www/html/loja-virtual-revenda';

echo "📊 CONFIGURAÇÕES:\n";
echo "  VPS: $vps_ip\n";
echo "  Usuário: $vps_user\n";
echo "  Caminho: $vps_path\n\n";

// 1. Verificar arquivos locais
echo "🔍 VERIFICANDO ARQUIVOS LOCAIS:\n";
$arquivos_necessarios = [
    'api/webhook_canal_37.php' => 'Webhook específico do canal comercial',
    'canais/comercial/canal_config.php' => 'Configuração do canal',
    'canais/comercial/teste_final.php' => 'Script de teste',
    'painel/receber_mensagem.php' => 'Processamento de mensagens (atualizado)',
    'painel/api/mensagens_cliente.php' => 'API de mensagens (atualizada)'
];

foreach ($arquivos_necessarios as $arquivo => $descricao) {
    if (file_exists(__DIR__ . '/' . $arquivo)) {
        $tamanho = filesize(__DIR__ . '/' . $arquivo);
        echo "  ✅ $arquivo - $descricao ($tamanho bytes)\n";
    } else {
        echo "  ❌ $arquivo - $descricao (não encontrado)\n";
    }
}

// 2. Gerar comandos para deploy
echo "\n📋 COMANDOS PARA DEPLOY:\n";
echo "Execute os seguintes comandos no VPS:\n\n";

echo "# 1. Conectar ao VPS\n";
echo "ssh $vps_user@$vps_ip\n\n";

echo "# 2. Navegar para o diretório\n";
echo "cd $vps_path\n\n";

echo "# 3. Fazer backup dos arquivos existentes\n";
echo "cp api/webhook_whatsapp.php api/webhook_whatsapp.php.backup\n";
echo "cp painel/receber_mensagem.php painel/receber_mensagem.php.backup\n";
echo "cp painel/api/mensagens_cliente.php painel/api/mensagens_cliente.php.backup\n\n";

echo "# 4. Criar diretório do canal comercial (se não existir)\n";
echo "mkdir -p canais/comercial\n\n";

echo "# 5. Copiar arquivos (você pode usar scp ou rsync)\n";
echo "# Opção 1 - Usar scp (execute no seu computador local):\n";
echo "scp api/webhook_canal_37.php $vps_user@$vps_ip:$vps_path/api/\n";
echo "scp canais/comercial/canal_config.php $vps_user@$vps_ip:$vps_path/canais/comercial/\n";
echo "scp canais/comercial/teste_final.php $vps_user@$vps_ip:$vps_path/canais/comercial/\n";
echo "scp painel/receber_mensagem.php $vps_user@$vps_ip:$vps_path/painel/\n";
echo "scp painel/api/mensagens_cliente.php $vps_user@$vps_ip:$vps_path/painel/api/\n\n";

echo "# 6. Definir permissões corretas\n";
echo "chmod 644 api/webhook_canal_37.php\n";
echo "chmod 644 canais/comercial/canal_config.php\n";
echo "chmod 644 canais/comercial/teste_final.php\n";
echo "chmod 644 painel/receber_mensagem.php\n";
echo "chmod 644 painel/api/mensagens_cliente.php\n\n";

echo "# 7. Verificar se os arquivos foram copiados\n";
echo "ls -la api/webhook_canal_37.php\n";
echo "ls -la canais/comercial/\n\n";

echo "# 8. Testar o webhook\n";
echo "curl -X POST https://app.pixel12digital.com.br/api/webhook_canal_37.php \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"from\":\"554797146908@c.us\",\"to\":\"4797309525@c.us\",\"body\":\"Teste deploy\"}'\n\n";

echo "# 9. Verificar logs\n";
echo "tail -f /var/log/nginx/error.log\n";
echo "tail -f $vps_path/logs/webhook_whatsapp_" . date('Y-m-d') . ".log\n\n";

// 3. Gerar script de deploy automático
echo "📄 SCRIPT DE DEPLOY AUTOMÁTICO:\n";
echo "Crie um arquivo 'deploy.sh' no VPS com o seguinte conteúdo:\n\n";

echo "#!/bin/bash\n";
echo "echo '🚀 Deploy Canal Comercial'\n";
echo "cd $vps_path\n\n";
echo "# Backup\n";
echo "cp api/webhook_whatsapp.php api/webhook_whatsapp.php.backup\n";
echo "cp painel/receber_mensagem.php painel/receber_mensagem.php.backup\n";
echo "cp painel/api/mensagens_cliente.php painel/api/mensagens_cliente.php.backup\n\n";
echo "# Criar diretórios\n";
echo "mkdir -p canais/comercial\n\n";
echo "# Definir permissões\n";
echo "chmod 644 api/webhook_canal_37.php\n";
echo "chmod 644 canais/comercial/canal_config.php\n";
echo "chmod 644 canais/comercial/teste_final.php\n";
echo "chmod 644 painel/receber_mensagem.php\n";
echo "chmod 644 painel/api/mensagens_cliente.php\n\n";
echo "# Testar\n";
echo "php -l api/webhook_canal_37.php\n";
echo "php -l canais/comercial/canal_config.php\n";
echo "echo '✅ Deploy concluído!'\n";

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. Execute os comandos de deploy no VPS\n";
echo "2. Teste o webhook após o deploy\n";
echo "3. Verifique se as mensagens estão sendo salvas no banco correto\n";
echo "4. Configure o VPS para usar o webhook específico do canal comercial\n";

echo "\n🌐 LINKS ÚTEIS:\n";
echo "• VPS SSH: ssh $vps_user@$vps_ip\n";
echo "• Webhook: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";
echo "• Status API: http://$vps_ip:3001/status\n";
echo "• phpMyAdmin: https://auth-db1607.hstgr.io/index.php?route=/sql&pos=0&db=u342734079_wts_com_pixel&table=mensagens_comunicacao\n";
?> 