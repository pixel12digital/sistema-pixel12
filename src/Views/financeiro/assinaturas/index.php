<?php include_once __DIR__ . '/../../../../painel/menu_lateral.php'; ?>
// lista de assinaturas em $assinaturas
echo '<h1>Assinaturas</h1>';
echo '<table>';
echo '<tr><th>ID</th><th>Cliente</th><th>Status</th><th>Periodicidade</th><th>Próximo Vencimento</th><th>Ações</th></tr>';
foreach ($assinaturas as $a) {
    echo '<tr>';
    echo "<td>{$a['id']}</td>";
    echo "<td>{$a['cliente_id']}</td>";
    echo "<td>{$a['status']}</td>";
    echo "<td>{$a['periodicidade']}</td>";
    echo "<td>{$a['next_due_date']}</td>";
    echo "<td>
            <a href=\"financeiro/assinaturas/{$a['id']}\">Ver</a> |
            <form action=\"financeiro/assinaturas/sync\" method=\"POST\" style=\"display:inline\">
              <button type=\"submit\">Sincronizar</button>
            </form>
          </td>";
    echo '</tr>';
}
echo '</table>'; 