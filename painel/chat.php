<?php
require_once 'config.php';
require_once 'db.php';
require_once 'cache_manager.php'; // Sistema de cache centralizado

$page = 'chat.php';
$page_title = 'Chat Centralizado';
$custom_header = '';

function render_content() {
  global $mysqli;
  
  // Buscar conversas recentes usando cache
  $conversas = cache_conversas($mysqli);
  
  // Cliente selecionado
  $cliente_selecionado = null;
  $mensagens = [];
  
  if (isset($_GET['cliente_id']) && $_GET['cliente_id']) {
    $cliente_id = intval($_GET['cliente_id']);
    
    // Usar cache para dados do cliente
    $cliente_selecionado = cache_cliente($cliente_id, $mysqli);
    
    if ($cliente_selecionado) {
      // Marcar mensagens como lidas (não fazer cache desta operação)
      $mysqli->query("UPDATE mensagens_comunicacao SET status = 'lido' WHERE cliente_id = $cliente_id AND direcao = 'recebido'");
      
      // Cache para mensagens do cliente
      $mensagens = cache_remember("mensagens_{$cliente_id}", function() use ($cliente_id, $mysqli) {
        $sql = "SELECT m.*, c.nome_exibicao as canal_nome
                FROM mensagens_comunicacao m
                LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
                WHERE m.cliente_id = ?
                ORDER BY m.data_hora ASC";
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensagens = [];
        while ($msg = $result->fetch_assoc()) {
          $mensagens[] = $msg;
        }
        $stmt->close();
        
        return $mensagens;
      }, 60); // Cache de 1 minuto para mensagens
      
      // Invalidar cache de mensagens quando houver nova mensagem
      if (isset($_POST['nova_mensagem'])) {
        cache_forget("mensagens_{$cliente_id}");
      }
    }
  }
  
  ?>
  
  <link rel="stylesheet" href="assets/chat-modern.css">
  <script src="assets/chat-functions.js"></script>
  
  <div class="chat-container-3cols">
    <!-- Coluna 1: Chat Centralizado - Lista de conversas -->
    <div class="chat-conversations-column">
      <div class="chat-sidebar-header">
        <h1 class="chat-sidebar-title">💬 Chat Centralizado</h1>
        <div class="chat-search">
          <input type="text" id="buscaConversa" placeholder="Buscar por número de telefone..." oninput="filtrarConversasPorNumero(this.value)">
          <button class="clear-search" onclick="limparFiltroConversa()" title="Limpar busca">✕</button>
        </div>
        <div class="chat-tabs">
          <button class="chat-tab active" onclick="filtrarConversas('abertas')">Abertas</button>
          <button class="chat-tab" onclick="filtrarConversas('fechadas')">Fechadas</button>
          <button class="chat-tab chat-tab-unread" onclick="filtrarConversas('nao-lidas')" id="tabNaoLidas">
            <span class="unread-indicator">●</span>
            Não Lidas
            <span class="unread-count" id="contadorNaoLidas">0</span>
          </button>
        </div>
        <button class="chat-action-btn" onclick="abrirNovaConversa()" style="width: 100%; margin-top: 0.5rem;">
          ➕ Nova Conversa
        </button>
        
        <!-- Status do Robô -->
        <div class="robot-status-container" style="margin-top: 1rem; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color);">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="font-weight: 600; font-size: 0.9rem;">🤖 Robô WhatsApp</span>
            <div class="robot-status-indicator" id="robotStatus" style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></div>
          </div>
          <div class="robot-status-text" id="robotStatusText" style="font-size: 0.8rem; color: var(--text-secondary);">Verificando...</div>
          <button class="robot-connect-btn" id="robotConnectBtn" onclick="gerenciarRobo()" style="width: 100%; margin-top: 0.5rem; padding: 0.5rem; border: 1px solid var(--border-color); background: var(--background-white); border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
            Conectar
          </button>
        </div>
      </div>
      
      <div class="chat-conversations" id="listaConversas">
        <?php foreach ($conversas as $conv): ?>
          <?php
          // Verificar se há mensagens não lidas para esta conversa
          $nao_lidas = cache_remember("conv_nao_lidas_{$conv['cliente_id']}", function() use ($conv, $mysqli) {
            $sql = "SELECT COUNT(*) as total FROM mensagens_comunicacao 
                    WHERE cliente_id = ? AND direcao = 'recebido' AND status != 'lido'";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $conv['cliente_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return intval($row['total']);
          }, 30); // Cache de 30 segundos
          ?>
          <div class="conversation-item <?= ($cliente_selecionado && $cliente_selecionado['id'] == $conv['cliente_id']) ? 'active' : '' ?> <?= ($nao_lidas > 0) ? 'has-unread' : '' ?>" 
               data-cliente-id="<?= $conv['cliente_id'] ?>"
               onclick="return carregarCliente(<?= $conv['cliente_id'] ?>, '<?= htmlspecialchars($conv['nome'], ENT_QUOTES) ?>', event);">
            <div class="conversation-avatar">
              <?= strtoupper(substr($conv['nome'], 0, 1)) ?>
            </div>
            <div class="conversation-content">
              <div class="conversation-header">
                <span class="conversation-name"><?= htmlspecialchars($conv['nome']) ?></span>
                <span class="conversation-time"><?= $conv['ultima_data'] ? date('H:i', strtotime($conv['ultima_data'])) : '' ?></span>
              </div>
              <div class="conversation-meta">
                <span class="conversation-tag"><?= htmlspecialchars($conv['canal_nome'] ?? 'Canal') ?></span>
                <span class="conversation-preview">
                  <?php if ($nao_lidas > 0): ?>
                    <?= $nao_lidas ?> nova<?= $nao_lidas > 1 ? 's' : '' ?> mensagem<?= $nao_lidas > 1 ? 's' : '' ?>
                  <?php else: ?>
                    <?= htmlspecialchars(substr($conv['ultima_mensagem'] ?? '', 0, 50)) ?>
                  <?php endif; ?>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Divisor redimensionável 1 -->
      <div class="resize-handle resize-handle-1" data-resize="1"></div>
    </div>
    
    <!-- Coluna 2: Detalhes do Cliente -->
    <div class="client-details-column">
      <?php if ($cliente_selecionado): ?>
        <div class="client-details-header">
          <h2>👤 Detalhes do Cliente</h2>
          </div>
        <div class="client-details-full">
          <iframe src="api/detalhes_cliente.php?cliente_id=<?= $cliente_selecionado['id'] ?>" 
                  frameborder="0" 
                  style="width: 100%; height: calc(100vh - 130px); border: none;">
          </iframe>
          </div>
      <?php else: ?>
        <div class="client-details-empty">
          <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
            <h3>Selecione um cliente</h3>
            <p>Escolha uma conversa para ver os detalhes do cliente</p>
          </div>
        </div>
      <?php endif; ?>
      
      <!-- Divisor redimensionável 2 -->
      <div class="resize-handle resize-handle-2" data-resize="2"></div>
    </div>
    
    <!-- Coluna 3: Conversas/Histórico + Campo de envio -->
    <div class="chat-messages-column">
      <?php if ($cliente_selecionado): ?>
        <div class="chat-messages-header">
          <h2>💬 Conversa com <?= htmlspecialchars($cliente_selecionado['nome']) ?></h2>
        </div>
        
        <!-- Área de mensagens -->
        <div class="chat-messages" id="chat-messages">
          <?php foreach ($mensagens as $msg): ?>
            <div class="message <?= $msg['direcao'] === 'recebido' ? 'received' : 'sent' ?> <?= ($msg['direcao'] === 'recebido' && $msg['status'] !== 'lido') ? 'unread' : '' ?>">
              <div class="message-bubble">
                <?php if (!empty($msg['anexo'])): ?>
                  <?php
                  $anexo = $msg['anexo'];
                  if (strpos($anexo, '|api_response:') !== false) {
                    $anexo = explode('|api_response:', $anexo)[0];
                  }
                  if (strpos($anexo, '|api_id:') !== false) {
                    $anexo = explode('|api_id:', $anexo)[0];
                  }
                  if (!empty($anexo) && !preg_match('/^[a-zA-Z0-9\/\-_\.]+$/', $anexo)):
                    $ext = strtolower(pathinfo($anexo, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])): ?>
                      <a href="<?= htmlspecialchars($anexo) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($anexo) ?>" alt="anexo" style="max-width:200px;max-height:150px;border-radius:8px;margin-bottom:8px;">
                      </a>
                    <?php else: ?>
                      <a href="<?= htmlspecialchars($anexo) ?>" target="_blank" style="color:inherit;text-decoration:underline;">
                        📎 <?= htmlspecialchars(basename($anexo)) ?>
                      </a><br>
                    <?php endif; ?>
                  <?php endif; ?>
                <?php endif; ?>
                <?= htmlspecialchars($msg['mensagem']) ?>
                <div class="message-time">
                  <?= date('H:i', strtotime($msg['data_hora'])) ?>
                  <?php if ($msg['direcao'] === 'enviado'): ?>
                    <span class="message-status">
                      <?php if ($msg['status'] === 'lido'): ?>
                        ✔✔
                      <?php elseif ($msg['status'] === 'entregue'): ?>
                        ✔✔
                      <?php else: ?>
                        ✔
                      <?php endif; ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Área de input -->
        <div class="chat-input-area">
          <form id="form-chat-enviar" enctype="multipart/form-data">
            <input type="hidden" name="cliente_id" value="<?= $cliente_selecionado['id'] ?>">
            <input type="hidden" name="canal_id" value="36">
            
            <!-- Primeira linha: Campo de digitação + Anexo -->
            <div class="chat-input-container">
              <div class="chat-input-wrapper">
                <textarea 
                  name="mensagem" 
                  class="chat-input" 
                  placeholder="Digite sua mensagem..."
                  rows="1"
                ></textarea>
              </div>
              <label class="chat-attachment" for="anexo" title="Anexar arquivo">
                📎
                <input type="file" id="anexo" name="anexo" style="display: none;" accept="image/*,.pdf,.doc,.docx,.txt">
              </label>
            </div>
              
            <!-- Segunda linha: Botão Enviar -->
              <button type="submit" class="chat-send-btn">
                Enviar
                <span>➤</span>
              </button>
          </form>
        </div>
      <?php else: ?>
        <!-- Estado vazio -->
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; color: var(--text-secondary);">
          <div style="font-size: 4rem; margin-bottom: 1rem;">💬</div>
          <h2 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">Selecione uma conversa</h2>
          <p style="margin: 0; text-align: center;">Escolha uma conversa da lista ao lado para começar a conversar</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Indicador de redimensionamento -->
    <div class="resize-indicator" id="resize-indicator"></div>
  </div>
  
  <script>
  // Funções JavaScript limpas
  function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
  }
  
  function filtrarConversas(tipo) {
    // Remover classe active de todas as tabs
    document.querySelectorAll('.chat-tab').forEach(tab => tab.classList.remove('active'));
    
    // Adicionar classe active na tab clicada
    const targetTab = document.querySelector(`[onclick="filtrarConversas('${tipo}')"]`);
    if (targetTab) {
      targetTab.classList.add('active');
    }
    
    const itensConversa = document.querySelectorAll('.conversation-item');
    
    if (tipo === 'nao-lidas') {
      // Filtro especial para mensagens não lidas
      filtrarConversasNaoLidas();
    } else if (tipo === 'fechadas') {
      // Mostrar apenas conversas fechadas (implementar se necessário)
      itensConversa.forEach(item => {
        // Por enquanto, esconder todas para fechadas
        item.style.display = 'none';
      });
    } else {
      // Mostrar todas as conversas (abertas)
      itensConversa.forEach(item => {
        item.style.display = 'flex';
        item.classList.remove('filtered-out');
      });
    }
  }
  
  /**
   * Filtrar apenas conversas com mensagens não lidas
   */
  function filtrarConversasNaoLidas() {
    const container = document.querySelector('.chat-conversations');
    
    // Mostrar loading
    container.innerHTML = `
      <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
        Carregando conversas não lidas...
      </div>
    `;
    
    fetch('api/conversas_nao_lidas.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.conversas.length > 0) {
            // Renderizar conversas não lidas
            let html = '';
            data.conversas.forEach(conv => {
              const iniciais = conv.nome.charAt(0).toUpperCase();
              const dataFormatada = new Date(conv.ultima_nao_lida).toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit'
              });
              
              html += `
                <div class="conversation-item has-unread" 
                     data-cliente-id="${conv.cliente_id}"
                     onclick="carregarCliente(${conv.cliente_id}, '${conv.nome.replace(/'/g, "\\'")}', event);">
                  <div class="conversation-avatar">${iniciais}</div>
                  <div class="conversation-content">
                    <div class="conversation-header">
                      <span class="conversation-name">${conv.nome}</span>
                      <span class="conversation-time">${dataFormatada}</span>
                    </div>
                    <div class="conversation-meta">
                      <span class="conversation-tag">${conv.canal_nome || 'Canal'}</span>
                      <span class="conversation-preview">${conv.total_nao_lidas} mensagem${conv.total_nao_lidas > 1 ? 's' : ''} não lida${conv.total_nao_lidas > 1 ? 's' : ''}</span>
                    </div>
                  </div>
                </div>
              `;
            });
            
            container.innerHTML = html;
          } else {
            // Nenhuma conversa não lida
            container.innerHTML = `
              <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 1rem;">✅</div>
                <p style="margin: 0; font-weight: 500;">Parabéns!</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                  Todas as mensagens foram lidas
                </p>
              </div>
            `;
          }
          
          // Atualizar contador global
          atualizarContadorNaoLidas(data.total_global);
        } else {
          container.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--error-color);">
              <p>Erro ao carregar conversas não lidas</p>
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Erro ao buscar conversas não lidas:', error);
        container.innerHTML = `
          <div style="text-align: center; padding: 2rem; color: var(--error-color);">
            <p>Erro de conexão</p>
          </div>
        `;
      });
  }
  
  /**
   * Atualizar contador de mensagens não lidas
   */
  function atualizarContadorNaoLidas(total) {
    const contador = document.getElementById('contadorNaoLidas');
    const tabNaoLidas = document.getElementById('tabNaoLidas');
    
    if (contador) {
      contador.textContent = total > 0 ? total : '';
      contador.setAttribute('data-count', total);
    }
    
    // Adicionar/remover indicador visual na tab
    if (tabNaoLidas) {
      if (total > 0) {
        tabNaoLidas.classList.add('has-unread-messages');
      } else {
        tabNaoLidas.classList.remove('has-unread-messages');
      }
    }
  }
  
  /**
   * Marcar conversa atual como lida e atualizar contador
   */
  function marcarConversaComoLida(clienteId) {
    fetch('api/marcar_como_lida.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `cliente_id=${clienteId}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Atualizar contador global
        verificarTotalNaoLidas();
        
        // Remover indicador visual da conversa atual
        const conversaAtual = document.querySelector(`[data-cliente-id="${clienteId}"]`);
        if (conversaAtual) {
          conversaAtual.classList.remove('has-unread');
        }
      }
    })
    .catch(error => {
      console.error('Erro ao marcar como lida:', error);
    });
  }
  
  /**
   * Verificar total de mensagens não lidas globalmente
   */
  function verificarTotalNaoLidas() {
    fetch('api/conversas_nao_lidas.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          atualizarContadorNaoLidas(data.total_global);
        }
      })
      .catch(error => {
        console.error('Erro ao verificar total não lidas:', error);
      });
  }
  
  function abrirNovaConversa() {
    // Criar modal unificado para todas as opções de conversa
    const modalHtml = `
      <div id="modal-nova-conversa" style="
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.5); display: flex; align-items: center; 
        justify-content: center; z-index: 1000;
      ">
        <div style="
          background: white; border-radius: 12px; padding: 2rem; 
          width: 95%; max-width: 800px; max-height: 85vh; overflow: hidden;
          box-shadow: 0 20px 40px rgba(0,0,0,0.3); display: flex; flex-direction: column;
        ">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0; color: var(--primary-color);">💬 Selecionar Conversa</h2>
            <button onclick="fecharModalNovaConversa()" style="
              background: none; border: none; font-size: 1.5rem; cursor: pointer;
              color: var(--text-muted); padding: 0; width: 30px; height: 30px;
            ">×</button>
          </div>
          
          <div style="margin-bottom: 1rem;">
            <input type="text" id="busca-cliente-modal" placeholder="Buscar por nome, telefone ou email..." style="
              width: 100%; padding: 0.75rem; border: 1px solid var(--border-color);
              border-radius: 8px; font-size: 0.9rem; box-sizing: border-box;
            ">
          </div>
          
          <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
            <button class="filtro-cliente active" data-filtro="ativas" onclick="filtrarClientesModal('ativas')">
              🟢 Conversas Ativas
            </button>
            <button class="filtro-cliente" data-filtro="sem-conversa" onclick="filtrarClientesModal('sem-conversa')">
              🆕 Nunca Contactados
            </button>
            <button class="filtro-cliente" data-filtro="inativo" onclick="filtrarClientesModal('inativo')">
              ⏰ Inativos (30+ dias)
            </button>
            <button class="filtro-cliente" data-filtro="todos" onclick="filtrarClientesModal('todos')">
              📋 Todos os Clientes
            </button>
          </div>
          
          <div id="lista-clientes-modal" style="
            flex: 1; overflow-y: auto; border: 1px solid var(--border-color);
            border-radius: 8px; padding: 0.5rem; min-height: 300px;
          ">
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
              <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
              Carregando conversas ativas...
            </div>
          </div>
          
          <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
            <button onclick="fecharModalNovaConversa()" style="
              padding: 0.75rem 1.5rem; border: 1px solid var(--border-color);
              background: white; border-radius: 8px; cursor: pointer;
            ">Cancelar</button>
          </div>
        </div>
      </div>
    `;
    
    // Adicionar estilos para o modal
    const estiloModal = `
      <style>
        .filtro-cliente {
          padding: 0.5rem 1rem; border: 1px solid var(--border-color);
          background: white; border-radius: 6px; cursor: pointer;
          font-size: 0.85rem; transition: all 0.2s;
        }
        .filtro-cliente.active {
          background: var(--primary-color); color: white; border-color: var(--primary-color);
        }
        .item-cliente-modal {
          padding: 0.75rem; border-bottom: 1px solid #f1f5f9;
          cursor: pointer; transition: background 0.2s; display: flex;
          align-items: center; gap: 0.75rem;
        }
        .item-cliente-modal:hover {
          background: var(--background-light);
        }
        .item-cliente-modal:last-child {
          border-bottom: none;
        }
        .avatar-cliente-modal {
          width: 36px; height: 36px; border-radius: 50%;
          background: var(--primary-color); color: white;
          display: flex; align-items: center; justify-content: center;
          font-weight: 600; font-size: 0.9rem; flex-shrink: 0;
        }
        .info-cliente-modal {
          flex: 1; min-width: 0;
        }
        .nome-cliente-modal {
          font-weight: 500; color: var(--text-primary);
          white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .meta-cliente-modal {
          font-size: 0.8rem; color: var(--text-secondary);
          margin-top: 0.2rem;
        }
        .status-cliente-modal {
          font-size: 0.75rem; padding: 0.2rem 0.5rem;
          border-radius: 4px; font-weight: 500; white-space: nowrap;
        }
        .status-ativa { background: #d1fae5; color: #065f46; }
        .status-sem-conversa { background: #fef3c7; color: #92400e; }
        .status-inativo { background: #fecaca; color: #991b1b; }
        .status-normal { background: #e0e7ff; color: #3730a3; }
        .tempo-conversa {
          font-size: 0.75rem; color: var(--text-muted);
          margin-left: 0.5rem;
        }
      </style>
    `;
    
    // Inserir modal no DOM
    document.body.insertAdjacentHTML('beforeend', estiloModal + modalHtml);
    
    // Carregar conversas ativas por padrão
    carregarClientesModal('ativas');
    
    // Event listener para busca
    document.getElementById('busca-cliente-modal').addEventListener('input', function() {
      const termo = this.value.toLowerCase();
      const itens = document.querySelectorAll('.item-cliente-modal');
      itens.forEach(item => {
        const texto = item.textContent.toLowerCase();
        item.style.display = texto.includes(termo) ? 'flex' : 'none';
      });
    });
  }
  
  function fecharModalNovaConversa() {
    const modal = document.getElementById('modal-nova-conversa');
    if (modal) {
      modal.remove();
    }
  }
  
  function filtrarClientesModal(filtro) {
    // Atualizar botões ativos
    document.querySelectorAll('.filtro-cliente').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.filtro === filtro);
    });
    
    // Recarregar lista com filtro
    carregarClientesModal(filtro);
  }
  
  function carregarClientesModal(filtro = 'ativas') {
    const container = document.getElementById('lista-clientes-modal');
    
    let loadingText = 'Carregando...';
    switch(filtro) {
      case 'ativas': loadingText = 'Carregando conversas ativas...'; break;
      case 'sem-conversa': loadingText = 'Carregando clientes nunca contactados...'; break;
      case 'inativo': loadingText = 'Carregando clientes inativos...'; break;
      case 'todos': loadingText = 'Carregando todos os clientes...'; break;
    }
    
    container.innerHTML = `
      <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
        ${loadingText}
      </div>
    `;
    
    if (filtro === 'ativas') {
      // Carregar conversas ativas da lista atual
      carregarConversasAtivas();
    } else {
      // Carregar outros filtros via API
      fetch('api/buscar_clientes_nova_conversa.php?filtro=' + filtro)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.clientes.length > 0) {
            let html = '';
            data.clientes.forEach(cliente => {
              let statusClass = 'status-normal';
              let statusTexto = 'Cliente';
              
              if (filtro === 'sem-conversa') {
                statusClass = 'status-sem-conversa';
                statusTexto = 'Nunca contactado';
              } else if (filtro === 'inativo') {
                statusClass = 'status-inativo';
                statusTexto = cliente.dias_inativo + ' dias inativo';
              } else if (cliente.ultima_conversa) {
                if (cliente.dias_inativo > 30) {
                  statusClass = 'status-inativo';
                  statusTexto = cliente.dias_inativo + ' dias inativo';
                } else {
                  statusClass = 'status-normal';
                  statusTexto = 'Última: ' + new Date(cliente.ultima_conversa).toLocaleDateString();
                }
              } else {
                statusClass = 'status-sem-conversa';
                statusTexto = 'Sem conversa';
              }
              
              html += `
                <div class="item-cliente-modal" onclick="iniciarConversaComCliente(${cliente.id}, '${cliente.nome.replace(/'/g, "\\'")}')">
                  <div class="avatar-cliente-modal">
                    ${cliente.nome.charAt(0).toUpperCase()}
                  </div>
                  <div class="info-cliente-modal">
                    <div class="nome-cliente-modal">${cliente.nome}</div>
                    <div class="meta-cliente-modal">
                      ${cliente.celular || cliente.telefone || 'Sem telefone'} 
                      ${cliente.email ? '• ' + cliente.email : ''}
                    </div>
                  </div>
                  <div class="status-cliente-modal ${statusClass}">
                    ${statusTexto}
                  </div>
                </div>
              `;
            });
            container.innerHTML = html;
          } else {
            mostrarListaVazia(filtro);
          }
        })
        .catch(error => {
          console.error('Erro ao carregar clientes:', error);
          container.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--error-color);">
              <p>Erro ao carregar clientes</p>
            </div>
          `;
        });
    }
  }
  
  function carregarConversasAtivas() {
    const container = document.getElementById('lista-clientes-modal');
    const conversasItems = document.querySelectorAll('.conversation-item');
    
    if (conversasItems.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
          <div style="font-size: 2rem; margin-bottom: 1rem;">💬</div>
          <p style="margin: 0;">Nenhuma conversa ativa</p>
          <p style="font-size: 0.9rem; margin: 0.5rem 0 0 0;">
            Use os outros filtros para encontrar clientes
          </p>
        </div>
      `;
      return;
    }
    
    let html = '';
    conversasItems.forEach(item => {
      const clienteId = item.dataset.clienteId;
      const nomeElement = item.querySelector('.conversation-name');
      const timeElement = item.querySelector('.conversation-time');
      const previewElement = item.querySelector('.conversation-preview');
      const tagElement = item.querySelector('.conversation-tag');
      
      const nome = nomeElement ? nomeElement.textContent : 'Cliente';
      const tempo = timeElement ? timeElement.textContent : '';
      const preview = previewElement ? previewElement.textContent : '';
      const canal = tagElement ? tagElement.textContent : '';
      
      html += `
        <div class="item-cliente-modal" onclick="iniciarConversaComCliente(${clienteId}, '${nome.replace(/'/g, "\\'")}')">
          <div class="avatar-cliente-modal">
            ${nome.charAt(0).toUpperCase()}
          </div>
          <div class="info-cliente-modal">
            <div class="nome-cliente-modal">${nome}</div>
            <div class="meta-cliente-modal">
              ${canal} • ${preview}
            </div>
          </div>
          <div class="status-cliente-modal status-ativa">
            Ativa
            ${tempo ? '<span class="tempo-conversa">' + tempo + '</span>' : ''}
          </div>
        </div>
      `;
    });
    
    container.innerHTML = html;
  }
  
  function mostrarListaVazia(filtro) {
    const container = document.getElementById('lista-clientes-modal');
    let titulo = '';
    let descricao = '';
    
    switch(filtro) {
      case 'sem-conversa':
        titulo = 'Nenhum cliente nunca contactado';
        descricao = 'Todos os clientes já possuem histórico de conversas';
        break;
      case 'inativo':
        titulo = 'Nenhum cliente inativo';
        descricao = 'Todos os clientes estão com conversas recentes';
        break;
      case 'todos':
        titulo = 'Nenhum cliente encontrado';
        descricao = 'Tente ajustar os filtros de busca';
        break;
      default:
        titulo = 'Lista vazia';
        descricao = 'Nenhum item encontrado';
    }
    
    container.innerHTML = `
      <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <div style="font-size: 2rem; margin-bottom: 1rem;">👥</div>
        <p style="margin: 0; font-weight: 500;">${titulo}</p>
        <p style="font-size: 0.9rem; margin: 0.5rem 0 0 0;">${descricao}</p>
      </div>
    `;
  }
  
  function iniciarConversaComCliente(clienteId, nomeCliente) {
    fecharModalNovaConversa();
    
    // Verificar se o cliente já está selecionado
    const clienteAtual = new URLSearchParams(window.location.search).get('cliente_id');
    if (clienteAtual == clienteId) {
      // Cliente já está carregado, apenas focar no input
      const inputMensagem = document.querySelector('textarea[name="mensagem"]');
      if (inputMensagem) {
        inputMensagem.focus();
        inputMensagem.placeholder = "Continue sua conversa com " + nomeCliente + "...";
      }
      return;
    }
    
    // Verificar se cliente está na lista de conversas ativas
    const itemConversa = document.querySelector('[data-cliente-id="' + clienteId + '"]');
    if (itemConversa) {
      // Cliente tem conversa ativa - carregar conversa existente
      console.log('Carregando conversa existente com ' + nomeCliente);
      carregarCliente(clienteId, nomeCliente);
      
      // Feedback visual temporário
      setTimeout(() => {
        const inputMensagem = document.querySelector('textarea[name="mensagem"]');
        if (inputMensagem) {
          inputMensagem.placeholder = "Continue sua conversa com " + nomeCliente + "...";
          inputMensagem.focus();
        }
      }, 1000);
    } else {
      // Cliente não tem conversa ativa - iniciar nova conversa
      console.log('Iniciando nova conversa com ' + nomeCliente);
      carregarCliente(clienteId, nomeCliente);
      
      // Feedback para nova conversa
      setTimeout(() => {
        const inputMensagem = document.querySelector('textarea[name="mensagem"]');
        if (inputMensagem) {
          inputMensagem.placeholder = "Inicie uma conversa com " + nomeCliente + "...";
          inputMensagem.focus();
        }
      }, 1000);
    }
  }
  
  function mostrarDetalhesCliente(clienteId) {
    window.open('cliente_detalhes.php?id=' + clienteId, '_blank');
  }
  
  function fecharConversa() {
    window.location.href = 'chat.php';
  }
  
  // Polling para atualização automática - OTIMIZADO
  let pollingInterval = null;
  let lastMessageTimestamp = 0;
  let cachedConversations = new Map();
  let cacheTimeout = 300000; // 5 minutos de cache
  let lastCacheUpdate = 0;
  
  function startChatPolling(clienteId) {
    if (pollingInterval) clearInterval(pollingInterval);
    
    // Primeira verificação imediata
    checkForNewMessages(clienteId);
    
    // Polling mais conservador - apenas para cliente ativo
    pollingInterval = setInterval(() => {
      // Só verificar se a janela está ativa
      if (document.visibilityState === 'visible') {
        checkForNewMessages(clienteId);
      }
    }, 30000); // Reduzido de 15s para 30s
    
    // Listener para quando a página volta ao foco
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible' && clienteId) {
        setTimeout(() => checkForNewMessages(clienteId), 1000);
      }
    });
  }
  
  function checkForNewMessages(clienteId) {
    // Cache local para evitar requests desnecessários
    const cacheKey = `messages_${clienteId}`;
    const now = Date.now();
    
    if (cachedConversations.has(cacheKey)) {
      const cached = cachedConversations.get(cacheKey);
      if (now - cached.timestamp < 15000) { // 15 segundos de cache
        return; // Não fazer request se já verificou recentemente
      }
    }
    
    fetch('api/check_new_messages.php?cliente_id=' + clienteId + '&last_timestamp=' + lastMessageTimestamp)
      .then(res => res.json())
      .then(data => {
        // Atualizar cache
        cachedConversations.set(cacheKey, {
          timestamp: now,
          data: data
        });
        
        if (data.has_new_messages) {
          lastMessageTimestamp = data.latest_timestamp || now;
          loadFullChatHistory(clienteId);
        }
      })
      .catch(() => {
        // Em caso de erro, só tentar novamente após 1 minuto
        setTimeout(() => {
          if (document.visibilityState === 'visible') {
            loadFullChatHistory(clienteId);
          }
        }, 60000);
      });
  }
  
  function loadFullChatHistory(clienteId) {
    // Throttle para evitar multiple requests simultâneos
    if (window.loadingHistory) return;
    window.loadingHistory = true;
    
    fetch('api/historico_mensagens.php?cliente_id=' + clienteId)
      .then(res => res.text())
      .then(html => {
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
          console.log('🔄 Atualizando histórico de mensagens...');
          chatMessages.innerHTML = html;
          lastMessageTimestamp = Date.now();
          
          // Configurar auto-scroll novamente após atualização
          setupAutoScroll();
          
          // Forçar scroll para última mensagem com delay menor
          setTimeout(() => {
            scrollToBottom(true);
            console.log('✅ Histórico atualizado e scroll aplicado');
          }, 200);
        }
        bindChatFormAjax();
      })
      .catch(err => {
        console.error('Erro ao carregar mensagens:', err);
      })
      .finally(() => {
        window.loadingHistory = false;
      });
  }
  
  function scrollToBottom(smooth = false) {
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
      if (smooth) {
        chatMessages.scrollTo({
          top: chatMessages.scrollHeight,
          behavior: 'smooth'
        });
      } else {
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }
      console.log('📜 Auto-scroll aplicado - altura:', chatMessages.scrollHeight);
    }
  }

  // Observer para detectar novas mensagens e fazer scroll automático
  let messageObserver = null;

  function setupAutoScroll() {
    const chatMessages = document.getElementById('chat-messages');
    if (!chatMessages) {
      console.warn('⚠️ Elemento #chat-messages não encontrado para auto-scroll');
      return;
    }

    // Remover observer anterior se existir
    if (messageObserver) {
      messageObserver.disconnect();
    }

    // Criar novo observer para detectar mudanças no conteúdo
    messageObserver = new MutationObserver(function(mutations) {
      let shouldScroll = false;
      
      mutations.forEach(function(mutation) {
        // Verificar qualquer mudança no container
        if (mutation.type === 'childList') {
          shouldScroll = true;
          console.log('🔄 Mudança detectada no chat - fazendo scroll automático');
        }
        
        // Verificar mudanças específicas em mensagens
        if (mutation.addedNodes.length > 0) {
          for (let node of mutation.addedNodes) {
            if (node.nodeType === Node.ELEMENT_NODE) {
              shouldScroll = true;
              console.log('🆕 Novo elemento detectado - fazendo scroll automático');
              break;
            }
          }
        }
      });

      if (shouldScroll) {
        // Scroll imediato para mudanças críticas
        scrollToBottom(false);
        
        // Scroll suave adicional após pequeno delay
        setTimeout(() => {
          scrollToBottom(true);
        }, 100);
      }
    });

    // Observar mudanças no container de mensagens com configuração ampla
    messageObserver.observe(chatMessages, {
      childList: true,
      subtree: true,
      characterData: true,
      attributes: true
    });

    console.log('🔄 Auto-scroll configurado para #chat-messages');
    
    // Scroll inicial imediato
    setTimeout(() => {
      scrollToBottom(false);
    }, 50);
  }

  // Função para forçar scroll quando necessário
  function forceScrollToBottom() {
    setTimeout(() => {
      scrollToBottom(true);
    }, 200);
  }

  // Interceptar mudanças no innerHTML do chat-messages
  function interceptInnerHTMLChanges() {
    const chatMessages = document.getElementById('chat-messages');
    if (!chatMessages) return;

    // Criar proxy para interceptar mudanças no innerHTML
    const originalInnerHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML');
    
    Object.defineProperty(chatMessages, 'innerHTML', {
      set: function(value) {
        originalInnerHTML.set.call(this, value);
        console.log('📝 innerHTML alterado - forçando scroll automático');
        setTimeout(() => {
          scrollToBottom(false);
          setTimeout(() => scrollToBottom(true), 100);
        }, 50);
      },
      get: function() {
        return originalInnerHTML.get.call(this);
      }
    });

    console.log('🎯 Interceptação innerHTML configurada');
  }
  
  // Inicializar
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - inicializando chat');
    
    // Verificar elementos principais
    const conversationsColumn = document.querySelector('.chat-conversations-column');
    const detailsColumn = document.querySelector('.client-details-column');
    const messagesColumn = document.querySelector('.chat-messages-column');
    
    console.log('Elementos encontrados:');
    console.log('- Conversations:', conversationsColumn);
    console.log('- Details:', detailsColumn);
    console.log('- Messages:', messagesColumn);
    
    bindChatFormAjax();
    scrollToBottom();
    
    // Configurar auto-scroll se já houver mensagens carregadas
    setupAutoScroll();
    
    // Configurar interceptação de mudanças innerHTML
    interceptInnerHTMLChanges();
    
    // Verificação periódica para garantir scroll automático (como WhatsApp) - OTIMIZADO
    let scrollCheckInterval = setInterval(() => {
      // Só verificar se a janela está ativa e há mensagens
      if (document.visibilityState === 'visible') {
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages && chatMessages.children.length > 0) {
          // Verificar se não está no final e fazer scroll suave se necessário
          const isAtBottom = (chatMessages.scrollTop + chatMessages.clientHeight) >= (chatMessages.scrollHeight - 10);
          if (!isAtBottom) {
            console.log('🔍 Verificação automática: fazendo scroll para última mensagem');
            scrollToBottom(true);
          }
        }
      }
    }, 10000); // Aumentado de 3s para 10s
    
    // Limpar interval quando sair da página
    window.addEventListener('beforeunload', () => {
      if (scrollCheckInterval) {
        clearInterval(scrollCheckInterval);
      }
      if (pollingInterval) {
        clearInterval(pollingInterval);
      }
    });
    
    // Inicializar o redimensionador
    window.columnResizer = new ColumnResizer();
    
    // Inicializar verificação do status do robô - OTIMIZADA
    verificarStatusRobo();
    
    // Verificação menos frequente e inteligente
    let robotCheckInterval = setInterval(() => {
      // Só verificar se a página está visível
      if (document.visibilityState === 'visible') {
        verificarStatusRobo();
      }
    }, 120000); // Aumentado de 30s para 2 minutos
    
    // Verificar quando a página volta ao foco
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible') {
        setTimeout(verificarStatusRobo, 2000);
      }
    });
    
    // Limpar interval do robô quando sair da página
    window.addEventListener('beforeunload', () => {
      if (robotCheckInterval) {
        clearInterval(robotCheckInterval);
      }
    });
    
    // Debug: Verificar se o chat-messages existe após carregamento
    setTimeout(() => {
      const chatMessages = document.getElementById('chat-messages');
      console.log('🔍 Verificação final - chat-messages encontrado:', chatMessages);
      if (chatMessages) {
        console.log('📊 Altura do container:', chatMessages.scrollHeight);
        console.log('📏 Scroll atual:', chatMessages.scrollTop);
        forceScrollToBottom();
      }
    }, 2000);
    
    // Iniciar polling se houver cliente selecionado
    const urlParams = new URLSearchParams(window.location.search);
    const clienteId = urlParams.get('cliente_id');
    console.log('Cliente ID da URL:', clienteId);
    if (clienteId) {
      startChatPolling(clienteId);
    }
    
    // Event listeners para scroll automático
    window.addEventListener('resize', () => {
      setTimeout(() => {
        forceScrollToBottom();
        console.log('📐 Redimensionamento: scroll aplicado');
      }, 100);
    });
    
    window.addEventListener('focus', () => {
      setTimeout(() => {
        forceScrollToBottom();
        console.log('👁️ Foco na janela: scroll aplicado');
      }, 100);
    });
    
    // Scroll ao clicar no campo de mensagem
    document.addEventListener('click', (e) => {
      if (e.target.matches('textarea[name="mensagem"]')) {
        setTimeout(() => {
          forceScrollToBottom();
        }, 100);
      }
    });
    
    // Verificar mensagens não lidas no carregamento inicial
    verificarTotalNaoLidas();
    
    // Verificação periódica de mensagens não lidas
    setInterval(() => {
      if (document.visibilityState === 'visible') {
        verificarTotalNaoLidas();
      }
    }, 60000); // A cada 1 minuto
    
    // Debug: Verificar se o chat-messages existe após carregamento
    setTimeout(() => {
      const chatMessages = document.getElementById('chat-messages');
      console.log('🔍 Verificação final - chat-messages encontrado:', chatMessages);
      if (chatMessages) {
        console.log('📊 Altura do container:', chatMessages.scrollHeight);
        console.log('📏 Scroll atual:', chatMessages.scrollTop);
        forceScrollToBottom();
      }
    }, 2000);
  });

  // Funções do Robô WhatsApp - OTIMIZADAS
  let robotStatus = {
    connected: false,
    port: 3000,
    lastCheck: null,
    checking: false
  };
  
  function verificarStatusRobo() {
    // Evitar múltiplas verificações simultâneas
    if (robotStatus.checking) return;
    
    // Cache de 60 segundos para status do robô
    if (robotStatus.lastCheck && (Date.now() - robotStatus.lastCheck < 60000)) {
      return;
    }
    
    robotStatus.checking = true;
    
    // CORREÇÃO: Usar proxy PHP ao invés de chamada direta ao VPS
    fetch('ajax_whatsapp.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'action=status'
    })
      .then(response => response.json())
      .then(data => {
        // CORREÇÃO: Usar a mesma lógica do frontend principal
        let isConnected = false;
        
        // 1. Verificar campo ready
        if (data.ready === true) {
          isConnected = true;
        }
        
        // 2. Verificar status direto
        if (data.status && ['connected', 'already_connected', 'authenticated', 'ready'].includes(data.status)) {
          isConnected = true;
        }
        
        // 3. Verificar raw_response_preview (mesma lógica do frontend)
        if (data.debug && data.debug.raw_response_preview) {
          try {
            const parsedResponse = JSON.parse(data.debug.raw_response_preview);
            const realStatus = parsedResponse.status?.status || parsedResponse.status;
            if (['connected', 'already_connected', 'authenticated', 'ready'].includes(realStatus)) {
              isConnected = true;
            }
          } catch (e) {
            // Ignorar erro de parse
          }
        }
        
        updateRobotUI(isConnected, data.number, null);
        robotStatus.connected = isConnected;
        robotStatus.lastCheck = Date.now();
        
        console.log('[Chat] Status do robô verificado:', isConnected ? 'CONECTADO' : 'DESCONECTADO');
      })
      .catch(error => {
        console.error('[Chat] Erro ao verificar status do robô:', error);
        updateRobotUI(false, null, 'Erro de conexão');
        robotStatus.connected = false;
        robotStatus.lastCheck = Date.now();
      })
      .finally(() => {
        robotStatus.checking = false;
      });
  }
  
  function updateRobotUI(connected, number, error) {
    const statusIndicator = document.getElementById('robotStatus');
    const statusText = document.getElementById('robotStatusText');
    const connectBtn = document.getElementById('robotConnectBtn');
    
    if (connected) {
      statusIndicator.style.background = '#22c55e';
      statusText.textContent = number ? `Conectado: ${number}` : 'Conectado';
      connectBtn.textContent = 'Desconectar';
      connectBtn.style.background = '#fee2e2';
      connectBtn.style.color = '#b91c1c';
    } else {
      statusIndicator.style.background = '#ef4444';
      statusText.textContent = error || 'Desconectado';
      connectBtn.textContent = 'Conectar';
      connectBtn.style.background = '#dcfce7';
      connectBtn.style.color = '#166534';
    }
  }
  
  function gerenciarRobo() {
    if (robotStatus.connected) {
      // Desconectar robô usando proxy PHP
      fetch('ajax_whatsapp.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=logout'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            updateRobotUI(false, null, 'Desconectado pelo usuário');
            robotStatus.connected = false;
            console.log('[Chat] Robô desconectado com sucesso');
          } else {
            alert('Erro ao desconectar robô: ' + (data.error || 'Erro desconhecido'));
          }
        })
        .catch(error => {
          console.error('[Chat] Erro ao desconectar robô:', error);
          alert('Erro ao desconectar robô: ' + error.message);
        });
    } else {
      // Abrir painel de comunicação para conectar
      window.open('/loja-virtual-revenda/painel/comunicacao.php', '_blank');
    }
  }
  
  // Função para enviar mensagem via robô
  async function enviarViaRobo(numero, mensagem) {
    if (!robotStatus.connected) {
      throw new Error('Robô não está conectado');
    }
    
    try {
      // Usar proxy PHP para enviar mensagem
      const response = await fetch('ajax_whatsapp.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=send&to=${encodeURIComponent(numero)}&message=${encodeURIComponent(mensagem)}`
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.error || 'Falha no envio via robô');
      }
      
      console.log('[Chat] Mensagem enviada via robô:', data);
      return data;
    } catch (error) {
      console.error('[Chat] Erro ao enviar via robô:', error);
      throw error;
    }
  }
  
  // Função para carregar cliente dinamicamente - OTIMIZADA
  let currentClientId = null;
  let clientDataCache = new Map();
  
  function carregarCliente(clienteId, nomeCliente, event) {
    // Prevenir qualquer comportamento padrão ou propagação
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    
    // Se já é o cliente atual, apenas focar no input
    if (currentClientId === clienteId) {
      const inputMensagem = document.querySelector('textarea[name="mensagem"]');
      if (inputMensagem) {
        inputMensagem.focus();
      }
      return false;
    }
    
    console.log('Carregando cliente:', clienteId, nomeCliente);
    
    // Atualizar cliente atual
    currentClientId = clienteId;
    
    // Atualizar URL sem recarregar página
    const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?cliente_id=" + clienteId;
    window.history.pushState({path: newUrl}, '', newUrl);
    
    // Atualizar título da página
    document.title = "Chat com " + nomeCliente + " - Chat Centralizado";
    
    // Remover classe active de todos os itens
    document.querySelectorAll('.conversation-item').forEach(item => {
      item.classList.remove('active');
    });
    
    // Adicionar classe active ao item clicado
    const targetItem = document.querySelector('[data-cliente-id="' + clienteId + '"]');
    if (targetItem) {
      targetItem.classList.add('active');
    }
    
    // Verificar cache antes de fazer requests
    const cacheKey = `client_${clienteId}`;
    const cachedData = clientDataCache.get(cacheKey);
    const now = Date.now();
    
    if (cachedData && (now - cachedData.timestamp < 30000)) { // Cache de 30 segundos
      console.log('📋 Usando dados do cache para cliente', clienteId);
      updateClientInterface(cachedData.detalhesHtml, cachedData.mensagensHtml, clienteId);
      return false;
    }
    
    // Mostrar loading nas colunas
    mostrarLoading();
    
    // Carregar dados do cliente
    Promise.all([
      fetch('api/detalhes_cliente.php?cliente_id=' + clienteId),
      fetch('api/mensagens_cliente.php?cliente_id=' + clienteId)
    ])
    .then(responses => {
      console.log('Responses recebidas:', responses);
      return Promise.all(responses.map(r => r.text()));
    })
    .then(([detalhesHtml, mensagensHtml]) => {
      console.log('Dados carregados com sucesso');
      
      // Salvar no cache
      clientDataCache.set(cacheKey, {
        timestamp: now,
        detalhesHtml: detalhesHtml,
        mensagensHtml: mensagensHtml
      });
      
      // Limpar cache antigo (manter apenas últimos 3 clientes)
      if (clientDataCache.size > 3) {
        const oldestKey = clientDataCache.keys().next().value;
        clientDataCache.delete(oldestKey);
      }
      
      updateClientInterface(detalhesHtml, mensagensHtml, clienteId);
    })
    .catch(error => {
      console.error('Erro ao carregar cliente:', error);
      esconderLoading();
      alert('Erro ao carregar dados do cliente. Tente novamente.');
    });
    
    // Retornar false para prevenir qualquer ação padrão
    return false;
  }
  
  function updateClientInterface(detalhesHtml, mensagensHtml, clienteId) {
    // Atualizar coluna de detalhes do cliente
    const detailsColumn = document.querySelector('.client-details-column');
    detailsColumn.innerHTML = `
      <div class="client-details-header">
        <h2>👤 Detalhes do Cliente</h2>
      </div>
      <div class="client-details-full">
        <iframe src="api/detalhes_cliente.php?cliente_id=${clienteId}" 
                frameborder="0" 
                style="width: 100%; height: calc(100vh - 130px); border: none;">
        </iframe>
      </div>
    `;
    
    // Atualizar coluna de mensagens
    const messagesColumn = document.querySelector('.chat-messages-column');
    
    if (messagesColumn) {
      messagesColumn.innerHTML = mensagensHtml;
      console.log('✅ Chat carregado com sucesso');
      
      // Marcar mensagens como lidas automaticamente
      marcarConversaComoLida(clienteId);
      
      // Configurar auto-scroll para novas mensagens
      setupAutoScroll();
      
      // Scroll adicional com delay para garantir renderização completa
      setTimeout(() => {
        forceScrollToBottom();
        console.log('🎯 Scroll inicial aplicado para conversa carregada');
      }, 300);
    } else {
      console.error('Elemento .chat-messages-column não encontrado!');
    }
    
    // Reconfigurar o formulário de chat
    bindChatFormAjax();
    
    // Esconder loading
    esconderLoading();
    
    // Iniciar polling para o novo cliente
    startChatPolling(clienteId);
  }
  
  function mostrarLoading() {
    const detailsColumn = document.querySelector('.client-details-column');
    const messagesColumn = document.querySelector('.chat-messages-column');
    
    // Adicionar classe de loading para transições
    detailsColumn.classList.add('loading');
    messagesColumn.classList.add('loading');
    
    const loadingHtml = `
      <div style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; color: #7c3aed;">
        <div class="loading-spinner" style="margin-bottom: 1rem;"></div>
        <div style="font-weight: 600; font-size: 1.1rem;">Carregando...</div>
        <div style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem;">Aguarde um momento</div>
      </div>
    `;
    
    detailsColumn.innerHTML = loadingHtml;
    messagesColumn.innerHTML = loadingHtml;
  }
  
  function esconderLoading() {
    const detailsColumn = document.querySelector('.client-details-column');
    const messagesColumn = document.querySelector('.chat-messages-column');
    
    // Remover classe de loading
    detailsColumn.classList.remove('loading');
    messagesColumn.classList.remove('loading');
    
    // Adicionar animação fade-in ao conteúdo carregado
    setTimeout(() => {
      detailsColumn.classList.add('fade-in');
      messagesColumn.classList.add('fade-in');
      
      // Remover classe fade-in após animação
      setTimeout(() => {
        detailsColumn.classList.remove('fade-in');
        messagesColumn.classList.remove('fade-in');
      }, 400);
    }, 50);
  }
  
  // Gerenciar botão voltar do navegador
  window.addEventListener('popstate', function(e) {
    location.reload(); // Por simplicidade, recarrega a página quando usa botão voltar
  });
  
  // Sistema de redimensionamento de colunas
  class ColumnResizer {
    constructor() {
      this.isResizing = false;
      this.currentHandle = null;
      this.startX = 0;
      this.startWidths = {};
      this.container = document.querySelector('.chat-container-3cols');
      this.columns = {
        1: document.querySelector('.chat-conversations-column'),
        2: document.querySelector('.client-details-column'),
        3: document.querySelector('.chat-messages-column')
      };
      this.indicator = document.getElementById('resize-indicator');
      
      this.init();
      this.loadSavedSizes();
    }
    
    init() {
      // Adicionar event listeners para os handles
      const handles = document.querySelectorAll('.resize-handle');
      console.log('Inicializando redimensionamento - handles encontrados:', handles.length);
      
      handles.forEach(handle => {
        const handleNum = handle.dataset.resize;
        console.log('Configurando handle', handleNum, handle);
        
        handle.addEventListener('mousedown', (e) => {
          console.log('Iniciando redimensionamento do handle', handleNum);
          this.startResize(e);
        });
        
        // Melhor feedback visual
        handle.addEventListener('mouseenter', () => {
          handle.style.background = 'rgba(99, 102, 241, 0.2)';
        });
        
        handle.addEventListener('mouseleave', () => {
          if (!handle.classList.contains('dragging')) {
            handle.style.background = 'transparent';
          }
        });
      });
      
      // Event listeners globais
      document.addEventListener('mousemove', (e) => this.handleResize(e));
      document.addEventListener('mouseup', () => this.stopResize());
      
      // Prevenção de seleção durante redimensionamento
      document.addEventListener('selectstart', (e) => {
        if (this.isResizing) e.preventDefault();
      });
    }
    
    startResize(e) {
      e.preventDefault();
      this.isResizing = true;
      this.currentHandle = parseInt(e.target.dataset.resize);
      this.startX = e.clientX;
      
      // Capturar larguras atuais
      this.startWidths = {
        1: this.columns[1].offsetWidth,
        2: this.columns[2].offsetWidth,
        3: this.columns[3].offsetWidth
      };
      
      // Adicionar classes visuais
      this.container.classList.add('resizing');
      e.target.classList.add('dragging');
      
      // Mostrar indicador
      this.indicator.style.left = e.clientX + 'px';
      this.indicator.style.display = 'block';
      
      // Cursor global
      document.body.style.cursor = 'col-resize';
    }
    
    handleResize(e) {
      if (!this.isResizing) return;
      
      const deltaX = e.clientX - this.startX;
      const containerWidth = this.container.offsetWidth;
      
      // Mover indicador
      this.indicator.style.left = e.clientX + 'px';
      
      if (this.currentHandle === 1) {
        // Redimensionando entre coluna 1 e 2
        const newWidth1 = Math.max(250, Math.min(500, this.startWidths[1] + deltaX));
        const newWidth2 = containerWidth - newWidth1 - this.startWidths[3] - 4; // -4 para bordas
        
        if (newWidth2 >= 300) { // Respeitar largura mínima da coluna 2
          this.columns[1].style.width = newWidth1 + 'px';
          // Coluna 2 se ajusta automaticamente pelo flex: 1
        }
      } else if (this.currentHandle === 2) {
        // Redimensionando entre coluna 2 e 3
        const newWidth3 = Math.max(350, Math.min(600, this.startWidths[3] - deltaX));
        const newWidth2 = containerWidth - this.startWidths[1] - newWidth3 - 4; // -4 para bordas
        
        if (newWidth2 >= 300 && newWidth3 >= 350) { // Respeitar larguras mínimas
          this.columns[3].style.width = newWidth3 + 'px';
          // Coluna 2 se ajusta automaticamente
          console.log('Redimensionando coluna 3 para:', newWidth3 + 'px');
        }
      }
    }
    
    stopResize() {
      if (!this.isResizing) return;
      
      this.isResizing = false;
      this.currentHandle = null;
      
      // Remover classes visuais
      this.container.classList.remove('resizing');
      document.querySelectorAll('.resize-handle').forEach(handle => {
        handle.classList.remove('dragging');
      });
      
      // Esconder indicador
      this.indicator.style.display = 'none';
      
      // Restaurar cursor
      document.body.style.cursor = '';
      
      // Salvar tamanhos
      this.saveSizes();
    }
    
    saveSizes() {
      // Salvar no localStorage para persistir preferências
      const sizes = {
        column1: this.columns[1].offsetWidth,
        column3: this.columns[3].offsetWidth
      };
      localStorage.setItem('chat-column-sizes', JSON.stringify(sizes));
    }
    
    loadSavedSizes() {
      try {
        const saved = localStorage.getItem('chat-column-sizes');
        if (saved) {
          const sizes = JSON.parse(saved);
          if (sizes.column1) {
            this.columns[1].style.width = sizes.column1 + 'px';
          }
          if (sizes.column3) {
            this.columns[3].style.width = sizes.column3 + 'px';
          }
        }
      } catch (e) {
        console.log('Erro ao carregar tamanhos salvos:', e);
      }
    }
    
    resetToDefault() {
      // Método para resetar para tamanhos padrão
      this.columns[1].style.width = '320px';
      this.columns[3].style.width = '450px';
      localStorage.removeItem('chat-column-sizes');
    }
  }

  // Função integrada de envio com robô
  function bindChatFormAjax() {
    const form = document.getElementById('form-chat-enviar');
    console.log('bindChatFormAjax executada - form encontrado:', form);
    if (!form) {
      console.error('Formulário #form-chat-enviar não encontrado!');
      return;
    }
    
    form.onsubmit = async function(e) {
      e.preventDefault();
      console.log('Formulário submetido');
      
      const formData = new FormData(form);
      const clienteId = formData.get('cliente_id');
      const mensagem = formData.get('mensagem');
      
      console.log('Cliente ID:', clienteId, 'Mensagem:', mensagem);
      
      if (!mensagem.trim()) {
        console.log('Mensagem vazia, não enviando');
        return;
      }
      
      // Obter dados do cliente para pegar o número de telefone
      let numeroCliente = null;
      try {
        // Primeiro tentar buscar o número do próprio formulário ou sessão
        const urlParams = new URLSearchParams(window.location.search);
        const clienteIdParam = urlParams.get('cliente_id');
        
        if (clienteIdParam) {
          const clienteResponse = await fetch(`api/dados_cliente.php?id=${clienteIdParam}`);
          const clienteData = await clienteResponse.json();
          numeroCliente = clienteData.celular || clienteData.telefone;
        }
      } catch (error) {
        console.error('Erro ao obter dados do cliente:', error);
      }
      
      try {
        // Indicar que está enviando
        const textarea = form.querySelector('textarea[name="mensagem"]');
        const sendBtn = form.querySelector('button[type="submit"]');
        const originalText = sendBtn.textContent;
        
        sendBtn.disabled = true;
        sendBtn.textContent = 'Enviando...';
        
        // Tentar enviar via robô se estiver conectado e tivermos o número
        if (robotStatus.connected && numeroCliente) {
          console.log('📤 Enviando via robô WhatsApp para:', numeroCliente);
          
          await enviarViaRobo(numeroCliente, mensagem);
          
          // Salvar no banco para histórico
          const saveResponse = await fetch('chat_enviar.php', {
            method: 'POST',
            body: formData
          });
          
          const saveData = await saveResponse.json();
          if (!saveData.success) {
            console.warn('Mensagem enviada via robô mas erro ao salvar no banco:', saveData.error);
          }
          
          console.log('✅ Mensagem enviada via robô WhatsApp');
        } else {
          // Enviar via método tradicional (API do painel)
          if (!robotStatus.connected) {
            console.log('📤 Robô desconectado, enviando via método tradicional...');
          } else {
            console.log('📤 Número não encontrado, enviando via método tradicional...');
          }
          
          const response = await fetch('chat_enviar.php', {
            method: 'POST',
            body: formData
          });
          
          const data = await response.json();
          if (!data.success) {
            throw new Error(data.error || 'Erro ao enviar mensagem');
          }
          
          console.log('✅ Mensagem enviada via método tradicional');
        }
        
        // Recarregar mensagens
        loadFullChatHistory(clienteId);
        
        // Limpar formulário
        form.reset();
        if (textarea) {
          textarea.style.height = 'auto';
        }
        
        // Restaurar botão
        sendBtn.disabled = false;
        sendBtn.textContent = originalText;
        
        // Garantir scroll para última mensagem após envio
        setTimeout(() => {
          forceScrollToBottom();
          console.log('📤 Mensagem enviada - scroll aplicado');
        }, 600);
        
      } catch (error) {
        console.error('❌ Erro ao enviar mensagem:', error);
        
        // Restaurar botão em caso de erro
        const sendBtn = form.querySelector('button[type="submit"]');
        const originalText = sendBtn.textContent === 'Enviando...' ? 'Enviar' : sendBtn.textContent;
        sendBtn.disabled = false;
        sendBtn.textContent = originalText;
        
        alert('Erro ao enviar mensagem: ' + error.message);
      }
    };
  }

  // Event listener para busca de conversas por número
  document.getElementById('buscaConversa').addEventListener('input', function() {
    filtrarConversasPorNumero(this.value);
  });
  
  /**
   * Filtrar conversas por número de telefone
   * Busca apenas em conversas ativas e números de telefone
   */
  function filtrarConversasPorNumero(termo) {
    termo = termo.trim();
    const itensConversa = document.querySelectorAll('.conversation-item');
    const searchContainer = document.querySelector('.chat-search');
    const searchInput = document.getElementById('buscaConversa');
    
    // Controlar exibição do botão limpar
    if (termo === '') {
      searchContainer.classList.remove('has-content', 'searching');
      // Mostrar todas as conversas se busca vazia
      itensConversa.forEach(item => {
        item.style.display = 'flex';
        item.classList.remove('filtered-out', 'filtered-match');
      });
      // Remover todos os destaques de número
      document.querySelectorAll('.numero-destacado').forEach(el => el.remove());
      return;
    }
    
    // Mostrar botão limpar
    searchContainer.classList.add('has-content');
    
    // Primeiro, remover todas as classes de filtro para resetar o estado
    itensConversa.forEach(item => {
      item.classList.remove('filtered-out', 'filtered-match');
    });
    
    // Validar se termo contém apenas números, espaços, hífens, parênteses ou sinal de +
    const regexNumero = /^[\d\s\-\(\)\+]*$/;
    if (!regexNumero.test(termo)) {
      // Se não é um número válido, não mostrar nenhum resultado
      itensConversa.forEach(item => {
        item.style.display = 'none';
        item.classList.add('filtered-out');
      });
      searchContainer.classList.remove('searching');
      return;
    }
    
    // Indicar que está buscando
    searchContainer.classList.add('searching');
    
    // Filtrar apenas conversas ativas que possuem números
    let totalItens = itensConversa.length;
    let itensProcessados = 0;
    
    itensConversa.forEach(item => {
      const clienteId = item.dataset.clienteId;
      
      // Buscar dados do cliente no cache ou fazer requisição
      buscarDadosClienteParaFiltro(clienteId, termo, item, () => {
        itensProcessados++;
        if (itensProcessados === totalItens) {
          // Remover indicador de busca quando terminar
          searchContainer.classList.remove('searching');
          
          // Verificar se encontrou algum resultado
          const resultados = document.querySelectorAll('.conversation-item[style*="flex"]');
          if (resultados.length === 0) {
            mostrarMensagemNenhumResultado();
          } else {
            removerMensagemNenhumResultado();
          }
        }
      });
    });
  }
  
  /**
   * Buscar dados do cliente para filtro de número
   */
  function buscarDadosClienteParaFiltro(clienteId, termo, itemElement, callback) {
    // Cache local para dados de clientes na busca
    if (!window.clienteDataCache) {
      window.clienteDataCache = new Map();
    }
    
    const cacheKey = `cliente_filtro_${clienteId}`;
    const cached = window.clienteDataCache.get(cacheKey);
    
    if (cached && (Date.now() - cached.timestamp < 60000)) { // Cache de 1 minuto
      // Usar dados do cache
      filtrarItemPorNumero(itemElement, cached.data, termo);
      if (callback) callback();
      return;
    }
    
    // Fazer requisição para buscar dados do cliente
    fetch(`api/dados_cliente_numero.php?id=${clienteId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Salvar no cache
          window.clienteDataCache.set(cacheKey, {
            timestamp: Date.now(),
            data: data.cliente
          });
          
          filtrarItemPorNumero(itemElement, data.cliente, termo);
        } else {
          // Se erro, esconder item
          itemElement.style.display = 'none';
          itemElement.classList.add('filtered-out');
        }
        if (callback) callback();
      })
      .catch(error => {
        console.error('Erro ao buscar dados do cliente:', error);
        // Em caso de erro, manter item visível
        itemElement.style.display = 'flex';
        if (callback) callback();
      });
  }
  
  /**
   * Filtrar item específico por número
   */
  function filtrarItemPorNumero(itemElement, dadosCliente, termo) {
    const celular = dadosCliente.celular || '';
    const telefone = dadosCliente.telefone || '';
    
    // Limpar números para comparação (remover caracteres especiais)
    const termoLimpo = termo.replace(/[\s\-\(\)\+]/g, '');
    const celularLimpo = celular.replace(/[\s\-\(\)\+]/g, '');
    const telefoneLimpo = telefone.replace(/[\s\-\(\)\+]/g, '');
    
    // Verificar se o termo está contido no celular ou telefone
    const encontrado = 
      celularLimpo.includes(termoLimpo) || 
      telefoneLimpo.includes(termoLimpo) ||
      celular.includes(termo) ||
      telefone.includes(termo);
    
    // Remover destaque anterior primeiro
    const destaqueAnterior = itemElement.querySelector('.numero-destacado');
    if (destaqueAnterior) {
      destaqueAnterior.remove();
    }
    
    if (encontrado) {
      // Mostrar item e marcar como resultado da busca
      itemElement.style.display = 'flex';
      itemElement.classList.remove('filtered-out');
      itemElement.classList.add('filtered-match');
      
      // Destacar número encontrado no elemento
      const metaElement = itemElement.querySelector('.conversation-meta');
      if (metaElement) {
        const numeroMostrar = celular || telefone || 'Sem número';
        metaElement.innerHTML = metaElement.innerHTML + 
          `<br><span class="numero-destacado">📞 ${numeroMostrar}</span>`;
      }
    } else {
      // Esconder item
      itemElement.style.display = 'none';
      itemElement.classList.add('filtered-out');
      itemElement.classList.remove('filtered-match');
    }
  }
  
  /**
   * Mostrar mensagem quando não há resultados
   */
  function mostrarMensagemNenhumResultado() {
    let mensagem = document.getElementById('nenhum-resultado-busca');
    if (!mensagem) {
      mensagem = document.createElement('div');
      mensagem.id = 'nenhum-resultado-busca';
      mensagem.style.cssText = `
        padding: 2rem;
        text-align: center;
        color: var(--text-muted);
        border: 1px dashed var(--border-color);
        border-radius: 8px;
        margin: 1rem;
      `;
      mensagem.innerHTML = `
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔍</div>
        <p style="margin: 0; font-weight: 500;">Nenhum número encontrado</p>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
          Tente um número diferente ou limpe o filtro
        </p>
      `;
      
      document.querySelector('.chat-conversations').appendChild(mensagem);
    }
  }
  
  /**
   * Remover mensagem de nenhum resultado
   */
  function removerMensagemNenhumResultado() {
    const mensagem = document.getElementById('nenhum-resultado-busca');
    if (mensagem) {
      mensagem.remove();
    }
  }
  
  /**
   * Limpar filtro de busca
   */
  function limparFiltroConversa() {
    const searchInput = document.getElementById('buscaConversa');
    const searchContainer = document.querySelector('.chat-search');
    
    searchInput.value = '';
    searchContainer.classList.remove('has-content', 'searching');
    
    filtrarConversasPorNumero('');
    removerMensagemNenhumResultado();
    
    // Focar no input após limpar
    searchInput.focus();
  }
  </script>
  
  <?php
}

include 'template.php';
?> 
<script>
document.addEventListener('DOMContentLoaded', function() {
  var iframe = document.querySelector('.client-details-full iframe');
  if (!iframe) return;
  iframe.addEventListener('load', function() {
    try {
      var btn = iframe.contentWindow.document.getElementById('btn-editar-cliente');
      if (btn) {
        btn.onclick = function(e) {
          e.preventDefault();
          var src = iframe.getAttribute('src');
          if (src.indexOf('editar=1') === -1) {
            src += (src.indexOf('?') === -1 ? '?' : '&') + 'editar=1';
            iframe.setAttribute('src', src);
          }
        };
      }
    } catch (err) {}
  });
});
</script>