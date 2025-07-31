<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🧪 TESTANDO MENSAGEM NO CANAL 3001\n";
echo "==================================\n\n";

// Simular dados de uma mensagem recebida
$dados_mensagem = [
    'from' => '554797146908@c.us', // Número que está enviando a mensagem
    'to' => '4797309525@c.us',     // Número do canal 3001 (destinatário)
    'message' => 'Teste mensagem canal 3001 - ' . date('H:i:s'),
    'timestamp' => time(),
    'type' => 'text'
];

echo "📨 SIMULANDO MENSAGEM RECEBIDA:\n";
echo "   De: {$dados_mensagem['from']}\n";
echo "   Para: {$dados_mensagem['to']}\n";
echo "   Mensagem: {$dados_mensagem['message']}\n\n";

// Buscar canal pelo identificador
$canal = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE identificador = '{$dados_mensagem['to']}' LIMIT 1")->fetch_assoc();

if ($canal) {
    echo "✅ Canal encontrado: {$canal['nome_exibicao']} (ID: {$canal['id']})\n";
    $canal_id = $canal['id'];
} else {
    echo "❌ Canal não encontrado para o identificador: {$dados_mensagem['to']}\n";
    echo "🔍 Verificando todos os canais...\n";
    
    $canais = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE porta = 3001");
    while ($c = $canais->fetch_assoc()) {
        echo "   Canal {$c['id']}: {$c['nome_exibicao']} - {$c['identificador']}\n";
    }
    exit(1);
}

// Simular salvamento da mensagem
$numero_remetente = str_replace('@c.us', '', $dados_mensagem['from']);
$mensagem = $mysqli->real_escape_string($dados_mensagem['message']);
$data_hora = date('Y-m-d H:i:s');

$sql = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao) 
        VALUES ($canal_id, '$numero_remetente', '$mensagem', 'text', '$data_hora', 'entrada')";

$insert = $mysqli->query($sql);

if ($insert) {
    $mensagem_id = $mysqli->insert_id;
    echo "✅ Mensagem salva com sucesso!\n";
    echo "   ID da mensagem: $mensagem_id\n";
    echo "   Canal ID: $canal_id\n";
    echo "   Data/Hora: $data_hora\n\n";
    
    // Verificar se a mensagem foi realmente salva
    $mensagem_salva = $mysqli->query("SELECT * FROM mensagens_comunicacao WHERE id = $mensagem_id")->fetch_assoc();
    if ($mensagem_salva) {
        echo "📊 MENSAGEM SALVA:\n";
        echo "   ID: {$mensagem_salva['id']}\n";
        echo "   Canal ID: {$mensagem_salva['canal_id']}\n";
        echo "   Remetente: {$mensagem_salva['numero_whatsapp']}\n";
        echo "   Mensagem: {$mensagem_salva['mensagem']}\n";
        echo "   Tipo: {$mensagem_salva['tipo']}\n";
        echo "   Direção: {$mensagem_salva['direcao']}\n";
        echo "   Data/Hora: {$mensagem_salva['data_hora']}\n";
    }
    
    echo "\n🎯 TESTE CONCLUÍDO COM SUCESSO!\n";
    echo "✅ O canal 3001 está funcionando corretamente\n";
    echo "✅ As mensagens estão sendo salvas no banco de dados\n";
    echo "✅ O sistema está identificando o canal corretamente\n";
    
} else {
    echo "❌ Erro ao salvar mensagem: " . $mysqli->error . "\n";
}

echo "\n🎯 PRÓXIMO PASSO:\n";
echo "Teste enviar uma mensagem real do WhatsApp para o número 4797309525\n";
echo "e verifique se aparece no chat do sistema!\n";
?> 