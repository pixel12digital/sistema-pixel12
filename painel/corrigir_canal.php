<?php
require_once 'config.php';
require_once 'db.php';

echo "🔧 Corrigindo problemas do sistema...\n\n";

// 1. Atualizar status do canal para conectado
echo "1. ✅ Atualizando status do canal...\n";
$result = $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE id = 36");
if ($result) {
    echo "   ✅ Canal ID 36 atualizado para 'conectado'\n";
} else {
    echo "   ❌ Erro ao atualizar canal: " . $mysqli->error . "\n";
}

// 2. Limpar cache se existir
echo "\n2. 🗄️ Limpando cache...\n";
$cache_files = glob('cache/*.cache');
if (count($cache_files) > 0) {
    foreach ($cache_files as $file) {
        unlink($file);
    }
    echo "   ✅ " . count($cache_files) . " arquivos de cache removidos\n";
} else {
    echo "   ℹ️ Nenhum arquivo de cache encontrado\n";
}

// 3. Verificar últimas mensagens no chat
echo "\n3. 📱 Verificando últimas mensagens no chat...\n";
$result = $mysqli->query("
    SELECT m.*, c.nome as cliente_nome 
    FROM mensagens_comunicacao m
    LEFT JOIN clientes c ON m.cliente_id = c.id
    WHERE m.direcao = 'recebido'
    AND DATE(m.data_hora) = CURDATE()
    ORDER BY m.data_hora DESC
    LIMIT 3
");

if ($result && $result->num_rows > 0) {
    while ($msg = $result->fetch_assoc()) {
        $cliente = $msg['cliente_nome'] ?? 'Cliente não identificado';
        $hora = date('H:i:s', strtotime($msg['data_hora']));
        echo "   📥 [$hora] $cliente: " . substr($msg['mensagem'], 0, 30) . "...\n";
    }
} else {
    echo "   ⚠️ Nenhuma mensagem encontrada\n";
}

// 4. Marcar mensagens como não lidas para teste
echo "\n4. 🔄 Marcando mensagens recentes como não lidas...\n";
$result = $mysqli->query("
    UPDATE mensagens_comunicacao 
    SET status = 'recebido' 
    WHERE direcao = 'recebido' 
    AND DATE(data_hora) = CURDATE()
    AND status = 'lido'
");

if ($result) {
    $affected = $mysqli->affected_rows;
    echo "   ✅ $affected mensagens marcadas como não lidas\n";
} else {
    echo "   ❌ Erro ao atualizar mensagens: " . $mysqli->error . "\n";
}

echo "\n✅ Correções aplicadas!\n";
echo "\nAgora:\n";
echo "1. Acesse o chat: http://localhost:8080/loja-virtual-revenda/painel/chat.php\n";
echo "2. As mensagens devem aparecer como não lidas\n";
echo "3. Teste enviando uma nova mensagem para: 554797146908\n";
?> 