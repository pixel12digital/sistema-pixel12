<?php
require_once __DIR__ . '/../config.php';
require_once 'db.php';
$canal_id = intval($_GET['canal_id'] ?? 0);
if (!$canal_id) { echo 'Canal não informado.'; exit; }
// Buscar mensagens personalizadas
$msgs = [];
$res = $mysqli->query("SELECT tipo, mensagem FROM mensagens_cobranca WHERE canal_id = $canal_id");
while ($row = $res->fetch_assoc()) $msgs[$row['tipo']] = $row['mensagem'];
// Critérios e labels
$criterios = [
  'vencendo_3dias' => 'Vencendo em 3 dias',
  'vencendo_hoje' => 'Vencendo hoje',
  'vencida_1dia' => 'Vencida há 1 dia',
  'vencida_3dias' => 'Vencida há 3 dias',
  'vencida_loop' => 'Vencida (loop)',
  'vencida_15dias' => 'Vencida há 15 dias (suspensão)'
];
$hoje = date('Y-m-d');
$em3dias = date('Y-m-d', strtotime('+3 days'));
$ontem = date('Y-m-d', strtotime('-1 day'));
$ha3dias = date('Y-m-d', strtotime('-3 days'));
$ha15dias = date('Y-m-d', strtotime('-15 days'));
$rows = [];
// Exemplo: buscar faturas para cada critério (ajuste conforme seu banco)
// Vencendo em 3 dias
$q = $mysqli->query("SELECT f.*, c.nome, c.celular, f.link_boleto FROM faturas f JOIN clientes c ON f.cliente_id = c.id WHERE f.status = 'pendente' AND f.vencimento = '$em3dias'");
while ($f = $q->fetch_assoc()) $rows[] = ['criterio'=>'vencendo_3dias','nome'=>$f['nome'],'vencimento'=>$f['vencimento'],'valor'=>$f['valor'],'link'=>$f['link_boleto'],'celular'=>$f['celular']];
// Vencendo hoje
$q = $mysqli->query("SELECT f.*, c.nome, c.celular, f.link_boleto FROM faturas f JOIN clientes c ON f.cliente_id = c.id WHERE f.status = 'pendente' AND f.vencimento = '$hoje'");
while ($f = $q->fetch_assoc()) $rows[] = ['criterio'=>'vencendo_hoje','nome'=>$f['nome'],'vencimento'=>$f['vencimento'],'valor'=>$f['valor'],'link'=>$f['link_boleto'],'celular'=>$f['celular']];
// Vencida há 1 dia
$q = $mysqli->query("SELECT f.*, c.nome, c.celular, f.link_boleto FROM faturas f JOIN clientes c ON f.cliente_id = c.id WHERE f.status = 'pendente' AND f.vencimento = '$ontem'");
while ($f = $q->fetch_assoc()) $rows[] = ['criterio'=>'vencida_1dia','nome'=>$f['nome'],'vencimento'=>$f['vencimento'],'valor'=>$f['valor'],'link'=>$f['link_boleto'],'celular'=>$f['celular']];
// Vencida há 3 dias
$q = $mysqli->query("SELECT f.*, c.nome, c.celular, f.link_boleto FROM faturas f JOIN clientes c ON f.cliente_id = c.id WHERE f.status = 'pendente' AND f.vencimento = '$ha3dias'");
while ($f = $q->fetch_assoc()) $rows[] = ['criterio'=>'vencida_3dias','nome'=>$f['nome'],'vencimento'=>$f['vencimento'],'valor'=>$f['valor'],'link'=>$f['link_boleto'],'celular'=>$f['celular']];
// Vencida há 15 dias (suspensão)
$q = $mysqli->query("SELECT f.*, c.nome, c.celular, f.link_boleto FROM faturas f JOIN clientes c ON f.cliente_id = c.id WHERE f.status = 'pendente' AND f.vencimento = '$ha15dias'");
while ($f = $q->fetch_assoc()) $rows[] = ['criterio'=>'vencida_15dias','nome'=>$f['nome'],'vencimento'=>$f['vencimento'],'valor'=>$f['valor'],'link'=>$f['link_boleto'],'celular'=>$f['celular']];
// Vencida (loop) - exemplo: vencidas há mais de 3 dias e não quitadas
$q = $mysqli->query("SELECT f.*, c.nome, c.celular, f.link_boleto FROM faturas f JOIN clientes c ON f.cliente_id = c.id WHERE f.status = 'pendente' AND f.vencimento < '$ha3dias'");
while ($f = $q->fetch_assoc()) $rows[] = ['criterio'=>'vencida_loop','nome'=>$f['nome'],'vencimento'=>$f['vencimento'],'valor'=>$f['valor'],'link'=>$f['link_boleto'],'celular'=>$f['celular']];
// Monta tabela
if (!$rows) { echo '<div>Nenhuma cobrança encontrada.</div>'; exit; }
echo '<table class="com-table"><thead><tr><th>Nome</th><th>Critério</th><th>Vencimento</th><th>Valor</th><th>Link</th><th>Mensagem</th><th>Enviar</th><th>Status</th></tr></thead><tbody>';
foreach ($rows as $i => $r) {
  $msg = $msgs[$r['criterio']] ?? '';
  $msg_final = str_replace(['{nome}','{link}'], [$r['nome'], $r['link']], $msg);
  echo '<tr>';
  echo '<td>' . htmlspecialchars($r['nome']) . '</td>';
  echo '<td>' . htmlspecialchars($criterios[$r['criterio']]) . '</td>';
  echo '<td>' . htmlspecialchars(date('d/m/Y', strtotime($r['vencimento']))) . '</td>';
  echo '<td>R$ ' . number_format($r['valor'],2,',','.') . '</td>';
  echo '<td><a href="' . htmlspecialchars($r['link']) . '" target="_blank">Ver boleto</a></td>';
  echo '<td>' . htmlspecialchars($msg_final) . '</td>';
  echo '<td><button class="btn-ac btn-enviar-cobranca" data-celular="' . htmlspecialchars($r['celular']) . '" data-msg="' . htmlspecialchars($msg_final) . '">Enviar</button></td>';
  echo '<td class="status-envio"></td>';
  echo '</tr>';
}
echo '</tbody></table>';
// JS para envio individual
?>
<script>
document.querySelectorAll('.btn-enviar-cobranca').forEach(function(btn) {
  btn.onclick = function() {
    var tr = this.closest('tr');
    var celular = this.getAttribute('data-celular');
    var msg = this.getAttribute('data-msg');
    var statusTd = tr.querySelector('.status-envio');
    statusTd.innerHTML = 'Enviando...';
    fetch('painel/enviar_cobranca.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ celular, msg })
    })
    .then(r => r.json())
    .then(resp => { statusTd.innerHTML = resp.success ? 'Enviado!' : ('Erro: ' + resp.error); })
    .catch(() => { statusTd.innerHTML = 'Erro ao enviar.'; });
  };
});
</script> 