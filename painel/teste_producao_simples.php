<?php
/**
 * Script de Teste Simplificado para Produção
 * Versão que pode ser copiada diretamente no File Manager
 * Envia resultados para: 47 996164699
 */

require_once 'config.php';
require_once 'db.php';

$NUMERO_TESTE = '47996164699';
$CLIENTE_TESTE_ID = 1;

echo "<h1>🧪 Teste Rápido do Sistema</h1>";
echo "<style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
    .container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .test{background:#f8f9fa;padding:15px;margin:15px 0;border-radius:8px;border-left:4px solid #007bff;}
    .success{color:#28a745;border-left-color:#28a745;}
    .error{color:#dc3545;border-left-color:#dc3545;}
    .warning{color:#ffc107;border-left-color:#ffc107;}
</style>";

echo "<div class='container'>";

$resultados = [];

// Função para enviar WhatsApp
function enviarWhatsApp($numero, $mensagem) {
    $numero_formatado = '55' . preg_replace('/\D/', '', $numero) . '@c.us';
    $payload = json_encode(['to' => $numero_formatado, 'message' => $mensagem]);
    
    $ch = curl_init("http://212.85.11.238:3000/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['success' => $http_code === 200, 'response' => $response];
}

// Teste 1: Banco de dados
echo "<div class='test'>";
echo "<h3>1. Banco de Dados</h3>";
try {
    $result = $mysqli->query("SELECT 1 as teste");
    if ($result && $result->fetch_assoc()['teste'] == 1) {
        echo "<p class='success'>✅ Conexão OK</p>";
        $resultados[] = "Banco: OK";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    $resultados[] = "Banco: ERRO";
}
echo "</div>";

// Teste 2: Tabelas
echo "<div class='test'>";
echo "<h3>2. Tabelas do Sistema</h3>";
$tabelas = ['clientes_monitoramento', 'mensagens_agendadas', 'tickets', 'cobrancas', 'clientes'];
$tabelas_ok = 0;

foreach ($tabelas as $tabela) {
    $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✅ $tabela</p>";
        $tabelas_ok++;
    } else {
        echo "<p class='error'>❌ $tabela</p>";
    }
}

$resultados[] = "Tabelas: $tabelas_ok/" . count($tabelas);
echo "</div>";

// Teste 3: API Asaas
echo "<div class='test'>";
echo "<h3>3. API Asaas</h3>";
try {
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    if ($config && $config['valor']) {
        $ch = curl_init("https://www.asaas.com/api/v3/customers");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['access_token: ' . $config['valor'], 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            echo "<p class='success'>✅ API Asaas OK</p>";
            $resultados[] = "Asaas: OK";
        } else {
            echo "<p class='warning'>⚠️ HTTP $http_code</p>";
            $resultados[] = "Asaas: HTTP $http_code";
        }
    } else {
        echo "<p class='error'>❌ Chave não configurada</p>";
        $resultados[] = "Asaas: SEM CHAVE";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    $resultados[] = "Asaas: ERRO";
}
echo "</div>";

// Teste 4: WhatsApp
echo "<div class='test'>";
echo "<h3>4. WhatsApp VPS</h3>";
$mensagem_teste = "🧪 Teste rápido - " . date('d/m/Y H:i:s');
$resultado = enviarWhatsApp($NUMERO_TESTE, $mensagem_teste);

if ($resultado['success']) {
    echo "<p class='success'>✅ Mensagem enviada</p>";
    $resultados[] = "WhatsApp: OK";
} else {
    echo "<p class='error'>❌ Erro no envio</p>";
    $resultados[] = "WhatsApp: ERRO";
}
echo "</div>";

// Resumo
echo "<div class='test success'>";
echo "<h3>📊 Resumo</h3>";
foreach ($resultados as $resultado) {
    echo "<p>• $resultado</p>";
}

$sucessos = count(array_filter($resultados, function($r) { return strpos($r, 'OK') !== false; }));
$total = count($resultados);
$percentual = round(($sucessos / $total) * 100, 1);

echo "<p><strong>Taxa de Sucesso: $percentual% ($sucessos/$total)</strong></p>";
echo "</div>";

// Relatório final
$relatorio = "📋 TESTE RÁPIDO - SISTEMA PIXEL12\n\n";
$relatorio .= "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
$relatorio .= "Taxa de Sucesso: $percentual%\n\n";

foreach ($resultados as $resultado) {
    $relatorio .= "• $resultado\n";
}

$relatorio .= "\n🎯 STATUS: ";
if ($percentual >= 80) {
    $relatorio .= "SISTEMA OK! 🚀";
} elseif ($percentual >= 60) {
    $relatorio .= "FUNCIONAL COM AJUSTES ⚠️";
} else {
    $relatorio .= "PROBLEMAS DETECTADOS ❌";
}

$relatorio .= "\n\nAtenciosamente,\nSistema de Testes";

$resultado_final = enviarWhatsApp($NUMERO_TESTE, $relatorio);

if ($resultado_final['success']) {
    echo "<div class='test success'>";
    echo "<h3>📤 Relatório Enviado</h3>";
    echo "<p>✅ Relatório final enviado para seu WhatsApp</p>";
    echo "</div>";
} else {
    echo "<div class='test error'>";
    echo "<h3>❌ Erro no Relatório</h3>";
    echo "<p>Não foi possível enviar o relatório final</p>";
    echo "</div>";
}

echo "</div>";
?> 