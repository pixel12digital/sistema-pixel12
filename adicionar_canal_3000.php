<?php
/**
 * 🚀 SCRIPT PARA ADICIONAR CANAL 3000 AUTOMATICAMENTE
 * 
 * Este script adiciona o canal 3000 diretamente no banco de dados
 */

require_once 'painel/db.php';

echo "🚀 ADICIONANDO CANAL 3000 AO SISTEMA\n";
echo "===================================\n\n";

// Dados do canal 3000
$identificador = '';
$nome_exibicao = 'Financeiro - Canal 3000';
$porta = 3000;
$tipo = 'whatsapp';
$status = 'pendente';

// Verificar se já existe um canal com esta porta
$canal_existente = $mysqli->query("SELECT id FROM canais_comunicacao WHERE porta = $porta")->fetch_assoc();

if ($canal_existente) {
    echo "❌ ERRO: Já existe um canal na porta $porta!\n";
    echo "Canal ID: " . $canal_existente['id'] . "\n\n";
    
    // Mostrar detalhes do canal existente
    $detalhes = $mysqli->query("SELECT * FROM canais_comunicacao WHERE porta = $porta")->fetch_assoc();
    echo "Detalhes do canal existente:\n";
    echo "- Nome: " . $detalhes['nome_exibicao'] . "\n";
    echo "- Status: " . $detalhes['status'] . "\n";
    echo "- Data: " . $detalhes['data_conexao'] . "\n";
    
} else {
    // Canal não existe, inserir novo
    $sql = "INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao, porta) 
            VALUES ('$tipo', '$identificador', '" . $mysqli->real_escape_string($nome_exibicao) . "', '$status', NULL, $porta)";
    
    if ($mysqli->query($sql)) {
        $canal_id = $mysqli->insert_id;
        echo "✅ SUCESSO: Canal 3000 adicionado com sucesso!\n";
        echo "Canal ID: $canal_id\n";
        echo "Nome: $nome_exibicao\n";
        echo "Porta: $porta\n";
        echo "Status: $status\n\n";
        
        echo "🎯 PRÓXIMOS PASSOS:\n";
        echo "1. Acesse o painel de comunicação\n";
        echo "2. O canal 3000 aparecerá na lista\n";
        echo "3. Clique em 'Conectar' para escanear o QR Code\n";
        echo "4. Use o primeiro número de WhatsApp para escanear\n\n";
        
    } else {
        echo "❌ ERRO ao inserir canal: " . $mysqli->error . "\n";
    }
}

// Mostrar todos os canais cadastrados
echo "📋 CANAIS CADASTRADOS ATUALMENTE:\n";
echo "================================\n";
$result = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY porta");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- Porta: " . $row['porta'] . " | Nome: " . $row['nome_exibicao'] . " | Status: " . $row['status'] . "\n";
    }
} else {
    echo "Nenhum canal cadastrado.\n";
}

echo "\n🔗 TESTE OS CANAIS:\n";
echo "- Canal 3000: http://212.85.11.238:3000/status\n";
echo "- Canal 3001: http://212.85.11.238:3001/status\n";
?> 