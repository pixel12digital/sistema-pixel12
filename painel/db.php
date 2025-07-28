<?php
// Incluir configurações globais
require_once __DIR__ . '/../config.php';

// Conexão MySQL com pooling para evitar limite de conexões
static $mysqli = null;

if ($mysqli === null) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    
    // Configurar timeout para evitar conexões órfãs
    $mysqli->query("SET SESSION wait_timeout=300");
    $mysqli->query("SET SESSION interactive_timeout=300");
}

// Verificar se a conexão ainda está ativa
if (!$mysqli->ping()) {
    $mysqli->close();
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die('Erro ao reconectar ao MySQL: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
} 