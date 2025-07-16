// Buscar canais WhatsApp conectados
$canais_whatsapp = [];
$resCanais = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status = 'conectado'");
while ($row = $resCanais->fetch_assoc()) {
  $canais_whatsapp[] = $row;
}
// var_dump($canais_whatsapp); // DEBUG: Exibir canais WhatsApp conectados
if (!empty($cliente['celular']) && count($canais_whatsapp) > 0) {
  if (count($canais_whatsapp) === 1) {
    // Só um canal, link direto
    $canal = $canais_whatsapp[0];
    echo 'Celular: <a href="chat.php?cliente_id=' . intval($cliente['id']) . '&canal_id=' . intval($canal['id']) . '" style="color:#7c2ae8;text-decoration:underline;cursor:pointer;" title="Abrir conversa WhatsApp interna">' . htmlspecialchars($cliente['celular']) . '</a>';
  } else {
    // (Removido: bloco antigo de seleção de canal e modal JS)
  }
} else {
  echo 'Celular: ' . (!empty($cliente['celular']) ? htmlspecialchars($cliente['celular']) : '-');
} 