<?php
// Componente reutilizável: renderiza o bloco completo de detalhes do cliente (com abas, cards, etc.)

// Função auxiliar para formatar campos
function formatar_campo($campo, $valor) {
  if ($valor === null || $valor === '' || $valor === '0-0-0' || $valor === '0000-00-00') return '—';
  $labels = [
    'nome' => 'Nome', 'contact_name' => 'Contato Principal', 'cpf_cnpj' => 'CPF/CNPJ', 'razao_social' => 'Razão Social',
    'data_criacao' => 'Data de Criação', 'data_atualizacao' => 'Data de Atualização', 'asaas_id' => 'ID Asaas',
    'referencia_externa' => 'Referência Externa', 'criado_em_asaas' => 'Criado no Asaas', 'email' => 'E-mail',
    'emails_adicionais' => 'E-mails Adicionais', 'telefone' => 'Telefone', 'celular' => 'Celular', 'cep' => 'CEP',
    'rua' => 'Rua', 'numero' => 'Número', 'complemento' => 'Complemento', 'bairro' => 'Bairro', 'cidade' => 'Cidade',
    'estado' => 'Estado', 'pais' => 'País', 'id' => 'ID', 'observacoes' => 'Observações', 'plano' => 'Plano', 'status' => 'Status',
    'notificacao_desativada' => 'Notificações Desativadas'
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
  
  // Notificação desativada (boolean)
  if ($campo === 'notificacao_desativada') {
    return "$label: " . ($valor ? 'Sim' : 'Não');
  }
  
  // Label padrão
  return "$label: $valor";
}

// Função para renderizar campo editável
function render_campo_editavel($campo, $valor, $cliente_id, $cliente) {
  $valor_exibicao = $valor;
  if ($valor === null || $valor === '' || $valor === '0-0-0' || $valor === '0000-00-00') {
    $valor_exibicao = '—';
  }
  
  // Campos que não devem ser editáveis
  $campos_nao_editaveis = ['id', 'data_criacao', 'data_atualizacao'];
  
  if (in_array($campo, $campos_nao_editaveis)) {
    // Campo somente leitura
    if ($campo === 'id') {
      return '<span style="font-family:monospace; background:#f3f4f6; padding:4px 8px; border-radius:6px; color:#7c2ae8;">' . htmlspecialchars($valor_exibicao) . '</span>';
    } elseif (in_array($campo, ['data_criacao', 'data_atualizacao'])) {
      return '<span style="color:#64748b; font-size:0.9em;">' . htmlspecialchars($valor_exibicao) . '</span>';
    }
  }
  
  // Campo editável
  $valor_original = $valor ?? '';
  $placeholder = '';
  
  // Placeholders específicos
  if ($campo === 'contact_name') $placeholder = 'Ex: João Silva';
  elseif ($campo === 'email') $placeholder = 'exemplo@email.com';
  elseif ($campo === 'telefone') $placeholder = '(11) 99999-9999';
  elseif ($campo === 'celular') $placeholder = '(11) 99999-9999';
  elseif ($campo === 'cpf_cnpj') $placeholder = '123.456.789-00';
  elseif ($campo === 'cep') $placeholder = '12345-678';
  
  // Caso especial para celular com link WhatsApp
  if ($campo === 'celular' && !empty($valor)) {
    $celularLimpo = preg_replace('/\D/', '', $valor);
    if (strlen($celularLimpo) === 11 && strpos($celularLimpo, '55') !== 0) {
      $celularLimpo = '55' . $celularLimpo;
    }
    if (preg_match('/^55\d{11}$/', $celularLimpo)) {
      return '<span class="campo-editavel" data-campo="' . $campo . '" data-valor="' . htmlspecialchars($valor_original) . '" data-placeholder="' . $placeholder . '">
        <a href="#" class="abrir-whats-url" style="color:#25D366;text-decoration:underline;" title="Abrir chat interno" data-numero="' . $celularLimpo . '" data-cliente-id="' . intval($cliente['id']) . '">' . htmlspecialchars($valor_exibicao) . '</a>
      </span>';
    }
  }
  
  return '<span class="campo-editavel" data-campo="' . $campo . '" data-valor="' . htmlspecialchars($valor_original) . '" data-placeholder="' . $placeholder . '">
    ' . htmlspecialchars($valor_exibicao) . '
  </span>';
}

function render_cliente_ficha($cliente_id, $modo_edicao = false) {
  global $mysqli;
  if (!$cliente_id) return;
  $res = $mysqli->query("SELECT * FROM clientes WHERE id = $cliente_id LIMIT 1");
  $cliente = $res ? $res->fetch_assoc() : null;
  if (!$cliente) {
    echo '<div class="painel-container"><div class="painel-header"><div class="painel-nome">Cliente não encontrado</div></div></div>';
    return;
  }
  
  // TODOS os campos da tabela clientes organizados por categoria
  $dados_pessoais = [
    'nome', 'contact_name', 'cpf_cnpj', 'razao_social'
  ];
  $contato = [
    'email', 'emails_adicionais', 'telefone', 'celular'
  ];
  $endereco = [
    'cep', 'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'pais'
  ];
  $sistema = [
    'id', 'asaas_id', 'data_criacao', 'data_atualizacao', 'criado_em_asaas'
  ];
  $configuracoes = [
    'notificacao_desativada', 'referencia_externa', 'observacoes'
  ];
  
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
.painel-aba { 
  background: #f3f4f6; 
  color: #7c2ae8; 
  border: none; 
  outline: none; 
  padding: 10px 22px; 
  border-radius: 8px 8px 0 0; 
  font-weight: 600; 
  font-size: 1rem; 
  cursor: pointer; 
  transition: background 0.18s, color 0.18s, transform 0.1s; 
  position: relative;
  z-index: 10;
  user-select: none;
}
.painel-aba:hover { 
  background: #e5e7eb; 
  color: #a259e6; 
  transform: translateY(-1px);
}
.painel-aba.active { 
  background: #fff; 
  color: #a259e6; 
  box-shadow: 0 -2px 8px #a259e610; 
  z-index: 20;
}
.painel-aba:active {
  transform: translateY(0);
}
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
  // Botão editar removido - agora usa apenas edição inline
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
  if ($modo_edicao) {
    // FORMULÁRIO DE EDIÇÃO (igual cliente_detalhes.php)
    echo '<form id="form-editar-cliente" method="post" autocomplete="off">';
    echo '<input type="hidden" name="id" value="' . $cliente_id . '">';
    echo '<div class="painel-tab painel-tab-dados" style="display:block;"><div class="painel-grid">';
    // Dados Pessoais
    echo '<div class="painel-card"><h4>👤 Dados Pessoais</h4><table><tbody>';
    // Nome
    echo '<tr><td class="font-semibold text-gray-600">Nome:</td><td><input type="text" name="nome" value="' . htmlspecialchars($cliente['nome'] ?? '') . '" class="painel-input"></td></tr>';
    // Contato Principal
    echo '<tr><td class="font-semibold text-gray-600">Contato Principal:</td><td><input type="text" name="contact_name" value="' . htmlspecialchars($cliente['contact_name'] ?? '') . '" class="painel-input" placeholder="Ex: João" autocomplete="off"></td></tr>';
    foreach ($dados_pessoais as $campo) {
      if (!isset($cliente[$campo]) || in_array($campo, ['nome','contact_name'])) continue;
      echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td>';
      echo '<td><input type="text" name="' . $campo . '" value="' . htmlspecialchars($cliente[$campo]) . '" class="painel-input"></td></tr>';
    }
    echo '</tbody></table></div>';
    // Contato
    echo '<div class="painel-card"><h4>✉️ Contato</h4><table><tbody>';
    foreach ($contato as $campo) {
      if (!isset($cliente[$campo])) continue;
      echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td>';
      if ($campo === 'emails_adicionais') {
        $valor = $cliente[$campo];
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
        $email_principal = $cliente['email'] ?? '';
        $emails = array_filter($emails, function($e) use ($email_principal) { return strtolower($e) !== strtolower($email_principal); });
        $input_value = $emails ? implode(', ', $emails) : '';
        echo '<td><input type="text" name="emails_adicionais" value="' . htmlspecialchars($input_value) . '" class="painel-input"></td>';
      } else {
        echo '<td><input type="text" name="' . $campo . '" value="' . htmlspecialchars($cliente[$campo]) . '" class="painel-input"></td>';
      }
      echo '</tr>';
    }
    echo '</tbody></table></div>';
    // Endereço
    echo '<div class="painel-card"><h4>📍 Endereço</h4><table><tbody>';
    foreach ($endereco as $campo) {
      if (!isset($cliente[$campo])) continue;
      echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td>';
      echo '<td><input type="text" name="' . $campo . '" value="' . htmlspecialchars($cliente[$campo]) . '" class="painel-input"></td></tr>';
    }
    echo '</tbody></table></div>';
    // Outros
    echo '<div class="painel-card"><h4>🗂️ Outros</h4><table><tbody>';
    foreach ($outros as $campo) {
      echo '<tr><td class="font-semibold text-gray-600">' . formatar_campo($campo, $cliente[$campo]) . '</td>';
      echo '<td><input type="text" name="' . $campo . '" value="' . htmlspecialchars($cliente[$campo]) . '" class="painel-input"></td></tr>';
    }
    echo '</tbody></table></div>';
    echo '</div></div>';
    echo '<div class="mt-6 text-right" style="text-align:right;margin-top:24px;">';
    echo '<button type="submit" class="bg-purple-600 hover:bg-purple-800 text-white px-6 py-2 rounded font-semibold text-base transition-colors">Salvar</button>';
    echo '<button type="button" id="btn-cancelar-edicao" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded font-semibold text-base transition-colors" style="margin-left:12px;">Cancelar</button>';
    echo '</div>';
    echo '</form>';
    // JS para AJAX
    echo "<script>
    document.getElementById('form-editar-cliente').onsubmit = function(e) {
      e.preventDefault();
      var form = this;
      var btn = form.querySelector('button[type=submit]');
      btn.disabled = true;
      btn.textContent = 'Salvando...';
      var formData = new FormData(form);
      
      // Debug: mostrar dados do formulário
      console.log('Dados do formulário:');
      for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
      }
      
      fetch('api/editar_cliente.php', {
        method: 'POST',
        body: formData
      })
      .then(r => {
        console.log('Status da resposta:', r.status);
        return r.json();
      })
      .then(resp => {
        console.log('Resposta do servidor:', resp);
        if (resp.success) {
          // Recarregar ficha em modo visualização
          var url = window.location.href.replace(/([&?])editar=1(&|$)/, '$1');
          if (url.indexOf('?') === -1) url += '?';
          url = url.replace(/([&?])$/, '');
          window.location.href = url;
        } else {
          alert('Erro ao salvar: ' + (resp.error || 'Erro desconhecido'));
        }
      })
      .catch(err => {
        console.error('Erro na requisição:', err);
        alert('Erro ao conectar com o servidor: ' + err.message);
      })
      .finally(() => { 
        btn.disabled = false; 
        btn.textContent = 'Salvar'; 
      });
      return false;
    };  
    document.getElementById('btn-cancelar-edicao').onclick = function() {
      var url = window.location.href.replace(/([&?])editar=1(&|$)/, '$1');
      if (url.indexOf('?') === -1) url += '?';
      url = url.replace(/([&?])$/, '');
      window.location.href = url;
    };
    </script>";
  } else {
    // Dados Gerais - MOSTRAR TODOS OS CAMPOS
    echo '<div class="painel-tab painel-tab-dados" style="display:block;">
      <div class="painel-grid">';
    
    // Dados Pessoais
    echo '<div class="painel-card"><h4>👤 Dados Pessoais</h4><table><tbody>';
    foreach ($dados_pessoais as $campo) {
      $label = ucfirst(str_replace('_', ' ', $campo));
      echo '<tr><td class="font-semibold text-gray-600">' . $label . ':</td><td>';
      echo render_campo_editavel($campo, $cliente[$campo] ?? null, $cliente_id, $cliente);
      echo '</td></tr>';
    }
    echo '</tbody></table></div>';
    
    // Contato
    echo '<div class="painel-card"><h4>✉️ Contato</h4><table><tbody>';
    foreach ($contato as $campo) {
      $label = ucfirst(str_replace('_', ' ', $campo));
      echo '<tr><td class="font-semibold text-gray-600">' . $label . ':</td><td>';
      echo render_campo_editavel($campo, $cliente[$campo] ?? null, $cliente_id, $cliente);
      echo '</td></tr>';
    }
    echo '</tbody></table></div>';
    
    // Endereço
    echo '<div class="painel-card"><h4>📍 Endereço</h4><table><tbody>';
    foreach ($endereco as $campo) {
      $label = ucfirst(str_replace('_', ' ', $campo));
      echo '<tr><td class="font-semibold text-gray-600">' . $label . ':</td><td>';
      echo render_campo_editavel($campo, $cliente[$campo] ?? null, $cliente_id, $cliente);
      echo '</td></tr>';
    }
    echo '</tbody></table></div>';
    
    // Sistema
    echo '<div class="painel-card"><h4>⚙️ Sistema</h4><table><tbody>';
    foreach ($sistema as $campo) {
      $label = ucfirst(str_replace('_', ' ', $campo));
      echo '<tr><td class="font-semibold text-gray-600">' . $label . ':</td><td>';
      echo render_campo_editavel($campo, $cliente[$campo] ?? null, $cliente_id, $cliente);
      echo '</td></tr>';
    }
    echo '</tbody></table></div>';
    
    // Configurações
    echo '<div class="painel-card"><h4>🔧 Configurações</h4><table><tbody>';
    foreach ($configuracoes as $campo) {
      $label = ucfirst(str_replace('_', ' ', $campo));
      echo '<tr><td class="font-semibold text-gray-600">' . $label . ':</td><td>';
      echo render_campo_editavel($campo, $cliente[$campo] ?? null, $cliente_id, $cliente);
      echo '</td></tr>';
    }
    echo '</tbody></table></div>';
    
    echo '</div></div>';
  }
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
  // Buscar apenas anotações manuais (não mensagens de conversa)
  $historico = [];
  $res_hist = $mysqli->query("SELECT m.*, c.nome_exibicao as canal_nome FROM mensagens_comunicacao m LEFT JOIN canais_comunicacao c ON m.canal_id = c.id WHERE m.cliente_id = $cliente_id AND m.tipo = 'anotacao' ORDER BY m.data_hora DESC");
  while ($msg = $res_hist && $res_hist->num_rows ? $res_hist->fetch_assoc() : null) $historico[] = $msg;
  if (empty($historico)) {
    echo '<div style="color:#64748b;font-style:italic;text-align:center;padding:40px 20px;">Nenhuma interação registrada para este cliente.</div>';
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
      
      // Botões de ação
      $acoes = '';
      // Apenas botão de excluir, discreto, e só para mensagens reais
      if ($id_msg > 0) {
        $acoes = '<div style="margin-top:8px;display:flex;gap:6px;justify-content:flex-end;">
          <button class="btn-excluir-msg" data-id="' . $id_msg . '" title="Excluir mensagem" style="background:none;color:#ef4444;border:none;padding:2px 6px;border-radius:4px;font-size:1.1em;cursor:pointer;opacity:0.7;">🗑️</button>
        </div>';
      }
      
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
  
  // Incluir e usar o componente financeiro reutilizável
  require_once __DIR__ . '/components_financeiro.php';
  render_componente_financeiro($cliente_id);
  
  echo '</div></div>';
  echo '</div>';
  
  // Adicionar JavaScript para as funções de ação
  echo '<script>
  function excluirCobranca(asaasPaymentId, cobrancaId) {
    if (!confirm("Tem certeza que deseja excluir esta cobrança?")) return;
    fetch("../api/excluir_cobranca.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ asaas_payment_id: asaasPaymentId, cobranca_id: cobrancaId })
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        alert("Cobrança excluída com sucesso!");
        location.reload();
      } else {
        alert("Erro ao excluir cobrança: " + (resp.error || "Erro desconhecido"));
      }
    })
    .catch(() => {
      alert("Erro ao conectar ao servidor.");
    });
  }

  function marcarRecebida(asaasPaymentId, cobrancaId) {
    if (!confirm("Confirmar recebimento desta cobrança?")) return;
    fetch("../api/marcar_recebida.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ asaas_payment_id: asaasPaymentId, cobranca_id: cobrancaId })
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        alert("Cobrança marcada como recebida!");
        location.reload();
      } else {
        alert("Erro ao marcar como recebida: " + (resp.error || "Erro desconhecido"));
      }
    })
    .catch(() => {
      alert("Erro ao conectar ao servidor.");
    });
  }
  </script>';
  
  // JS abas
  echo '<script>
  document.addEventListener("DOMContentLoaded", function() {
    console.log("Inicializando sistema de abas...");
    
    const abas = document.querySelectorAll(".painel-aba");
    const tabs = document.querySelectorAll(".painel-tab");
    
    console.log("Abas encontradas:", abas.length);
    console.log("Tabs encontradas:", tabs.length);
    
    abas.forEach(function(btn) {
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log("Aba clicada:", this.getAttribute("data-tab"));
        
        // Remove classe active de todas as abas
        abas.forEach(function(b) {
          b.classList.remove("active");
        });
        
        // Esconde todos os conteúdos das abas
        tabs.forEach(function(tab) {
          tab.style.display = "none";
        });
        
        // Adiciona classe active na aba clicada
        this.classList.add("active");
        
        // Mostra o conteúdo da aba correspondente
        const tabName = this.getAttribute("data-tab");
        const tabContent = document.querySelector(".painel-tab-" + tabName);
        
        console.log("Procurando tab:", ".painel-tab-" + tabName);
        console.log("Tab encontrada:", tabContent);
        
        if (tabContent) {
          tabContent.style.display = "block";
          console.log("Tab exibida:", tabName);
        } else {
          console.error("Tab não encontrada:", tabName);
        }
      });
    });
    
    // Garante que a primeira aba esteja ativa por padrão
    const primeiraAba = document.querySelector(".painel-aba");
    if (primeiraAba) {
      console.log("Ativando primeira aba por padrão");
      primeiraAba.click();
    }
  });
  </script>';
?>
<script>
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
      // Determinar o caminho correto da API
      var apiPath = (typeof getApiPath === 'function') ? getApiPath('salvar_anotacao_manual.php') : 'api/salvar_anotacao_manual.php';
      fetch(apiPath, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "cliente_id=<?php echo $cliente_id; ?>&titulo=" + encodeURIComponent(titulo) + "&anotacao=" + encodeURIComponent(anotacao)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.success) {
          document.getElementById("titulo-anotacao").value = "";
          document.getElementById("anotacao-manual").value = "";
          const mensagensArea = document.getElementById("mensagens-relacionamento");
          const hoje = new Date().toLocaleDateString("pt-BR");
          const agora = new Date().toLocaleTimeString("pt-BR", {hour: "2-digit", minute: "2-digit"});
          let grupoHoje = mensagensArea.querySelector("[data-data='" + hoje + "']");
          if (!grupoHoje) {
            grupoHoje = document.createElement("div");
            grupoHoje.setAttribute("data-data", hoje);
            grupoHoje.style = "margin-top:24px;margin-bottom:16px;";
            grupoHoje.innerHTML = '<div style="color:#7c2ae8;font-weight:bold;font-size:1.1em;margin-bottom:12px;padding:16px 12px;border-bottom:3px solid #7c2ae8;background:#f8fafc;border-radius:6px;">' + hoje + '</div>';
            mensagensArea.appendChild(grupoHoje);
          }
          const anotacaoDiv = document.createElement("div");
          anotacaoDiv.style = "background:#fef3c7;color:#23232b;border-radius:12px;padding:12px 16px;margin-bottom:12px;width:100%;max-width:100%;box-shadow:0 3px 12px rgba(0,0,0,0.15);display:block;word-wrap:break-word;border:1px solid #f59e0b;";
          anotacaoDiv.setAttribute("data-mensagem-id", resp.id);
          let conteudo = "<div style='font-size:0.9em;font-weight:600;margin-bottom:6px;opacity:0.9;'>Anotação <span style='font-size:0.85em;font-weight:400;margin-left:8px;'>Enviado às " + agora + "</span></div>";
          if (titulo) {
            conteudo += "<div style='font-weight:bold;margin-bottom:6px;color:#92400e;font-size:1.05em;'>" + titulo + "</div>";
          }
          conteudo += "<div class='mensagem-conteudo' style='line-height:1.4;white-space:pre-wrap;cursor:pointer;' title='Clique para editar'>" + anotacao + "</div>";
          conteudo += "<div style='margin-top:8px;display:flex;gap:6px;justify-content:flex-end;'><button class='btn-excluir-msg' data-id='" + resp.id + "' title='Excluir mensagem' style='background:none;color:#ef4444;border:none;padding:2px 6px;border-radius:4px;font-size:1.1em;cursor:pointer;opacity:0.7;'>🗑️</button></div>";
          anotacaoDiv.innerHTML = conteudo;
          grupoHoje.appendChild(anotacaoDiv);
          mensagensArea.scrollTop = mensagensArea.scrollHeight;
          anotacaoDiv.querySelector('.mensagem-conteudo').onclick = function(e) {
            if (this.querySelector('input')) return;
            var valorOriginal = this.textContent;
            var input = document.createElement('input');
            input.type = 'text';
            input.value = valorOriginal;
            input.style.width = '98%';
            input.style.fontSize = 'inherit';
            input.style.fontFamily = 'inherit';
            input.style.background = '#f3f4f6';
            input.style.border = '1px solid #a259e6';
            input.style.borderRadius = '6px';
            input.style.padding = '4px 8px';
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            input.select();
            function salvar() {
              var novoValor = input.value.trim();
              if (novoValor && novoValor !== valorOriginal) {
                var mensagemId = anotacaoDiv.getAttribute('data-mensagem-id');
                window.editarMensagem(mensagemId, novoValor);
                anotacaoDiv.querySelector('.mensagem-conteudo').textContent = novoValor;
              } else {
                anotacaoDiv.querySelector('.mensagem-conteudo').textContent = valorOriginal;
              }
            }
            input.onblur = salvar;
            input.onkeydown = function(e) {
              if (e.key === 'Enter') { e.preventDefault(); salvar(); }
              if (e.key === 'Escape') { anotacaoDiv.querySelector('.mensagem-conteudo').textContent = valorOriginal; }
            };
          };
          anotacaoDiv.querySelector('.btn-excluir-msg').onclick = function() {
            var mensagemId = this.getAttribute('data-id');
            window.excluirMensagem(mensagemId);
          };
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
</script>
<?php
  // Adicionar CSS e JavaScript para edição inline
  echo '<style>
  /* Estilos para campos editáveis inline */
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
  </style>';
  
  echo '<script>
  // Funcionalidade de edição inline
  document.addEventListener("DOMContentLoaded", function() {
    console.log("Iniciando edição inline no components_cliente.php...");
    
    function initEdicaoInline() {
      const campos = document.querySelectorAll(".campo-editavel");
      console.log("Campos encontrados:", campos.length);
      
      campos.forEach(function(campo) {
        campo.onclick = function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if (this.classList.contains("editando")) return;
          
          const valorOriginal = this.getAttribute("data-valor") || "";
          const nomeCampo = this.getAttribute("data-campo");
          
          console.log("Editando:", nomeCampo, valorOriginal);
          
          // Criar input
          const input = document.createElement("input");
          input.type = "text";
          input.value = valorOriginal;
          input.style.cssText = "border:none;outline:none;background:transparent;font-size:inherit;font-family:inherit;color:inherit;width:100%;min-width:200px;padding:0;margin:0;";
          
          // Substituir conteúdo
          this.innerHTML = "";
          this.appendChild(input);
          this.classList.add("editando");
          
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
            campo.innerHTML = "<span style=\"color: #7c2ae8;\">Salvando...</span>";
            campo.classList.add("salvando");
            
            // Enviar para servidor
            const formData = new FormData();
            formData.append("id", ' . json_encode($cliente_id) . ');
            formData.append(nomeCampo, novoValor);
            
            // Determinar o caminho correto da API baseado no contexto
            const apiPath = window.location.pathname.includes("/chat.php") ? "api/editar_cliente.php" : "../api/editar_cliente.php";
            
            fetch(apiPath, {
              method: "POST",
              body: formData
            })
            .then(function(response) {
              return response.json();
            })
            .then(function(data) {
              if (data.success) {
                // Sucesso
                campo.classList.remove("salvando", "editando");
                campo.classList.add("sucesso");
                campo.innerHTML = novoValor || "—";
                campo.setAttribute("data-valor", novoValor);
                
                // Atualizar nome no cabeçalho se for o campo nome
                if (nomeCampo === "nome") {
                  const nomeHeader = document.querySelector(".painel-nome");
                  if (nomeHeader) nomeHeader.textContent = novoValor;
                  const avatar = document.querySelector(".painel-avatar");
                  if (avatar) avatar.textContent = novoValor.charAt(0).toUpperCase();
                }
                
                setTimeout(function() {
                  campo.classList.remove("sucesso");
                }, 2000);
              } else {
                // Erro
                campo.classList.remove("salvando");
                campo.classList.add("erro");
                campo.innerHTML = "<span style=\"color: #ef4444;\">Erro ao salvar</span>";
                
                setTimeout(function() {
                  campo.classList.remove("erro");
                  campo.innerHTML = valorOriginal || "—";
                }, 3000);
              }
            })
            .catch(function(error) {
              // Erro de rede
              campo.classList.remove("salvando");
              campo.classList.add("erro");
              campo.innerHTML = "<span style=\"color: #ef4444;\">Erro de conexão</span>";
              
              setTimeout(function() {
                campo.classList.remove("erro");
                campo.innerHTML = valorOriginal || "—";
              }, 3000);
            });
          }
          
          // Função para cancelar
          function cancelar() {
            campo.classList.remove("editando");
            campo.innerHTML = valorOriginal || "—";
          }
          
          // Event listeners
          input.onkeydown = function(e) {
            if (e.key === "Enter") {
              e.preventDefault();
              salvar();
            } else if (e.key === "Escape") {
              e.preventDefault();
              cancelar();
            }
          };
          
          input.onblur = function() {
            setTimeout(function() {
              if (campo.classList.contains("editando")) {
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
  </script>';
  // Incluir o arquivo JavaScript com as funções de edição e exclusão (garantir que seja incluído antes do HTML)
  echo '<script src="../assets/chat-functions.js"></script>';
  echo '<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Edição inline ao clicar no texto da mensagem
    document.querySelectorAll(".mensagem-conteudo").forEach(function(span) {
      span.style.cursor = "pointer";
      span.title = "Clique para editar";
      span.onclick = function(e) {
        if (span.querySelector("input")) return; // já está editando
        var valorOriginal = span.textContent;
        var input = document.createElement("input");
        input.type = "text";
        input.value = valorOriginal;
        input.style.width = "98%";
        input.style.fontSize = "inherit";
        input.style.fontFamily = "inherit";
        input.style.background = "#f3f4f6";
        input.style.border = "1px solid #a259e6";
        input.style.borderRadius = "6px";
        input.style.padding = "4px 8px";
        span.innerHTML = "";
        span.appendChild(input);
        input.focus();
        input.select();
        // Salvar ao sair do campo ou pressionar Enter
        function salvar() {
          var novoValor = input.value.trim();
          if (novoValor && novoValor !== valorOriginal) {
            var mensagemId = span.closest("[data-mensagem-id]").getAttribute("data-mensagem-id");
            window.editarMensagem(mensagemId, novoValor);
            span.textContent = novoValor;
          } else {
            span.textContent = valorOriginal;
          }
        }
        input.onblur = salvar;
        input.onkeydown = function(e) {
          if (e.key === "Enter") { e.preventDefault(); salvar(); }
          if (e.key === "Escape") { span.textContent = valorOriginal; }
        };
      };
    });
    // Exclusão
    document.querySelectorAll(".btn-excluir-msg").forEach(function(btn) {
      btn.onclick = function() {
        var mensagemId = this.getAttribute("data-id");
        window.excluirMensagem(mensagemId);
      };
    });
  });
  </script>';
} 