<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🔍 Verificando status do canal comercial...\n\n";

// Buscar canal na porta 3001
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = 3001");

if ($result && $result->num_rows > 0) {
    $canal = $result->fetch_assoc();
    echo "📱 Canal Comercial (Porta 3001):\n";
    echo "   ID: " . $canal['id'] . "\n";
    echo "   Nome: " . $canal['nome_exibicao'] . "\n";
    echo "   Status: " . $canal['status'] . "\n";
    echo "   Identificador: " . $canal['identificador'] . "\n";
    echo "   Data Conexão: " . $canal['data_conexao'] . "\n";
    
    // Verificar se o status está incorreto
    if ($canal['status'] === 'conectado') {
        echo "\n⚠️ PROBLEMA DETECTADO:\n";
        echo "   O canal aparece como 'conectado' mas a porta 3001 não está respondendo!\n";
        echo "   Isso indica que o status no banco está incorreto.\n";
        
        echo "\n🔧 SOLUÇÃO:\n";
        echo "   1. Atualizar status para 'pendente'\n";
        echo "   2. Configurar servidor WhatsApp na porta 3001\n";
        echo "   3. Gerar QR code para conectar\n";
        
        // Perguntar se quer corrigir
        echo "\n❓ Deseja corrigir o status do canal? (s/n): ";
        $handle = fopen("php://stdin", "r");
        $resposta = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($resposta) === 's') {
            $update = $mysqli->query("UPDATE canais_comunicacao SET status = 'pendente', data_conexao = NULL WHERE porta = 3001");
            if ($update) {
                echo "✅ Status corrigido para 'pendente'\n";
            } else {
                echo "❌ Erro ao corrigir status: " . $mysqli->error . "\n";
            }
        }
    }
} else {
    echo "❌ Canal comercial não encontrado na porta 3001\n";
}

echo "\n📊 Status atual dos canais:\n";
$todos = $mysqli->query("SELECT id, nome_exibicao, porta, status, identificador FROM canais_comunicacao ORDER BY porta");
if ($todos && $todos->num_rows > 0) {
    while ($c = $todos->fetch_assoc()) {
        $status_icon = $c['status'] === 'conectado' ? '🟢' : '🔴';
        echo "   {$status_icon} {$c['nome_exibicao']} (Porta {$c['porta']}) - {$c['status']}\n";
    }
}

echo "\n✅ Verificação concluída!\n";
?> 