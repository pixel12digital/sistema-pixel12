<?php
/**
 * 🔍 VERIFICAR SE CONFIGURAÇÃO SSH FUNCIONOU
 * 
 * Execute após configurar o webhook via SSH
 */

echo "🔍 VERIFICANDO CONFIGURAÇÃO DO WEBHOOK\n";
echo "=====================================\n\n";

require_once 'config.php';
require_once 'painel/db.php';

$vps_ip = '212.85.11.238';
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

// 1. TESTAR STATUS VPS
echo "📡 1. STATUS VPS CANAL 3000:\n";
echo "============================\n";

$status_ch = curl_init("http://$vps_ip:3000/status");
curl_setopt($status_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($status_ch, CURLOPT_TIMEOUT, 10);
$status_response = curl_exec($status_ch);
$status_code = curl_getinfo($status_ch, CURLINFO_HTTP_CODE);
curl_close($status_ch);

if ($status_code === 200) {
    echo "✅ VPS Canal 3000: ATIVO\n";
    $status_data = json_decode($status_response, true);
    if ($status_data && isset($status_data['ready']) && $status_data['ready']) {
        echo "✅ WhatsApp: CONECTADO\n";
    } else {
        echo "⚠️ WhatsApp: Status desconhecido\n";
    }
} else {
    echo "❌ VPS Canal 3000: FALHA (HTTP $status_code)\n";
}
echo "\n";

// 2. CONTAR MENSAGENS ANTES DO TESTE
echo "📊 2. MENSAGENS ATUAIS NO BANCO:\n";
echo "================================\n";

$count_antes = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc();
echo "Total mensagens hoje: {$count_antes['total']}\n\n";

// 3. SIMULAÇÃO RÁPIDA PARA TESTAR WEBHOOK
echo "🧪 3. TESTE SIMULADO DO WEBHOOK:\n";
echo "================================\n";

$teste_dados = json_encode([
    'from' => '5547999999999@c.us',
    'body' => '🔍 TESTE WEBHOOK - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
]);

$teste_ch = curl_init($webhook_url);
curl_setopt($teste_ch, CURLOPT_POST, true);
curl_setopt($teste_ch, CURLOPT_POSTFIELDS, $teste_dados);
curl_setopt($teste_ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($teste_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($teste_ch, CURLOPT_TIMEOUT, 15);
curl_setopt($teste_ch, CURLOPT_SSL_VERIFYPEER, false);

$teste_response = curl_exec($teste_ch);
$teste_code = curl_getinfo($teste_ch, CURLINFO_HTTP_CODE);
curl_close($teste_ch);

echo "Status webhook: HTTP $teste_code\n";
if ($teste_code === 200) {
    echo "✅ Webhook: FUNCIONANDO\n";
    
    $response_data = json_decode($teste_response, true);
    if ($response_data && isset($response_data['success']) && $response_data['success']) {
        echo "✅ Ana: RESPONDEU\n";
        if (isset($response_data['ana_response'])) {
            echo "Ana disse: " . substr($response_data['ana_response'], 0, 50) . "...\n";
        }
    } else {
        echo "⚠️ Ana: Sem resposta válida\n";
    }
} else {
    echo "❌ Webhook: FALHA (HTTP $teste_code)\n";
}
echo "\n";

// 4. VERIFICAR SE MENSAGEM FOI SALVA
sleep(2);
$count_depois = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE DATE(data_hora) = CURDATE()")->fetch_assoc();
$novas = $count_depois['total'] - $count_antes['total'];

echo "📈 4. RESULTADO DO TESTE:\n";
echo "=========================\n";
echo "Mensagens antes: {$count_antes['total']}\n";
echo "Mensagens depois: {$count_depois['total']}\n";
echo "Novas mensagens: $novas\n\n";

if ($novas >= 2) {
    echo "✅ SUCESSO! Webhook processou mensagem e Ana respondeu!\n";
} elseif ($novas >= 1) {
    echo "⚠️ PARCIAL: Webhook recebeu mensagem, mas Ana pode não ter respondido\n";
} else {
    echo "❌ FALHA: Nenhuma mensagem foi processada\n";
}

// 5. INSTRUÇÕES FINAIS
echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "==================\n";

if ($novas >= 2) {
    echo "🎉 CONFIGURAÇÃO CONCLUÍDA COM SUCESSO!\n\n";
    echo "📱 AGORA TESTE COM WHATSAPP REAL:\n";
    echo "1. Envie uma mensagem do seu WhatsApp para o canal 3000\n";
    echo "2. Aguarde 30 segundos\n";
    echo "3. Execute: php verificar_mensagens_recentes.php\n";
    echo "4. Verifique se aparece no chat centralizado\n\n";
    echo "✨ Se funcionar, Ana está 100% operacional!\n";
} else {
    echo "🔧 CONFIGURAÇÃO AINDA NECESSÁRIA:\n\n";
    echo "1. Volte ao SSH e verifique os logs:\n";
    echo "   pm2 logs whatsapp-3000 --lines 20\n\n";
    echo "2. Procure por erros ou mensagens de webhook\n\n";
    echo "3. Se não encontrar referência ao webhook, tente:\n";
    echo "   - Opção B ou C do guia SSH\n";
    echo "   - Verificar se processo foi reiniciado\n\n";
    echo "4. Execute este script novamente após mudanças\n";
}

echo "\n📞 SUPORTE:\n";
echo "===========\n";
echo "Se precisar de ajuda, copie e cole:\n";
echo "- Resultado deste script\n";
echo "- Output de: pm2 logs whatsapp-3000 --lines 20\n";
echo "- Output de: pm2 show whatsapp-3000\n";

?> 