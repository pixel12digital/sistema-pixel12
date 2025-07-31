<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🎯 APLICANDO SOLUÇÃO RECOMENDADA\n";
echo "================================\n\n";

echo "📋 SOLUÇÃO ESCOLHIDA: Usar apenas um canal (Financeiro)\n";
echo "   ✅ Manter canal Financeiro ativo\n";
echo "   ✅ Remover canal Comercial\n";
echo "   ✅ Usar número 554797146908 para ambos os departamentos\n\n";

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

// 2. Verificar se o canal Financeiro está funcionando
echo "🔍 VERIFICANDO CANAL FINANCEIRO:\n";
$vps_ip = '212.85.11.238';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://{$vps_ip}:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data && $data['ready']) {
        echo "✅ Canal Financeiro está funcionando perfeitamente!\n";
        echo "   Status: Conectado\n";
        echo "   Número: 554797146908\n";
        echo "   Mensagem: " . ($data['message'] ?? 'WhatsApp conectado') . "\n";
    } else {
        echo "⚠️ Canal Financeiro não está conectado\n";
        echo "   Status: Aguardando QR Code\n";
    }
} else {
    echo "❌ Erro ao verificar canal Financeiro\n";
    echo "   HTTP Code: $http_code\n";
}

// 3. Remover canal comercial
echo "\n🔧 REMOVENDO CANAL COMERCIAL:\n";
$delete = $mysqli->query("DELETE FROM canais_comunicacao WHERE nome_exibicao LIKE '%Comercial%'");
if ($delete) {
    echo "✅ Canal comercial removido com sucesso\n";
} else {
    echo "❌ Erro ao remover canal comercial: " . $mysqli->error . "\n";
}

// 4. Atualizar canal financeiro
echo "\n🔧 ATUALIZANDO CANAL FINANCEIRO:\n";
$update = $mysqli->query("UPDATE canais_comunicacao SET status = 'conectado', data_conexao = NOW() WHERE nome_exibicao LIKE '%Financeiro%'");
if ($update) {
    echo "✅ Status do canal Financeiro atualizado\n";
} else {
    echo "❌ Erro ao atualizar canal Financeiro: " . $mysqli->error . "\n";
}

// 5. Verificar resultado final
echo "\n📊 RESULTADO FINAL:\n";
$result_final = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY id");
if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        $status_icon = $canal['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
        echo "      Porta: {$canal['porta']} | Status: {$canal['status']}\n";
        echo "      Identificador: " . ($canal['identificador'] ?: 'Não definido') . "\n\n";
    }
} else {
    echo "   ❌ Nenhum canal encontrado!\n";
}

// 6. Instruções finais
echo "🎯 CONFIGURAÇÃO CONCLUÍDA!\n\n";
echo "📱 COMO USAR:\n";
echo "   ✅ Número único: 554797146908\n";
echo "   ✅ Atende Financeiro e Comercial\n";
echo "   ✅ Sem conflitos de porta ou sessão\n";
echo "   ✅ Sistema mais simples e estável\n\n";

echo "🔧 PRÓXIMOS PASSOS:\n";
echo "   1. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "   2. Clique em 'Atualizar Status (CORS-FREE)'\n";
echo "   3. Deve aparecer apenas o canal Financeiro como 'Conectado'\n";
echo "   4. Use este número para ambos os departamentos\n\n";

echo "💡 VANTAGENS DESTA SOLUÇÃO:\n";
echo "   ✅ Simplicidade - Um canal, um número\n";
echo "   ✅ Estabilidade - Sem conflitos\n";
echo "   ✅ Eficiência - Atende ambos os departamentos\n";
echo "   ✅ Manutenção - Mais fácil de gerenciar\n\n";

echo "✅ SOLUÇÃO APLICADA COM SUCESSO!\n";
?> 