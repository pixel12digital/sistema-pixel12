<?php
header("Content-Type: application/json");

try {
    // Log de entrada
    error_log("[WEBHOOK_TEST] Iniciando...");
    
    $input = file_get_contents("php://input");
    error_log("[WEBHOOK_TEST] Input: " . $input);
    
    // Dados mínimos
    $data = json_decode($input, true);
    if (!$data && !empty($_GET)) {
        $data = $_GET;
    }
    
    $from = $data["from"] ?? $data["number"] ?? "teste";
    $body = $data["body"] ?? $data["message"] ?? "teste";
    
    error_log("[WEBHOOK_TEST] From: $from, Body: $body");
    
    // Resposta básica
    $response = [
        "success" => true,
        "message_id" => 999,
        "response_id" => 999,
        "ana_response" => "Olá! Webhook funcionando em modo teste. Mensagem: $body",
        "action_taken" => "teste",
        "debug_mode" => true,
        "timestamp" => date("Y-m-d H:i:s")
    ];
    
    error_log("[WEBHOOK_TEST] Resposta: " . json_encode($response));
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[WEBHOOK_TEST] ERRO: " . $e->getMessage());
    
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "debug_mode" => true
    ]);
}
?>