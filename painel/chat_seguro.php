<?php
require_once 'config.php';
require_once 'db.php';

$page = 'chat.php';
$page_title = 'Chat Centralizado';
$custom_header = '';

function render_content() {
  global $mysqli;
  
  // Verificar se a conexão com o banco está disponível
  if (!$mysqli || !$mysqli->ping()) {
    echo '<div class="error-container">';
    echo '<h2>⚠️ Sistema Temporariamente Indisponível</h2>';
    echo '<p>O sistema está temporariamente indisponível devido a manutenção no banco de dados.</p>';
    echo '<p>Limite de conexões excedido. Tente novamente em alguns minutos.</p>';
    echo '<button onclick="window.location.reload()" class="btn-reload">🔄 Tentar Novamente</button>';
    echo '</div>';
    echo '<style>';
    echo '.error-container { text-align: center; padding: 50px; margin: 50px auto; max-width: 600px; border: 2px solid #ff6b6b; border-radius: 10px; background: #fff5f5; }';
    echo '.btn-reload { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 20px; }';
    echo '.btn-reload:hover { background: #0056b3; }';
    echo '</style>';
    return;
  }
  
  // Tentar carregar cache_manager com tratamento de erro
  $cache_available = false;
  try {
    require_once 'cache_manager.php';
    $cache_available = function_exists('cache_conversas');
  } catch (Exception $e) {
    error_log("Erro ao carregar cache_manager: " . $e->getMessage());
  }
  
  // Buscar conversas com fallback para query direta se cache falhar
  $conversas = [];
  if ($cache_available) {
    try {
      $conversas = cache_conversas($mysqli);
    } catch (Exception $e) {
      error_log("Erro no cache_conversas: " . $e->getMessage());
      $cache_available = false;
    }
  }
  
  // Fallback: buscar conversas diretamente do banco se cache falhar
  if (!$cache_available || empty($conversas)) {
    try {
      $sql = "SELECT 
                  c.id as cliente_id,
                  c.nome,
                  c.celular,
                  'WhatsApp' as canal_nome,
                  COALESCE(ultima.mensagem, 'Sem mensagens') as ultima_mensagem,
                  COALESCE(ultima.data_hora, c.data_criacao) as ultima_data,
                  0 as mensagens_nao_lidas
              FROM clientes c
              LEFT JOIN (
                  SELECT 
                      cliente_id,
                      mensagem,
                      data_hora,
                      ROW_NUMBER() OVER (PARTITION BY cliente_id ORDER BY data_hora DESC) as rn
                  FROM mensagens_comunicacao 
                  WHERE cliente_id IS NOT NULL
              ) ultima ON c.id = ultima.cliente_id AND ultima.rn = 1
              WHERE c.id IS NOT NULL
              ORDER BY ultima.data_hora DESC
              LIMIT 20";
      
      $result = $mysqli->query($sql);
      if ($result) {
        while ($conv = $result->fetch_assoc()) {
          $conversas[] = $conv;
        }
      }
    } catch (Exception $e) {
      error_log("Erro na query de conversas: " . $e->getMessage());
    }
  }
  
  // Cliente selecionado
  $cliente_selecionado = null;
  $mensagens = [];
  
  if (isset($_GET['cliente_id']) && $_GET['cliente_id']) {
    $cliente_id = intval($_GET['cliente_id']);
    
    // Buscar dados do cliente diretamente do banco
    try {
      $stmt = $mysqli->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
      if ($stmt) {
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente_selecionado = $result->fetch_assoc();
        $stmt->close();
      }
      
      if ($cliente_selecionado) {
        // Marcar mensagens como lidas
        $mysqli->query("UPDATE mensagens_comunicacao SET status = 'lido' WHERE cliente_id = $cliente_id AND direcao = 'recebido'");
        
        // Buscar mensagens do cliente
        $stmt = $mysqli->prepare("SELECT m.*, 'WhatsApp' as canal_nome FROM mensagens_comunicacao m WHERE m.cliente_id = ? ORDER BY m.data_hora ASC");
        if ($stmt) {
          $stmt->bind_param('i', $cliente_id);
          $stmt->execute();
          $result = $stmt->get_result();
          
          while ($msg = $result->fetch_assoc()) {
            $mensagens[] = $msg;
          }
          $stmt->close();
        }
      }
    } catch (Exception $e) {
      error_log("Erro ao buscar cliente/mensagens: " . $e->getMessage());
    }
  }
  
  ?>
  
  <link rel="stylesheet" href="assets/chat-modern.css">
  <script src="assets/chat-functions.js"></script>
  
  <?php if (empty($conversas)): ?>
  <!-- Estado quando não há conversas -->
  <div class="chat-empty-state">
    <h2>💬 Chat Centralizado</h2>
    <p>Nenhuma conversa encontrada ou sistema em manutenção.</p>
    <button onclick="window.location.reload()" class="btn-reload">🔄 Atualizar</button>
  </div>
  
  <style>
  .chat-empty-state { text-align: center; padding: 100px 20px; }
  .btn-reload { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
  </style>
  
  <?php else: ?>
  
  <div class="chat-container-3cols">
    <!-- Coluna 1: Lista de conversas -->
    <div class="chat-conversations-column">
      <div class="chat-sidebar-header">
        <h1 class="chat-sidebar-title">💬 Chat Centralizado</h1>
        <div class="chat-search">
          <input type="text" id="buscaConversa" placeholder="Buscar por número..." oninput="filtrarConversasPorNumero(this.value)">
          <button class="clear-search" onclick="limparFiltroConversa()" title="Limpar busca">✕</button>
        </div>
      </div>
      
      <div class="chat-conversations-list" id="conversationsList">
        <?php foreach ($conversas as $conversa): ?>
          <div class="conversation-item <?= ($cliente_selecionado && $cliente_selecionado['id'] == $conversa['cliente_id']) ? 'active' : '' ?>" 
               data-cliente-id="<?= $conversa['cliente_id'] ?>"
               onclick="carregarCliente(<?= $conversa['cliente_id'] ?>, '<?= htmlspecialchars($conversa['nome']) ?>', event)">
            
            <div class="conversation-info">
              <div class="conversation-header">
                <span class="client-name"><?= htmlspecialchars($conversa['nome']) ?></span>
                <span class="conversation-time"><?= date('H:i', strtotime($conversa['ultima_data'])) ?></span>
              </div>
              
              <div class="conversation-preview">
                <span class="phone-number"><?= htmlspecialchars($conversa['celular']) ?></span>
                <span class="last-message"><?= htmlspecialchars(mb_substr($conversa['ultima_mensagem'], 0, 50)) ?>...</span>
              </div>
              
              <div class="conversation-meta">
                <span class="channel-name"><?= htmlspecialchars($conversa['canal_nome']) ?></span>
                <?php if (isset($conversa['mensagens_nao_lidas']) && $conversa['mensagens_nao_lidas'] > 0): ?>
                  <span class="unread-badge"><?= $conversa['mensagens_nao_lidas'] ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <!-- Coluna 2: Detalhes do cliente -->
    <div class="chat-client-column">
      <?php if ($cliente_selecionado): ?>
        <div class="client-details-header">
          <h2>👤 <?= htmlspecialchars($cliente_selecionado['nome']) ?></h2>
          <p><strong>Telefone:</strong> <?= htmlspecialchars($cliente_selecionado['celular']) ?></p>
          <?php if (!empty($cliente_selecionado['email'])): ?>
            <p><strong>Email:</strong> <?= htmlspecialchars($cliente_selecionado['email']) ?></p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="client-details-empty">
          <p>Selecione uma conversa para ver os detalhes do cliente</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Coluna 3: Mensagens -->
    <div class="chat-messages-column">
      <?php if ($cliente_selecionado): ?>
        <div class="chat-messages-header">
          <h2>💬 Conversa com <?= htmlspecialchars($cliente_selecionado['nome']) ?></h2>
        </div>
        
        <div class="chat-messages" id="chat-messages">
          <?php foreach ($mensagens as $msg): ?>
            <div class="message <?= $msg['direcao'] === 'recebido' ? 'received' : 'sent' ?>">
              <div class="message-bubble">
                <?= htmlspecialchars($msg['mensagem']) ?>
                <div class="message-time">
                  <?= date('H:i', strtotime($msg['data_hora'])) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="chat-input-area">
          <form id="form-chat-enviar" onsubmit="return enviarMensagemSegura(event)">
            <input type="hidden" name="cliente_id" value="<?= $cliente_selecionado['id'] ?>">
            <input type="hidden" name="canal_id" value="36">
            
            <div class="chat-input-container">
              <textarea name="mensagem" class="chat-input" placeholder="Digite sua mensagem..." rows="1"></textarea>
              <button type="submit" class="chat-send-btn">Enviar ➤</button>
            </div>
          </form>
        </div>
      <?php else: ?>
        <div class="chat-messages-empty">
          <h2>Selecione uma conversa</h2>
          <p>Escolha uma conversa da lista para começar a visualizar as mensagens.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  
  <?php endif; ?>
  
  <script>
  function carregarCliente(clienteId, nomeCliente, event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    
    window.location.href = 'chat_seguro.php?cliente_id=' + clienteId;
  }
  
  function enviarMensagemSegura(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const mensagem = formData.get('mensagem');
    
    if (!mensagem.trim()) {
      alert('Digite uma mensagem');
      return false;
    }
    
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = 'Enviando...';
    
    fetch('chat_enviar.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        alert('Erro ao enviar: ' + (data.error || 'Erro desconhecido'));
      }
    })
    .catch(error => {
      alert('Erro de conexão: ' + error.message);
    })
    .finally(() => {
      button.disabled = false;
      button.textContent = 'Enviar ➤';
    });
    
    return false;
  }
  
  function filtrarConversasPorNumero(termo) {
    const items = document.querySelectorAll('.conversation-item');
    items.forEach(item => {
      const numero = item.querySelector('.phone-number').textContent;
      const nome = item.querySelector('.client-name').textContent;
      
      if (numero.includes(termo) || nome.toLowerCase().includes(termo.toLowerCase())) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  }
  
  function limparFiltroConversa() {
    document.getElementById('buscaConversa').value = '';
    document.querySelectorAll('.conversation-item').forEach(item => {
      item.style.display = 'block';
    });
  }
  </script>
  
  <?php
}

include 'template.php';
?> 