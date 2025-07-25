<?php
require_once __DIR__ . '/../../config.php';
require_once '../db.php';
require_once '../cache_manager.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: private, max-age=15'); // Cache HTTP de 15 segundos

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo '<div style="padding: 20px; text-align: center; color: #64748b;">
            <p>Selecione uma conversa para ver as mensagens</p>
          </div>';
    exit;
}

file_put_contents(__DIR__ . '/debug_mensagens_cliente.log', date('Y-m-d H:i:s') . " - Cliente: $cliente_id\n", FILE_APPEND);

// Cache para todo o output HTML das mensagens
echo cache_remember("mensagens_html_{$cliente_id}", function() use ($cliente_id, $mysqli) {
    // Buscar dados do cliente usando cache
    $cliente = cache_cliente($cliente_id, $mysqli);
    
    if (!$cliente) {
        return '<div style="padding: 20px; text-align: center; color: #ef4444;">
                  <p>Cliente não encontrado</p>
                </div>';
    }
    
    // Marcar mensagens como lidas (não fazer cache desta operação)
    $mysqli->query("UPDATE mensagens_comunicacao SET status = 'lido' WHERE cliente_id = $cliente_id AND direcao = 'recebido'");
    
    // Buscar mensagens usando cache
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
            file_put_contents(__DIR__ . '/debug_mensagens_cliente.log', print_r($msg, true), FILE_APPEND);
            $mensagens[] = $msg;
        }
        $stmt->close();
        
        return $mensagens;
    }, 30); // Cache de 30 segundos para mensagens
    
    // Gerar HTML
    ob_start();
    ?>
    
    <!-- Header da conversa -->
    <div class="chat-header">
      <div class="chat-header-info">
        <div class="chat-header-avatar">
          <?= strtoupper(substr($cliente['nome'], 0, 1)) ?>
        </div>
        <div class="chat-header-details">
          <h3 class="chat-header-name"><?= htmlspecialchars($cliente['nome']) ?></h3>
          <p class="chat-header-status">Online</p>
        </div>
      </div>
      <div class="chat-header-actions">
        <button class="chat-action-btn" onclick="abrirDetalhesCliente(<?= $cliente_id ?>)" title="Ver detalhes">
          👤
        </button>
        <button class="chat-action-btn" onclick="abrirHistorico(<?= $cliente_id ?>)" title="Histórico">
          📋
        </button>
      </div>
    </div>
    
    <!-- Área de mensagens -->
    <div class="chat-messages" id="chat-messages">
      <?php if (empty($mensagens)): ?>
        <div class="chat-empty">
          <p>Nenhuma mensagem ainda. Inicie a conversa!</p>
        </div>
      <?php else: ?>
        <?php 
        $current_date = '';
        foreach ($mensagens as $msg): 
          $msg_date = date('Y-m-d', strtotime($msg['data_hora']));
          if ($msg_date !== $current_date) {
            $current_date = $msg_date;
            echo '<div class="date-separator">' . date('d/m/Y', strtotime($msg_date)) . '</div>';
          }
        ?>
          <div class="message <?= $msg['direcao'] === 'enviado' ? 'sent' : 'received' ?>">
            <div class="message-bubble">
              <?php if (!empty($msg['anexo'])): ?>
                <?php 
                $anexo = $msg['anexo'];
                $extensoes_imagem = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                $extensao = strtolower(pathinfo($anexo, PATHINFO_EXTENSION));
                ?>
                <?php if (in_array($extensao, $extensoes_imagem)): ?>
                  <a href="<?= htmlspecialchars($anexo) ?>" target="_blank">
                    <img src="<?= htmlspecialchars($anexo) ?>" alt="anexo" style="max-width:200px;max-height:150px;border-radius:8px;margin-bottom:8px;">
                  </a>
                <?php else: ?>
                  <a href="<?= htmlspecialchars($anexo) ?>" target="_blank" style="color:inherit;text-decoration:underline;">
                    📎 <?= htmlspecialchars(basename($anexo)) ?>
                  </a><br>
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
      <?php endif; ?>
    </div>
    
    <!-- Área de input -->
    <div class="chat-input-area">
      <form id="form-chat-enviar" enctype="multipart/form-data">
        <input type="hidden" name="cliente_id" value="<?= $cliente['id'] ?>">
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
        
        <!-- Segunda linha: Botão enviar -->
        <div class="chat-send-container">
          <button type="submit" class="chat-send-btn">
            Enviar ➤
          </button>
        </div>
      </form>
    </div>
    
    <?php
    return ob_get_clean();
}, 15); // Cache de 15 segundos para HTML completo
?> 