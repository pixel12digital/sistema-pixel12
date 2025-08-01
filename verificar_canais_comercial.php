<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 VERIFICAÇÃO DOS CANAIS COMERCIAIS\n";
echo "===================================\n\n";

// Verificar todos os canais WhatsApp
$sql = "SELECT id, nome_exibicao, identificador, porta, status, data_conexao FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "📱 CANAIS CONFIGURADOS:\n";
    echo "=======================\n";
    
    while ($canal = $result->fetch_assoc()) {
        echo "ID: {$canal['id']}\n";
        echo "Nome: {$canal['nome_exibicao']}\n";
        echo "Identificador: {$canal['identificador']}\n";
        echo "Porta: {$canal['porta']}\n";
        echo "Status: {$canal['status']}\n";
        echo "Última conexão: {$canal['data_conexao']}\n";
        echo "---\n";
    }
} else {
    echo "❌ Nenhum canal WhatsApp encontrado\n";
}

echo "\n🔧 TESTE DE ENVIO PARA CANAL COMERCIAL:\n";
echo "========================================\n";

// Testar envio para canal comercial
$numero_teste = "554796164699"; // Charles
$mensagem_teste = "Teste de envio para canal comercial - " . date('H:i:s');

echo "📤 Enviando mensagem:\n";
echo "Para: $numero_teste\n";
echo "Mensagem: $mensagem_teste\n";
echo "Canal: Comercial - Pixel\n\n";

// Simular envio via ajax_whatsapp.php
$data_envio = [
    'action' => 'send',
    'to' => $numero_teste,
    'message' => $mensagem_teste,
    'canal_id' => 37 // Canal comercial
];

$ch = curl_init('http://localhost:8080/loja-virtual-revenda/painel/ajax_whatsapp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_envio));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta do envio:\n";
echo "HTTP Code: $http_code\n";
echo "Erro: " . ($error ?: 'Nenhum') . "\n";
echo "Resposta: $response\n\n";

if ($http_code === 200) {
    $json_response = json_decode($response, true);
    if ($json_response && isset($json_response['success'])) {
        if ($json_response['success']) {
            echo "✅ Envio realizado com sucesso!\n";
        } else {
            echo "❌ Erro no envio: " . ($json_response['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "⚠️ Resposta inválida do servidor\n";
    }
} else {
    echo "❌ Erro HTTP: $http_code\n";
}

echo "\n💡 DIAGNÓSTICO:\n";
echo "===============\n";
echo "1. Verifique se o canal comercial está conectado\n";
echo "2. Verifique se a porta está correta (deve ser 3000)\n";
echo "3. Verifique se o número está no formato correto\n";
echo "4. Verifique os logs do VPS para erros\n";
?> 