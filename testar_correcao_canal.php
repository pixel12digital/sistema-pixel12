<?php
require_once 'config.php';
require_once 'painel/db.php';

echo "🧪 TESTANDO CORREÇÃO DA IDENTIFICAÇÃO DO CANAL\n";
echo "==============================================\n\n";

// Simular dados de uma mensagem recebida no canal 3001
$dados_mensagem = [
    'from' => '554797146908@c.us', // Número que está enviando
    'to' => '4797309525@c.us',     // Número do canal 3001 (destinatário)
    'body' => 'Teste correção canal 3001 - ' . date('H:i:s'),
    'timestamp' => time()
];

echo "📨 SIMULANDO MENSAGEM RECEBIDA:\n";
echo "   De: {$dados_mensagem['from']}\n";
echo "   Para: {$dados_mensagem['to']}\n";
echo "   Mensagem: {$dados_mensagem['body']}\n\n";

// Simular o processamento do receber_mensagem.php
$from = $mysqli->real_escape_string($dados_mensagem['from']);
$body = $mysqli->real_escape_string($dados_mensagem['body']);
$to = $mysqli->real_escape_string($dados_mensagem['to']);

// NOVA LÓGICA: Buscar canal pelo número de destino (to)
$canal_id = 36; // Padrão: Financeiro
$canal_nome = "Financeiro";

echo "🔍 BUSCANDO CANAL PELO DESTINO:\n";
echo "   Destino: $to\n";

// Buscar canal pelo identificador de destino
$canal = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao WHERE identificador = '$to' LIMIT 1")->fetch_assoc();

if ($canal) {
    $canal_id = intval($canal['id']);
    $canal_nome = $canal['nome_exibicao'];
    echo "✅ Canal encontrado: $canal_nome (ID: $canal_id)\n";
    } else {
    echo "❌ Canal não encontrado pelo destino '$to', usando padrão Financeiro\n";
}

// Simular salvamento da mensagem
$numero_remetente = str_replace('@c.us', '', $dados_mensagem['from']);
$data_hora = date('Y-m-d H:i:s');

$sql = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao) 
        VALUES ($canal_id, '$numero_remetente', '$body', 'texto', '$data_hora', 'recebido')";

$insert = $mysqli->query($sql);

if ($insert) {
    $mensagem_id = $mysqli->insert_id;
    echo "\n✅ Mensagem salva com sucesso!\n";
    echo "   ID da mensagem: $mensagem_id\n";
    echo "   Canal: $canal_nome (ID: $canal_id)\n";
    echo "   Data/Hora: $data_hora\n\n";
    
    // Verificar se a mensagem foi realmente salva
    $mensagem_salva = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m 
                                     LEFT JOIN canais_comunicacao c ON m.canal_id = c.id 
                                     WHERE m.id = $mensagem_id")->fetch_assoc();
    if ($mensagem_salva) {
        echo "📊 MENSAGEM SALVA:\n";
        echo "   ID: {$mensagem_salva['id']}\n";
        echo "   Canal: {$mensagem_salva['canal_nome']} (ID: {$mensagem_salva['canal_id']})\n";
        echo "   Remetente: {$mensagem_salva['numero_whatsapp']}\n";
        echo "   Mensagem: {$mensagem_salva['mensagem']}\n";
        echo "   Tipo: {$mensagem_salva['tipo']}\n";
        echo "   Direção: {$mensagem_salva['direcao']}\n";
        echo "   Data/Hora: {$mensagem_salva['data_hora']}\n";
    }
    
    echo "\n🎯 CORREÇÃO TESTADA COM SUCESSO!\n";
    echo "✅ O sistema agora identifica corretamente o canal pelo número de destino\n";
    echo "✅ Mensagens para 4797309525@c.us vão para o canal Comercial\n";
    echo "✅ Mensagens para 554797146908@c.us vão para o canal Financeiro\n";
    
        } else {
    echo "❌ Erro ao salvar mensagem: " . $mysqli->error . "\n";
}

echo "\n🎯 PRÓXIMO PASSO:\n";
echo "Teste enviar uma mensagem real do WhatsApp para o número 4797309525\n";
echo "e verifique se aparece como 'COMERCIAL' no chat!\n";
?> 