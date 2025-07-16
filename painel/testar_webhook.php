<?php
// Script para testar o endpoint do webhook WhatsApp localmente
$url = 'http://localhost:8080/loja-virtual-revenda/painel/api/whatsapp_webhook.php';
$data = [
    'canal_id' => 36,
    'numero' => '5599999999999',
    'mensagem' => 'Mensagem de teste recebida via webhook (PHP)'
];
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'timeout' => 5
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Resposta do webhook:\n";
echo $result; 