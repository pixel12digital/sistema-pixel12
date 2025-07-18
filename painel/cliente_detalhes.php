<?php
$page = 'clientes.php';
$page_title = 'Detalhes do Cliente';
$custom_header = '';
require_once 'config.php';
require_once 'db.php';

// Processa salvamento do formulário de edição do cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $campos = [
        'nome', 'contact_name', 'cpf_cnpj', 'razao_social', 'data_criacao', 'data_atualizacao', 'asaas_id', 'referencia_externa', 'criado_em_asaas',
        'email', 'emails_adicionais', 'telefone', 'celular',
        'cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais',
        'observacoes', 'plano', 'status'
    ];
    $set = [];
    $params = [];
    $types = '';
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $valor = trim($_POST[$campo]);
            // Limpar telefone/celular para conter apenas números
            if (in_array($campo, ['telefone', 'celular'])) {
                $valor = preg_replace('/\\D/', '', $valor);
            }
            // Limpar e padronizar emails_adicionais para texto simples
            if ($campo === 'emails_adicionais') {
                // Extrair todos os e-mails válidos
                preg_match_all('/[\w\.-]+@[\w\.-]+/', $valor, $matches);
                $emails = $matches[0];
                // Remover o e-mail principal dos adicionais
                $email_principal = $_POST['email'] ?? '';
                $emails = array_filter($emails, function($e) use ($email_principal) {
                  return strtolower($e) !== strtolower($email_principal);
                });
                $valor = $emails ? implode(', ', $emails) : '';
            }
            $set[] = "$campo = ?";
            $params[] = $valor;
            $types .= 's';
        }
    }
    if ($set) {
        $sql = "UPDATE clientes SET ".implode(', ', $set)." WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $params[] = $id;
        $types .= 'i';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: cliente_detalhes.php?id=$id");
    exit;
}

include 'template.php';
// Função para formatar campos (agora fora de render_content)
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
    $ddd = substr($valor, 0, 2);
    if (strlen($valor) === 11) {
      // Celular com nono dígito
      $parte1 = substr($valor, 2, 5);
      $parte2 = substr($valor, 7, 4);
      return "$label: ($ddd) $parte1-$parte2";
    } else {
      // Telefone fixo ou celular antigo
      $parte1 = substr($valor, 2, 4);
      $parte2 = substr($valor, 6, 4);
      return "$label: ($ddd) $parte1-$parte2";
    }
  }
  // E-mails Adicionais
  if ($campo === 'emails_adicionais' && !empty($valor)) {
    // Tenta decodificar JSON
    $emails = [];
    $json = json_decode($valor, true);
    if (is_array($json)) {
      foreach ($json as $email) {
        if (is_string($email)) $emails[] = $email;
        elseif (is_array($email)) $emails = array_merge($emails, $email);
      }
    } elseif (preg_match_all('/[\w\.-]+@[\w\.-]+/', $valor, $matches)) {
      $emails = $matches[0];
    }
    // Remover o e-mail principal dos adicionais
    global $cliente;
    $email_principal = $cliente['email'] ?? '';
    $emails = array_filter($emails, function($e) use ($email_principal) {
      return strtolower($e) !== strtolower($email_principal);
    });
    if ($emails) {
      return $label . ': ' . implode(', ', $emails);
    }
    // Se não conseguir decodificar, mostra o valor bruto
    return $label . ': ' . htmlspecialchars($valor);
  }
  // Label padrão
  return "$label: $valor";
}
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
  border-bottom: 1.5px solid #888888 !important;
}
.painel-card tr {
  border-bottom: none !important;
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
  </div>
  <!-- Abas modernas -->
  <div class="painel-abas">
    <button class="painel-aba active" data-tab="dados">Dados Gerais</button>
    <button class="painel-aba" data-tab="projetos">Projetos</button>
    <button class="painel-aba" data-tab="relacionamento">Suporte & Relacionamento</button>
    <button class="painel-aba" data-tab="financeiro">Financeiro</button>
  </div>
  <div class="painel-tabs-content">
    <div class="painel-tab painel-tab-dados" style="display:block;">
      <div class="painel-grid">
        <!-- Dados Pessoais -->
        <div class="painel-card">
          <h4>👤 Dados Pessoais</h4>
          <table>
            <tbody>
              <!-- Nome -->
              <tr>
                <td class="font-semibold text-gray-600">Nome:</td>
                <td>
                  <span class="campo-editavel" data-campo="nome" data-valor="<?= htmlspecialchars($cliente['nome'] ?? '') ?>">
                    <?= htmlspecialchars($cliente['nome'] ?? '') ?>
                  </span>
                </td>
              </tr>
              <!-- Contato Principal -->
              <tr>
                <td class="font-semibold text-gray-600">Contato Principal:</td>
                <td>
                  <span class="campo-editavel" data-campo="contact_name" data-valor="<?= htmlspecialchars($cliente['contact_name'] ?? '') ?>" data-placeholder="Ex: João">
                    <?= htmlspecialchars($cliente['contact_name'] ?? '—') ?>
                  </span>
                </td>
              </tr>
              <?php foreach ($dados_pessoais as $campo): if (!isset($cliente[$campo]) || in_array($campo, ['nome','contact_name'])) continue; ?>
                <tr>
                  <td class="font-semibold text-gray-600"><?= ucfirst(str_replace('_', ' ', $campo)) ?>:</td>
                  <td>
                    <?php if ($campo === 'asaas_id'): ?>
                      <span style="font-family:monospace; background:#f3f4f6; padding:4px 8px; border-radius:6px; color:#7c2ae8;"><?= htmlspecialchars($cliente[$campo]) ?></span>
                    <?php else: ?>
                      <span class="campo-editavel" data-campo="<?= $campo ?>" data-valor="<?= htmlspecialchars($cliente[$campo]) ?>">
                        <?= htmlspecialchars($cliente[$campo]) ?>
                      </span>
                    <?php endif; ?>
                  </td>
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
                  <td class="font-semibold text-gray-600"><?= ucfirst(str_replace('_', ' ', $campo)) ?>:</td>
                  <td>
                    <span class="campo-editavel" data-campo="<?= $campo ?>" data-valor="<?= htmlspecialchars($cliente[$campo]) ?>">
                      <?= htmlspecialchars($cliente[$campo]) ?>
                    </span>
                  </td>
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
                  <td class="font-semibold text-gray-600"><?= ucfirst(str_replace('_', ' ', $campo)) ?>:</td>
                  <td>
                    <span class="campo-editavel" data-campo="<?= $campo ?>" data-valor="<?= htmlspecialchars($cliente[$campo]) ?>">
                      <?= htmlspecialchars($cliente[$campo]) ?>
                    </span>
                  </td>
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
                  <td class="font-semibold text-gray-600"><?= ucfirst(str_replace('_', ' ', $campo)) ?>:</td>
                  <td>
                    <span class="campo-editavel" data-campo="<?= $campo ?>" data-valor="<?= htmlspecialchars($cliente[$campo]) ?>">
                      <?= htmlspecialchars($cliente[$campo]) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="painel-tab painel-tab-projetos" style="display:none;">
      <div class="painel-card"><h4>📁 Projetos</h4><p>Lista de projetos relacionados ao cliente.</p></div>
    </div>
    <div class="painel-tab painel-tab-relacionamento" style="display:none;">
      <div class="painel-card" style="background:#fff;color:#23232b; min-height:500px; max-height:calc(80vh - 32px); position:relative; padding-bottom:80px;">
      <h4 style="color:#7c2ae8;"> Suporte & Relacionamento</h4>
      <div id="mensagens-relacionamento" style="display: flex; flex-direction: column; gap: 10px; overflow-y: auto; max-height: calc(80vh - 180px); min-height: 200px; padding-bottom: 8px;">
      <?php
      // Buscar apenas anotações (não mensagens de conversa)
      $historico = [];
      if ($cliente_id) {
        $res_hist = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m LEFT JOIN canais_comunicacao c ON m.canal_id = c.id WHERE m.cliente_id = $cliente_id AND (m.tipo = 'anotacao' OR m.tipo IS NULL AND m.direcao = 'enviado' AND m.mensagem LIKE '%fatura%') ORDER BY m.data_hora DESC");
        while ($msg = $res_hist && $res_hist->num_rows ? $res_hist->fetch_assoc() : null) {
          $historico[] = $msg;
        }
      }
      if (empty($historico)) {
        echo '<div class="text-gray-500">Nenhuma interação registrada para este cliente.</div>';
      } else {
        $ultimo_dia = '';
        echo '<div style="display: flex; flex-direction: column; gap: 10px;">';
        foreach ($historico as $msg) {
          $dia = date('d/m/Y', strtotime($msg['data_hora']));
          if ($dia !== $ultimo_dia) {
            if ($ultimo_dia !== '') echo '</div>';
            echo '<div style="margin-top:18px;"><div style="color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:6px;">' . $dia . '</div>';
            $ultimo_dia = $dia;
          }
          $is_received = $msg['direcao'] === 'recebido';
          $is_anotacao = isset($msg['tipo']) && $msg['tipo'] === 'anotacao';
          $bubble = $is_anotacao ? 'background:#fef3c7;color:#23232b;' : ($is_received ? 'background:#23232b;color:#fff;' : 'background:#7c2ae8;color:#fff;');
          $canal = $is_anotacao ? 'Anotação' : htmlspecialchars($msg['canal_nome'] ?? 'Canal');
          $hora = date('H:i', strtotime($msg['data_hora']));
          $mensagem_original = $msg['mensagem'];
          $conteudo = '';
          if (!empty($msg['anexo'])) {
            $ext = strtolower(pathinfo($msg['anexo'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
              $conteudo .= '<a href="' . htmlspecialchars($msg['anexo']) . '" target="_blank"><img src="' . htmlspecialchars($msg['anexo']) . '" alt="anexo" style="max-width:160px;max-height:100px;border-radius:8px;box-shadow:0 1px 4px #0001;margin-bottom:4px;"></a><br>';
            } else {
              $nome_arquivo = basename($msg['anexo']);
              $conteudo .= '<a href="' . htmlspecialchars($msg['anexo']) . '" target="_blank" style="color:#7c2ae8;text-decoration:underline;"><span style="color:#7c2ae8;">📎</span> ' . htmlspecialchars($nome_arquivo) . '</a><br>';
            }
          }
          $conteudo .= htmlspecialchars($mensagem_original);
          $id_msg = intval($msg['id']);
          $is_enviado = !$is_received && !$is_anotacao;
          echo '<div style="' . $bubble . 'border-radius:10px;padding:10px 16px;margin-bottom:8px;max-width:520px;box-shadow:0 1px 4px #0001;display:block;position:relative;">';
          echo '<div style="font-size:0.98em;font-weight:500;margin-bottom:2px;">' . $canal . ' <span style="font-size:0.92em;color:#888;font-weight:400;">' . ($is_anotacao ? '' : ($is_received ? 'Recebido' : 'Enviado') . ' às ' . $hora) . '</span></div>';
          if ($is_anotacao && !empty($msg['titulo'])) {
            echo '<div style="font-weight:bold;color:#7c2ae8;font-size:1.08em;margin-bottom:2px;">' . htmlspecialchars($msg['titulo']) . '</div>';
          }
          echo '<span class="msg-text" data-id="' . $id_msg . '">' . $conteudo . '</span>';
          if ($is_enviado) {
            echo '<button class="btn-editar-msg" data-id="' . $id_msg . '" style="position:absolute;top:8px;right:36px;background:none;border:none;cursor:pointer;color:#fff;opacity:0.7;">✏️</button>';
            echo '<button class="btn-excluir-msg" data-id="' . $id_msg . '" style="position:absolute;top:8px;right:8px;background:none;border:none;cursor:pointer;color:#fff;opacity:0.7;">🗑️</button>';
          }
          echo '</div>';
        }
        if ($ultimo_dia !== '') echo '</div>';
        echo '</div>';
      }
      ?>
      </div>
      <form id="form-anotacao-manual" method="post" style="position:absolute;left:0;right:0;bottom:0;display:flex;gap:0;align-items:center;padding:18px 20px 18px 20px;background:#f9f9fb;border-top:1.5px solid #ede9fe;z-index:2;">
        <input id="anotacao-mensagem" name="mensagem" type="text" maxlength="1000" placeholder="Digite sua mensagem..." style="flex:1;border-radius:8px 0 0 8px;padding:14px 18px;font-size:1.08em;border:1.5px solid #e0e7ff;background:#fff;outline:none;transition:border 0.2s;">
        <button type="submit" style="background:#a259e6;color:#fff;font-weight:bold;padding:0 32px;height:48px;border:none;border-radius:0 8px 8px 0;cursor:pointer;font-size:1.08em;transition:background 0.2s;margin-left:-1px;">Enviar</button>
      </form>
      <div id="anotacao-msg-sucesso" style="display:none;color:#22c55e;font-weight:bold;margin-top:8px;position:absolute;left:0;right:0;bottom:70px;text-align:center;z-index:3;">Mensagem salva!</div>
      <style>
      #mensagens-relacionamento::-webkit-scrollbar { width: 8px; background: #ede9fe; }
      #mensagens-relacionamento::-webkit-scrollbar-thumb { background: #a259e6; border-radius: 8px; }
      #form-anotacao-manual input:focus {
        border-color: #a259e6;
        box-shadow: 0 0 0 2px #ede9fe;
      }
      #form-anotacao-manual button:hover {
        background: #7c2ae8;
      }
      @media (max-width: 700px) {
        #form-anotacao-manual { flex-direction:column; position:static; border-radius:0 0 16px 16px; }
        #form-anotacao-manual input, #form-anotacao-manual button {
          border-radius:8px !important;
          width:100%;
          margin:0;
        }
        #form-anotacao-manual button { margin-top:8px; height:44px; }
        #mensagens-relacionamento { max-height: 40vh; }
      }
      </style>
      </div>
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
        // $asaas_payments = $asaas_subs = [];
        // error_log('[DEBUG] Entrou na busca do Asaas. ID do cliente: ' . var_export($cliente['asaas_id'] ?? null, true));
        // if (!empty($cliente['asaas_id'])) {
        //     require_once __DIR__ . '/../src/Services/AsaasService.php';
        //     $asaasService = new \Services\AsaasService();
        //     try {
        //         error_log('[DEBUG] Chamando getCustomerPayments para ID: ' . $cliente['asaas_id']);
        //         $asaas_payments = $asaasService->getCustomerPayments($cliente['asaas_id']);
        //         $asaas_subs = $asaasService->getCustomerSubscriptions($cliente['asaas_id']);
        //     } catch (\Exception $e) {
        //         error_log('[DEBUG] Exceção ao buscar dados do Asaas: ' . $e->getMessage());
        //         echo '<div style="color:#e11d48;">Erro ao buscar dados do Asaas: '.htmlspecialchars($e->getMessage()).'</div>';
        //     }
        // }
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
                <td><?php
                  $status_map = [
                    'RECEIVED' => 'RECEBIDO',
                    'PAID' => 'PAGO',
                    'PENDING' => 'PENDENTE',
                    'OVERDUE' => 'VENCIDO',
                    'CANCELLED' => 'CANCELADO',
                    'REFUNDED' => 'ESTORNADO',
                    'PROCESSING' => 'PROCESSANDO',
                    'AUTHORIZED' => 'AUTORIZADO',
                    'EXPIRED' => 'EXPIRADO',
                  ];
                  $status_pt = $status_map[$cob['status']] ?? $cob['status'];
                  echo htmlspecialchars($status_pt);
                ?></td>
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
var clienteIdGlobal = <?= json_encode($cliente_id) ?>;
function ativarEdicaoMensagem(btn) {
  const id = btn.getAttribute('data-id');
  const span = document.querySelector('.msg-text[data-id="'+id+'"]');
  const oldText = span.textContent;
  const textarea = document.createElement('textarea');
  textarea.value = oldText;
  textarea.style.width = '96%';
  textarea.style.minHeight = '90px';
  textarea.style.borderRadius = '8px';
  textarea.style.marginTop = '12px';
  textarea.style.marginBottom = '12px';
  textarea.style.padding = '10px 12px';
  textarea.style.fontSize = '1.08em';
  textarea.style.background = '#fff';
  textarea.style.color = '#23232b';
  textarea.style.border = '2px solid #a259e6';
  textarea.style.boxShadow = '0 2px 12px #a259e620';
  const btnSalvar = document.createElement('button');
  btnSalvar.textContent = 'Salvar';
  btnSalvar.style = 'margin-left:0;margin-top:8px;background:#7c2ae8;color:#fff;border:none;padding:8px 32px;border-radius:8px;cursor:pointer;font-weight:bold;font-size:1.08em;display:block;width:100%;max-width:220px;';
  const card = span.closest('div[style*="border-radius"]');
  if (card) {
    card.style.background = '#fff';
    card.style.border = '2px solid #a259e6';
    card.style.boxShadow = '0 4px 24px #a259e620';
    card.style.maxWidth = '600px';
    card.style.width = '98%';
    card.style.padding = '24px 24px 18px 24px';
  }
  span.replaceWith(textarea);
  btn.style.display = 'none';
  textarea.after(btnSalvar);
  btnSalvar.onclick = function() {
    const painelRelacionamento = document.querySelector('.painel-tab-relacionamento .painel-card');
    fetch('/loja-virtual-revenda/painel/api/editar_mensagem_comunicacao.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id='+encodeURIComponent(id)+'&mensagem='+encodeURIComponent(textarea.value)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        fetch('/loja-virtual-revenda/painel/api/relacionamento_cliente.php?cliente_id=' + encodeURIComponent(clienteIdGlobal))
          .then(r => r.text())
          .then(html => {
            painelRelacionamento.innerHTML = html;
            painelRelacionamento.querySelectorAll('.btn-editar-msg').forEach(novoBtn => {
              novoBtn.addEventListener('click', function() {
                ativarEdicaoMensagem(novoBtn);
              });
            });
          });
      } else {
        alert('Erro ao salvar: ' + (resp.error || ''));
      }
    })
    .catch(() => alert('Erro ao salvar mensagem.'));
  };
}
document.addEventListener("DOMContentLoaded", function() {
  const abas = document.querySelectorAll(".painel-aba");
  const tabs = document.querySelectorAll(".painel-tab");
  // Restaurar aba ativa do localStorage, se houver
  const abaSalva = localStorage.getItem('aba_cliente_ativa');
  if (abaSalva) {
    abas.forEach(b => b.classList.remove("active"));
    tabs.forEach(tab => tab.style.display = "none");
    document.querySelector(".painel-aba[data-tab='"+abaSalva+"']").classList.add("active");
    document.querySelector(".painel-tab-"+abaSalva).style.display = "block";
    localStorage.removeItem('aba_cliente_ativa');
  }
  abas.forEach(btn => {
    btn.addEventListener("click", function() {
      abas.forEach(b => b.classList.remove("active"));
      this.classList.add("active");
      tabs.forEach(tab => tab.style.display = "none");
      document.querySelector(".painel-tab-"+this.dataset.tab).style.display = "block";
    });
  });
  document.querySelectorAll('.btn-editar-msg').forEach(btn => {
    btn.addEventListener('click', function() {
      ativarEdicaoMensagem(btn);
    });
  });
  document.querySelectorAll('.btn-excluir-msg').forEach(btn => {
    btn.addEventListener('click', function() {
      if (!confirm('Tem certeza que deseja excluir esta mensagem?')) return;
      const id = btn.getAttribute('data-id');
      fetch('/loja-virtual-revenda/painel/api/excluir_mensagem_comunicacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.success) {
          // Remove a mensagem da tela
          const bubble = btn.closest('div[style*="border-radius"]');
          if (bubble) bubble.remove();
        } else {
          alert('Erro ao excluir: ' + (resp.error || '') + '\n' + (resp.query || ''));
        }
      })
      .catch(() => alert('Erro ao excluir mensagem.'));
    });
  });
});
</script>
<style>
/* Estilos para campos editáveis inline - Versão simplificada */
.campo-editavel {
  cursor: pointer !important;
  padding: 4px 8px !important;
  border-radius: 6px !important;
  transition: all 0.2s ease !important;
  display: inline-block !important;
  min-width: 100px !important;
  border: 1px solid transparent !important;
  background-color: transparent !important;
}

.campo-editavel:hover {
  background-color: #f3f4f6 !important;
  border-color: #d1d5db !important;
}

.campo-editavel.editando {
  background-color: #fff !important;
  border-color: #7c2ae8 !important;
  box-shadow: 0 0 0 2px #ede9fe !important;
  padding: 6px 10px !important;
}

.campo-editavel input {
  border: none !important;
  outline: none !important;
  background: transparent !important;
  font-size: inherit !important;
  font-family: inherit !important;
  color: inherit !important;
  width: 100% !important;
  min-width: 200px !important;
  padding: 0 !important;
  margin: 0 !important;
}

.campo-editavel input:focus {
  outline: none !important;
}

.campo-editavel.salvando {
  opacity: 0.6 !important;
  pointer-events: none !important;
}

.campo-editavel.erro {
  border-color: #ef4444 !important;
  background-color: #fef2f2 !important;
}

.campo-editavel.sucesso {
  border-color: #22c55e !important;
  background-color: #f0fdf4 !important;
}
</style>
<script>
// Funcionalidade de edição inline - Versão simplificada
document.addEventListener('DOMContentLoaded', function() {
  console.log('Iniciando edição inline...');
  
  function initEdicaoInline() {
    const campos = document.querySelectorAll('.campo-editavel');
    console.log('Campos encontrados:', campos.length);
    
    campos.forEach(function(campo) {
      campo.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (this.classList.contains('editando')) return;
        
        const valorOriginal = this.getAttribute('data-valor') || '';
        const nomeCampo = this.getAttribute('data-campo');
        
        console.log('Editando:', nomeCampo, valorOriginal);
        
        // Criar input
        const input = document.createElement('input');
        input.type = 'text';
        input.value = valorOriginal;
        input.style.cssText = 'border:none;outline:none;background:transparent;font-size:inherit;font-family:inherit;color:inherit;width:100%;min-width:200px;padding:0;margin:0;';
        
        // Substituir conteúdo
        this.innerHTML = '';
        this.appendChild(input);
        this.classList.add('editando');
        
        // Focar no input
        setTimeout(function() {
          input.focus();
          input.select();
        }, 10);
        
        // Função para salvar
        function salvar() {
          const novoValor = input.value.trim();
          
          if (novoValor === valorOriginal) {
            cancelar();
            return;
          }
          
          // Mostrar salvando
          campo.innerHTML = '<span style="color: #7c2ae8;">Salvando...</span>';
          campo.classList.add('salvando');
          
          // Enviar para servidor
          const formData = new FormData();
          formData.append('id', <?= json_encode($cliente_id) ?>);
          formData.append(nomeCampo, novoValor);
          
          fetch('api/editar_cliente.php', {
            method: 'POST',
            body: formData
          })
          .then(function(response) {
            return response.json();
          })
          .then(function(data) {
            if (data.success) {
              // Sucesso
              campo.classList.remove('salvando', 'editando');
              campo.classList.add('sucesso');
              campo.innerHTML = novoValor || '—';
              campo.setAttribute('data-valor', novoValor);
              
              // Atualizar nome no cabeçalho se for o campo nome
              if (nomeCampo === 'nome') {
                const nomeHeader = document.querySelector('.painel-nome');
                if (nomeHeader) nomeHeader.textContent = novoValor;
                const avatar = document.querySelector('.painel-avatar');
                if (avatar) avatar.textContent = novoValor.charAt(0).toUpperCase();
              }
              
              setTimeout(function() {
                campo.classList.remove('sucesso');
              }, 2000);
            } else {
              // Erro
              campo.classList.remove('salvando');
              campo.classList.add('erro');
              campo.innerHTML = '<span style="color: #ef4444;">Erro ao salvar</span>';
              
              setTimeout(function() {
                campo.classList.remove('erro');
                campo.innerHTML = valorOriginal || '—';
              }, 3000);
            }
          })
          .catch(function(error) {
            // Erro de rede
            campo.classList.remove('salvando');
            campo.classList.add('erro');
            campo.innerHTML = '<span style="color: #ef4444;">Erro de conexão</span>';
            
            setTimeout(function() {
              campo.classList.remove('erro');
              campo.innerHTML = valorOriginal || '—';
            }, 3000);
          });
        }
        
        // Função para cancelar
        function cancelar() {
          campo.classList.remove('editando');
          campo.innerHTML = valorOriginal || '—';
        }
        
        // Event listeners
        input.onkeydown = function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            salvar();
          } else if (e.key === 'Escape') {
            e.preventDefault();
            cancelar();
          }
        };
        
        input.onblur = function() {
          setTimeout(function() {
            if (campo.classList.contains('editando')) {
              salvar();
            }
          }, 100);
        };
      };
    });
  }
  
  // Inicializar
  initEdicaoInline();
  
  // Reinicializar após um delay
  setTimeout(initEdicaoInline, 1000);
});
</script>
<?php
}
?>