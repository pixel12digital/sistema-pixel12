<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "📱 CONFIGURANDO NÚMERO DO CANAL COMERCIAL\n";
echo "=========================================\n\n";

// 1. Verificar status atual
echo "📊 STATUS ATUAL:\n";
$result = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result && $result->num_rows > 0) {
    while ($canal = $result->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

// 2. Perguntar qual número usar para comercial
echo "❓ QUAL NÚMERO USAR PARA O CANAL COMERCIAL?\n";
echo "   Digite o número (apenas números, sem formatação): ";
$handle = fopen("php://stdin", "r");
$numero_comercial = trim(fgets($handle));
fclose($handle);

if (!empty($numero_comercial)) {
    // Validar formato do número
    $numero_limpo = preg_replace('/[^0-9]/', '', $numero_comercial);
    
    if (strlen($numero_limpo) >= 10) {
        $update_numero = $mysqli->query("UPDATE canais_comunicacao SET identificador = '$numero_limpo' WHERE nome_exibicao LIKE '%Comercial%'");
        if ($update_numero) {
            echo "✅ Número comercial configurado: $numero_limpo\n";
        } else {
            echo "❌ Erro ao configurar número: " . $mysqli->error . "\n";
        }
    } else {
        echo "❌ Número inválido. Deve ter pelo menos 10 dígitos.\n";
        exit(1);
    }
} else {
    echo "❌ Nenhum número foi fornecido.\n";
    exit(1);
}

// 3. Verificar configuração final
echo "\n📊 CONFIGURAÇÃO FINAL:\n";
$result_final = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta, id");
if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
}

echo "🎯 CONFIGURAÇÃO CONCLUÍDA!\n\n";
echo "📱 CANAIS CONFIGURADOS:\n";
echo "   🟢 Financeiro: Porta 3000 - 554797146908\n";
echo "   🔴 Comercial: Porta 3001 - $numero_limpo\n\n";

echo "🚨 PRÓXIMOS PASSOS:\n";
echo "   1. Configure o servidor WhatsApp na porta 3001 da VPS\n";
echo "   2. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   3. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   4. Canal comercial deve aparecer como 'Desconectado'\n";
echo "   5. Clique em 'Conectar' para gerar QR code\n";
echo "   6. Escaneie com o WhatsApp do número $numero_limpo\n\n";

echo "✅ NÚMERO CONFIGURADO COM SUCESSO!\n";
?> 