<?php
$page = 'relatorios_financeiros.php';
$page_title = 'Relatórios Financeiros';
$custom_header = '<button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center gap-2" id="btn-exportar-relatorio"><span>📊 Exportar</span></button> <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2" id="btn-atualizar-dados"><span>🔄 Atualizar</span></button>';

function render_content() {
?>
<!-- Dashboard de Relatórios -->
<section class="bg-white shadow-sm p-6 mb-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">📊 Relatórios Financeiros</h2>
      <p class="text-gray-600 mt-1">Visão consolidada de faturas e monitoramento</p>
    </div>
    <div class="flex gap-3">
      <div class="text-center">
        <div class="text-2xl font-bold text-blue-600" id="total-faturas">0</div>
        <div class="text-sm text-gray-500">Total Faturas</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-red-600" id="total-vencidas">0</div>
        <div class="text-sm text-gray-500">Vencidas</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-green-600" id="total-recebidas">0</div>
        <div class="text-sm text-gray-500">Recebidas</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-purple-600" id="total-monitorados">0</div>
        <div class="text-sm text-gray-500">Monitorados</div>
      </div>
    </div>
  </div>

  <!-- Cards de Resumo -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm opacity-90">Valor Total</p>
          <p class="text-2xl font-bold" id="valor-total">R$ 0,00</p>
        </div>
        <div class="text-3xl">💰</div>
      </div>
    </div>
    <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-4 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm opacity-90">Vencido</p>
          <p class="text-2xl font-bold" id="valor-vencido">R$ 0,00</p>
        </div>
        <div class="text-3xl">⚠️</div>
      </div>
    </div>
    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm opacity-90">Recebido</p>
          <p class="text-2xl font-bold" id="valor-recebido">R$ 0,00</p>
        </div>
        <div class="text-3xl">✅</div>
      </div>
    </div>
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm opacity-90">Efetividade</p>
          <p class="text-2xl font-bold" id="taxa-efetividade">0%</p>
        </div>
        <div class="text-3xl">📈</div>
      </div>
    </div>
  </div>
</section>

<!-- Filtros de Período -->
<section class="bg-white shadow-sm p-4 mb-6">
  <div class="grid gap-4 md:grid-cols-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
      <select id="filter-periodo" class="w-full rounded-md border-gray-300">
        <option value="hoje">Hoje</option>
        <option value="semana">Última Semana</option>
        <option value="mes" selected>Último Mês</option>
        <option value="trimestre">Último Trimestre</option>
        <option value="ano">Último Ano</option>
        <option value="personalizado">Personalizado</option>
      </select>
    </div>
    <div id="periodo-personalizado" style="display: none;">
      <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
      <input type="date" id="data-inicio" class="w-full rounded-md border-gray-300">
    </div>
    <div id="periodo-personalizado-fim" style="display: none;">
      <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
      <input type="date" id="data-fim" class="w-full rounded-md border-gray-300">
    </div>
    <div class="flex items-end">
      <button id="btn-aplicar-periodo" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">Aplicar</button>
    </div>
  </div>
</section>

<!-- Gráficos e Análises -->
<section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <!-- Gráfico de Status -->
  <div class="bg-white shadow-sm p-4 rounded-lg">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Distribuição por Status</h3>
    <div id="grafico-status" class="h-64 flex items-center justify-center text-gray-500">
      Carregando gráfico...
    </div>
  </div>

  <!-- Gráfico de Evolução -->
  <div class="bg-white shadow-sm p-4 rounded-lg">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Evolução Mensal</h3>
    <div id="grafico-evolucao" class="h-64 flex items-center justify-center text-gray-500">
      Carregando gráfico...
    </div>
  </div>
</section>

<!-- Tabela de Top Clientes -->
<section class="bg-white shadow-sm p-4 mb-6">
  <h3 class="text-lg font-semibold text-gray-800 mb-4">🏆 Top Clientes</h3>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Cliente</th>
          <th class="px-3 py-2 text-left">Total Faturas</th>
          <th class="px-3 py-2 text-left">Valor Total</th>
          <th class="px-3 py-2 text-left">Vencido</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Monitoramento</th>
          <th class="px-3 py-2 text-left">Ações</th>
        </tr>
      </thead>
      <tbody id="tabela-top-clientes">
        <!-- Dados serão carregados via JavaScript -->
      </tbody>
    </table>
  </div>
</section>

<!-- Análise de Monitoramento -->
<section class="bg-white shadow-sm p-4">
  <h3 class="text-lg font-semibold text-gray-800 mb-4">📱 Análise de Monitoramento</h3>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">📊</div>
        <div>
          <div class="font-semibold text-blue-800">Clientes Monitorados</div>
          <div class="text-sm text-blue-600" id="clientes-monitorados">0</div>
        </div>
      </div>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white">💬</div>
        <div>
          <div class="font-semibold text-green-800">Mensagens Enviadas</div>
          <div class="text-sm text-green-600" id="mensagens-enviadas">0</div>
        </div>
      </div>
    </div>
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white">📈</div>
        <div>
          <div class="font-semibold text-purple-800">Taxa de Resposta</div>
          <div class="text-sm text-purple-600" id="taxa-resposta">0%</div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="assets/relatorios_financeiros.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar relatórios
  if (typeof RelatoriosFinanceiros !== 'undefined') {
    window.relatoriosFinanceiros = new RelatoriosFinanceiros();
  }
});
</script>

<?php
}
include 'template.php';
?> 