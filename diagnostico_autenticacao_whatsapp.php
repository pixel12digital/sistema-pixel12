<?php
/**
 * Diagnóstico e Correção - Problemas de Autenticação WhatsApp (MELHORADO)
 */

echo "🔍 DIAGNÓSTICO DE AUTENTICAÇÃO WHATSAPP - VERSÃO MELHORADA\n";
echo "==========================================================\n\n";

echo "📋 PASSO 1: VERIFICAR LOGS DETALHADOS\n";
echo "-------------------------------------\n";

echo "⚠️ ATENÇÃO: Execute os seguintes comandos na VPS para verificar logs:\n\n";

echo "1. Verificar logs em tempo real (com filtros específicos):\n";
echo "   pm2 logs whatsapp-3000 --lines 100 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST)'\n";
echo "   pm2 logs whatsapp-3001 --lines 100 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST)'\n\n";

echo "2. Verificar usuário do processo PM2:\n";
echo "   ps aux | grep whatsapp-api-server\n";
echo "   pm2 describe whatsapp-3000\n";
echo "   pm2 describe whatsapp-3001\n\n";

echo "3. Verificar permissões das sessões:\n";
echo "   ls -la /var/whatsapp-api/sessions/\n";
echo "   ls -la /var/whatsapp-api/sessions/default/\n";
echo "   ls -la /var/whatsapp-api/sessions/comercial/\n\n";

echo "4. Verificar se há arquivos corrompidos:\n";
echo "   find /var/whatsapp-api/sessions/ -name '*.json' -exec ls -la {} \\;\n";
echo "   find /var/whatsapp-api/sessions/ -name '*.json' -exec file {} \\;\n\n";

echo "📋 PASSO 2: CORREÇÃO DE PERMISSÕES (MELHORADO)\n";
echo "---------------------------------------------\n";

echo "Execute estes comandos para corrigir permissões:\n\n";

echo "1. Identificar usuário do processo PM2:\n";
echo "   PM2_USER=\$(ps -o user= -p \$(pm2 pid whatsapp-3000))\n";
echo "   echo \"Usuário PM2: \$PM2_USER\"\n\n";

echo "2. Corrigir proprietário e grupo (usando usuário correto):\n";
echo "   chown -R \$PM2_USER:\$PM2_USER /var/whatsapp-api/sessions/\n";
echo "   chmod -R 755 /var/whatsapp-api/sessions/\n\n";

echo "3. Corrigir permissões específicas:\n";
echo "   chmod -R 700 /var/whatsapp-api/sessions/default/\n";
echo "   chmod -R 700 /var/whatsapp-api/sessions/comercial/\n\n";

echo "4. Verificar se o diretório é gravável:\n";
echo "   test -w /var/whatsapp-api/sessions/default && echo '✅ Gravável' || echo '❌ Não gravável'\n";
echo "   test -w /var/whatsapp-api/sessions/comercial && echo '✅ Gravável' || echo '❌ Não gravável'\n\n";

echo "📋 PASSO 3: REGENERAR SESSÕES (COM BACKUP)\n";
echo "-----------------------------------------\n";

echo "Se os logs mostrarem auth_failure, execute:\n\n";

echo "1. Criar backup das sessões atuais:\n";
echo "   cp -r /var/whatsapp-api/sessions/ /var/whatsapp-api/sessions_backup_\$(date +%Y%m%d_%H%M%S)/\n\n";

echo "2. Parar processos:\n";
echo "   pm2 stop whatsapp-3000\n";
echo "   pm2 stop whatsapp-3001\n\n";

echo "3. Limpar sessões corrompidas:\n";
echo "   rm -rf /var/whatsapp-api/sessions/default/*\n";
echo "   rm -rf /var/whatsapp-api/sessions/comercial/*\n\n";

echo "4. Reiniciar processos:\n";
echo "   pm2 start whatsapp-3000\n";
echo "   pm2 start whatsapp-3001\n\n";

echo "5. Aguardar inicialização e verificar:\n";
echo "   sleep 10\n";
echo "   pm2 logs whatsapp-3000 --lines 20\n";
echo "   pm2 logs whatsapp-3001 --lines 20\n\n";

echo "📋 PASSO 4: TESTAR CONECTIVIDADE (MELHORADO)\n";
echo "-------------------------------------------\n";

echo "Após as correções, teste:\n\n";

echo "1. Verificar status geral:\n";
echo "   curl -i \"http://212.85.11.238:3000/status\"\n";
echo "   curl -i \"http://212.85.11.238:3001/status\"\n\n";

echo "2. Testar QR Codes:\n";
echo "   curl -i \"http://212.85.11.238:3000/qr?session=default\"\n";
echo "   curl -i \"http://212.85.11.238:3001/qr?session=comercial\"\n\n";

echo "3. Verificar sessões específicas:\n";
echo "   curl -i \"http://212.85.11.238:3000/session/default/status\"\n";
echo "   curl -i \"http://212.85.11.238:3001/session/comercial/status\"\n\n";

echo "📋 PASSO 5: TESTE LOCAL (MELHORADO)\n";
echo "----------------------------------\n";

echo "Testando proxy local após correções:\n\n";

// Testar proxy com porta 3000
echo "🔗 Testando proxy porta 3000 (default):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=qr&porta=3000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $http_code\n";
if ($http_code == 200) {
    $json = json_decode($response, true);
    if ($json) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            echo "🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
        }
        if (isset($json['debug'])) {
            echo "🔍 Debug info disponível\n";
        }
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "---\n\n";

// Testar proxy com porta 3001
echo "🔗 Testando proxy porta 3001 (comercial):\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=qr&porta=3001');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $http_code\n";
if ($http_code == 200) {
    $json = json_decode($response, true);
    if ($json) {
        echo "✅ JSON válido\n";
        if (isset($json['success'])) {
            echo "📊 Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($json['qr'])) {
            echo "🎯 QR: " . (empty($json['qr']) ? 'não disponível' : 'disponível (' . strlen($json['qr']) . ' chars)') . "\n";
        }
        if (isset($json['debug'])) {
            echo "🔍 Debug info disponível\n";
        }
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "---\n\n";

echo "📋 PASSO 6: MONITORAMENTO CONTÍNUO\n";
echo "---------------------------------\n";

echo "Comandos para monitoramento em tempo real:\n\n";

echo "1. Monitorar logs com filtros específicos:\n";
echo "   pm2 logs whatsapp-3000 --lines 0 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'\n";
echo "   pm2 logs whatsapp-3001 --lines 0 | grep -E '(AUTH_FAILURE|authenticated|ready|disconnected|NAVIGATION|CONNECTION_LOST|LOGOUT)'\n\n";

echo "2. Monitorar uso de recursos:\n";
echo "   pm2 monit\n\n";

echo "3. Verificar status das sessões periodicamente:\n";
echo "   watch -n 30 'curl -s http://212.85.11.238:3000/status | jq .'\n";
echo "   watch -n 30 'curl -s http://212.85.11.238:3001/status | jq .'\n\n";

echo "📋 INSTRUÇÕES FINAIS (MELHORADAS)\n";
echo "--------------------------------\n";

echo "1. Execute os comandos SSH na VPS conforme listado acima\n";
echo "2. Monitore os logs em tempo real com filtros específicos\n";
echo "3. Procure por mensagens de erro específicas:\n";
echo "   - '🚨 FALHA DE AUTENTICAÇÃO' - Indica auth_failure\n";
echo "   - '🔐 WhatsApp sessão autenticado' - Indica sucesso na autenticação\n";
echo "   - '✅ WhatsApp sessão conectado' - Indica conexão completa\n";
echo "   - '❌ WhatsApp sessão desconectado' - Indica desconexão\n";
echo "   - '🔄 Tentando reconectar' - Indica tentativa de reconexão\n";
echo "4. Se houver auth_failure, execute a regeneração de sessões\n";
echo "5. Teste novamente no painel administrativo\n\n";

echo "🎯 ESTRATÉGIA RECOMENDADA (MELHORADA):\n";
echo "1. Primeiro: Verificar logs e permissões (com filtros específicos)\n";
echo "2. Segundo: Corrigir permissões usando usuário correto do PM2\n";
echo "3. Terceiro: Regenerar sessões com backup (se auth_failure persistir)\n";
echo "4. Quarto: Testar conectividade completa\n";
echo "5. Quinto: Implementar monitoramento contínuo\n";
echo "6. Sexto: Testar no painel administrativo\n\n";

echo "⚠️ ATENÇÃO IMPORTANTE:\n";
echo "- A regeneração de sessões invalidará todos os dispositivos conectados\n";
echo "- Agende em janela de baixa demanda se necessário\n";
echo "- Mantenha backup das sessões antes de limpar\n\n";

echo "✅ Script de diagnóstico melhorado finalizado!\n";
?> 