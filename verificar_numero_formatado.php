<?php
require_once 'painel/config.php';
require_once 'painel/db.php';

echo "=== VERIFICAÇÃO DO NÚMERO DO CHARLES ===\n\n";

$cliente = $mysqli->query("SELECT id, nome, celular FROM clientes WHERE id = 156")->fetch_assoc();

if ($cliente) {
    echo "Cliente encontrado:\n";
    echo "ID: " . $cliente['id'] . "\n";
    echo "Nome: " . $cliente['nome'] . "\n";
    echo "Celular original: " . $cliente['celular'] . "\n";
    
    // Verificar formatação para WhatsApp
    $celular_limpo = preg_replace('/\D/', '', $cliente['celular']);
    echo "Celular limpo: " . $celular_limpo . "\n";
    
    // Verificar se precisa adicionar 55
    if (strlen($celular_limpo) === 11 && strpos($celular_limpo, '55') !== 0) {
        $celular_whatsapp = '55' . $celular_limpo;
        echo "Celular para WhatsApp: " . $celular_whatsapp . "\n";
    } else {
        $celular_whatsapp = $celular_limpo;
        echo "Celular para WhatsApp: " . $celular_whatsapp . "\n";
    }
    
    // Verificar se é válido
    if (preg_match('/^55\d{11}$/', $celular_whatsapp)) {
        echo "✅ Número válido para WhatsApp\n";
    } else {
        echo "❌ Número inválido para WhatsApp\n";
    }
    
    echo "\n=== TESTE DE FORMATAÇÃO ===\n";
    echo "Link WhatsApp: https://wa.me/" . $celular_whatsapp . "\n";
    
} else {
    echo "Cliente não encontrado!\n";
}

echo "\n=== MENSAGENS DO CHARLES ===\n";

$mensagens = $mysqli->query("SELECT id, mensagem, direcao, data_hora FROM mensagens_comunicacao WHERE cliente_id = 156 ORDER BY data_hora DESC");

if ($mensagens->num_rows > 0) {
    while ($msg = $mensagens->fetch_assoc()) {
        echo "ID: " . $msg['id'] . " | " . $msg['direcao'] . " | " . $msg['data_hora'] . "\n";
        echo "Mensagem: " . $msg['mensagem'] . "\n\n";
    }
} else {
    echo "Nenhuma mensagem encontrada!\n";
}
?> 