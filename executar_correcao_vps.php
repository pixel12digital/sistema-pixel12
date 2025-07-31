<?php
/**
 * Executar Correção VPS
 * Executa comandos na VPS para corrigir o endpoint /send
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "<h2>🔧 Executar Correção na VPS</h2>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

$vps_url = WHATSAPP_ROBOT_URL;
$vps_ip = '212.85.11.238';

echo "<h3>1. Informações da VPS</h3>";
echo "<ul>";
echo "<li>IP: $vps_ip</li>";
echo "<li>URL: $vps_url</li>";
echo "<li>Porta: 3000</li>";
echo "</ul>";

echo "<h3>2. Comandos para Executar na VPS</h3>";
echo "<div style='background: #e0e7ff; padding: 15px; border-radius: 5px; font-family: monospace;'>";

echo "<strong>Passo 1: Conectar na VPS via SSH</strong><br>";
echo "ssh root@$vps_ip<br><br>";

echo "<strong>Passo 2: Navegar para o diretório do WhatsApp API</strong><br>";
echo "cd /var/whatsapp-api<br><br>";

echo "<strong>Passo 3: Fazer backup do arquivo atual</strong><br>";
echo "cp whatsapp-api-server.js whatsapp-api-server.js.backup." . date('Ymd_His') . "<br><br>";

echo "<strong>Passo 4: Adicionar o endpoint /send</strong><br>";
echo "cat >> whatsapp-api-server.js << 'EOF'<br>";
echo "<br>";
echo "// Endpoint para envio de mensagens WhatsApp<br>";
echo "app.post('/send', async (req, res) => {<br>";
echo "    try {<br>";
echo "        const { to, message } = req.body;<br>";
echo "        <br>";
echo "        // Validar parâmetros<br>";
echo "        if (!to || !message) {<br>";
echo "            return res.status(400).json({<br>";
echo "                success: false,<br>";
echo "                error: 'Parâmetros obrigatórios: to, message'<br>";
echo "            });<br>";
echo "        }<br>";
echo "        <br>";
echo "        console.log(\`[SEND] Tentando enviar mensagem para \${to}: \${message}\`);<br>";
echo "        <br>";
echo "        // Verificar se o cliente está conectado<br>";
echo "        const client = whatsappClients['default'];<br>";
echo "        if (!client || !clientStatus['default'] || clientStatus['default'].status !== 'connected') {<br>";
echo "            return res.status(503).json({<br>";
echo "                success: false,<br>";
echo "                error: 'WhatsApp não está conectado'<br>";
echo "            });<br>";
echo "        }<br>";
echo "        <br>";
echo "        // Formatar número<br>";
echo "        let formattedNumber = to;<br>";
echo "        if (!formattedNumber.includes('@')) {<br>";
echo "            formattedNumber = formattedNumber + '@c.us';<br>";
echo "        }<br>";
echo "        <br>";
echo "        // Enviar mensagem<br>";
echo "        const result = await client.sendMessage(formattedNumber, message);<br>";
echo "        <br>";
echo "        console.log(\`[SEND] Mensagem enviada com sucesso. ID: \${result.id._serialized}\`);<br>";
echo "        <br>";
echo "        res.json({<br>";
echo "            success: true,<br>";
echo "            messageId: result.id._serialized,<br>";
echo "            message: 'Mensagem enviada com sucesso'<br>";
echo "        });<br>";
echo "        <br>";
echo "    } catch (error) {<br>";
echo "        console.error('[SEND] Erro ao enviar mensagem:', error);<br>";
echo "        res.status(500).json({<br>";
echo "            success: false,<br>";
echo "            error: error.message || 'Erro interno do servidor'<br>";
echo "        });<br>";
echo "    }<br>";
echo "});<br>";
echo "EOF<br><br>";

echo "<strong>Passo 5: Reiniciar o servidor</strong><br>";
echo "pm2 restart whatsapp-bot<br><br>";

echo "<strong>Passo 6: Verificar se está funcionando</strong><br>";
echo "curl -X GET http://localhost:3000/status<br><br>";

echo "<strong>Passo 7: Testar o novo endpoint</strong><br>";
echo "curl -X POST http://localhost:3000/send \\<br>";
echo "  -H 'Content-Type: application/json' \\<br>";
echo "  -d '{\"to\":\"554797146908\",\"message\":\"Teste após correção\"}'<br><br>";

echo "<strong>Passo 8: Verificar logs</strong><br>";
echo "pm2 logs whatsapp-bot --lines 20<br><br>";

echo "</div>";

echo "<h3>3. Script Automatizado (Alternativa)</h3>";
echo "<div style='background: #fef3c7; padding: 15px; border-radius: 5px;'>";
echo "<p>Se preferir, você pode usar o script automatizado:</p>";
echo "<pre style='background: #f3f4f6; padding: 10px; border-radius: 5px;'>";
echo "wget -O adicionar_endpoint_send.sh https://raw.githubusercontent.com/seu-repo/adicionar_endpoint_send.sh<br>";
echo "chmod +x adicionar_endpoint_send.sh<br>";
echo "./adicionar_endpoint_send.sh<br>";
echo "</pre>";
echo "</div>";

echo "<h3>4. Verificação Pós-Correção</h3>";
echo "<div style='background: #d1fae5; padding: 15px; border-radius: 5px;'>";
echo "<p>Após executar os comandos, execute novamente o teste:</p>";
echo "<pre style='background: #f3f4f6; padding: 10px; border-radius: 5px;'>";
echo "php teste_canal_financeiro_vps.php<br>";
echo "</pre>";
echo "</div>";

echo "<h3>5. Troubleshooting</h3>";
echo "<div style='background: #fee2e2; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Se algo der errado:</strong></p>";
echo "<ul>";
echo "<li>Verificar se o arquivo whatsapp-api-server.js existe</li>";
echo "<li>Verificar se o PM2 está rodando: <code>pm2 status</code></li>";
echo "<li>Verificar logs: <code>pm2 logs whatsapp-bot</code></li>";
echo "<li>Restaurar backup se necessário</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Instruções concluídas em:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Próximo passo:</strong> Execute os comandos na VPS e depois teste novamente.</p>";
?> 