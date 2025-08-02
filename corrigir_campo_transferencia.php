<?php
require_once 'painel/db.php';

echo "🔧 CORRIGINDO CAMPO TRANSFERENCIA_EXECUTADA\n";
echo "============================================\n\n";

// Verificar se o campo existe
$check = $mysqli->query("SHOW COLUMNS FROM logs_integracao_ana LIKE 'transferencia_executada'");

if ($check && $check->num_rows > 0) {
    echo "✅ Campo transferencia_executada já existe\n";
} else {
    echo "❌ Campo transferencia_executada não existe - criando...\n";
    
    // Adicionar o campo
    $sql = "ALTER TABLE logs_integracao_ana ADD COLUMN transferencia_executada TINYINT(1) DEFAULT 0 AFTER status_api";
    
    if ($mysqli->query($sql)) {
        echo "✅ Campo transferencia_executada criado com sucesso!\n";
    } else {
        echo "❌ Erro ao criar campo: " . $mysqli->error . "\n";
    }
}

// Verificar se o campo tempo_resposta_ms existe também
echo "\n🔍 Verificando campo tempo_resposta_ms...\n";
$check2 = $mysqli->query("SHOW COLUMNS FROM logs_integracao_ana LIKE 'tempo_resposta_ms'");

if ($check2 && $check2->num_rows > 0) {
    echo "✅ Campo tempo_resposta_ms já existe\n";
} else {
    echo "❌ Campo tempo_resposta_ms não existe - criando...\n";
    
    $sql2 = "ALTER TABLE logs_integracao_ana ADD COLUMN tempo_resposta_ms INT(11) NULL AFTER status_api";
    
    if ($mysqli->query($sql2)) {
        echo "✅ Campo tempo_resposta_ms criado com sucesso!\n";
    } else {
        echo "❌ Erro ao criar campo: " . $mysqli->error . "\n";
    }
}

echo "\n🎉 CORREÇÃO CONCLUÍDA!\n";
echo "Agora teste novamente o webhook:\n";
echo "https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php?from=5547999999999&body=Preciso%20de%20um%20site\n";

$mysqli->close();
?> 