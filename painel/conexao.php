<?php
// Incluir configurações globais
require_once '../config.php';

// Função para conectar ao banco de dados de um cliente
function conectarCliente($host, $usuario, $senha, $banco) {
    $conn = new mysqli($host, $usuario, $senha, $banco);
    if ($conn->connect_error) {
        die('Erro na conexão: ' . $conn->connect_error);
    }
    return $conn;
}

// Conexão MySQL padrão para o painel (Hostinger)
$host = DB_HOST;
$usuario = DB_USER;
$senha = DB_PASS;
$banco = DB_NAME;
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die('Erro na conexão com o banco de dados: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?> 