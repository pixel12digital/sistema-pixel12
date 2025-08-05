<?php
/**
 * DIAGNÓSTICO COMPLETO VPS - WHATSAPP
 * Script para diagnosticar e corrigir problemas no VPS
 */

echo "🔧 DIAGNÓSTICO COMPLETO VPS - WHATSAPP\n";
echo "======================================\n\n";

echo "🚨 PROBLEMA IDENTIFICADO NO VPS:\n";
echo "================================\n";
echo "   PM2 Error: Script not found: /var/whatsapp-api/whatsapp-multi-session\n\n";

echo "📋 COMANDOS PARA EXECUTAR NO VPS:\n";
echo "=================================\n\n";

echo "1. 🔍 VERIFICAR ESTRUTURA DE DIRETÓRIOS:\n";
echo "   ssh root@212.85.11.238\n";
echo "   ls -la /var/\n";
echo "   ls -la /var/whatsapp-api/\n";
echo "   ls -la /root/\n\n";

echo "2. 🔍 ENCONTRAR ARQUIVOS DO WHATSAPP:\n";
echo "   find /var -name '*whatsapp*' -type f\n";
echo "   find /root -name '*whatsapp*' -type f\n";
echo "   find /opt -name '*whatsapp*' -type f\n\n";

echo "3. 🔍 VERIFICAR PROCESSOS ATIVOS:\n";
echo "   ps aux | grep whatsapp\n";
echo "   ps aux | grep node\n";
echo "   netstat -tulpn | grep :3000\n";
echo "   netstat -tulpn | grep :3001\n\n";

echo "4. 🔧 VERIFICAR CONFIGURAÇÃO PM2:\n";
echo "   pm2 list\n";
echo "   pm2 show whatsapp-multi-session\n";
echo "   pm2 logs\n\n";

echo "5. 🔧 POSSÍVEIS SOLUÇÕES:\n";
echo "========================\n\n";

echo "OPÇÃO A - Se o arquivo existe em outro local:\n";
echo "   # Encontrar o arquivo correto\n";
echo "   find / -name 'whatsapp-api-server.js' 2>/dev/null\n";
echo "   find / -name 'app.js' 2>/dev/null | grep whatsapp\n\n";
echo "   # Atualizar configuração PM2\n";
echo "   pm2 delete whatsapp-multi-session\n";
echo "   pm2 start /caminho/correto/para/app.js --name whatsapp-multi-session\n\n";

echo "OPÇÃO B - Reinstalar o serviço WhatsApp:\n";
echo "   # Parar todos os processos\n";
echo "   pm2 stop all\n";
echo "   pm2 delete all\n\n";
echo "   # Ir para diretório de trabalho\n";
echo "   cd /var/whatsapp-api\n\n";
echo "   # Se não existir, criar\n";
echo "   mkdir -p /var/whatsapp-api\n";
echo "   cd /var/whatsapp-api\n\n";
echo "   # Baixar e instalar WhatsApp API\n";
echo "   git clone https://github.com/chrishubert/whatsapp-api.git .\n";
echo "   npm install\n\n";
echo "   # Iniciar com PM2\n";
echo "   pm2 start app.js --name whatsapp-multi-session\n\n";

echo "OPÇÃO C - Usar configuração alternativa:\n";
echo "   # Criar arquivo de configuração PM2\n";
echo "   cat > /var/whatsapp-api/ecosystem.config.js << 'EOF'\n";
echo "module.exports = {\n";
echo "  apps: [{\n";
echo "    name: 'whatsapp-multi-session',\n";
echo "    script: './app.js',\n";
echo "    instances: 1,\n";
echo "    autorestart: true,\n";
echo "    watch: false,\n";
echo "    max_memory_restart: '1G',\n";
echo "    env: {\n";
echo "      NODE_ENV: 'production',\n";
echo "      PORT: 3000\n";
echo "    }\n";
echo "  }]\n";
echo "};\n";
echo "EOF\n\n";
echo "   # Iniciar com o arquivo de configuração\n";
echo "   pm2 start ecosystem.config.js\n\n";

echo "6. 🧪 TESTAR APÓS CORREÇÃO:\n";
echo "==========================\n";
echo "   # Verificar se está rodando\n";
echo "   pm2 status\n";
echo "   pm2 logs whatsapp-multi-session --lines 20\n\n";
echo "   # Testar endpoints\n";
echo "   curl http://localhost:3000/status\n";
echo "   curl http://localhost:3001/status\n\n";
echo "   # Verificar portas\n";
echo "   netstat -tulpn | grep :3000\n";
echo "   netstat -tulpn | grep :3001\n\n";

echo "7. 🔧 COMANDOS ÚTEIS PARA DEBUG:\n";
echo "===============================\n";
echo "   # Ver logs em tempo real\n";
echo "   pm2 logs whatsapp-multi-session --follow\n\n";
echo "   # Reiniciar aplicação\n";
echo "   pm2 restart whatsapp-multi-session\n\n";
echo "   # Salvar configuração PM2\n";
echo "   pm2 save\n";
echo "   pm2 startup\n\n";

echo "8. 📱 VERIFICAÇÃO FINAL:\n";
echo "=======================\n";
echo "   Após executar os comandos acima:\n";
echo "   1. Execute o script 'testar_status_final.php' novamente\n";
echo "   2. Verifique se as portas 3000 e 3001 respondem com 'Ready: SIM'\n";
echo "   3. Acesse o painel de comunicação\n";
echo "   4. Os status devem mudar de 'Verificando...' para 'Conectado' ou 'Pendente'\n\n";

echo "⚠️  NOTAS IMPORTANTES:\n";
echo "=====================\n";
echo "   • Execute os comandos como root no VPS\n";
echo "   • Se usar OPÇÃO B, certifique-se de ter Node.js instalado\n";
echo "   • Backup de configurações antes de reinstalar\n";
echo "   • Verifique firewall se as portas não responderem\n\n";

echo "✅ DIAGNÓSTICO CONCLUÍDO!\n";
echo "Execute os comandos no VPS conforme necessário.\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 