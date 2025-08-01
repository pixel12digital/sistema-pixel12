<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "📱 ATUALIZANDO STATUS FINANCEIRO\n";
echo "================================\n\n";

// Atualizar status para qr_ready
$sql = "UPDATE canais_comunicacao SET status = 'qr_ready' WHERE id = 36";
if ($mysqli->query($sql)) {
    echo "✅ Status atualizado para 'qr_ready'!\n";
} else {
    echo "❌ Erro ao atualizar: " . $mysqli->error . "\n";
}

// Verificar configuração final
echo "\n📋 CONFIGURAÇÃO FINAL:\n";
echo "======================\n";
$sql_final = "SELECT id, nome_exibicao, porta, sessao, status FROM canais_comunicacao WHERE tipo = 'whatsapp' ORDER BY id";
$result_final = $mysqli->query($sql_final);

if ($result_final && $result_final->num_rows > 0) {
    while ($canal = $result_final->fetch_assoc()) {
        echo "ID: {$canal['id']} | Nome: {$canal['nome_exibicao']} | Porta: {$canal['porta']} | Sessão: " . ($canal['sessao'] ?: 'NULL') . " | Status: {$canal['status']}\n";
    }
}

echo "\n🎯 QR CODE DISPONÍVEL!\n";
echo "=====================\n";
echo "✅ Canal Financeiro (Porta 3000): QR Code pronto!\n";
echo "📱 Escaneie o QR Code com WhatsApp 554797146908\n";
echo "🔗 URL do QR: http://212.85.11.238:3000/qr?session=default\n\n";

echo "💡 PRÓXIMOS PASSOS:\n";
echo "==================\n";
echo "1. Acesse: http://localhost:8080/loja-virtual-revenda/painel/comunicacao.php\n";
echo "2. Clique em 'Conectar' no canal Financeiro\n";
echo "3. Escaneie o QR Code com WhatsApp 554797146908\n";
echo "4. Aguarde a conexão ser estabelecida\n";
echo "5. Ambos os canais ficarão independentes!\n";
?> 