<?php
/**
 * Gerador de Scripts para VPS (MELHORADO)
 * Este script gera os comandos para criar os scripts bash na VPS
 */

echo "🔧 GERADOR DE SCRIPTS PARA VPS - VERSÃO MELHORADA\n";
echo "=================================================\n\n";

echo "📋 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "---------------------------------\n\n";

echo "1. Criar script de diagnóstico:\n";
echo "cat > /var/whatsapp-api/comandos_ssh_autenticacao.sh << 'EOF'\n";

// Ler o conteúdo do script de diagnóstico
$diagnostico_content = file_get_contents('comandos_ssh_autenticacao.sh');
echo $diagnostico_content;

echo "EOF\n\n";

echo "2. Criar script de regeneração:\n";
echo "cat > /var/whatsapp-api/regenerar_sessoes.sh << 'EOF'\n";

// Ler o conteúdo do script de regeneração
$regeneracao_content = file_get_contents('regenerar_sessoes.sh');
echo $regeneracao_content;

echo "EOF\n\n";

echo "3. Dar permissão de execução:\n";
echo "chmod +x /var/whatsapp-api/comandos_ssh_autenticacao.sh\n";
echo "chmod +x /var/whatsapp-api/regenerar_sessoes.sh\n\n";

echo "4. Executar diagnóstico:\n";
echo "bash /var/whatsapp-api/comandos_ssh_autenticacao.sh\n\n";

echo "5. Se necessário, regenerar sessões:\n";
echo "bash /var/whatsapp-api/regenerar_sessoes.sh\n\n";

echo "📋 COMANDOS ALTERNATIVOS MELHORADOS (SE OS SCRIPTS NÃO FUNCIONAREM):\n";
echo "--------------------------------------------------------------------\n\n";

echo "1. Verificar logs com filtros abrangentes:\n";
echo "pm2 logs whatsapp-3000 --lines 100 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'\n";
echo "pm2 logs whatsapp-3001 --lines 100 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'\n";
echo "pm2 logs whatsapp-3000 --lines 100 | grep -E '(AUTH_FAILURE|authenticated|disconnected)'\n";
echo "pm2 logs whatsapp-3001 --lines 100 | grep -E '(AUTH_FAILURE|authenticated|disconnected)'\n\n";

echo "2. Verificar permissões:\n";
echo "ls -la /var/whatsapp-api/sessions/\n";
echo "ls -la /var/whatsapp-api/sessions/default/\n";
echo "ls -la /var/whatsapp-api/sessions/comercial/\n\n";

echo "3. Corrigir permissões (detecção automática de usuário):\n";
echo "PM2_USER=\$(ps -o user= -p \$(pm2 pid whatsapp-3000))\n";
echo "chown -R \$PM2_USER:\$PM2_USER /var/whatsapp-api/sessions/\n";
echo "chmod -R 755 /var/whatsapp-api/sessions/\n";
echo "chmod -R 700 /var/whatsapp-api/sessions/default/\n";
echo "chmod -R 700 /var/whatsapp-api/sessions/comercial/\n\n";

echo "4. Testar conectividade (comandos combinados):\n";
echo "for p in 3000 3001; do echo \"Porta \$p:\"; curl -i http://212.85.11.238:\$p/status; done\n";
echo "curl -i http://212.85.11.238:3000/qr?session=default\n";
echo "curl -i http://212.85.11.238:3001/qr?session=comercial\n\n";

echo "5. Se houver AUTH_FAILURE, regenerar (com backup com timestamp):\n";
echo "pm2 stop whatsapp-3000 whatsapp-3001\n";
echo "BACKUP_DIR=\"/var/whatsapp-api/sessions_backup_\$(date +%Y%m%d_%H%M%S)\"\n";
echo "mkdir -p \"\$BACKUP_DIR\"\n";
echo "cp -r /var/whatsapp-api/sessions/* \"\$BACKUP_DIR/\"\n";
echo "rm -rf /var/whatsapp-api/sessions/default/*\n";
echo "rm -rf /var/whatsapp-api/sessions/comercial/*\n";
echo "pm2 start whatsapp-3000 whatsapp-3001\n";
echo "sleep 15\n";
echo "pm2 logs whatsapp-3000 --lines 20\n";
echo "pm2 logs whatsapp-3001 --lines 20\n\n";

echo "6. Monitoramento contínuo otimizado:\n";
echo "pm2 logs whatsapp-3000 --lines 0 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'\n";
echo "pm2 logs whatsapp-3001 --lines 0 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'\n\n";

echo "✅ Comandos melhorados gerados! Copie e cole na VPS.\n";
echo "🎯 Melhorias implementadas:\n";
echo "   - Filtros de log mais abrangentes\n";
echo "   - Detecção automática de usuário PM2\n";
echo "   - Backup com timestamp\n";
echo "   - Comandos combinados para eficiência\n";
echo "   - Testes de conectividade otimizados\n";
?> 