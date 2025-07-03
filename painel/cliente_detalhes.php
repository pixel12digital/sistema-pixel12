<?php
$page = 'clientes.php';
$page_title = 'Detalhes do Cliente';
$custom_header = '';
require_once 'config.php';
require_once 'db.php';
include 'template.php';
function render_content() {
  global $mysqli;
  echo '<style>
.painel-container {
  max-width: 900px !important;
  margin: 40px auto !important;
  background: #f3f4f6 !important;
  border-radius: 18px !important;
  box-shadow: 0 6px 32px #7c2ae820, 0 2px 12px #0003 !important;
  padding: 32px 24px !important;
}
.painel-card {
  background: #fff !important;
  border-radius: 16px !important;
  box-shadow: 0 6px 24px rgba(124,42,232,0.12), 0 2px 12px rgba(0,0,0,0.10) !important;
  padding: 24px 20px !important;
  margin-bottom: 24px !important;
  border: 1.5px solid #ede9fe !important;
  transition: box-shadow 0.2s;
}
.painel-card:hover {
  box-shadow: 0 10px 32px rgba(124,42,232,0.18), 0 4px 16px rgba(0,0,0,0.13) !important;
}
.painel-card h4 {
  color: #7c2ae8 !important;
  font-size: 1.1rem !important;
  margin-bottom: 12px !important;
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
}
.painel-card table {
  width: 100% !important;
  font-size: 0.98rem !important;
}
.painel-card td {
  padding: 4px 8px !important;
  border-bottom: 1px solid #ececec !important;
}
.painel-avatar {
  width: 56px !important; height: 56px !important;
  border-radius: 50% !important;
  background: #ede9fe !important;
  color: #7c2ae8 !important;
  font-size: 2rem !important;
  font-weight: bold !important;
  display: flex !important; align-items: center !important; justify-content: center !important;
  margin-right: 16px !important;
}
.painel-header {
  display: flex !important; align-items: center !important; gap: 16px !important; margin-bottom: 12px !important;
}
.painel-nome {
  font-size: 1.7rem !important; font-weight: bold !important; color: #7c2ae8 !important;
}
.painel-badge {
  display: inline-block !important; background: #e0e7ff !important; color: #3730a3 !important;
  border-radius: 6px !important; padding: 2px 10px !important; font-size: 0.85rem !important; margin-left: 8px !important;
}
@media (max-width: 900px) {
  .painel-grid { display: block !important; }
  .painel-card { margin-bottom: 18px !important; }
  .painel-container { padding: 12px 2vw !important; }
}
.painel-grid {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 24px !important;
}
.painel-abas {
  display: flex; gap: 0.5rem; margin-bottom: 24px; margin-top: 8px;
}
.painel-aba {
  background: #f3f4f6; color: #7c2ae8; border: none; outline: none;
  padding: 10px 22px; border-radius: 8px 8px 0 0; font-weight: 600; font-size: 1rem;
  cursor: pointer; transition: background 0.18s, color 0.18s;
}
.painel-aba.active, .painel-aba:hover {
  background: #fff; color: #a259e6; box-shadow: 0 -2px 8px #a259e610;
}
.painel-tabs-content { min-height: 320px; }
.painel-tab { display: none; }
.painel-tab[style*="display:block"] { display: block !important; }
</style>';
  $cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  $cliente = null;
  if ($cliente_id) {
    $res = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id LIMIT 1");
    $cliente = $res ? $res->fetch_assoc() : null;
  }
  // Separar campos por categoria
  $dados_pessoais = [
    'nome', 'contact_name', 'cpf_cnpj', 'razao_social', 'data_criacao', 'data_atualizacao', 'asaas_id', 'referencia_externa', 'criado_em_asaas'
  ];
  $contato = ['email', 'emails_adicionais', 'telefone', 'celular'];
  $endereco = ['cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais'];
  $outros = array_diff(array_keys($cliente ?? []), array_merge($dados_pessoais, $contato, $endereco));
  // Função para formatar campos
  function formatar_campo($campo, $valor) {
    if ($valor === null || $valor === '' || $valor === '0-0-0' || $valor === '0000-00-00') return '—';
    $labels = [
      'nome' => 'Nome',
      'contact_name' => 'Contato',
      'cpf_cnpj' => 'CPF/CNPJ',
      'razao_social' => 'Razão Social',
      'data_criacao' => 'Data de Criação',
      'data_atualizacao' => 'Data de Atualização',
      'asaas_id' => 'ID Asaas',
      'referencia_externa' => 'Referência Externa',
      'criado_em_asaas' => 'Criado no Asaas',
      'email' => 'E-mail',
      'emails_adicionais' => 'E-mails Adicionais',
      'telefone' => 'Telefone',
      'celular' => 'Celular',
      'cep' => 'CEP',
      'rua' => 'Rua',
      'numero' => 'Número',
      'complemento' => 'Complemento',
      'bairro' => 'Bairro',
      'cidade' => 'Cidade',
      'estado' => 'Estado',
      'pais' => 'País',
      'id' => 'ID',
      'observacoes' => 'Observações',
      'plano' => 'Plano',
      'status' => 'Status',
    ];
    $label = $labels[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    // Datas
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
      $data = substr($valor, 0, 10);
      $partes = explode('-', $data);
      if (count($partes) === 3) return "$label: {$partes[2]}/{$partes[1]}/{$partes[0]}";
    }
    // CPF/CNPJ
    if ($campo === 'cpf_cnpj' && preg_match('/^\d{11,14}$/', $valor)) {
      if (strlen($valor) === 11) {
        return "$label: " . preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $valor);
      } else {
        return "$label: " . preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $valor);
      }
    }
    // Telefone/Celular
    if (($campo === 'telefone' || $campo === 'celular') && preg_match('/^\d{10,11}$/', $valor)) {
      return "$label: (" . substr($valor,0,2) . ") " . substr($valor,-9,-4) . '-' . substr($valor,-4);
    }
    // Label padrão
    return "$label: $valor";
  }
  // Adicionar variável de modo edição
  $modo_edicao = isset($_GET['editar']) && $_GET['editar'] == '1';
?>
<div class="painel-container">
  <div class="painel-header">
    <div class="painel-avatar">
      <?= strtoupper(substr($cliente['nome'] ?? '?', 0, 1)) ?>
    </div>
    <div>
      <div class="painel-nome"><?= htmlspecialchars($cliente['nome'] ?? 'Cliente não encontrado') ?></div>
      <?php if (!empty($cliente['status'])): ?>
        <span class="painel-badge" style="background:#d1fae5;color:#065f46;">Status: <?= htmlspecialchars($cliente['status']) ?></span>
      <?php endif; ?>
      <?php if (!empty($cliente['plano'])): ?>
        <span class="painel-badge">Plano: <?= htmlspecialchars($cliente['plano']) ?></span>
      <?php endif; ?>
      <div class="text-gray-500 text-sm">ID: <?= htmlspecialchars($cliente['id'] ?? '-') ?> | Asaas: <?= htmlspecialchars($cliente['asaas_id'] ?? '-') ?></div>
    </div>
    <a href="clientes.php" class="bg-purple-600 hover:bg-purple-800 text-white px-4 py-2 rounded transition-colors font-semibold text-sm self-start md:self-auto" style="margin-left:auto;" title="Voltar para clientes">← Voltar para Clientes</a>
    <?php if (!$modo_edicao): ?>
      <a href="?id=<?= $cliente_id ?>&editar=1" class="ml-2 bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded font-semibold text-sm transition-colors" title="Editar Cliente">✏️ Editar</a>
    <?php endif; ?>
  </div>
  <!-- Abas modernas -->
  <div class="painel-abas">
    <button class="painel-aba active" data-tab="dados">Dados Gerais</button>
    <button class="painel-aba" data-tab="projetos">Projetos</button>
    <button class="painel-aba" data-tab="relacionamento">Suporte & Relacionamento</button>
    <button class="painel-aba" data-tab="financeiro">Financeiro</button>
  </div>
  <div class="painel-tabs-content">
    <?php if ($modo_edicao): ?>
    <form method="post" action="">
      <input type="hidden" name="id" value="<?= $cliente_id ?>">
      <div class="painel-tab painel-tab-dados" style="display:block;">
        <div class="painel-grid">
          <!-- Dados Pessoais -->
          <div class="painel-card">
            <h4>👤 Dados Pessoais</h4>
            <table>
              <tbody>
                <?php foreach ($dados_pessoais as $campo): if (!isset($cliente[$campo])) continue; ?>
                  <tr>
                    <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                    <?php if ($campo === 'asaas_id'): ?>
                      <td><span style="font-family:monospace; background:#f3f4f6; padding:4px 8px; border-radius:6px; color:#7c2ae8;"><?= htmlspecialchars($cliente[$campo]) ?></span></td>
                    <?php else: ?>
                      <td><input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($cliente[$campo]) ?>" class="painel-input"></td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <!-- Contato -->
          <div class="painel-card">
            <h4>✉️ Contato</h4>
            <table>
              <tbody>
                <?php foreach ($contato as $campo): if (!isset($cliente[$campo])) continue; ?>
                  <tr>
                    <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                    <td><input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($cliente[$campo]) ?>" class="painel-input"></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <!-- Endereço -->
          <div class="painel-card">
            <h4>📍 Endereço</h4>
            <table>
              <tbody>
                <?php foreach ($endereco as $campo): if (!isset($cliente[$campo])) continue; ?>
                  <tr>
                    <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                    <td><input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($cliente[$campo]) ?>" class="painel-input"></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <!-- Outros -->
          <div class="painel-card">
            <h4>🗂️ Outros</h4>
            <table>
              <tbody>
                <?php foreach ($outros as $campo): ?>
                  <tr>
                    <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                    <td><input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($cliente[$campo]) ?>" class="painel-input"></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="mt-6 text-right">
        <button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-6 py-2 rounded font-semibold text-base transition-colors">Salvar</button>
        <a href="?id=<?= $cliente_id ?>" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold text-base transition-colors">Cancelar</a>
      </div>
    </form>
    <?php else: ?>
    <div class="painel-tab painel-tab-dados" style="display:block;">
      <div class="painel-grid">
        <!-- Dados Pessoais -->
        <div class="painel-card">
          <h4>👤 Dados Pessoais</h4>
          <table>
            <tbody>
              <?php foreach ($dados_pessoais as $campo): if (!isset($cliente[$campo])) continue; ?>
                <tr>
                  <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Contato -->
        <div class="painel-card">
          <h4>✉️ Contato</h4>
          <table>
            <tbody>
              <?php foreach ($contato as $campo): if (!isset($cliente[$campo])) continue; ?>
                <tr>
                  <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Endereço -->
        <div class="painel-card">
          <h4>📍 Endereço</h4>
          <table>
            <tbody>
              <?php foreach ($endereco as $campo): if (!isset($cliente[$campo])) continue; ?>
                <tr>
                  <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Outros -->
        <div class="painel-card">
          <h4>🗂️ Outros</h4>
          <table>
            <tbody>
              <?php foreach ($outros as $campo): ?>
                <tr>
                  <td class="font-semibold text-gray-600"> <?= formatar_campo($campo, $cliente[$campo]) ?> </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <div class="painel-tab painel-tab-projetos" style="display:none;">
      <div class="painel-card"><h4>📁 Projetos</h4><p>Lista de projetos relacionados ao cliente.</p></div>
    </div>
    <div class="painel-tab painel-tab-relacionamento" style="display:none;">
      <div class="painel-card"><h4> Suporte & Relacionamento</h4><p>Histórico de tickets, comunicações e interações.</p></div>
    </div>
    <div class="painel-tab painel-tab-financeiro" style="display:none;">
      <div class="painel-card">
        <h4>💸 Financeiro</h4>
        <?php
        // Buscar cobranças/faturas do cliente (local)
        $cobrancas = [];
        $total_pago = $total_aberto = $total_vencido = 0.0;
        if ($cliente_id) {
          $res_cob = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = $cliente_id ORDER BY vencimento DESC");
          if ($res_cob) {
            while ($cob = $res_cob->fetch_assoc()) {
              $cobrancas[] = $cob;
              $valor = floatval($cob['valor']);
              if ($cob['status'] === 'RECEIVED' || $cob['status'] === 'PAID') $total_pago += $valor;
              elseif ($cob['status'] === 'PENDING' && strtotime($cob['vencimento']) < time()) $total_vencido += $valor;
              elseif ($cob['status'] === 'PENDING') $total_aberto += $valor;
            }
          }
        }
        // Buscar dados do Asaas se houver asaas_id
        $asaas_payments = $asaas_subs = [];
        if (!empty($cliente['asaas_id'])) {
          require_once __DIR__ . '/../src/Services/AsaasService.php';
          $asaasService = new \Services\AsaasService();
          try {
            $asaas_payments = $asaasService->getCustomerPayments($cliente['asaas_id']);
            $asaas_subs = $asaasService->getCustomerSubscriptions($cliente['asaas_id']);
          } catch (\Exception $e) {
            echo '<div style="color:#e11d48;">Erro ao buscar dados do Asaas: '.htmlspecialchars($e->getMessage()).'</div>';
          }
        }
        ?>
        <div class="mb-4">
          <b>Total pago:</b> R$ <?= number_format($total_pago,2,',','.') ?> |
          <b>Em aberto:</b> R$ <?= number_format($total_aberto,2,',','.') ?> |
          <b>Vencido:</b> R$ <?= number_format($total_vencido,2,',','.') ?>
        </div>
        <div style="overflow-x:auto;">
        <table class="w-full text-sm mb-6">
          <thead>
            <tr>
              <th colspan="6" style="text-align:left;color:#7c2ae8;font-weight:bold;">Cobranças/Faturas (Banco Local)</th>
            </tr>
            <tr>
              <th>Nº</th>
              <th>Valor</th>
              <th>Vencimento</th>
              <th>Status</th>
              <th>Pagamento</th>
              <th>Fatura</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($cobrancas)): ?>
              <tr><td colspan="6" class="text-center text-gray-400">Nenhuma cobrança encontrada.</td></tr>
            <?php else: foreach ($cobrancas as $i => $cob): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>R$ <?= number_format($cob['valor'],2,',','.') ?></td>
                <td><?= date('d/m/Y', strtotime($cob['vencimento'])) ?></td>
                <td><?= htmlspecialchars($cob['status']) ?></td>
                <td><?= $cob['data_pagamento'] ? date('d/m/Y', strtotime($cob['data_pagamento'])) : '—' ?></td>
                <td><?php if (!empty($cob['url_fatura'])): ?><a href="<?= htmlspecialchars($cob['url_fatura']) ?>" target="_blank" style="color:#7c2ae8;">Ver Fatura</a><?php else: ?>—<?php endif; ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
        <?php if (!empty($asaas_payments)): ?>
        <table class="w-full text-sm mb-6">
          <thead>
            <tr>
              <th colspan="7" style="text-align:left;color:#7c2ae8;font-weight:bold;">Cobranças/Faturas (Asaas)</th>
            </tr>
            <tr>
              <th>Nº</th>
              <th>Valor</th>
              <th>Vencimento</th>
              <th>Status</th>
              <th>Pagamento</th>
              <th>Tipo</th>
              <th>Fatura</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($asaas_payments as $i => $pay): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>R$ <?= number_format($pay['value'],2,',','.') ?></td>
                <td><?= isset($pay['dueDate']) ? date('d/m/Y', strtotime($pay['dueDate'])) : '—' ?></td>
                <td><?= htmlspecialchars($pay['status']) ?></td>
                <td><?= !empty($pay['paymentDate']) ? date('d/m/Y', strtotime($pay['paymentDate'])) : '—' ?></td>
                <td><?= htmlspecialchars($pay['billingType'] ?? '-') ?></td>
                <td><?php if (!empty($pay['invoiceUrl'])): ?><a href="<?= htmlspecialchars($pay['invoiceUrl']) ?>" target="_blank" style="color:#7c2ae8;">Ver Fatura</a><?php else: ?>—<?php endif; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
        <?php if (!empty($asaas_subs)): ?>
        <table class="w-full text-sm">
          <thead>
            <tr>
              <th colspan="7" style="text-align:left;color:#7c2ae8;font-weight:bold;">Assinaturas (Asaas)</th>
            </tr>
            <tr>
              <th>Nº</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Início</th>
              <th>Próx. Vencimento</th>
              <th>Tipo</th>
              <th>Descrição</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($asaas_subs as $i => $sub): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>R$ <?= number_format($sub['value'],2,',','.') ?></td>
                <td><?= htmlspecialchars($sub['status']) ?></td>
                <td><?= !empty($sub['startDate']) ? date('d/m/Y', strtotime($sub['startDate'])) : '—' ?></td>
                <td><?= !empty($sub['nextDueDate']) ? date('d/m/Y', strtotime($sub['nextDueDate'])) : '—' ?></td>
                <td><?= htmlspecialchars($sub['billingType'] ?? '-') ?></td>
                <td><?= htmlspecialchars($sub['description'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
@media (max-width: 768px) {
  .grid-cols-2 { grid-template-columns: 1fr !important; }
}
</style>
<style>
.tab-link { transition: color 0.2s, border-color 0.2s; }
.tab-link.active { color: #7c2ae8; border-color: #7c2ae8; }
.tab-content { animation: fadein 0.2s; }
@keyframes fadein { from { opacity: 0; } to { opacity: 1; } }
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const abas = document.querySelectorAll(".painel-aba");
  const tabs = document.querySelectorAll(".painel-tab");
  abas.forEach(btn => {
    btn.addEventListener("click", function() {
      abas.forEach(b => b.classList.remove("active"));
      this.classList.add("active");
      tabs.forEach(tab => tab.style.display = "none");
      document.querySelector(".painel-tab-"+this.dataset.tab).style.display = "block";
    });
  });
});
</script>
<?php
// No início do render_content, se $_SERVER['REQUEST_METHOD']==='POST', atualizar o banco
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['id'])) {
    echo '<div style="background:#fef3c7;color:#92400e;padding:12px 18px;border-radius:8px;margin-bottom:18px;">Erro: ID do cliente não enviado no formulário.</div>';
  } else {
    $id = intval($_POST['id']);
    $campos = array_merge($dados_pessoais, $contato, $endereco, $outros);
    $set = [];
    foreach ($campos as $campo) {
      if (isset($_POST[$campo])) {
        $valor = $mysqli->real_escape_string($_POST[$campo]);
        $set[] = "$campo='$valor'";
      }
    }
    if ($set) {
      $sql = "UPDATE clientes SET ".implode(',', $set)." WHERE id=$id LIMIT 1";
      if ($mysqli->query($sql)) {
        // Atualizar também no Asaas se necessário
        $cliente = $mysqli->query("SELECT * FROM clientes WHERE id=$id LIMIT 1")->fetch_assoc();
        if ($cliente && !empty($cliente['asaas_id'])) {
          require_once __DIR__ . '/../src/Services/AsaasService.php';
          $asaasService = new \Services\AsaasService();
          $asaasData = [];
          if (!empty($_POST['nome'])) $asaasData['name'] = $_POST['nome'];
          if (!empty($_POST['email'])) $asaasData['email'] = $_POST['email'];
          if (!empty($_POST['telefone'])) $asaasData['phone'] = $_POST['telefone'];
          if (!empty($_POST['celular'])) $asaasData['mobilePhone'] = $_POST['celular'];
          if (!empty($cliente['cpf_cnpj'])) $asaasData['cpfCnpj'] = $cliente['cpf_cnpj'];
          if (!empty($_POST['cep'])) $asaasData['postalCode'] = $_POST['cep'];
          if (!empty($_POST['rua'])) $asaasData['address'] = $_POST['rua'];
          if (!empty($_POST['numero'])) $asaasData['addressNumber'] = $_POST['numero'];
          if (!empty($_POST['complemento'])) $asaasData['complement'] = $_POST['complemento'];
          if (!empty($_POST['bairro'])) $asaasData['province'] = $_POST['bairro'];
          if (!empty($_POST['cidade'])) $asaasData['city'] = $_POST['cidade'];
          if (!empty($_POST['estado'])) $asaasData['state'] = $_POST['estado'];
          if (!empty($_POST['pais'])) $asaasData['country'] = $_POST['pais'];
          if (!empty($_POST['referencia_externa'])) $asaasData['externalReference'] = $_POST['referencia_externa'];
          if (!empty($_POST['observacoes'])) $asaasData['observations'] = $_POST['observacoes'];
          if (!empty($_POST['razao_social'])) $asaasData['company'] = $_POST['razao_social'];
          try {
            $asaasService->updateCustomer($cliente['asaas_id'], $asaasData);
            echo '<div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:8px;margin-bottom:18px;">Dados atualizados com sucesso e sincronizados com o Asaas!</div>';
          } catch (\Exception $e) {
            echo '<div style="background:#fef3c7;color:#92400e;padding:12px 18px;border-radius:8px;margin-bottom:18px;">Dados atualizados localmente, mas houve erro ao sincronizar com o Asaas: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }
        } else {
          echo '<div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:8px;margin-bottom:18px;">Dados atualizados com sucesso!</div>';
        }
        // Redirecionar para a listagem de clientes
        echo '<script>setTimeout(function(){window.location.href="clientes.php"},1200);</script>';
      } else {
        echo '<div style="background:#fef3c7;color:#92400e;padding:12px 18px;border-radius:8px;margin-bottom:18px;">Erro ao atualizar no banco: ' . htmlspecialchars($mysqli->error) . '</div>';
      }
    } else {
      echo '<div style="background:#fef3c7;color:#92400e;padding:12px 18px;border-radius:8px;margin-bottom:18px;">Nenhum campo para atualizar foi enviado.</div>';
    }
  }
}
echo '<style>.painel-input{width:100%;padding:6px 8px;border-radius:6px;border:1.5px solid #d1d5db;font-size:1rem;margin:2px 0 2px 0;}</style>';
}
?>