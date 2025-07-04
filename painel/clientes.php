<?php
$page = 'clientes.php';
// Exemplo de gerenciamento de clientes (simples)
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: index.php');
    exit;
}
// Exemplo de clientes em array (depois será banco)
$clientes = [
    ['nome' => 'Cliente 1', 'banco' => 'cliente1_db'],
    ['nome' => 'Cliente 2', 'banco' => 'cliente2_db'],
];
// Cards de resumo (exemplo)
$totalClientes = count($clientes);
$totalBancos = count(array_unique(array_column($clientes, 'banco')));
require_once 'config.php';
require_once 'db.php';
// Paginação real
$por_pagina = 15;
$pagina = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($pagina - 1) * $por_pagina;

// Buscar total de clientes
$total_clientes_res = $mysqli->query("SELECT COUNT(*) as total FROM clientes");
$total_clientes = $total_clientes_res->fetch_assoc()['total'];
$total_paginas = max(1, ceil($total_clientes / $por_pagina));

// Buscar clientes da página atual
$result = $mysqli->query("SELECT * FROM clientes ORDER BY data_criacao DESC LIMIT $por_pagina OFFSET $offset");
$page_title = 'Clientes';
$custom_header = '
  <input type="text" class="invoices-search-bar w-full px-3 py-2 rounded-md text-gray-800" placeholder="Buscar por nome, e-mail ou CPF/CNPJ" id="buscaCliente" style="max-width:300px;">
  <button class="bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md ml-2" id="btnNovoCliente">+ Novo Cliente</button>
';
function render_content() {
    global $result, $pagina, $total_paginas;
    ?>
    <!-- CSS CUSTOM CLIENTES ATIVO -->
    <style>
    /* Nunca use bg-white em <tr> ou <td> da tabela de clientes! */
    #listaClientes > tr:nth-child(even), 
    #listaClientes > tr:nth-child(even) > td, 
    #listaClientes tr:nth-child(even), 
    #listaClientes tr:nth-child(even) td {
      background: rgba(236,236,236,0.8) !important;
    }
    #listaClientes > tr:nth-child(odd), 
    #listaClientes > tr:nth-child(odd) > td, 
    #listaClientes tr:nth-child(odd), 
    #listaClientes tr:nth-child(odd) td {
      background: #fff !important;
    }
    #listaClientes tr:hover, #listaClientes tr:hover td {
      background: #f3e8ff !important;
    }
    .acao-icones { display: flex; gap: 8px; align-items: center; justify-content: center; }
    .acao-icones a svg {
      width: 20px;
      height: 20px;
      vertical-align: middle;
      display: inline-block;
      transition: transform 0.15s;
    }
    .acao-icones a:hover svg { transform: scale(1.15); }
    .table-zebra tbody tr:nth-child(even) td,
    #listaClientes tr:nth-child(even) td {
      background: #d1b3ff !important;
    }
    .table-zebra tbody tr:nth-child(odd) td,
    #listaClientes tr:nth-child(odd) td {
      background: #fff !important;
    }
    #listaClientes tr:hover td {
      background: #f3e8ff !important;
    }
    </style>
    <div class="bg-white rounded-lg shadow-sm p-4">
      <table class="w-full text-sm whitespace-nowrap table-zebra">
        <thead>
          <tr>
            <th class="px-3 py-2">Nome</th>
            <th class="px-3 py-2 text-center">E-mail</th>
            <th class="px-3 py-2 text-center">Celular</th>
            <th class="px-3 py-2">Plano Ativo</th>
            <th class="px-3 py-2">Ações</th>
          </tr>
        </thead>
        <tbody id="listaClientes">
          <?php $i = 0; while ($cli = $result->fetch_assoc()): $i++; ?>
          <?php $style = 'vertical-align:middle;padding:8px 6px;border-bottom:1px solid #ececec;'; ?>
          <tr>
            <td style="<?= $style ?>"><?= htmlspecialchars($cli['nome']) ?></td>
            <td style="<?= $style ?>"><?= htmlspecialchars($cli['email']) ?></td>
            <td style="<?= $style ?>"><?= htmlspecialchars($cli['celular'] ?? $cli['telefone']) ?></td>
            <td style="<?= $style ?>">
              <?php if (!empty($cli['plano'])): ?>
                <span class="font-semibold text-purple-700"><?= htmlspecialchars($cli['plano']) ?></span>
                <span class="ml-2 px-2 py-1 rounded text-xs <?= strtolower($cli['status']) === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' ?>">
                  <?= htmlspecialchars($cli['status']) ?>
                </span>
              <?php else: ?>
                <span class="text-gray-400">-</span>
              <?php endif; ?>
            </td>
            <td style="<?= $style ?>">
              <div style="display:flex;gap:8px;align-items:center;justify-content:center;">
                <a href="cliente_detalhes.php?id=<?= $cli['id'] ?>" title="Visualizar">
                  <svg style="width:20px;height:20px;vertical-align:middle;display:inline-block;transition:transform 0.15s;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#a259e6" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="mailto:<?= htmlspecialchars($cli['email']) ?>" title="Enviar e-mail">
                  <svg style="width:20px;height:20px;vertical-align:middle;display:inline-block;transition:transform 0.15s;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3,7 12,13 21,7"/></svg>
                </a>
                <?php $cel = preg_replace('/\D/', '', $cli['celular'] ?? $cli['telefone']); ?>
                <?php if ($cel): ?>
                  <a href="https://wa.me/55<?= $cel ?>" target="_blank" title="WhatsApp">
                    <svg style="width:20px;height:20px;vertical-align:middle;display:inline-block;transition:transform 0.15s;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2">
                      <circle cx="12" cy="12" r="10" stroke="#22c55e" stroke-width="2" fill="none"/>
                      <path d="M16.511 13.942c-.228-.114-1.348-.665-1.557-.74-.209-.076-.362-.114-.515.114-.152.228-.59.74-.724.892-.133.152-.266.171-.494.057-.228-.114-.962-.354-1.833-1.13-.677-.604-1.135-1.35-1.27-1.578-.133-.228-.014-.351.1-.465.103-.102.228-.266.342-.399.114-.133.152-.228.228-.38.076-.152.038-.285-.019-.399-.057-.114-.515-1.242-.706-1.7-.186-.447-.376-.386-.515-.393-.133-.007-.285-.009-.437-.009-.152 0-.399.057-.609.285-.209.228-.8.782-.8 1.904 0 1.122.818 2.207.931 2.36.114.152 1.61 2.457 3.905 3.35.546.188.971.3 1.303.384.547.138 1.045.119 1.438.072.439-.052 1.348-.551 1.539-1.084.19-.533.19-.99.133-1.084-.057-.095-.209-.152-.437-.266z" fill="#22c55e"/>
                    </svg>
                  </a>
                <?php endif; ?>
                <a href="#" class="btn-whatsapp" 
                   data-cliente-id="<?= $cli['id'] ?>" 
                   data-cliente-nome="<?= htmlspecialchars($cli['nome']) ?>"
                   data-cliente-celular="<?= htmlspecialchars($cli['celular']) ?>"
                   title="Conversar via WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <!-- Modal de visualização/edição -->
    <div id="modalCliente" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;">
      <div style="background:#fff; border-radius:8px; max-width:480px; width:90vw; padding:32px; position:relative;">
        <button id="fecharModalCliente" style="position:absolute; top:8px; right:12px; font-size:1.5rem; background:none; border:none; color:#a259e6; cursor:pointer;">&times;</button>
        <div id="modalClienteConteudo">Carregando...</div>
      </div>
    </div>
    <script src="modal_cliente.js"></script>
    <script>
    document.getElementById('buscaCliente').addEventListener('input', function() {
        let termo = this.value.toLowerCase();
        document.querySelectorAll('#listaClientes tr').forEach(tr => {
            let texto = tr.innerText.toLowerCase();
            tr.style.display = texto.includes(termo) ? '' : 'none';
        });
    });
    function abrirModalCliente(id, modo) {
        document.getElementById('modalCliente').style.display = 'flex';
        document.getElementById('modalClienteConteudo').innerHTML = 'Carregando...';
        fetch('cliente_busca.php?id=' + id)
            .then(r => r.json())
            .then(cli => {
                let html = '<h2 style="color:#6b21a8;">' + (modo === 'editar' ? 'Editar Cliente' : 'Dados do Cliente') + '</h2>';
                html += '<div style="margin-top:16px;">';
                for (const [k, v] of Object.entries(cli)) {
                    html += '<div style="margin-bottom:8px;"><b>' + k + ':</b> ' + (v || '-') + '</div>';
                }
                html += '</div>';
                if (modo === 'editar') {
                    html += '<button style="margin-top:16px; background:#a259e6; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-weight:500; cursor:pointer;" onclick="alert(\'Funcionalidade de edição em breve!\')">Salvar</button>';
                }
                document.getElementById('modalClienteConteudo').innerHTML = html;
            });
    }
    document.getElementById('fecharModalCliente').onclick = function() {
        document.getElementById('modalCliente').style.display = 'none';
    };
    document.getElementById('btnNovoCliente').onclick = function() {
        window.location.href = 'cliente_novo.php';
    };
    window.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        var el = document.getElementById('listaClientes');
        if (el) {
          el.style.display = 'none';
          void el.offsetHeight;
          el.style.display = '';
        }
      }, 100);
    });
    </script>
    <?php
    // Paginação real
    $max_links = 3;
    $start = max(1, $pagina - $max_links);
    $end = min($total_paginas, $pagina + $max_links);
    ?>
    <div class="flex gap-2 justify-center my-4">
      <?php if ($pagina > 1): ?>
        <a href="?page=1" class="px-2 py-1 bg-gray-200 rounded">Primeira</a>
        <a href="?page=<?= $pagina-1 ?>" class="px-2 py-1 bg-gray-200 rounded">Anterior</a>
      <?php endif; ?>
      <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?page=<?= $i ?>" class="px-2 py-1 rounded <?= $i == $pagina ? 'bg-purple-600 text-white font-bold' : 'bg-gray-200' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <span class="px-2 py-1">Página <?= $pagina ?> de <?= $total_paginas ?></span>
      <?php if ($pagina < $total_paginas): ?>
        <a href="?page=<?= $pagina+1 ?>" class="px-2 py-1 bg-gray-200 rounded">Próxima</a>
        <a href="?page=<?= $total_paginas ?>" class="px-2 py-1 bg-gray-200 rounded">Última</a>
      <?php endif; ?>
    </div>
    <?php
}
include 'template.php'; 
?>
<!-- CSS CUSTOM CLIENTES ATIVO (FINAL DO BODY) -->
<style>
#listaClientes tr:nth-child(even) td {
  background: rgba(0,0,0,0.07) !important;
  color: #232836 !important;
}
#listaClientes tr:nth-child(odd) td {
  background: #fff !important;
  color: #232836 !important;
}
#listaClientes tr:hover td {
  background: #f3e8ff !important;
  color: #7c2ae8 !important;
}
.acao-icones { display: flex; gap: 8px; align-items: center; justify-content: center; }
.acao-icones a svg {
  width: 20px;
  height: 20px;
  vertical-align: middle;
  display: inline-block;
  transition: transform 0.15s;
}
.acao-icones a:hover svg { transform: scale(1.15); }
</style>
<div id="modalChat" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="fecharModalChat()">&times;</span>
    <h3 id="modalClienteNome"></h3>
    <div id="modalClienteCelular" style="font-size:0.9em;color:#888;"></div>
    <div id="canalSelectorArea" style="margin:10px 0;">
      <label for="selectCanalWhatsapp">Escolha o número para enviar:</label>
      <select id="selectCanalWhatsapp"></select>
    </div>
    <div id="chatArea" style="display:none;">
      <div id="chatMensagens" style="height:200px;overflow-y:auto;background:#f9f9f9;padding:10px;margin-bottom:10px;border-radius:5px;"></div>
      <textarea id="chatMensagem" placeholder="Digite sua mensagem" style="width:100%;height:60px;"></textarea>
      <button id="btnEnviarMensagem" style="margin-top:5px;">Enviar</button>
    </div>
  </div>
</div>
<style>
.modal { position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:9999;}
.modal-content { background:#fff; padding:20px; border-radius:8px; min-width:350px; max-width:95vw; position:relative;}
.close { position:absolute; top:10px; right:15px; font-size:22px; cursor:pointer;}
</style>
<script>
document.querySelectorAll('.btn-whatsapp').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        abrirModalChat(
            this.getAttribute('data-cliente-id'),
            this.getAttribute('data-cliente-nome'),
            this.getAttribute('data-cliente-celular')
        );
    });
});

function abrirModalChat(clienteId, clienteNome, clienteCelular) {
    document.getElementById('modalChat').style.display = 'flex';
    document.getElementById('modalClienteNome').textContent = clienteNome;
    document.getElementById('modalClienteCelular').textContent = 'Celular: ' + clienteCelular;
    document.getElementById('chatArea').style.display = 'none';
    document.getElementById('chatMensagens').innerHTML = '';
    document.getElementById('chatMensagem').value = '';

    // Buscar canais WhatsApp
    fetch('api/listar_canais_whatsapp.php')
        .then(res => res.json())
        .then(canais => {
            const select = document.getElementById('selectCanalWhatsapp');
            select.innerHTML = '';
            canais.forEach(canal => {
                const opt = document.createElement('option');
                opt.value = canal.id;
                opt.textContent = canal.nome_exibicao + ' (' + canal.identificador + ')';
                select.appendChild(opt);
            });
            document.getElementById('canalSelectorArea').style.display = canais.length > 1 ? 'block' : 'none';
            if (canais.length > 0) {
                document.getElementById('chatArea').style.display = 'block';
                carregarHistorico(clienteId, canais[0].id);
            }
            select.onchange = () => {
                carregarHistorico(clienteId, select.value);
            };
        });

    document.getElementById('btnEnviarMensagem').onclick = function() {
        const canalId = document.getElementById('selectCanalWhatsapp').value;
        const mensagem = document.getElementById('chatMensagem').value.trim();
        if (!mensagem) return;
        fetch('api/enviar_mensagem.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                canal_id: canalId,
                cliente_id: clienteId,
                mensagem: mensagem
            })
        }).then(res => res.json()).then(resp => {
            if(resp.success) {
                document.getElementById('chatMensagem').value = '';
                carregarHistorico(clienteId, canalId);
            } else {
                alert('Erro: ' + resp.error);
            }
        });
    };
}

function carregarHistorico(clienteId, canalId) {
    fetch(`api/historico_mensagens.php?cliente_id=${clienteId}&canal_id=${canalId}`)
        .then(res => res.json())
        .then(msgs => {
            const area = document.getElementById('chatMensagens');
            area.innerHTML = '';
            msgs.forEach(msg => {
                const div = document.createElement('div');
                div.textContent = `[${msg.data_hora}] ${msg.direcao === 'enviado' ? 'Você' : 'Cliente'}: ${msg.mensagem}`;
                div.style.marginBottom = '4px';
                div.style.color = msg.direcao === 'enviado' ? '#2e7d32' : '#333';
                area.appendChild(div);
            });
            area.scrollTop = area.scrollHeight;
        });
}

function fecharModalChat() {
    document.getElementById('modalChat').style.display = 'none';
}
</script> 