<?php include_once __DIR__ . '/../../../../painel/menu_lateral.php'; ?>
// detalhes de $fatura
echo "<h1>Fatura #{$fatura['id']}</h1>";
echo "<p>Cliente: {$fatura['cliente_id']}</p>";
echo "<p>Valor: R$ {$fatura['valor']}</p>";
echo "<p>Status: {$fatura['status']}</p>";
echo "<p>Vencimento: {$fatura['due_date']}</p>";
if (!empty($fatura['invoice_url'])) {
    echo "<p><strong>Link de Pagamento:</strong> <a href=\"{$fatura['invoice_url']}\" target=\"_blank\" style=\"background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block; margin:10px 0;\">PAGAR AGORA</a></p>";
    echo "<p>Link: <a href=\"{$fatura['invoice_url']}\" target=\"_blank\">Baixar Fatura</a></p>";
}
echo "<p>Criado em: {$fatura['created_at']}</p>";
echo "<p>Atualizado em: {$fatura['updated_at']}</p>"; 