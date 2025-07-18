<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CORREÇÃO DO NÚMERO DO CLIENTE ===\n\n";

// Conectar ao banco
$mysqli = new mysqli('srv1607.hstgr.io', 'u342734079_revendaweb', 'Los@ngo#081081', 'u342734079_revendaweb');
if ($mysqli->connect_errno) {
    echo "❌ Erro ao conectar ao MySQL: " . $mysqli->connect_error . "\n";
    exit;
}
$mysqli->set_charset('utf8mb4');

// Buscar cliente Charles Dietrich
echo "1. Buscando cliente Charles Dietrich...\n";
$res = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE id = 156 LIMIT 1");

if (!$res || !($cliente = $res->fetch_assoc())) {
    echo "❌ Cliente não encontrado\n";
    exit;
}

echo "Cliente encontrado: " . $cliente['nome'] . "\n";
echo "Número atual: " . $cliente['celular'] . "\n\n";

// Analisar o número atual
$numero_atual = $cliente['celular'];
$numero_limpo = preg_replace('/\D/', '', $numero_atual);

echo "2. Análise do número:\n";
echo "Número original: $numero_atual\n";
echo "Número limpo: $numero_limpo\n";
echo "Comprimento: " . strlen($numero_limpo) . " dígitos\n";

// Verificar se tem código do país
if (strpos($numero_limpo, '55') === 0) {
    $ddd_numero = substr($numero_limpo, 2);
    echo "DDD + Número: $ddd_numero\n";
} else {
    $ddd_numero = $numero_limpo;
    echo "DDD + Número: $ddd_numero\n";
}

// Identificar DDD
$ddd = substr($ddd_numero, 0, 2);
$numero_telefone = substr($ddd_numero, 2);
echo "DDD: $ddd\n";
echo "Número: $numero_telefone\n";
echo "Comprimento do número: " . strlen($numero_telefone) . " dígitos\n\n";

// Verificar regras por DDD
echo "3. Regras por DDD:\n";
switch ($ddd) {
    case '47': // Santa Catarina
        if (strlen($numero_telefone) == 9) {
            echo "⚠️ DDD 47 deve ter 8 dígitos, não 9\n";
            $numero_correto = substr($numero_telefone, 1); // Remove o 9
            echo "Número correto: $numero_correto\n";
        } elseif (strlen($numero_telefone) == 8) {
            echo "✅ DDD 47 com 8 dígitos - correto\n";
            $numero_correto = $numero_telefone;
        } else {
            echo "❌ DDD 47 com formato inválido\n";
            exit;
        }
        break;
        
    case '11': // São Paulo
        if (strlen($numero_telefone) == 9) {
            echo "✅ DDD 11 com 9 dígitos - correto\n";
            $numero_correto = $numero_telefone;
        } elseif (strlen($numero_telefone) == 8) {
            echo "⚠️ DDD 11 deve ter 9 dígitos, não 8\n";
            $numero_correto = '9' . $numero_telefone; // Adiciona o 9
            echo "Número correto: $numero_correto\n";
        } else {
            echo "❌ DDD 11 com formato inválido\n";
            exit;
        }
        break;
        
    default:
        echo "⚠️ DDD $ddd - verificar regras específicas\n";
        $numero_correto = $numero_telefone;
        break;
}

// Formatar número final
$numero_final = '55' . $ddd . $numero_correto;
echo "\n4. Número final formatado: $numero_final\n";

// Perguntar se deve atualizar
echo "\n5. Atualizar número no banco?\n";
echo "Número atual: $numero_atual\n";
echo "Número corrigido: $ddd . $numero_correto\n";

// Atualizar no banco
$numero_para_salvar = $ddd . $numero_correto;
$numero_escaped = $mysqli->real_escape_string($numero_para_salvar);

$sql = "UPDATE clientes SET celular = '$numero_escaped' WHERE id = 156";
if ($mysqli->query($sql)) {
    echo "✅ Número atualizado no banco!\n";
    echo "Novo número salvo: $numero_para_salvar\n";
} else {
    echo "❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

// Testar envio com novo número
echo "\n6. Testando envio com novo número...\n";
$test_url = "http://212.85.11.238:3000/send";
$test_data = [
    'to' => $numero_final,
    'message' => "Teste após correção do número - " . date('H:i:s')
];

$ch = curl_init($test_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$test_response = curl_exec($ch);
$test_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($test_http === 200) {
    $test_result = json_decode($test_response, true);
    if ($test_result && isset($test_result['success']) && $test_result['success']) {
        echo "✅ Teste com novo número enviado com sucesso!\n";
        echo "Message ID: " . ($test_result['messageId'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Erro no teste: " . json_encode($test_result) . "\n";
    }
} else {
    echo "❌ Erro HTTP no teste: $test_http\n";
}

$mysqli->close();
echo "\n=== CORREÇÃO CONCLUÍDA ===\n";
?> 