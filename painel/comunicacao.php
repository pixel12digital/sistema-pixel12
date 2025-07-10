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
  $mysqli->query("UPDATE canais_comunicacao SET status = 'excluido' WHERE id = $canal_id");
  echo '<script>location.href = location.pathname;</script>';
  exit;
}

// Processa cadastro de canal
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['acao']) && $_POST['acao'] === 'add_canal'
) {
  $identificador = $mysqli->real_escape_string(trim($_POST['identificador']));
  $nome_exibicao = $mysqli->real_escape_string(trim($_POST['nome_exibicao']));
  $porta = intval($_POST['porta']);
  $tipo = 'whatsapp';
  $status = 'pendente';
  
  // Verifica se já existe um canal com este tipo e identificador
  $canal_existente = $mysqli->query("SELECT id, status FROM canais_comunicacao WHERE tipo = '$tipo' AND identificador = '$identificador'")->fetch_assoc();
  
  if ($canal_existente) {
    if ($canal_existente['status'] === 'excluido') {
      // Reativa o canal existente
      $mysqli->query("UPDATE canais_comunicacao SET status = '$status', nome_exibicao = '$nome_exibicao', porta = $porta, data_conexao = NULL WHERE id = " . $canal_existente['id']);
      $canal_id = $canal_existente['id'];
    } else {
      $erro_cadastro = 'Já existe um canal WhatsApp com este número cadastrado.';
    }
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
  . '.btn-ac { display: inline-block; margin: 0 2px; padding: 5px 12px; border-radius: 6px; font-weight: 500; text-decoration: none; transition: background 0.2s; font-size: 0.97em; border: none; cursor: pointer; }'
  . '.btn-editar { background: #ede9fe; color: #6d28d9; border: 1px solid #c7d2fe; }'
  . '.btn-editar:hover { background: #c7d2fe; }'
  . '.btn-conectar { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }'
  . '.btn-conectar:hover { background: #bbf7d0; }'
  . '.btn-excluir { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }'
  . '.btn-excluir:hover { background: #fecaca; }'
  . '.com-table th, .com-table td { vertical-align: middle; }'
  . '.com-table thead { position: sticky; top: 0; z-index: 1; }'
  . '.modal { background: #fff; border-radius: 14px; box-shadow: 0 8px 32px #0003; padding: 36px 28px; min-width: 320px; max-width: 95vw; position: relative; }'
  . '.modal h3 { font-size: 1.25em; margin-bottom: 18px; }'
  . '.modal button { top: 14px; right: 18px; }'
  . '@media (max-width: 700px) { .com-table th, .com-table td { padding: 8px 2px; font-size: 0.95em; } .modal { padding: 18px 6px; } }'
  . '</style>';
  
  echo '<link rel="stylesheet" href="/public/assets/css/style.css">';
  echo '<h1 class="text-2xl font-bold mb-6">Central de Comunicação</h1>';
  
  // Botão de cadastrar robô
  echo '<div class="mb-4 flex justify-between items-center">';
  echo '<h2 class="text-lg font-semibold">Canais conectados</h2>';
  echo '<button id="btn-cadastrar-robo" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold">Cadastrar Robô</button>';
  echo '</div>';

  // Calcula próxima porta disponível
  $porta_sugerida = 3000;
  $resPorta = $mysqli->query("SELECT MAX(porta) as max_porta FROM canais_comunicacao");
  if ($resPorta && ($rowPorta = $resPorta->fetch_assoc()) && $rowPorta['max_porta']) {
    $porta_sugerida = intval($rowPorta['max_porta']) + 1;
  }

  // ===== RENDERIZAÇÃO DE TODOS OS MODAIS =====
  
  // Modal de cadastrar robô
  echo '<div id="modal-cadastrar-robo" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div style="background:#fff;padding:32px 24px;border-radius:10px;min-width:300px;position:relative;">';
  echo '<button id="close-modal-cadastrar" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Cadastrar Robô Financeiro</h3>';
  
  if (isset($erro_cadastro)) {
    echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-4">' . htmlspecialchars($erro_cadastro) . '</div>';
  }
  
  echo '<form method="post">';
  echo '<input type="hidden" name="acao" value="add_canal">';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Número WhatsApp (com DDD e país)</label><input type="text" name="identificador" required class="border rounded px-3 py-2 w-full" placeholder="Ex: 5511999999999"></div>';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Nome de Exibição</label><input type="text" name="nome_exibicao" value="Financeiro" required class="border rounded px-3 py-2 w-full" placeholder="Financeiro"></div>';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Porta do Robô</label><input type="number" name="porta" required class="border rounded px-3 py-2 w-full" value="' . $porta_sugerida . '" placeholder="Ex: 3000"></div>';
  echo '<button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold w-full">Cadastrar</button>';
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

  // ===== RENDERIZAÇÃO DA TABELA DE CANAIS =====
  
  $res = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY data_conexao DESC, id DESC");
  echo '<div class="overflow-x-auto"><table class="com-table">';
  echo '<thead class="bg-gray-100"><tr>';
  echo '<th class="px-4 py-2">Tipo</th>';
  echo '<th class="px-4 py-2">Identificador</th>';
  echo '<th class="px-4 py-2">Nome de Exibição</th>';
  echo '<th class="px-4 py-2">Status</th>';
  echo '<th class="px-4 py-2">Data de Conexão</th>';
  echo '<th class="px-4 py-2">Porta</th>';
  echo '<th class="px-4 py-2">Último Envio</th>';
  echo '<th class="px-4 py-2">Ações</th>';
  echo '</tr></thead><tbody>';
  
  if ($res && $res->num_rows > 0) {
    $i = 0;
    while ($row = $res->fetch_assoc()) {
      $zebra = ($i++ % 2 == 0) ? ' style="background:#f3f4f6;"' : '';
      echo '<tr' . $zebra . '>';
      echo '<td class="px-4 py-2">' . htmlspecialchars(ucfirst($row['tipo'])) . '</td>';
      echo '<td class="px-4 py-2">' . htmlspecialchars($row['identificador']) . '</td>';
      echo '<td class="px-4 py-2">' . htmlspecialchars($row['nome_exibicao']) . '</td>';
      $statusClass = ($row['status'] === 'conectado') ? 'status-conectado' : (($row['status'] === 'pendente') ? 'status-pendente' : '');
      echo '<td class="' . $statusClass . '" data-canal-id="' . $row['id'] . '">' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
      echo '<td class="px-4 py-2">' . ($row['data_conexao'] ? date('d/m/Y H:i', strtotime($row['data_conexao'])) : '-') . '</td>';
      echo '<td class="px-4 py-2">' . ($row['porta'] ? htmlspecialchars($row['porta']) : '-') . '</td>';
      echo '<td class="px-4 py-2">' . ($row['ultimo_envio'] ? date('d/m/Y H:i', strtotime($row['ultimo_envio'])) : '-') . '</td>';
      
      $acoes = '<a href="#" class="btn-ac btn-editar">Editar</a>';
      $botaoConectar = '';
      if ($row['status'] === 'pendente') {
        $botaoConectar = '<a href="#" class="btn-ac btn-conectar btn-conectar-canal" data-canal-id="' . $row['id'] . '" data-identificador="' . htmlspecialchars($row['identificador']) . '" data-porta="' . htmlspecialchars($row['porta']) . '">Conectar</a>';
      }
      if ($row['status'] === 'conectado') {
        $acoes .= ' <a href="#" class="btn-ac btn-desconectar btn-desconectar-canal" data-identificador="' . htmlspecialchars($row['identificador']) . '">Desconectar</a>';
      }
      $acoes .= ' <a href="#" class="btn-ac btn-excluir btn-excluir-canal" data-canal-id="' . $row['id'] . '">Excluir</a>';
      
      // Adiciona botão de personalização para o robô Financeiro
      if (strtolower($row['nome_exibicao']) === 'financeiro') {
        $acoes .= ' <a href="#" class="btn-ac btn-editar-mensagens" data-canal-id="' . $row['id'] . '">Personalizar Mensagens</a>';
        $acoes .= ' <a href="#" class="btn-ac btn-gerar-fila" data-canal-id="' . $row['id'] . '">Gerar Fila de Cobrança</a>';
      }
      
      echo '<td class="px-4 py-2">' . $acoes . '<div style="margin-top:8px;">' . $botaoConectar . '</div></td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="8" class="text-center text-gray-400 py-4">Nenhum canal cadastrado ainda.</td></tr>';
  }
  
  echo '</tbody></table></div>';
}

// ===== JAVASCRIPT CONSOLIDADO NO FINAL =====
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var modalQr = document.getElementById('modal-qr-canal');
  var closeQr = document.getElementById('close-modal-qr');
  var pollingInterval = null;
  var portaAtual = '3000';

  document.querySelectorAll('.btn-conectar-canal').forEach(function(btn) {
    btn.onclick = function(e) {
      e.preventDefault();
      portaAtual = btn.getAttribute('data-porta') || '3000';
      abrirModalQr(portaAtual);
    };
  });

  function abrirModalQr(porta) {
    modalQr.style.display = 'flex';
    document.getElementById('qr-code-area').innerHTML = 'Aguardando QR Code...';
    exibirQrCode(porta);
    pollingInterval = setInterval(function() {
      exibirQrCode(porta);
      checarStatus(porta);
    }, 5000);
    closeQr.onclick = function() {
      modalQr.style.display = 'none';
      clearInterval(pollingInterval);
    };
  }

  function exibirQrCode(porta) {
    fetch('http://localhost:' + porta + '/qr')
      .then(r => r.json())
      .then(resp => {
        if (resp.qr) {
          document.getElementById('qr-code-area').innerHTML = '';
          new QRCode(document.getElementById('qr-code-area'), {
            text: resp.qr,
            width: 220,
            height: 220
          });
        } else {
          document.getElementById('qr-code-area').innerHTML = 'QR Code não disponível. Aguarde...';
        }
      })
      .catch(() => {
        document.getElementById('qr-code-area').innerHTML = 'Erro ao buscar QR Code.';
      });
  }

  function checarStatus(porta) {
    fetch('http://localhost:' + porta + '/status')
      .then(r => r.json())
      .then(resp => {
        if (resp.ready) {
          modalQr.style.display = 'none';
          clearInterval(pollingInterval);
          alert('Robô conectado com sucesso!');
          location.reload();
        }
      });
  }
});
</script>
<?php
?> 