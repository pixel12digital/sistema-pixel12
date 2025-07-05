<?php
$page = 'chat.php';
$page_title = 'Chat Centralizado';
require_once 'config.php';
require_once 'db.php';
require_once 'components_cliente.php';
include 'template.php';

function render_content() {
  global $mysqli;

  // Buscar conversas (clientes com mensagens)
  $conversas = $mysqli->query("
    SELECT DISTINCT 
      c.id as cliente_id,
      c.nome as cliente_nome,
      c.email as cliente_email,
      c.celular as cliente_celular,
      COUNT(m.id) as total_mensagens,
      MAX(m.data_hora) as ultima_mensagem,
      MAX(m.mensagem) as ultima_mensagem_texto,
      SUM(CASE WHEN m.direcao = 'recebido' AND m.status != 'lido' THEN 1 ELSE 0 END) as nao_lidas,
      MAX(m.canal_id) as canal_id
    FROM clientes c
    LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
    WHERE m.id IS NOT NULL
    GROUP BY c.id, c.nome, c.email, c.celular
    ORDER BY ultima_mensagem DESC
  ");

  // Buscar canais conectados (para exibir nome do canal)
  $canais = [];
  $resCanais = $mysqli->query("SELECT id, nome_exibicao FROM canais_comunicacao");
  while ($row = $resCanais->fetch_assoc()) {
    $canais[$row['id']] = $row['nome_exibicao'];
  }

  // Buscar cliente selecionado e mensagens
  $cliente_selecionado = null;
  $mensagens = [];
  if (isset($_GET['cliente_id']) && $_GET['cliente_id']) {
    $cliente_id = intval($_GET['cliente_id']);
    $cliente_selecionado = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id")->fetch_assoc();
    if ($cliente_selecionado) {
      // Marcar mensagens como lidas
      $mysqli->query("UPDATE mensagens_comunicacao SET status = 'lido' WHERE cliente_id = $cliente_id AND direcao = 'recebido'");
      // Buscar mensagens
      $mensagens_result = $mysqli->query("
        SELECT m.*, c.nome_exibicao as canal_nome
        FROM mensagens_comunicacao m
        LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
        WHERE m.cliente_id = $cliente_id
        ORDER BY m.data_hora ASC
      ");
      while ($msg = $mensagens_result->fetch_assoc()) {
        $mensagens[] = $msg;
      }
    }
  }

  echo '<div class="flex bg-gray-50" style="overflow-x:hidden; height:calc(100vh - 72px);">';

  // Sidebar esquerda (280px)
  echo '<aside class="bg-white border-r border-gray-200 flex flex-col" style="min-width:280px;max-width:280px;height:calc(100vh - 72px);">';
  echo '<div class="p-4 border-b border-gray-200">';
  echo '<input type="text" id="buscaConversa" placeholder="Buscar..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 mb-3">';
  echo '<div class="mb-2 flex gap-2">';
  echo '<button id="btnFiltroAbertas" class="px-3 py-1 bg-purple-100 text-purple-700 rounded text-xs filtro-status filtro-ativo" data-status="aberta">Abertas</button>';
  echo '<button id="btnFiltroFechadas" class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-xs filtro-status" data-status="fechada">Fechadas</button>';
  echo '</div>';
  echo '</div>';
  echo '<div class="flex-1 overflow-y-auto" id="listaConversas">';
  if ($conversas && $conversas->num_rows > 0) {
    $conversas_array = [];
    while ($conversa = $conversas->fetch_assoc()) {
      $conversas_array[] = $conversa;
    }
    foreach ($conversas_array as $conversa) {
      $is_active = ($cliente_selecionado && $cliente_selecionado['id'] == $conversa['cliente_id']) ? 'bg-purple-50 border-purple-200' : 'hover:bg-gray-50';
      $nao_lidas_badge = $conversa['nao_lidas'] > 0 ? '<span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full mt-1">' . $conversa['nao_lidas'] . '</span>' : '';
      $canal_nome = isset($canais[$conversa['canal_id']]) ? $canais[$conversa['canal_id']] : 'Canal';
      $status_conversa = 'aberta'; // TODO: ajustar se houver campo de status no banco
      echo '<div class="conversa-item flex items-center gap-3 p-4 border-b border-gray-100 cursor-pointer ' . $is_active . '" data-nome="' . htmlspecialchars(strtolower($conversa['cliente_nome'])) . '" data-email="' . htmlspecialchars(strtolower($conversa['cliente_email'])) . '" data-celular="' . htmlspecialchars($conversa['cliente_celular']) . '" data-mensagem="' . htmlspecialchars(strtolower($conversa['ultima_mensagem_texto'])) . '" data-status="' . $status_conversa . '">
        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-lg font-bold text-gray-500">' . strtoupper(substr($conversa['cliente_nome'],0,1)) . '</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <span class="font-medium text-gray-900 text-sm truncate">' . htmlspecialchars($conversa['cliente_nome']) . '</span>
            <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">' . htmlspecialchars($canal_nome) . '</span>
          </div>
          <div class="text-xs text-gray-500 truncate">' . htmlspecialchars($conversa['ultima_mensagem_texto']) . '</div>
        </div>
        <div class="flex flex-col items-end">
          <span class="text-xs text-gray-400">' . ($conversa['ultima_mensagem'] ? date('H:i', strtotime($conversa['ultima_mensagem'])) : '') . '</span>
          ' . $nao_lidas_badge . '
        </div>
      </div>';
    }
  } else {
    echo '<div class="p-8 text-center text-gray-500">Nenhuma conversa encontrada</div>';
  }
  echo '</div>';
  echo '</aside>';

  // Coluna 2: Detalhes do cliente (altura ajustada)
  echo '<section class="flex flex-col bg-white" style="min-width:0;height:calc(100vh - 72px);padding-left:16px;padding-right:16px;max-width:900px;flex-shrink:0;">';
  echo '<div style="width:100%;padding:8px 0;box-sizing:border-box;">';
  if ($cliente_selecionado) {
    render_cliente_ficha($cliente_selecionado['id'], false);
  } else {
    echo '<div id="detalhes-cliente" style="height:100%;display:flex;align-items:center;justify-content:center;color:#888;font-size:1.2rem;">Selecione um chat para ver detalhes do cliente.</div>';
  }
  echo '</div>';
  echo '</section>';

  // Coluna 3: Chat (altura ajustada)
  echo '<section class="bg-white border-l border-gray-200 flex flex-col" style="min-width:260px;height:calc(100vh - 72px);flex:1 1 0%;">';
  if ($cliente_selecionado) {
    // Timeline de mensagens
    echo '<div class="flex-1 overflow-y-auto p-6 flex flex-col gap-4">';
    if (count($mensagens) > 0) {
      foreach ($mensagens as $msg) {
        $is_received = $msg['direcao'] === 'recebido';
        $align = $is_received ? 'items-start' : 'items-end';
        $bubble = $is_received ? 'bg-gray-100 text-gray-800' : 'bg-purple-500 text-white';
        $margin = $is_received ? 'mr-16' : 'ml-16';
        $status_icon = '';
        if (!$is_received) {
          if ($msg['status'] === 'lido') {
            $status_icon = '<span style="color:#4f46e5;font-size:1em;vertical-align:middle;">✔✔</span>';
          } elseif ($msg['status'] === 'entregue') {
            $status_icon = '<span style="color:#888;font-size:1em;vertical-align:middle;">✔✔</span>';
          } elseif ($msg['status'] === 'enviado') {
            $status_icon = '<span style="color:#888;font-size:1em;vertical-align:middle;">✔</span>';
          }
        }
        $conteudo = '';
        if (!empty($msg['anexo'])) {
          $ext = strtolower(pathinfo($msg['anexo'], PATHINFO_EXTENSION));
          if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
            $conteudo .= '<a href="' . htmlspecialchars($msg['anexo']) . '" target="_blank"><img src="' . htmlspecialchars($msg['anexo']) . '" alt="anexo" style="max-width:140px;max-height:90px;border-radius:8px;box-shadow:0 1px 4px #0001;margin-bottom:4px;"></a><br>';
          } else {
            $nome_arquivo = basename($msg['anexo']);
            $conteudo .= '<a href="' . htmlspecialchars($msg['anexo']) . '" target="_blank" style="color:#7c2ae8;text-decoration:underline;"><span style="color:#7c2ae8;">📎</span> ' . htmlspecialchars($nome_arquivo) . '</a><br>';
          }
        }
        $conteudo .= htmlspecialchars($msg['mensagem']);
        echo '<div class="flex ' . $align . '"><div class="max-w-xs ' . $bubble . ' rounded-lg px-4 py-2 shadow-sm ' . $margin . '">' . $conteudo . '<div class="text-xs mt-1 text-gray-400">' . date('H:i', strtotime($msg['data_hora'])) . ' ' . $status_icon . '</div></div></div>';
      }
    } else {
      echo '<div class="text-center text-gray-400">Nenhuma mensagem nesta conversa.</div>';
    }
    echo '</div>';
    // Formulário de envio de mensagem fixo no rodapé (ajustado: linha 1 campo mensagem amplo + botão, linha 2 upload)
    echo '<div class="bg-white border-t border-gray-200 p-4" style="flex-shrink:0;">';
    echo '<form class="flex flex-col gap-2" method="POST" action="chat_enviar.php" enctype="multipart/form-data">';
    echo '<input type="hidden" name="cliente_id" value="' . intval($cliente_selecionado['id']) . '">';
    echo '<div class="flex gap-2">';
    echo '<input type="text" name="mensagem" placeholder="Digite sua mensagem..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">';
    echo '<button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700" style="min-width:90px;">Enviar</button>';
    echo '</div>';
    echo '<input type="file" name="anexo" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt" class="px-2 py-2 border border-gray-300 rounded-lg text-sm">';
    echo '</form>';
    echo '</div>';
  } else {
    echo '<div style="flex:1;display:flex;align-items:center;justify-content:center;color:#888;">Nenhum cliente selecionado.</div>';
  }
  echo '</section>';

  echo '</div>';
}
?>

<!-- Modal Nova Conversa -->
<div id="modalNovaConversa" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="fecharModalNovaConversa()">&times;</span>
    <h3>Nova Conversa</h3>
    <input type="text" id="buscaClienteOuNumero" placeholder="Nome, e-mail ou número" style="width:100%;margin:12px 0;padding:8px;border-radius:6px;border:1px solid #ccc;">
    <div id="resultadosBusca" style="max-height:120px;overflow-y:auto;"></div>
    <button id="btnIniciarConversa" style="margin-top:10px;display:none;">Iniciar Conversa</button>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const buscaInput = document.getElementById('buscaConversa');
  const listaConversas = document.getElementById('listaConversas');
  let buscaTimeout = null;

  if (!buscaInput || !listaConversas) return;

  buscaInput.addEventListener('input', function() {
    const termo = this.value.trim();
    if (termo.length < 3) {
      // Restaurar lista original de conversas
      document.querySelectorAll('#listaConversas > .conversa-item').forEach(item => item.style.display = '');
      document.querySelectorAll('.busca-resultado').forEach(item => item.remove());
      return;
    }
    clearTimeout(buscaTimeout);
    buscaTimeout = setTimeout(() => {
      fetch('api/buscar_clientes.php?termo=' + encodeURIComponent(termo))
        .then(res => res.json())
        .then(clientes => {
          // Remove resultados anteriores da busca
          document.querySelectorAll('.busca-resultado').forEach(item => item.remove());
          if (clientes.length > 0) {
            clientes.forEach(cli => {
              let div = document.createElement('div');
              div.className = 'conversa-item busca-resultado hover:bg-gray-50 cursor-pointer flex items-center gap-3 p-4 border-b border-gray-100';
              div.innerHTML =
                `<div class='w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-lg font-bold text-gray-500'>${cli.nome.charAt(0).toUpperCase()}</div>` +
                `<div class='flex-1 min-w-0'><div class='font-medium text-gray-900 text-sm truncate'>${cli.nome}</div><div class='text-xs text-gray-500 truncate'>${cli.email ?? ''} ${cli.celular ?? ''}</div></div>`;
              div.onclick = function() {
                window.location.href = 'chat.php?cliente_id=' + cli.id;
              };
              listaConversas.insertAdjacentElement('afterbegin', div);
            });
          } else {
            let div = document.createElement('div');
            div.className = 'busca-resultado';
            div.innerHTML = '<div class="p-8 text-center text-gray-500">Nenhum cliente encontrado</div>';
            listaConversas.insertAdjacentElement('afterbegin', div);
          }
          // Esconde apenas os itens originais da lista
          document.querySelectorAll('#listaConversas > .conversa-item:not(.busca-resultado)').forEach(item => item.style.display = 'none');
        });
    }, 250);
  });
});

document.getElementById('btnNovaConversa').onclick = function() {
    document.getElementById('modalNovaConversa').style.display = 'flex';
    document.getElementById('buscaClienteOuNumero').value = '';
    document.getElementById('resultadosBusca').innerHTML = '';
    document.getElementById('btnIniciarConversa').style.display = 'none';
};
function fecharModalNovaConversa() {
    document.getElementById('modalNovaConversa').style.display = 'none';
}
let clienteSelecionado = null;
document.getElementById('buscaClienteOuNumero').oninput = function() {
    const termo = this.value.trim();
    clienteSelecionado = null;
    document.getElementById('btnIniciarConversa').style.display = 'none';
    if (termo.length < 3) {
        document.getElementById('resultadosBusca').innerHTML = '';
        return;
    }
    fetch('../api/buscar_clientes.php?termo=' + encodeURIComponent(termo))
        .then(res => res.json())
        .then(clientes => {
            let html = '';
            if (clientes.length > 0) {
                html += '<div style="font-size:0.95em;color:#888;margin-bottom:4px;">Clientes encontrados:</div>';
                clientes.forEach(function(cli) {
                    html += `<div style="padding:6px 0;cursor:pointer;" onclick="selecionarClienteNovaConversa(${cli.id}, '${cli.nome.replace(/'/g, "\\'")}', '${cli.celular.replace(/'/g, "\\'")}')"><b>${cli.nome}</b> <span style='color:#888;font-size:0.95em;'>${cli.celular}</span></div>`;
                });
            } else {
                html += '<div style="color:#888;">Nenhum cliente encontrado. Você pode iniciar conversa com este número.</div>';
            }
            document.getElementById('resultadosBusca').innerHTML = html;
            if (clientes.length === 0 && termo.match(/^\d{10,}$/)) {
                clienteSelecionado = {id: null, nome: termo, celular: termo};
                document.getElementById('btnIniciarConversa').style.display = 'inline-block';
            }
        });
};
window.selecionarClienteNovaConversa = function(id, nome, celular) {
    clienteSelecionado = {id, nome, celular};
    document.getElementById('btnIniciarConversa').style.display = 'inline-block';
};
document.getElementById('btnIniciarConversa').onclick = function() {
    if (!clienteSelecionado) return;
    window.location.href = 'chat.php?cliente_id=' + (clienteSelecionado.id ? clienteSelecionado.id : '') + '&numero=' + encodeURIComponent(clienteSelecionado.celular);
};

function fecharConversa() {
  alert('Funcionalidade de fechar conversa em breve!');
}
function transferirConversa() {
  alert('Funcionalidade de transferir conversa em breve!');
}
</script>
<?php 