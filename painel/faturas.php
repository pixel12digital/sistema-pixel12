<?php
$page = 'faturas.php';
$page_title = 'Faturas';
$custom_header = '<button type="button" class="invoices-new-btn bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md flex items-center gap-2" id="btn-config-asaas"><span>🔑 Configurar API</span></button> <button type="button" class="invoices-new-btn bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md flex items-center gap-2" id="btn-sincronizar"><span>🔄 Sincronizar com Asaas</span></button> <button type="button" class="invoices-new-btn bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md flex items-center gap-2">+ Nova Fatura</button>';

function render_content() {
?>
<!-- Status da API do Asaas (Otimizado) -->
<section class="api-status-section bg-white shadow-sm p-4 mb-4">
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-semibold text-gray-800 mb-2">🔍 Status da API do Asaas</h3>
      <div id="status-chave-asaas-container">
        <div class="status-chave-asaas status-valido">
          <div class="status-header">
            <span class="status-icone">⏳</span>
            <span class="status-texto">Carregando status...</span>
          </div>
        </div>
      </div>
    </div>
    <div class="flex gap-2">
      <button id="btn-verificar-chave" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
        🔍 Verificar Agora
      </button>
      <button id="btn-estatisticas" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm">
        📊 Estatísticas
      </button>
      <a href="configuracao_ia.php" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-md text-sm inline-flex items-center gap-1">
        🤖 Configurar IA
      </a>
    </div>
  </div>
  
  <!-- Container para alertas -->
  <div id="alertas-chave-asaas-container"></div>
  
  <!-- Estatísticas detalhadas (inicialmente oculto) -->
  <div id="estatisticas-detalhadas" style="display: none; margin-top: 16px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
    <h4 class="font-semibold text-gray-800 mb-3">📊 Estatísticas do Monitoramento</h4>
    <div id="estatisticas-content" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <!-- Estatísticas serão preenchidas via JavaScript -->
    </div>
  </div>
</section>

<!-- Header -->
<section class="invoices-filters bg-white shadow-sm p-4">
  <div class="grid gap-4 md:grid-cols-4">
    <div>
      <label for="filter-status" class="block text-xs font-medium">Status</label>
      <select id="filter-status" class="filter-status mt-1 w-full rounded-md border-gray-300">
        <option value="">Todos</option>
        <option value="PENDING">Aguardando pagamento</option>
        <option value="OVERDUE">Vencida</option>
        <option value="RECEIVED">Recebida</option>
        <option value="CONFIRMED">Confirmada</option>
      </select>
    </div>
    <div class="filtro-datas">
      <label class="block text-xs font-medium">Vencimento</label>
      <div style="display: flex; gap: 8px;">
        <input type="date" class="filter-date-due-inicio" placeholder="dd/mm/aaaa" />
        <input type="date" class="filter-date-due-fim" placeholder="dd/mm/aaaa" />
      </div>
    </div>
    <div>
      <label for="filter-client" class="block text-xs font-medium">Cliente</label>
      <input id="filter-client" type="text" class="filter-client mt-1 w-full rounded-md border-gray-300" placeholder="Buscar cliente..." />
    </div>
    <div class="flex items-end">
      <button id="btn-aplicar-filtros" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded-md">Aplicar Filtros</button>
    </div>
  </div>
</section>
<!-- Summary -->
<section class="invoices-summary grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
  <div class="summary-card summary-open bg-white p-4 rounded-lg shadow-sm">
    <p class="text-xs uppercase text-gray-500">Em aberto</p>
    <p class="text-xl font-semibold mt-1">R$ 0,00</p>
  </div>
  <div class="summary-card summary-pending bg-white p-4 rounded-lg shadow-sm">
    <p class="text-xs uppercase text-gray-500">Pendentes</p>
    <p class="text-xl font-semibold mt-1">0 (R$ 0,00)</p>
  </div>
  <div class="summary-card summary-overdue bg-white p-4 rounded-lg shadow-sm">
    <p class="text-xs uppercase text-gray-500">Vencidas</p>
    <p class="text-xl font-semibold mt-1">0 (R$ 0,00)</p>
  </div>
  <div class="summary-card summary-monitoring bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs uppercase text-purple-100">Monitoramento</p>
        <p class="text-xl font-semibold mt-1" id="monitoring-stats">0 clientes</p>
      </div>
      <div class="text-2xl">📊</div>
    </div>
    <div class="mt-2 text-xs text-purple-100" id="monitoring-details">
      R$ 0,00 vencidos
    </div>
  </div>
</section>
<!-- Invoices Table -->
<section class="p-4 overflow-x-auto">
  <table class="invoices-table w-full text-sm whitespace-nowrap">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Nº</th>
        <th class="px-3 py-2">Cliente</th>
        <th class="px-3 py-2">Contato Principal</th>
        <th class="px-3 py-2">Valor</th>
        <th class="px-3 py-2">Vencimento</th>
        <th class="px-3 py-2">Status</th>
        <th class="px-3 py-2">Última Interação</th>
        <th class="px-3 py-2">Status Envio</th>
        <th class="px-3 py-2">Monitoramento</th>
        <th class="px-3 py-2">Ações</th>
      </tr>
    </thead>
    <tbody id="invoices-tbody">
      <!-- Linhas serão preenchidas via JS -->
    </tbody>
  </table>
  <div id="pagination" class="flex justify-center items-center gap-2 mt-4"></div>
  <div id="total-faturas-info" class="text-sm text-gray-600 mt-2 flex justify-end"></div>
</section>
<!-- Drawer Details -->
<aside class="drawer-invoice-details fixed top-0 right-0 w-full sm:w-96 h-full bg-white shadow-xl transform translate-x-full transition-transform z-50">
  <header class="details-header p-4 border-b flex items-center justify-between">
    <h2 class="text-lg font-semibold">Fatura</h2>
    <button aria-label="Fechar detalhes" class="close-details text-gray-400 hover:text-gray-600">&times;</button>
  </header>
  <div class="details-client p-4 border-b"></div>
  <div class="details-items p-4 border-b"></div>
  <div class="details-totals p-4 border-b"></div>
  <div class="details-actions p-4 flex gap-2"></div>
</aside>
<!-- Toast container -->
<div class="fixed top-4 right-4 space-y-2 z-50" id="toast-container"></div>
<!-- Modal de log completo (inicialmente oculto) -->
<div id="modal-log-completo" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:10000;align-items:center;justify-content:center;">
  <div style="background:#fff;padding:32px 24px;border-radius:12px;min-width:400px;max-width:90vw;max-height:90vh;overflow:auto;position:relative;box-shadow:0 8px 32px #0003;">
    <button id="btn-fechar-log-completo" style="position:absolute;top:16px;right:20px;font-size:1.5em;background:none;border:none;color:#7c3aed;cursor:pointer;">&times;</button>
    <h3 style="font-size:1.2em;font-weight:bold;margin-bottom:18px;color:#7c3aed;">Log completo da sincronização</h3>
    <pre id="log-completo-area" style="background:#f3f4f6;padding:16px;border-radius:8px;max-height:65vh;overflow:auto;font-size:0.97em;color:#222;white-space:pre-wrap;"></pre>
  </div>
</div>
<!-- Modal de progresso de sincronização -->
<div id="modal-sync-progress" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.8);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;padding:0;border-radius:16px;min-width:500px;max-width:90vw;max-height:85vh;overflow:hidden;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
    <!-- Header do Modal -->
    <div style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;padding:24px 32px;position:relative;">
      <button id="btn-fechar-sync-modal" style="position:absolute;top:20px;right:24px;background:none;border:none;color:#fff;font-size:24px;cursor:pointer;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">&times;</button>
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;">🔄</div>
        <div>
          <h3 style="font-size:1.4em;font-weight:700;margin:0;">Sincronização com Asaas</h3>
          <p style="margin:4px 0 0 0;opacity:0.9;font-size:0.95em;">Acompanhe o progresso em tempo real</p>
        </div>
      </div>
    </div>
    <!-- Bloco de resumo do erro (inicialmente oculto) -->
    <div id="sync-error-summary" style="display:none;background:#fef2f2;border-bottom:2px solid #fecaca;padding:18px 32px 10px 32px;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="font-size:2em;color:#dc2626;">❌</div>
        <div>
          <div style="font-weight:700;color:#dc2626;font-size:1.1em;">Erro na sincronização</div>
          <div id="sync-error-message" style="color:#b91c1c;font-size:0.98em;margin-top:2px;"></div>
        </div>
      </div>
      <div style="margin-top:8px;">
        <button id="btn-ver-log-completo" style="background:#fff;color:#7c3aed;border:1px solid #7c3aed;padding:6px 18px;border-radius:6px;font-weight:600;cursor:pointer;transition:background 0.2s;">Ver log completo</button>
      </div>
    </div>
    <!-- Conteúdo do Modal -->
    <div style="padding:32px;">
      <!-- Status Geral -->
      <div id="sync-status-card" style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;transition:all 0.3s;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
          <div id="status-icon" style="width:32px;height:32px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;">⏳</div>
          <div>
            <h4 id="status-title" style="margin:0;font-weight:600;color:#1e293b;">Iniciando sincronização...</h4>
            <p id="status-description" style="margin:4px 0 0 0;color:#64748b;font-size:0.9em;">Preparando conexão com Asaas</p>
          </div>
        </div>
        <!-- Barra de Progresso -->
        <div style="margin-bottom:16px;">
          <div style="width:100%;background:#e5e7eb;border-radius:8px;height:18px;overflow:hidden;">
            <div id="sync-progress-bar" style="height:18px;width:0%;background:#7c3aed;transition:width 0.4s;"></div>
          </div>
          <div id="sync-progress-label" style="font-size:0.98em;color:#7c3aed;margin-top:4px;font-weight:500;">0%</div>
        </div>
        <!-- Estatísticas -->
        <div id="sync-stats" style="display:flex;gap:24px;margin-bottom:10px;">
          <div class="summary-card" style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:10px 15px;display:flex;align-items:center;gap:8px;">
            <div style="width:24px;height:24px;background:#3b82f6;display:flex;align-items:center;justify-content:center;border-radius:50%;color:#fff;font-size:14px;">💾</div>
            <div>
              <p class="text-xs uppercase text-gray-500">Processados</p>
              <p class="text-xl font-semibold mt-1" id="stats-processed">0</p>
            </div>
          </div>
          <div class="summary-card" style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:10px 15px;display:flex;align-items:center;gap:8px;">
            <div style="width:24px;height:24px;background:#059669;display:flex;align-items:center;justify-content:center;border-radius:50%;color:#fff;font-size:14px;">✅</div>
            <div>
              <p class="text-xs uppercase text-gray-500">Atualizados</p>
              <p class="text-xl font-semibold mt-1" id="stats-updated">0</p>
            </div>
          </div>
          <div class="summary-card" style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:10px 15px;display:flex;align-items:center;gap:8px;">
            <div style="width:24px;height:24px;background:#dc2626;display:flex;align-items:center;justify-content:center;border-radius:50%;color:#fff;font-size:14px;">❌</div>
            <div>
              <p class="text-xs uppercase text-gray-500">Erros</p>
              <p class="text-xl font-semibold mt-1" id="stats-errors">0</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Logs -->
      <div id="sync-logs-area" style="background:#f3f4f6;border-radius:8px;padding:16px;max-height:180px;overflow:auto;font-size:0.97em;color:#222;white-space:pre-wrap;"></div>
    </div>
  </div>
</div>
<!-- Modal de detalhes do cliente -->
<div id="modal-cliente-detalhes" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);z-index:10001;align-items:center;justify-content:center;">
  <div id="modal-cliente-detalhes-content" style="background:#fff;padding:32px 24px;border-radius:12px;min-width:400px;max-width:95vw;max-height:90vh;overflow:auto;position:relative;box-shadow:0 8px 32px #0003;">
    <button id="btn-fechar-modal-cliente" style="position:absolute;top:16px;right:20px;font-size:1.5em;background:none;border:none;color:#7c3aed;cursor:pointer;">&times;</button>
    <div id="modal-cliente-detalhes-body">Carregando...</div>
  </div>
</div>
<!-- Modal de configuração da API do Asaas -->
<div id="modal-config-asaas" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.8);z-index:10002;align-items:center;justify-content:center;overflow-y:auto;padding:20px;">
  <div style="background:#fff;padding:0;border-radius:16px;min-width:600px;max-width:90vw;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.3);margin:auto;">
    <!-- Header do Modal -->
    <div style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;padding:24px 32px;position:relative;">
      <button id="btn-fechar-config-asaas" style="position:absolute;top:20px;right:24px;background:none;border:none;color:#fff;font-size:24px;cursor:pointer;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='transparent'">&times;</button>
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;">🔑</div>
        <div>
          <h3 style="font-size:1.4em;font-weight:700;margin:0;">Configuração da API do Asaas</h3>
          <p style="margin:4px 0 0 0;opacity:0.9;font-size:0.95em;">Teste e configure sua chave da API</p>
        </div>
      </div>
    </div>

    <!-- Conteúdo do Modal -->
    <div style="padding:32px;">
      
      <!-- Status da Chave Atual -->
      <div id="status-chave-atual" style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
          <div id="status-chave-icon" style="width:32px;height:32px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;">⏳</div>
          <div>
            <h4 id="status-chave-title" style="margin:0;font-weight:600;color:#1e293b;">Verificando chave atual...</h4>
            <p id="status-chave-description" style="margin:4px 0 0 0;color:#64748b;font-size:0.9em;">Testando conexão com Asaas</p>
          </div>
        </div>
        
        <!-- Chave Atual (mascarada) -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-size:0.8em;color:#64748b;margin-bottom:4px;">Chave Atual</div>
              <div id="chave-atual-display" style="font-family:monospace;font-size:0.9em;color:#1e293b;">Carregando...</div>
            </div>
            <button id="btn-testar-chave-atual" style="background:#7c3aed;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:0.9em;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">Testar</button>
          </div>
        </div>
        
        <!-- Resultado do Teste -->
        <div id="resultado-teste" style="display:none;padding:12px;border-radius:8px;margin-top:12px;">
          <div id="resultado-teste-content"></div>
        </div>
      </div>

      <!-- Formulário para Nova Chave -->
      <div style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h4 style="margin:0 0 16px 0;font-weight:600;color:#1e293b;">Adicionar Nova Chave</h4>
        
        <form id="form-nova-chave">
          <div style="margin-bottom:16px;">
            <label for="nova-chave-input" style="display:block;margin-bottom:6px;font-weight:600;color:#374151;font-size:0.9em;">Nova Chave da API:</label>
            <input type="text" id="nova-chave-input" name="nova_chave" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-family:monospace;font-size:0.9em;" placeholder="$aact_test_... ou $aact_prod_..." required>
            <div style="font-size:0.8em;color:#6b7280;margin-top:4px;">A chave deve começar com $aact_test_ (teste) ou $aact_prod_ (produção)</div>
          </div>
          
          <div style="margin-bottom:16px;">
            <label for="tipo-chave-select" style="display:block;margin-bottom:6px;font-weight:600;color:#374151;font-size:0.9em;">Tipo de Chave:</label>
            <select id="tipo-chave-select" name="tipo_chave" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:0.9em;">
              <option value="test">Teste (Sandbox) - Recomendado para desenvolvimento</option>
              <option value="prod">Produção - Use apenas em ambiente de produção</option>
            </select>
          </div>
          
          <div style="display:flex;gap:12px;">
            <button type="button" id="btn-testar-nova-chave" style="background:#f59e0b;color:#fff;border:none;padding:12px 20px;border-radius:8px;font-weight:600;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">🧪 Testar Nova Chave</button>
            <button type="submit" id="btn-aplicar-nova-chave" style="background:#059669;color:#fff;border:none;padding:12px 20px;border-radius:8px;font-weight:600;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">✅ Aplicar Nova Chave</button>
          </div>
        </form>
        
        <!-- Resultado do Teste da Nova Chave -->
        <div id="resultado-teste-nova" style="display:none;padding:12px;border-radius:8px;margin-top:16px;">
          <div id="resultado-teste-nova-content"></div>
        </div>
      </div>

      <!-- Informações e Links -->
      <div style="background:#f0f9ff;border:1px solid #0ea5e9;border-radius:8px;padding:16px;">
        <h4 style="margin:0 0 12px 0;color:#0c4a6e;font-size:1em;">📚 Como obter sua chave da API:</h4>
        <ol style="margin:0;padding-left:20px;color:#0c4a6e;font-size:0.9em;">
          <li>Acesse <a href="https://www.asaas.com/" target="_blank" style="color:#7c3aed;">www.asaas.com</a></li>
          <li>Faça login na sua conta</li>
          <li>Vá em <strong>Configurações</strong> → <strong>API</strong></li>
          <li>Copie a <strong>Chave de Teste</strong> ou <strong>Chave de Produção</strong></li>
        </ol>
        <div style="margin-top:12px;font-size:0.8em;color:#0369a1;">
          <strong>⚠️ Importante:</strong> Use chave de teste para desenvolvimento local para evitar cobranças reais.
        </div>
      </div>

      <!-- Botões de Ação -->
      <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end;">
        <button id="btn-fechar-config" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Fechar</button>
        <button id="btn-testar-sincronizacao" style="background:#7c3aed;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">🔄 Testar Sincronização</button>
      </div>

    </div>
  </div>
</div>
<script src="assets/invoices.js"></script>
<script src="assets/cobrancas.js"></script>
<script src="assets/faturas_monitoramento.js"></script>
<script src="assets/faturas_monitoramento_integracao.js"></script>
<script>
function excluirCobranca(asaasPaymentId, cobrancaId) {
  if (!confirm('Tem certeza que deseja excluir esta cobrança?')) return;
  fetch('api/excluir_cobranca.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ asaas_payment_id: asaasPaymentId, cobranca_id: cobrancaId })
  })
  .then(r => r.json())
  .then(resp => {
    if (resp.success) {
      alert('Cobrança excluída com sucesso!');
      location.reload();
    } else {
      alert('Erro ao excluir cobrança: ' + (resp.error || 'Erro desconhecido'));
    }
  })
  .catch(() => {
    alert('Erro ao conectar ao servidor.');
  });
}

function marcarRecebida(asaasPaymentId, cobrancaId) {
  if (!confirm('Confirmar recebimento desta cobrança?')) return;
  fetch('api/marcar_recebida.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ asaas_payment_id: asaasPaymentId, cobranca_id: cobrancaId })
  })
  .then(r => r.json())
  .then(resp => {
    if (resp.success) {
      alert('Cobrança marcada como recebida!');
      location.reload();
    } else {
      alert('Erro ao marcar como recebida: ' + (resp.error || 'Erro desconhecido'));
    }
  })
  .catch(() => {
    alert('Erro ao conectar ao servidor.');
  });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Verificar se o script foi carregado
  if (typeof ClienteMonitoramento !== 'undefined') {
    window.clienteMonitoramento = new ClienteMonitoramento();
  }
  
  const btnSync = document.getElementById('btn-sincronizar');
  const btnConfigAsaas = document.getElementById('btn-config-asaas');
  const modal = document.getElementById('modal-sync-progress');
  const modalConfigAsaas = document.getElementById('modal-config-asaas');
  const btnFechar = document.getElementById('btn-fechar-sync-modal');
  const btnFecharConfigAsaas = document.getElementById('btn-fechar-config-asaas');
  const btnFecharConfig = document.getElementById('btn-fechar-config');
  const btnTestarSincronizacao = document.getElementById('btn-testar-sincronizacao');
  const syncLogsArea = document.getElementById('sync-logs-area');
  const syncProgressBar = document.getElementById('sync-progress-bar');
  const syncProgressLabel = document.getElementById('sync-progress-label');
  const syncStatusCard = document.getElementById('sync-status-card');
  const statusIcon = document.getElementById('status-icon');
  const statusTitle = document.getElementById('status-title');
  const statusDescription = document.getElementById('status-description');
  const syncErrorSummary = document.getElementById('sync-error-summary');
  const syncErrorMessage = document.getElementById('sync-error-message');
  const logCompletoArea = document.getElementById('log-completo-area');
  const modalLogCompleto = document.getElementById('modal-log-completo');
  const btnFecharLogCompleto = document.getElementById('btn-fechar-log-completo');
  const btnVerLogCompleto = document.getElementById('btn-ver-log-completo');
  let syncInterval = null;

  // Elementos do modal de configuração
  const statusChaveIcon = document.getElementById('status-chave-icon');
  const statusChaveTitle = document.getElementById('status-chave-title');
  const statusChaveDescription = document.getElementById('status-chave-description');
  const chaveAtualDisplay = document.getElementById('chave-atual-display');
  const btnTestarChaveAtual = document.getElementById('btn-testar-chave-atual');
  const resultadoTeste = document.getElementById('resultado-teste');
  const resultadoTesteContent = document.getElementById('resultado-teste-content');
  const formNovaChave = document.getElementById('form-nova-chave');
  const novaChaveInput = document.getElementById('nova-chave-input');
  const tipoChaveSelect = document.getElementById('tipo-chave-select');
  const btnTestarNovaChave = document.getElementById('btn-testar-nova-chave');
  const btnAplicarNovaChave = document.getElementById('btn-aplicar-nova-chave');
  const resultadoTesteNova = document.getElementById('resultado-teste-nova');
  const resultadoTesteNovaContent = document.getElementById('resultado-teste-nova-content');

  function abrirModalSync() {
    modal.style.display = 'flex';
    syncLogsArea.innerHTML = '<div style="color:#64748b;font-style:italic;">Aguardando início da sincronização...</div>';
    syncProgressBar.style.width = '0%';
    syncProgressLabel.textContent = '0%';
    statusIcon.textContent = '⏳';
    statusIcon.style.background = '#3b82f6';
    statusTitle.textContent = 'Iniciando sincronização corrigida...';
    statusDescription.textContent = 'Preparando conexão com Asaas';
    syncErrorSummary.style.display = 'none';
    syncErrorMessage.textContent = '';
    if (syncInterval) clearInterval(syncInterval);
  }

  function fecharModalSync() {
    modal.style.display = 'none';
    if (syncInterval) clearInterval(syncInterval);
  }

  function atualizarProgresso(percent) {
    syncProgressBar.style.width = percent + '%';
    syncProgressLabel.textContent = percent + '%';
  }

  function adicionarLog(msg, tipo) {
    const div = document.createElement('div');
    div.textContent = msg;
    if (tipo === 'error') div.style.color = '#dc2626';
    if (tipo === 'success') div.style.color = '#059669';
    if (tipo === 'warn') div.style.color = '#d97706';
    syncLogsArea.appendChild(div);
    syncLogsArea.scrollTop = syncLogsArea.scrollHeight;
  }

  function atualizarStatus(icon, title, desc, bg) {
    statusIcon.textContent = icon;
    statusIcon.style.background = bg;
    statusTitle.textContent = title;
    statusDescription.textContent = desc;
  }

  function mostrarErroSync(msg) {
    syncErrorSummary.style.display = 'block';
    syncErrorMessage.textContent = msg;
    atualizarStatus('❌', 'Erro na sincronização', msg, '#dc2626');
  }

  function carregarLogCompleto() {
    fetch('api/sync_status.php?all=1')
      .then(r => r.json())
      .then(logs => {
        logCompletoArea.textContent = (logs.lines || []).join('\n');
      });
    modalLogCompleto.style.display = 'flex';
  }

  function abrirModalConfigAsaas() {
    modalConfigAsaas.style.display = 'flex';
    carregarChaveAtual();
    testarChaveAtual();
  }

  function fecharModalConfigAsaas() {
    modalConfigAsaas.style.display = 'none';
    resultadoTeste.style.display = 'none';
    resultadoTesteNova.style.display = 'none';
  }

  function carregarChaveAtual() {
    fetch('api/get_asaas_config.php')
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const chave = data.chave;
          const tipo = chave.includes('_test_') ? 'TESTE' : 'PRODUÇÃO';
          const chaveMascarada = chave.substring(0, 20) + '...' + chave.substring(chave.length - 10);
          chaveAtualDisplay.textContent = `${tipo}: ${chaveMascarada}`;
          chaveAtualDisplay.setAttribute('data-chave', chave); // Salva a chave completa
        } else {
          chaveAtualDisplay.textContent = 'Erro ao carregar chave';
          chaveAtualDisplay.removeAttribute('data-chave');
        }
      })
      .catch(error => {
        console.error('Erro ao carregar chave:', error);
        chaveAtualDisplay.textContent = 'Erro ao carregar chave';
        chaveAtualDisplay.removeAttribute('data-chave');
      });
  }

  function testarChaveAtual() {
    statusChaveIcon.textContent = '⏳';
    statusChaveIcon.style.background = '#3b82f6';
    statusChaveTitle.textContent = 'Testando chave atual...';
    statusChaveDescription.textContent = 'Verificando conexão com Asaas';
    resultadoTeste.style.display = 'none';

    fetch('api/test_asaas_key.php')
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          statusChaveIcon.textContent = '✅';
          statusChaveIcon.style.background = '#059669';
          statusChaveTitle.textContent = 'Chave válida!';
          statusChaveDescription.textContent = 'Conexão com Asaas estabelecida';
          
          resultadoTeste.style.display = 'block';
          resultadoTeste.style.background = '#f0fdf4';
          resultadoTeste.style.border = '1px solid #bbf7d0';
          resultadoTesteContent.innerHTML = `
            <div style="color: #059669; font-weight: 600;">✅ Chave da API válida!</div>
            <div style="color: #047857; font-size: 0.9em; margin-top: 4px;">Conexão estabelecida com sucesso</div>
          `;
        } else {
          statusChaveIcon.textContent = '❌';
          statusChaveIcon.style.background = '#dc2626';
          statusChaveTitle.textContent = 'Chave inválida';
          statusChaveDescription.textContent = data.error || 'Erro na conexão com Asaas';
          
          resultadoTeste.style.display = 'block';
          resultadoTeste.style.background = '#fef2f2';
          resultadoTeste.style.border = '1px solid #fecaca';
          resultadoTesteContent.innerHTML = `
            <div style="color: #dc2626; font-weight: 600;">❌ Chave da API inválida</div>
            <div style="color: #b91c1c; font-size: 0.9em; margin-top: 4px;">${data.error || 'Erro na conexão'}</div>
          `;
        }
      })
      .catch(error => {
        console.error('Erro ao testar chave:', error);
        statusChaveIcon.textContent = '❌';
        statusChaveIcon.style.background = '#dc2626';
        statusChaveTitle.textContent = 'Erro no teste';
        statusChaveDescription.textContent = 'Erro ao conectar com o servidor';
        
        resultadoTeste.style.display = 'block';
        resultadoTeste.style.background = '#fef2f2';
        resultadoTeste.style.border = '1px solid #fecaca';
        resultadoTesteContent.innerHTML = `
          <div style="color: #dc2626; font-weight: 600;">❌ Erro no teste</div>
          <div style="color: #b91c1c; font-size: 0.9em; margin-top: 4px;">Erro ao conectar com o servidor</div>
        `;
      });
  }

  function testarNovaChave() {
    const novaChave = novaChaveInput.value.trim();
    
    if (!novaChave) {
      alert('Por favor, insira uma chave da API');
      return;
    }
    
    if (!novaChave.startsWith('$aact_')) {
      alert('A chave deve começar com $aact_test_ ou $aact_prod_');
      return;
    }
    
    btnTestarNovaChave.disabled = true;
    btnTestarNovaChave.textContent = 'Testando...';
    resultadoTesteNova.style.display = 'none';
    
    fetch('api/test_asaas_key.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ chave: novaChave })
    })
    .then(r => r.json())
    .then(data => {
      btnTestarNovaChave.disabled = false;
      btnTestarNovaChave.textContent = '🧪 Testar Nova Chave';
      
      resultadoTesteNova.style.display = 'block';
      
      if (data.success) {
        resultadoTesteNova.style.background = '#f0fdf4';
        resultadoTesteNova.style.border = '1px solid #bbf7d0';
        resultadoTesteNovaContent.innerHTML = `
          <div style="color: #059669; font-weight: 600;">✅ Nova chave válida!</div>
          <div style="color: #047857; font-size: 0.9em; margin-top: 4px;">Conexão estabelecida com sucesso</div>
        `;
      } else {
        resultadoTesteNova.style.background = '#fef2f2';
        resultadoTesteNova.style.border = '1px solid #fecaca';
        resultadoTesteNovaContent.innerHTML = `
          <div style="color: #dc2626; font-weight: 600;">❌ Nova chave inválida</div>
          <div style="color: #b91c1c; font-size: 0.9em; margin-top: 4px;">${data.error || 'Erro na conexão'}</div>
        `;
      }
    })
    .catch(error => {
      console.error('Erro ao testar nova chave:', error);
      btnTestarNovaChave.disabled = false;
      btnTestarNovaChave.textContent = '🧪 Testar Nova Chave';
      
      resultadoTesteNova.style.display = 'block';
      resultadoTesteNova.style.background = '#fef2f2';
      resultadoTesteNova.style.border = '1px solid #fecaca';
      resultadoTesteNovaContent.innerHTML = `
        <div style="color: #dc2626; font-weight: 600;">❌ Erro no teste</div>
        <div style="color: #b91c1c; font-size: 0.9em; margin-top: 4px;">Erro ao conectar com o servidor</div>
      `;
    });
  }

  function aplicarNovaChave() {
    const novaChave = novaChaveInput.value.trim();
    const tipoChave = tipoChaveSelect.value;
    
    if (!novaChave) {
      alert('Por favor, insira uma chave da API');
      return;
    }
    
    if (!novaChave.startsWith('$aact_')) {
      alert('A chave deve começar com $aact_test_ ou $aact_prod_');
      return;
    }
    
    if (!confirm('Tem certeza que deseja aplicar esta nova chave? Isso irá substituir a chave atual.')) {
      return;
    }
    
    btnAplicarNovaChave.disabled = true;
    btnAplicarNovaChave.textContent = 'Aplicando...';
    
    fetch('api/update_asaas_key.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        chave: novaChave, 
        tipo: tipoChave 
      })
    })
    .then(r => r.json())
    .then(data => {
      btnAplicarNovaChave.disabled = false;
      btnAplicarNovaChave.textContent = '✅ Aplicar Nova Chave';
      
      if (data.success) {
        alert('✅ Chave da API atualizada com sucesso!');
        carregarChaveAtual();
        testarChaveAtual();
        novaChaveInput.value = '';
        resultadoTesteNova.style.display = 'none';
      } else {
        alert('❌ Erro ao atualizar chave: ' + (data.error || 'Erro desconhecido'));
      }
    })
    .catch(error => {
      console.error('Erro ao aplicar nova chave:', error);
      btnAplicarNovaChave.disabled = false;
      btnAplicarNovaChave.textContent = '✅ Aplicar Nova Chave';
      alert('❌ Erro ao conectar com o servidor');
    });
  }

  if (btnSync) {
    btnSync.addEventListener('click', function() {
      btnSync.disabled = true;
      abrirModalSync();
      adicionarLog('🚀 Iniciando sincronização CORRIGIDA...', '');
      adicionarLog('📋 Versão: sincroniza_asaas_melhorado.php', '');
      atualizarStatus('⏳', 'Iniciando sincronização corrigida...', 'Preparando conexão com Asaas', '#3b82f6');
      
      fetch('sincronizar_asaas_ajax.php')
        .then(r => r.json())
        .then(resp => {
          if (resp.success) {
            adicionarLog('✅ Sincronização CORRIGIDA concluída com sucesso!', 'success');
            adicionarLog('📊 ' + (resp.message || 'Todos os dados foram atualizados'), 'success');
            atualizarStatus('✅', 'Sincronização corrigida concluída!', 'Todos os dados foram atualizados com sucesso', '#059669');
            atualizarProgresso(100);
          } else {
            adicionarLog('❌ Erro na sincronização corrigida: ' + (resp.error || 'Erro desconhecido'), 'error');
            mostrarErroSync(resp.error || 'Erro desconhecido ao sincronizar.');
          }
          
          // Mostrar output da sincronização se disponível
          if (resp.output) {
            adicionarLog('📄 Log da sincronização:', '');
            const linhas = resp.output.split('\n');
            linhas.forEach(linha => {
              if (linha.trim()) {
                const tipo = linha.toLowerCase().includes('erro') ? 'error' : 
                           (linha.toLowerCase().includes('sucesso') || linha.toLowerCase().includes('concluída')) ? 'success' : '';
                adicionarLog('  ' + linha, tipo);
              }
            });
          }
        })
        .catch((error) => {
          adicionarLog('❌ Erro ao conectar ao servidor: ' + error.message, 'error');
          mostrarErroSync('Erro ao conectar ao servidor!');
        })
        .finally(() => {
          btnSync.disabled = false;
        });
      
      // Atualizar logs em tempo real da versão corrigida
      let progresso = 0;
      let contadorProcessados = 0;
      let contadorAtualizados = 0;
      let contadorErros = 0;
      
      syncInterval = setInterval(() => {
        // Primeiro verificar logs da versão corrigida
        fetch('api/sync_status.php?log=sincronizacao_melhorada.log')
          .then(r => {
            if (!r.ok) {
              throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            }
            return r.json();
          })
          .then(data => {
            // Verificar se há erro na resposta
            if (data.error) {
              console.error('Erro na API:', data.message);
              return;
            }
            
            // Limpar área de logs
            syncLogsArea.innerHTML = '';
            
            // Adicionar cabeçalho da versão corrigida
            adicionarLog('🔄 Sincronização CORRIGIDA em andamento...', '');
            adicionarLog('📋 Logs da versão melhorada:', '');
            
            // Processar logs da versão corrigida
            if (data.lines && Array.isArray(data.lines)) {
              data.lines.forEach(line => {
                const tipo = line.toLowerCase().includes('[error]') ? 'error' : 
                           (line.toLowerCase().includes('[success]') || line.toLowerCase().includes('concluída')) ? 'success' : 
                           line.toLowerCase().includes('[warn]') ? 'warn' : '';
                adicionarLog(line, tipo);
              });
            }
            
            // Atualizar contadores baseado nos logs
            contadorProcessados = 0;
            contadorAtualizados = 0;
            contadorErros = 0;
            
            if (data.lines && Array.isArray(data.lines)) {
              data.lines.forEach(line => {
                const lineLower = line.toLowerCase();
                if (lineLower.includes('cliente processado com sucesso') || 
                    lineLower.includes('cobrança processada com sucesso')) {
                  contadorProcessados++;
                  contadorAtualizados++;
                } else if (lineLower.includes('[error]') || 
                          lineLower.includes('erro ao processar')) {
                  contadorErros++;
                }
              });
            }
            
            // Atualizar contadores na interface
            const statsProcessed = document.getElementById('stats-processed');
            const statsUpdated = document.getElementById('stats-updated');
            const statsErrors = document.getElementById('stats-errors');
            
            if (statsProcessed) statsProcessed.textContent = contadorProcessados;
            if (statsUpdated) statsUpdated.textContent = contadorAtualizados;
            if (statsErrors) statsErrors.textContent = contadorErros;
            
            // Se não houver logs da versão corrigida, verificar logs antigos
            if (!data.lines || data.lines.length === 0) {
              fetch('api/sync_status.php')
                .then(r => r.json())
                .then(dataOld => {
                  if (dataOld.lines && Array.isArray(dataOld.lines)) {
                    dataOld.lines.forEach(line => {
                      const tipo = line.toLowerCase().includes('erro') && !line.toLowerCase().includes('0 erros') ? 'error' : 
                                 (line.toLowerCase().includes('sucesso') || line.toLowerCase().includes('concluída')) ? 'success' : '';
                      adicionarLog(line, tipo);
                    });
                  }
                })
                .catch(error => {
                  console.error('Erro ao carregar logs antigos:', error);
                });
            }
            
            // Atualizar progresso baseado no status real
            if (data.progress !== undefined && data.progress !== null && data.progress !== 0) {
              atualizarProgresso(data.progress);
            } else if (data.total_expected && data.total_expected > 0) {
              // Calcular progresso real usando o total esperado do backend
              const progressoReal = Math.min(99, Math.round((contadorProcessados / data.total_expected) * 100));
              atualizarProgresso(progressoReal);
            } else {
              // Calcular progresso baseado nos contadores (fallback)
              const totalEsperado = Math.max(contadorProcessados, 1);
              const progressoCalculado = Math.min(95, (contadorProcessados / totalEsperado) * 100);
              atualizarProgresso(progressoCalculado);
            }
            
            // Atualizar status baseado na análise inteligente
            if (data.status) {
              switch (data.status) {
                case 'success':
                  atualizarStatus('✅', 'Sincronização corrigida concluída!', 'Todos os dados foram atualizados com sucesso', '#059669');
                  atualizarProgresso(100);
                  syncErrorSummary.style.display = 'none';
                  if (syncInterval) clearInterval(syncInterval);
                  break;
                case 'error':
                  mostrarErroSync(data.last_message || 'Erro durante a sincronização corrigida');
                  if (syncInterval) clearInterval(syncInterval);
                  break;
                case 'processing':
                  atualizarStatus('🔄', 'Sincronizando (versão corrigida)...', 'Processando dados do Asaas', '#3b82f6');
                  break;
                case 'starting':
                  atualizarStatus('⏳', 'Iniciando sincronização corrigida...', 'Preparando conexão com Asaas', '#3b82f6');
                  break;
                default:
                  break;
              }
            }
            
            // Fallback para detecção manual se não houver status
            if (!data.status && data.lines && data.lines.length > 0) {
              const ultima = data.lines[data.lines.length - 1].toLowerCase();
              if (ultima.includes('sincronizando clientes')) {
                atualizarStatus('👥', 'Sincronizando clientes (corrigido)...', 'Buscando clientes no Asaas', '#3b82f6');
              } else if (ultima.includes('clientes sincronizados')) {
                atualizarStatus('💾', 'Clientes sincronizados (corrigido)!', 'Avançando para cobranças...', '#3b82f6');
              } else if (ultima.includes('sincronizando cobranças')) {
                atualizarStatus('💸', 'Sincronizando cobranças (corrigido)...', 'Buscando cobranças no Asaas', '#3b82f6');
              } else if (ultima.includes('cobranças sincronizadas')) {
                atualizarStatus('💾', 'Cobranças sincronizadas (corrigido)!', 'Finalizando...', '#3b82f6');
              } else if (ultima.includes('sincronização melhorada concluída com sucesso')) {
                atualizarStatus('✅', 'Sincronização corrigida concluída!', 'Todos os dados foram atualizados com sucesso', '#059669');
                atualizarProgresso(100);
                if (syncInterval) clearInterval(syncInterval);
              }
            }
          })
          .catch(error => {
            console.error('Erro ao carregar logs:', error);
            // Em caso de erro, tentar carregar logs antigos como fallback
            fetch('api/sync_status.php')
              .then(r => r.json())
              .then(dataOld => {
                if (dataOld.lines && Array.isArray(dataOld.lines)) {
                  syncLogsArea.innerHTML = '';
                  adicionarLog('⚠️ Erro ao carregar logs da versão corrigida, mostrando logs antigos...', 'warn');
                  dataOld.lines.forEach(line => {
                    const tipo = line.toLowerCase().includes('erro') && !line.toLowerCase().includes('0 erros') ? 'error' : 
                               (line.toLowerCase().includes('sucesso') || line.toLowerCase().includes('concluída')) ? 'success' : '';
                    adicionarLog(line, tipo);
                  });
                }
              })
              .catch(fallbackError => {
                console.error('Erro no fallback também:', fallbackError);
                adicionarLog('❌ Erro ao carregar logs: ' + error.message, 'error');
              });
          });
      }, 1000);
    });
  }

  if (btnConfigAsaas) {
    btnConfigAsaas.addEventListener('click', abrirModalConfigAsaas);
  }
  if (btnFecharConfigAsaas) {
    btnFecharConfigAsaas.addEventListener('click', fecharModalConfigAsaas);
  }
  if (btnFecharConfig) {
    btnFecharConfig.addEventListener('click', fecharModalConfigAsaas);
  }
  if (btnTestarSincronizacao) {
    btnTestarSincronizacao.addEventListener('click', function() {
      fecharModalConfigAsaas();
      btnSync.click();
    });
  }
  if (btnTestarChaveAtual) {
    btnTestarChaveAtual.addEventListener('click', testarChaveAtual);
  }
  if (btnTestarNovaChave) {
    btnTestarNovaChave.addEventListener('click', testarNovaChave);
  }
  if (formNovaChave) {
    formNovaChave.addEventListener('submit', function(e) {
      e.preventDefault();
      aplicarNovaChave();
    });
  }

  // Fechar modal ao clicar fora
  if (modalConfigAsaas) {
    modalConfigAsaas.addEventListener('click', function(e) {
      if (e.target === modalConfigAsaas) {
        fecharModalConfigAsaas();
      }
    });
  }

  if (btnFechar) {
    btnFechar.addEventListener('click', fecharModalSync);
  }
  if (btnVerLogCompleto) {
    btnVerLogCompleto.addEventListener('click', carregarLogCompleto);
  }
  if (btnFecharLogCompleto) {
    btnFecharLogCompleto.addEventListener('click', function() {
      modalLogCompleto.style.display = 'none';
    });
  }

  // Função showToast (caso não exista)
  if (typeof showToast !== 'function') {
    window.showToast = function(msg, tipo) {
      const toast = document.createElement('div');
      toast.textContent = msg;
      toast.style = `position:fixed;top:24px;right:24px;z-index:9999;padding:12px 22px;background:${tipo==='success'?'#bbf7d0':'#fee2e2'};color:${tipo==='success'?'#166534':'#b91c1c'};border-radius:8px;font-weight:500;box-shadow:0 2px 8px #0002;transition:opacity 0.3s;`;
      document.body.appendChild(toast);
      setTimeout(()=>{
        toast.style.opacity = '0';
        setTimeout(()=>{toast.remove();}, 300);
      }, 2500);
    }
  } 

  // Funções para edição e exclusão de mensagens (disponíveis globalmente)
  window.editarMensagem = function(id, textoAtual) {
    console.log('Editando mensagem ID:', id, 'Texto atual:', textoAtual);
    const novoTexto = prompt("Editar mensagem:", textoAtual);
    if (novoTexto === null || novoTexto.trim() === "") return;
    
    console.log('Novo texto:', novoTexto);
    
    fetch("api/editar_mensagem.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + encodeURIComponent(id) + "&mensagem=" + encodeURIComponent(novoTexto.trim())
    })
    .then(r => {
      console.log('Status da resposta:', r.status);
      return r.json();
    })
    .then(resp => {
      console.log('Resposta do servidor:', resp);
      if (resp.success) {
        // Atualizar o texto da mensagem diretamente no DOM
        const mensagemElement = document.querySelector(`[data-mensagem-id="${id}"]`);
        if (mensagemElement) {
          const conteudoElement = mensagemElement.querySelector('.mensagem-conteudo');
          if (conteudoElement) {
            conteudoElement.textContent = novoTexto.trim();
          }
        }
        showToast("Mensagem editada com sucesso!", "success");
      } else {
        showToast("Erro ao editar: " + (resp.error || "Erro desconhecido"), "error");
      }
    })
    .catch(error => {
      console.error('Erro na requisição:', error);
      showToast("Erro ao conectar ao servidor: " + error.message, "error");
    });
  };

  window.excluirMensagem = function(id) {
    console.log('Excluindo mensagem ID:', id);
    if (!confirm("Tem certeza que deseja excluir esta mensagem?")) return;
    
    fetch("api/excluir_mensagem.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + encodeURIComponent(id)
    })
    .then(r => {
      console.log('Status da resposta:', r.status);
      return r.json();
    })
    .then(resp => {
      console.log('Resposta do servidor:', resp);
      if (resp.success) {
        // Remover a mensagem do DOM diretamente
        const mensagemElement = document.querySelector(`[data-mensagem-id="${id}"]`);
        if (mensagemElement) {
          mensagemElement.remove();
        }
        showToast("Mensagem excluída com sucesso!", "success");
      } else {
        showToast("Erro ao excluir: " + (resp.error || "Erro desconhecido"), "error");
      }
    })
    .catch(error => {
      console.error('Erro na requisição:', error);
      showToast("Erro ao conectar ao servidor: " + error.message, "error");
    });
  };

  // ===== SISTEMA DE MONITORAMENTO SIMPLES =====
  
  // Configurar botões do monitoramento
  const btnVerificarChave = document.getElementById('btn-verificar-chave');
  const btnEstatisticas = document.getElementById('btn-estatisticas');
  const estatisticasDetalhadas = document.getElementById('estatisticas-detalhadas');
  
  // ===== VERIFICAÇÃO INICIAL AUTOMÁTICA =====
  async function verificarStatusInicial() {
    console.log('Verificando status inicial da API do Asaas...');
    
    try {
      const response = await fetch('api/verificar_status_asaas.php');
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const data = await response.json();
      console.log('Status inicial recebido:', data);
      
      atualizarStatusInterface(data);
      
    } catch (error) {
      console.error('Erro na verificação inicial:', error);
      mostrarStatusErro(error.message);
    }
  }
  
  function atualizarStatusInterface(status) {
    const container = document.getElementById('status-chave-asaas-container');
    if (!container) {
      console.error('Container status-chave-asaas-container não encontrado');
      return;
    }
    
    if (!status) {
      container.innerHTML = `
        <div class="status-chave-asaas status-invalido">
          <div class="status-header">
            <span class="status-icone">❌</span>
            <span class="status-texto">Erro ao carregar status</span>
          </div>
        </div>
      `;
      return;
    }
    
    const icone = status.valida ? '✅' : '❌';
    const classe = status.valida ? 'status-valido' : 'status-invalido';
    const texto = status.valida ? 'Chave Válida' : 'Chave Inválida';
    const tipoChave = status.tipo_chave || (status.chave_mascarada && status.chave_mascarada.includes('_test_') ? 'TESTE' : 'PRODUÇÃO');
    
    container.innerHTML = `
      <div class="status-chave-asaas ${classe}">
        <div class="status-header">
          <span class="status-icone">${icone}</span>
          <span class="status-texto">${texto}</span>
          ${tipoChave ? `<span class="status-tipo">(${tipoChave})</span>` : ''}
        </div>
        <div class="status-detalhes">
          <small>Última verificação: ${status.timestamp || 'N/A'}</small>
          ${status.http_code ? `<br><small>HTTP: ${status.http_code}</small>` : ''}
          ${status.response_time ? `<br><small>Tempo: ${status.response_time}ms</small>` : ''}
        </div>
      </div>
    `;
    
    console.log('Interface atualizada com sucesso');
  }
  
  function mostrarStatusErro(mensagem) {
    const container = document.getElementById('status-chave-asaas-container');
    if (container) {
      container.innerHTML = `
        <div class="status-chave-asaas status-invalido">
          <div class="status-header">
            <span class="status-icone">❌</span>
            <span class="status-texto">Erro: ${mensagem}</span>
          </div>
        </div>
      `;
    }
  }
  
  // Executar verificação inicial quando a página carregar
  verificarStatusInicial();
  
  // ===== EVENT LISTENERS DOS BOTÕES =====
  
  if (btnVerificarChave) {
    btnVerificarChave.addEventListener('click', async () => {
      btnVerificarChave.disabled = true;
      btnVerificarChave.textContent = '🔍 Verificando...';
      
      try {
        if (window.monitoramentoAsaas) {
          const resultado = await window.monitoramentoAsaas.forcarVerificacao();
          if (resultado) {
            mostrarNotificacao(resultado.valida ? '✅ Chave válida!' : '❌ Chave inválida', resultado.valida ? 'success' : 'error');
            atualizarStatusInterface(resultado);
          }
        } else {
          // Fallback para verificação direta
          const response = await fetch('verificador_automatico_chave_otimizado.php?action=verificar');
          const data = await response.json();
          mostrarNotificacao(data.valida ? '✅ Chave válida!' : '❌ Chave inválida', data.valida ? 'success' : 'error');
          atualizarStatusInterface(data);
        }
      } catch (error) {
        mostrarNotificacao('Erro ao verificar chave', 'error');
      } finally {
        btnVerificarChave.disabled = false;
        btnVerificarChave.textContent = '🔍 Verificar Agora';
      }
    });
  }
  
  if (btnEstatisticas) {
    btnEstatisticas.addEventListener('click', async () => {
      try {
        const response = await fetch('verificador_automatico_chave_otimizado.php?action=estatisticas');
        const data = await response.json();
        
        const estatisticasContent = document.getElementById('estatisticas-content');
        if (estatisticasContent) {
          estatisticasContent.innerHTML = `
            <div class="bg-white p-3 rounded-lg border">
              <p class="text-xs text-gray-500">Última Verificação</p>
              <p class="font-semibold">${data.ultima_verificacao}</p>
            </div>
            <div class="bg-white p-3 rounded-lg border">
              <p class="text-xs text-gray-500">Próxima Verificação</p>
              <p class="font-semibold">${data.proxima_verificacao}</p>
            </div>
            <div class="bg-white p-3 rounded-lg border">
              <p class="text-xs text-gray-500">Tem Alertas</p>
              <p class="font-semibold">${data.tem_alertas ? 'Sim' : 'Não'}</p>
            </div>
            <div class="bg-white p-3 rounded-lg border">
              <p class="text-xs text-gray-500">Chave Mudou</p>
              <p class="font-semibold">${data.chave_mudou ? 'Sim' : 'Não'}</p>
            </div>
          `;
        }
        
        estatisticasDetalhadas.style.display = estatisticasDetalhadas.style.display === 'none' ? 'block' : 'none';
        
      } catch (error) {
        mostrarNotificacao('Erro ao carregar estatísticas', 'error');
      }
    });
  }

  // Função para mostrar notificações
  function mostrarNotificacao(mensagem, tipo) {
    const notificacao = document.createElement('div');
    notificacao.className = `notificacao ${tipo}`;
    notificacao.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 20px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      z-index: 9999;
      max-width: 300px;
      word-wrap: break-word;
      ${tipo === 'success' ? 'background-color: #10b981;' : 'background-color: #ef4444;'}
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transform: translateX(100%);
      transition: transform 0.3s ease;
    `;
    
    notificacao.innerHTML = `
      <div class="notificacao-conteudo">
        <span class="notificacao-mensagem">${mensagem}</span>
      </div>
    `;
    
    document.body.appendChild(notificacao);
    
    // Animação de entrada
    setTimeout(() => {
      notificacao.style.transform = 'translateX(0)';
    }, 10);
    
    // Remover após 5 segundos
    setTimeout(() => {
      notificacao.style.transform = 'translateX(100%)';
      setTimeout(() => {
        if (notificacao.parentNode) {
          notificacao.remove();
        }
      }, 300);
    }, 5000);
  }
  
  // Configurar monitoramento quando estiver disponível
  // scriptMonitoramento.onload = () => {
  //   if (window.monitoramentoAsaas) {
  //     // Registrar callback para mudanças de status
  //     window.monitoramentoAsaas.onStatusChange((status) => {
  //       console.log('Status da API mudou:', status);
        
  //       // Atualizar interface se necessário
  //       if (status && !status.valida) {
  //         showToast('⚠️ Chave da API inválida detectada', 'error');
  //       }
  //     });
  //   }
  // };
});
</script>
<?php
}
include 'template.php'; 