<?php
echo "🔍 VERIFICANDO ARQUIVO WHATSAPP-API-SERVER.JS\n";
echo "=============================================\n\n";

$vps_ip = '212.85.11.238';

// Comandos para verificar o arquivo na VPS
echo "🔧 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "================================\n\n";

echo "1. Conectar na VPS:\n";
echo "   ssh root@{$vps_ip}\n\n";

echo "2. Verificar se o endpoint /send existe no servidor 3000:\n";
echo "   cd /var/whatsapp-api\n";
echo "   grep -n 'app.post.*send' whatsapp-api-server.js\n\n";

echo "3. Verificar se o endpoint /send existe no servidor 3001:\n";
echo "   cd /var/whatsapp-api-3001\n";
echo "   grep -n 'app.post.*send' whatsapp-api-server.js\n\n";

echo "4. Se não existir, copiar do servidor original:\n";
echo "   cd /var/whatsapp-api-3001\n";
echo "   cp ../whatsapp-api/whatsapp-api-server.js .\n";
echo "   sed -i 's/const PORT = 3000/const PORT = 3001/' whatsapp-api-server.js\n\n";

echo "5. Reiniciar servidor 3001:\n";
echo "   pm2 restart whatsapp-3001\n";
echo "   pm2 save\n\n";

echo "6. Verificar se o endpoint /send está funcionando:\n";
echo "   curl -X POST http://{$vps_ip}:3001/send \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"to\":\"test@c.us\",\"message\":\"test\"}'\n\n";

echo "🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Execute os comandos acima na VPS\n";
echo "2. Depois execute: php corrigir_canal_3001_completo.php\n";
echo "3. Teste enviar uma mensagem para o canal 3001\n\n";

echo "📋 RESUMO DO PROBLEMA:\n";
echo "=====================\n";
echo "- O servidor 3001 não tem o endpoint /send implementado\n";
echo "- Isso impede o envio de mensagens via API\n";
echo "- O webhook está funcionando, mas não consegue identificar o canal\n";
echo "- Precisamos copiar o arquivo correto do servidor original\n\n";

echo "🎯 VERIFICAÇÃO CONCLUÍDA!\n";
?> 