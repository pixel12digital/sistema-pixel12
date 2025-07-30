<?php
/**
 * 🧪 TESTE DE RESPOSTA PARA "FATURAS" E "CONSULTA"
 * Testa se o sistema está processando corretamente as mensagens
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'painel/db.php';

echo "<h2>🧪 Teste de Resposta para 'Faturas' e 'Consulta'</h2>";

// Buscar cliente específico (Detetive Aguiar - 69 9324-5042)
$numero_teste = '6993245042';
$numero_limpo = preg_replace('/\D/', '', $numero_teste);

echo "<h3>🔍 Buscando cliente: $numero_teste</h3>";

$sql = "SELECT id, nome, celular, contact_name FROM clientes 
        WHERE celular LIKE '%$numero_limpo%' 
        OR celular LIKE '%$numero_teste%'
        LIMIT 1";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    echo "<p>✅ Cliente encontrado:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nome'] . "</li>";
    echo "<li><strong>Celular:</strong> " . $cliente['celular'] . "</li>";
    echo "<li><strong>Contact Name:</strong> " . $cliente['contact_name'] . "</li>";
    echo "</ul>";
    
    $cliente_id = $cliente['id'];
    
    // Verificar se cliente está sendo monitorado
    $sql_monitor = "SELECT monitorado FROM clientes_monitoramento WHERE cliente_id = $cliente_id LIMIT 1";
    $result_monitor = $mysqli->query($sql_monitor);
    $monitorado = $result_monitor->fetch_assoc();
    
    echo "<h3>📊 Status de Monitoramento</h3>";
    if ($monitorado && $monitorado['monitorado'] == 1) {
        echo "<p>✅ Cliente está sendo monitorado</p>";
    } else {
        echo "<p>❌ Cliente NÃO está sendo monitorado</p>";
    }
    
    // Verificar faturas do cliente
    echo "<h3>💰 Faturas do Cliente</h3>";
    $sql_faturas = "SELECT 
                        cob.id,
                        cob.valor,
                        cob.status,
                        DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                        cob.url_fatura
                    FROM cobrancas cob
                    WHERE cob.cliente_id = $cliente_id
                    ORDER BY cob.vencimento DESC
                    LIMIT 10";
    
    $result_faturas = $mysqli->query($sql_faturas);
    
    if ($result_faturas && $result_faturas->num_rows > 0) {
        echo "<p>✅ Cliente possui " . $result_faturas->num_rows . " faturas:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Valor</th><th>Vencimento</th><th>Status</th><th>URL</th></tr>";
        
        while ($fatura = $result_faturas->fetch_assoc()) {
            $valor = number_format($fatura['valor'], 2, ',', '.');
            $status = $fatura['status'];
            $url = $fatura['url_fatura'] ? 'Sim' : 'Não';
            
            echo "<tr>";
            echo "<td>" . $fatura['id'] . "</td>";
            echo "<td>R$ $valor</td>";
            echo "<td>" . $fatura['vencimento_formatado'] . "</td>";
            echo "<td>$status</td>";
            echo "<td>$url</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Cliente NÃO possui faturas cadastradas</p>";
    }
    
    // Testar função de busca de faturas
    echo "<h3>🧪 Testando Função buscarFaturasCliente()</h3>";
    
    // Incluir a função de teste
    function buscarFaturasCliente($cliente_id, $mysqli) {
        $sql = "SELECT 
                    cob.id,
                    cob.valor,
                    cob.status,
                    DATE_FORMAT(cob.vencimento, '%d/%m/%Y') as vencimento_formatado,
                    cob.url_fatura
                FROM cobrancas cob
                WHERE cob.cliente_id = $cliente_id
                ORDER BY cob.vencimento DESC
                LIMIT 10";
        
        $result = $mysqli->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            return "Você não possui faturas cadastradas no momento.";
        }
        
        $resposta = "📋 Suas faturas:\n\n";
        
        while ($fatura = $result->fetch_assoc()) {
            $status = traduzirStatus($fatura['status']);
            $valor = number_format($fatura['valor'], 2, ',', '.');
            
            $resposta .= "Fatura #{$fatura['id']}\n";
            $resposta .= "Valor: R$ $valor\n";
            $resposta .= "Vencimento: {$fatura['vencimento_formatado']}\n";
            $resposta .= "Status: $status\n";
            
            if ($fatura['url_fatura']) {
                $resposta .= "Link: {$fatura['url_fatura']}\n";
            }
            
            $resposta .= "\n";
        }
        
        return $resposta;
    }
    
    function traduzirStatus($status) {
        $statusMap = [
            'PENDING' => 'Aguardando pagamento',
            'OVERDUE' => 'Vencida',
            'RECEIVED' => 'Paga',
            'CONFIRMED' => 'Confirmada',
            'CANCELLED' => 'Cancelada'
        ];
        return $statusMap[$status] ?? $status;
    }
    
    $resposta_faturas = buscarFaturasCliente($cliente_id, $mysqli);
    echo "<h4>Resposta gerada:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($resposta_faturas);
    echo "</pre>";
    
    // Verificar mensagens recentes do cliente
    echo "<h3>💬 Mensagens Recentes do Cliente</h3>";
    $sql_mensagens = "SELECT * FROM mensagens_comunicacao 
                      WHERE cliente_id = $cliente_id 
                      ORDER BY data_hora DESC 
                      LIMIT 10";
    
    $result_mensagens = $mysqli->query($sql_mensagens);
    
    if ($result_mensagens && $result_mensagens->num_rows > 0) {
        echo "<p>Últimas mensagens:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Data/Hora</th><th>Direção</th><th>Mensagem</th><th>Tipo</th></tr>";
        
        while ($msg = $result_mensagens->fetch_assoc()) {
            $mensagem_short = substr($msg['mensagem'], 0, 50) . (strlen($msg['mensagem']) > 50 ? '...' : '');
            echo "<tr>";
            echo "<td>" . $msg['data_hora'] . "</td>";
            echo "<td>" . $msg['direcao'] . "</td>";
            echo "<td>" . htmlspecialchars($mensagem_short) . "</td>";
            echo "<td>" . $msg['tipo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma mensagem encontrada para este cliente.</p>";
    }
    
} else {
    echo "<p>❌ Cliente NÃO encontrado no banco de dados</p>";
    echo "<p>Número testado: $numero_teste</p>";
    echo "<p>Número limpo: $numero_limpo</p>";
}

// Verificar se existem mensagens com "faturas" ou "consulta" no sistema
echo "<h3>🔍 Verificando Mensagens com 'faturas' ou 'consulta'</h3>";

$sql_geral = "SELECT * FROM mensagens_comunicacao 
              WHERE mensagem LIKE '%fatura%' 
              OR mensagem LIKE '%consulta%'
              OR mensagem LIKE '%faturas%'
              ORDER BY data_hora DESC 
              LIMIT 10";

$result_geral = $mysqli->query($sql_geral);

if ($result_geral && $result_geral->num_rows > 0) {
    echo "<p>Mensagens encontradas com 'faturas' ou 'consulta':</p>";
    while ($msg = $result_geral->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        echo "<strong>Data:</strong> " . $msg['data_hora'] . "<br>";
        echo "<strong>Cliente ID:</strong> " . $msg['cliente_id'] . "<br>";
        echo "<strong>Direção:</strong> " . $msg['direcao'] . "<br>";
        echo "<strong>Mensagem:</strong> " . htmlspecialchars($msg['mensagem']) . "<br>";
        echo "</div>";
    }
} else {
    echo "<p>Nenhuma mensagem encontrada com 'faturas' ou 'consulta'</p>";
}

echo "<h3>📊 Estatísticas Gerais</h3>";

// Total de mensagens hoje
$sql_stats = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN direcao = 'recebido' THEN 1 END) as recebidas,
                COUNT(CASE WHEN direcao = 'enviado' THEN 1 END) as enviadas
              FROM mensagens_comunicacao 
              WHERE DATE(data_hora) = CURDATE()";

$result_stats = $mysqli->query($sql_stats);
$stats = $result_stats->fetch_assoc();

echo "<p><strong>Hoje:</strong> {$stats['total']} mensagens ({$stats['recebidas']} recebidas, {$stats['enviadas']} enviadas)</p>";

echo "<hr>";
echo "<p><em>Teste concluído em: " . date('d/m/Y H:i:s') . "</em></p>";
?> 