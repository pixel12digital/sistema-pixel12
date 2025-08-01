<?php
require_once 'config.php';

echo "🔍 VERIFICAÇÃO DE FIREWALL VPS\n";
echo "==============================\n\n";

echo "📋 INSTRUÇÕES PARA O VPS:\n";
echo "=========================\n";
echo "Execute estes comandos no VPS (SSH):\n\n";

echo "1. Verificar se a porta 3001 está ouvindo:\n";
echo "   netstat -tlnp | grep :3001\n\n";

echo "2. Verificar se o firewall está bloqueando:\n";
echo "   ufw status\n\n";

echo "3. Se a porta 3001 não estiver liberada, execute:\n";
echo "   ufw allow 3001\n\n";

echo "4. Verificar se o processo está rodando:\n";
echo "   pm2 show whatsapp-3001\n\n";

echo "5. Verificar logs do processo:\n";
echo "   pm2 logs whatsapp-3001 --lines 10\n\n";

echo "6. Testar localmente na VPS:\n";
echo "   curl http://localhost:3001/status\n\n";

echo "7. Se funcionar localmente mas não externamente, verificar:\n";
echo "   - Firewall da VPS\n";
echo "   - Configuração do provedor de hospedagem\n";
echo "   - Se a porta 3001 está aberta no painel da VPS\n\n";

echo "💡 POSSÍVEIS SOLUÇÕES:\n";
echo "=====================\n";
echo "1. Liberar porta no firewall: ufw allow 3001\n";
echo "2. Reiniciar o processo: pm2 restart whatsapp-3001\n";
echo "3. Verificar se o processo está na porta correta\n";
echo "4. Contatar o provedor da VPS para liberar a porta 3001\n\n";

echo "🔧 COMANDOS RÁPIDOS PARA EXECUTAR NO VPS:\n";
echo "=========================================\n";
echo "ufw allow 3001 && pm2 restart whatsapp-3001 && sleep 3 && curl http://localhost:3001/status\n\n";
?> 