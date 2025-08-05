<?php
/**
 * 🧪 SIMULAÇÃO DE MENSAGEM REAL - USUÁRIO 554796164699 PARA CANAL 3000
 * 
 * Este script simula exatamente uma mensagem real do WhatsApp
 * usando a mesma rota e formato que uma mensagem real teria
 * 
 * OBJETIVO: Testar todas as rotas, comportamento do sistema, webhook, 
 * salvando banco de dados, chat do sistema e atendimento Ana.
 * Identificar possíveis falhas e aplicar correções.
 */

echo "🧪 SIMULAÇÃO DE MENSAGEM REAL - USUÁRIO 554796164699\n";
echo "===================================================\n\n";

// Incluir configurações
require_once __DIR__ . '/config.php';

// Função para conectar ao banco de forma segura
function conectarBanco() {
    global $mysqli;
    
    try {
        if (!isset($mysqli) || !$mysqli->ping()) {
            require_once 'painel/db.php';
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($mysqli->connect_errno) {
                throw new Exception('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
            }
            
            $mysqli->set_charset('utf8mb4');
        }
        
        return $mysqli;
    } catch (Exception $e) {
        echo "❌ Erro de conexão com banco: " . $e->getMessage() . "\n";
        return null;
    }
}

// 1. VERIFICAR ESTADO ATUAL
echo "1️⃣ VERIFICANDO ESTADO ATUAL\n";
echo "============================\n";

$mysqli = conectarBanco();
if (!$mysqli) {
    echo "❌ Não foi possível conectar ao banco de dados\n";
    exit(1);
}

// Verificar se o cliente existe
$cliente_check = $mysqli->query("SELECT id, nome FROM clientes WHERE celular LIKE '%554796164699%' LIMIT 1");
if ($cliente_check && $cliente_check->num_rows > 0) {
    $cliente_existente = $cliente_check->fetch_assoc();
    echo "✅ Cliente encontrado: {$cliente_existente['nome']} (ID: {$cliente_existente['id']})\n";
} else {
    echo "⚠️ Cliente não encontrado - será criado automaticamente\n";
}

// Verificar mensagens recentes
$ultimas_mensagens = $mysqli->query("SELECT id, mensagem, data_hora FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' ORDER BY id DESC LIMIT 3");
if ($ultimas_mensagens && $ultimas_mensagens->num_rows > 0) {
    echo "📋 Últimas mensagens do usuário:\n";
    while ($msg = $ultimas_mensagens->fetch_assoc()) {
        echo "   - ID {$msg['id']}: {$msg['mensagem']} ({$msg['data_hora']})\n";
    }
} else {
    echo "📋 Nenhuma mensagem anterior encontrada\n";
}

// Verificar status do canal 3000
$canal_3000 = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3000 LIMIT 1");
if ($canal_3000 && $canal_3000->num_rows > 0) {
    $canal = $canal_3000->fetch_assoc();
    echo "✅ Canal 3000 encontrado: {$canal['nome_exibicao']} (ID: {$canal['id']}) - Status: {$canal['status']}\n";
} else {
    echo "❌ Canal 3000 não encontrado!\n";
}

echo "\n";

// 2. PREPARAR DADOS DE SIMULAÇÃO
echo "2️⃣ PREPARANDO DADOS DE SIMULAÇÃO\n";
echo "=================================\n";

// Dados exatos que o WhatsApp enviaria
$dados_simulacao = [
    "event" => "onmessage",
    "data" => [
        "from" => "554796164699@c.us",
        "to" => "554797146908@c.us", // Canal 3000 (Financeiro/Ana)
        "text" => "Olá! Preciso de ajuda com minha fatura. Pode me informar o status?",
        "type" => "text",
        "session" => "default",
        "timestamp" => time()
    ]
];

echo "📤 Dados de simulação preparados:\n";
echo "   - De: 554796164699@c.us\n";
echo "   - Para: 554797146908@c.us (Canal 3000)\n";
echo "   - Mensagem: \"{$dados_simulacao['data']['text']}\"\n";
echo "   - Tipo: {$dados_simulacao['data']['type']}\n";
echo "   - Sessão: {$dados_simulacao['data']['session']}\n";

echo "\n";

// 3. SIMULAR ENVIO PARA WEBHOOK
echo "3️⃣ SIMULANDO ENVIO PARA WEBHOOK\n";
echo "===============================\n";

echo "🔄 Enviando dados para webhook...\n";

// Fazer requisição para o webhook (mesma rota que mensagem real)
$ch = curl_init("https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_simulacao));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "User-Agent: WhatsApp/2.0",
    "X-Forwarded-For: 212.85.11.238"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_info = curl_getinfo($ch);
curl_close($ch);

echo "📥 Resposta do webhook:\n";
echo "   HTTP Code: $http_code\n";
echo "   Response: $response\n";

if ($curl_error) {
    echo "   ❌ Erro cURL: $curl_error\n";
}

echo "\n";

// 4. VERIFICAR SE MENSAGEM FOI SALVA
echo "4️⃣ VERIFICANDO SE MENSAGEM FOI SALVA\n";
echo "====================================\n";

// Aguardar um pouco para garantir que a inserção foi processada
sleep(2);

// Reconectar ao banco se necessário
$mysqli = conectarBanco();

// Verificar se a mensagem foi salva
$mensagem_salva = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' AND mensagem LIKE '%Preciso de ajuda com minha fatura%' ORDER BY id DESC LIMIT 1");

if ($mensagem_salva && $mensagem_salva->num_rows > 0) {
    $msg = $mensagem_salva->fetch_assoc();
    echo "✅ Mensagem salva com sucesso!\n";
    echo "   - ID: {$msg['id']}\n";
    echo "   - Canal: {$msg['canal_id']}\n";
    echo "   - Cliente: {$msg['cliente_id']}\n";
    echo "   - Número: {$msg['numero_whatsapp']}\n";
    echo "   - Mensagem: {$msg['mensagem']}\n";
    echo "   - Data: {$msg['data_hora']}\n";
    echo "   - Direção: {$msg['direcao']}\n";
    echo "   - Status: {$msg['status']}\n";
} else {
    echo "❌ Mensagem não foi salva no banco\n";
    
    // Verificar se há algum erro
    $ultima_mensagem = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE numero_whatsapp = '554796164699@c.us' ORDER BY id DESC LIMIT 1");
    if ($ultima_mensagem && $ultima_mensagem->num_rows > 0) {
        $ultima = $ultima_mensagem->fetch_assoc();
        echo "📋 Última mensagem do usuário (ID: {$ultima['id']}): {$ultima['mensagem']}\n";
    }
}

echo "\n";

// 5. VERIFICAR RESPOSTA DA ANA
echo "5️⃣ VERIFICANDO RESPOSTA DA ANA\n";
echo "==============================\n";

// Verificar se Ana respondeu
$resposta_ana = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE canal_id = 36 AND direcao = 'enviado' AND data_hora > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY id DESC LIMIT 1");

if ($resposta_ana && $resposta_ana->num_rows > 0) {
    $ana = $resposta_ana->fetch_assoc();
    echo "✅ Ana respondeu automaticamente!\n";
    echo "   - ID: {$ana['id']}\n";
    echo "   - Mensagem: " . substr($ana['mensagem'], 0, 100) . "...\n";
    echo "   - Data: {$ana['data_hora']}\n";
} else {
    echo "⚠️ Nenhuma resposta da Ana encontrada\n";
}

echo "\n";

// 6. VERIFICAR LOGS
echo "6️⃣ VERIFICANDO LOGS\n";
echo "===================\n";

$log_file = 'logs/webhook_sem_redirect_' . date('Y-m-d') . '.log';
if (file_exists($log_file)) {
    $log_content = file($log_file);
    $ultimas_linhas = array_slice($log_content, -5);
    echo "📋 Últimas 5 linhas do log:\n";
    foreach ($ultimas_linhas as $linha) {
        echo "   " . trim($linha) . "\n";
    }
} else {
    echo "⚠️ Arquivo de log não encontrado: $log_file\n";
}

echo "\n";

// 7. TESTAR CONSULTA DE FATURAS
echo "7️⃣ TESTANDO CONSULTA DE FATURAS\n";
echo "===============================\n";

// Verificar se o cliente tem faturas
$cliente_check_faturas = $mysqli->query("SELECT id, nome FROM clientes WHERE celular LIKE '%554796164699%' LIMIT 1");
if ($cliente_check_faturas && $cliente_check_faturas->num_rows > 0) {
    $cliente_data = $cliente_check_faturas->fetch_assoc();
    $cliente_id = $cliente_data['id'];
    
    // Verificar se a tabela cobrancas existe
    $table_exists = $mysqli->query("SHOW TABLES LIKE 'cobrancas'");
    if ($table_exists && $table_exists->num_rows > 0) {
        // Buscar faturas vencidas
        $faturas_vencidas = $mysqli->query("SELECT COUNT(*) as total FROM cobrancas WHERE cliente_id = $cliente_id AND status = 'OVERDUE'");
        if ($faturas_vencidas) {
            $total_vencidas = $faturas_vencidas->fetch_assoc()['total'];
            echo "📊 Faturas vencidas: $total_vencidas\n";
        }
        
        // Buscar próxima fatura
        $proxima_fatura = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = $cliente_id AND status = 'PENDING' ORDER BY vencimento ASC LIMIT 1");
        if ($proxima_fatura && $proxima_fatura->num_rows > 0) {
            $fatura = $proxima_fatura->fetch_assoc();
            echo "📅 Próxima fatura: R$ " . number_format($fatura['valor'], 2, ',', '.') . " - Vence em: {$fatura['vencimento']}\n";
        } else {
            echo "✅ Nenhuma fatura pendente encontrada\n";
        }
    } else {
        echo "⚠️ Tabela 'cobrancas' não encontrada\n";
    }
} else {
    echo "⚠️ Cliente não encontrado para consulta de faturas\n";
}

echo "\n";

// 8. VERIFICAR CACHE E CHAT
echo "8️⃣ VERIFICANDO CACHE E CHAT\n";
echo "===========================\n";

// Verificar se existe cache para o cliente
$cliente_check_cache = $mysqli->query("SELECT id, nome FROM clientes WHERE celular LIKE '%554796164699%' LIMIT 1");
if ($cliente_check_cache && $cliente_check_cache->num_rows > 0) {
    $cliente_data = $cliente_check_cache->fetch_assoc();
    $cliente_id = $cliente_data['id'];
    
    // Verificar cache de mensagens
    $cache_file = "cache/mensagens_{$cliente_id}.cache";
    if (file_exists($cache_file)) {
        echo "✅ Cache de mensagens encontrado: $cache_file\n";
        $cache_size = filesize($cache_file);
        echo "   - Tamanho: " . number_format($cache_size) . " bytes\n";
    } else {
        echo "⚠️ Cache de mensagens não encontrado\n";
    }
    
    // Verificar se há mensagens no chat
    $mensagens_chat = $mysqli->query("SELECT COUNT(*) as total FROM mensagens_comunicacao WHERE cliente_id = $cliente_id");
    if ($mensagens_chat) {
        $total_mensagens = $mensagens_chat->fetch_assoc()['total'];
        echo "💬 Total de mensagens no chat: $total_mensagens\n";
    }
} else {
    echo "⚠️ Cliente não encontrado para verificação de cache\n";
}

echo "\n";

// 9. TESTAR ENDPOINTS
echo "9️⃣ TESTANDO ENDPOINTS\n";
echo "=====================\n";

// Testar endpoint da Ana
echo "🔍 Testando endpoint da Ana...\n";
$ana_url = 'https://agentes.pixel12digital.com.br/api/chat/agent_chat.php';
$ana_payload = [
    'question' => 'Preciso de ajuda com minha fatura',
    'agent_id' => '3'
];

$ch_ana = curl_init($ana_url);
curl_setopt($ch_ana, CURLOPT_POST, true);
curl_setopt($ch_ana, CURLOPT_POSTFIELDS, json_encode($ana_payload));
curl_setopt($ch_ana, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch_ana, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_ana, CURLOPT_TIMEOUT, 10);
curl_setopt($ch_ana, CURLOPT_SSL_VERIFYPEER, false);

$response_ana = curl_exec($ch_ana);
$http_code_ana = curl_getinfo($ch_ana, CURLINFO_HTTP_CODE);
$curl_error_ana = curl_error($ch_ana);
curl_close($ch_ana);

if (!$curl_error_ana && $http_code_ana === 200) {
    echo "✅ Endpoint da Ana funcionando (HTTP: $http_code_ana)\n";
    $ana_data = json_decode($response_ana, true);
    if ($ana_data && isset($ana_data['response'])) {
        echo "   - Resposta: " . substr($ana_data['response'], 0, 100) . "...\n";
    }
} else {
    echo "❌ Erro no endpoint da Ana (HTTP: $http_code_ana, Erro: $curl_error_ana)\n";
}

echo "\n";

// 10. RESUMO FINAL
echo "🔟 RESUMO FINAL\n";
echo "===============\n";

$status_geral = [];

// Verificar webhook
if ($http_code === 200) {
    $status_geral[] = "✅ Webhook processou a mensagem corretamente";
} else {
    $status_geral[] = "❌ Webhook retornou erro HTTP $http_code";
}

// Verificar salvamento
if ($mensagem_salva && $mensagem_salva->num_rows > 0) {
    $status_geral[] = "✅ Mensagem foi salva no banco de dados";
} else {
    $status_geral[] = "❌ Mensagem não foi salva no banco de dados";
}

// Verificar resposta da Ana
if ($resposta_ana && $resposta_ana->num_rows > 0) {
    $status_geral[] = "✅ Ana respondeu automaticamente";
} else {
    $status_geral[] = "⚠️ Ana não respondeu (pode ser normal se não detectou palavras-chave)";
}

// Verificar endpoint da Ana
if (!$curl_error_ana && $http_code_ana === 200) {
    $status_geral[] = "✅ Endpoint da Ana funcionando";
} else {
    $status_geral[] = "❌ Endpoint da Ana com problemas";
}

// Exibir resumo
foreach ($status_geral as $status) {
    echo "$status\n";
}

echo "\n🎯 SIMULAÇÃO CONCLUÍDA!\n";
echo "Para verificar no painel: https://app.pixel12digital.com.br/painel/chat.php\n";
echo "Para verificar logs: https://app.pixel12digital.com.br/logs/\n";

// 11. SUGESTÕES DE CORREÇÃO
echo "\n🔧 SUGESTÕES DE CORREÇÃO\n";
echo "========================\n";

if ($http_code !== 200) {
    echo "⚠️ Verificar configuração do webhook\n";
    echo "   - URL: https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php\n";
    echo "   - Verificar logs de erro\n";
}

if (!$mensagem_salva || $mensagem_salva->num_rows === 0) {
    echo "⚠️ Verificar inserção no banco de dados\n";
    echo "   - Verificar estrutura da tabela mensagens_comunicacao\n";
    echo "   - Verificar permissões de escrita\n";
}

if (!$resposta_ana || $resposta_ana->num_rows === 0) {
    echo "⚠️ Verificar integração com Ana\n";
    echo "   - Verificar endpoint: https://agentes.pixel12digital.com.br/api/chat/agent_chat.php\n";
    echo "   - Verificar agent_id: 3\n";
}

echo "\n✅ Teste completo finalizado!\n";

// Fechar conexão
if ($mysqli) {
    $mysqli->close();
}
?> 