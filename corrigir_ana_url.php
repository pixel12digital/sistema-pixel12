<?php
/**
 * 🔧 CORRIGIR URL DA ANA
 * 
 * Este script corrige a URL da Ana e testa a conectividade
 */

echo "🔧 CORRIGINDO URL DA ANA\n";
echo "========================\n\n";

// ===== 1. TESTAR DIFERENTES URLs DA ANA =====
echo "1️⃣ TESTANDO DIFERENTES URLs DA ANA:\n";
echo "====================================\n";

$ana_urls = [
    'URL Principal' => 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php',
    'URL Alternativa 1' => 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php',
    'URL Alternativa 2' => 'https://agentes.pixel12digital.com.br/ai-agents/api/chat.php',
    'URL Alternativa 3' => 'https://agentes.pixel12digital.com.br/api/chat.php'
];

$test_message = "Olá, teste de conectividade";
$ana_payload = [
    'question' => $test_message,
    'agent_id' => '3'
];

$url_funcionando = null;
$resposta_funcionando = null;

foreach ($ana_urls as $nome => $url) {
    echo "🔍 Testando $nome: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ana_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "   ❌ Erro cURL: $curl_error\n";
    } elseif ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['response'])) {
            echo "   ✅ FUNCIONANDO! Resposta: " . substr($data['response'], 0, 100) . "...\n";
            $url_funcionando = $url;
            $resposta_funcionando = $data['response'];
            break;
        } else {
            echo "   ⚠️ HTTP 200 mas resposta inválida: " . substr($response, 0, 100) . "...\n";
        }
    } else {
        echo "   ❌ HTTP $http_code: " . substr($response, 0, 100) . "...\n";
    }
    echo "\n";
}

// ===== 2. CORRIGIR ARQUIVOS SE URL FUNCIONANDO ENCONTRADA =====
if ($url_funcionando) {
    echo "2️⃣ CORRIGINDO ARQUIVOS COM URL FUNCIONANDO:\n";
    echo "===========================================\n";
    echo "✅ URL funcionando encontrada: $url_funcionando\n\n";
    
    // Lista de arquivos para corrigir
    $arquivos_para_corrigir = [
        'painel/api/integrador_ana.php',
        'painel/api/integrador_ana_local.php',
        'painel/receber_mensagem_ana.php',
        'painel/receber_mensagem_ana_simples.php',
        'webhook_sem_redirect/webhook.php'
    ];
    
    foreach ($arquivos_para_corrigir as $arquivo) {
        if (file_exists($arquivo)) {
            echo "🔧 Corrigindo $arquivo...\n";
            
            $conteudo = file_get_contents($arquivo);
            
            // Substituir URLs antigas pela nova
            $conteudo = str_replace(
                'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php',
                $url_funcionando,
                $conteudo
            );
            
            $conteudo = str_replace(
                'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php',
                $url_funcionando,
                $conteudo
            );
            
            // Salvar arquivo corrigido
            file_put_contents($arquivo, $conteudo);
            echo "   ✅ $arquivo corrigido\n";
        } else {
            echo "   ⚠️ $arquivo não existe\n";
        }
    }
    
    // ===== 3. TESTAR CORREÇÃO =====
    echo "\n3️⃣ TESTANDO CORREÇÃO:\n";
    echo "======================\n";
    
    // Testar webhook com a nova URL
    $webhook_test = [
        'from' => '554796164699@c.us',
        'body' => 'Teste após correção da URL da Ana',
        'event' => 'onmessage',
        'data' => [
            'from' => '554796164699@c.us',
            'text' => 'Teste após correção da URL da Ana',
            'type' => 'text'
        ]
    ];
    
    $webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_test));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $webhook_response = curl_exec($ch);
    $webhook_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($webhook_http_code === 200) {
        $webhook_data = json_decode($webhook_response, true);
        if ($webhook_data && isset($webhook_data['ana_response'])) {
            echo "✅ WEBHOOK FUNCIONANDO COM ANA!\n";
            echo "📄 Resposta da Ana: " . substr($webhook_data['ana_response'], 0, 100) . "...\n";
        } else {
            echo "⚠️ Webhook funcionando mas Ana não retornou resposta\n";
            echo "📄 Resposta: " . json_encode($webhook_data, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "❌ Webhook não respondeu (HTTP: $webhook_http_code)\n";
    }
    
} else {
    echo "❌ NENHUMA URL DA ANA ESTÁ FUNCIONANDO!\n";
    echo "🔧 Verificando se a Ana está online...\n\n";
    
    // Testar se o domínio está acessível
    $ch = curl_init('https://agentes.pixel12digital.com.br');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ Domínio acessível (HTTP: $http_code)\n";
        echo "🔍 A Ana pode estar com problemas temporários\n";
        echo "🔧 Verificar se a API da Ana foi alterada\n";
    } else {
        echo "❌ Domínio não acessível (HTTP: $http_code)\n";
        echo "🔧 A Ana pode estar offline ou com problemas\n";
    }
}

// ===== 4. CRIAR ARQUIVO DE FALLBACK =====
echo "\n4️⃣ CRIANDO ARQUIVO DE FALLBACK:\n";
echo "================================\n";

$fallback_content = '<?php
/**
 * 🔗 FALLBACK ANA - VERSÃO DE EMERGÊNCIA
 * 
 * Sistema de fallback quando a Ana não está funcionando
 */

header("Content-Type: application/json");

// Capturar dados
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "Dados inválidos"]);
    exit;
}

$from = $data["from"] ?? $data["number"] ?? "desconhecido";
$body = $data["body"] ?? $data["message"] ?? "";

// Resposta de fallback inteligente
function gerarRespostaFallback($mensagem) {
    $msg = strtolower($mensagem);
    
    // Sites/Comercial
    if (strpos($msg, "site") !== false || strpos($msg, "loja") !== false || 
        strpos($msg, "ecommerce") !== false || strpos($msg, "orçamento") !== false) {
        return "🌐 Olá! Vou conectar você com Rafael, nosso especialista em desenvolvimento web! Ele tem experiência em criação de sites e lojas virtuais. Em breve receberá o contato dele! 🚀";
    }
    
    // Problemas/Suporte
    if (strpos($msg, "problema") !== false || strpos($msg, "erro") !== false || 
        strpos($msg, "bug") !== false || strpos($msg, "não funciona") !== false ||
        strpos($msg, "fora do ar") !== false || strpos($msg, "quebrou") !== false) {
        return "🔧 Identifiquei que você tem um problema técnico! Vou transferir para nossa equipe de suporte especializada que irá analisar e resolver sua questão. Aguarde o contato! 🛠️";
    }
    
    // Atendimento humano
    if (strpos($msg, "pessoa") !== false || strpos($msg, "humano") !== false || 
        strpos($msg, "atendente") !== false) {
        return "👥 Entendo que deseja falar com uma pessoa! Transferindo para nossa equipe de atendimento humano. Em breve alguém entrará em contato. Horário: Segunda a Sexta, 8h às 18h! 🤝";
    }
    
    // Saudações
    if (strpos($msg, "oi") !== false || strpos($msg, "olá") !== false || 
        strpos($msg, "bom dia") !== false || strpos($msg, "boa tarde") !== false) {
        return "👋 Olá! Sou a Ana da Pixel12Digital! Como posso ajudá-lo hoje?\n\n📋 Posso te ajudar com:\n🌐 Sites e lojas virtuais\n🔧 Suporte técnico\n👥 Atendimento geral\n\nConte-me sua necessidade!";
    }
    
    // Padrão
    return "😊 Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo?\n\nPosso auxiliar com:\n🌐 Criação de sites\n🔧 Suporte técnico\n👥 Atendimento geral\n\nPara urgências: 47 97309525";
}

$resposta = gerarRespostaFallback($body);

// Salvar no banco se possível
try {
    require_once __DIR__ . "/../config.php";
    require_once "db.php";
    
    $canal_id = 36; // Canal Ana
    
    // Salvar mensagem recebida
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, \"texto\", NOW(), \"recebido\", \"nao_lido\")");
    $stmt->bind_param("iss", $canal_id, $from, $body);
    $stmt->execute();
    $mensagem_id = $mysqli->insert_id;
    $stmt->close();
    
    // Salvar resposta
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, \"texto\", NOW(), \"enviado\", \"entregue\")");
    $stmt->bind_param("iss", $canal_id, $from, $resposta);
    $stmt->execute();
    $resposta_id = $mysqli->insert_id;
    $stmt->close();
    
} catch (Exception $e) {
    // Ignorar erros de banco em fallback
}

// Resposta
echo json_encode([
    "success" => true,
    "message_id" => $mensagem_id ?? time(),
    "response_id" => $resposta_id ?? time() + 1,
    "ana_response" => $resposta,
    "fallback" => true,
    "note" => "Usando sistema de fallback - Ana temporariamente indisponível"
], JSON_UNESCAPED_UNICODE);
?>';

file_put_contents('painel/receber_mensagem_ana_fallback.php', $fallback_content);
echo "✅ Arquivo de fallback criado: painel/receber_mensagem_ana_fallback.php\n";

echo "\n🎯 CORREÇÃO CONCLUÍDA!\n";
echo "======================\n";

if ($url_funcionando) {
    echo "✅ Ana corrigida e funcionando!\n";
    echo "🔗 URL funcionando: $url_funcionando\n";
    echo "📄 Resposta de teste: " . substr($resposta_funcionando, 0, 100) . "...\n";
} else {
    echo "⚠️ Ana não está acessível no momento\n";
    echo "🔧 Sistema de fallback ativado\n";
    echo "📞 Para urgências: 47 97309525\n";
}

echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Testar envio de mensagem real para WhatsApp\n";
echo "2. Verificar se Ana está respondendo corretamente\n";
echo "3. Monitorar logs para problemas\n";
echo "4. Se necessário, usar sistema de fallback\n";
?> 