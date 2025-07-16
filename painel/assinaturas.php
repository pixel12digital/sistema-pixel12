<?php $page = 'assinaturas.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Assinaturas • Pixel12Digital</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .assinaturas-filters input, .assinaturas-filters select {
      min-width: 0;
      font-size: 1em;
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      margin: 0;
      padding: 6px 8px;
    }
    .assinaturas-filters .grid {
      gap: 12px !important;
    }
    .assinaturas-filters .grid > div {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    /* Bordas mais suaves para campos do modal */
    #modal-novo-plano input,
    #modal-novo-plano textarea,
    #modal-novo-plano select {
      border: 1.2px solid #bfa3e6 !important;
      background: #fff;
      color: #232836;
      border-radius: 6px;
      font-size: 1em;
      transition: border-color 0.2s;
    }
    #modal-novo-plano input:focus,
    #modal-novo-plano textarea:focus,
    #modal-novo-plano select:focus {
      border-color: #a259e6 !important;
      outline: none;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <?php include 'menu_lateral.php'; ?>
  <main class="main-content">
    <header class="invoices-header bg-purple-700 text-white p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-6">
      <h1 class="invoices-title text-2xl font-semibold flex-1">Assinaturas</h1>
      <button type="button" class="invoices-new-btn bg-purple-600 hover:bg-purple-800 transition-colors px-4 py-2 rounded-md flex items-center gap-2" id="btn-nova-assinatura">
        <span>+ Novo Plano</span>
      </button>
    </header>
    <section class="assinaturas-filters bg-white shadow-sm p-4">
      <div class="grid gap-4 md:grid-cols-4">
        <div>
          <label for="filter-nome" class="block text-xs font-medium">Nome do Plano</label>
          <input id="filter-nome" type="text" class="filter-nome mt-1 w-full rounded-md border-gray-300" placeholder="Buscar plano..." />
        </div>
        <div>
          <label for="filter-periodicidade" class="block text-xs font-medium">Periodicidade</label>
          <select id="filter-periodicidade" class="filter-periodicidade mt-1 w-full rounded-md border-gray-300">
            <option value="">Todas</option>
            <option value="mensal">Mensal</option>
            <option value="trimestral">Trimestral</option>
            <option value="semestral">Semestral</option>
            <option value="anual">Anual</option>
          </select>
        </div>
      </div>
    </section>
    <section class="p-4 overflow-x-auto">
      <table class="assinaturas-table w-full text-sm whitespace-nowrap">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2">#</th>
            <th class="px-3 py-2">Nome do Plano</th>
            <th class="px-3 py-2">Descrição</th>
            <th class="px-3 py-2">Valor</th>
            <th class="px-3 py-2">Periodicidade</th>
            <th class="px-3 py-2">Ações</th>
          </tr>
        </thead>
        <tbody id="assinaturas-tbody">
          <!-- Linhas preenchidas via JS -->
        </tbody>
      </table>
      <div id="pagination-assinaturas" class="flex justify-center items-center gap-2 mt-4"></div>
    </section>
    <!-- Modal Novo Plano -->
    <div id="modal-novo-plano" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:9999;align-items:center;justify-content:center;">
      <div style="background:#fff;padding:32px 28px;border-radius:12px;min-width:380px;max-width:99vw;width:480px;box-shadow:0 4px 32px #7c2ae820;position:relative;">
        <button id="close-modal-novo-plano" style="position:absolute;top:12px;right:18px;font-size:1.4rem;background:none;border:none;cursor:pointer;">&times;</button>
        <h3 class="text-lg font-bold mb-3">Novo Plano de Assinatura</h3>
        <form id="form-novo-plano">
          <div class="mb-2">
            <label for="plano-nome" class="block text-xs font-medium">Nome do Plano</label>
            <input id="plano-nome" name="nome" type="text" class="w-full border rounded px-2 py-1" required />
          </div>
          <div class="mb-2">
            <label for="plano-desc" class="block text-xs font-medium">Descrição</label>
            <textarea id="plano-desc" name="descricao" class="w-full border rounded px-2 py-1" rows="3" required></textarea>
          </div>
          <div class="mb-2">
            <label for="plano-valor" class="block text-xs font-medium">Valor da Assinatura (R$)</label>
            <input id="plano-valor" name="valor" type="number" step="0.01" min="0" class="w-full border rounded px-2 py-1" required />
          </div>
          <div class="mb-4">
            <label for="plano-periodicidade" class="block text-xs font-medium">Periodicidade</label>
            <select id="plano-periodicidade" name="periodicidade" class="w-full border rounded px-2 py-1" required>
              <option value="mensal">Mensal</option>
              <option value="trimestral">Trimestral</option>
              <option value="semestral">Semestral</option>
              <option value="anual">Anual</option>
            </select>
          </div>
          <button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded font-semibold w-full">Salvar Plano</button>
        </form>
        <div id="status-novo-plano" class="mt-3 text-sm"></div>
      </div>
    </div>
  </main>
  <script src="assets/assinaturas.js"></script>
</body>
</html> 