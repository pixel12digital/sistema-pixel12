<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>📡 Verificação de Recebimento - Múltiplos Canais</h1>";

echo "<h2>📊 Status dos Canais:</h2>";

// Verificar status dos canais
$canais = $mysqli->query("SELECT id, nome_exibicao, porta, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY porta");

echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<thead>";
echo "<tr style='background: #f8fafc;'>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Canal</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Porta</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Status</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Banco de Dados</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($canal = $canais->fetch_assoc()) {
    $status_color = $canal['status'] === 'conectado' ? '#22c55e' : '#ef4444';
    
    // Determinar banco de dados baseado na porta
    $banco = '';
    switch ($canal['porta']) {
        case 3000:
            $banco = 'pixel12digital (principal)';
            break;
        case 3001:
            $banco = 'pixel12digital_comercial';
            break;
        case 3002:
            $banco = 'pixel12digital_suporte';
            break;
        case 3003:
            $banco = 'pixel12digital_vendas';
            break;
        default:
            $banco = 'pixel12digital (principal)';
    }
    
    echo "<tr>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600;'>{$canal['nome_exibicao']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$canal['porta']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; color: $status_color;'>{$canal['status']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem;'>$banco</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";

echo "<h2>🗄️ Informações sobre Bancos de Dados:</h2>";
echo "<div style='background: #f0f9ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<h3 style='margin: 0 0 0.5rem 0; color: #1e40af;'>📊 Arquitetura Multi-Canal com Bancos Separados</h3>";
echo "<p><strong>🎯 Conceito:</strong> Cada canal WhatsApp usa um banco de dados separado para isolamento completo.</p>";
echo "<ul>";
echo "<li><strong>Porta 3000 (Financeiro):</strong> Banco <code>pixel12digital</code> - Principal</li>";
echo "<li><strong>Porta 3001 (Comercial):</strong> Banco <code>pixel12digital_comercial</code> - Comercial</li>";
echo "<li><strong>Porta 3002 (Suporte):</strong> Banco <code>pixel12digital_suporte</code> - Suporte</li>";
echo "<li><strong>Porta 3003 (Vendas):</strong> Banco <code>pixel12digital_vendas</code> - Vendas</li>";
echo "</ul>";
echo "<p><strong>💡 Vantagens:</strong> Isolamento completo, backup independente, escalabilidade.</p>";
echo "</div>";

echo "<h2>🌐 Teste de Conectividade dos Canais:</h2>";

// Testar conectividade de cada canal
$canais_test = $mysqli->query("SELECT id, nome_exibicao, porta FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY porta");

while ($canal = $canais_test->fetch_assoc()) {
    $api_url = WHATSAPP_ROBOT_URL;
    if ($canal['porta'] == 3001) {
        $api_url = str_replace(':3000', ':3001', WHATSAPP_ROBOT_URL);
    }
    $status_url = $api_url . "/status";
    
    echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
    echo "<h3 style='margin: 0 0 0.5rem 0; color: #1e293b;'>{$canal['nome_exibicao']} (Porta {$canal['porta']})</h3>";
    echo "<p><strong>URL:</strong> $status_url</p>";
    
    $ch = curl_init($status_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "<p style='color: #ef4444;'>❌ Erro de conexão: $curl_error</p>";
    } else {
        echo "<p style='color: #22c55e;'>✅ Conexão estabelecida (HTTP: $http_code)</p>";
        echo "<p><strong>Resposta:</strong> $response</p>";
    }
    
    echo "</div>";
}

echo "<h2>📨 Mensagens Recebidas por Canal:</h2>";

// Verificar mensagens recebidas por canal
$sql = "SELECT m.*, 
               c.nome_exibicao as canal_nome,
               c.porta as canal_porta,
               cl.nome as cliente_nome,
               CASE 
                   WHEN m.direcao = 'enviado' THEN 'Você'
                   WHEN m.direcao = 'recebido' THEN c.nome_exibicao
                   ELSE 'Sistema'
               END as contato_interagiu
        FROM mensagens_comunicacao m
        LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
        LEFT JOIN clientes cl ON m.cliente_id = cl.id
        WHERE m.direcao = 'recebido'
        ORDER BY m.data_hora DESC
        LIMIT 20";

$mensagens = $mysqli->query($sql);

if ($mensagens->num_rows > 0) {
    echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
    
    while ($msg = $mensagens->fetch_assoc()) {
        $time = date('d/m H:i', strtotime($msg['data_hora']));
        $contatoInfo = $msg['contato_interagiu'];
        $canalInfo = $msg['canal_nome'] ? "via {$msg['canal_nome']}" : '';
        
        echo "<div style='margin-bottom: 1rem; padding: 0.5rem; border-radius: 6px; background: #f0f9ff; border-left: 3px solid #3b82f6;'>";
        
        // Identificação do contato
        echo "<div style='display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-size: 0.75rem;'>";
        echo "<span style='font-weight: 600; color: #3b82f6; background: #dbeafe; padding: 0.2rem 0.5rem; border-radius: 12px; border: 1px solid #3b82f6; font-size: 0.7rem; text-transform: uppercase;'>$contatoInfo</span>";
        
        if ($canalInfo) {
            echo "<span style='color: #64748b; font-size: 0.65rem; font-style: italic; background: #e0e7ff; padding: 0.15rem 0.4rem; border-radius: 8px; border: 1px solid #6366f1;'>$canalInfo</span>";
        }
        
        echo "<span style='color: #64748b; font-size: 0.65rem;'>Cliente: {$msg['cliente_nome']}</span>";
        echo "</div>";
        
        // Mensagem
        echo "<div style='font-size: 0.9rem; margin-bottom: 0.25rem;'>" . htmlspecialchars($msg['mensagem']) . "</div>";
        
        // Horário
        echo "<div style='font-size: 0.75rem; color: #64748b;'>$time</div>";
        
        echo "</div>";
    }
    
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma mensagem recebida encontrada</p>";
}

echo "<h2>📊 Estatísticas por Canal:</h2>";

// Estatísticas de mensagens por canal
$stats = $mysqli->query("SELECT 
    c.nome_exibicao as canal_nome,
    c.porta as canal_porta,
    COUNT(*) as total_mensagens,
    SUM(CASE WHEN m.direcao = 'enviado' THEN 1 ELSE 0 END) as enviadas,
    SUM(CASE WHEN m.direcao = 'recebido' THEN 1 ELSE 0 END) as recebidas,
    MAX(m.data_hora) as ultima_atividade
FROM mensagens_comunicacao m
LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
WHERE c.tipo = 'whatsapp'
GROUP BY c.id, c.nome_exibicao, c.porta
ORDER BY c.porta");

echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<thead>";
echo "<tr style='background: #f8fafc;'>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Canal</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Porta</th>";
echo "<th style='padding: 0.5rem; text-align: center; border-bottom: 1px solid #e2e8f0;'>Total</th>";
echo "<th style='padding: 0.5rem; text-align: center; border-bottom: 1px solid #e2e8f0;'>Enviadas</th>";
echo "<th style='padding: 0.5rem; text-align: center; border-bottom: 1px solid #e2e8f0;'>Recebidas</th>";
echo "<th style='padding: 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0;'>Última Atividade</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($stat = $stats->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600;'>{$stat['canal_nome']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$stat['canal_porta']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; text-align: center; font-weight: 600;'>{$stat['total_mensagens']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; text-align: center; color: #22c55e;'>{$stat['enviadas']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0; text-align: center; color: #3b82f6;'>{$stat['recebidas']}</td>";
    echo "<td style='padding: 0.5rem; border-bottom: 1px solid #e2e8f0;'>{$stat['ultima_atividade']}</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";

echo "<h2>🔧 Como Testar o Recebimento:</h2>";
echo "<ol>";
echo "<li><strong>Envie uma mensagem para o número 554796164699 via WhatsApp</strong></li>";
echo "<li>Verifique se a mensagem aparece no chat do sistema</li>";
echo "<li>Confirme se a identificação do canal está correta</li>";
echo "<li>Teste enviando para ambos os canais (Financeiro e Comercial)</li>";
echo "</ol>";

echo "<h2>🎯 Verificação no Chat:</h2>";
echo "<ol>";
echo "<li>Acesse: <a href='painel/chat.php?cliente_id=4296' target='_blank'>Chat com Charles</a></li>";
echo "<li>Verifique se as mensagens recebidas mostram a identificação correta do canal</li>";
echo "<li>Mensagens do Financeiro devem mostrar 'FINANCEIRO via Financeiro'</li>";
echo "<li>Mensagens do Comercial devem mostrar 'COMERCIAL - PIXEL via Comercial - Pixel'</li>";
echo "</ol>";

echo "<h2>💡 Importante sobre Bancos Separados:</h2>";
echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem; margin: 1rem 0;'>";
echo "<p><strong>⚠️ Nota:</strong> Cada canal usa um banco de dados separado. As mensagens mostradas acima são apenas do banco atual (principal).</p>";
echo "<p>Para ver mensagens de outros canais, seria necessário conectar aos respectivos bancos:</p>";
echo "<ul>";
echo "<li><strong>Comercial:</strong> Banco <code>pixel12digital_comercial</code></li>";
echo "<li><strong>Suporte:</strong> Banco <code>pixel12digital_suporte</code></li>";
echo "<li><strong>Vendas:</strong> Banco <code>pixel12digital_vendas</code></li>";
echo "</ul>";
echo "</div>";

echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>🎉 Sistema de identificação de canais implementado!</p>";
?> 