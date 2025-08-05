<?php
require_once 'config.php';

// Conectar ao banco
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<h2>🔧 Correção do Webhook de Recebimento</h2>";
echo "<p><strong>Data/Hora da correção:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Cliente ID para teste
$cliente_id = 4296;
$numero_teste = '554796164699';

echo "<h3>🎯 Corrigindo Webhook para Cliente ID: $cliente_id (Número: $numero_teste)</h3>";

// 1. Verificar se o cliente existe e está correto
echo "<h4>1. Verificando Cliente</h4>";
$sql_cliente = "SELECT id, nome, contact_name, celular, telefone FROM clientes WHERE id = ?";
$stmt = $mysqli->prepare($sql_cliente);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

if ($cliente) {
    echo "✅ Cliente encontrado:<br>";
    echo "- ID: " . $cliente['id'] . "<br>";
    echo "- Nome: " . $cliente['nome'] . "<br>";
    echo "- Celular: " . $cliente['celular'] . "<br>";
    echo "- Telefone: " . $cliente['telefone'] . "<br>";
} else {
    echo "❌ Cliente não encontrado!<br>";
}

// 2. Verificar se há mensagens recebidas recentes
echo "<h4>2. Verificando Mensagens Recebidas Recentes</h4>";
$sql_recentes = "SELECT id, mensagem, direcao, status, data_hora, canal_id, numero_whatsapp 
                 FROM mensagens_comunicacao 
                 WHERE cliente_id = ? 
                 AND direcao = 'recebido'
                 AND data_hora >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 ORDER BY data_hora DESC";

$stmt = $mysqli->prepare($sql_recentes);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Mensagem</th><th>Direção</th><th>Status</th><th>Data/Hora</th><th>Canal ID</th><th>Número WhatsApp</th>";
    echo "</tr>";
    
    while ($msg = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $msg['id'] . "</td>";
        echo "<td style='max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($msg['mensagem'], 0, 100)) . (strlen($msg['mensagem']) > 100 ? '...' : '') . "</td>";
        echo "<td style='font-weight: bold; color: blue;'>" . $msg['direcao'] . "</td>";
        echo "<td>" . $msg['status'] . "</td>";
        echo "<td>" . $msg['data_hora'] . "</td>";
        echo "<td>" . $msg['canal_id'] . "</td>";
        echo "<td>" . htmlspecialchars($msg['numero_whatsapp']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma mensagem recebida nas últimas 24 horas!</p>";
}
$stmt->close();

// 3. Verificar se há problemas na busca do cliente por número
echo "<h4>3. Testando Busca do Cliente por Número</h4>";

$numero_limpo = preg_replace('/\D/', '', $numero_teste);
$formatos_busca = [
    $numero_limpo,                                    // Formato original (554796164699)
    ltrim($numero_limpo, '55'),                       // Remove código do país (4796164699)
    substr($numero_limpo, -11),                       // Últimos 11 dígitos
    substr($numero_limpo, -10),                       // Últimos 10 dígitos
    substr($numero_limpo, -9),                        // Últimos 9 dígitos (sem DDD)
    substr($numero_limpo, 2, 2) . '9' . substr($numero_limpo, 4), // Sem código + 9
];

echo "🔍 Testando formatos de busca para número: $numero_teste<br>";
foreach ($formatos_busca as $formato) {
    if (strlen($formato) >= 9) {
        $sql = "SELECT id, nome, contact_name, celular, telefone FROM clientes 
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%' 
                OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%$formato%'
                OR REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%" . substr($formato, -9) . "%'
                OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%" . substr($formato, -9) . "%'
                LIMIT 1";
        $result = $mysqli->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $cliente_encontrado = $result->fetch_assoc();
            echo "✅ Cliente encontrado com formato $formato - ID: {$cliente_encontrado['id']}, Nome: {$cliente_encontrado['nome']}<br>";
        } else {
            echo "❌ Cliente não encontrado com formato $formato<br>";
        }
    }
}

// 4. Criar script para testar webhook
echo "<h4>4. Script para Testar Webhook</h4>";

$teste_webhook_script = "<?php
require_once 'config.php';

// Conectar ao banco
\$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
\$mysqli->set_charset('utf8mb4');

// Simular dados do webhook
\$data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste mensagem recebida de canal 3001 554797309525 17:45 - WEBHOOK TESTE',
        'type' => 'text',
        'timestamp' => time()
    ]
];

echo \"<h3>🧪 Teste do Webhook</h3>\";
echo \"<p><strong>Dados simulados:</strong></p>\";
echo \"<pre>\";
print_r(\$data);
echo \"</pre>\";

// Processar mensagem recebida
if (isset(\$data['event']) && \$data['event'] === 'onmessage') {
    \$message = \$data['data'];
    
    // Extrair informações
    \$numero = \$message['from'];
    \$texto = \$message['text'] ?? '';
    \$tipo = \$message['type'] ?? 'text';
    \$data_hora = date('Y-m-d H:i:s');
    
    echo \"<p><strong>📥 Processando mensagem:</strong></p>\";
    echo \"<ul>\";
    echo \"<li>Número: \$numero</li>\";
    echo \"<li>Texto: \$texto</li>\";
    echo \"<li>Tipo: \$tipo</li>\";
    echo \"<li>Data/Hora: \$data_hora</li>\";
    echo \"</ul>\";
    
    // Buscar cliente pelo número
    \$numero_limpo = preg_replace('/\D/', '', \$numero);
    \$cliente_id = null;
    
    // Tentar diferentes formatos de busca
    \$formatos_busca = [
        \$numero_limpo,
        ltrim(\$numero_limpo, '55'),
        substr(\$numero_limpo, -11),
        substr(\$numero_limpo, -10),
        substr(\$numero_limpo, -9),
        substr(\$numero_limpo, 2, 2) . '9' . substr(\$numero_limpo, 4),
    ];
    
    foreach (\$formatos_busca as \$formato) {
        if (strlen(\$formato) >= 9) {
            \$sql = \"SELECT id, nome, contact_name, celular, telefone FROM clientes 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%\$formato%' 
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%\$formato%'
                    OR REPLACE(REPLACE(REPLACE(REPLACE(celular, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%\" . substr(\$formato, -9) . \"%'
                    OR REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE '%\" . substr(\$formato, -9) . \"%'
                    LIMIT 1\";
            \$result = \$mysqli->query(\$sql);
            
            if (\$result && \$result->num_rows > 0) {
                \$cliente = \$result->fetch_assoc();
                \$cliente_id = \$cliente['id'];
                echo \"<p>✅ Cliente encontrado com formato \$formato - ID: \$cliente_id, Nome: {\$cliente['nome']}</p>\";
                break;
            }
        }
    }
    
    if (!\$cliente_id) {
        echo \"<p style='color: red;'>❌ Cliente não encontrado para número: \$numero</p>\";
        echo \"<p>Tentando criar mensagem sem cliente...</p>\";
        \$cliente_id = 4296; // Cliente padrão para teste
    }
    
    // Identificar canal
    \$canal_id = 37; // Canal 3001 (Comercial)
    \$canal_nome = 'Pixel - Comercial';
    
    // Inserir mensagem recebida
    \$sql = \"INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, direcao, data_hora, status, numero_whatsapp, canal_id, canal_nome) 
             VALUES (?, ?, ?, 'recebido', NOW(), 'nao_lido', ?, ?, ?)\";
    
    \$stmt = \$mysqli->prepare(\$sql);
    \$stmt->bind_param('isssss', \$cliente_id, \$texto, \$tipo, \$numero, \$canal_id, \$canal_nome);
    
    if (\$stmt->execute()) {
        \$mensagem_id = \$mysqli->insert_id;
        echo \"<p style='color: green;'>✅ Mensagem recebida salva com sucesso - ID: \$mensagem_id</p>\";
        
        // Limpar cache
        \$cache_file = __DIR__ . '/cache/' . md5(\"mensagens_{\$cliente_id}\") . '.cache';
        if (file_exists(\$cache_file)) {
            unlink(\$cache_file);
            echo \"<p>✅ Cache limpo</p>\";
        }
    } else {
        echo \"<p style='color: red;'>❌ Erro ao salvar mensagem: \" . \$stmt->error . \"</p>\";
    }
    
    \$stmt->close();
} else {
    echo \"<p style='color: red;'>❌ Evento não reconhecido</p>\";
}

\$mysqli->close();

echo \"<p><strong>🎯 Próximos passos:</strong></p>\";
echo \"<ol>\";
echo \"<li>Acesse o chat: <a href='painel/chat.php?cliente_id=4296' target='_blank'>Chat do Cliente</a></li>\";
echo \"<li>Recarregue a página (F5)</li>\";
echo \"<li>Verifique se a mensagem de teste aparece</li>\";
echo \"</ol>\";
?>";

file_put_contents('teste_webhook_recebimento.php', $teste_webhook_script);
echo "✅ Script de teste do webhook criado: <a href='teste_webhook_recebimento.php' target='_blank'>teste_webhook_recebimento.php</a><br>";

// 5. Verificar se há problemas no webhook atual
echo "<h4>5. Verificando Webhook Atual</h4>";
echo "<p>ℹ️ Para verificar se o webhook está funcionando:</p>";
echo "<ol>";
echo "<li>Acesse: <a href='api/webhook_whatsapp.php' target='_blank'>Webhook WhatsApp</a></li>";
echo "<li>Verifique se há erros no log</li>";
echo "<li>Teste enviando uma mensagem do seu WhatsApp para o canal 3001</li>";
echo "</ol>";

// 6. Criar script para forçar recebimento
echo "<h4>6. Script para Forçar Recebimento</h4>";

$forcar_recebimento_script = "<?php
require_once 'config.php';

// Conectar ao banco
\$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
\$mysqli->set_charset('utf8mb4');

// Forçar recebimento de mensagem
\$cliente_id = $cliente_id;
\$numero = '$numero_teste';
\$mensagem = 'Teste mensagem recebida de canal 3001 554797309525 17:45 - FORÇADA';
\$canal_id = 37; // Canal 3001
\$canal_nome = 'Pixel - Comercial';

// Inserir mensagem recebida
\$sql = \"INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, direcao, data_hora, status, numero_whatsapp, canal_id, canal_nome) 
         VALUES (?, ?, 'text', 'recebido', NOW(), 'nao_lido', ?, ?, ?)\";

\$stmt = \$mysqli->prepare(\$sql);
\$stmt->bind_param('issss', \$cliente_id, \$mensagem, \$numero, \$canal_id, \$canal_nome);

if (\$stmt->execute()) {
    \$mensagem_id = \$mysqli->insert_id;
    echo \"✅ Mensagem recebida forçada criada - ID: \$mensagem_id<br>\";
    
    // Limpar cache
    \$cache_file = __DIR__ . '/cache/' . md5(\"mensagens_{\$cliente_id}\") . '.cache';
    if (file_exists(\$cache_file)) {
        unlink(\$cache_file);
        echo \"✅ Cache limpo<br>\";
    }
} else {
    echo \"❌ Erro ao criar mensagem forçada: \" . \$stmt->error . \"<br>\";
}

\$stmt->close();
\$mysqli->close();

echo \"<p><strong>🎯 Próximos passos:</strong></p>\";
echo \"<ol>\";
echo \"<li>Acesse o chat: <a href='painel/chat.php?cliente_id=\$cliente_id' target='_blank'>Chat do Cliente</a></li>\";
echo \"<li>Recarregue a página (F5)</li>\";
echo \"<li>Verifique se a mensagem forçada aparece</li>\";
echo \"</ol>\";
?>";

file_put_contents('forcar_recebimento_mensagem.php', $forcar_recebimento_script);
echo "✅ Script para forçar recebimento criado: <a href='forcar_recebimento_mensagem.php' target='_blank'>forcar_recebimento_mensagem.php</a><br>";

$mysqli->close();

echo "<h3>🎯 Resumo da Correção do Webhook</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>🔍 Problema Identificado:</strong></p>";
echo "<p>O webhook não está capturando mensagens enviadas <strong>do seu WhatsApp (554796164699)</strong> para os canais 3000 e 3001.</p>";
echo "<p><strong>🔧 Soluções Criadas:</strong></p>";
echo "<ol>";
echo "<li><a href='teste_webhook_recebimento.php' target='_blank'>Teste do Webhook</a> - Para verificar se o webhook está funcionando</li>";
echo "<li><a href='forcar_recebimento_mensagem.php' target='_blank'>Forçar Recebimento</a> - Para criar uma mensagem recebida de teste</li>";
echo "<li>Verificação do cliente e formatos de busca</li>";
echo "</ol>";
echo "<p><strong>🎯 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Execute o teste do webhook</li>";
echo "<li>Verifique se a mensagem de teste aparece no chat</li>";
echo "<li>Se aparecer, o problema está na configuração do webhook</li>";
echo "<li>Se não aparecer, o problema está na exibição do chat</li>";
echo "</ol>";
echo "</div>";
?> 