<?php
require_once __DIR__ . '/../config.php';
require_once 'db.php';

$page = 'chat.php';
$page_title = 'Chat Centralizado - Versão Simplificada';
$custom_header = '';

function render_content() {
  global $mysqli;
  
  // Buscar conversas recentes (sem cache)
  $sql = "SELECT 
            c.id,
            c.nome,
            c.telefone,
            c.email,
            COUNT(m.id) as total_mensagens,
            MAX(m.data_hora) as ultima_mensagem,
            SUM(CASE WHEN m.status = 'nao_lido' AND m.direcao = 'recebido' THEN 1 ELSE 0 END) as nao_lidas
          FROM clientes c
          LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
          GROUP BY c.id
          ORDER BY ultima_mensagem DESC
          LIMIT 50";
  
  $result = $mysqli->query($sql);
  $conversas = [];
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $conversas[] = $row;
    }
  }
  
  // Cliente selecionado
  $cliente_selecionado = null;
  $mensagens = [];
  
  if (isset($_GET['cliente_id']) && $_GET['cliente_id']) {
    $cliente_id = intval($_GET['cliente_id']);
    
    // Buscar dados do cliente
    $stmt = $mysqli->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $cliente_selecionado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($cliente_selecionado) {
      // Marcar mensagens como lidas
      $mysqli->query("UPDATE mensagens_comunicacao SET status = 'lido' WHERE cliente_id = $cliente_id AND direcao = 'recebido'");
      
      // Buscar mensagens do cliente
      $sql = "SELECT m.*, c.nome_exibicao as canal_nome
              FROM mensagens_comunicacao m
              LEFT JOIN canais_comunicacao c ON m.canal_id = c.id
              WHERE m.cliente_id = ?
              ORDER BY m.data_hora ASC";
      
      $stmt = $mysqli->prepare($sql);
      $stmt->bind_param('i', $cliente_id);
      $stmt->execute();
      $result = $stmt->get_result();
      
      while ($msg = $result->fetch_assoc()) {
        $mensagens[] = $msg;
      }
      $stmt->close();
    }
  }
  
  ?>
  
  <style>
    .chat-container {
      display: flex;
      height: 80vh;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
    }
    
    .chat-sidebar {
      width: 300px;
      border-right: 1px solid #ddd;
      background: #f8f9fa;
    }
    
    .chat-main {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    
    .chat-header {
      padding: 15px;
      border-bottom: 1px solid #ddd;
      background: #fff;
    }
    
    .chat-messages {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
      background: #fff;
    }
    
    .chat-input {
      padding: 15px;
      border-top: 1px solid #ddd;
      background: #fff;
    }
    
    .conversation-item {
      padding: 10px 15px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
    }
    
    .conversation-item:hover {
      background: #e9ecef;
    }
    
    .conversation-item.active {
      background: #007bff;
      color: white;
    }
    
    .message {
      margin: 10px 0;
      padding: 10px;
      border-radius: 8px;
      max-width: 70%;
    }
    
    .message.received {
      background: #f1f3f4;
      align-self: flex-start;
    }
    
    .message.sent {
      background: #007bff;
      color: white;
      align-self: flex-end;
    }
    
    .unread-badge {
      background: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
      margin-left: 5px;
    }
  </style>
  
  <div class="chat-container">
    <!-- Sidebar com conversas -->
    <div class="chat-sidebar">
      <div class="chat-header">
        <h3>💬 Chat Centralizado</h3>
        <p>Versão Simplificada</p>
      </div>
      
      <div class="conversations-list">
        <?php foreach ($conversas as $conv): ?>
          <div class="conversation-item <?= (isset($_GET['cliente_id']) && $_GET['cliente_id'] == $conv['id']) ? 'active' : '' ?>"
               onclick="window.location.href='?cliente_id=<?= $conv['id'] ?>'">
            <strong><?= htmlspecialchars($conv['nome']) ?></strong>
            <br>
            <small><?= htmlspecialchars($conv['telefone']) ?></small>
            <?php if ($conv['nao_lidas'] > 0): ?>
              <span class="unread-badge"><?= $conv['nao_lidas'] ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <!-- Área principal do chat -->
    <div class="chat-main">
      <?php if ($cliente_selecionado): ?>
        <div class="chat-header">
          <h4><?= htmlspecialchars($cliente_selecionado['nome']) ?></h4>
          <small><?= htmlspecialchars($cliente_selecionado['telefone']) ?></small>
        </div>
        
        <div class="chat-messages">
          <?php if (empty($mensagens)): ?>
            <p style="text-align: center; color: #666; margin-top: 50px;">
              Nenhuma mensagem ainda. Inicie uma conversa!
            </p>
          <?php else: ?>
            <?php foreach ($mensagens as $msg): ?>
              <div class="message <?= $msg['direcao'] ?>">
                <div class="message-content">
                  <?= htmlspecialchars($msg['conteudo']) ?>
                </div>
                <small style="opacity: 0.7;">
                  <?= date('d/m/Y H:i', strtotime($msg['data_hora'])) ?>
                  <?php if ($msg['canal_nome']): ?>
                    via <?= htmlspecialchars($msg['canal_nome']) ?>
                  <?php endif; ?>
                </small>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        
        <div class="chat-input">
          <form method="post" style="display: flex; gap: 10px;">
            <input type="text" name="mensagem" placeholder="Digite sua mensagem..." 
                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px;">
              Enviar
            </button>
          </form>
        </div>
      <?php else: ?>
        <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
          <div style="text-align: center; color: #666;">
            <h3>Selecione uma conversa</h3>
            <p>Escolha um cliente na lista ao lado para iniciar o chat.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  
  <script>
    // Auto-scroll para a última mensagem
    const messagesContainer = document.querySelector('.chat-messages');
    if (messagesContainer) {
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
  </script>
  
  <?php
}

// Incluir o template principal
require_once 'template.php';
?> 