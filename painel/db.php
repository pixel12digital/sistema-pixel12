<?php
// Conexão MySQL padrão Hostinger
$mysqli = new mysqli('srv1067.hstgr.io', 'u819562010_revenda_sites', 'Los@ngo#081081', 'u819562010_revenda_sites');
if ($mysqli->connect_errno) {
    die('Erro ao conectar ao MySQL: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
?> 