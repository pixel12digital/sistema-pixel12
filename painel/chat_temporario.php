<?php
/**
 * CHAT TEMPORÁRIO
 * 
 * Versão que funciona sem banco de dados quando há problemas de conexão
 */

require_once __DIR__ . '/../config.php';

$page = 'chat_temporario.php';
$page_title = 'Chat Centralizado (Modo Temporário)';
$custom_header = '';

function render_content() {
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
          <div class="robot-status-text" id="robotStatusText" style="font-size: 0.8rem; color: var(--text-secondary);">Modo Temporário - Banco Indisponível</div>
          <button class="robot-connect-btn" id="robotConnectBtn" onclick="gerenciarRobo()" style="width: 100%; margin-top: 0.5rem; padding: 0.5rem; border: 1px solid var(--border-color); background: var(--background-white); border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
            Tentar Reconectar
          </button>
        </div>
        
        <!-- Aviso de modo temporário -->
        <div style="margin-top: 1rem; padding: 0.75rem; border-radius: 8px; background: #fef3c7; border: 1px solid #f59e0b; color: #92400e;">
          <div style="font-weight: 600; margin-bottom: 0.5rem;">⚠️ Modo Temporário</div>
          <div style="font-size: 0.8rem;">
            O banco de dados está temporariamente indisponível devido ao limite de conexões.<br>
            As mensagens estão sendo salvas localmente.
          </div>
        </div>
      </div>
      
      <div class="chat-conversations" id="listaConversas">
        <!-- Conversas carregadas via JavaScript -->
        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
          <div style="font-size: 2rem; margin-bottom: 1rem;">📱</div>
          <h3>Carregando conversas...</h3>
          <p>Buscando mensagens do arquivo local</p>
        </div>
      </div>
      
      <!-- Divisor redimensionável 1 -->
      <div class="resize-handle resize-handle-1" data-resize="1"></div>
    </div>
    
    <!-- Coluna 2: Detalhes do Cliente -->
    <div class="client-details-column">
      <div class="client-details-empty">
        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
          <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
          <h3>Selecione um cliente</h3>
          <p>Escolha uma conversa para ver os detalhes do cliente</p>
        </div>
      </div>
      
      <!-- Divisor redimensionável 2 -->
      <div class="resize-handle resize-handle-2" data-resize="2"></div>
    </div>
    
    <!-- Coluna 3: Conversas/Histórico + Campo de envio -->
    <div class="chat-messages-column">
      <!-- Estado vazio -->
      <div style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; color: var(--text-secondary);">
        <div style="font-size: 4rem; margin-bottom: 1rem;">💬</div>
        <h2 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">Selecione uma conversa</h2>
        <p style="margin: 0; text-align: center;">Escolha uma conversa da lista ao lado para começar a conversar</p>
      </div>
    </div>
    
    <!-- Indicador de redimensionamento -->
    <div class="resize-indicator" id="resize-indicator"></div>
  </div>
  
  <script>
  // Funções JavaScript para modo temporário
  
  // Carregar conversas do arquivo local
  function carregarConversasTemporarias() {
    fetch('api/conversas_temporarias.php')
      .then(response => response.json())
      .then(data => {
        const listaConversas = document.getElementById('listaConversas');
        listaConversas.innerHTML = '';
        
        if (data.conversas && data.conversas.length > 0) {
          data.conversas.forEach(conv => {
            const div = document.createElement('div');
            div.className = 'conversation-item';
            div.setAttribute('data-cliente-id', conv.cliente_id);
            div.onclick = () => carregarClienteTemporario(conv.cliente_id, conv.nome);
            
            div.innerHTML = `
              <div class="conversation-avatar">
                ${conv.nome.charAt(0).toUpperCase()}
              </div>
              <div class="conversation-content">
                <div class="conversation-header">
                  <span class="conversation-name">${conv.nome}</span>
                  <span class="conversation-time">${conv.ultima_data}</span>
                </div>
                <div class="conversation-meta">
                  <span class="conversation-tag">${conv.canal_nome || 'Canal'}</span>
                  <span class="conversation-preview">
                    ${conv.nao_lidas > 0 ? `<strong>${conv.nao_lidas} nova${conv.nao_lidas > 1 ? 's' : ''} mensagem${conv.nao_lidas > 1 ? 's' : ''}</strong>` : conv.ultima_mensagem}
                  </span>
                </div>
              </div>
              ${conv.nao_lidas > 0 ? `<div class="unread-badge">${conv.nao_lidas}</div>` : ''}
            `;
            
            listaConversas.appendChild(div);
          });
        } else {
          listaConversas.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
              <div style="font-size: 2rem; margin-bottom: 1rem;">📱</div>
              <h3>Nenhuma conversa encontrada</h3>
              <p>As mensagens serão carregadas quando o banco estiver disponível</p>
            </div>
          `;
        }
      })
      .catch(error => {
        console.error('Erro ao carregar conversas:', error);
        document.getElementById('listaConversas').innerHTML = `
          <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
            <div style="font-size: 2rem; margin-bottom: 1rem;">❌</div>
            <h3>Erro ao carregar conversas</h3>
            <p>Verifique a conexão e tente novamente</p>
          </div>
        `;
      });
  }
  
  // Carregar cliente temporário
  function carregarClienteTemporario(clienteId, nome) {
    fetch(`api/mensagens_temporarias.php?cliente_id=${clienteId}`)
      .then(response => response.json())
      .then(data => {
        // Atualizar área de mensagens
        const chatMessages = document.querySelector('.chat-messages-column');
        chatMessages.innerHTML = `
          <div class="chat-messages-header">
            <h2>💬 Conversa com ${nome}</h2>
          </div>
          
          <div class="chat-messages" id="chat-messages">
            ${data.mensagens.map(msg => `
              <div class="message ${msg.direcao === 'recebido' ? 'received' : 'sent'}">
                <div class="message-bubble">
                  ${msg.mensagem}
                  <div class="message-time">
                    ${msg.data_hora}
                    ${msg.direcao === 'enviado' ? '<span class="message-status">✔</span>' : ''}
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
          
          <div class="chat-input-area">
            <form id="form-chat-enviar" enctype="multipart/form-data">
              <input type="hidden" name="cliente_id" value="${clienteId}">
              <input type="hidden" name="canal_id" value="36">
              
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
                
              <button type="submit" class="chat-send-btn">
                Enviar
                <span>➤</span>
              </button>
            </form>
          </div>
        `;
        
        // Configurar envio de mensagem
        document.getElementById('form-chat-enviar').onsubmit = function(e) {
          e.preventDefault();
          enviarMensagemTemporaria(clienteId);
        };
        
        // Marcar conversa como ativa
        document.querySelectorAll('.conversation-item').forEach(item => {
          item.classList.remove('active');
        });
        document.querySelector(`[data-cliente-id="${clienteId}"]`).classList.add('active');
      })
      .catch(error => {
        console.error('Erro ao carregar mensagens:', error);
      });
  }
  
  // Enviar mensagem temporária
  function enviarMensagemTemporaria(clienteId) {
    const form = document.getElementById('form-chat-enviar');
    const formData = new FormData(form);
    
    fetch('api/enviar_mensagem_temporaria.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Recarregar mensagens
        carregarClienteTemporario(clienteId, data.nome);
        form.reset();
      } else {
        alert('Erro ao enviar mensagem: ' + data.error);
      }
    })
    .catch(error => {
      console.error('Erro ao enviar mensagem:', error);
      alert('Erro ao enviar mensagem. Tente novamente.');
    });
  }
  
  // Funções auxiliares
  function filtrarConversas(tipo) {
    document.querySelectorAll('.chat-tab').forEach(tab => tab.classList.remove('active'));
    const targetTab = document.querySelector(`[onclick="filtrarConversas('${tipo}')"]`);
    if (targetTab) {
      targetTab.classList.add('active');
    }
  }
  
  function filtrarConversasPorNumero(valor) {
    // Implementar filtro se necessário
  }
  
  function limparFiltroConversa() {
    document.getElementById('buscaConversa').value = '';
    filtrarConversasPorNumero('');
  }
  
  function abrirNovaConversa() {
    alert('Funcionalidade temporariamente indisponível. Aguarde o banco de dados estar disponível.');
  }
  
  function gerenciarRobo() {
    alert('Tentando reconectar ao banco de dados...\n\nSe o problema persistir, aguarde 1 hora para resetar o limite de conexões.');
  }
  
  // Carregar conversas ao iniciar
  document.addEventListener('DOMContentLoaded', function() {
    carregarConversasTemporarias();
    
    // Atualizar a cada 5 minutos (OTIMIZADO para economizar conexões)
    setInterval(carregarConversasTemporarias, 300000); // 5 minutos
  });
  </script>
  
  <?php
}

// Incluir template
require_once 'template.php';
?> 