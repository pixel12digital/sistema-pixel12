<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CORRIGINDO NOME DO CANAL 37\n";
echo "==============================\n\n";

// Verificar nome atual
$sql = "SELECT nome_exibicao FROM canais_comunicacao WHERE id = 37";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "📋 Nome atual do canal 37: {$canal['nome_exibicao']}\n";
    
    if ($canal['nome_exibicao'] !== 'Comercial - Pixel') {
        echo "🔧 Atualizando para 'Comercial - Pixel'...\n";
        
        $sql_update = "UPDATE canais_comunicacao SET nome_exibicao = 'Comercial - Pixel' WHERE id = 37";
        if ($mysqli->query($sql_update)) {
            echo "✅ Canal 37 atualizado com sucesso!\n";
            
            // Verificar se foi atualizado
            $result = $mysqli->query($sql);
            if ($result && $result->num_rows > 0) {
                $canal = $result->fetch_assoc();
                echo "📋 Novo nome: {$canal['nome_exibicao']}\n";
            }
        } else {
            echo "❌ Erro ao atualizar: " . $mysqli->error . "\n";
        }
    } else {
        echo "✅ Nome já está correto!\n";
    }
} else {
    echo "❌ Canal 37 não encontrado\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. Recarregue o chat no painel\n";
echo "2. As mensagens do canal 37 agora devem aparecer como 'COMERCIAL'\n";
echo "3. Teste enviando uma mensagem real para 4797309525\n";
?> 