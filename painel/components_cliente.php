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
.painel-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; grid-auto-rows: auto !important; gap: 24px !important; align-items: start !important; }
.painel-abas { display: flex; gap: 0.5rem; margin-bottom: 24px; margin-top: 8px; }
.painel-aba { background: #f3f4f6; color: #7c2ae8; border: none; outline: none; padding: 10px 22px; border-radius: 8px 8px 0 0; font-weight: 600; font-size: 1rem; cursor: pointer; transition: background 0.18s, color 0.18s; }
.painel-aba.active, .painel-aba:hover { background: #fff; color: #a259e6; box-shadow: 0 -2px 8px #a259e610; }
.painel-tabs-content { min-height: 320px; }
.painel-tab { display: none; }
.painel-tab[style*="display:block"] { display: block !important; }

/* Barra de rolagem personalizada */
#mensagens-relacionamento::-webkit-scrollbar {
  width: 14px;
}

#mensagens-relacionamento::-webkit-scrollbar-track {
  background: #e2e8f0;
  border-radius: 7px;
  border: 1px solid #cbd5e1;
  margin: 4px 0;
}

#mensagens-relacionamento::-webkit-scrollbar-thumb {
  background: #7c3aed;
  border-radius: 7px;
  border: 1px solid #6d28d9;
  min-height: 40px;
}

#mensagens-relacionamento::-webkit-scrollbar-thumb:hover {
  background: #6d28d9;
}

#mensagens-relacionamento::-webkit-scrollbar-thumb:active {
  background: #5b21b6;
}

#mensagens-relacionamento::-webkit-scrollbar-button {
  height: 20px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 3px;
  display: block;
}

#mensagens-relacionamento::-webkit-scrollbar-button:hover {
  background: #e2e8f0;
}

#mensagens-relacionamento::-webkit-scrollbar-button:active {
  background: #cbd5e1;
}

#mensagens-relacionamento::-webkit-scrollbar-button:single-button {
  display: block;
}

#mensagens-relacionamento::-webkit-scrollbar-button:single-button:vertical:decrement {
  border-bottom: 1px solid #cbd5e1;
}

#mensagens-relacionamento::-webkit-scrollbar-button:single-button:vertical:increment {
  border-top: 1px solid #cbd5e1;
}

/* Para Firefox */
#mensagens-relacionamento {
  scrollbar-width: auto;
  scrollbar-color: #7c3aed #e2e8f0;
}

/* Garantir que a rolagem funcione */
#mensagens-relacionamento {
  overflow-y: scroll !important;
  padding-right: 8px !important;
  scrollbar-gutter: stable;
  box-sizing: border-box;
}

/* Garantir que o container tenha espaço suficiente */
.painel-tab-relacionamento .painel-card {
  overflow: hidden !important;
  box-sizing: border-box;
}

/* Espaçamento adicional para a barra de rolagem */
.painel-tab-relacionamento {
  padding-right: 4px;
}

/* Padronizar todas as abas com o mesmo tamanho e estilo */
.painel-tab {
  min-height: 500px !important;
  position: relative !important;
  padding-bottom: 20px !important;
  padding-right: 12px !important;
  background: #fff !important;
  color: #23232b !important;
  box-sizing: border-box !important;
  overflow-y: auto !important;
}

/* Padronizar cards dentro das abas */
.painel-tab .painel-card {
  background: #fff !important;
  color: #23232b !important;
  position: relative !important;
  padding: 24px 20px !important;
  margin-bottom: 24px !important;
  box-sizing: border-box !important;
  border-radius: 16px !important;
  box-shadow: 0 6px 24px rgba(124,42,232,0.12), 0 2px 12px rgba(0,0,0,0.10) !important;
  border: 1.5px solid #ede9fe !important;
  transition: box-shadow 0.2s;
}

/* Padronizar títulos das abas */
.painel-tab h4 {
  color: #7c2ae8 !important;
  font-size: 1.1rem !important;
  margin-bottom: 16px !important;
  font-weight: 600 !important;
}

/* Área de conteúdo padronizada */
.painel-tabs-content {
  min-height: 500px !important;
  position: relative !important;
  box-sizing: border-box !important;
}

/* Especial para aba de relacionamento com limitação de altura */
.painel-tab-relacionamento .painel-card {
  min-height: 500px !important;
  max-height: calc(80vh - 32px) !important;
  padding-bottom: 100px !important;
  overflow: hidden !important;
}
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
  echo '<div class="painel-tab painel-tab-dados" style="display:block;">
      <div class="painel-grid">';
  // Dados Pessoais
  echo '<div class="painel-card"><h4>👤 Dados Pessoais</h4><table><tbody>';
  // Nome
  echo '<tr><td class="font-semibold text-gray-600">Nome: ' . htmlspecialchars($cliente['nome'] ?? '') . '</td></tr>';
  // Contato Principal
  echo '<tr><td class="font-semibold text-gray-600">Contato Principal: ' . htmlspecialchars($cliente['contact_name'] ?? '—') . '</td></tr>';
  // Outros campos de dados pessoais (excluindo nome e contact_name que já foram exibidos)
  foreach ($dados_pessoais as $campo) {
    if (!isset($cliente[$campo]) || in_array($campo, ['nome','contact_name'])) continue;
    echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  }
  echo '</tbody></table></div>';
  
  // Contato
  echo '<div class="painel-card"><h4>✉️ Contato</h4><table><tbody>';
  foreach ($contato as $campo) {
    if (!isset($cliente[$campo])) continue;
    if ($campo === 'celular' && !empty($cliente[$campo])) {
      // Deixar o número clicável para abrir o chat
      $celularLimpo = preg_replace('/\D/', '', $cliente[$campo]);
      if (strlen($celularLimpo) === 11 && strpos($celularLimpo, '55') !== 0) {
        $celularLimpo = '55' . $celularLimpo;
      }
      // Garante que o link só é gerado se o número for válido
      if (preg_match('/^55\d{11}$/', $celularLimpo)) {
        echo '<tr><td class="font-semibold text-gray-600">Celular: <a href="#" class="abrir-whats-url" style="color:#25D366;text-decoration:underline;" title="Abrir chat interno" data-numero="' . $celularLimpo . '" data-cliente-id="' . intval($cliente['id']) . '">' . htmlspecialchars($cliente[$campo]) . '</a></td></tr>';
      } else {
        // Se não for válido, apenas exibe o número sem link
        echo '<tr><td class="font-semibold text-gray-600">Celular: ' . htmlspecialchars($cliente[$campo]) . '</td></tr>';
      }
    } else {
      echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
    }
  }
  echo '</tbody></table></div>';
  
  // Endereço
  echo '<div class="painel-card"><h4>📍 Endereço</h4><table><tbody>';
  foreach ($endereco as $campo) {
    if (!isset($cliente[$campo])) continue;
    echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  }
  echo '</tbody></table></div>';
  
  // Outros
  echo '<div class="painel-card"><h4>🗂️ Outros</h4><table><tbody>';
  foreach ($outros as $campo) {
    echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td></tr>';
  }
  echo '</tbody></table></div>';
  echo '</div></div>';
  // Projetos
  echo '<div class="painel-tab painel-tab-projetos" style="display:none;">
    <div class="painel-card">
      <h4>📁 Projetos</h4>
      <div style="padding: 20px; text-align: center; color: #64748b;">
        <p style="font-size: 1.1em; margin-bottom: 16px;">Lista de projetos relacionados ao cliente</p>
        <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; margin: 20px 0;">
          <div style="font-size: 3em; margin-bottom: 16px;">📁</div>
          <p style="font-size: 1.1em; color: #64748b; margin: 0;">Nenhum projeto cadastrado</p>
          <p style="font-size: 0.9em; color: #94a3b8; margin: 8px 0 0 0;">Os projetos aparecerão aqui quando forem adicionados</p>
        </div>
      </div>
    </div>
  </div>';
  // Suporte & Relacionamento
  echo '<div class="painel-tab painel-tab-relacionamento" style="display:none;">
    <div class="painel-card" style="background:#fff;color:#23232b; min-height:500px; max-height:calc(80vh - 32px); position:relative; padding-bottom:100px; padding-right:12px;">
      <h4 style="color:#7c2ae8;"> Suporte & Relacionamento</h4>
      <div id="mensagens-relacionamento" style="display: flex; flex-direction: column; gap: 12px; overflow-y: auto; max-height: calc(80vh - 220px); min-height: 200px; padding: 16px 8px 32px 16px; height: calc(80vh - 220px); margin-right: 4px;">';
  // Mensagens e anexos
  $historico = [];
  $res_hist = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m LEFT JOIN canais_comunicacao c ON m.canal_id = c.id WHERE m.cliente_id = $cliente_id ORDER BY m.data_hora DESC");
  while ($msg = $res_hist && $res_hist->num_rows ? $res_hist->fetch_assoc() : null) $historico[] = $msg;
  if (empty($historico)) {
    echo '<div style="color:#64748b;font-style:italic;text-align:center;padding:40px 20px;">Nenhuma interação registrada para este cliente.</div>';
    // Adicionar mensagens de teste para garantir rolagem
    echo '<div style="margin-top:24px;margin-bottom:16px;">
      <div style="color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:12px;padding:16px 12px;border-bottom:3px solid #7c2ae8;background:#f8fafc;border-radius:6px;">15/07/2025</div>';
    for ($i = 1; $i <= 15; $i++) {
      echo '<div style="background:#7c2ae8;color:#fff;border-radius:12px;padding:12px 16px;margin-bottom:12px;width:100%;max-width:100%;box-shadow:0 3px 12px rgba(0,0,0,0.15);display:block;word-wrap:break-word;border:1px solid #6d28d9;" data-mensagem-id="' . $i . '">
        <div style="font-size:0.9em;font-weight:600;margin-bottom:6px;opacity:0.9;">Teste ' . $i . ' <span style="font-size:0.85em;font-weight:400;margin-left:8px;">Enviado às 10:' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</span></div>
        <div class="mensagem-conteudo" style="line-height:1.4;white-space:pre-wrap;">Esta é uma mensagem de teste ' . $i . ' para verificar se a rolagem está funcionando corretamente. Esta mensagem tem um conteúdo mais longo para garantir que ocupe espaço suficiente na tela.</div>
        <div style="margin-top:8px;display:flex;gap:6px;justify-content:flex-end;">
          <button onclick="editarMensagem(' . $i . ', \'Mensagem de teste ' . $i . '\')" style="background:#3b82f6;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8em;cursor:pointer;">Editar</button>
          <button onclick="excluirMensagem(' . $i . ')" style="background:#ef4444;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8em;cursor:pointer;">Excluir</button>
        </div>
      </div>';
    }
    echo '</div>';
  } else {
    $ultimo_dia = '';
    foreach ($historico as $msg) {
      $dia = date('d/m/Y', strtotime($msg['data_hora']));
      if ($dia !== $ultimo_dia) {
        if ($ultimo_dia !== '') echo '</div>';
        echo '<div style="margin-top:24px;margin-bottom:16px;">
          <div style="color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:12px;padding:16px 12px;border-bottom:3px solid #7c2ae8;background:#f8fafc;border-radius:6px;">' . $dia . '</div>';
        $ultimo_dia = $dia;
      }
      $is_received = $msg['direcao'] === 'recebido';
      $is_anotacao = isset($msg['tipo']) && $msg['tipo'] === 'anotacao';
      $bubble = $is_anotacao ? 'background:#fbbf24;color:#23232b;' : ($is_received ? 'background:#23232b;color:#fff;' : 'background:#7c2ae8;color:#fff;');
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
      
      // Botões de ação
      $acoes = '';
      // Mostrar botões para todas as mensagens (não apenas anotações)
      $acoes = '<div style="margin-top:8px;display:flex;gap:6px;justify-content:flex-end;">
        <button onclick="editarMensagem(' . $id_msg . ', \'' . addslashes($mensagem_original) . '\')" style="background:#3b82f6;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8em;cursor:pointer;">Editar</button>
        <button onclick="excluirMensagem(' . $id_msg . ')" style="background:#ef4444;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8em;cursor:pointer;">Excluir</button>
      </div>';
      
      echo '<div style="' . $bubble . 'border-radius:12px;padding:12px 16px;margin-bottom:12px;width:100%;max-width:100%;box-shadow:0 3px 12px rgba(0,0,0,0.15);display:block;word-wrap:break-word;border:1px solid ' . ($is_anotacao ? '#f59e0b' : ($is_received ? '#374151' : '#6d28d9')) . ';" data-mensagem-id="' . $id_msg . '">
        <div style="font-size:0.9em;font-weight:600;margin-bottom:6px;opacity:0.9;">' . $canal . ' <span style="font-size:0.85em;font-weight:400;margin-left:8px;">' . ($is_received ? 'Recebido' : 'Enviado') . ' às ' . $hora . '</span></div>
        <div class="mensagem-conteudo" style="line-height:1.4;white-space:pre-wrap;">' . $conteudo . '</div>
        ' . $acoes . '
      </div>';
    }
    if ($ultimo_dia !== '') echo '</div>';
  }
  echo '</div>
      <!-- Espaçamento adicional para evitar que mensagens fiquem coladas no formulário -->
      <div style="height: 20px;"></div>
      <form id="form-anotacao-manual" method="post" style="position:absolute;left:0;right:0;bottom:0;display:flex;gap:8px;align-items:center;padding:18px 20px;background:#f1f5f9;border-top:3px solid #7c2ae8;z-index:10;box-shadow:0 -2px 8px rgba(124,42,232,0.1);">
        <input type="text" id="titulo-anotacao" placeholder="Título da anotação (opcional)" style="flex:1;padding:10px 12px;border:2px solid #cbd5e1;border-radius:8px;font-size:0.9em;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <input type="text" id="anotacao-manual" placeholder="Digite sua anotação..." style="flex:2;padding:10px 12px;border:2px solid #cbd5e1;border-radius:8px;font-size:0.9em;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <button type="submit" style="background:#7c2ae8;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:500;font-size:0.9em;transition:background 0.2s;box-shadow:0 2px 4px rgba(124,42,232,0.3);" onmouseover="this.style.background=\'#6d28d9\'" onmouseout="this.style.background=\'#7c2ae8\'">Salvar</button>
      </form>
    </div>
  </div>';
  // Financeiro
  echo '<div class="painel-tab painel-tab-financeiro" style="display:none;">
    <div class="painel-card">
      <h4>💸 Financeiro</h4>';
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
  echo '<div class="mb-4" style="background: #f8fafc; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
      <div style="flex: 1; min-width: 150px;">
        <div style="font-size: 0.9em; color: #64748b; margin-bottom: 4px;">Total Pago</div>
        <div style="font-size: 1.3em; font-weight: bold; color: #059669;">R$ ' . number_format($total_pago,2,',','.') . '</div>
      </div>
      <div style="flex: 1; min-width: 150px;">
        <div style="font-size: 0.9em; color: #64748b; margin-bottom: 4px;">Em Aberto</div>
        <div style="font-size: 1.3em; font-weight: bold; color: #7c3aed;">R$ ' . number_format($total_aberto,2,',','.') . '</div>
      </div>
      <div style="flex: 1; min-width: 150px;">
        <div style="font-size: 0.9em; color: #64748b; margin-bottom: 4px;">Vencido</div>
        <div style="font-size: 1.3em; font-weight: bold; color: #dc2626;">R$ ' . number_format($total_vencido,2,',','.') . '</div>
      </div>
    </div>
  </div>';
  echo '<div style="overflow-x:auto;"><table class="w-full text-sm mb-6" style="border-collapse: collapse; width: 100%;">
    <thead>
      <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
        <th colspan="6" style="text-align:left;color:#7c2ae8;font-weight:bold;padding:12px;font-size:1.1em;">Cobranças/Faturas (Banco Local)</th>
      </tr>
      <tr style="background: #f1f5f9;">
        <th style="padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-weight:600;">Nº</th>
        <th style="padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-weight:600;">Valor</th>
        <th style="padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-weight:600;">Vencimento</th>
        <th style="padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-weight:600;">Status</th>
        <th style="padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-weight:600;">Pagamento</th>
        <th style="padding:10px;text-align:left;border-bottom:1px solid #e2e8f0;font-weight:600;">Fatura</th>
      </tr>
    </thead>
    <tbody>';
  if (empty($cobrancas)) {
    echo '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:40px;font-style:italic;">Nenhuma cobrança encontrada.</td></tr>';
  } else {
    foreach ($cobrancas as $i => $cob) {
      $status_map = [ 'RECEIVED' => 'RECEBIDO', 'PAID' => 'PAGO', 'PENDING' => 'PENDENTE', 'OVERDUE' => 'VENCIDO', 'CANCELLED' => 'CANCELADO', 'REFUNDED' => 'ESTORNADO', 'PROCESSING' => 'PROCESSANDO', 'AUTHORIZED' => 'AUTORIZADO', 'EXPIRED' => 'EXPIRADO', ];
      $status_pt = $status_map[$cob['status']] ?? $cob['status'];
      $status_color = $cob['status'] === 'RECEIVED' || $cob['status'] === 'PAID' ? '#059669' : ($cob['status'] === 'PENDING' ? '#7c3aed' : '#dc2626');
      echo '<tr style="border-bottom:1px solid #f1f5f9;">
        <td style="padding:10px;font-weight:500;">' . ($i+1) . '</td>
        <td style="padding:10px;font-weight:600;">R$ ' . number_format($cob['valor'],2,',','.') . '</td>
        <td style="padding:10px;">' . date('d/m/Y', strtotime($cob['vencimento'])) . '</td>
        <td style="padding:10px;"><span style="color:' . $status_color . ';font-weight:500;">' . htmlspecialchars($status_pt) . '</span></td>
        <td style="padding:10px;">' . ($cob['data_pagamento'] ? date('d/m/Y', strtotime($cob['data_pagamento'])) : '—') . '</td>
        <td style="padding:10px;">' . (!empty($cob['url_fatura']) ? '<a href="' . htmlspecialchars($cob['url_fatura']) . '" target="_blank" style="color:#7c2ae8;text-decoration:underline;font-weight:500;">Ver Fatura</a>' : '—') . '</td>
      </tr>';
    }
  }
  echo '</tbody></table></div>';
  echo '</div></div>';
  echo '</div>';
  // JS abas
  echo '<script>document.addEventListener("DOMContentLoaded",function(){const abas=document.querySelectorAll(".painel-aba");const tabs=document.querySelectorAll(".painel-tab");abas.forEach(btn=>{btn.addEventListener("click",function(){abas.forEach(b=>b.classList.remove("active"));this.classList.add("active");tabs.forEach(tab=>tab.style.display="none");document.querySelector(".painel-tab-"+this.dataset.tab).style.display="block";});});});</script>';
  
  // JavaScript para anotações manuais
  echo '<script>
  document.addEventListener("DOMContentLoaded", function() {
    const formAnotacao = document.getElementById("form-anotacao-manual");
    if (formAnotacao) {
      formAnotacao.addEventListener("submit", function(e) {
        e.preventDefault();
        const titulo = document.getElementById("titulo-anotacao").value.trim();
        const anotacao = document.getElementById("anotacao-manual").value.trim();
        if (!anotacao) return;
        
        const btn = formAnotacao.querySelector("button[type=submit]");
        btn.disabled = true;
        btn.textContent = "Salvando...";
        
        fetch("api/salvar_anotacao_manual.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "cliente_id=' . $cliente_id . '&titulo=" + encodeURIComponent(titulo) + "&anotacao=" + encodeURIComponent(anotacao)
        })
        .then(r => r.json())
        .then(resp => {
          if (resp.success) {
            // Limpar campos
            document.getElementById("titulo-anotacao").value = "";
            document.getElementById("anotacao-manual").value = "";
            
            // Adicionar anotação ao histórico
            const mensagensArea = document.getElementById("mensagens-relacionamento");
            const hoje = new Date().toLocaleDateString("pt-BR");
            const agora = new Date().toLocaleTimeString("pt-BR", {hour: "2-digit", minute: "2-digit"});
            
            // Verificar se já existe um grupo para hoje
            let grupoHoje = mensagensArea.querySelector("[data-data=\"" + hoje + "\"]");
            if (!grupoHoje) {
              grupoHoje = document.createElement("div");
              grupoHoje.setAttribute("data-data", hoje);
              grupoHoje.style = "margin-top:24px;margin-bottom:16px;";
              grupoHoje.innerHTML = "<div style=\"color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:12px;padding:16px 12px;border-bottom:3px solid #7c2ae8;background:#f8fafc;border-radius:6px;\">" + hoje + "</div>";
              mensagensArea.insertBefore(grupoHoje, mensagensArea.firstChild);
            }
            
            // Criar anotação
            const anotacaoDiv = document.createElement("div");
            anotacaoDiv.style = "background:#fbbf24;color:#23232b;border-radius:12px;padding:12px 16px;margin-bottom:12px;width:100%;max-width:100%;box-shadow:0 3px 12px rgba(0,0,0,0.15);display:block;word-wrap:break-word;border:1px solid #f59e0b;";
            anotacaoDiv.setAttribute("data-mensagem-id", resp.id);
            
            let conteudo = "<div style=\"font-size:0.9em;font-weight:600;margin-bottom:6px;opacity:0.9;\">Anotação <span style=\"font-size:0.85em;font-weight:400;margin-left:8px;\">Enviado às " + agora + "</span></div>";
            if (titulo) {
              conteudo += "<div style=\"font-weight:bold;margin-bottom:6px;color:#92400e;font-size:1.05em;\">" + titulo + "</div>";
            }
            conteudo += "<div class=\"mensagem-conteudo\" style=\"line-height:1.4;white-space:pre-wrap;\">" + anotacao + "</div>";
            conteudo += "<div style=\"margin-top:8px;display:flex;gap:6px;justify-content:flex-end;\">
              <button onclick=\"editarMensagem(" + resp.id + ", \'" + anotacao.replace(/\'/g, "\\\'") + "\')\" style=\"background:#3b82f6;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8em;cursor:pointer;\">Editar</button>
              <button onclick=\"excluirMensagem(" + resp.id + ")\" style=\"background:#ef4444;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.8em;cursor:pointer;\">Excluir</button>
            </div>";
            anotacaoDiv.innerHTML = conteudo;
            
            // Inserir no início do grupo de hoje
            grupoHoje.appendChild(anotacaoDiv);
            
            // Scroll para a nova anotação
            anotacaoDiv.scrollIntoView({behavior: "smooth"});
          } else {
            alert("Erro ao salvar anotação: " + (resp.error || ""));
          }
        })
        .catch(() => {
          alert("Erro ao conectar ao servidor.");
        })
        .finally(() => {
          btn.disabled = false;
          btn.textContent = "Salvar";
        });
      });
    }
  });
  </script>';
  
  echo '</div>';
} 