<?php include_once __DIR__ . '/../../../../painel/menu_lateral.php'; ?>
// lista de faturas em $faturas

echo '<h1>Faturas</h1>';

// Formulário de filtros
$status_sel = isset($_GET['status']) ? $_GET['status'] : '';
$date_from_val = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to_val = isset($_GET['date_to']) ? $_GET['date_to'] : '';
echo '<form method="GET" style="margin-bottom:20px; display:flex; gap:10px; align-items:end;">';
echo '<label>Status <select name="status">
        <option value="">Todos</option>
        <option value="PENDING"' . ($status_sel=="PENDING"?' selected':'') . '>PENDENTE</option>
        <option value="RECEIVED"' . ($status_sel=="RECEIVED"?' selected':'') . '>PAGO</option>
        <option value="OVERDUE"' . ($status_sel=="OVERDUE"?' selected':'') . '>ATRASADO</option>
      </select></label>';
echo '<label>De <input type="date" name="date_from" value="' . htmlspecialchars($date_from_val) . '"></label>';
echo '<label>Até <input type="date" name="date_to" value="' . htmlspecialchars($date_to_val) . '"></label>';
echo '<button type="submit">Filtrar</button>';
echo '</form>';

echo '<table>';
echo '<tr><th>ID</th><th>Cliente</th><th>Valor</th><th>Status</th><th>Vencimento</th><th>Ações</th></tr>';
foreach ($faturas as $f) {
    echo '<tr>';
    echo "<td>{$f['id']}</td>";
    echo "<td>{$f['cliente_id']}</td>";
    echo "<td>R$ {$f['valor']}</td>";
    echo "<td>{$f['status']}</td>";
    echo "<td>{$f['due_date']}</td>";
    echo "<td>
            <a href=\"financeiro/faturas/{$f['id']}\">Ver</a> |";
    if (!empty($f['invoice_url'])) {
        echo "<a href=\"{$f['invoice_url']}\" target=\"_blank\" style=\"margin:0 5px;\">Pagar</a> |";
    }
    echo "<form action=\"financeiro/faturas/sync\" method=\"POST\" style=\"display:inline\">
              <button type=\"submit\">Sincronizar</button>
            </form>
          </td>";
    echo '</tr>';
}
echo '</table>';

// Controles de paginação
if (isset($pagina) && isset($total_paginas) && $total_paginas > 1) {
    echo '<div style="margin-top:20px;">';
    $query = $_GET;
    if ($pagina > 1) {
        $prev = $pagina - 1;
        $query['pagina'] = $prev;
        $qstr = http_build_query($query);
        echo "<a href='?$qstr' style='margin-right:10px;'>&laquo; Anterior</a>";
    }
    echo "Página $pagina de $total_paginas";
    if ($pagina < $total_paginas) {
        $next = $pagina + 1;
        $query['pagina'] = $next;
        $qstr = http_build_query($query);
        echo "<a href='?$qstr' style='margin-left:10px;'>Próximo &raquo;</a>";
    }
    echo '</div>';
} 