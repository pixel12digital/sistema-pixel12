<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page = 'comunicacao.php';
$page_title = 'Comunicação - Gerenciar Canais';
require_once 'config.php';
require_once 'db.php';

// Processa exclusão de canal antes de renderizar a página
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['acao']) && $_POST['acao'] === 'excluir_canal' &&
  isset($_POST['canal_id'])
) {
  $canal_id = intval($_POST['canal_id']);
  $mysqli->query("DELETE FROM canais_comunicacao WHERE id = $canal_id");
  echo '<script>location.href = location.pathname;</script>';
  exit;
}

// Processa cadastro de canal
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['acao']) && $_POST['acao'] === 'add_canal'
) {
  $identificador = '';
  $nome_exibicao = $mysqli->real_escape_string(trim($_POST['nome_exibicao']));
  $porta = intval($_POST['porta']);
  $tipo = 'whatsapp';
  $status = 'pendente';
  
  // Verifica se já existe um canal com esta porta
  $canal_existente = $mysqli->query("SELECT id FROM canais_comunicacao WHERE porta = $porta")->fetch_assoc();
  if ($canal_existente) {
    $erro_cadastro = 'Já existe um canal WhatsApp nesta porta.';
  } else {
    // Canal não existe, insere novo
    $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao, porta) VALUES ('$tipo', '$identificador', '$nome_exibicao', '$status', NULL, $porta)");
    $canal_id = $mysqli->insert_id;
  }
  
  // Se não houve erro, apenas recarrega a página para mostrar o novo canal
  if (!isset($erro_cadastro) && isset($canal_id)) {
    echo '<script>location.href = location.pathname;</script>';
    exit;
  }
}

// Processa salvamento de mensagens de cobrança
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['acao']) && $_POST['acao'] === 'salvar_mensagens_cobranca'
) {
  $canal_id = intval($_POST['canal_id']);
  $tipos = [
    'vencendo_3dias', 'vencendo_hoje', 'vencida_1dia', 
    'vencida_3dias', 'vencida_loop', 'vencida_15dias'
  ];
  
  foreach ($tipos as $tipo) {
    $msg = $mysqli->real_escape_string(trim($_POST['mensagem_' . $tipo]));
    $mysqli->query("INSERT INTO mensagens_cobranca (canal_id, tipo, mensagem) VALUES ($canal_id, '$tipo', '$msg') ON DUPLICATE KEY UPDATE mensagem = '$msg'");
  }
  
  echo '<script>alert("Mensagens salvas com sucesso!");location.href=location.pathname;</script>';
  exit;
}

// Processa definição de canal padrão por função
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'set_padrao_funcao') {
  $funcao = $_POST['funcao'];
  $canal_id = intval($_POST['canal_id']);
  $mysqli->query("INSERT INTO canais_padrao_funcoes (funcao, canal_id) VALUES ('" . $mysqli->real_escape_string($funcao) . "', $canal_id) ON DUPLICATE KEY UPDATE canal_id = $canal_id");
  echo '<script>location.href=location.pathname;</script>';
  exit;
}

include 'template.php';

function render_content() {
  global $mysqli, $erro_cadastro;
  
  // CSS PADRÃO DO PAINEL
  echo '<style>'
  . 'body { background: #f7f8fa; }'
  . '.com-table { width: 100%; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 2px 12px #0001; margin-bottom: 30px; border-collapse: separate; border-spacing: 0; }'
  . '.com-table th { background: #ede9fe; color: #4b2995; font-weight: bold; font-size: 1.08em; padding: 14px 10px; text-align: left; }'
  . '.com-table td { padding: 13px 10px; font-size: 1.04em; text-align: left; }'
  . '.com-table tr.zebra { background: #f3f4f6; }'
  . '.com-table tr { border-bottom: 1px solid #ececec; }'
  . '.com-table tr:last-child { border-bottom: none; }'
  . '.status-conectado { color: #22c55e; font-weight: bold; }'
  . '.status-pendente { color: #f59e42; font-weight: bold; }'
  . '.status-verificando { color: #6b7280; font-style: italic; }'
  . '.btn-ac { display: inline-block; margin: 0 2px; padding: 5px 12px; border-radius: 6px; font-weight: 500; text-decoration: none; transition: background 0.2s; font-size: 0.97em; border: none; cursor: pointer; }'
  . '.btn-editar { background: #ede9fe; color: #6d28d9; border: 1px solid #c7d2fe; }'
  . '.btn-editar:hover { background: #c7d2fe; }'
  . '.btn-conectar { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }'
  . '.btn-conectar:hover { background: #bbf7d0; }'
  . '.btn-excluir { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }'
  . '.btn-excluir:hover { background: #fecaca; }'
  . '.btn-desconectar { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }'
  . '.btn-desconectar:hover { background: #fecaca; }'
  . '.com-table th, .com-table td { vertical-align: middle; }'
  . '.com-table thead { position: sticky; top: 0; z-index: 1; }'
  . '.modal { background: #fff; border-radius: 14px; box-shadow: 0 8px 32px #0003; padding: 36px 28px; min-width: 320px; max-width: 95vw; position: relative; }'
  . '.modal h3 { font-size: 1.25em; margin-bottom: 18px; }'
  . '.modal button { top: 14px; right: 18px; }'
  . '@media (max-width: 700px) { .com-table th, .com-table td { padding: 8px 2px; font-size: 0.95em; } .modal { padding: 18px 6px; } }'
  . '</style>';
  
  echo '<link rel="stylesheet" href="/public/assets/css/style.css">';
  echo '<h1 class="text-2xl font-bold mb-6">Central de Comunicação</h1>';
  
  // Botão de cadastrar canal
  echo '<div class="mb-4 flex justify-between items-center">';
  echo '<h2 class="text-lg font-semibold">Canais conectados</h2>';
  echo '<button id="btn-cadastrar-robo" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold">Cadastrar Canal</button>';
  echo '</div>';

  // Calcula próxima porta disponível
  $porta_sugerida = 3000;
  $resPorta = $mysqli->query("SELECT MAX(porta) as max_porta FROM canais_comunicacao WHERE status <> 'excluido'");
  if ($resPorta && ($rowPorta = $resPorta->fetch_assoc()) && $rowPorta['max_porta']) {
    $porta_sugerida = intval($rowPorta['max_porta']) + 1;
  }

  // ===== RENDERIZAÇÃO DE TODOS OS MODAIS =====
  
  // Modal de cadastrar robô/canal
  echo '<div id="modal-cadastrar-robo" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div style="background:#fff;padding:32px 24px;border-radius:10px;min-width:300px;position:relative;">';
  echo '<button id="close-modal-cadastrar" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Cadastrar Canal WhatsApp</h3>';
  if (isset($erro_cadastro)) {
    echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-4">' . htmlspecialchars($erro_cadastro) . '</div>';
  }
  echo '<form method="post" id="form-cadastrar-canal">';
  echo '<input type="hidden" name="acao" value="add_canal">';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Nome de Exibição</label><input type="text" name="nome_exibicao" value="" required class="border rounded px-3 py-2 w-full" placeholder="Ex: Financeiro"></div>';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Porta do Robô</label><input type="number" name="porta" required class="border rounded px-3 py-2 w-full" value="' . $porta_sugerida . '" placeholder="Ex: 3000"></div>';
  echo '<button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold w-full">Cadastrar Canal</button>';
  echo '</form>';
  echo '</div></div>';

  // Modal de adicionar canal (genérico)
  echo '<div id="modal-add-canal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal">';
  echo '<button id="close-modal-canal" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Adicionar Canal WhatsApp</h3>';
  echo '<form method="post" id="form-add-canal">';
  echo '<input type="hidden" name="acao" value="add_canal">';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Número WhatsApp (com DDD e país)</label><input type="text" name="identificador" required class="border rounded px-3 py-2 w-full" placeholder="Ex: 5511999999999"></div>';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Nome de Exibição</label><input type="text" name="nome_exibicao" required class="border rounded px-3 py-2 w-full" placeholder="Ex: Suporte 1"></div>';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Porta do Robô</label><input type="number" name="porta" required class="border rounded px-3 py-2 w-full" placeholder="Ex: 3000"></div>';
  echo '<button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold w-full">Salvar Canal</button>';
  echo '</form>';
  echo '</div></div>';

  // Modal para exibir QR Code
  echo '<div id="modal-qr-canal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal">';
  echo '<button id="close-modal-qr" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Conectar WhatsApp</h3>';
  echo '<div id="qr-code-area" class="flex flex-col items-center justify-center" style="min-height:180px;"></div>';
  echo '</div></div>';

  // Modal de confirmação de exclusão
  echo '<div id="modal-confirm-excluir" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal">';
  echo '<button id="close-modal-excluir" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Excluir Canal</h3>';
  echo '<p class="mb-4">Tem certeza que deseja excluir este canal? Esta ação não poderá ser desfeita.</p>';
  echo '<form method="post" id="form-excluir-canal">';
  echo '<input type="hidden" name="acao" value="excluir_canal">';
  echo '<input type="hidden" name="canal_id" id="input-canal-id-excluir">';
  echo '<button type="submit" class="bg-red-600 hover:bg-red-800 text-white px-4 py-2 rounded font-semibold w-full">Excluir</button>';
  echo '</form>';
  echo '</div></div>';

  // Modal de personalização de mensagens de cobrança
  echo '<div id="modal-mensagens-cobranca" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal" style="max-width:600px;">';
  echo '<button id="close-modal-mensagens" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Personalizar Mensagens de Cobrança</h3>';
  echo '<form method="post" id="form-mensagens-cobranca">';
  echo '<input type="hidden" name="acao" value="salvar_mensagens_cobranca">';
  echo '<input type="hidden" name="canal_id" id="input-canal-id-mensagens">';
  echo '<div class="mb-2 text-sm text-gray-600">Use <b>{nome}</b> para o nome do cliente e <b>{link}</b> para o link da fatura.</div>';
  
  $tipos = [
    'vencendo_3dias' => 'Fatura vence em 3 dias',
    'vencendo_hoje' => 'Fatura vence hoje',
    'vencida_1dia' => 'Fatura vencida há 1 dia',
    'vencida_3dias' => 'Fatura vencida há 3 dias',
    'vencida_loop' => 'Fatura vencida (loop)',
    'vencida_15dias' => 'Fatura vencida há 15 dias (suspensão)'
  ];
  
  $mensagens_padrao = [
    'vencendo_3dias' => 'Olá {nome}! Notamos que sua fatura vence em 3 dias. Se precisar de alguma informação ou apoio, estamos à disposição. {link}',
    'vencendo_hoje' => 'Olá {nome}! Lembrando que sua fatura vence hoje. Caso já tenha realizado o pagamento, por favor, desconsidere esta mensagem. {link}',
    'vencida_1dia' => 'Olá {nome}! Identificamos que sua fatura está em aberto desde ontem. Se precisar de ajuda, conte conosco. {link}',
    'vencida_3dias' => 'Olá {nome}! Sua fatura está em aberto há alguns dias. Se já regularizou, desconsidere. Se precisar de apoio, estamos aqui. {link}',
    'vencida_loop' => 'Olá {nome}! Sua fatura segue em aberto. Caso já tenha efetuado o pagamento, por favor, ignore esta mensagem. Estamos à disposição para ajudar. {link}',
    'vencida_15dias' => 'Olá {nome}! Sua assinatura está com mais de 15 dias de atraso. Para evitar a suspensão dos serviços, por favor, regularize o pagamento. Se já pagou, desconsidere. Em caso de dúvidas, estamos prontos para ajudar. {link}'
  ];
  
  foreach ($tipos as $tipo => $label) {
    $msg = $mensagens_padrao[$tipo];
    echo '<div class="mb-3"><label class="block text-sm font-medium">' . $label . '</label>';
    echo '<textarea name="mensagem_' . $tipo . '" rows="2" class="border rounded px-3 py-2 w-full">' . htmlspecialchars($msg) . '</textarea></div>';
  }
  
  echo '<button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold w-full">Salvar Mensagens</button>';
  echo '</form>';
  echo '</div></div>';

  // Modal da fila de cobrança
  echo '<div id="modal-fila-cobranca" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal" style="max-width:900px;min-width:350px;">';
  echo '<button id="close-modal-fila" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Fila de Envio de Cobranças</h3>';
  echo '<div id="fila-cobranca-lista">Carregando...</div>';
  echo '</div></div>';

  // Buscar canais padrão por função
  $padroes = [];
  $resPadrao = $mysqli->query("SELECT funcao, canal_id FROM canais_padrao_funcoes");
  if ($resPadrao) while ($p = $resPadrao->fetch_assoc()) $padroes[$p['funcao']] = $p['canal_id'];
  // Buscar todos os canais ativos
  $canais = [];
  $resCanais = $mysqli->query("SELECT id, nome_exibicao, identificador FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY nome_exibicao, id");
  if ($resCanais) while ($c = $resCanais->fetch_assoc()) $canais[] = $c;
  // Bloco de seleção de canal padrão por função
  if (count($canais) > 0) {
    echo '<div class="mb-6 flex flex-wrap gap-8">';
    foreach ([['financeiro','Financeiro'],['comercial','Comercial']] as $f) {
      $func = $f[0]; $label = $f[1];
      echo '<form class="form-set-padrao-funcao" method="post" style="display:inline-block;min-width:260px;">';
      echo '<input type="hidden" name="acao" value="set_padrao_funcao">';
      echo '<input type="hidden" name="funcao" value="' . $func . '">';
      echo '<label class="block text-sm font-semibold mb-1">Canal padrão para ' . $label . ':</label>';
      echo '<select name="canal_id" class="border rounded px-2 py-1 w-full">';
      echo '<option value="">-- Selecione --</option>';
      foreach ($canais as $c) {
        $sel = (isset($padroes[$func]) && $padroes[$func] == $c['id']) ? 'selected' : '';
        echo '<option value="' . $c['id'] . '" ' . $sel . '>' . htmlspecialchars($c['nome_exibicao']) . ' (' . htmlspecialchars($c['identificador']) . ')</option>';
      }
      echo '</select>';
      echo '<span class="msg-sucesso-setpadrao" style="display:none;color:#22c55e;font-size:0.97em;margin-left:8px;">Salvo!</span>';
      echo '</form>';
    }
    echo '</div>';
  }

  // Adicionar modal de erro reutilizável no HTML

  echo '<div id="modal-erro" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:99999;align-items:center;justify-content:center;">';
  echo '<div class="modal" style="max-width:420px;min-width:280px;text-align:center;">';
  echo '<button id="close-modal-erro" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4" id="modal-erro-titulo">Erro</h3>';
  echo '<div id="modal-erro-msg" style="white-space:pre-line;"></div>';
  echo '</div></div>';

  // ===== RENDERIZAÇÃO DA TABELA DE CANAIS =====
  
  $res = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY data_conexao DESC, id DESC");
  echo '<div class="overflow-x-auto"><table class="com-table">';
  echo '<thead class="bg-gray-100"><tr>';
  echo '<th class="px-4 py-2">Tipo</th>';
  echo '<th class="px-4 py-2">Identificador</th>';
  echo '<th class="px-4 py-2">Nome de Exibição</th>';
  echo '<th class="px-4 py-2">Status</th>';
  echo '<th class="px-4 py-2">Última Sessão</th>';
  echo '<th class="px-4 py-2">Porta</th>';
  echo '<th class="px-4 py-2" style="text-align:center;">Ações</th>';
  echo '</tr></thead><tbody>';
  
  if ($res && $res->num_rows > 0) {
    $i = 0;
    while ($row = $res->fetch_assoc()) {
      $zebra = ($i++ % 2 == 0) ? ' style="background:#f3f4f6;"' : '';
      echo '<tr' . $zebra . '>';
      echo '<td class="px-4 py-2">' . htmlspecialchars(ucfirst($row['tipo'])) . '</td>';
      echo '<td class="px-4 py-2">' . htmlspecialchars($row['identificador']) . '</td>';
      echo '<td class="px-4 py-2">' . htmlspecialchars($row['nome_exibicao']) . '</td>';
      // Status: sempre vazio, será preenchido pelo JS após consulta real
      echo '<td class="px-4 py-2 canal-status-area status-verificando" data-canal-id="' . $row['id'] . '" data-porta="' . $row['porta'] . '"><span class="status-text">Verificando...</span></td>';
      echo '<td class="px-4 py-2 canal-data-conexao" data-canal-id="' . $row['id'] . '">-</td>';
      echo '<td class="px-4 py-2">' . ($row['porta'] ? htmlspecialchars($row['porta']) : '-') . '</td>';
      $acoes = '';
      $acoes .= '<div class="acoes-btn-group" style="display:flex;gap:8px;align-items:center;justify-content:center;">';
      $acoes .= '<div class="acoes-btn-area" data-canal-id="' . $row['id'] . '"></div>';
      $acoes .= '<a href="#" class="btn-ac btn-excluir btn-excluir-canal" data-canal-id="' . $row['id'] . '">Excluir</a>';
      $acoes .= '</div>';
      echo '<td class="px-4 py-2" style="text-align:center;">' . $acoes . '</td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="7" class="text-center text-gray-400 py-4">Nenhum canal cadastrado ainda.</td></tr>';
  }
  
  echo '</tbody></table></div>';
}

// ===== JAVASCRIPT CONSOLIDADO NO FINAL =====
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
function exibirErro(titulo, msg) {
  document.getElementById('modal-erro-titulo').textContent = titulo || 'Erro';
  document.getElementById('modal-erro-msg').textContent = msg || 'Ocorreu um erro inesperado.';
  document.getElementById('modal-erro').style.display = 'flex';
}

// ===== ALERTA VISUAL DE CANAIS DESCONECTADOS =====
function exibirAlertaCanaisDesconectados(qtd) {
  if (qtd > 0) {
    showPushNotification('Atenção: Existem canais WhatsApp desconectados!', 0);
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var modalQr = document.getElementById('modal-qr-canal');
  var closeQr = document.getElementById('close-modal-qr');
  var pollingInterval = null;
  var pollingPorta = null;
  var pollingStatusInterval = null;
  var pollingStatusPaused = false;
  var pushStatusErrorShown = false;
  var qrCodeErrorShown = false;

  function iniciarPollingQr(porta) {
    pararPollingQr();
    pollingPorta = porta;
    exibirQrCode(porta);
    pollingInterval = setInterval(function() {
      exibirQrCode(porta);
      checarStatus(porta);
    }, 20000); // 20 segundos
  }
  function pararPollingQr() {
    if (pollingInterval) {
      clearInterval(pollingInterval);
      pollingInterval = null;
      pollingPorta = null;
    }
  }

  function iniciarPollingStatus() {
    if (pollingStatusInterval) clearInterval(pollingStatusInterval);
    pollingStatusPaused = false;
    atualizarStatusCanais();
    pollingStatusInterval = setInterval(function() {
      if (!pollingStatusPaused) atualizarStatusCanais();
    }, 600000); // 10 minutos
  }
  function pausarPollingStatus() {
    pollingStatusPaused = true;
  }
  function retomarPollingStatus() {
    pollingStatusPaused = false;
  }

  // Atualiza status e botão de cada canal
  document.querySelectorAll('.canal-status-area').forEach(function(td) {
    var canalId = td.getAttribute('data-canal-id');
    var porta = td.getAttribute('data-porta');
    var statusText = td.querySelector('.status-text');
    var acoesArea = document.querySelector('.acoes-btn-area[data-canal-id="' + canalId + '"]');
    var dataConexaoTd = document.querySelector('.canal-data-conexao[data-canal-id="' + canalId + '"]');
    function atualizarStatus() {
      fetch('http://localhost:' + porta + '/status')
        .then(r => r.json())
        .then(resp => {
          if (resp.ready) {
            statusText.textContent = 'Conectado';
            td.classList.remove('status-verificando');
            td.classList.add('status-conectado');
            td.classList.remove('status-pendente');
            if (acoesArea) acoesArea.innerHTML = '<button class="btn-ac btn-desconectar btn-desconectar-canal" data-porta="' + porta + '">Desconectar</button>';
            if (resp.lastSession) {
              var dt = new Date(resp.lastSession);
              dataConexaoTd.textContent = dt.toLocaleString('pt-BR');
            } else {
              dataConexaoTd.textContent = '-';
            }
            if (resp.ready && resp.number) {
              // Atualiza o identificador no banco se for diferente
              var atual = td.parentElement.querySelector('td:nth-child(2)').textContent.trim();
              if (resp.number && atual !== resp.number) {
                fetch('atualizar_identificador.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: 'canal_id=' + encodeURIComponent(canalId) + '&identificador=' + encodeURIComponent(resp.number)
                }).then(() => location.reload());
              }
            }
          } else {
            statusText.textContent = 'Desconectado';
            td.classList.remove('status-verificando');
            td.classList.remove('status-conectado');
            td.classList.add('status-pendente');
            if (acoesArea) acoesArea.innerHTML = '<button class="btn-ac btn-conectar btn-conectar-canal" data-porta="' + porta + '">Conectar</button>';
            dataConexaoTd.textContent = '-';
          }
        })
        .catch(() => {
          statusText.textContent = 'Desconectado';
          td.classList.remove('status-verificando');
          td.classList.remove('status-conectado');
          td.classList.add('status-pendente');
          if (acoesArea) acoesArea.innerHTML = '<button class="btn-ac btn-conectar btn-conectar-canal" data-porta="' + porta + '">Conectar</button>';
          dataConexaoTd.textContent = '-';
        });
    }
    atualizarStatus();
    setInterval(atualizarStatus, 30000); // Aumentado para 30 segundos para reduzir carga no banco
    if (acoesArea) {
      acoesArea.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-conectar-canal')) {
          abrirModalQr(porta);
        }
        if (e.target.classList.contains('btn-desconectar-canal')) {
          fetch('http://localhost:' + porta + '/logout')
            .then(r => r.json())
            .then(resp => {
              if (resp.success) {
                statusText.textContent = 'Desconectado';
                td.classList.remove('status-verificando');
                td.classList.remove('status-conectado');
                td.classList.add('status-pendente');
                if (acoesArea) acoesArea.innerHTML = '<button class="btn-ac btn-conectar btn-conectar-canal" data-porta="' + porta + '">Conectar</button>';
                dataConexaoTd.textContent = '-';
                alert('Robô desconectado com sucesso!');
              }
            });
        }
      });
    }
  });

  function abrirModalQr(porta) {
    pausarPollingStatus();
    modalQr.style.display = 'flex';
    document.getElementById('qr-code-area').innerHTML = 'Aguardando QR Code...';
    exibirQrCode(porta); // Exibe imediatamente
    // Atualiza o QR Code e checa status a cada 7 segundos enquanto o modal estiver aberto
    let qrInterval = setInterval(function() {
      if (modalQr.style.display === 'flex') {
        exibirQrCode(porta);
        checarStatus(porta, qrInterval); // Passa o intervalo para poder limpar ao conectar
      } else {
        clearInterval(qrInterval);
      }
    }, 7000);
    closeQr.onclick = function() {
      modalQr.style.display = 'none';
      pararPollingQr();
      clearInterval(qrInterval);
      retomarPollingStatus();
    };
  }

  function exibirQrCode(porta) {
    fetch('http://localhost:' + porta + '/qr?_=' + Date.now())
      .then(r => r.json())
      .then(resp => {
        var qrArea = document.getElementById('qr-code-area');
        while (qrArea.firstChild) qrArea.removeChild(qrArea.firstChild);
        if (resp.qr) {
          new QRCode(qrArea, {
            text: resp.qr,
            width: 220,
            height: 220
          });
        } else {
          qrArea.innerHTML = 'QR Code não disponível. Aguarde...';
        }
      })
      .catch(() => {
        var qrArea = document.getElementById('qr-code-area');
        qrArea.innerHTML = '<span style="color:#b91c1c;font-weight:bold;">Erro ao buscar QR Code.<br>Verifique se o robô está rodando e conectado.</span>';
      });
  }

  // Ajuste: aceita qrInterval para garantir que sempre limpa ao conectar
  function checarStatus(porta, qrInterval) {
    fetch('http://localhost:' + porta + '/status')
      .then(r => {
        if (!r.ok) throw new Error('Erro HTTP ao checar status');
        return r.json();
      })
      .then(resp => {
        if (resp.ready) {
          modalQr.style.display = 'none'; // Fecha o modal automaticamente
          pararPollingQr();
          if (qrInterval) clearInterval(qrInterval); // Garante que o polling do modal pare
          retomarPollingStatus(); // Retoma polling global
          atualizarStatusCanais(); // Atualiza status visual imediatamente
          alert('Canal conectado com sucesso!');
        }
      })
      .catch((err) => {
        // Não exibe erro se for polling automático
      });
  }

  // ====== EXCLUSÃO DE CANAL ======
  document.querySelectorAll('.btn-excluir-canal').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var canalId = btn.getAttribute('data-canal-id');
      document.getElementById('input-canal-id-excluir').value = canalId;
      document.getElementById('modal-confirm-excluir').style.display = 'flex';
    });
  });
  document.getElementById('close-modal-excluir').onclick = function() {
    document.getElementById('modal-confirm-excluir').style.display = 'none';
  };

  document.getElementById('btn-cadastrar-robo').onclick = function() {
    document.getElementById('modal-cadastrar-robo').style.display = 'flex';
  };
  document.getElementById('close-modal-cadastrar').onclick = function() {
    document.getElementById('modal-cadastrar-robo').style.display = 'none';
  };

  // AJAX para seleção de canal padrão por função
  document.querySelectorAll('.form-set-padrao-funcao select').forEach(function(sel) {
    sel.addEventListener('change', function(e) {
      var form = sel.closest('form');
      var formData = new FormData(form);
      fetch('', {
        method: 'POST',
        body: formData
      }).then(function(resp) {
        if (resp.ok) {
          var msg = form.querySelector('.msg-sucesso-setpadrao');
          if (msg) {
            msg.style.display = 'inline';
            setTimeout(function() { msg.style.display = 'none'; }, 1200);
          }
        }
      });
    });
  });

  document.getElementById('close-modal-erro').onclick = function() {
    document.getElementById('modal-erro').style.display = 'none';
  };

  // Adiciona botão manual de atualização de status
  const tabelaCanais = document.querySelector('.com-table');
  if (tabelaCanais) {
    const btnAtualizar = document.createElement('button');
    btnAtualizar.textContent = 'Atualizar status';
    btnAtualizar.className = 'btn-ac btn-atualizar-status';
    btnAtualizar.style = 'margin-bottom:12px;background:#ede9fe;color:#7c2ae8;font-weight:bold;border:none;padding:8px 18px;border-radius:8px;cursor:pointer;';
    tabelaCanais.parentElement.insertBefore(btnAtualizar, tabelaCanais);
    btnAtualizar.onclick = function() {
      atualizarStatusCanais();
    };
  }

  // ===== MONITORAMENTO AUTOMÁTICO DOS CANAIS VIA AJAX =====
  function atualizarStatusCanais() {
    fetch('api/status_canais.php')
      .then(r => {
        if (!r.ok && r.status === 503) {
          // Não mostra erro, apenas aguarda (QR aguardando leitura)
          return Promise.reject({ aguardandoQR: true });
        }
        return r.json();
      })
      .then(statusList => {
        let desconectados = 0;
        let conectados = 0;
        statusList.forEach(st => {
          const td = document.querySelector('.canal-status-area[data-canal-id="' + st.id + '"]');
          const dataConexaoTd = document.querySelector('.canal-data-conexao[data-canal-id="' + st.id + '"]');
          if (!td) return;
          const statusText = td.querySelector('.status-text');
          const btnArea = document.querySelector('.acoes-btn-area[data-canal-id="' + st.id + '"]');
          if (st.conectado) {
            td.classList.remove('status-verificando');
            td.classList.add('status-conectado');
            td.classList.remove('status-pendente');
            statusText.textContent = 'Conectado';
            btnArea.innerHTML = '<button class="btn-ac btn-desconectar btn-desconectar-canal" data-porta="' + st.porta + '">Desconectar</button>';
            if (dataConexaoTd) {
              if (st.lastSession) {
                dataConexaoTd.textContent = st.lastSession;
              } else {
                dataConexaoTd.textContent = '-';
              }
            }
            conectados++;
          } else {
            td.classList.remove('status-verificando');
            td.classList.remove('status-conectado');
            td.classList.add('status-pendente');
            statusText.textContent = 'Desconectado';
            btnArea.innerHTML = '<button class="btn-ac btn-conectar btn-conectar-canal" data-porta="' + st.porta + '">Conectar</button>';
            if (dataConexaoTd) {
              if (st.lastSession) {
                dataConexaoTd.textContent = st.lastSession;
              } else {
                dataConexaoTd.textContent = '-';
              }
            }
            desconectados++;
          }
        });
        // Só mostra notificação se houver pelo menos 1 desconectado e nenhum conectado
        if (desconectados > 0 && conectados === 0) {
          showPushNotification('Atenção: Existem canais WhatsApp desconectados!', 0);
        } else {
          // Esconde notificação se todos conectados
          document.getElementById('push-notification').style.display = 'none';
        }
        pushStatusErrorShown = false; // Resetar flag de erro ao sucesso
      })
      .catch((err) => {
        if (err && err.aguardandoQR) {
          // Não mostra erro, apenas aguarda
          return;
        }
        // Força todos os canais para desconectado visualmente
        document.querySelectorAll('.canal-status-area').forEach(function(td) {
          td.classList.remove('status-verificando');
          td.classList.remove('status-conectado');
          td.classList.add('status-pendente');
          const statusText = td.querySelector('.status-text');
          if (statusText) {
            statusText.textContent = 'Desconectado';
          }
        });
        if (!pushStatusErrorShown) {
          showPushNotification('Não foi possível consultar o status dos canais WhatsApp.', 0);
          pushStatusErrorShown = true;
        }
      });
  }
  iniciarPollingStatus();

  // Delegação para botões de conectar/desconectar
  document.querySelectorAll('.com-table').forEach(function(tbl) {
    tbl.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn-conectar-canal')) {
        const porta = e.target.getAttribute('data-porta');
        abrirModalQr(porta);
      }
      if (e.target.classList.contains('btn-desconectar-canal')) {
        const porta = e.target.getAttribute('data-porta');
        fetch('http://localhost:' + porta + '/logout')
          .then(r => r.json())
          .then(resp => {
            if (resp.success) {
              alert('Robô desconectado com sucesso!');
              atualizarStatusCanais();
            }
          });
      }
    });
  });
});
</script>
<?php
?> 