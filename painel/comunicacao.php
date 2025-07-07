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
include 'template.php';
function render_content() {
  global $mysqli;
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
  echo '<div class="mb-4 flex justify-between items-center">';
  echo '<h2 class="text-lg font-semibold">Canais conectados</h2>';
  echo '<a href="#" id="btn-add-canal" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold" style="text-decoration:none;">+ Adicionar Canal</a>';
  echo '</div>';
  // Modal de adicionar canal
  echo '<div id="modal-add-canal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal">';
  echo '<button id="close-modal-canal" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Adicionar Canal WhatsApp</h3>';
  echo '<form method="post" id="form-add-canal">';
  echo '<input type="hidden" name="acao" value="add_canal">';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Número WhatsApp (com DDD e país)</label><input type="text" name="identificador" required class="border rounded px-3 py-2 w-full" placeholder="Ex: 5511999999999"></div>';
  echo '<div class="mb-3"><label class="block text-sm font-medium">Nome de Exibição</label><input type="text" name="nome_exibicao" required class="border rounded px-3 py-2 w-full" placeholder="Ex: Suporte 1"></div>';
  echo '<button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold w-full">Salvar Canal</button>';
  echo '</form>';
  echo '</div></div>';
  // Modal para exibir QR Code
  echo '<div id="modal-qr-canal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">';
  echo '<div class="modal">';
  echo '<button id="close-modal-qr" style="position:absolute;top:12px;right:16px;font-size:1.3rem;background:none;border:none;cursor:pointer;">&times;</button>';
  echo '<h3 class="text-lg font-bold mb-4">Conectar WhatsApp</h3>';
  echo '<div id="qr-code-area" class="flex flex-col items-center justify-center" style="min-height:180px;"></div>';
  echo '</div>';
  echo '</div>';
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
  $res = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status <> 'excluido' ORDER BY data_conexao DESC, id DESC");
  echo '<div class="overflow-x-auto"><table class="com-table">';
  echo '<thead class="bg-gray-100"><tr>';
  echo '<th class="px-4 py-2">Tipo</th>';
  echo '<th class="px-4 py-2">Identificador</th>';
  echo '<th class="px-4 py-2">Nome de Exibição</th>';
  echo '<th class="px-4 py-2">Status</th>';
  echo '<th class="px-4 py-2">Data de Conexão</th>';
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
      $acoes = '<a href="#" class="btn-ac btn-editar">Editar</a>';
      if ($row['status'] === 'pendente') {
        $acoes .= ' <a href="#" class="btn-ac btn-conectar btn-conectar-canal" data-canal-id="' . $row['id'] . '" data-identificador="' . htmlspecialchars($row['identificador']) . '">Conectar</a>';
      }
      if ($row['status'] === 'conectado') {
        $acoes .= ' <a href="#" class="btn-ac btn-desconectar btn-desconectar-canal" data-identificador="' . htmlspecialchars($row['identificador']) . '">Desconectar</a>';
      }
      $acoes .= ' <a href="#" class="btn-ac btn-excluir btn-excluir-canal" data-canal-id="' . $row['id'] . '">Excluir</a>';
      echo '<td class="px-4 py-2">' . $acoes . '</td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="6" class="text-center text-gray-400 py-4">Nenhum canal cadastrado ainda.</td></tr>';
  }
  echo '</tbody></table></div>';
  if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['acao']) && $_POST['acao'] === 'add_canal'
  ) {
    $identificador = $mysqli->real_escape_string(trim($_POST['identificador']));
    $nome_exibicao = $mysqli->real_escape_string(trim($_POST['nome_exibicao']));
    $tipo = 'whatsapp';
    $status = 'pendente';
    $data_conexao = null;
    $mysqli->query("INSERT INTO canais_comunicacao (tipo, identificador, nome_exibicao, status, data_conexao) VALUES ('$tipo', '$identificador', '$nome_exibicao', '$status', NULL)");
    $canal_id = $mysqli->insert_id;
    // Chama o backend para iniciar sessão
    echo '<script>
      fetch("https://api.pixel12digital.com.br:8443/api/start-session", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ identificador: "' . $identificador . '" })
      }).then(() => {
        location.href = location.pathname + "?qr_identificador=' . urlencode($identificador) . '";
      });
    </script>';
    exit;
  }
  // JS para abrir/fechar modal
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
  document.getElementById('btn-add-canal').onclick = function(e) {
    e.preventDefault();
    document.getElementById('modal-add-canal').style.display = 'flex';
  };
  document.getElementById('close-modal-canal').onclick = function() {
    document.getElementById('modal-add-canal').style.display = 'none';
  };
  window.onclick = function(event) {
    var modal = document.getElementById('modal-add-canal');
    if (event.target === modal) modal.style.display = 'none';
    var modalQr = document.getElementById('modal-qr-canal');
    if (event.target === modalQr) modalQr.style.display = 'none';
    var modalExcluir = document.getElementById('modal-confirm-excluir');
    if (event.target === modalExcluir) modalExcluir.style.display = 'none';
  };
  document.getElementById('close-modal-qr').onclick = function() {
    document.getElementById('modal-qr-canal').style.display = 'none';
  };

  // IMPLEMENTAÇÃO DO FLUXO DO BOTÃO CONECTAR
  document.querySelectorAll('.btn-conectar-canal').forEach(function(btn) {
    btn.onclick = function(e) {
      e.preventDefault();
      const identificador = this.getAttribute('data-identificador');
      // Inicia a sessão no backend
      fetch('https://api.pixel12digital.com.br:8443/api/start-session', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ identificador })
      }).then(() => {
        // Abre o modal de QR Code e busca o QR
        document.getElementById('modal-qr-canal').style.display = 'flex';
        document.getElementById('qr-code-area').innerHTML = '<span style="color:#888;">Gerando QR Code...</span>';
        // Polling a cada 40 segundos
        let pollingInterval;
        function fetchQr() {
          fetch('https://api.pixel12digital.com.br:8443/api/qr?identificador=' + encodeURIComponent(identificador))
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
                // Se não há QR, verifica status do canal
                fetch('api/whatsapp_status.php?identificador=' + encodeURIComponent(identificador))
                  .then(r => r.json())
                  .then(statusResp => {
                    if (statusResp.status === 'conectado') {
                      document.getElementById('modal-qr-canal').style.display = 'none';
                      clearInterval(pollingInterval);
                    } else {
                      document.getElementById('qr-code-area').innerHTML = '<span style="color:red;">QR Code não disponível. Aguarde alguns segundos e tente novamente.</span>';
                    }
                  });
              }
            })
            .catch(() => {
              document.getElementById('qr-code-area').innerHTML = '<span style="color:red;">Erro ao conectar ao backend.</span>';
            });
        }
        fetchQr();
        pollingInterval = setInterval(fetchQr, 40000); // 40 segundos
        // Limpa o polling ao fechar o modal
        document.getElementById('close-modal-qr').onclick = function() {
          document.getElementById('modal-qr-canal').style.display = 'none';
          clearInterval(pollingInterval);
        };
      });
    };
  });

  // No JS, ao carregar a página, busca o QR Code do canal correto
  (function() {
    const urlParams = new URLSearchParams(window.location.search);
    const canalIdentificadorNovo = urlParams.get('qr_identificador');
    if (canalIdentificadorNovo) {
      document.getElementById('modal-qr-canal').style.display = 'flex';
      document.getElementById('qr-code-area').innerHTML = '<span style="color:#888;">Gerando QR Code...</span>';
      fetch('https://api.pixel12digital.com.br:8443/api/qr?identificador=' + encodeURIComponent(canalIdentificadorNovo))
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
            document.getElementById('qr-code-area').innerHTML = '<span style="color:red;">QR Code não disponível. Aguarde alguns segundos e tente novamente.</span>';
          }
        })
        .catch(() => {
          document.getElementById('qr-code-area').innerHTML = '<span style="color:red;">Erro ao conectar ao backend.</span>';
        });
    }
  })();
  // JS para exclusão de canal
  document.querySelectorAll('.btn-excluir-canal').forEach(function(btn) {
    btn.onclick = function(e) {
      e.preventDefault();
      document.getElementById('input-canal-id-excluir').value = this.getAttribute('data-canal-id');
      document.getElementById('modal-confirm-excluir').style.display = 'flex';
    };
  });
  document.getElementById('close-modal-excluir').onclick = function() {
    document.getElementById('modal-confirm-excluir').style.display = 'none';
  };
  // JS para desconectar canal WhatsApp
  document.querySelectorAll('.btn-desconectar-canal').forEach(function(btn) {
    btn.onclick = function(e) {
      e.preventDefault();
      const identificador = this.getAttribute('data-identificador');
      if (confirm('Deseja realmente desconectar este canal?')) {
        fetch('https://api.pixel12digital.com.br:8443/api/logout', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ identificador })
        }).then(r => r.json()).then(resp => {
          if (resp.success) {
            location.reload();
          } else {
            alert('Erro ao desconectar: ' + resp.error);
          }
        });
      }
    };
  });
  </script>
  <?php
} 