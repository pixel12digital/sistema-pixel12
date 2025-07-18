<?php
require_once 'painel/config.php';
require_once 'painel/db.php';
require_once 'painel/cache_manager.php';

echo "=== LIMPEZA DE CACHE E ATUALIZAÇÃO DE CONVERSAS ===\n\n";

// 1. Limpar cache de conversas
echo "1. LIMPANDO CACHE DE CONVERSAS...\n";
cache_forget('conversas_recentes');
cache_forget('conversas_nao_lidas');
echo "✅ Cache limpo!\n\n";

// 2. Verificar conversas atuais
echo "2. VERIFICANDO CONVERSAS ATUAIS...\n";
$sql = "SELECT DISTINCT 
            c.id as cliente_id,
            c.nome,
            c.celular,
            cc.nome_exibicao as canal_nome,
            (SELECT m.mensagem FROM mensagens_comunicacao m WHERE m.cliente_id = c.id ORDER BY m.data_hora DESC LIMIT 1) as ultima_mensagem,
            (SELECT m.data_hora FROM mensagens_comunicacao m WHERE m.cliente_id = c.id ORDER BY m.data_hora DESC LIMIT 1) as ultima_data
        FROM clientes c
        LEFT JOIN mensagens_comunicacao mc ON c.id = mc.cliente_id
        LEFT JOIN canais_comunicacao cc ON mc.canal_id = cc.id
        WHERE mc.id IS NOT NULL
        GROUP BY c.id
        ORDER BY ultima_data DESC
        LIMIT 10";

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    echo "✅ Conversas encontradas:\n\n";
    while ($conv = $result->fetch_assoc()) {
        echo "Cliente: " . $conv['nome'] . " (ID: " . $conv['cliente_id'] . ")\n";
        echo "Canal: " . $conv['canal_nome'] . "\n";
        echo "Última mensagem: " . substr($conv['ultima_mensagem'], 0, 50) . "...\n";
        echo "Data: " . $conv['ultima_data'] . "\n";
        echo str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhuma conversa encontrada!\n";
}

// 3. Verificar mensagens recentes do Charles Dietrich
echo "\n3. VERIFICANDO MENSAGENS DO CHARLES DIETRICH...\n";
$cliente_id = 4296;
$sql_mensagens = "SELECT m.*, c.nome_exibicao as canal_nome 
                  FROM mensagens_comunicacao m 
                  LEFT JOIN canais_comunicacao c ON m.canal_id = c.id 
                  WHERE m.cliente_id = ? 
                  ORDER BY m.data_hora DESC 
                  LIMIT 5";

$stmt = $mysqli->prepare($sql_mensagens);
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result_mensagens = $stmt->get_result();

if ($result_mensagens && $result_mensagens->num_rows > 0) {
    echo "✅ Mensagens encontradas:\n\n";
    while ($msg = $result_mensagens->fetch_assoc()) {
        echo "ID: " . $msg['id'] . "\n";
        echo "Mensagem: " . substr($msg['mensagem'], 0, 50) . "...\n";
        echo "Direção: " . $msg['direcao'] . "\n";
        echo "Status: " . $msg['status'] . "\n";
        echo "Data: " . $msg['data_hora'] . "\n";
        echo str_repeat("-", 30) . "\n";
    }
} else {
    echo "❌ Nenhuma mensagem encontrada!\n";
}
$stmt->close();

// 4. Forçar recriação do cache
echo "\n4. RECRIANDO CACHE DE CONVERSAS...\n";
$conversas = cache_conversas($mysqli);
echo "✅ Cache recriado com " . count($conversas) . " conversas!\n";

// 5. Verificar se Charles Dietrich está na lista
echo "\n5. VERIFICANDO SE CHARLES DIETRICH ESTÁ NA LISTA...\n";
$encontrado = false;
foreach ($conversas as $conv) {
    if ($conv['cliente_id'] == $cliente_id) {
        echo "✅ Charles Dietrich encontrado na lista!\n";
        echo "Nome: " . $conv['nome'] . "\n";
        echo "Última mensagem: " . substr($conv['ultima_mensagem'], 0, 50) . "...\n";
        echo "Data: " . $conv['ultima_data'] . "\n";
        $encontrado = true;
        break;
    }
}

if (!$encontrado) {
    echo "❌ Charles Dietrich NÃO está na lista de conversas!\n";
    echo "Isso pode indicar um problema na query ou nos dados.\n";
}

echo "\n=== FIM DA LIMPEZA ===\n";
echo "Agora acesse o chat.php para ver se a conversa aparece na lista.\n";
?> 