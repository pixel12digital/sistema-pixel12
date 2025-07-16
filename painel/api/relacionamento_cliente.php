<?php
require_once '../config.php';
require_once '../db.php';
header('Content-Type: text/html; charset=utf-8');
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
global $mysqli;
if (!$cliente_id) { echo '<div class="text-gray-500">Cliente não informado.</div>'; exit; }
// Buscar todas as mensagens do cliente
$historico = [];
$res_hist = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m LEFT JOIN canais_comunicacao c ON m.canal_id = c.id WHERE m.cliente_id = $cliente_id ORDER BY m.data_hora DESC");
while ($msg = $res_hist && $res_hist->num_rows ? $res_hist->fetch_assoc() : null) {
  $historico[] = $msg;
}
if (empty($historico)) {
  echo '<div class="text-gray-500">Nenhuma interação registrada para este cliente.</div>';
  exit;
}
$ultimo_dia = '';
foreach ($historico as $msg) {
  $dia = date('d/m/Y', strtotime($msg['data_hora']));
  if ($dia !== $ultimo_dia) {
    if ($ultimo_dia !== '') echo '</div>';
    echo '<div style="margin-top:18px;"><div style="color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:6px;">' . $dia . '</div>';
    $ultimo_dia = $dia;
  }
  $is_received = $msg['direcao'] === 'recebido';
  $bubble = $is_received ? 'background:#23232b;color:#fff;' : 'background:#7c2ae8;color:#fff;';
  $canal = htmlspecialchars($msg['canal_nome'] ?? 'Canal');
  $hora = date('H:i', strtotime($msg['data_hora']));
  $is_cobranca_automatica = false;
  $tipo_cobranca = '';
  $mensagem_original = $msg['mensagem'];
  $mensagem_lower = strtolower($mensagem_original);
  if (!$is_received && !empty($mensagem_original)) {
    if (strpos($mensagem_lower, 'lembrete') !== false && strpos($mensagem_lower, 'pagamento') !== false) {
      $is_cobranca_automatica = true;
      $tipo_cobranca = 'Lembrete de pagamento';
    } elseif (strpos($mensagem_lower, 'cobrança') !== false && strpos($mensagem_lower, 'vencida') !== false) {
      $is_cobranca_automatica = true;
      $tipo_cobranca = 'Aviso de cobrança vencida';
    } elseif (strpos($mensagem_lower, 'fatura') !== false && strpos($mensagem_lower, 'disponível') !== false) {
      $is_cobranca_automatica = true;
      $tipo_cobranca = 'Fatura disponível';
    } elseif (strpos($mensagem_lower, 'pagamento') !== false && strpos($mensagem_lower, 'confirmado') !== false) {
      $is_cobranca_automatica = true;
      $tipo_cobranca = 'Pagamento confirmado';
    } elseif (strpos($mensagem_lower, 'boleto') !== false || strpos($mensagem_lower, 'pix') !== false) {
      $is_cobranca_automatica = true;
      $tipo_cobranca = 'Informação de pagamento';
    }
  }
  $conteudo = '';
  if ($is_cobranca_automatica && (
    $mensagem_original === 'WhatsApp: ' . $canal . ' — ' . $tipo_cobranca ||
    $mensagem_lower === strtolower('WhatsApp: ' . $canal . ' — ' . $tipo_cobranca)
  )) {
    $conteudo = 'WhatsApp: ' . $canal . ' — ' . $tipo_cobranca;
  } else {
    if (!empty($msg['anexo'])) {
      $ext = strtolower(pathinfo($msg['anexo'], PATHINFO_EXTENSION));
      if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
        $conteudo .= '<a href="' . htmlspecialchars($msg['anexo']) . '" target="_blank"><img src="' . htmlspecialchars($msg['anexo']) . '" alt="anexo" style="max-width:160px;max-height:100px;border-radius:8px;box-shadow:0 1px 4px #0001;margin-bottom:4px;"></a><br>';
      } else {
        $nome_arquivo = basename($msg['anexo']);
        $conteudo .= '<a href="' . htmlspecialchars($msg['anexo']) . '" target="_blank" style="color:#7c2ae8;text-decoration:underline;"><span style="color:#7c2ae8;">📎</span> ' . htmlspecialchars($nome_arquivo) . '</a><br>';
      }
    }
    $conteudo .= htmlspecialchars($mensagem_original);
  }
  $id_msg = intval($msg['id']);
  $is_enviado = !$is_received;
  echo '<div style="' . $bubble . 'border-radius:10px;padding:10px 16px;margin-bottom:8px;max-width:520px;box-shadow:0 1px 4px #0001;display:inline-block;position:relative;">';
  echo '<div style="font-size:0.98em;font-weight:500;margin-bottom:2px;">' . $canal . ' <span style="font-size:0.92em;color:#888;font-weight:400;">' . ($is_received ? 'Recebido' : 'Enviado') . ' às ' . $hora . '</span></div>';
  echo '<span class="msg-text" data-id="' . $id_msg . '">' . $conteudo . '</span>';
  if ($is_enviado) {
    echo '<button class="btn-editar-msg" data-id="' . $id_msg . '" style="position:absolute;top:8px;right:8px;background:none;border:none;cursor:pointer;color:#fff;opacity:0.7;">✏️</button>';
  }
  echo '</div>';
}
if ($ultimo_dia !== '') echo '</div>'; 