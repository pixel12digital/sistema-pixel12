<?php
// Componente financeiro reutilizável
function render_componente_financeiro($cliente_id) {
  global $mysqli;
  
  if (!$cliente_id) {
    echo '<div style="text-align: center; color: #94a3b8; padding: 40px; font-style: italic;">Cliente não especificado.</div>';
    return;
  }
  
  // Buscar cobranças do cliente (igual ao modal de faturas)
  $cobrancas = [];
  $total_pago = $total_aberto = $total_vencido = 0.0;
  
  $res_cob = $mysqli->query("SELECT * FROM cobrancas WHERE cliente_id = $cliente_id ORDER BY vencimento DESC");
  if ($res_cob) {
    while ($cob = $res_cob->fetch_assoc()) {
      $cobrancas[] = $cob;
      $valor = floatval($cob['valor']);
      if ($cob['status'] === 'RECEIVED' || $cob['status'] === 'PAID') {
        $total_pago += $valor;
      } elseif ($cob['status'] === 'PENDING' && strtotime($cob['vencimento']) < time()) {
        $total_vencido += $valor;
      } elseif ($cob['status'] === 'PENDING') {
        $total_aberto += $valor;
      }
    }
  }
  
  // Resumo financeiro (exatamente como no modal de faturas)
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
  
  // Tabela de cobranças (exatamente como no modal de faturas)
  echo '<div style="overflow-x:auto; max-height:400px; overflow-y:auto;">
    <table class="w-full text-sm mb-6" style="border-collapse: collapse; width: 100%;">
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
      $status_color = $cob['status'] === 'RECEIVED' || $cob['status'] === 'PAID' ? '#059669' : ($cob['status'] === 'PENDING' ? '#7c3aed' : '#dc2626');
      
      echo '<tr style="border-bottom:1px solid #f1f5f9;">
        <td style="padding:10px;font-weight:500;">' . ($i+1) . '</td>
        <td style="padding:10px;font-weight:600;">R$ ' . number_format($cob['valor'],2,',','.') . '</td>
        <td style="padding:10px;">' . date('d/m/Y', strtotime($cob['vencimento'])) . '</td>
        <td style="padding:10px;">
          <span class="status-clicavel" style="color:' . $status_color . ';font-weight:500;cursor:pointer;text-decoration:underline;" onclick="abrirMenuStatusCobranca(\'' . htmlspecialchars($cob['asaas_payment_id']) . '\', ' . (int)$cob['id'] . ', \'' . htmlspecialchars($cob['status']) . '\', this)">' . htmlspecialchars($status_pt) . '</span>
        </td>
        <td style="padding:10px;">' . ($cob['data_pagamento'] ? date('d/m/Y', strtotime($cob['data_pagamento'])) : '—') . '</td>
        <td style="padding:10px;">
          ' . (!empty($cob['url_fatura']) ? '<a href="' . htmlspecialchars($cob['url_fatura']) . '" target="_blank" style="color:#7c2ae8;text-decoration:underline;font-weight:500;">Ver Fatura</a>' : '—') . '
          <button onclick="excluirCobranca(\'' . htmlspecialchars($cob['asaas_payment_id']) . '\', ' . (int)$cob['id'] . ')" style="margin-left:8px;background:#ef4444;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:0.9em;cursor:pointer;">Excluir</button>';
      
      if (in_array($cob['status'], ['PENDING','OVERDUE'])) {
        echo '<button onclick="marcarRecebida(\'' . htmlspecialchars($cob['asaas_payment_id']) . '\', ' . (int)$cob['id'] . ')" style="margin-left:8px;background:#059669;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:0.9em;cursor:pointer;">Marcar como Recebida</button>';
      }
      
      echo '</td>
      </tr>';
    }
  }
  
  echo '</tbody></table></div>';
  
  // JavaScript para as funções de ação (exatamente como no modal de faturas)
  echo '<script>
  // Detectar o contexto para usar o caminho correto da API
  function getApiPath(endpoint) {
    const currentPath = window.location.pathname;
    if (currentPath.includes("/chat.php")) {
      return "../api/" + endpoint;
    } else {
      return "api/" + endpoint;
    }
  }

  function excluirCobranca(asaasPaymentId, cobrancaId) {
    if (!confirm("Tem certeza que deseja excluir esta cobrança?")) return;
    fetch(getApiPath("excluir_cobranca.php"), {
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
    fetch(getApiPath("marcar_recebida.php"), {
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

  function abrirMenuStatusCobranca(asaasPaymentId, cobrancaId, status, el) {
    // Remove menu anterior, se existir
    document.querySelectorAll(".menu-status-cobranca").forEach(e => e.remove());
    // Cria menu
    const menu = document.createElement("div");
    menu.className = "menu-status-cobranca";
    menu.style = "position:absolute;z-index:9999;background:#fff;border:1.5px solid #7c2ae8;border-radius:8px;box-shadow:0 4px 16px #7c2ae820;padding:8px 0;min-width:160px;top:" + (el.getBoundingClientRect().bottom + window.scrollY + 4) + "px;left:" + (el.getBoundingClientRect().left + window.scrollX) + "px;";
    if (status === "PENDING" || status === "OVERDUE") {
      menu.innerHTML += "<div style=\"padding:8px 18px;cursor:pointer;color:#059669;font-weight:500;\" onmouseover=\"this.style.background=\'#f0fdf4\'\" onmouseout=\"this.style.background=\'#fff\'\" onclick=\"marcarRecebida(\'" + asaasPaymentId + "\'," + cobrancaId + ");this.parentNode.remove();\">Marcar como Recebido</div>";
    }
    menu.innerHTML += "<div style=\"padding:8px 18px;cursor:pointer;color:#ef4444;font-weight:500;\" onmouseover=\"this.style.background=\'#fef2f2\'\" onmouseout=\"this.style.background=\'#fff\'\" onclick=\"excluirCobranca(\'" + asaasPaymentId + "\'," + cobrancaId + ");this.parentNode.remove();\">Excluir</div>";
    menu.innerHTML += "<div style=\"padding:8px 18px;cursor:pointer;color:#64748b;\" onmouseover=\"this.style.background=\'#f1f5f9\'\" onmouseout=\"this.style.background=\'#fff\'\" onclick=\"this.parentNode.remove();\">Cancelar</div>";
    document.body.appendChild(menu);
    // Fechar menu ao clicar fora
    setTimeout(() => {
      document.addEventListener("mousedown", function fecharMenu(e) {
        if (!menu.contains(e.target)) { 
          menu.remove(); 
          document.removeEventListener("mousedown", fecharMenu); 
        }
      });
    }, 10);
  }
  </script>';
}
?> 