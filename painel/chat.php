<?php
$page = 'chat.php';
$page_title = 'Chat Centralizado';
require_once 'config.php';
require_once 'db.php';
include 'template.php';

function render_content() {
  global $mysqli;
  
  // Buscar canais conectados
  $canais = $mysqli->query("SELECT * FROM canais_comunicacao WHERE status = 'conectado' ORDER BY nome_exibicao");
  
  // Buscar conversas (clientes com mensagens)
  $conversas = $mysqli->query("
    SELECT DISTINCT 
      c.id as cliente_id,
      c.nome as cliente_nome,
      c.email as cliente_email,
      c.celular as cliente_celular,
      COUNT(m.id) as total_mensagens,
      MAX(m.data_hora) as ultima_mensagem,
      SUM(CASE WHEN m.direcao = 'recebido' AND m.status != 'lido' THEN 1 ELSE 0 END) as nao_lidas
    FROM clientes c
    LEFT JOIN mensagens_comunicacao m ON c.id = m.cliente_id
    WHERE m.id IS NOT NULL
    GROUP BY c.id, c.nome, c.email, c.celular
    ORDER BY ultima_mensagem DESC
  ");
  
  // Buscar mensagens se um cliente foi selecionado
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
  
  echo '<div class="flex h-screen bg-gray-50">';
  
  // Sidebar com lista de conversas
  echo '<div class="w-80 bg-white border-r border-gray-200 flex flex-col">';
  echo '<div class="p-4 border-b border-gray-200">';
  echo '<h2 class="text-lg font-semibold text-gray-800">Conversas</h2>';
  echo '<div class="mt-2 relative">';
  echo '<input type="text" id="busca-conversas" placeholder="Buscar cliente..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">';
  echo '</div>';
  echo '</div>';
  
  echo '<div class="flex-1 overflow-y-auto">';
  if ($conversas && $conversas->num_rows > 0) {
    while ($conversa = $conversas->fetch_assoc()) {
      $is_active = ($cliente_selecionado && $cliente_selecionado['id'] == $conversa['cliente_id']) ? 'bg-purple-50 border-purple-200' : 'hover:bg-gray-50';
      $nao_lidas_badge = $conversa['nao_lidas'] > 0 ? '<span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-auto">' . $conversa['nao_lidas'] . '</span>' : '';
      
      echo '<div class="conversa-item p-4 border-b border-gray-100 cursor-pointer ' . $is_active . '" data-cliente-id="' . $conversa['cliente_id'] . '">';
      echo '<div class="flex items-center justify-between">';
      echo '<div class="flex-1 min-w-0">';
      echo '<h3 class="text-sm font-medium text-gray-900 truncate">' . htmlspecialchars($conversa['cliente_nome']) . '</h3>';
      echo '<p class="text-xs text-gray-500 truncate">' . htmlspecialchars($conversa['cliente_email']) . '</p>';
      echo '</div>';
      echo $nao_lidas_badge;
      echo '</div>';
      echo '<div class="mt-1 flex items-center justify-between text-xs text-gray-400">';
      echo '<span>' . $conversa['total_mensagens'] . ' mensagens</span>';
      if ($conversa['ultima_mensagem']) {
        echo '<span>' . date('d/m H:i', strtotime($conversa['ultima_mensagem'])) . '</span>';
      }
      echo '</div>';
      echo '</div>';
    }
  } else {
    echo '<div class="p-8 text-center text-gray-500">';
    echo '<p>Nenhuma conversa encontrada</p>';
    echo '<p class="text-sm mt-2">As conversas aparecerão aqui quando houver mensagens</p>';
    echo '</div>';
  }
  echo '</div>';
  echo '</div>';
  
  // Área principal do chat
  echo '<div class="flex-1 flex flex-col">';
  
  if ($cliente_selecionado) {
    // Header do chat
    echo '<div class="bg-white border-b border-gray-200 p-4">';
    echo '<div class="flex items-center justify-between">';
    echo '<div>';
    echo '<h2 class="text-lg font-semibold text-gray-800">' . htmlspecialchars($cliente_selecionado['nome']) . '</h2>';
    echo '<p class="text-sm text-gray-500">' . htmlspecialchars($cliente_selecionado['email']) . '</p>';
    echo '</div>';
    echo '<div class="flex items-center space-x-2">';
    if ($cliente_selecionado['celular']) {
      echo '<a href="https://wa.me/55' . preg_replace('/\D/', '', $cliente_selecionado['celular']) . '" target="_blank" class="text-green-600 hover:text-green-700" title="WhatsApp">';
      echo '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg>';
      echo '</a>';
    }
    echo '<a href="mailto:' . htmlspecialchars($cliente_selecionado['email']) . '" class="text-blue-600 hover:text-blue-700" title="E-mail">';
    echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>';
    echo '</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Área de mensagens
    echo '<div class="flex-1 overflow-y-auto p-4 bg-gray-50" id="chat-messages">';
    if (!empty($mensagens)) {
      foreach ($mensagens as $msg) {
        $is_received = $msg['direcao'] == 'recebido';
        $bg_color = $is_received ? 'bg-white' : 'bg-purple-500 text-white';
        $align = $is_received ? 'justify-start' : 'justify-end';
        $margin = $is_received ? 'mr-12' : 'ml-12';
        
        echo '<div class="flex ' . $align . ' mb-4">';
        echo '<div class="max-w-xs lg:max-w-md ' . $bg_color . ' rounded-lg px-4 py-2 shadow-sm ' . $margin . '">';
        echo '<div class="text-sm">' . nl2br(htmlspecialchars($msg['mensagem'])) . '</div>';
        echo '<div class="text-xs mt-1 ' . ($is_received ? 'text-gray-500' : 'text-purple-100') . '">';
        echo date('d/m H:i', strtotime($msg['data_hora']));
        if ($msg['canal_nome']) {
          echo ' • ' . htmlspecialchars($msg['canal_nome']);
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
      }
    } else {
      echo '<div class="text-center text-gray-500 mt-8">';
      echo '<p>Nenhuma mensagem ainda</p>';
      echo '<p class="text-sm mt-2">Inicie uma conversa enviando uma mensagem</p>';
      echo '</div>';
    }
    echo '</div>';
    
    // Área de envio de mensagem
    echo '<div class="bg-white border-t border-gray-200 p-4">';
    echo '<form id="form-enviar-mensagem" class="flex space-x-2">';
    echo '<input type="hidden" name="cliente_id" value="' . $cliente_selecionado['id'] . '">';
    echo '<select name="canal_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" required>';
    echo '<option value="">Selecionar canal</option>';
    if ($canais && $canais->num_rows > 0) {
      while ($canal = $canais->fetch_assoc()) {
        echo '<option value="' . $canal['id'] . '">' . htmlspecialchars($canal['nome_exibicao']) . '</option>';
      }
    }
    echo '</select>';
    echo '<input type="text" name="mensagem" placeholder="Digite sua mensagem..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" required>';
    echo '<button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">Enviar</button>';
    echo '</form>';
    echo '</div>';
    
  } else {
    // Tela inicial quando nenhum cliente está selecionado
    echo '<div class="flex-1 flex items-center justify-center">';
    echo '<div class="text-center text-gray-500">';
    echo '<svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8 4.03-8 9-8 9 3.582 9 8z"/>';
    echo '</svg>';
    echo '<h3 class="text-lg font-medium mb-2">Selecione uma conversa</h3>';
    echo '<p class="text-sm">Escolha um cliente na lista ao lado para iniciar uma conversa</p>';
    echo '</div>';
    echo '</div>';
  }
  
  echo '</div>'; // Fim da área principal
  
  echo '</div>'; // Fim do container principal
  
  // JavaScript para funcionalidade do chat
  echo '<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Busca de conversas
    const buscaInput = document.getElementById("busca-conversas");
    const conversaItems = document.querySelectorAll(".conversa-item");
    
    buscaInput.addEventListener("input", function() {
      const termo = this.value.toLowerCase();
      conversaItems.forEach(item => {
        const nome = item.querySelector("h3").textContent.toLowerCase();
        const email = item.querySelector("p").textContent.toLowerCase();
        if (nome.includes(termo) || email.includes(termo)) {
          item.style.display = "block";
        } else {
          item.style.display = "none";
        }
      });
    });
    
    // Navegação entre conversas
    conversaItems.forEach(item => {
      item.addEventListener("click", function() {
        const clienteId = this.dataset.clienteId;
        window.location.href = "chat.php?cliente_id=" + clienteId;
      });
    });
    
    // Envio de mensagem
    const formEnviar = document.getElementById("form-enviar-mensagem");
    if (formEnviar) {
      formEnviar.addEventListener("submit", function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const mensagem = formData.get("mensagem");
        const canalId = formData.get("canal_id");
        const clienteId = formData.get("cliente_id");
        
        if (!mensagem.trim() || !canalId) {
          alert("Por favor, selecione um canal e digite uma mensagem.");
          return;
        }
        
        // Enviar via AJAX
        fetch("chat_enviar.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            cliente_id: clienteId,
            canal_id: canalId,
            mensagem: mensagem
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Recarregar a página para mostrar a nova mensagem
            window.location.reload();
          } else {
            alert("Erro ao enviar mensagem: " + (data.error || "Erro desconhecido"));
          }
        })
        .catch(error => {
          console.error("Erro:", error);
          alert("Erro ao enviar mensagem. Tente novamente.");
        });
      });
    }
    
    // Auto-scroll para a última mensagem
    const chatMessages = document.getElementById("chat-messages");
    if (chatMessages) {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
  });
  </script>';
}
?> 