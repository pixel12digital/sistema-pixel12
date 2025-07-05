// Buscar canais WhatsApp conectados
$canais_whatsapp = [];
$resCanais = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE tipo = 'whatsapp' AND status = 'conectado'");
while ($row = $resCanais->fetch_assoc()) {
  $canais_whatsapp[] = $row;
}
if (!empty($cliente['celular']) && count($canais_whatsapp) > 0) {
  if (count($canais_whatsapp) === 1) {
    // Só um canal, link direto
    $canal = $canais_whatsapp[0];
    echo 'Celular: <a href="chat.php?cliente_id=' . intval($cliente['id']) . '&canal_id=' . intval($canal['id']) . '" style="color:#7c2ae8;text-decoration:underline;cursor:pointer;" title="Abrir conversa WhatsApp interna">' . htmlspecialchars($cliente['celular']) . '</a>';
  } else {
    // Mais de um canal, exibe popover/modal para escolher
    $canalList = htmlspecialchars(json_encode($canais_whatsapp), ENT_QUOTES, 'UTF-8');
    echo 'Celular: <a href="#" onclick="abrirSelecaoCanalWhatsapp(' . intval($cliente['id']) . ', \'" . addslashes($cliente['celular']) . "\', \'" . $canalList . "\')" style="color:#7c2ae8;text-decoration:underline;cursor:pointer;" title="Escolher canal WhatsApp">' . htmlspecialchars($cliente['celular']) . '</a>';
    echo '<div id="modalSelecaoCanalWhatsapp" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:9999;align-items:center;justify-content:center;">';
    echo '<div style="background:#fff;padding:32px 32px 24px 32px;border-radius:16px;min-width:320px;max-width:420px;width:100%;position:relative;box-shadow:0 8px 32px #0002,0 1.5px 8px #a259e633;display:flex;flex-direction:column;align-items:center;">';
    echo '<span style="position:absolute;top:18px;right:22px;font-size:26px;cursor:pointer;color:#a259e6;" onclick="document.getElementById(\'modalSelecaoCanalWhatsapp\').style.display=\'none\'">&times;</span>';
    echo '<h3 style="font-size:1.15rem;font-weight:600;color:#232836;letter-spacing:0.5px;margin-bottom:2px;">Escolha o canal WhatsApp</h3>';
    echo '<div id="listaCanaisWhatsapp"></div>';
    echo '</div></div>';
    echo '<script>
function abrirSelecaoCanalWhatsapp(clienteId, celular, canaisJson) {
  var canais = JSON.parse(canaisJson);
  var lista = canais.map(function(canal) {
    return `<div style=\"margin:10px 0;\"><a href=\"chat.php?cliente_id=${clienteId}&canal_id=${canal.id}\" style=\"color:#7c2ae8;text-decoration:underline;font-weight:500;font-size:1.08em;\">${canal.nome_exibicao} (${canal.identificador})</a></div>`;
  }).join('');
  document.getElementById("listaCanaisWhatsapp").innerHTML = lista;
  document.getElementById("modalSelecaoCanalWhatsapp").style.display = 'flex';
}
</script>';
  }
} else {
  echo 'Celular: ' . (!empty($cliente['celular']) ? htmlspecialchars($cliente['celular']) : '-');
} 