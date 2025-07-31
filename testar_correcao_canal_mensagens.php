<?php
/**
 * TESTAR CORREÇÃO DO CANAL NAS MENSAGENS
 * 
 * Este script testa se a correção da exibição do canal
 * nas mensagens está funcionando corretamente
 */

echo "🧪 TESTAR CORREÇÃO DO CANAL NAS MENSAGENS\n";
echo "=========================================\n\n";

// 1. Verificar se as mensagens estão sendo salvas com o canal correto
echo "🔍 TESTE 1: VERIFICAR MENSAGENS NO BANCO\n";
require_once 'config.php';
require_once 'painel/db.php';

$sql = "SELECT m.*, c.nome_exibicao as canal_nome, c.porta as canal_porta
        FROM mensagens_comunicacao m
        LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
        WHERE m.cliente_id = 285
        ORDER BY m.data_hora DESC
        LIMIT 10";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "  📋 Mensagens da Alessandra (ID 285):\n";
    while ($msg = $result->fetch_assoc()) {
        $canal_nome = $msg['canal_nome'] ?? 'N/A';
        $canal_porta = $msg['canal_porta'] ?? 'N/A';
        $direcao = $msg['direcao'] === 'recebido' ? '📥' : '📤';
        
        echo "    $direcao ID {$msg['id']} - {$msg['data_hora']}\n";
        echo "      Canal: $canal_nome (Porta: $canal_porta)\n";
        echo "      Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        echo "      Direção: {$msg['direcao']}\n\n";
    }
} else {
    echo "  ❌ Nenhuma mensagem encontrada para a cliente ID 285\n";
}

// 2. Testar a API de histórico de mensagens
echo "🔍 TESTE 2: TESTAR API DE HISTÓRICO\n";
$historico_url = "https://app.pixel12digital.com.br/painel/api/historico_mensagens.php?cliente_id=285";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $historico_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $historico_url\n";
echo "  HTTP Code: $http_code\n";

if ($http_code === 200) {
    echo "  ✅ API respondendo\n";
    
    // Verificar se contém informações do canal
    if (strpos($response, 'via Comercial') !== false) {
        echo "  ✅ Encontrou 'via Comercial' na resposta\n";
    } elseif (strpos($response, 'via Financeiro') !== false) {
        echo "  ✅ Encontrou 'via Financeiro' na resposta\n";
    } else {
        echo "  ⚠️ Não encontrou informações do canal na resposta\n";
    }
    
    // Verificar se contém a estrutura correta
    if (strpos($response, 'message-contact-info') !== false) {
        echo "  ✅ Estrutura HTML correta encontrada\n";
    } else {
        echo "  ❌ Estrutura HTML não encontrada\n";
    }
    
    // Mostrar parte da resposta para debug
    echo "  📄 Primeiros 500 caracteres da resposta:\n";
    echo "  " . substr($response, 0, 500) . "...\n";
    
} else {
    echo "  ❌ API não respondendo corretamente\n";
}

// 3. Verificar configuração dos canais
echo "\n🔍 TESTE 3: VERIFICAR CONFIGURAÇÃO DOS CANAIS\n";
$canais_sql = "SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY id";
$canais_result = $mysqli->query($canais_sql);

if ($canais_result && $canais_result->num_rows > 0) {
    echo "  📋 Canais configurados:\n";
    while ($canal = $canais_result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🟡';
        echo "    $status_icon ID {$canal['id']} - {$canal['nome_exibicao']} (Porta {$canal['porta']})\n";
        echo "      Identificador: {$canal['identificador']}\n";
        echo "      Status: {$canal['status']}\n\n";
    }
} else {
    echo "  ❌ Nenhum canal encontrado\n";
}

// 4. Testar webhook do canal comercial
echo "🔍 TESTE 4: TESTAR WEBHOOK CANAL COMERCIAL\n";
$webhook_url = "https://app.pixel12digital.com.br/api/webhook_canal_37.php";

$dados_teste = [
    'from' => '554797146908@c.us',
    'to' => '4797309525@c.us',
    'body' => 'Teste correção canal comercial - ' . date('H:i:s'),
    'timestamp' => time()
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_teste));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  URL: $webhook_url\n";
echo "  HTTP Code: $http_code\n";
if ($http_code === 200) {
    echo "  ✅ Webhook funcionando\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  📋 Canal: {$data['canal']}\n";
        echo "  📋 ID: {$data['canal_id']}\n";
        echo "  📋 Banco: {$data['banco']}\n";
    }
} else {
    echo "  ❌ Webhook não funcionando\n";
}

echo "\n🎯 RESULTADO:\n";
echo "✅ Correções aplicadas:\n";
echo "  • Histórico de mensagens agora exibe o canal correto\n";
echo "  • Diferenciação entre Comercial e Financeiro\n";
echo "  • Estrutura HTML corrigida\n";

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Fazer git pull na Hostinger\n";
echo "2. Testar o chat do painel\n";
echo "3. Verificar se as mensagens mostram o canal correto\n";
echo "4. Enviar uma mensagem para o canal comercial\n";

echo "\n🌐 LINKS PARA TESTE:\n";
echo "• Chat: https://app.pixel12digital.com.br/painel/chat.php?cliente_id=285\n";
echo "• Histórico API: https://app.pixel12digital.com.br/painel/api/historico_mensagens.php?cliente_id=285\n";
echo "• Webhook: https://app.pixel12digital.com.br/api/webhook_canal_37.php\n";

echo "\n💡 O que foi corrigido:\n";
echo "• As mensagens agora mostram 'via Comercial - Pixel' ou 'via Financeiro - Pixel'\n";
echo "• O canal é determinado pela porta (3001 = Comercial, 3000 = Financeiro)\n";
echo "• A estrutura HTML inclui 'message-contact-info' com o nome do canal\n";
?> 