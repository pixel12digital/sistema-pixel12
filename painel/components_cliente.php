<?php
// Componente reutilizável: renderiza o bloco completo de detalhes do cliente (com abas, cards, etc.)
function render_cliente_ficha($cliente_id, $modo_edicao = false) {
  global $mysqli;
  if (!$cliente_id) return;
  $res = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id LIMIT 1");
  $cliente = $res ? $res->fetch_assoc() : null;
  if (!$cliente) {
    echo '<div class="painel-container"><div class="painel-header"><div class="painel-nome">Cliente não encontrado</div></div></div>';
    return;
  }
  $dados_pessoais = [
    'nome', 'contact_name', 'cpf_cnpj', 'razao_social', 'data_criacao', 'data_atualizacao', 'asaas_id', 'referencia_externa', 'criado_em_asaas'
  ];
  $contato = ['email', 'emails_adicionais', 'telefone', 'celular'];
  $endereco = ['cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais'];
  $outros = array_diff(array_keys($cliente ?? []), array_merge($dados_pessoais, $contato, $endereco));
  // Função auxiliar para formatar campos
  if (!function_exists('formatar_campo')) {
    function formatar_campo($campo, $valor) {
      if ($valor === null || $valor === '' || $valor === '0-0-0' || $valor === '0000-00-00') return '—';
      $labels = [
        'nome' => 'Nome', 'contact_name' => 'Contato', 'cpf_cnpj' => 'CPF/CNPJ', 'razao_social' => 'Razão Social',
        'data_criacao' => 'Data de Criação', 'data_atualizacao' => 'Data de Atualização', 'asaas_id' => 'ID Asaas',
        'referencia_externa' => 'Referência Externa', 'criado_em_asaas' => 'Criado no Asaas', 'email' => 'E-mail',
        'emails_adicionais' => 'E-mails Adicionais', 'telefone' => 'Telefone', 'celular' => 'Celular', 'cep' => 'CEP',
        'rua' => 'Rua', 'numero' => 'Número', 'complemento' => 'Complemento', 'bairro' => 'Bairro', 'cidade' => 'Cidade',
        'estado' => 'Estado', 'pais' => 'País', 'id' => 'ID', 'observacoes' => 'Observações', 'plano' => 'Plano', 'status' => 'Status',
      ];
      $label = $labels[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
      if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
        $data = substr($valor, 0, 10);
        $partes = explode('-', $data);
        if (count($partes) === 3) return "$label: {$partes[2]}/{$partes[1]}/{$partes[0]}";
      }
      if ($campo === 'cpf_cnpj' && preg_match('/^\d{11,14}$/', $valor)) {
        if (strlen($valor) === 11) {
          return "$label: " . preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $valor);
        } else {
          return "$label: " . preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $valor);
        }
      }
      if (($campo === 'telefone' || $campo === 'celular') && preg_match('/^\d{10,11}$/', $valor)) {
        return "$label: (" . substr($valor,0,2) . ") " . substr($valor,-9,-4) . '-' . substr($valor,-4);
      }
      return "$label: $valor";
    }
  }
  echo '<style>
.painel-container { max-width: 900px !important; margin: 40px auto !important; background: #f3f4f6 !important; border-radius: 18px !important; box-shadow: 0 6px 32px #7c2ae820, 0 2px 12px #0003 !important; padding: 32px 24px !important; }
.painel-card { background: #fff !important; border-radius: 16px !important; box-shadow: 0 6px 24px rgba(124,42,232,0.12), 0 2px 12px rgba(0,0,0,0.10) !important; padding: 24px 20px !important; margin-bottom: 24px !important; border: 1.5px solid #ede9fe !important; transition: box-shadow 0.2s; }
.painel-card:hover { box-shadow: 0 10px 32px rgba(124,42,232,0.18), 0 4px 16px rgba(0,0,0,0.13) !important; }
.painel-card h4 { color: #7c2ae8 !important; font-size: 1.1rem !important; margin-bottom: 12px !important; display: flex !important; align-items: center !important; gap: 8px !important; }
.painel-card table { width: 100% !important; font-size: 0.98rem !important; }
.painel-card td { padding: 4px 8px !important; border-bottom: 1.5px solid #888888 !important; }
.painel-card tr { border-bottom: none !important; }
.painel-avatar { width: 56px !important; height: 56px !important; border-radius: 50% !important; background: #ede9fe !important; color: #7c2ae8 !important; font-size: 2rem !important; font-weight: bold !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-right: 16px !important; }
.painel-header { display: flex !important; align-items: center !important; gap: 16px !important; margin-bottom: 12px !important; }
.painel-nome { font-size: 1.7rem !important; font-weight: bold !important; color: #7c2ae8 !important; }
.painel-badge { display: inline-block !important; background: #e0e7ff !important; color: #3730a3 !important; border-radius: 6px !important; padding: 2px 10px !important; font-size: 0.85rem !important; margin-left: 8px !important; }
@media (max-width: 900px) { .painel-grid { display: block !important; } .painel-card { margin-bottom: 18px !important; } .painel-container { padding: 12px 2vw !important; } }
.painel-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 24px !important; }
.painel-abas { display: flex; gap: 0.5rem; margin-bottom: 24px; margin-top: 8px; }
.painel-aba { background: #f3f4f6; color: #7c2ae8; border: none; outline: none; padding: 10px 22px; border-radius: 8px 8px 0 0; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.18s, color 0.18s; }
.painel-aba.active, .painel-aba:hover { background: #fff; color: #a259e6; box-shadow: 0 -2px 8px #a259e610; }
.painel-tabs-content { min-height: 320px; }
.painel-tab { display: none; }
.painel-tab[style*="display:block"] { display: block !important; }
</style>';
  echo '<div class="painel-container">';
  echo '<div class="painel-header">';
  echo '<div class="painel-avatar">' . strtoupper(substr($cliente['nome'] ?? '?', 0, 1)) . '</div>';
  echo '<div>';
  echo '<div class="painel-nome">' . htmlspecialchars($cliente['nome'] ?? 'Cliente não encontrado') . '</div>';
  if (!empty($cliente['status'])) echo '<span class="painel-badge" style="background:#d1fae5;color:#065f46;">Status: ' . htmlspecialchars($cliente['status']) . '</span>';
  if (!empty($cliente['plano'])) echo '<span class="painel-badge">Plano: ' . htmlspecialchars($cliente['plano']) . '</span>';
  echo '<div class="text-gray-500 text-sm">ID: ' . htmlspecialchars($cliente['id'] ?? '-') . ' | Asaas: ' . htmlspecialchars($cliente['asaas_id'] ?? '-') . '</div>';
  echo '</div>';
  // Não exibir botões de navegação/edição no modo embed (chat)
  echo '</div>';
  // Abas
  echo '<div class="painel-abas">';
  echo '<button class="painel-aba active" data-tab="dados">Dados Gerais</button>';
  echo '<button class="painel-aba" data-tab="projetos">Projetos</button>';
  echo '<button class="painel-aba" data-tab="relacionamento">Suporte & Relacionamento</button>';
  echo '<button class="painel-aba" data-tab="financeiro">Financeiro</button>';
  echo '</div>';
  echo '<div class="painel-tabs-content">';
  // Dados Gerais
  echo '<div class="painel-tab painel-tab-dados" style="display:block;">';
  echo '<div class="painel-grid">';
  // Dados Pessoais
  echo '<div class="painel-card"><h4>👤 Dados Pessoais</h4><table><tbody>';
  foreach ($dados_pessoais as $campo) if (isset($cliente[$campo])) echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  echo '</tbody></table></div>';
  // Contato
  echo '<div class="painel-card"><h4>✉️ Contato</h4><table><tbody>';
  foreach ($contato as $campo) if (isset($cliente[$campo])) echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  echo '</tbody></table></div>';
  // Endereço
  echo '<div class="painel-card"><h4>📍 Endereço</h4><table><tbody>';
  foreach ($endereco as $campo) if (isset($cliente[$campo])) echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  echo '</tbody></table></div>';
  // Outros
  echo '<div class="painel-card"><h4>🗂️ Outros</h4><table><tbody>';
  foreach ($outros as $campo) echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  echo '</tbody></table></div>';
  echo '</div></div>';
  // Projetos
  echo '<div class="painel-tab painel-tab-projetos" style="display:none;"><div class="painel-card"><h4>📁 Projetos</h4><p>Lista de projetos relacionados ao cliente.</p></div></div>';
  // Suporte & Relacionamento
  echo '<div class="painel-tab painel-tab-relacionamento" style="display:none;"><div class="painel-card" style="background:#181920;color:#fff;"><h4 style="color:#fff;"> Suporte & Relacionamento</h4>';
  // Mensagens e anexos
  $historico = [];
  $res_hist = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m LEFT JOIN canais_comunicacao c ON m.canal_id = c.id WHERE m.cliente_id = $cliente_id ORDER BY m.data_hora DESC");
  while ($msg = $res_hist && $res_hist->num_rows ? $res_hist->fetch_assoc() : null) $historico[] = $msg;
  if (empty($historico)) {
    echo '<div class="text-gray-500">Nenhuma interação registrada para este cliente.</div>';
  } else {
    $ultimo_dia = '';
    foreach ($historico as $msg) {
      $dia = date('d/m/Y', strtotime($msg['data_hora']));
      if ($dia !== $ultimo_dia) {
        if ($ultimo_dia !== '') echo '</div>';
        echo '<div style="margin-top:18px;"><div style="color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:6px;">' . $dia . '</div>';
        $ultimo_dia = $dia;
      }
      $is_received = $msg['direcao'] === 'recebido';
      $bubble = $is_received ? 'background:#23232b;color:#fff;' : 'background:#7c2ae8;color:#fff;';
      $canal = htmlspecialchars($msg['canal_nome'] ?? 'Canal');
      $hora = date('H:i', strtotime($msg['data_hora']));
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
      $conteudo .= htmlspecialchars($msg['mensagem']);
      echo '<div style="' . $bubble . 'border-radius:10px;padding:10px 16px;margin-bottom:8px;max-width:520px;box-shadow:0 1px 4px #0001;display:inline-block;">';
      echo '<div style="font-size:0.98em;font-weight:500;margin-bottom:2px;">' . $canal . ' <span style="font-size:0.92em;color:#888;font-weight:400;">' . ($is_received ? 'Recebido' : 'Enviado') . ' às ' . $hora . '</span></div>';
      echo $conteudo;
      echo '</div>';
    }
    if ($ultimo_dia !== '') echo '</div>';
  }
  echo '</div></div>';
  // Financeiro
  echo '<div class="painel-tab painel-tab-financeiro" style="display:none;"><div class="painel-card"><h4>💸 Financeiro</h4>';
  $cobrancas = [];
  $total_pago = $total_aberto = $total_vencido = 0.0;
  $res_cob = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = $cliente_id ORDER BY vencimento DESC");
  while ($cob = $res_cob && $res_cob->num_rows ? $res_cob->fetch_assoc() : null) {
    $cobrancas[] = $cob;
    $valor = floatval($cob['valor']);
    if ($cob['status'] === 'RECEIVED' || $cob['status'] === 'PAID') $total_pago += $valor;
    elseif ($cob['status'] === 'PENDING' && strtotime($cob['vencimento']) < time()) $total_vencido += $valor;
    elseif ($cob['status'] === 'PENDING') $total_aberto += $valor;
  }
  echo '<div class="mb-4"><b>Total pago:</b> R$ ' . number_format($total_pago,2,',','.') . ' | <b>Em aberto:</b> R$ ' . number_format($total_aberto,2,',','.') . ' | <b>Vencido:</b> R$ ' . number_format($total_vencido,2,',','.') . '</div>';
  echo '<div style="overflow-x:auto;"><table class="w-full text-sm mb-6"><thead><tr><th colspan="6" style="text-align:left;color:#7c2ae8;font-weight:bold;">Cobranças/Faturas (Banco Local)</th></tr><tr><th>Nº</th><th>Valor</th><th>Vencimento</th><th>Status</th><th>Pagamento</th><th>Fatura</th></tr></thead><tbody>';
  if (empty($cobrancas)) {
    echo '<tr><td colspan="6" class="text-center text-gray-400">Nenhuma cobrança encontrada.</td></tr>';
  } else {
    foreach ($cobrancas as $i => $cob) {
      $status_map = [ 'RECEIVED' => 'RECEBIDO', 'PAID' => 'PAGO', 'PENDING' => 'PENDENTE', 'OVERDUE' => 'VENCIDO', 'CANCELLED' => 'CANCELADO', 'REFUNDED' => 'ESTORNADO', 'PROCESSING' => 'PROCESSANDO', 'AUTHORIZED' => 'AUTORIZADO', 'EXPIRED' => 'EXPIRADO', ];
      $status_pt = $status_map[$cob['status']] ?? $cob['status'];
      echo '<tr><td>' . ($i+1) . '</td><td>R$ ' . number_format($cob['valor'],2,',','.') . '</td><td>' . date('d/m/Y', strtotime($cob['vencimento'])) . '</td><td>' . htmlspecialchars($status_pt) . '</td><td>' . ($cob['data_pagamento'] ? date('d/m/Y', strtotime($cob['data_pagamento'])) : '—') . '</td><td>' . (!empty($cob['url_fatura']) ? '<a href="' . htmlspecialchars($cob['url_fatura']) . '" target="_blank" style="color:#7c2ae8;">Ver Fatura</a>' : '—') . '</td></tr>';
    }
  }
  echo '</tbody></table></div>';
  echo '</div></div>';
  echo '</div>';
  // JS abas
  echo '<script>document.addEventListener("DOMContentLoaded",function(){const abas=document.querySelectorAll(".painel-aba");const tabs=document.querySelectorAll(".painel-tab");abas.forEach(btn=>{btn.addEventListener("click",function(){abas.forEach(b=>b.classList.remove("active"));this.classList.add("active");tabs.forEach(tab=>tab.style.display="none");document.querySelector(".painel-tab-"+this.dataset.tab).style.display="block";});});});</script>';
  echo '</div>';
} 