<?php
/**
 * Diagnóstico Avançado do VPS WhatsApp
 * Identifica como o serviço está configurado e como iniciá-lo
 */

echo "=== DIAGNÓSTICO AVANÇADO VPS WHATSAPP ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// URLs dos VPSs
$vps_urls = [
    '3000' => 'http://212.85.11.238:3000',
    '3001' => 'http://212.85.11.238:3001'
];

echo "🔍 INSTRUÇÕES PARA DIAGNOSTICAR O VPS:\n";
echo "Execute os seguintes comandos no VPS (212.85.11.238):\n\n";

echo "1. 🔍 VERIFICAR PROCESSOS ATIVOS:\n";
echo "   ps aux | grep -i whatsapp\n";
echo "   ps aux | grep -i node\n";
echo "   ps aux | grep -i 3000\n";
echo "   ps aux | grep -i 3001\n\n";

echo "2. 🔍 VERIFICAR PORTAS EM USO:\n";
echo "   netstat -tlnp | grep :3000\n";
echo "   netstat -tlnp | grep :3001\n";
echo "   lsof -i :3000\n";
echo "   lsof -i :3001\n\n";

echo "3. 🔍 VERIFICAR DIRETÓRIOS DO SERVIÇO:\n";
echo "   find /var -name '*whatsapp*' -type d 2>/dev/null\n";
echo "   find /opt -name '*whatsapp*' -type d 2>/dev/null\n";
echo "   find /home -name '*whatsapp*' -type d 2>/dev/null\n";
echo "   ls -la /var/whatsapp-api/\n\n";

echo "4. 🔍 VERIFICAR SERVIÇOS DO SYSTEMD:\n";
echo "   systemctl list-units --type=service | grep -i whatsapp\n";
echo "   systemctl list-units --type=service | grep -i node\n\n";

echo "5. 🔍 VERIFICAR PM2:\n";
echo "   pm2 list\n";
echo "   pm2 status\n\n";

echo "6. 🔍 VERIFICAR LOGS:\n";
echo "   journalctl -u whatsapp* --no-pager -n 50\n";
echo "   tail -f /var/log/whatsapp*.log 2>/dev/null\n";
echo "   tail -f /var/whatsapp-api/logs/*.log 2>/dev/null\n\n";

echo "7. 🔍 VERIFICAR CONFIGURAÇÕES:\n";
echo "   cat /etc/systemd/system/whatsapp*.service 2>/dev/null\n";
echo "   cat /var/whatsapp-api/package.json 2>/dev/null\n";
echo "   cat /var/whatsapp-api/config.json 2>/dev/null\n\n";

echo "=== COMANDOS PARA INICIAR O SERVIÇO ===\n\n";

echo "OPÇÃO 1: Iniciar manualmente (se encontrar o diretório):\n";
echo "   cd /var/whatsapp-api\n";
echo "   npm start\n";
echo "   # ou\n";
echo "   node index.js\n";
echo "   # ou\n";
echo "   node app.js\n\n";

echo "OPÇÃO 2: Usar PM2 (se instalado):\n";
echo "   cd /var/whatsapp-api\n";
echo "   pm2 start index.js --name whatsapp-multi-session\n";
echo "   pm2 save\n";
echo "   pm2 startup\n\n";

echo "OPÇÃO 3: Criar serviço systemd:\n";
echo "   sudo nano /etc/systemd/system/whatsapp-multi-session.service\n";
echo "   # Conteúdo do arquivo:\n";
echo "   [Unit]\n";
echo "   Description=WhatsApp Multi-Session API\n";
echo "   After=network.target\n\n";
echo "   [Service]\n";
echo "   Type=simple\n";
echo "   User=root\n";
echo "   WorkingDirectory=/var/whatsapp-api\n";
echo "   ExecStart=/usr/bin/node index.js\n";
echo "   Restart=always\n";
echo "   RestartSec=10\n\n";
echo "   [Install]\n";
echo "   WantedBy=multi-user.target\n\n";
echo "   # Depois:\n";
echo "   systemctl daemon-reload\n";
echo "   systemctl enable whatsapp-multi-session\n";
echo "   systemctl start whatsapp-multi-session\n\n";

echo "=== TESTE APÓS INICIAR ===\n\n";
echo "Após iniciar o serviço, teste:\n";
echo "   curl http://localhost:3000/status\n";
echo "   curl http://localhost:3001/status\n";
echo "   curl http://212.85.11.238:3000/status\n";
echo "   curl http://212.85.11.238:3001/status\n\n";

echo "=== RESULTADO ESPERADO ===\n";
echo "Se o serviço estiver funcionando, você deve ver:\n";
echo "   {\n";
echo "     \"success\": true,\n";
echo "     \"ready\": true,\n";
echo "     \"clients_status\": {}\n";
echo "   }\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Execute os comandos de diagnóstico acima\n";
echo "2. Identifique como o serviço está configurado\n";
echo "3. Use o comando apropriado para iniciá-lo\n";
echo "4. Teste a conectividade\n";
echo "5. Volte ao painel e tente conectar o WhatsApp\n\n";

echo "=== FIM DO DIAGNÓSTICO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
?> 