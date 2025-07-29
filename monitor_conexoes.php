<?php
/**
 * MONITOR DE CONEXÕES
 * 
 * Monitora e controla o número de conexões com o banco
 */

require_once "config.php";

// Contador de conexões
$contador_file = "cache/conexoes_contador.txt";
$limite_conexoes = 400; // Limite seguro

function incrementar_conexao() {
    global $contador_file, $limite_conexoes;
    
    $contador = 0;
    if (file_exists($contador_file)) {
        $contador = (int)file_get_contents($contador_file);
    }
    
    // Reset diário
    $hoje = date("Y-m-d");
    $ultimo_reset = file_exists("cache/ultimo_reset.txt") ? file_get_contents("cache/ultimo_reset.txt") : "";
    
    if ($ultimo_reset !== $hoje) {
        $contador = 0;
        file_put_contents("cache/ultimo_reset.txt", $hoje);
    }
    
    $contador++;
    file_put_contents($contador_file, $contador);
    
    if ($contador > $limite_conexoes) {
        error_log("ALERTA: Limite de conexões excedido: $contador");
        return false;
    }
    
    return true;
}

function get_conexoes_count() {
    global $contador_file;
    return file_exists($contador_file) ? (int)file_get_contents($contador_file) : 0;
}

// Verificar se pode conectar
if (!incrementar_conexao()) {
    http_response_code(503);
    echo json_encode(["error" => "Limite de conexões excedido"]);
    exit;
}
?>