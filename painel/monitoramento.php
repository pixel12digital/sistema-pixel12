<?php
$page = 'monitoramento.php';
$page_title = 'Monitoramento de Clientes';
$custom_header = '<button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center gap-2" id="btn-executar-monitoramento"><span>🔄 Executar Monitoramento</span></button> <button type="button" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md flex items-center gap-2" id="btn-agendar-pendentes"><span>📅 Agendar Pendentes</span></button> <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2" id="btn-configuracoes"><span>⚙️ Configurações</span></button>';

// Configurações de polling OTIMIZADAS para economizar conexões
const POLLING_INTERVAL = 600000; // 10 minutos (era 1 segundo)
const CACHE_TTL = 1800; // 30 minutos de cache

function render_content() {
?>
<!-- Dashboard de Monitoramento -->
<section class="bg-white shadow-sm p-6 mb-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">📊 Dashboard de Monitoramento</h2>
      <p class="text-gray-600 mt-1">Controle inteligente de clientes com cobranças vencidas</p>
      <!-- Relógio do servidor e do navegador -->
      <div class="flex gap-6 mt-2 mb-2">
        <div class="text-sm text-gray-700">
          <b>Horário do servidor:</b> <?php echo date('d/m/Y H:i:s'); ?> (<?php echo date_default_timezone_get(); ?>)
        </div>
        <div class="text-sm text-gray-700" id="relogio-navegador">
          <b>Horário do navegador:</b> <span id="hora-navegador"></span>
        </div>
      </div>
      <script>
        function atualizarRelogioNavegador() {
          document.getElementById('hora-navegador').innerText = new Date().toLocaleString('pt-BR');
        }
        // Atualizar relógio a cada 10 minutos para economizar conexões
        setInterval(atualizarRelogioNavegador, POLLING_INTERVAL);
        atualizarRelogioNavegador();
      </script>
    </div>
    <div class="flex gap-3">
      <div class="text-center">
        <div class="text-2xl font-bold text-green-600" id="total-monitorados">0</div>
        <div class="text-sm text-gray-500">Monitorados</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-red-600" id="total-vencidas">0</div>
        <div class="text-sm text-gray-500">Vencidas</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-blue-600" id="total-mensagens">0</div>
        <div class="text-sm text-gray-500">Mensagens</div>
      </div>
    </div>
  </div>

  <!-- Status do Sistema -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white">✅</div>
        <div>
          <div class="font-semibold text-green-800">Sistema Ativo</div>
          <div class="text-sm text-green-600">Monitoramento funcionando</div>
        </div>
      </div>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">⏰</div>
        <div>
          <div class="font-semibold text-blue-800">Próxima Verificação</div>
          <div class="text-sm text-blue-600" id="proxima-verificacao">Carregando...</div>
        </div>
      </div>
    </div>
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white">📱</div>
        <div>
          <div class="font-semibold text-purple-800">Última Execução</div>
          <div class="text-sm text-purple-600" id="ultima-execucao">Carregando...</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Filtros e Controles -->
<section class="bg-white shadow-sm p-4 mb-6">
  <div class="grid gap-4 md:grid-cols-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Status da Cobrança</label>
      <select id="filter-status" class="w-full rounded-md border-gray-300">
        <option value="">Todos os Status</option>
        <option value="PENDING">Aguardando Pagamento</option>
        <option value="OVERDUE">Vencida</option>
        <option value="RECEIVED">Recebida</option>
        <option value="CONFIRMED">Confirmada</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Dias Vencidos</label>
      <select id="filter-dias-vencidos" class="w-full rounded-md border-gray-300">
        <option value="">Todos</option>
        <option value="1-7">1-7 dias</option>
        <option value="8-15">8-15 dias</option>
        <option value="16-30">16-30 dias</option>
        <option value="30+">Mais de 30 dias</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Valor</label>
      <select id="filter-valor" class="w-full rounded-md border-gray-300">
        <option value="">Todos</option>
        <option value="0-50">Até R$ 50</option>
        <option value="50-100">R$ 50-100</option>
        <option value="100-500">R$ 100-500</option>
        <option value="500+">Acima de R$ 500</option>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button id="btn-aplicar-filtros" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">Aplicar Filtros</button>
      <button id="btn-ir-faturas" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md" title="Ir para página de Faturas">📋 Faturas</button>
    </div>
  </div>
  
  <!-- Filtro específico de cliente (inicialmente oculto) -->
  <div id="filtro-cliente-especifico" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md" style="display: none;">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-blue-600">🎯</span>
        <span class="text-sm font-medium text-blue-800">Visualizando cliente específico:</span>
        <span id="nome-cliente-filtro" class="text-sm text-blue-700"></span>
      </div>
      <button id="btn-limpar-filtro-cliente" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">
        Limpar Filtro
      </button>
    </div>
  </div>
</section>

<!-- Tabela de Clientes Monitorados -->
<section class="bg-white shadow-sm p-4">
  <div class="flex items-center justify-between mb-4">
    <h3 class="text-lg font-semibold text-gray-800">Clientes em Monitoramento</h3>
    <div class="flex gap-2">
      <button id="btn-exportar" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm">📊 Exportar</button>
      <button id="btn-limpar-filtros" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md text-sm">🔄 Limpar</button>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Cliente</th>
          <th class="px-3 py-2 text-left">Contato</th>
          <th class="px-3 py-2 text-left">Cobranças</th>
          <th class="px-3 py-2 text-left">Valor Total</th>
          <th class="px-3 py-2 text-left">Dias Vencido</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Última Mensagem</th>
          <th class="px-3 py-2 text-left">Monitoramento</th>
          <th class="px-3 py-2 text-left">Ações</th>
        </tr>
      </thead>
      <tbody id="tabela-monitoramento">
        <!-- Dados serão carregados via JavaScript -->
      </tbody>
    </table>
  </div>

  <div id="paginacao-monitoramento" class="flex justify-center items-center gap-2 mt-4"></div>
</section>

<!-- Modal de Detalhes do Cliente Monitorado -->
<div id="modal-detalhes-monitoramento" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" style="display: none;">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">👁️ Detalhes do Monitoramento</h3>
      <button id="btn-fechar-modal-detalhes" class="text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">&times;</button>
    </div>
    <div id="detalhes-monitoramento-content">
      <!-- Conteúdo dinâmico via JS -->
    </div>
  </div>
</div>

<!-- Modal de Configurações -->
<div id="modal-configuracoes" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">⚙️ Configurações do Monitoramento</h3>
      <button id="btn-fechar-config" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Intervalo de Verificação</label>
        <select id="intervalo-verificacao" class="w-full rounded-md border-gray-300">
          <option value="30">30 minutos</option>
          <option value="60">1 hora</option>
          <option value="120">2 horas</option>
          <option value="240">4 horas</option>
          <option value="480">8 horas</option>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Dias Mínimos Vencidos</label>
        <input type="number" id="dias-minimos" class="w-full rounded-md border-gray-300" value="1" min="1" max="30">
        <p class="text-xs text-gray-500 mt-1">Só enviar mensagem após X dias de vencimento</p>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Mensagens por Dia</label>
        <input type="number" id="limite-mensagens" class="w-full rounded-md border-gray-300" value="1" min="1" max="5">
        <p class="text-xs text-gray-500 mt-1">Máximo de mensagens por cliente por dia</p>
      </div>
      
      <div class="flex items-center">
        <input type="checkbox" id="monitorar-apenas-vencidas" class="rounded border-gray-300" checked>
        <label for="monitorar-apenas-vencidas" class="ml-2 text-sm text-gray-700">
          Monitorar apenas cobranças vencidas (PENDING/OVERDUE)
        </label>
      </div>
      
      <div class="flex items-center">
        <input type="checkbox" id="verificar-status-asaas" class="rounded border-gray-300" checked>
        <label for="verificar-status-asaas" class="ml-2 text-sm text-gray-700">
          Verificar status real no Asaas antes de enviar
        </label>
      </div>
    </div>
    
    <div class="flex gap-3 mt-6">
      <button id="btn-salvar-config" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">💾 Salvar Configurações</button>
      <button id="btn-cancelar-config" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Cancelar</button>
    </div>
  </div>
</div>

<!-- Modal de Logs -->
<div id="modal-logs" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[80vh] overflow-hidden">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">📋 Logs de Monitoramento</h3>
      <button id="btn-fechar-logs" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    
    <div id="logs-content" class="bg-gray-100 p-4 rounded-md max-h-[60vh] overflow-y-auto font-mono text-sm">
      <!-- Logs serão carregados aqui -->
    </div>
    
    <div class="flex gap-3 mt-4">
      <button id="btn-atualizar-logs" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">🔄 Atualizar</button>
      <button id="btn-limpar-logs" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">🗑️ Limpar Logs</button>
      <button id="btn-fechar-logs-modal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Fechar</button>
    </div>
  </div>
</div>

<!-- Modal de Mensagem Agendada -->
<div id="modal-mensagem-agendada" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" style="display: none;">
  <div class="bg-white rounded-lg p-6 w-full max-w-3xl mx-4 max-h-[80vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">💬 Mensagem Agendada</h3>
      <button id="btn-fechar-modal-mensagem" class="text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">&times;</button>
    </div>
    
    <div id="mensagem-agendada-content">
      <!-- Conteúdo dinâmico via JS -->
    </div>
  </div>
</div>

<script src="assets/monitoramento_dashboard.js"></script>
<script src="assets/traducoes.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar dashboard
  if (typeof MonitoramentoDashboard !== 'undefined') {
    window.monitoramentoDashboard = new MonitoramentoDashboard();
  }
  
  // Aplicar traduções após carregar o dashboard
  if (typeof traducoes !== 'undefined') {
    setTimeout(() => {
      traducoes.traduzirPagina();
    }, 1000); // Aguardar 1 segundo para garantir que o conteúdo dinâmico foi carregado
  }
});
</script>

<?php
}
include 'template.php';
?> 