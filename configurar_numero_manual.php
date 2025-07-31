<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CONFIGURANDO NÚMERO MANUALMENTE\n";
echo "=================================\n\n";

echo "📋 INSTRUÇÕES:\n";
echo "1. Acesse http://212.85.11.238:3001/qr no navegador\n";
echo "2. Verifique qual número do WhatsApp está conectado\n";
echo "3. Digite o número abaixo (apenas números, sem código do país)\n\n";

echo "🔢 Digite o número do WhatsApp conectado na porta 3001: ";
$handle = fopen("php://stdin", "r");
$numero = trim(fgets($handle));
fclose($handle);

if (empty($numero)) {
    echo "❌ Número não informado!\n";
    exit(1);
}

// Limpar número (apenas dígitos)
$numero_limpo = preg_replace('/\D/', '', $numero);

// Adicionar código do país se não tiver
if (strlen($numero_limpo) === 11 && substr($numero_limpo, 0, 2) === '55') {
    $numero_completo = $numero_limpo;
} elseif (strlen($numero_limpo) === 9) {
    $numero_completo = '55' . $numero_limpo;
} else {
    $numero_completo = $numero_limpo;
}

$identificador = $numero_completo . '@c.us';

echo "\n🔧 CONFIGURANDO CANAL 3001:\n";
echo "   Número informado: $numero\n";
echo "   Número limpo: $numero_limpo\n";
echo "   Número completo: $numero_completo\n";
echo "   Identificador: $identificador\n\n";

// Atualizar canal no banco
$update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$identificador', status = 'conectado', data_conexao = NOW() WHERE porta = 3001");

if ($update) {
    echo "✅ Canal 3001 configurado com sucesso!\n";
    echo "✅ Identificador: $identificador\n";
    echo "✅ Status: conectado\n\n";
    
    echo "🎯 PRÓXIMOS PASSOS:\n";
    echo "1. Teste enviar uma mensagem para o número $numero_completo\n";
    echo "2. Verifique se a mensagem aparece no chat do sistema\n";
    echo "3. Confirme que está associada ao canal Comercial (ID 37)\n";
} else {
    echo "❌ Erro ao configurar canal: " . $mysqli->error . "\n";
}

echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n";
?> 