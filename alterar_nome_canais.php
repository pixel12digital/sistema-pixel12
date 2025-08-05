<?php
require_once 'painel/db.php';

// Canal comercial
$novo_nome_comercial = 'Pixel - Comercial';
$identificador_comercial = '554797309525@c.us';
$sql1 = "UPDATE canais_comunicacao SET nome_exibicao = '$novo_nome_comercial' WHERE identificador = '$identificador_comercial'";
$res1 = $mysqli->query($sql1);

// Canal financeiro (porta 3000)
$novo_nome_financeiro = 'Pixel12Digital';
$sql2 = "UPDATE canais_comunicacao SET nome_exibicao = '$novo_nome_financeiro' WHERE porta = 3000";
$res2 = $mysqli->query($sql2);

if ($res1) {
    echo "✅ Nome do canal comercial alterado para '$novo_nome_comercial'\n";
} else {
    echo "❌ Erro ao atualizar canal comercial: " . $mysqli->error . "\n";
}
if ($res2) {
    echo "✅ Nome do canal financeiro (porta 3000) alterado para '$novo_nome_financeiro'\n";
} else {
    echo "❌ Erro ao atualizar canal financeiro: " . $mysqli->error . "\n";
}

// Exibir canais para conferência
$result = $mysqli->query("SELECT id, nome_exibicao, identificador, porta FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");
echo "\nCANAIS ATUAIS:\n";
while ($row = $result->fetch_assoc()) {
    echo "- Porta: {$row['porta']} | Nome: {$row['nome_exibicao']} | Identificador: {$row['identificador']}\n";
} 