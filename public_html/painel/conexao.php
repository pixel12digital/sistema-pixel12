<?php
// Função para conectar ao banco de dados de um cliente
function conectarCliente($host, $usuario, $senha, $banco) {
    $conn = new mysqli($host, $usuario, $senha, $banco);
    if ($conn->connect_error) {
        die('Erro na conexão: ' . $conn->connect_error);
    }
    return $conn;
}

// Conexão MySQL padrão para o painel (Hostinger)
$host = 'srv1067.hstgr.io';
$usuario = 'u819562010_revenda_sites';
$senha = 'Los@ngo#081081';
$banco = 'u819562010_revenda_sites';
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die('Erro na conexão com o banco de dados: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?> 