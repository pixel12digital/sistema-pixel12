<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

echo "🌐 Configurando Webhook para Ambos os Ambientes\n\n";

// URLs dos webhooks para cada ambiente
$webhooks = [
    'local' => 'http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php',
    'hostinger' => 'https://revendawebvirtual.com.br/api/webhook_whatsapp.php'
];

$vps_url = 'http://212.85.11.238:3000';

// Função para configurar webhook
function configurarWebhook($nome_ambiente, $webhook_url, $vps_url) {
    echo "🔧 Configurando webhook para $nome_ambiente...\n";
    echo "   URL: $webhook_url\n";
    
    // 1. Testar se a URL do webhook está acessível
    echo "   1. Testando acessibilidade do webhook...\n";
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "      ✅ Webhook acessível (HTTP $http_code)\n";
    } else {
        echo "      ❌ Webhook não acessível (HTTP $http_code)\n";
        if ($nome_ambiente === 'local' && $http_code === 0) {
            echo "      ℹ️ Normal se XAMPP não estiver rodando\n";
        }
    }
    
    // 2. Configurar no servidor VPS
    echo "   2. Configurando no servidor VPS...\n";
    $ch = curl_init($vps_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "      ✅ Configurado com sucesso no VPS\n";
        $result = json_decode($response, true);
        if ($result) {
            echo "      📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "      ❌ Erro ao configurar no VPS (HTTP $http_code)\n";
        echo "      📝 Resposta: $response\n";
    }
    
    echo "\n";
}

// Detectar ambiente atual
$ambiente_atual = $is_local ? 'local' : 'hostinger';
echo "📍 Ambiente atual detectado: " . strtoupper($ambiente_atual) . "\n\n";

// Configurar webhook para ambiente atual
echo "=== CONFIGURAÇÃO PARA AMBIENTE ATUAL ===\n";
configurarWebhook($ambiente_atual, $webhooks[$ambiente_atual], $vps_url);

// Mostrar informações sobre o outro ambiente
$outro_ambiente = $ambiente_atual === 'local' ? 'hostinger' : 'local';
echo "=== INFORMAÇÕES PARA $outro_ambiente ===\n";
echo "🔧 Para configurar o $outro_ambiente, execute este mesmo script no ambiente $outro_ambiente\n";
echo "   URL que será configurada: {$webhooks[$outro_ambiente]}\n\n";

// Verificar configuração atual
echo "=== CONFIGURAÇÃO ATUAL NO VPS ===\n";
echo "🔍 Verificando webhook atual configurado...\n";
$ch = curl_init($vps_url . '/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Configuração atual obtida\n";
    $config = json_decode($response, true);
    echo "   📋 Config: " . json_encode($config, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   ❌ Não foi possível obter configuração atual (HTTP $http_code)\n";
}

echo "\n";

// Instruções
echo "=== INSTRUÇÕES ===\n";
echo "✅ Para que o sistema funcione em AMBOS os ambientes:\n\n";

echo "1. 🏠 **Ambiente Local (XAMPP):**\n";
echo "   - Webhook: {$webhooks['local']}\n";
echo "   - Execute este script quando estiver desenvolvendo localmente\n";
echo "   - XAMPP deve estar rodando na porta 8080\n\n";

echo "2. 🌐 **Ambiente Produção (Hostinger):**\n";
echo "   - Webhook: {$webhooks['hostinger']}\n";
echo "   - Execute este script quando fizer deploy na Hostinger\n";
echo "   - Certifique-se de que o domínio esteja funcionando\n\n";

echo "3. 🔄 **Como alternar:**\n";
echo "   - Ao subir para produção: Execute este script na Hostinger\n";
echo "   - Ao voltar para desenvolvimento: Execute este script no XAMPP\n";
echo "   - O VPS sempre apontará para o último ambiente configurado\n\n";

echo "4. 🧪 **Como testar:**\n";
echo "   - Envie uma mensagem para: 554797146908\n";
echo "   - Verifique se aparece no chat do ambiente ativo\n";
echo "   - Logs ficam em: api/debug_webhook.log\n\n";

echo "🎯 Script concluído!\n";
?> 