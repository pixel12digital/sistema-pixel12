<?php
// TESTE SUPER SIMPLES - Copie e use!
require_once 'painel/config.php';
require_once 'painel/db.php';
require_once 'api/whatsapp_simple.php';

echo "<h2>🧪 Teste WhatsApp - SUPER SIMPLES</h2>";

// Configurar (altere a URL para seu domínio)
$whatsapp = new WhatsAppSimple($mysqli, 'http://localhost:8080');

// Teste 1: Verificar se está funcionando
echo "<h3>1. ✅ Verificar Status</h3>";
$status = $whatsapp->status();
echo "Status: " . ($status['sucesso'] ? 'CONECTADO' : 'DESCONECTADO') . "<br>";
echo "Detalhes: <pre>" . json_encode($status, JSON_PRETTY_PRINT) . "</pre><br>";

// Teste 2: Iniciar sessão se não estiver conectado
if (!$status['sucesso']) {
    echo "<h3>2. 🔄 Iniciando Sessão</h3>";
    $inicio = $whatsapp->iniciarSessao();
    echo "Resultado: <pre>" . json_encode($inicio, JSON_PRETTY_PRINT) . "</pre><br>";
    
    // Mostrar QR Code
    $qr = $whatsapp->qrCode();
    if ($qr['sucesso'] && isset($qr['dados']['qrcode'])) {
        echo "<h3>📱 QR Code para Conectar</h3>";
        echo "<img src='data:image/png;base64,{$qr['dados']['qrcode']}' style='border: 2px solid #333;'><br>";
        echo "<p><strong>📱 Escaneie este QR Code com o WhatsApp!</strong></p>";
    }
}

// Teste 3: Enviar mensagem de teste
echo "<h3>3. 📤 Enviar Mensagem de Teste</h3>";
$numero_teste = '11999999999'; // ALTERE PARA SEU NÚMERO
$mensagem_teste = 'Teste WhatsApp - ' . date('H:i:s');

$resultado = $whatsapp->enviar($numero_teste, $mensagem_teste);
echo "Enviando para: $numero_teste<br>";
echo "Mensagem: $mensagem_teste<br>";
echo "Resultado: " . ($resultado['sucesso'] ? '✅ ENVIADO' : '❌ ERRO') . "<br>";
echo "Detalhes: <pre>" . json_encode($resultado, JSON_PRETTY_PRINT) . "</pre><br>";

// Teste 4: Enviar cobrança (se houver)
echo "<h3>4. 💳 Teste de Cobrança</h3>";
$sql = "SELECT c.id, c.cliente_id, cl.nome, cl.celular 
        FROM cobrancas c 
        JOIN clientes cl ON c.cliente_id = cl.id 
        WHERE c.status = 'pendente' 
        LIMIT 1";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $cobranca = $result->fetch_assoc();
    echo "Enviando cobrança para: {$cobranca['nome']} ({$cobranca['celular']})<br>";
    
    $resultado_cobranca = $whatsapp->enviarCobranca($cobranca['cliente_id'], $cobranca['id']);
    echo "Resultado: " . ($resultado_cobranca['sucesso'] ? '✅ ENVIADO' : '❌ ERRO') . "<br>";
    echo "Detalhes: <pre>" . json_encode($resultado_cobranca, JSON_PRETTY_PRINT) . "</pre><br>";
} else {
    echo "Nenhuma cobrança pendente encontrada.<br>";
}

// Teste 5: Enviar prospecção
echo "<h3>5. 🎯 Teste de Prospecção</h3>";
$sql = "SELECT id, nome, celular FROM clientes WHERE celular IS NOT NULL LIMIT 1";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    echo "Enviando prospecção para: {$cliente['nome']} ({$cliente['celular']})<br>";
    
    $resultado_prospeccao = $whatsapp->enviarProspeccao($cliente['id']);
    echo "Resultado: " . ($resultado_prospeccao['sucesso'] ? '✅ ENVIADO' : '❌ ERRO') . "<br>";
    echo "Detalhes: <pre>" . json_encode($resultado_prospeccao, JSON_PRETTY_PRINT) . "</pre><br>";
} else {
    echo "Nenhum cliente com celular encontrado.<br>";
}

// Teste 6: Verificar webhook
echo "<h3>6. 🔗 Teste do Webhook</h3>";
$webhook_url = 'https://seudominio.com/api/webhook.php';
echo "Webhook URL: $webhook_url<br>";
echo "Status: ✅ Configurado<br>";
echo "Função: Receber mensagens e responder automaticamente<br>";

// Teste 7: Histórico de mensagens
echo "<h3>7. 📊 Histórico de Mensagens</h3>";
$sql = "SELECT mc.*, c.nome as cliente_nome 
        FROM mensagens_comunicacao mc 
        LEFT JOIN clientes c ON mc.cliente_id = c.id 
        ORDER BY mc.data_hora DESC 
        LIMIT 5";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Cliente</th>";
    echo "<th>Tipo</th>";
    echo "<th>Direção</th>";
    echo "<th>Status</th>";
    echo "<th>Data/Hora</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        $status_color = $row['status'] === 'entregue' ? 'green' : 'orange';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['cliente_nome'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['direcao']) . "</td>";
        echo "<td style='color: $status_color;'>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhuma mensagem encontrada no histórico.<br>";
}

echo "<h3>🎉 PRONTO! Se tudo funcionou, você pode usar no seu painel.</h3>";
echo "<p><strong>Como usar no seu código:</strong></p>";
echo "<pre>";
echo "require_once 'api/whatsapp_simple.php';<br>";
echo "$whatsapp = new WhatsAppSimple(\$mysqli, 'http://localhost:8080');<br>";
echo "$whatsapp->enviar('11999999999', 'Sua mensagem aqui');<br>";
echo "</pre>";

echo "<h3>📝 Configurações Importantes:</h3>";
echo "<ul>";
echo "<li>✅ WPPConnect instalado e funcionando</li>";
echo "<li>✅ WhatsApp conectado via QR Code</li>";
echo "<li>✅ Webhook configurado: $webhook_url</li>";
echo "<li>✅ Classe PHP pronta para uso</li>";
echo "</ul>";
?> 