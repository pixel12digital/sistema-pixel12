<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔧 CONFIGURANDO CANAL COMERCIAL SEPARADO\n";
echo "========================================\n\n";

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

// 2. Configurar canal comercial para porta 3001
echo "🔧 CONFIGURANDO CANAL COMERCIAL PARA PORTA 3001:\n";

// Atualizar canal comercial existente
$update = $mysqli->query("UPDATE canais_comunicacao SET porta = 3001, status = 'pendente', data_conexao = NULL WHERE nome_exibicao LIKE '%Comercial%'");
if ($update) {
    echo "✅ Canal comercial configurado para porta 3001\n";
} else {
    echo "❌ Erro ao configurar canal: " . $mysqli->error . "\n";
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
echo "   🔴 Comercial: Porta 3001 - (aguardando número)\n\n";

echo "🚨 PRÓXIMO PASSO NECESSÁRIO:\n";
echo "   Você precisa configurar o servidor WhatsApp na porta 3001 da VPS.\n\n";

echo "🔧 COMANDOS PARA EXECUTAR NA VPS:\n";
echo "   ssh root@212.85.11.238\n";
echo "   netstat -tulpn | grep :3001\n";
echo "   # Se porta 3001 estiver livre, configurar servidor WhatsApp\n\n";

echo "✅ CONFIGURAÇÃO APLICADA!\n";
echo "Agora configure o servidor na porta 3001 e depois configure o número comercial.\n";
?> 