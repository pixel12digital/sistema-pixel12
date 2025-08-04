<?php
/**
 * Função para enviar mensagens via WhatsApp Web.js
 */
function enviarMensagemWhatsApp($numero, $mensagem) {
    $vps_url = "http://212.85.11.238:3000/send/text";
    
    // Garantir formato correto do número
    if (strpos($numero, "@c.us") === false) {
        $numero = $numero . "@c.us";
    }
    
    $data = [
        "number" => $numero,
        "message" => $mensagem
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        "success" => $http_code == 200,
        "http_code" => $http_code,
        "response" => $response,
        "error" => $error
    ];
}

// Teste da função (só se executado diretamente)
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    echo "🧪 Testando função de envio:\n";
    $resultado = enviarMensagemWhatsApp("554796164699", "🎉 Função de envio funcionando! - " . date("Y-m-d H:i:s"));
    echo json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
}
?>