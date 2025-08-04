<?php
/**
 * Função robusta para enviar mensagens via WhatsApp Web.js
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
    
    // Log do que está sendo enviado
    error_log("[ENVIO_WHATSAPP] Tentando enviar para: $numero");
    error_log("[ENVIO_WHATSAPP] Mensagem: " . substr($mensagem, 0, 100) . "...");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $vps_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8); // Timeout menor
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout de conexão
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $resultado = [
        "success" => $http_code == 200,
        "http_code" => $http_code,
        "response" => $response,
        "error" => $error
    ];
    
    // Log do resultado
    if ($resultado["success"]) {
        error_log("[ENVIO_WHATSAPP] ✅ Sucesso: HTTP $http_code");
    } else {
        error_log("[ENVIO_WHATSAPP] ❌ Erro: HTTP $http_code - $error");
    }
    
    return $resultado;
}

/**
 * Função alternativa usando outro método (caso VPS falhe)
 */
function enviarMensagemAlternativa($numero, $mensagem) {
    // Log da tentativa alternativa
    error_log("[ENVIO_ALT] Método alternativo para: $numero");
    
    // Aqui pode implementar outro método se necessário
    // Por enquanto, retorna falha
    return [
        "success" => false,
        "http_code" => 0,
        "response" => "Método alternativo não implementado",
        "error" => "VPS indisponível"
    ];
}

/**
 * Função principal que tenta múltiplos métodos
 */
function enviarMensagemRobusta($numero, $mensagem) {
    // Primeira tentativa: VPS normal
    $resultado = enviarMensagemWhatsApp($numero, $mensagem);
    
    if ($resultado["success"]) {
        return $resultado;
    }
    
    // Se VPS falhou, log detalhado
    error_log("[ENVIO_ROBUSTA] VPS falhou, tentando alternativa...");
    
    // Segunda tentativa: método alternativo
    $resultado_alt = enviarMensagemAlternativa($numero, $mensagem);
    
    return $resultado_alt;
}

// Teste da função (só se executado diretamente)
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    echo "🧪 Testando função robusta:\n";
    $resultado = enviarMensagemRobusta("554796164699", "🎉 Teste função robusta - " . date("Y-m-d H:i:s"));
    echo json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
}
?>