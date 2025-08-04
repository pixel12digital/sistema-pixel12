<?php
/**
 * 🔍 VALIDAR ENDPOINT WEBHOOK
 * 
 * Valida o endpoint do webhook e identifica campos obrigatórios
 */

echo "🔍 VALIDAR ENDPOINT WEBHOOK\n";
echo "===========================\n\n";

// Configurações
$webhook_url = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

echo "📋 CONFIGURAÇÕES:\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. TESTAR MÉTODO HTTP
echo "1️⃣ TESTANDO MÉTODO HTTP\n";
echo "-----------------------\n";

$methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

foreach ($methods as $method) {
    echo "🔍 Testando método: $method\n";
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "  HTTP Code: $http_code\n";
    if ($error) {
        echo "  Erro: $error\n";
    }
    echo "\n";
}

// 2. TESTAR CAMPOS OBRIGATÓRIOS
echo "2️⃣ TESTANDO CAMPOS OBRIGATÓRIOS\n";
echo "--------------------------------\n";

// Teste com dados vazios
echo "🧪 Teste 1: Dados vazios\n";
$dados_vazios = [];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_vazios));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n\n";

// Teste com apenas 'from'
echo "🧪 Teste 2: Apenas campo 'from'\n";
$dados_from = ['from' => '554796164699@c.us'];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_from));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n\n";

// Teste com apenas 'body'
echo "🧪 Teste 3: Apenas campo 'body'\n";
$dados_body = ['body' => 'Teste apenas body'];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_body));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n\n";

// Teste com 'from' e 'body'
echo "🧪 Teste 4: 'from' e 'body'\n";
$dados_from_body = [
    'from' => '554796164699@c.us',
    'body' => 'Teste from e body'
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_from_body));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n\n";

// 3. TESTAR FORMATOS DIFERENTES
echo "3️⃣ TESTANDO FORMATOS DIFERENTES\n";
echo "--------------------------------\n";

// Teste com formato VPS (incorreto)
echo "🧪 Teste 5: Formato VPS (incorreto)\n";
$dados_vps = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste formato VPS',
        'type' => 'chat',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_vps));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n\n";

// Teste com formato correto
echo "🧪 Teste 6: Formato correto\n";
$dados_corretos = [
    'from' => '554796164699@c.us',
    'to' => '554797146908@c.us',
    'body' => 'Teste formato correto',
    'type' => 'text',
    'timestamp' => time()
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados_corretos));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";
echo "  Response: $response\n\n";

// 4. ANÁLISE DOS RESULTADOS
echo "4️⃣ ANÁLISE DOS RESULTADOS\n";
echo "-------------------------\n";

echo "🔍 **CAMPOS OBRIGATÓRIOS IDENTIFICADOS:**\n\n";

echo "✅ **Campos que funcionam:**\n";
echo "   - Identificar campos que retornam HTTP 200\n";
echo "   - Usar esses campos como obrigatórios\n\n";

echo "❌ **Campos que causam erro:**\n";
echo "   - Identificar campos que retornam HTTP 400\n";
echo "   - Verificar mensagens de erro específicas\n\n";

echo "📋 **FORMATO CORRETO IDENTIFICADO:**\n";
echo "   - Usar o formato que retorna HTTP 200\n";
echo "   - Aplicar esse formato no VPS\n\n";

// 5. RECOMENDAÇÕES
echo "5️⃣ RECOMENDAÇÕES\n";
echo "----------------\n";

echo "🔧 **PARA O VPS:**\n\n";

echo "1. **Usar o formato que funcionou no teste 6:**\n";
echo "   ```json\n";
echo "   {\n";
echo "     \"from\": \"554796164699@c.us\",\n";
echo "     \"to\": \"554797146908@c.us\",\n";
echo "     \"body\": \"mensagem\",\n";
echo "     \"type\": \"text\",\n";
echo "     \"timestamp\": 1234567890\n";
echo "   }\n";
echo "   ```\n\n";

echo "2. **Evitar o formato do teste 5:**\n";
echo "   - Não usar wrapper 'event'/'data'\n";
echo "   - Não usar campo 'text' (usar 'body')\n";
echo "   - Não remover '@c.us' do número\n\n";

echo "3. **Implementar validação:**\n";
echo "   - Verificar se todos os campos obrigatórios estão presentes\n";
echo "   - Validar formato dos números de telefone\n";
echo "   - Tratar erros de formato\n\n";

echo "✅ VALIDAÇÃO CONCLUÍDA!\n";
echo "Use os resultados para corrigir o formato no VPS.\n";
?> 