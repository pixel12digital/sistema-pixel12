<?php
require_once 'config.php';
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $canal_id = intval($_POST['canal_id'] ?? 0);
  $identificador = trim($_POST['identificador'] ?? '');
  if ($canal_id && $identificador) {
    $mysqli->query("UPDATE canais_comunicacao SET identificador = '" . $mysqli->real_escape_string($identificador) . "' WHERE id = $canal_id LIMIT 1");
  }
}
$mysqli->close(); 