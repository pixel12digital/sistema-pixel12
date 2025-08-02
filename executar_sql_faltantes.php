<?php
require_once 'painel/db.php';

echo "🚀 Executando tabelas que faltaram...\n\n";

$sql = file_get_contents('criar_tabelas_faltantes.sql');
$sqls = explode(';', $sql);
$executados = 0;
$erros = 0;

foreach ($sqls as $s) {
    $s = trim($s);
    if (!empty($s) && strpos($s, '--') !== 0 && strpos($s, 'USE') !== 0) {
        echo "Executando: " . substr($s, 0, 80) . "...\n";
        
        if ($mysqli->query($s)) {
            echo "✅ OK\n\n";
            $executados++;
        } else {
            echo "❌ Erro: " . $mysqli->error . "\n\n";
            $erros++;
        }
    }
}

echo "📊 RESULTADO:\n";
echo "✅ Executados com sucesso: $executados\n";
echo "❌ Erros: $erros\n\n";

if ($erros == 0) {
    echo "🎉 Todas as tabelas foram criadas com sucesso!\n";
} else {
    echo "⚠️ Houve alguns erros, mas o sistema pode funcionar.\n";
}
?> 