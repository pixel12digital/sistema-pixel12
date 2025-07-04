<?php $page = 'faturas.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Faturas • Pixel12Digital</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <?php include 'menu_lateral.php'; ?>
  <main class="main-content">
    <!-- Header -->
    <header class="invoices-header bg-purple-700 text-white p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-6">
      <h1 class="invoices-title text-2xl font-semibold flex-1">Faturas</h1>
      <div class="flex-1 lg:max-w-md">
        <label for="invoice-search" class="sr-only">Buscar fatura</label>
        <input id="invoice-search" type="search" placeholder="Buscar por número ou cliente" class="invoices-search-bar w-full px-3 py-2 rounded-md text-gray-800" />
      </div>
      <button type="button" class="invoices-new-btn bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md flex items-center gap-2" id="btn-sincronizar">
        <span>🔄 Sincronizar com Asaas</span>
      </button>
      <div id="sync-status" style="display:none; margin-top:10px; width:300px;">
        <div style="width:100%;background:#eee;border-radius:5px;">
          <div id="sync-bar" style="width:0%;height:18px;background:#a259e6;border-radius:5px;transition:width 0.4s;"></div>
        </div>
        <span id="sync-msg" style="font-size:14px;color:#555;">Sincronizando...</span>
      </div>
      <button type="button" class="invoices-new-btn bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md flex items-center gap-2">
        <span>+ Nova Fatura</span>
        <svg class="hidden h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </button>
    </header>
    <!-- Filters -->
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
        <div>
          <label class="block text-xs font-medium">Vencimento inicial</label>
          <input type="date" class="filter-date-due-inicio mt-1 w-full rounded-md border-gray-300" />
        </div>
        <div>
          <label class="block text-xs font-medium">Vencimento final</label>
          <input type="date" class="filter-date-due-fim mt-1 w-full rounded-md border-gray-300" />
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
    </section>
    <!-- Invoices Table -->
    <section class="p-4 overflow-x-auto">
      <table class="invoices-table w-full text-sm whitespace-nowrap">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2">Nº</th>
            <th class="px-3 py-2">Cliente</th>
            <th class="px-3 py-2">Valor</th>
            <th class="px-3 py-2">Emissão</th>
            <th class="px-3 py-2">Vencimento</th>
            <th class="px-3 py-2">Tipo de Pagamento</th>
            <th class="px-3 py-2">Status</th>
            <th class="px-3 py-2">Ações</th>
          </tr>
        </thead>
        <tbody id="invoices-tbody">
          <!-- Linhas serão preenchidas via JS -->
        </tbody>
      </table>
      <div id="pagination" class="flex justify-center items-center gap-2 mt-4"></div>
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
  </main>
  <script src="/painel/assets/invoices.js"></script>
  <script src="assets/cobrancas.js"></script>
  <script>
    document.getElementById('btn-sincronizar').onclick = function() {
      if (!confirm('Deseja realmente sincronizar as cobranças com o Asaas?')) return;
      this.disabled = true;
      this.innerHTML = 'Sincronizando...';
      fetch('sincronizar_asaas_ajax.php')
        .then(r => r.json())
        .then(resp => {
          if (resp.success) {
            alert('Sincronização concluída!');
            location.reload();
          } else {
            alert('Erro ao sincronizar!\n' + (resp.error || '') + '\n' + (resp.output || ''));
          }
        })
        .catch(() => alert('Erro ao sincronizar!'))
        .finally(() => {
          this.disabled = false;
          this.innerHTML = '🔄 Sincronizar com Asaas';
        });
    };
  </script>
</body>
</html> 