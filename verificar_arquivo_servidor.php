<?php
/**
 * Verificar Arquivo do Servidor WhatsApp
 */

echo "=== VERIFICAÇÃO DO ARQUIVO DO SERVIDOR ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

echo "🔍 INSTRUÇÕES PARA VERIFICAR O ARQUIVO DO SERVIDOR:\n";
echo "Execute os seguintes comandos no VPS (212.85.11.238):\n\n";

echo "1. 🔍 Verificar o conteúdo do arquivo principal:\n";
echo "   cd /var/whatsapp-api\n";
echo "   head -50 whatsapp-api-server.js\n\n";

echo "2. 🔍 Verificar se há endpoints definidos:\n";
echo "   grep -n 'app.get\\|app.post\\|app.put\\|app.delete' whatsapp-api-server.js\n\n";

echo "3. 🔍 Verificar se há rotas de QR Code:\n";
echo "   grep -i 'qr\\|qrcode' whatsapp-api-server.js\n\n";

echo "4. 🔍 Verificar se há rotas de sessão:\n";
echo "   grep -i 'session\\|init\\|start\\|create' whatsapp-api-server.js\n\n";

echo "5. 🔍 Verificar se há rotas de status:\n";
echo "   grep -i 'status' whatsapp-api-server.js\n\n";

echo "6. 🔍 Verificar se há rotas de logout/disconnect:\n";
echo "   grep -i 'logout\\|disconnect' whatsapp-api-server.js\n\n";

echo "7. 🔍 Verificar se há rotas de envio de mensagem:\n";
echo "   grep -i 'send\\|message' whatsapp-api-server.js\n\n";

echo "8. 🔍 Verificar se há webhooks:\n";
echo "   grep -i 'webhook' whatsapp-api-server.js\n\n";

echo "9. 🔍 Verificar se há configuração de porta:\n";
echo "   grep -i 'listen\\|port' whatsapp-api-server.js\n\n";

echo "10. 🔍 Verificar se há configuração de CORS:\n";
echo "    grep -i 'cors' whatsapp-api-server.js\n\n";

echo "=== ANÁLISE ESPERADA ===\n";
echo "O arquivo deve conter:\n";
echo "- Express.js setup\n";
echo "- Definição de rotas (app.get, app.post, etc.)\n";
echo "- Endpoints para QR Code\n";
echo "- Endpoints para sessões\n";
echo "- Endpoints para envio de mensagens\n";
echo "- Configuração de porta\n\n";

echo "=== SE O ARQUIVO ESTIVER INCOMPLETO ===\n";
echo "Se o arquivo não tiver os endpoints necessários:\n\n";

echo "1. 🔄 Fazer backup do arquivo atual:\n";
echo "   cp whatsapp-api-server.js whatsapp-api-server.js.backup.$(date +%Y%m%d_%H%M%S)\n\n";

echo "2. 🔍 Verificar se há outros arquivos de servidor:\n";
echo "   ls -la *.js | grep -i server\n";
echo "   ls -la *.js | grep -i api\n\n";

echo "3. 🔍 Verificar se há arquivo de configuração:\n";
echo "   cat ecosystem.config.js\n";
echo "   cat package.json\n\n";

echo "4. 🔄 Se necessário, usar um arquivo de backup:\n";
echo "   ls -la *.backup*\n";
echo "   cp whatsapp-api-server.js.backup whatsapp-api-server.js\n\n";

echo "5. 🔄 Reiniciar o serviço após correção:\n";
echo "   pkill -f whatsapp-api-server\n";
echo "   sleep 3\n";
echo "   node whatsapp-api-server.js &\n\n";

echo "=== TESTE APÓS CORREÇÃO ===\n";
echo "Após corrigir o arquivo, teste:\n";
echo "   curl http://localhost:3000/status\n";
echo "   curl http://localhost:3000/qr\n";
echo "   curl http://localhost:3001/status\n";
echo "   curl http://localhost:3001/qr\n\n";

echo "=== FIM DA VERIFICAÇÃO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 