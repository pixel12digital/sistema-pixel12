<?php
// Conexão MySQL padrão XAMPP local
$mysqli = new mysqli('localhost', 'root', '', 'admin_revenda_sites');
if ($mysqli->connect_errno) {
    die('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
?> 