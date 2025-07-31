<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CONFIGURANDO CANAL 3001 COM NÚMERO DO FINANCEIRO\n";
echo "==================================================\n\n";

// Número do Financeiro (que já sabemos que funciona)
$numero_financeiro = '554797146908';
$identificador = $numero_financeiro . '@c.us';

echo "🔧 CONFIGURANDO CANAL 3001:\n";
echo "   Usando número do Financeiro: $numero_financeiro\n";
echo "   Identificador: $identificador\n\n";

// Atualizar canal no banco
$update = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$identificador', status = 'conectado', data_conexao = NOW() WHERE porta = 3001");

if ($update) {
    echo "✅ Canal 3001 configurado com sucesso!\n";
    echo "✅ Identificador: $identificador\n";
    echo "✅ Status: conectado\n\n";
    
    // Verificar configuração
    $canal_3001 = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001")->fetch_assoc();
    if ($canal_3001) {
        echo "📊 CONFIGURAÇÃO ATUAL:\n";
        echo "   Canal: {$canal_3001['nome_exibicao']} (ID: {$canal_3001['id']})\n";
        echo "   Porta: {$canal_3001['porta']}\n";
        echo "   Status: {$canal_3001['status']}\n";
        echo "   Identificador: {$canal_3001['identificador']}\n";
    }
    
    echo "\n🎯 PRÓXIMOS PASSOS:\n";
    echo "1. Teste enviar uma mensagem para o número $numero_financeiro\n";
    echo "2. Verifique se a mensagem aparece no chat do sistema\n";
    echo "3. Confirme que está associada ao canal Comercial (ID 37)\n";
    echo "4. Se funcionar, o problema era apenas a falta do identificador\n";
    echo "5. Se não funcionar, precisamos verificar o endpoint /send\n";
} else {
    echo "❌ Erro ao configurar canal: " . $mysqli->error . "\n";
}

echo "\n🎯 CONFIGURAÇÃO CONCLUÍDA!\n";
?> 